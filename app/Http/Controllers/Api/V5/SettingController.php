<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLive;
use App\Models\Kick;
use App\Models\Comment;
use App\Models\LiveCall;
use Auth;
use RedisCacheFunction;
use Illuminate\Support\Facades\Redis;
class SettingController extends Controller
{
     private $prefix = 'queenlive:';
    public function ActiveInvisible(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=RedisCacheFunction::UserfindById($user_id);
            if($data->is_invisible_active==1)
            {
                $data->is_invisible_active=0;
                $data->save();
                array_push($response,array('message'=>'Profile Invisible Deactive Successfully ','code'=>'200','data'=>$data));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                $data->is_invisible_active=1;
                $data->save();
                array_push($response,array('message'=>'Profile Invisible Active Successfully ','code'=>'200','data'=>$data));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
           

       }else{
        array_push($response,array('message'=>'Unauthorized','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
    }
     public function LiveOff(Request $request)
    {
        $response = array();
        $websocket = array();
         $list = array();
        $token = $request->access_token;
        $host = $request->host_id;
        $channelName = $request->channelName;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if((Auth::user()->brd_off_power==1 || Auth::user()->is_official_id != 0)){
                
                $host_id=RedisCacheFunction::UserfindById($host);
    		$sender_name=Auth::user();
    		$commnet_message = $commnet_message = "⚠️ Warning {$host_id->name}, আপনার লাইভ নিয়ম ভঙ্গের কারণে -- {$sender_name->name} -{$sender_name->id} -- অফিসিয়ালি অফ করে দিয়েছেন।";

    		  $gift_comment = [
                    'balance' => strval($sender_name->balance),
                    'channelName' => strval($channelName),
                    'id' => $sender_name->id,
                    'message' => strval('@'.$commnet_message),
                    'level' => strval($sender_name->level),
                    'name' => strval($sender_name->name),
                    'profile' => strval($sender_name->profile),
                    'is_vip' => strval($sender_name->is_vip),
                    'frame' => strval($sender_name->frame),
                    'is_official_id' => strval($sender_name->is_official_id),
                    'is_agency' => strval($sender_name->is_agency),
                    'is_host_id' => strval($sender_name->is_host_id),
                    'comment_badge' => strval($sender_name->comment_badge),
                    'type' => "message",
                ];
                
                    self::Websoket([array_merge($gift_comment, [
                        'code' => '200',
                        'event_type' => 'audio.call.pending_list',
                    ])]);
                    $comment=new Comment;
                    $comment->user_id=$sender_name->id;
                    $comment->channelName=$channelName;
                    $comment->message=$commnet_message;
                    $comment->reciever_id=$host_id->id;
                    $comment->type='message';
                    $comment->save();
                  // 2026-07-03 perf fix: sleep(7) removed (V4 LiveOff was already fixed; the 7s stall pinned a worker per official room-off).
        	$row = array();
		
    		$row['channelName'] = $channelName;
    		$row['status'] = strval(0);
    		$row['host_id'] = strval($host);

            		array_push($list, $row);
             array_push($websocket,array('message'=>'bd_bdr_off','channelName' => $channelName,'data'=>$list,'code'=>'200','event_type' => 'room.board.closed'));
       
        // Send the WebSocket message
            self::Websoket($websocket);
            
    		$live=UserLive::where('channelName',$channelName)->where('user_id',$host)->first();
    		if($live){
    		    Redis::del($this->prefix . "Video_Brd_Call_Details_{$host}_{$channelName}");
    		     Redis::del($this->prefix . "live_users_type_1");
                Redis::del($this->prefix . "live_frined_home");
                Redis::del($this->prefix . "live_top_list");
                Redis::del($this->prefix . "global_lives");
                Redis::del($this->prefix . "followerLive_{$host}");
                for ($i = 1; $i <= 10; $i++) {
                    Redis::del($this->prefix . "live_list_page_{$i}");
                    Redis::del($this->prefix . "live_list_v2_page_{$i}");
                }
    		    $live->delete();
    		}
    		
    		
    		array_push($response,array('message'=>'Brd Removed Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
			
            }else{
               
           array_push($response,array('message'=>'Your Device Banned ','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

       }else{
        array_push($response,array('message'=>'Unauthorized','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
    }
    
     private function Websoket($data) {
    try {
        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'SettingController']);
    } catch (\Throwable $th) {
        info('Setting named websocket exception: ' . $th->getMessage());
    }
}
}
