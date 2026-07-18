<?php

namespace App\Http\Controllers\Api\V4;

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
        $token = $request->access_token;
        $host = $request->host_id;
        $channelName = $request->channelName;

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if((Auth::user()->brd_off_power==1 || Auth::user()->is_official_id != 0)){
                // Delegated to unified service: broadcasts bd_bdr_off ch103,
                // posts the system comment, purges audio+video+multi feed caches.
                // No more sleep(7) - request returns immediately.
                app(\App\Services\LiveOffService::class)->offRoom(
                    $channelName,
                    $host,
                    Auth::id(),
                    'app_setting'
                );

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
