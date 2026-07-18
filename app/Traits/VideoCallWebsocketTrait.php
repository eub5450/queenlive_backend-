<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use RedisCacheFunction;
use App\Models\User;
use App\Models\Gift;
use App\Models\LiveCall;
use DB;

trait VideoCallWebsocketTrait
{
    /**
     * Message templates for different actions - SUCCESS only
     */
    private $actionMessages = [
        'kick'            => 'Video Call Mute Update',
        'accept'          => 'Video Call Accept Successfully',
        'mute'            => 'Video Call Mute Update',
        'store'           => 'Video Brd Store Successfully',
        'remove'          => 'Video Call Removed Successfully',
        'gift'            => 'Video Call Gift Successfully',
        'request'         => 'Video Call Request Successfully',
        'pending_remove'  => 'Call Request Removed Successfully',
        'host_remove'     => 'Video Call Removed By Host Successfully',
        'cohost_active'   => 'Co-Host Active Update',
        'user_data'       => 'User Data Load Successfully',
        'call_mute'       => 'Video Call Mute Update',
        'default'         => 'Video Call Update Successfully'
    ];

    /**
     * ONE FUNCTION to rule them all!
     * Just pass the action name, always returns success message
     */
    private function sendVideoCallUpdate($host_id, $channelName, $action = 'default')
    {
        // Get message for this action (always success)
        $messageText = $this->actionMessages[$action] ?? $this->actionMessages['default'];

        $live = RedisCacheFunction::getUserLive($host_id, $channelName);

        if (!$live) {
            return $this->getEmptyVideoCallResponse($channelName, $messageText);
        }

        $top_profile  = RedisCacheFunction::TopProfile($host_id);
        $total_reward = RedisCacheFunction::getTotalReward($host_id);
        $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

        $response = [[
            'message'               => $messageText,
            'host_list'             => $call_details['host_list'] ?? [],
            'co_host_list'          => $call_details['co_host_list'] ?? [],
            'host_balance'          => $call_details['host_balance'] ?? 0,
            'star'                  => $call_details['star'] ?? 0,
            'star_complete_parcent' => $call_details['star_complete_parcent'] ?? 0,
            'top_profile'           => $top_profile,
            'total_reward'          => $total_reward,
            'channelName'           => $channelName,
            'code'                  => '200'
        ]];

        $this->sendVideoCallWebsocket($channelName, $response);

        return $response;
    }

    /**
     * Empty response when live not found
     */
    private function getEmptyVideoCallResponse($channelName, $messageText)
    {
        return [[
            'message'               => $messageText,
            'host_list'             => [],
            'co_host_list'          => [],
            'host_balance'          => 0,
            'star'                  => 0,
            'star_complete_parcent' => 0,
            'top_profile'           => [],
            'total_reward'          => 0,
            'channelName'           => $channelName,
            'code'                  => '200'
        ]];
    }

    /**
     * Send video call websocket
     */
    private function sendVideoCallWebsocket($channelName, $response)
    {
        $websocket_call = [[
            'message'       => 'bd_video_call',
            'data'          => $response,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ]];

        self::Websoket($websocket_call);
    }
      
    /**
     * Send call request update
     */
    private function sendCallRequestUpdate($host_id, $channelName)
    {
        $call_list = LiveCall::join('users', 'users.id', 'live_calls.co_host_id')
            ->select(
                'users.name', 
                'users.profile', 
                'live_calls.channelName',
                'live_calls.co_host_id', 
                'live_calls.status', 
                'live_calls.set_no'
            )
            ->where('live_calls.host_id', $host_id)
            ->where('live_calls.channelName', $channelName)
            ->where('live_calls.status', 'pending')
            ->get();
        
        $websocket_call_request = [[
            'message'       => 'Video Call Request',
            'channelName'   => $channelName,
            'call_count'    => $call_list->count(),
            'data'          => $call_list,
            'code'          => '200',
            'channel_type'  => '13'
        ]];
        
        self::Websoket($websocket_call_request);
    }
    
    /**
     * Send kick notification
     */
     private function sendHostCallRemoveNotification($channelName, $response)
    {
        self::Websoket([[
            'message' => 'video_host_call_remove',
            'data' => $response,
            'channelName' => $channelName,
            'code' => '200',
            'channel_type' => '22'
        ]]);
    }
    private function sendKickNotification($channelName, $kick_user_id, $kick_by_name)
    {
        $kick_response = [[
            'message'       => 'bd_kick',
            'data'          => [[
                'message'       => 'Kick Successfully',
                'channelName'   => $channelName,
                'user_id'       => $kick_user_id,
                'user_by_kick'  => $kick_by_name,
                'code'          => '200'
            ]],
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '20'
        ]];
        
        self::Websoket($kick_response);
    }
    
    /**
     * Send global gift banner
     */
    private function sendGlobalGiftBanner($channelName, $message, $user)
    {
        $global_txt = [[
            'message' => $message,
            'image'   => $user->profile,
            'name'    => $user->name
        ]];
        
        $global_websocket = [[
            'message'       => 'bp_golbal_gift_banner',
            'channelName'   => $channelName,
            'data'          => $global_txt,
            'code'          => '200',
            'channel_type'  => '17'
        ]];
        
        self::Websoket($global_websocket);
    }
    
