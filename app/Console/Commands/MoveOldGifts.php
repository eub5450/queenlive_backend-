<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gift;
use App\Models\OldGift;
use App\Models\FuritsPotsBackup;
use App\Models\DayTime;
use App\Models\Comment ;
use App\Models\Kick ;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoveOldGifts extends Command
{
    protected $signature = 'gifts:archive';
    protected $description = 'Move old gifts to old_gifts table and aggregate game backups (keep current & previous month)';

    public function handle()
    {
        try {
            Log::info('Archive process started');

            $this->archiveOldGifts();

            $this->aggregateGameBackups();

            if ($this->isFirstDayOfMonth()) {
                $this->cleanupDayTime();
                Log::info('Monthly cleanup jobs completed', ['date' => now()->format('Y-m-d')]);
            } else {
                Log::info('Skipped monthly cleanup jobs', [
                    'date' => now()->format('Y-m-d'),
                    'reason' => 'Not first day of month'
                ]);
            }

            Log::info('Archive process completed successfully');

        } catch (\Exception $e) {
            Log::error('Archive process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }

        return 0;
    }

    private function isFirstDayOfMonth(): bool
    {
        return now()->day === 1;
    }

   private function archiveOldGifts(): void
    {
        // Cutoff date: সব মাসের আগের মাসের data
        $cutoffDate = now()->startOfMonth()->subMonth()->startOfMonth();

        Log::info('Gift archive cutoff date', ['cutoff' => $cutoffDate]);

        Gift::where('date', '<', $cutoffDate)
            ->orderBy('id')
            ->chunkById(1000, function ($gifts) {

                $data = [];

                foreach ($gifts as $gift) {
                    $data[] = [
                        'sander_id'    => $gift->sander_id,
                        'reciever_id'  => $gift->reciever_id,
                        'name'         => $gift->name,
                        'value'        => $gift->value,
                        'date'         => $gift->date,
                        'channelName'  => $gift->channelName,
                        'reaward_time' => $gift->reaward_time,
                        'created_at'   => $gift->date,
                        'updated_at'   => now(),
                    ];
                }

                // Batch insert to OldGift table
                foreach (array_chunk($data, 200) as $batch) {
                    OldGift::insert($batch);
                }

                // Delete original Gift rows
                Gift::whereIn('id', $gifts->pluck('id'))->delete();

                // Small pause to avoid DB spike
                usleep(200000); // 0.2 second
            });

        Log::info('Gift archiving completed');
    }

    
    private function aggregateGameBackups(): void
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
    
            DB::beginTransaction();
    
            $skippedAfter = now()->subDays(2)->format('Y-m-d 23:59:59');
    
            $totalRows = DB::table('furits_pots_backups')
                ->where('date', '<=', $skippedAfter)
                ->count();
    
            if ($totalRows === 0) {
                DB::commit();
                Log::info('No game backups to aggregate');
                return;
            }
    
            $processedRows = 0;
    
            $allAggregated = []; // temporary array, per-chunk batch insert
    
            // 1️⃣ Aggregate chunk by chunk
            DB::table('furits_pots_backups')
                ->where('date', '<=', $skippedAfter)
                ->orderBy('id')
                ->chunkById(1000, function ($rows) use (&$allAggregated) {
    
                    $grouped = $rows->groupBy(function ($item) {
                        return $item->user_id . '|' . $item->game_name . '|' . $item->status . '|' . $item->pot_no;
                    });
    
                    foreach ($grouped as $group) {
                        $first = $group->first();
                        $totalAmount = $group->sum('amount');
                        $totalServe = $group->sum('serve_balance');
    
                        $allAggregated[] = [
                            'tray_id'       => 'archive_' . now()->format('YmdHis'),
                            'user_id'       => $first->user_id,
                            'game_name'     => $first->game_name,
                            'status'        => $first->status,
                            'pot_no'        => $first->pot_no,
                            'amount'        => $totalAmount,
                            'serve_balance' => $totalServe,
                            'total_row_sum' => count($group),
                            'date'          => now()->format('Y-m-d'),
                        ];
                    }
                });
    
            // 2️⃣ Delete original rows safely AFTER aggregation
            do {
                $deleted = DB::table('furits_pots_backups')
                    ->where('date', '<=', $skippedAfter)
                    ->limit(1000)
                    ->delete();
            } while ($deleted > 0);
    
            // 3️⃣ Insert aggregated data in batches
            foreach (array_chunk($allAggregated, 1000) as $chunk) {
                DB::table('furits_pots_backups')->insert($chunk);
                $processedRows += count($chunk);
            }
    
            DB::commit();
    
            Log::info('Game backups aggregation completed', [
                'total_rows' => $totalRows,
                'processed_rows' => $processedRows,
                'skipped_after' => $skippedAfter
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Game backups aggregation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function cleanupDayTime(): void
    {
        try {
            DayTime::truncate();
            FuritsPotsBackup::truncate();
            Comment::truncate();
            Kick::truncate();

            Log::info('Tables cleaned up', [
                'execution_date' => now()->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            Log::error('Cleanup failed', [
                'error' => $e->getMessage(),
                'date' => now()->format('Y-m-d')
            ]);

            throw $e;
        }
    }
}