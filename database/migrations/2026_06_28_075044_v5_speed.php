<?php
/**
 * v5 endpoint speed: composite indexes covering the hot paths in
 * VideoBrdController::UserData -> prepareCallDetails and the per-join
 * audience/kicks/host_data lookups.
 *
 * All add-index operations use a manual SHOW INDEX guard so re-running on a
 * partially indexed prod node is a no-op. No data changes, no destructive
 * statements.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Indexes to create, keyed by table.
     * Each entry: [index_name, [columns], 'reason']
     */
    private array $plan = [
        'gifts' => [
            // host MONTHLY/today sums: WHERE reciever_id=? AND date BETWEEN ? AND ?
            // currently picks ALL (full scan of 13k rows, will grow).
            ['bdlive_gifts_reciever_date_idx',  ['reciever_id', 'date'],
                'host_balance/today_gift SUM in prepareCallDetails'],
            // per-cohost gift breakdown: WHERE channelName=? AND reciever_id IN (...)
            ['bdlive_gifts_channel_reciever_idx', ['channelName', 'reciever_id'],
                'co_host_gifts groupBy in prepareCallDetails'],
            // daily reward exists() check in VideoBrdDayTimeRequest
            ['bdlive_gifts_reciever_date_sender_idx', ['reciever_id', 'date', 'sander_id'],
                'live-time reward existingReward lookup'],
        ],
        'kicks' => [
            // per-audience-join kick check (currently full table scan, no indexes).
            ['bdlive_kicks_user_channel_idx',   ['user_id', 'channelName'],
                'kicks check on audience join'],
            ['bdlive_kicks_channel_idx',        ['channelName'],
                'channel-scoped kick scan'],
        ],
        'host_data' => [
            // UserData JOINs host_data ON user_id repeatedly; only PRIMARY exists.
            ['bdlive_host_data_user_id_idx',    ['user_id'],
                'UserData hosting_type/agency JOIN'],
            ['bdlive_host_data_agency_code_idx',['agency_code'],
                'agencies JOIN side of UserData'],
        ],
        'agencies' => [
            // UserData JOINs agencies ON code.
            ['bdlive_agencies_code_idx',        ['code'],
                'UserData agencies JOIN target'],
        ],
        // The following are already indexed in prod (verified 2026-06-28) but
        // we still ensure them so dev/staging match prod:
        'live_calls' => [
            ['bdlive_lc_cohost_channel_idx',   ['co_host_id', 'channelName'],
                'JoinStore stale-row scan / cohost lookup'],
        ],
        'audience_joins' => [
            ['bdlive_aj_channel_only_idx',     ['channelName'],
                'audience_count scan'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->plan as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                $this->info("skip {$table}: table not present");
                continue;
            }
            foreach ($indexes as [$name, $cols, $reason]) {
                if ($this->indexExists($table, $name)) {
                    continue;
                }
                $colList = implode(',', array_map(fn ($c) => "`{$c}`", $cols));
                try {
                    DB::statement("CREATE INDEX `{$name}` ON `{$table}` ({$colList})");
                } catch (\Throwable $e) {
                    // Soft-fail: don't abort the whole migration if one table is
                    // already partially indexed under a different name. Log and
                    // continue so the remaining indexes still land.
                    $this->info("skip {$table}.{$name}: " . $e->getMessage());
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->plan as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as [$name, $cols, $reason]) {
                if (!$this->indexExists($table, $name)) {
                    continue;
                }
                try {
                    DB::statement("DROP INDEX `{$name}` ON `{$table}`");
                } catch (\Throwable $e) {
                    // ignore: down() is best-effort
                }
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
              WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        );
        return ($rows[0]->c ?? 0) > 0;
    }

    private function info(string $msg): void
    {
        if (function_exists('app') && app()->runningInConsole()) {
            fwrite(STDOUT, "  [v5_speed] {$msg}\n");
        }
    }
};
