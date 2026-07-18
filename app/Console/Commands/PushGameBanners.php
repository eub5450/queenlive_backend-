<?php
// app/Console/Commands/PushGameBanners.php

namespace App\Console\Commands;

use App\Models\GameBannner;
use App\Helpers\BannerService;
use App\Helpers\JsonLogger;
use Illuminate\Console\Command;

class PushGameBanners extends Command
{
    protected $signature = 'banners:push';
    protected $description = 'Push game banners via WebSocket';
    
    private $bannerService;
    private $logger;
    
    public function __construct(BannerService $bannerService)
    {
        parent::__construct();
        $this->bannerService = $bannerService;
        $this->logger = new JsonLogger(['name' => 'game_banner_cron_job']);
    }

    public function handle()
    {
        $this->info('Starting banner push...'.date('H:i:s'));
        
        try {
            $redisCount = $this->bannerService->getBannerCount();
            
            if ($redisCount == 0) {
                $dbCount = GameBannner::count();
                
                if ($dbCount > 0) {
                    $synced = $this->bannerService->syncBannersToRedis();
                    $this->logger->table('Redis Store', ['Time', 'Action', 'Count'])
                        ->add([
                            'time' => date('H:i:s'),
                            'action' => 'DB→Redis',
                            'count' => $synced
                        ]);
                    $redisCount = $synced;
                }
            }
            
            $result = $this->bannerService->getNextBanners(10);
            $banners = $result['banners'];
            
            if (empty($banners)) {
                return;
            }
            
            $success = 0;
            $fail = 0;
            
            foreach ($banners as $index => $banner) {
                $payload = [
                    'message' => $banner['message'] ?? '',
                    'name'    => $banner['name'] ?? '',
                    'game'    => $banner['game'] ?? '',
                    'level'   => $banner['level'] ?? '',
                ];

                try {
                    $this->broadcastV5($payload);
                    $success++;
                } catch (\Exception $e) {
                    $fail++;
                    $this->logger->addError([
                        'subject' => 'WS FAILED',
                        'code' => 'WS' . str_pad($index + 1, 3, '0'),
                        'message' => $e->getMessage(),
                        'location' => __FILE__ . ':' . __LINE__
                    ]);
                }

                if ($index < count($banners) - 1) {
                    sleep(6);
                }
            }

            try {
                \Illuminate\Support\Facades\Redis::incr('queenlive:banners:version');
            } catch (\Throwable $t) { /* best-effort */ }
            
            $this->logger->table('Banner Pushes', ['Time', 'Success', 'Fail', 'Remaining'])
                ->add([
                    'time' => date('H:i:s'),
                    'success' => $success,
                    'fail' => $fail,
                    'remaining' => $this->bannerService->getBannerCount()
                ]);
            
            $this->info("Pushed: {$success} success, {$fail} fail {date('H:i:s')}");
            
        } catch (\Exception $e) {
            $this->logger->addError([
                'subject' => 'CRITICAL',
                'code' => 'CRIT001',
                'message' => $e->getMessage(),
                'location' => $e->getFile() . ':' . $e->getLine()
            ]);
            $this->error($e->getMessage());
        }
    }
    
    private function broadcastV5(array $payload)
    {
        // Fan out the global game banner to EVERY live audio + multi room's own
        // private channel (was one dummy channel no room subscribes to).
        $svc = app(\App\Services\V5\RoomBroadcastService::class);
        $rooms = \App\Models\UserLive::whereIn('type', [1, 2, 3])->get(['channelName', 'type']); // 2026-07-03: include VIDEO (type 2) rooms
        foreach ($rooms as $room) {
            $ch = (string) $room->channelName;
            if ($ch === '') { continue; }
            $roomType = ((int) $room->type === 3) ? 'multi' : (((int) $room->type === 2) ? 'video' : 'audio');
            try {
                $svc->broadcast($roomType, $ch, '0', 'game.banner.updated', $payload, ['actor_user_id' => null]);
            } catch (\Throwable $th) { /* best-effort per room */ }
        }
        return true;
    }
}
