<?php

namespace App\Services\V5;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * V5 gift counter denormalizer.
 *
 * Source of truth = `gifts` table. Counters on `users`, `ranking_daily`,
 * `ranking_monthly`, and `room_list_cache` are DERIVED — never read by money
 * paths. Read paths (balance / withdraw / SUM(value)) still hit `gifts`
 * directly until a separate agent flips them.
 *
 * Integrity rule: every gift insert MUST go through ::recordGift(). The whole
 * insert + counter update runs in DB::transaction so if any counter UPDATE
 * throws the gift row rolls back too. No silent half-state possible.
 *
 * Agent M / 2026-06-28.
 */
class GiftBalanceService
{
    /**
     * Wrap a gift insert with denorm counter updates.
     *
     * Caller passes the gift DATA in the same shape used by the existing
     * `Gift::insert(...)` / `new Gift; ->save()` paths so callers can swap
     * in-place without changing semantics.
     *
     * Required keys: sander_id, reciever_id, value
     * Optional keys: name, channelName, date, agency_code, reaward_time,
     *                checked, reward_type, imie
     *
     * Returns the new gifts.id.
     *
     * Throws on any DB failure; the outer caller's surrounding transaction
     * (if any) will roll the whole thing back — including the wallet debit.
     */
    public function recordGift(array $giftData): int
    {
        return DB::transaction(function () use ($giftData) {
            $senderId   = (string) ($giftData['sander_id'] ?? '');
            $receiverId = (string) ($giftData['reciever_id'] ?? '');
            $value      = (int) ($giftData['value'] ?? 0);

            if ($senderId === '' || $receiverId === '' || $value <= 0) {
                throw new \InvalidArgumentException(
                    'GiftBalanceService::recordGift requires sander_id, reciever_id, value'
                );
            }

            // Normalize date: gifts.date is the per-day aggregation key used
            // by every existing SUM query. Fall back to NOW.
            $rawDate = $giftData['date'] ?? now();
            if ($rawDate instanceof \DateTimeInterface) {
                $today = \Carbon\Carbon::instance($rawDate)->setTimezone(config('app.timezone', 'Europe/London'))->toDateString();
            } else {
                $today = \Carbon\Carbon::parse((string) $rawDate, config('app.timezone', 'Europe/London'))->toDateString();
            }
            $monthKey = \Carbon\Carbon::parse($today, config('app.timezone', 'Europe/London'))->startOfMonth()->toDateString();
            $now = now()->toDateTimeString();

            // Fill defaults the underlying Gift model expects (matches the
            // shape created by existing controllers).
            $row = $giftData;
            $row['sander_id']   = $senderId;
            $row['reciever_id'] = $receiverId;
            $row['value']       = $value;
            if (!isset($row['date'])) {
                $row['date'] = $now;
            }
            if (!array_key_exists('created_at', $row)) {
                $row['created_at'] = $now;
            }
            if (!array_key_exists('updated_at', $row)) {
                $row['updated_at'] = $now;
            }

            // 1. Insert the source-of-truth row first.
            $giftId = DB::table('gifts')->insertGetId($row);

            // 2. Reset today counters on the FIRST gift of a new day before
            //    incrementing. The check is per-user, atomic via WHERE.
            $this->resetTodayIfStale($senderId, $today);
            $this->resetTodayIfStale($receiverId, $today);

            // 3. Bump user counters (idempotent under txn, atomic UPDATE).
            DB::table('users')->where('id', $receiverId)->update([
                'total_gifts_received_value' => DB::raw("`total_gifts_received_value` + {$value}"),
                'today_gifts_received_value' => DB::raw("`today_gifts_received_value` + {$value}"),
                'today_aggregation_date'     => $today,
            ]);
            DB::table('users')->where('id', $senderId)->update([
                'total_gifts_sent_value' => DB::raw("`total_gifts_sent_value` + {$value}"),
                'today_gifts_sent_value' => DB::raw("`today_gifts_sent_value` + {$value}"),
                'today_aggregation_date' => $today,
            ]);

            // 4. ranking_daily upsert (one row per user per date; both
            //    receiver-side and sender-side).
            DB::statement(
                "INSERT INTO ranking_daily (user_id, aggregation_date, received_value, sent_value, created_at, updated_at)
                 VALUES (?, ?, ?, 0, ?, ?)
                 ON DUPLICATE KEY UPDATE received_value = received_value + VALUES(received_value), updated_at = ?",
                [$receiverId, $today, $value, $now, $now, $now]
            );
            DB::statement(
                "INSERT INTO ranking_daily (user_id, aggregation_date, received_value, sent_value, created_at, updated_at)
                 VALUES (?, ?, 0, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE sent_value = sent_value + VALUES(sent_value), updated_at = ?",
                [$senderId, $today, $value, $now, $now, $now]
            );

            // 5. ranking_monthly upsert (same shape, month bucket).
            DB::statement(
                "INSERT INTO ranking_monthly (user_id, aggregation_month, received_value, sent_value, created_at, updated_at)
                 VALUES (?, ?, ?, 0, ?, ?)
                 ON DUPLICATE KEY UPDATE received_value = received_value + VALUES(received_value), updated_at = ?",
                [$receiverId, $monthKey, $value, $now, $now, $now]
            );
            DB::statement(
                "INSERT INTO ranking_monthly (user_id, aggregation_month, received_value, sent_value, created_at, updated_at)
                 VALUES (?, ?, 0, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE sent_value = sent_value + VALUES(sent_value), updated_at = ?",
                [$senderId, $monthKey, $value, $now, $now, $now]
            );

            // 6. room_list_cache today-bump (only if this gift is in a room
            //    and the cache row exists; do NOT auto-create — the room list
            //    builder is responsible for creating those rows).
            $channelName = (string) ($giftData['channelName'] ?? '');
            if ($channelName !== '' && $channelName !== 'chat_gifiting') {
                DB::table('room_list_cache')
                    ->where('channelName', $channelName)
                    ->update([
                        'gift_today_value' => DB::raw("`gift_today_value` + {$value}"),
                        'updated_at'       => now(),
                    ]);
            }

            return (int) $giftId;
        });
    }

    /**
     * Batch variant: same semantics as recordGift but for an array of rows
     * (matches the existing VideoBrdController::VideoGiftPush path which
     * builds $giftEntries and calls Gift::insert($giftEntries)).
     *
     * The whole batch runs inside one DB::transaction so if ANY counter
     * update throws, EVERY gift in the batch rolls back.
     */
    public function recordGiftBatch(array $giftEntries): int
    {
        if (empty($giftEntries)) {
            return 0;
        }

        return DB::transaction(function () use ($giftEntries) {
            $inserted = 0;
            foreach ($giftEntries as $row) {
                $this->recordGift($row);
                $inserted++;
            }
            return $inserted;
        });
    }

    /**
     * Zero today_* counters and reset the rollover key when the stored date
     * is NULL or != current date. Idempotent: the WHERE clause filters out
     * users whose counter was already reset earlier this same day.
     */
    private function resetTodayIfStale(string $userId, string $today): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->where(function ($q) use ($today) {
                $q->whereNull('today_aggregation_date')
                  ->orWhere('today_aggregation_date', '!=', $today);
            })
            ->update([
                'today_gifts_received_value' => 0,
                'today_gifts_sent_value'     => 0,
                'today_aggregation_date'     => $today,
            ]);
    }

    /**
     * Rebuild room_call_list_cache for one channel. Idempotent — safe to
     * call from RoomActionService cohost mutations (Agent K's surface;
     * caller-side wiring is NOT done by Agent M).
     *
     * The cached payload shape MUST match the existing prepareCallDetails
     * return so a future read-path swap is transparent. Keys are:
     *   host_list, co_host_list, host_balance, star, star_complete_parcent
     */
    public function rebuildCallListCache(string $roomType, string $channelName, string $hostId = ''): void
    {
        $payload = $this->assembleCallListPayload($roomType, $channelName, $hostId);

        $hostFromPayload = '';
        if (!empty($payload['host_list'][0]['co_host_id'])) {
            $hostFromPayload = (string) $payload['host_list'][0]['co_host_id'];
        }

        DB::table('room_call_list_cache')->updateOrInsert(
            ['channelName' => $channelName],
            [
                'host_id'        => $hostId !== '' ? $hostId : $hostFromPayload,
                'room_type'      => $roomType,
                'snapshot_json'  => json_encode($payload),
                'cohost_count'   => count($payload['co_host_list'] ?? []),
                'audience_count' => (int) ($payload['audience_count'] ?? 0),
                'rebuilt_at'     => now(),
                'updated_at'     => now(),
            ]
        );
    }

    /**
     * Build the prepareCallDetails-shaped payload from the database, but
     * read host/cohost aggregations from the denorm counters on `users`
     * (NOT from SUM(gifts.value)). Returns the same keys VideoBrdController
     * ::prepareCallDetails returns:
     *   host_list, co_host_list, host_balance, star, star_complete_parcent
     *
     * Plus an extra `audience_count` field for the cache row counter.
     *
     * Used only by the cache table; the read path still calls the original
     * SUM-based prepareCallDetails until a future agent swaps it.
     */
    private function assembleCallListPayload(string $roomType, string $channelName, string $hostId = ''): array
    {
        $start_date = now()->startOfMonth()->toDateString();
        $end_date   = now()->endOfMonth()->toDateString();
        $today      = now()->toDateString();

        // Resolve host_id: prefer caller-supplied, else fall back to the
        // first non-null host_id on live_calls for this channel.
        if ($hostId === '') {
            $row = DB::table('live_calls')
                ->where('channelName', $channelName)
                ->whereNotNull('host_id')
                ->select('host_id')
                ->first();
            $hostId = (string) ($row->host_id ?? '');
        }

        if ($hostId === '') {
            return [
                'host_list'             => [],
                'co_host_list'          => [],
                'host_balance'          => 0,
                'star'                  => 0,
                'star_complete_parcent' => 0,
                'audience_count'        => 0,
            ];
        }

        $host = DB::table('users')->where('id', $hostId)->first();
        if (!$host) {
            return [
                'host_list'             => [],
                'co_host_list'          => [],
                'host_balance'          => 0,
                'star'                  => 0,
                'star_complete_parcent' => 0,
                'audience_count'        => 0,
            ];
        }

        // Monthly + today sums for host. The denorm counters give us LIFETIME
        // received and TODAY received; the existing read path wants MONTHLY.
        // We still need the monthly sum from `gifts` until the read path is
        // swapped — but at that point we'll add a monthly counter too. For
        // now compute monthly inline so the cached payload matches reads.
        $host_monthly_gift = (int) DB::table('gifts')
            ->where('reciever_id', $hostId)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('value');
        $host_balance = ((int) ($host->previous_coin ?? 0)) + $host_monthly_gift;
        $today_gift   = (int) ($host->today_gifts_received_value ?? 0);

        $host_entry = [
            'channelName'    => $channelName,
            'profile'        => $host->profile ?? '',
            'is_vip'         => $host->is_vip ?? '0',
            'balance'        => $host_balance,
            'co_host_name'   => $host->name ?? '',
            'set_no'         => '0',
            'mute'           => 0,
            'frame'          => (string) ($host->frame ?? ''),
            'co_host_id'     => (string) $host->id,
            'co_host_status' => 'Accept',
            'super_mute'     => '0',
        ];

        $list         = [$host_entry];
        $co_host_list = [];

        // Cohosts via live_calls + users (1 join, no SUM).
        $accept_rows = DB::table('live_calls as lc')
            ->leftJoin('users as u', 'u.id', '=', 'lc.co_host_id')
            ->where('lc.host_id', $hostId)
            ->where('lc.channelName', $channelName)
            ->where('lc.status', 'Accept')
            ->select(
                'lc.co_host_id',
                'lc.mute',
                'lc.super_mute',
                'lc.is_co_host_active',
                'lc.set_no',
                'u.profile',
                'u.is_vip',
                'u.name',
                'u.frame'
            )
            ->get();

        if ($accept_rows->isNotEmpty()) {
            $cohostIds = $accept_rows->pluck('co_host_id')->unique()->all();
            $co_host_gifts = DB::table('gifts')
                ->where('channelName', $channelName)
                ->whereIn('reciever_id', $cohostIds)
                ->groupBy('reciever_id')
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->pluck('total_value', 'reciever_id');

            foreach ($accept_rows as $row) {
                $co_host_balance = (int) ($co_host_gifts[$row->co_host_id] ?? 0);
                $co_host_data = [
                    'channelName'    => $channelName,
                    'profile'        => $row->profile ?? '',
                    'is_vip'         => $row->is_vip ?? '0',
                    'balance'        => $co_host_balance,
                    'co_host_name'   => $row->name ?? '',
                    'set_no'         => (string) ($row->set_no ?? '0'),
                    'mute'           => $row->mute ?? 0,
                    'frame'          => (string) ($row->frame ?? ''),
                    'co_host_id'     => (string) $row->co_host_id,
                    'co_host_status' => (string) ($row->is_co_host_active ?? ''),
                    'super_mute'     => (string) ($row->super_mute ?? '0'),
                ];
                $list[]         = $co_host_data;
                $co_host_list[] = $co_host_data;
            }
        }

        // Star level — same boundary table as the existing prepareCallDetails.
        $levels = [
            [0, 50000, 1, 50000],
            [50000, 200000, 2, 200000],
            [200000, 500000, 3, 500000],
            [500000, 1000000, 4, 1000000],
            [1000000, 2000000, 5, 2000000],
            [2000000, PHP_INT_MAX, 5, 20000000],
        ];
        $star = 0;
        $next_level_amount = 1;
        foreach ($levels as $lv) {
            if ($today_gift >= $lv[0] && $today_gift < $lv[1]) {
                $star = $lv[2];
                $next_level_amount = $lv[3];
                break;
            }
        }
        $need_percent = ($next_level_amount > 0)
            ? intval(($today_gift / $next_level_amount) * 100)
            : 0;

        // Audience count for the cache row (not part of legacy payload but
        // cheap to read here so the cache table can serve room-list sort).
        $audience_count = 0;
        if (Schema::hasTable('audience_joins')) {
            try {
                $audience_count = (int) DB::table('audience_joins')
                    ->where('channelName', $channelName)
                    ->count();
            } catch (\Throwable $e) {
                $audience_count = 0;
            }
        }

        return [
            'host_list'             => $list,
            'co_host_list'          => $co_host_list,
            'host_balance'          => ((int) ($host->previous_coin ?? 0)) + $host_monthly_gift,
            'star'                  => $star,
            'star_complete_parcent' => $need_percent,
            'audience_count'        => $audience_count,
        ];
    }
}