    /**
     * Send gift effect
     */
    private function sendGiftEffect($channelName, $gift_name, $receiverId, $sender_balance, $host_balance, $gift_type)
    {
        $gift_effect = [[
            'message'      => 'video gift',
            'channelName'  => $channelName,
            'data'         => [[
                'channelName'      => $channelName,
                'name'             => $gift_name,
                'gift_time'        => '5',
                'host_balance'     => (string)$host_balance,
                'music'            => '',
                'audience_balance' => (string)$sender_balance,
                'reciever_id'      => (string)$receiverId,
                'status'           => 'active',
                'gift_type'        => (string)$gift_type
            ]],
            'code'         => '200',
            'channel_type' => '24'
        ]];
        
        self::Websoket($gift_effect);
    }
    
    /**
     * Prepare call details with caching
     */
    private function prepareCallDetails($host_id, $channelName, $live)
    {
        $cacheKey = "Video_Brd_Call_Details_{$host_id}_{$channelName}";
        $ttl = 3;
        
        return Cache::remember($cacheKey, $ttl, function () use ($host_id, $channelName, $live) {
            $today = date('Y-m-d');
            $start_date = date('Y-m') . '-01';
            $end_date = date('Y-m') . '-31';
            
            $host_data = User::find($host_id);
            if (!$host_data) {
                return [
                    'host_list' => [],
                    'co_host_list' => [],
                    'host_balance' => 0,
                    'star' => 0,
                    'star_complete_parcent' => 0
                ];
            }
            
            $gift_values = Gift::where('channelName', $channelName)
                ->whereIn('reciever_id', function ($query) use ($host_id, $channelName) {
                    $query->select('id')
                        ->from('users')
                        ->where('id', $host_id);
                })
                ->orWhere('reciever_id', $host_id)
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->groupBy('reciever_id')
                ->pluck('total_value', 'reciever_id');
            
            $host_balance = $gift_values[$host_id] ?? 0;
            
            $host = [
                'channelName'       => $channelName,
                'profile'           => $host_data->profile,
                'is_vip'            => $host_data->is_vip,
                'balance'           => $host_balance,
                'co_host_name'      => $host_data->name,
                'set_no'            => "0",
                'mute'              => $live->mute ?? 0,
                'frame'             => (string)$host_data->frame,
                'co_host_id'        => (string)$host_data->id,
                'co_host_status'    => 'Accept',
                'super_mute'        => "0"
            ];
            
            $list = [$host];
            $co_host_list = [];
            
            $accept_list = DB::table('live_calls')
                ->where('host_id', $host_id)
                ->where('channelName', $channelName)
                ->where('status', 'Accept')
                ->get();
            
            $co_host_ids = $accept_list->pluck('co_host_id')->unique();
            $co_hosts = User::whereIn('id', $co_host_ids)->get()->keyBy('id');
            
            $co_host_gifts = Gift::where('channelName', $channelName)
                ->whereIn('reciever_id', $co_host_ids)
                ->groupBy('reciever_id')
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->pluck('total_value', 'reciever_id');
            
            foreach ($accept_list as $call) {
                $co_host = $co_hosts->get($call->co_host_id);
                if (!$co_host) continue;
                
                $co_host_balance = $co_host_gifts[$call->co_host_id] ?? 0;
                
                $co_host_data = [
                    'channelName'       => $channelName,
                    'profile'           => $co_host->profile,
                    'is_vip'            => $co_host->is_vip,
                    'balance'           => $co_host_balance,
                    'co_host_name'      => $co_host->name,
                    'set_no'            => "0",
                    'mute'              => $call->mute,
                    'frame'             => (string)$co_host->frame,
                    'co_host_id'        => (string)$call->co_host_id,
                    'co_host_status'    => (string)$call->is_co_host_active,
                    'super_mute'        => (string)$call->super_mute
                ];
                
                $list[] = $co_host_data;
                $co_host_list[] = $co_host_data;
            }
            
            $monthly_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('value');
            
            $total_gift_sum = ($host_data->previous_coin + $monthly_gift);
            
            $today_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
            
            // Calculate star level
            $levels = [
                [0, 50000, 1, 50000],
                [50000, 200000, 2, 200000],
                [200000, 500000, 3, 500000],
                [500000, 1000000, 4, 1000000],
                [1000000, 2000000, 5, 2000000],
                [2000000, PHP_INT_MAX, 5, 20000000]
            ];
            
            $star = 0;
            $next_level_amount = 1;
            foreach ($levels as $level) {
                if ($today_gift >= $level[0] && $today_gift < $level[1]) {
                    $star = $level[2];
                    $next_level_amount = $level[3];
                    break;
                }
            }
            
            $need_percent = intval(($today_gift / $next_level_amount) * 100);
            
            return [
                'host_list'              => $list,
                'co_host_list'           => $co_host_list,
                'host_balance'           => $total_gift_sum,
                'star'                   => $star,
                'star_complete_parcent'  => $need_percent
            ];
        });
    }
}