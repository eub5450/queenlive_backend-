<?php

namespace App\Services\V5;

use App\Models\Gift;
use App\Models\Lavel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use RedisCacheFunction;

/**
 * V5 schema-style VERSIONED user state cache (DISPLAY-ONLY).
 *
 * Boss 2026-06-28 / Agent SC1.
 *
 * WHAT THIS IS
 * ------------
 * An etag-style, version-stamped per-user state object stored in Redis. On a
 * MISS we compute the object from the AUTHORITATIVE queries (the exact same
 * formulas the v4 read paths use today) and cache it. On a HIT we return the
 * cached object WITHOUT recomputing. A monotonically increasing version
 * counter is the "schema version": every gift/balance mutation calls bump()
 * which INCRs the counter and deletes the cached object, so the next get()
 * recomputes a fresh object with a higher version. Unchanged users keep
 * serving the same cached object (no DB hit, same version).
 *
 * No DDL (task #53): version + object live entirely in Redis. The `gifts`
 * table and `users.balance` remain untouched.
 *
 * ===================================================================
 * MONEY-SAFETY RULE (do not violate)
 * -------------------------------------------------------------------
 * This cache is DISPLAY-ONLY: profile balance display, gift totals shown in
 * the UI, level + level progress. It is NEVER the source of truth for a
 * spend / withdraw / transfer / buy / debit decision. Those paths MUST read
 * the authoritative query (gifts table / users.balance under lockForUpdate,
 * already wrapped by Agent T) — never this cache.
 *
 * The `balance` field below mirrors v4 VideoBrdController::prepareCallDetails
 * host_balance = previous_coin + monthly SUM(gifts.value received). That is
 * the DISPLAYED host earning balance — it is NOT users.balance (the spendable
 * wallet). Do not feed this value into a sufficiency check.
 * ===================================================================
 */
class V5UserStateCache
{
    /** Redis key prefix for the cached state object (JSON). */
    private const OBJ_PREFIX = 'queenlive:v5:userstate:';

    /** Redis key prefix for the per-user version counter. */
    private const VER_PREFIX = 'queenlive:v5:userstate:ver:';

    /** Safety expiry on the cached object so a missed bump can never serve stale forever. */
    private const TTL = 300;

    private static function objKey(string $userId): string
    {
        return self::OBJ_PREFIX . $userId;
    }

    private static function verKey(string $userId): string
    {
        return self::VER_PREFIX . $userId;
    }

    /**
     * Return the versioned state object for a user.
     *
     * MISS  -> compute from authoritative queries, cache, return.
     * HIT   -> return the cached object verbatim (no recompute, same version).
     *
     * Shape:
     *   version, balance, total_gifts_received, total_gifts_sent,
     *   today_received, today_sent, level, level_progress_pct, updated_at
     */
    public static function get(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return self::emptyState('0', 0);
        }

