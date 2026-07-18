<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * One-shot backfill of the V5 denorm counters from the source `gifts` table.
 *
 *   php artisan db:seed --class=V5BackfillDenormSeeder
 *
 * Idempotent: re-running just RE-WRITES the snapshot from current `gifts`.
 * Safe to run on a live database — every step is a pure SUM/GROUP BY read
 * followed by either an UPDATE-from-subquery or an INSERT IGNORE.
 *
 * Agent M / 2026-06-28.
 */
class V5BackfillDenormSeeder extends Seeder
{
    public function run(): void
    {
        $t0 = microtime(true);
        $this->command->info('[V5Backfill] starting');

        // -------------------------------------------------------------------
        // 1. users.total_gifts_received_value
        // -------------------------------------------------------------------
        $t = microtime(true);
        $rows = DB::update("
            UPDATE users u
            JOIN (
                SELECT reciever_id, SUM(value) AS s
                FROM gifts
                WHERE reciever_id IS NOT NULL
                GROUP BY reciever_id
            ) g ON CAST(u.id AS CHAR) = g.reciever_id
            SET u.total_gifts_received_value = g.s
        ");
        $this->command->info("[V5Backfill] users.total_received updated rows={$rows} t=" . round(microtime(true) - $t, 2) . 's');

        // 2. users.total_gifts_sent_value
        $t = microtime(true);
        $rows = DB::update("
            UPDATE users u
            JOIN (
                SELECT sander_id, SUM(value) AS s
                FROM gifts
                WHERE sander_id IS NOT NULL
                GROUP BY sander_id
            ) g ON CAST(u.id AS CHAR) = g.sander_id
            SET u.total_gifts_sent_value = g.s
        ");
        $this->command->info("[V5Backfill] users.total_sent updated rows={$rows} t=" . round(microtime(true) - $t, 2) . 's');

        // 3. users.today_*  (where date = CURDATE())
        $t = microtime(true);
        $rows = DB::update("
            UPDATE users u
            JOIN (
                SELECT reciever_id, SUM(value) AS s
                FROM gifts
                WHERE reciever_id IS NOT NULL AND DATE(date) = CURDATE()
                GROUP BY reciever_id
            ) g ON CAST(u.id AS CHAR) = g.reciever_id
            SET u.today_gifts_received_value = g.s,
                u.today_aggregation_date = CURDATE()
        ");
        $this->command->info("[V5Backfill] users.today_received updated rows={$rows} t=" . round(microtime(true) - $t, 2) . 's');

        $t = microtime(true);
        $rows = DB::update("
            UPDATE users u
            JOIN (
                SELECT sander_id, SUM(value) AS s
                FROM gifts
                WHERE sander_id IS NOT NULL AND DATE(date) = CURDATE()
                GROUP BY sander_id
            ) g ON CAST(u.id AS CHAR) = g.sander_id
            SET u.today_gifts_sent_value = g.s,
                u.today_aggregation_date = CURDATE()
        ");
        $this->command->info("[V5Backfill] users.today_sent updated rows={$rows} t=" . round(microtime(true) - $t, 2) . 's');

        // -------------------------------------------------------------------
        // 4. ranking_daily — last 90 days, both received and sent in one pass
        //    per side. INSERT IGNORE then UPDATE so re-runs reflect current
        //    SUMs.
        // -------------------------------------------------------------------
        $t = microtime(true);

        // 4a. seed receiver rows
        $rows = DB::statement("
            INSERT IGNORE INTO ranking_daily (user_id, aggregation_date, received_value, sent_value, created_at, updated_at)
            SELECT reciever_id, DATE(date) AS d, SUM(value), 0, NOW(), NOW()
            FROM gifts
            WHERE reciever_id IS NOT NULL
              AND DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY reciever_id, d
        ");
        // 4b. seed sender rows (will UNIQUE-collide if same user_id+date already exists)
        DB::statement("
            INSERT IGNORE INTO ranking_daily (user_id, aggregation_date, received_value, sent_value, created_at, updated_at)
            SELECT sander_id, DATE(date) AS d, 0, SUM(value), NOW(), NOW()
            FROM gifts
            WHERE sander_id IS NOT NULL
              AND DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY sander_id, d
        ");
        // 4c. now UPDATE both sides from the real SUM so re-runs converge.
        DB::statement("
            UPDATE ranking_daily rd
            JOIN (
                SELECT reciever_id AS uid, DATE(date) AS d, SUM(value) AS s
                FROM gifts
                WHERE reciever_id IS NOT NULL
                  AND DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                GROUP BY reciever_id, d
            ) g ON g.uid = rd.user_id AND g.d = rd.aggregation_date
            SET rd.received_value = g.s
        ");
        DB::statement("
            UPDATE ranking_daily rd
            JOIN (
                SELECT sander_id AS uid, DATE(date) AS d, SUM(value) AS s
                FROM gifts
                WHERE sander_id IS NOT NULL
                  AND DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                GROUP BY sander_id, d
            ) g ON g.uid = rd.user_id AND g.d = rd.aggregation_date
            SET rd.sent_value = g.s
        ");
        $dailyCount = DB::table('ranking_daily')->count();
        $this->command->info("[V5Backfill] ranking_daily rows={$dailyCount} t=" . round(microtime(true) - $t, 2) . 's');

        // -------------------------------------------------------------------
        // 5. ranking_monthly — ALL months. Same shape as daily.
        // -------------------------------------------------------------------
        $t = microtime(true);
        DB::statement("
            INSERT IGNORE INTO ranking_monthly (user_id, aggregation_month, received_value, sent_value, created_at, updated_at)
            SELECT reciever_id,
                   DATE_FORMAT(date, '%Y-%m-01') AS m,
                   SUM(value), 0, NOW(), NOW()
            FROM gifts
            WHERE reciever_id IS NOT NULL
            GROUP BY reciever_id, m
        ");
        DB::statement("
            INSERT IGNORE INTO ranking_monthly (user_id, aggregation_month, received_value, sent_value, created_at, updated_at)
            SELECT sander_id,
                   DATE_FORMAT(date, '%Y-%m-01') AS m,
                   0, SUM(value), NOW(), NOW()
            FROM gifts
            WHERE sander_id IS NOT NULL
            GROUP BY sander_id, m
        ");
        DB::statement("
            UPDATE ranking_monthly rm
            JOIN (
                SELECT reciever_id AS uid, DATE_FORMAT(date, '%Y-%m-01') AS m, SUM(value) AS s
                FROM gifts
                WHERE reciever_id IS NOT NULL
                GROUP BY reciever_id, m
            ) g ON g.uid = rm.user_id AND g.m = rm.aggregation_month
            SET rm.received_value = g.s
        ");
        DB::statement("
            UPDATE ranking_monthly rm
            JOIN (
                SELECT sander_id AS uid, DATE_FORMAT(date, '%Y-%m-01') AS m, SUM(value) AS s
                FROM gifts
                WHERE sander_id IS NOT NULL
                GROUP BY sander_id, m
            ) g ON g.uid = rm.user_id AND g.m = rm.aggregation_month
            SET rm.sent_value = g.s
        ");
        $monthlyCount = DB::table('ranking_monthly')->count();
        $this->command->info("[V5Backfill] ranking_monthly rows={$monthlyCount} t=" . round(microtime(true) - $t, 2) . 's');

        $this->command->info('[V5Backfill] DONE total=' . round(microtime(true) - $t0, 2) . 's');
    }
}
