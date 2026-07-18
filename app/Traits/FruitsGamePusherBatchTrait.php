<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait FruitsGamePusherBatchTrait
{
    protected function addToPusherBatch($boardName, $amount)
    {
        // Cache key for storing pending updates
        $cacheKey = 'friuts_game_pusher_batch_updates';
        
        // Get existing updates from cache
        $pendingUpdates = Cache::get($cacheKey, []);
        
        // Add new update to the batch
        $pendingUpdates[] = [
            'board_name' => $boardName,
            'amount' => $amount,
            'timestamp' => time()
        ];
        
        // Store back to cache (expires in 5 seconds as safety)
        Cache::put($cacheKey, $pendingUpdates, now()->addSeconds(5));
        
        // Check if we need to process the batch
        $this->checkAndProcessBatch();
    }
    
    protected function checkAndProcessBatch()
    {
        $cacheKey = 'friuts_game_pusher_batch_updates';
        $lastProcessTime = Cache::get('friuts_game_pusher_last_process_time', 0);
        $currentTime = time();
        
        // Get pending updates
        $pendingUpdates = Cache::get($cacheKey, []);
        
        // Process if:
        // 1. There are updates AND (2 seconds passed OR more than 20 updates)
        if (!empty($pendingUpdates) && 
            ($currentTime - $lastProcessTime >= 2 || count($pendingUpdates) >= 20)) {
            
            $this->processPusherBatch();
        }
    }
    
    protected function processPusherBatch()
    {
        $cacheKey = 'friuts_game_pusher_batch_updates';
        
        // Get and clear pending updates atomically
        $pendingUpdates = Cache::get($cacheKey, []);
        
        if (empty($pendingUpdates)) {
            return true;
        }
        
        // Clear the cache
        Cache::forget($cacheKey);
        
        // Update last process time
        Cache::put('friuts_game_pusher_last_process_time', time(), now()->addSeconds(10));
        
        // Group updates by board to consolidate amounts
        $consolidatedData = [];
        foreach ($pendingUpdates as $update) {
            $board = $update['board_name'];
            if (!isset($consolidatedData[$board])) {
                $consolidatedData[$board] = 0;
            }
            $consolidatedData[$board] += $update['amount'];
        }
        
        // Send consolidated Pusher updates
        return $this->sendConsolidatedPusherUpdates($consolidatedData);
    }
    
    protected function sendConsolidatedPusherUpdates($consolidatedData)
    {
        try {
            $fortunesetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();
            
            $options = [
                'cluster' => $fortunesetting->cluster_pusher,
                'useTLS'  => true,
            ];
            
            $pusher = new \Pusher\Pusher(
                $fortunesetting->key_pusher,
                $fortunesetting->secret_pusher,
                $fortunesetting->app_id_pusher,
                $options
            );
            
            // Send each consolidated update
            foreach ($consolidatedData as $boardName => $totalAmount) {
                $pusher->trigger('users_amount_name', 'users_amount_event', [
                    'bord_name' => $boardName, 
                    'bord_amount' => $totalAmount
                ]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Pusher batch error: ' . $e->getMessage());
            
            // Try to switch Pusher account if needed
            $fortunesetting = FortuneSetting::first();
            $switched = $this->switchPusherAccount($fortunesetting->pusher_id);
            
            if ($switched) {
                $newSetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();
                
                $newOptions = [
                    'cluster' => $newSetting->cluster_pusher,
                    'useTLS'  => true,
                ];
                
                $newPusher = new \Pusher\Pusher(
                    $newSetting->key_pusher,
                    $newSetting->secret_pusher,
                    $newSetting->app_id_pusher,
                    $newOptions
                );
                
                foreach ($consolidatedData as $boardName => $totalAmount) {
                    $newPusher->trigger('users_amount_name', 'users_amount_event', [
                        'bord_name' => $boardName, 
                        'bord_amount' => $totalAmount
                    ]);
                }
            }
            
            return false;
        }
    }
}