<?php

namespace App\Console\Commands;

use App\Services\FanClubService;
use App\Services\V5\RoomBroadcastService;
use Illuminate\Console\Command;

/**
 * Cron: sweeps Fan Club rows past their expiry.
 * schedule:run every 5 minutes (Kernel wiring is done inline where the
 * boss keeps other schedules). Boss 2026-07-07.
 */
class SweepFanClub extends Command
{
    protected $signature = 'fanclub:sweep-expirations';
    protected $description = 'Move Fan Club subscriptions past expiry: active->grace, grace->expired';

    public function handle(): int
    {
        $svc = new FanClubService(app(RoomBroadcastService::class));
        $out = $svc->sweepExpirations();
        $this->info(json_encode($out));
        return self::SUCCESS;
    }
}
