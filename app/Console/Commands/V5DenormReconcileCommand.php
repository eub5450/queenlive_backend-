<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Sample 1000 random users and compare `users.total_gifts_received_value`
 * against `SUM(gifts.value) WHERE reciever_id = u.id`.
 *
 *   php artisan v5:denorm-reconcile
 *
 * Logs every divergence > 0 to storage/logs/v5_denorm_reconcile.log.
 * Exit code 0 = clean, 1 = any divergence found.
 *
 * Scheduled hourlyAt(15) — see App\Console\Kernel::schedule().
 *
 * Agent M / 2026-06-28.
 */
class V5DenormReconcileCommand extends Command
{
    protected $signature = 'v5:denorm-reconcile {--sample=1000}';
    protected $description = 'V5 denorm sanity: sample users and compare gifts SUM vs counter.';

    public function handle(): int
    {
        $t0 = microtime(true);

        // Soft-skip until the denorm migration has landed. Required so the
        // hourly cron does not spam errors on a node whose schema is still
        // pre-migration. Returns 0 (SUCCESS) so scheduler treats it as clean.
        if (!Schema::hasColumn('users', 'total_gifts_received_value')) {
            $this->info('[v5_denorm_reconcile] users.total_gifts_received_value not present yet; skipping.');
            return self::SUCCESS;
        }

        $sampleSize = (int) $this->option('sample');
        if ($sampleSize <= 0) {
            $sampleSize = 1000;
        }

        // RAND() ORDER on users(65k) is OK at this size; if users grow huge
        // we should switch to id-range sampling.
        $sample = DB::table('users')
            ->inRandomOrder()
            ->limit($sampleSize)
            ->select('id', 'total_gifts_received_value', 'total_gifts_sent_value')
            ->get();

        $diverged = [];
        foreach ($sample as $u) {
            $id = (string) $u->id;
            $dbSumReceived = (int) DB::table('gifts')->where('reciever_id', $id)->sum('value');
            $dbSumSent     = (int) DB::table('gifts')->where('sander_id', $id)->sum('value');
            $denormReceived = (int) $u->total_gifts_received_value;
            $denormSent     = (int) $u->total_gifts_sent_value;

            $rDiff = abs($dbSumReceived - $denormReceived);
            $sDiff = abs($dbSumSent - $denormSent);
            if ($rDiff > 0 || $sDiff > 0) {
                $diverged[] = [
                    'user_id'         => $id,
                    'db_sum_received' => $dbSumReceived,
                    'denorm_received' => $denormReceived,
                    'diff_received'   => $dbSumReceived - $denormReceived,
                    'db_sum_sent'     => $dbSumSent,
                    'denorm_sent'     => $denormSent,
                    'diff_sent'       => $dbSumSent - $denormSent,
                ];
            }
        }

        $elapsed = round(microtime(true) - $t0, 2);

        $logLine = sprintf(
            "[v5_denorm_reconcile] sample=%d diverged=%d elapsed=%ss",
            $sample->count(),
            count($diverged),
            $elapsed
        );

        // Log channel: default file appender goes through the standard
        // logging stack; force a stand-alone file so the seeder/cron line
        // is easy to grep regardless of LOG_CHANNEL config.
        $logFile = storage_path('logs/v5_denorm_reconcile.log');
        @file_put_contents($logFile, date('Y-m-d H:i:s') . ' ' . $logLine . PHP_EOL, FILE_APPEND);

        foreach ($diverged as $d) {
            $line = sprintf(
                'DIVERGE user_id=%s db_received=%d denorm_received=%d (diff=%d) db_sent=%d denorm_sent=%d (diff=%d)',
                $d['user_id'], $d['db_sum_received'], $d['denorm_received'], $d['diff_received'],
                $d['db_sum_sent'], $d['denorm_sent'], $d['diff_sent']
            );
            @file_put_contents($logFile, date('Y-m-d H:i:s') . ' ' . $line . PHP_EOL, FILE_APPEND);
        }

        if (count($diverged) > 0) {
            $this->error($logLine);
            $this->error('First 10 diverged ids: ' . implode(',', array_slice(array_column($diverged, 'user_id'), 0, 10)));
            return self::FAILURE;
        }

        $this->info($logLine);
        return self::SUCCESS;
    }
}
