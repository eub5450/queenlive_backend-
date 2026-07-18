<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
trait CacheClearTrait
{
    private $prefix = 'queenlive:';
    protected function clearJustVideoCall($host_id, $channelName)
    {  
        $today = now()->format('Y-m-d');
        $start_date = now()->startOfMonth()->toDateString();
        $end_date = now()->endOfMonth()->toDateString();
        
        Redis::del($this->prefix . "gift_range:{$host_id}:{$start_date}:{$end_date}");
        Redis::del($this->prefix . "today_gift:{$host_id}:{$today}");
        Redis::del($this->prefix . "sander_total:{$host_id}");
        Redis::del($this->prefix . "channel_gift:{$host_id}:{$channelName}");
        Redis::del($this->prefix . "withdraw_range:{$host_id}:{$start_date}:{$end_date}");
        
        Redis::del($this->prefix . "Video_Brd_Call_Details_{$host_id}_{$channelName}");
     
       
    }
    
    protected function clearVideoCallAndLists($host_id, $channelName)
    {
        $this->clearJustVideoCall($host_id, $channelName);
       
    }
    
    protected function clearVideoCallAndHome($host_id, $channelName)
    {
        $this->clearJustVideoCall($host_id, $channelName);
        // Clear home page caches
        Redis::del($this->prefix . "live_users_type_1");
        Redis::del($this->prefix . "live_frined_home");
        Redis::del($this->prefix . "live_top_list");
        for ($i = 1; $i <= 10; $i++) {
            Redis::del($this->prefix . "live_list_page_{$i}");
            Redis::del($this->prefix . "live_list_v2_page_{$i}");
        }
    }
    
    protected function clearVideoCallAndStatus($host_id, $channelName, $co_host_id)
    {
        $this->clearJustVideoCall($host_id, $channelName);
        Cache::forget("live_call_accept_count_{$host_id}_{$channelName}");
    }
    
    protected function clearAllVideoCachesWithGift($host_id, $channelName, $user_id)
    {
        $this->clearVideoCallAndLists($host_id, $channelName);
       
    }
    
    protected function clearJustHomeLists()
    {
        Redis::del($this->prefix . "live_users_type_1");
        Redis::del($this->prefix . "live_frined_home");
        Redis::del($this->prefix . "live_top_list");
        for ($i = 1; $i <= 10; $i++) {
            Redis::del($this->prefix . "live_list_page_{$i}");
            Redis::del($this->prefix . "live_list_v2_page_{$i}");
        }
        return true;
    }
}