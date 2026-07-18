<?php

namespace App\Services\V5;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * V5 Redis Optimizer.
 *
 * Centralizes hot-key cache shape for v5 room reads:
 *  - Stampede-protected snapshot reads (Cache::lock with immediate-or-fail).
 *  - Short-TTL comment list cache.
 *  - Atomic counter increments (audience count, gift totals).
 *  - Deterministic invalidation after mutations.
 *
 * Boss 2026-06-28: replaces the ad-hoc `Redis::setex(..., 200, ...)` pattern
 * in V5 controllers — 200s with no invalidation guaranteed a stale mute /
 * cohost window after every accept/cut.
 */
class V5RedisOptimizer
{
    /** Snapshot TTL — short so a missed invalidation self-heals fast. */
    const SNAPSHOT_TTL = 60;

    /** Audience-presence count TTL. */
    const PRESENCE_TTL = 30;

    /** Profile data inside a room (level, frame, vip). */
    const PROFILE_TTL = 300;

    /** Comment list slice (new for V5 /comments/since). */
    const COMMENTS_TTL = 10;

    /** Action lock / rate-limit. */
    const ACTION_LOCK_TTL = 8;

    /** Stampede lock TTL. */
    const STAMPEDE_LOCK_TTL = 5;

    /**
     * Build the canonical snapshot cache key for a given room type + channel.
     */
    public static function snapshotKey($type, $channel)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        return "queenlive:v5:snapshot:{$type}:{$channel}";
    }

    /**
     * Comment-slice key, bucketed by the sinceMs floor.
     */
    public static function commentsKey($type, $channel, $sinceMs)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        // Bucket sinceMs to 1s — avoids per-millisecond key explosion.
        $bucket = intval($sinceMs / 1000) * 1000;
        return "queenlive:v5:comments:{$type}:{$channel}:{$bucket}";
    }

    /**
     * Presence count key.
     */
    public static function presenceKey($type, $channel)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        return "queenlive:v5:presence:{$type}:{$channel}";
    }

    /**
     * Metric counter key.
     */
    public static function metricKey($type, $channel, $metric)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        $metric = preg_replace('/[^a-z0-9_]/', '', strtolower($metric));
        return "queenlive:v5:metric:{$type}:{$channel}:{$metric}";
    }

    /**
     * Delete every snapshot-flavoured key for the room.
     * Called after every cohost / mute / seat / comment mutation.
     */
    public static function bustRoomSnapshot($type, $channel)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        if ($channel === '') {
            return;
        }

        $redis = Cache::store('redis');

        // 1. Direct snapshot key.
        $redis->forget(self::snapshotKey($type, $channel));

        // 2. Comment slice keys — short TTL, scan-and-forget.
        try {
            $prefix = "queenlive:v5:comments:{$type}:{$channel}:*";
            $cursor = null;
            $iter = 0;
            do {
                $res = Redis::scan($cursor, ['match' => $prefix, 'count' => 100]);
                if (!is_array($res) || count($res) < 2) {
                    break;
                }
                $cursor = $res[0];
                if (!empty($res[1])) {
                    foreach ($res[1] as $k) {
                        // Predis returns prefixed keys — strip Laravel's cache prefix.
                        $bare = preg_replace('/^.*:cache:/', '', $k);
                        $redis->forget($bare);
                    }
                }
                if (++$iter > 50) {
                    break; // safety
                }
            } while ($cursor != 0);
        } catch (\Throwable $e) {
            Log::warning('V5RedisOptimizer.bustRoomSnapshot scan failed', [
                'err' => $e->getMessage(),
                'type' => $type,
                'channel' => $channel,
            ]);
        }

        // 3. Legacy v4 key — bust for back-compat during cutover.
        try {
            $legacyKeys = Redis::keys('queenlive:Video_Brd_Call_Details_*_' . $channel);
            if (!empty($legacyKeys)) {
                Redis::del($legacyKeys);
            }
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    /**
     * Read-through snapshot cache with stampede protection.
     *
     * On a thundering herd (host accepts cohost -> 100 audience snapshot reads
     * in the same 50ms), only ONE caller rebuilds the payload. Everyone else
     * either returns the freshly-built value (block(0) succeeded) or the prior
     * cached value if one exists. Never have 100 simultaneous prepareCallDetails
     * DB rebuilds.
     */
    public static function cacheRoomSnapshot($type, $channel, callable $build, $ttl = null)
    {
        $key = self::snapshotKey($type, $channel);
        $ttl = $ttl ?: self::SNAPSHOT_TTL;
        $redis = Cache::store('redis');

        $cached = $redis->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $lockKey = "lock:{$key}";
        $lock = $redis->lock($lockKey, self::STAMPEDE_LOCK_TTL);

        try {
            if ($lock->get()) {
                // Double-check inside the lock — another worker may have populated.
                $cached = $redis->get($key);
                if ($cached !== null) {
                    return $cached;
                }
                $value = $build();
                $redis->put($key, $value, $ttl);
                return $value;
            }

            // Lock not acquired — wait briefly for the leader, fall back to build.
            for ($i = 0; $i < 10; $i++) {
                usleep(50000); // 50ms
                $cached = $redis->get($key);
                if ($cached !== null) {
                    return $cached;
                }
            }
            // Worst case: still no value, just build (no cache write — leader will).
            return $build();
        } finally {
            try {
                $lock->release();
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Short-TTL comment slice cache. New for V5 /comments/since path.
     */
    public static function cacheCommentsSince($type, $channel, $sinceMs, callable $build)
    {
        $key = self::commentsKey($type, $channel, $sinceMs);
        return Cache::store('redis')->remember($key, self::COMMENTS_TTL, $build);
    }

    /**
     * Atomic increment for cheap metrics. Returns the new value.
     */
    public static function incrementRoomMetric($type, $channel, $metric, $by = 1)
    {
        $key = self::metricKey($type, $channel, $metric);
        try {
            $new = Redis::incrby($key, (int) $by);
            // First write -> set a TTL so abandoned rooms don't leak.
            if ($new == $by) {
                Redis::expire($key, 3600);
            }
            return (int) $new;
        } catch (\Throwable $e) {
            Log::warning('V5RedisOptimizer.incrementRoomMetric failed', [
                'err' => $e->getMessage(),
                'key' => $key,
            ]);
            return 0;
        }
    }

    /**
     * Read a metric counter without incrementing.
     */
    public static function getRoomMetric($type, $channel, $metric)
    {
        $key = self::metricKey($type, $channel, $metric);
        try {
            return (int) Redis::get($key);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Clear all metric counters for a room (called on room end).
     */
    public static function clearRoomMetrics($type, $channel)
    {
        $type = self::normType($type);
        $channel = trim((string) $channel);
        if ($channel === '') {
            return;
        }
        try {
            $cursor = null;
            $iter = 0;
            $pattern = "*queenlive:v5:metric:{$type}:{$channel}:*";
            do {
                $res = Redis::scan($cursor, ['match' => $pattern, 'count' => 100]);
                if (!is_array($res) || count($res) < 2) {
                    break;
                }
                $cursor = $res[0];
                if (!empty($res[1])) {
                    foreach ($res[1] as $k) {
                        Redis::del($k);
                    }
                }
                if (++$iter > 50) {
                    break;
                }
            } while ($cursor != 0);
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    private static function normType($type)
    {
        $t = strtolower(trim((string) $type));
        if (!in_array($t, ['audio', 'video', 'multi'], true)) {
            $t = 'video';
        }
        return $t;
    }
}
