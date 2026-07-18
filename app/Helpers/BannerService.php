<?php
// app/Helpers/BannerService.php

namespace App\Helpers;

use App\Models\GameBannner;
use Illuminate\Support\Facades\Redis;

class BannerService
{
    private $redisKey = 'queenlive:game_banners_list';
    private $redisIndex = 'queenlive:banner_push_index';
    
    public function syncBannersToRedis()
    {
        $banners = GameBannner::orderBy('id')->get();
        
        if ($banners->isEmpty()) {
            return 0;
        }
        
        $bannerArray = [];
        foreach ($banners as $banner) {
            $bannerArray[] = [
                'id' => $banner->id,
                'name' => $banner->name,
                'message' => $banner->message,
                'game' => $banner->game,
                'level' => $banner->level,
                'tray_id' => $banner->tray_id,
                'banner_color' => $banner->banner_color,
                'user_id' => $banner->user_id
            ];
        }
        
        Redis::set($this->redisKey, json_encode($bannerArray));
        Redis::set($this->redisIndex, 0);
        
        GameBannner::query()->delete();
        
        return count($bannerArray);
    }
    
    public function getBannerCount()
    {
        $bannersJson = Redis::get($this->redisKey);
        return $bannersJson ? count(json_decode($bannersJson, true)) : 0;
    }
    
    public function getNextBanners($count = 10)
    {
        $bannersJson = Redis::get($this->redisKey);
        
        if (!$bannersJson) {
            return ['banners' => [], 'total' => 0];
        }
        
        $allBanners = json_decode($bannersJson, true);
        $totalBanners = count($allBanners);
        
        if ($totalBanners == 0) {
            return ['banners' => [], 'total' => 0];
        }
        
        $startIndex = (int)Redis::get($this->redisIndex) ?: 0;
        
        $bannersToSend = [];
        for ($i = 0; $i < $count; $i++) {
            $index = ($startIndex + $i) % $totalBanners;
            $bannersToSend[] = $allBanners[$index];
        }
        
        $newIndex = ($startIndex + $count) % $totalBanners;
        Redis::set($this->redisIndex, $newIndex);
        
        return [
            'banners' => $bannersToSend,
            'total' => $totalBanners
        ];
    }
    
    public function removePushedBanners($count = 10)
    {
        $bannersJson = Redis::get($this->redisKey);
        
        if (!$bannersJson) {
            return 0;
        }
        
        $banners = json_decode($bannersJson, true);
        $remainingBanners = array_slice($banners, $count);
        
        Redis::set($this->redisKey, json_encode($remainingBanners));
        Redis::set($this->redisIndex, 0);
        
        return count($remainingBanners);
    }
    
    public function checkAndRefillFromDb()
    {
        $dbCount = GameBannner::count();
        
        if ($dbCount > 0) {
            return $this->syncBannersToRedis();
        }
        
        return 0;
    }
}