<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 2026-07-03: replaces the inline sleep(12) in CoinBegController::Claim.
 * Every coin-bag opener pinned a PHP-FPM worker for 12+ seconds; under room
 * load this exhausted the pool and stalled the whole API. The 12s
 * "collect clicks then distribute" game delay now runs on the queue with the
 * same click==0 single-run guard.
 */
class CoinBegResultJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $userId;
    public $begId;
    public $channelName;
    public $apiVersion;

    public $tries = 1;
    public $timeout = 30;

    public function __construct($userId, $begId, $channelName, $apiVersion = 'V5')
    {
        $this->userId = $userId;
        $this->begId = $begId;
        $this->channelName = $channelName;
        $this->apiVersion = $apiVersion;
        $this->onQueue('default');
    }

    public function handle()
    {
        $coinBeg = \App\Models\CoinBeg::find($this->begId);
        if (!$coinBeg || $coinBeg->click != 0) {
            return; // already distributed by an earlier opener's job
        }

        $class = $this->apiVersion === 'V4'
            ? \App\Http\Controllers\Api\V4\CoinBegController::class
            : \App\Http\Controllers\Api\V5\CoinBegController::class;

        app($class)->CoinBegResult($this->userId, $this->begId, $this->channelName);
    }
}
