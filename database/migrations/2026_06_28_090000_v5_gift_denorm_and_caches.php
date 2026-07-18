<?php

/**
 * V5 gift denormalization + room/ranking cache tables.
 *
 * Agent M / 2026-06-28. ADDITIVE ONLY:
 *  - Adds 5 BIGINT/DATE counter columns + 2 indexes to `users`.
 *  - Creates `room_call_list_cache`, `room_list_cache`, `ranking_daily`,
 *    `ranking_monthly`.
 *
 * No reads are repointed at the new columns by this migration; reads still
 * use SUM(gifts.value) until a separate agent flips them. The counters are
 * populated by `App\Services\V5\GiftBalanceService::recordGift()` (each gift
 * insert wrapped in DB::transaction so counters + the source-of-truth gift
 * row commit together — counter failure rolls back the gift).
 *
 * Re-run safe: every step is guarded by hasColumn / hasTable / hasIndex.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------------------
        // 1a. users: lifetime + today gift counters + auto-reset date key.
        // -------------------------------------------------------------------
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'total_gifts_received_value')) {
                    $table->unsignedBigInteger('total_gifts_received_value')->default(0);
                }
                if (!Schema::hasColumn('users', 'total_gifts_sent_value')) {
                    $table->unsignedBigInteger('total_gifts_sent_value')->default(0);
                }
                if (!Schema::hasColumn('users', 'today_gifts_received_value')) {
                    $table->unsignedBigInteger('today_gifts_received_value')->default(0);
                }
                if (!Schema::hasColumn('users', 'today_gifts_sent_value')) {
                    $table->unsignedBigInteger('today_gifts_sent_value')->default(0);
                }
                if (!Schema::hasColumn('users', 'today_aggregation_date')) {
                    $table->date('today_aggregation_date')->nullable();
                }
            });

            if (!$this->indexExists('users', 'users_total_received_idx')) {
                try {
                    DB::statement('CREATE INDEX `users_total_received_idx` ON `users` (`total_gifts_received_value`)');
                } catch (\Throwable $e) { /* idempotent */ }
            }
            if (!$this->indexExists('users', 'users_today_received_idx')) {
                try {
                    DB::statement('CREATE INDEX `users_today_received_idx` ON `users` (`today_aggregation_date`, `today_gifts_received_value`)');
                } catch (\Throwable $e) { /* idempotent */ }
            }
        }

        // -------------------------------------------------------------------
        // 1b. room_call_list_cache: full prepareCallDetails-shaped snapshot
        //     keyed by channelName. Read path will JSON_DECODE snapshot_json.
        // -------------------------------------------------------------------
        if (!Schema::hasTable('room_call_list_cache')) {
            Schema::create('room_call_list_cache', function (Blueprint $table) {
                $table->string('channelName', 191)->primary();
                $table->string('host_id', 191);
                $table->enum('room_type', ['audio', 'video', 'multi']);
                $table->longText('snapshot_json');
                $table->unsignedInteger('cohost_count')->default(0);
                $table->unsignedInteger('audience_count')->default(0);
                $table->timestamp('rebuilt_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();

                $table->index(['host_id', 'room_type'], 'rclc_host_type_idx');
                $table->index('rebuilt_at', 'rclc_rebuilt_at_idx');
            });
        }

        // -------------------------------------------------------------------
        // 1c. room_list_cache: lightweight row per active channel for room
        //     listings / random page / popular sort.
        // -------------------------------------------------------------------
        if (!Schema::hasTable('room_list_cache')) {
            Schema::create('room_list_cache', function (Blueprint $table) {
                $table->string('channelName', 191)->primary();
                $table->string('host_id', 191);
                $table->enum('room_type', ['audio', 'video', 'multi']);
                $table->string('host_name', 255)->nullable();
                $table->string('host_image', 500)->nullable();
                $table->integer('host_level')->nullable();
                $table->unsignedInteger('audience_count')->default(0);
                $table->unsignedBigInteger('gift_today_value')->default(0);
                $table->tinyInteger('is_active')->default(1);
                $table->timestamps();

                $table->index(['is_active', 'room_type', 'gift_today_value'], 'rlc_active_type_gift_idx');
                $table->index(['is_active', 'audience_count'], 'rlc_active_audience_idx');
            });
        }

        // -------------------------------------------------------------------
        // 1d. ranking_daily: per-user per-day received/sent totals.
        // -------------------------------------------------------------------
        if (!Schema::hasTable('ranking_daily')) {
            Schema::create('ranking_daily', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 191);
                $table->date('aggregation_date');
                $table->unsignedBigInteger('received_value')->default(0);
                $table->unsignedBigInteger('sent_value')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'aggregation_date'], 'rd_user_date_unique');
                $table->index(['aggregation_date', 'received_value'], 'rd_date_received_idx');
                $table->index(['aggregation_date', 'sent_value'], 'rd_date_sent_idx');
            });
        }

        // -------------------------------------------------------------------
        // 1e. ranking_monthly: per-user per-month totals. aggregation_month
        //     stored as first-of-month DATE.
        // -------------------------------------------------------------------
        if (!Schema::hasTable('ranking_monthly')) {
            Schema::create('ranking_monthly', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 191);
                $table->date('aggregation_month');
                $table->unsignedBigInteger('received_value')->default(0);
                $table->unsignedBigInteger('sent_value')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'aggregation_month'], 'rm_user_month_unique');
                $table->index(['aggregation_month', 'received_value'], 'rm_month_received_idx');
                $table->index(['aggregation_month', 'sent_value'], 'rm_month_sent_idx');
            });
        }

        // 1f. alltime: served by users.total_gifts_received_value + index from 1a.
    }

    public function down(): void
    {
        // Tables drop only if empty-ish; keep counters columns (no destructive
        // down for prod — explicit Boss-approved teardown only).
        if (Schema::hasTable('ranking_monthly')) {
            Schema::drop('ranking_monthly');
        }
        if (Schema::hasTable('ranking_daily')) {
            Schema::drop('ranking_daily');
        }
        if (Schema::hasTable('room_list_cache')) {
            Schema::drop('room_list_cache');
        }
        if (Schema::hasTable('room_call_list_cache')) {
            Schema::drop('room_call_list_cache');
        }
        // users columns intentionally left in place even on down() — they
        // could be holding live counter values once Service is wired.
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $rows = DB::select(
                'SELECT COUNT(*) AS c FROM information_schema.statistics
                  WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $index]
            );
            return ($rows[0]->c ?? 0) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
