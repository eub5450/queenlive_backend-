<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * V5 hot-read indexes for live_calls.
 *
 * Notes:
 *  - (host_id, channelName, status) already exists as
 *    bdlive_lc_host_channel_status_idx — skipped here to avoid a redundant
 *    duplicate index.
 *  - (co_host_id, channelName) is NEW — JoinStore stale-row scan uses these
 *    two cols without host_id, so the existing 4-col composite cannot serve it
 *    (left-most prefix rule).
 *  - (channelName, mute_updated_at) is added ONLY if Agent C has landed the
 *    mute_updated_at column. Guarded so this migration is safe to run before
 *    or after Agent C.
 */
return new class extends Migration
{
    public function up()
    {
        // Index 1 — co_host_id stale-row scan.
        if (!$this->indexExists('live_calls', 'bdlive_lc_cohost_channel_v5_idx')) {
            Schema::table('live_calls', function (Blueprint $table) {
                $table->index(['co_host_id', 'channelName'], 'bdlive_lc_cohost_channel_v5_idx');
            });
        }

        // Index 2 — mute desync scan (gated on Agent C's column).
        if (Schema::hasColumn('live_calls', 'mute_updated_at')) {
            if (!$this->indexExists('live_calls', 'bdlive_lc_channel_mute_updated_v5_idx')) {
                Schema::table('live_calls', function (Blueprint $table) {
                    $table->index(['channelName', 'mute_updated_at'], 'bdlive_lc_channel_mute_updated_v5_idx');
                });
            }
        }
    }

    public function down()
    {
        if ($this->indexExists('live_calls', 'bdlive_lc_cohost_channel_v5_idx')) {
            Schema::table('live_calls', function (Blueprint $table) {
                $table->dropIndex('bdlive_lc_cohost_channel_v5_idx');
            });
        }
        if ($this->indexExists('live_calls', 'bdlive_lc_channel_mute_updated_v5_idx')) {
            Schema::table('live_calls', function (Blueprint $table) {
                $table->dropIndex('bdlive_lc_channel_mute_updated_v5_idx');
            });
        }
    }

    private function indexExists($table, $index)
    {
        try {
            $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return !empty($rows);
        } catch (\Throwable $e) {
            return false;
        }
    }
};