        // HIT path: return cached object as-is.
        try {
            $cached = Redis::get(self::objKey($userId));
            if ($cached !== null && $cached !== false) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded) && isset($decoded['version'])) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('V5UserStateCache::get redis read failed', [
                'user_id' => $userId, 'error' => $e->getMessage(),
            ]);
        }

        // MISS path: compute + cache.
        $state = self::compute($userId);

        try {
            Redis::setex(self::objKey($userId), self::TTL, json_encode($state, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::warning('V5UserStateCache::get redis write failed', [
                'user_id' => $userId, 'error' => $e->getMessage(),
            ]);
        }

        return $state;
    }

    /**
     * Invalidate a user's state: INCR the version counter and drop the cached
     * object so the next get() recomputes with a fresh, higher version.
     *
     * Call this AFTER an authoritative gift/balance write commits (inside
     * DB::afterCommit at the mutation site). Cheap and idempotent — safe to
     * call more than once.
     */
    public static function bump(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }
        try {
            Redis::incr(self::verKey($userId));
            Redis::del(self::objKey($userId));
        } catch (\Throwable $e) {
            Log::warning('V5UserStateCache::bump failed', [
                'user_id' => $userId, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Batch get() for lists (ranking rows, cohost lists). One User::whereIn
     * for any users that need recompute, plus pipelined Redis reads/writes.
     *
     * Returns map: userId => state object.
     */
    public static function getMany(array $userIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            fn ($v) => trim((string) $v),
            $userIds
        ), fn ($v) => $v !== '')));

        if (empty($ids)) {
            return [];
        }

        $result = [];
        $missing = [];

        // 1. Pipelined HIT read for all ids.
        try {
            $rows = Redis::pipeline(function ($pipe) use ($ids) {
                foreach ($ids as $id) {
                    $pipe->get(self::objKey($id));
                }
            });
            foreach ($ids as $i => $id) {
                $raw = $rows[$i] ?? null;
                if ($raw !== null && $raw !== false) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded) && isset($decoded['version'])) {
                        $result[$id] = $decoded;
                        continue;
                    }
                }
                $missing[] = $id;
            }
        } catch (\Throwable $e) {
            Log::warning('V5UserStateCache::getMany pipeline read failed', [
                'error' => $e->getMessage(),
            ]);
            $missing = $ids;
        }

        if (empty($missing)) {
            return $result;
        }

        // 2. Compute the misses (one whereIn for the user rows).
        $computed = self::computeMany($missing);

        // 3. Pipelined write-back of the misses.
        try {
            Redis::pipeline(function ($pipe) use ($computed) {
                foreach ($computed as $id => $state) {
                    $pipe->setex(self::objKey((string) $id), self::TTL, json_encode($state, JSON_UNESCAPED_UNICODE));
                }
            });
        } catch (\Throwable $e) {
            Log::warning('V5UserStateCache::getMany pipeline write failed', [
                'error' => $e->getMessage(),
            ]);
        }

        foreach ($computed as $id => $state) {
            $result[(string) $id] = $state;
        }

        return $result;
    }

    // -----------------------------------------------------------------
    // Computation (authoritative, mirrors v4 read formulas exactly).
    // -----------------------------------------------------------------

    private static function currentVersion(string $userId): int
    {
        try {
            $v = Redis::get(self::verKey($userId));
            return (int) ($v ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Compute the full state object for one user from authoritative sources.
     */
    private static function compute(string $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return self::emptyState($userId, self::currentVersion($userId));
        }

        $today      = now()->toDateString();
        $start_date = date('Y-m') . '-01';
        $end_date   = date('Y-m-t');

        // --- Gift totals (gifts table = source of truth) ---
        $total_received = (int) Gift::where('reciever_id', $userId)->sum('value');
        $total_sent     = (int) Gift::where('sander_id', $userId)->sum('value');

        $today_received = (int) Gift::where('reciever_id', $userId)
            ->whereDate('date', $today)->sum('value');
        $today_sent = (int) Gift::where('sander_id', $userId)
            ->whereDate('date', $today)->sum('value');

        // --- Display balance: EXACTLY as v4 prepareCallDetails host_balance ---
        //     previous_coin + monthly SUM(gifts.value received this month).
        $monthly_received = (int) Gift::where('reciever_id', $userId)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('value');
        $balance = (int) (($user->previous_coin ?? 0) + $monthly_received);

        // --- Level + progress from the Lavel ladder (v4 LavelController) ---
        [$level, $progressPct] = self::levelAndProgress($user, $total_sent);

        return [
            'version'              => self::currentVersion($userId),
            'balance'              => $balance,
            'total_gifts_received' => $total_received,
            'total_gifts_sent'     => $total_sent,
            'today_received'       => $today_received,
            'today_sent'           => $today_sent,
            'level'                => $level,
            'level_progress_pct'   => $progressPct,
            'updated_at'           => now()->toDateTimeString(),
        ];
    }

    /**
     * Batch compute for getMany — one whereIn for the user rows. Gift sums
     * are still per-user (same SUM queries v4 uses); correctness over
     * micro-optimization, and ranking lists are short.
     */
    private static function computeMany(array $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        $out = [];

        $today      = now()->toDateString();
        $start_date = date('Y-m') . '-01';
        $end_date   = date('Y-m-t');

        foreach ($userIds as $id) {
            $user = $users->get($id);
            if (!$user) {
                $out[$id] = self::emptyState((string) $id, self::currentVersion((string) $id));
                continue;
            }

            $total_received = (int) Gift::where('reciever_id', $id)->sum('value');
            $total_sent     = (int) Gift::where('sander_id', $id)->sum('value');
            $today_received = (int) Gift::where('reciever_id', $id)->whereDate('date', $today)->sum('value');
            $today_sent     = (int) Gift::where('sander_id', $id)->whereDate('date', $today)->sum('value');
            $monthly_received = (int) Gift::where('reciever_id', $id)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('value');
            $balance = (int) (($user->previous_coin ?? 0) + $monthly_received);

            [$level, $progressPct] = self::levelAndProgress($user, $total_sent);

            $out[$id] = [
                'version'              => self::currentVersion((string) $id),
                'balance'              => $balance,
                'total_gifts_received' => $total_received,
                'total_gifts_sent'     => $total_sent,
                'today_received'       => $today_received,
                'today_sent'           => $today_sent,
                'level'                => $level,
                'level_progress_pct'   => $progressPct,
                'updated_at'           => now()->toDateTimeString(),
            ];
        }

        return $out;
    }

    /**
     * Level + progress percent, mirroring v4 LavelController:
     *   current level    = users.level
     *   next threshold   = Lavel.amount WHERE update_lavel = level+1
     *   progress percent = total_sent_gift / next_threshold * 100  (capped 100)
     */
    private static function levelAndProgress($user, int $totalSent): array
    {
        $current_level = (int) ($user->level ?? 0);
        $next_level    = $current_level + 1;

        // Admin-managed slow-changing table — cache the lookup like v4 does.
        $next_row = Cache::remember(
            'v5:queenlive:lavel_target_v1_' . $next_level,
            86400,
            function () use ($next_level) {
                return Lavel::where('update_lavel', $next_level)->first();
            }
        );

        if (!$next_row || (int) $next_row->amount <= 0) {
            // No next row => top-level user => 100%.
            return [$current_level, 100];
        }

        $next_amount = (int) $next_row->amount;
        $pct = intval($totalSent / max(1, $next_amount) * 100);
        if ($pct > 100) {
            $pct = 100;
        }
        if ($pct < 0) {
            $pct = 0;
        }

        return [$current_level, $pct];
    }

    private static function emptyState(string $userId, int $version): array
    {
        return [
            'version'              => $version,
            'balance'              => 0,
            'total_gifts_received' => 0,
            'total_gifts_sent'     => 0,
            'today_received'       => 0,
            'today_sent'           => 0,
            'level'                => 0,
            'level_progress_pct'   => 0,
            'updated_at'           => now()->toDateTimeString(),
        ];
    }
}
