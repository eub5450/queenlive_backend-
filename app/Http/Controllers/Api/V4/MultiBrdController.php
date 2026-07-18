<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon;
use Pusher;
use App\Models\LiveCall;
use App\Models\AudienceJoin;
use App\Models\Setting;
use App\Models\Gift;
use App\Models\OldGift;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Comment;
use App\Models\Kick;
use App\Models\UserLive;
use App\Models\Avater;
use App\Models\Withdraw;
use App\Models\Follower;
use App\Models\BrdAdmin;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
class MultiBrdController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function HostList(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            
            $list=DB::table('live_calls')
            ->join('users','users.id','live_calls.co_host_id')
            ->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no','live_calls.camera_status')
            ->where('live_calls.host_id',$host_id)
            ->where('live_calls.channelName',$channelName)
            ->where('live_calls.status','Accept')
            ->get();
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
     public function PendingCallRemoved(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
         $channelName = $request->channelName;
        $response = array();
        $websoket_kick = array();
        $list = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            
          $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','pending')->first();
            if($data){
           $data->delete();
                    $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
                  
                array_push($response,array('message'=>'Call Request Removed Successfully ','data'=>$list,'code'=>'200'));
                
                 $call_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no','live_calls.camera_status')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                try {
                    app(\App\Services\V5\RoomBroadcastService::class)->broadcast(
                        'multi', $channelName, (string) $host_id,
                        'audio.call.pending_list',
                        ['channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list],
                        ['actor_user_id'=>$host_id,'target_user_id'=>$co_host_id]
                    );
                } catch (\Throwable $e_v5) { info('V5 multi pending_list failed: '.$e_v5->getMessage()); }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                // $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.set_no')->where('live_calls.channelName',$channelName)->get();
                   
                 array_push($response,array('message'=>'Call Not Request Removed Successfully ','data'=>$list,'code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
           
        
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    } 
    public function Kick(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $user_id = $request->user_id;
         $kick_by = $request->kick_by;
        $response = array();
        $websoket_kick = array();

        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $check_offical_user=User::find($kick_by);
            $check_admin=BrdAdmin::where('user_id',$host_id)->where('admin_id',$kick_by)->first(); // 2026-07-03: any room-admin slot (1/2/3) may kick
            
            if($kick_by==$host_id || ($check_offical_user && ($check_offical_user->kick_power==1 || $check_offical_user->is_official_id != 0 || $check_offical_user->is_admin == 1 || $check_offical_user->is_bd_admin == 1)) || $check_admin ){
            $remove_old_call=LiveCall::where('co_host_id',$user_id)->first();
            if($remove_old_call){
            $remove_old_call->delete();
            }
            $kick=new Kick;
            $kick->user_id=$user_id;
            $kick->channelName=$channelName;
            $kick->host_id=$host_id;
            $kick->kick_by=$kick_by;
            $kick->save();
            $user_by_kick=User::find($kick_by);
                array_push($response,array('message'=>'Kick Successfully ','channelName'=>$channelName,'user_id'=>$user_id,'user_by_kick'=>$user_by_kick->name,'channelName'=>$channelName,'code'=>'200'));
           
               try {
                    app(\App\Services\V5\RoomBroadcastService::class)->broadcast(
                        'multi', $channelName, (string) $host_id,
                        'room.member.kicked',
                        ['data'=>$response,'channelName'=>$channelName,'user_id'=>(string)$user_id],
                        ['actor_user_id'=>$kick_by,'target_user_id'=>$user_id]
                    );
               } catch (\Throwable $e_v5) { info('V5 multi kick(A) failed: '.$e_v5->getMessage()); }
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                // 2026-07-03 moderation fix: an unauthorized kick used to kick
                // the REQUESTER (self-kick) - room admins granted as slot 2/3
                // failed the old type=1 check and got ejected themselves.
                array_push($response,array('message'=>'You are not allowed to kick in this room','code'=>'403'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
   
     public function AudioCallAccept(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $co_host_id = $request->co_host_id;
         $set_no = $request->set_no;
         $channelName = $request->channelName;
         $response = array();
         $call_request = array();
         $websoket_kick = array();
         $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','pending')->first();
          if($data){
            $data->status='Accept';
            $data->save(); 
            }
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            $accept_list=DB::table('live_calls')->where('host_id',$host_id)->where('channelName','=',$channelName)->where('status','Accept')->get();
            $key = $channelName;
      
            $host_data=User::find($host_id);
            $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
          foreach ($accept_list as $call) {
                              $co_host = User::find($call->co_host_id);
                              $row = array();
                              $row['channelName'] = $channelName;
                              $row['profile'] = $co_host->profile;
                              $row['is_vip'] = $co_host->is_vip;
                              $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                              $row['co_host_name'] = $co_host->name;
                              $row['set_no'] = $call->set_no;
                              $row['mute'] = $call->mute;
                              $row['frame'] = strval($co_host->frame);
                              $row['co_host_id'] = strval($call->co_host_id);
                               $row['co_host_status'] = strval($call->is_co_host_active);
                               $row['camera_status'] = $call->camera_status;
                              $row['super_mute'] = strval($call->super_mute);
                              array_push($list, $row);
                          }
                          
                          
                            $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;

                              $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }
                                 $need_parcent=intval($today_gift/$next_level_amount*100);
         $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
         $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($response,array('message'=>'Audio Call Accept List Data Show Successfull come from call Accept ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
           
             array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
               self::Websoket($websocket_call);
                $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
                $call_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no','live_calls.camera_status')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                array_push($websoket_kick,array('message'=>'Call Request','channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list,'code'=>'200','channel_type' => '11'));
                self::Websoket($websoket_kick);
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
   
    
     public function CallRequest(Request $request)
    {
   
      $setting=Setting::find(1);
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $co_host_id = $request->co_host_id;
          $set_no = $request->set_no;
        $response = array();
        $websocket_call = array();
        $call_request = array();
        $websoket_kick = array();
        $list = array();
        // 2026-07-17: level gate removed - every user (level 1 included) may
        // send a co-host/call request, in every room type. The old check never
        // matched its own error text ("Level Need Must Be 2"): audio used
        // `level>0` (true for everyone), and multi used
        // `level>1 || $co->is_host_id=1` where the single `=` is an ASSIGNMENT,
        // not a comparison - it always evaluated truthy and short-circuited the
        // gate open. Video never had a gate at all. Removing it makes all three
        // room types consistent and drops a needless per-request user lookup.
            
      // info('HTTP response code For Audio Brd recieve-one: ' . $co);
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $remove_old_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','!=',$channelName)->where('host_id','!=',$host_id)->first();
            if($remove_old_call){
            $remove_old_call->delete();
            }
            // Clear ANY same-room row (pending OR a stale Accept left over
            // from a previous cohost session) so the next tap recreates and
            // rebroadcasts the host request. Without this, an audience who
            // was a cohost earlier (Accept row never cleaned up) gets a
            // permanent 401 "Call Already Sand" and the host never receives
            // a realtime notification.
            LiveCall::where('co_host_id',$co_host_id)
                ->where('channelName',$channelName)
                ->where('host_id',$host_id)
                ->delete();
          $check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
          if($check_call){
             array_push($response,array('message'=>'Call Already Sand ','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            if($live){
                $check_call_set=LiveCall::where('channelName','=',$channelName)->where('type',1)->where('host_id',$host_id)->where('set_no',$set_no)->first();
                if($check_call_set){
                      array_push($response,array('message'=>'Set Allready Booked','code'=>'401'));
                       return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                  // F: room LOCKED -> host approval (pending + dialog); room UNLOCKED -> auto-accept empty seat (else branch).
                  if ($live->locked!=0) {
                    $data=new LiveCall;
                    $data->co_host_id=$co_host_id;
                    $data->channelName=$channelName;
                    $data->type=$live->type;
                    $data->host_id=$host_id;
                    $data->set_no=$set_no;
                    $data->status='pending';
                    $data->super_mute='0';
                    $data->save();
                    $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
                    $call_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no','live_calls.camera_status')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                    array_push($websoket_kick,array('message'=>'Call Request','channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list,'code'=>'200','channel_type' => '11'));
                self::Websoket($websoket_kick);
                    array_push($response,array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);

                  }else{
                      $data=new LiveCall;
                      $data->co_host_id=$co_host_id;
                      $data->channelName=$channelName;
                      $data->type=$live->type;
                      $data->host_id=$host_id;
                      $data->set_no=$set_no;
                      $data->mute=1;
                      $data->status='Accept';
                      $data->is_co_host_active='pending';
                      $data->save();

                      $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
                      $host_data=User::find($host_id);
                      $list = array();

                          $host = array();
                          $host['channelName'] = $channelName;
                          $host['profile'] = $host_data->profile;
                          $host['is_vip'] = $host_data->is_vip;
                          $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
                          $host['co_host_name'] = $host_data->name;
                          $host['set_no'] = "0";
                          $host['mute'] = $live ? $live->mute : 0;
                          $host['frame'] = strval($host_data->frame);
                          $host['co_host_id'] = strval($host_data->id);
                          $host['co_host_status'] = strval('Accept');
                          $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
                          $host['super_mute'] = "0";
                          array_push($list, $host);
                          
                          foreach ($accept_list as $call) {
                              $co_host = User::find($call->co_host_id);
                              $row = array();
                              $row['channelName'] = $channelName;
                              $row['profile'] = $co_host->profile;
                              $row['is_vip'] = $co_host->is_vip;
                              $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                              $row['co_host_name'] = $co_host->name;
                              $row['set_no'] = $call->set_no;
                              $row['mute'] = $call->mute;
                              $row['frame'] = strval($co_host->frame);
                              $row['co_host_id'] = strval($call->co_host_id);
                               $row['co_host_status'] = strval($call->is_co_host_active);
                               $row['camera_status'] = $call->camera_status;
                              $row['super_mute'] = strval($call->super_mute);
                              array_push($list, $row);
                          }
                             $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
                            
                             $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }
                                 $need_parcent=intval($today_gift/$next_level_amount*100);
                           $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
                           $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
                             array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from  Call Request UnLockBrd ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
                            
                            array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
                           return json_encode($response,JSON_UNESCAPED_UNICODE);

                  }
                }
            }else{
                array_push($response,array('message'=>'Live Off Already','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
          }
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
       
    } 
    public function CallList(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('status','pending')->get();

            $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no','live_calls.camera_status')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

            array_push($response,array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
     public function Store(Request $request)
    {
            try {
         $access_token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
         $token = $request->token;
        $type = $request->type;
        $image = $request->image;
        $pin = $request->pin;
        $notice = $request->notice;
         $date = Carbon\Carbon::now();
        $response = array();
        $list = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
             $remove_old_call=LiveCall::where('co_host_id',$user_id)->first();
           if($remove_old_call){
            $remove_old_call->delete();
            }
            
	          $user=User::find($user_id);
	          $user_total_gift_recived_today=Gift::where('reciever_id', $user->id)->whereDate('date',now()->toDateString())->sum('value');
	          $top_value=$user->top_value+$user_total_gift_recived_today;
	          $avater=Avater::where('user_id',$user_id)->first();
	            $live = UserLive::storeOneActiveForUser(array(
	                'user_id' => $user_id,
	                'channelName' => $channelName,
	                'name' => $user->name,
	                'top_value' => $top_value,
	                'type' => $type,
	                'notice' => $notice,
	                'pin' => $pin,
	                'avatar' => $avater ? $avater->image : $user->profile,
	                'backgorund' => $image,
	                'audio_brd_design' => $request->audio_brd_design ?: '1',
	                'token' => $token,
	                'date' => $date,
	            ));
	             $this->CacheRemoved();
            $host_data=User::find($user_id);
        self::send_ws_notification($host_data,$channelName,$type);
      $host=array();
      $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
             $host['balance'] = 0;
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = "1";
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            
         array_push($list,$host);
                            $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $user_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $user_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
              $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }
                 $need_parcent=intval($today_gift/$next_level_amount*100);
         $total_reward=Gift::where('sander_id',1)->where('reciever_id',$user_id)->sum('value');
               array_push($response,array('message'=>'Host List come from brd start ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
                   array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
            
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized22','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
                
            } catch (\Exception $e) {
        array_push($response, array('message' => 'Internal Server Error', 'code' => '500', 'error' => $e->getMessage()));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
      public function CallMute(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $co_host_id = $request->co_host_id;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $mute_satus = $request->mute_satus;
         $super_mute = $request->super_mute;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
         // Officials and app-admins are protected from a host force-mute
         // ("speaker off"). Only blocks force-mute (super_mute=1).
         if ($super_mute == 1) {
             $muteTarget = \App\Models\User::find($co_host_id);
             if ($muteTarget && ($muteTarget->is_official_id != 0 || $muteTarget->is_admin == 1 || $muteTarget->is_bd_admin == 1)) {
                 array_push($response, array('message' => 'Official / admin cannot be speaker-muted', 'code' => '403'));
                 return json_encode($response, JSON_UNESCAPED_UNICODE);
             }
         }
         $data=LiveCall::where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('host_id',$host_id)->where('status','Accept')->first();
          if($data){
          $data->mute=$mute_satus;
          $data->mute_time = $mute_satus == 0 ? Carbon\Carbon::now() : null;
          $data->super_mute = $super_mute == 1 ? 1 : 0;
           $data->save();
          }
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
            $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
                            $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
                $today_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
            
            $levels = [
                2000000 => [5, 20000000],
                1000000 => [5, 2000000],
                500000  => [4, 1000000],
                200000  => [3, 500000],
                50000   => [2, 200000],
                0       => [1, 50000],
            ];
            
            foreach ($levels as $threshold => [$level, $nextAmount]) {
                if ($today_gift >= $threshold) {
                    $star = $level;
                    $next_level_amount = $nextAmount;
                    break;
                }
            }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
             $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
             $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($response,array('message'=>'Call Mute Successfully','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
             
              $roomName='audio_call_host_list';
               array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
             // self::RealTime($response,$roomName,$channelName);
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    } 
    public function HostCallCamera(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $host_camera_status = $request->host_camera_status;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
                $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
            $live=UserLive::where('user_id',$host_id)->first();
            info('Multi Host Data ' . $host_data);
            if($live)
            {
                $live->host_camera_status=$host_camera_status;
                $live->save();
            }
        
            
            $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
                            $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
                $today_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
            
            $levels = [
                2000000 => [5, 20000000],
                1000000 => [5, 2000000],
                500000  => [4, 1000000],
                200000  => [3, 500000],
                50000   => [2, 200000],
                0       => [1, 50000],
            ];
            
            foreach ($levels as $threshold => [$level, $nextAmount]) {
                if ($today_gift >= $threshold) {
                    $star = $level;
                    $next_level_amount = $nextAmount;
                    break;
                }
            }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
             $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
             $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($response,array('message'=>'Call Mute Successfully','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
             
              $roomName='audio_call_host_list';
               array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
             // self::RealTime($response,$roomName,$channelName);
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
     public function CallRemoved(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
         $channelName = $request->channelName;
      $set_no = $request->set_no;
        $response = array();
        $accept = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            
            // Boss 2026-06-27: status-agnostic delete so cut-call always wipes the seat row
            // (previous Accept-only filter left pending/other-status rows on the host UI
            //  and blocked re-broadcast of the next CallRequest).
            LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->delete();
            $data=null;
          $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
          $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
          
            $host_data=User::find($host_id);
            $list = array();
            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] = Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                  $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
             $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
            $today_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
            
            $levels = [
                2000000 => [5, 20000000],
                1000000 => [5, 2000000],
                500000  => [4, 1000000],
                200000  => [3, 500000],
                50000   => [2, 200000],
                0       => [1, 50000],
            ];
            
            foreach ($levels as $threshold => [$level, $nextAmount]) {
                if ($today_gift >= $threshold) {
                    $star = $level;
                    $next_level_amount = $nextAmount;
                    break;
                }
            }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
            $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
            $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
            array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from remove call ','host_list'=>$list,'set_remove'=>$set_no,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
             array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
            self::Websoket($websocket_call);
             
               return json_encode($response,JSON_UNESCAPED_UNICODE);
        
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    } 
    public function HostCallRemove(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
         $channelName = $request->channelName;
      $set_no = $request->set_no;
        $websocket_call = array();
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
              array_push($response,array('message'=>'Audio Call Removed By Host ','co_host_id'=>$co_host_id,'set_remove'=>$set_no,'host_id'=>$host_id,'channelName'=>$channelName,'code'=>'200'));
              
               $roomName='audio_host_call_remove';
             
              array_push($websocket_call,array('message'=>'bd_audio_host_call_remove','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '23'));
                self::Websoket($websocket_call);
                
            $data = LiveCall::where('host_id', $host_id)
                    ->where('channelName', $channelName)
                    ->where('co_host_id', $co_host_id)
                    ->where('status', 'Accept')
                    ->first();
                
                if ($data) {
                    // 2026-07-03 perf fix: delete immediately (was sleep(3),
                    // pinning a worker and leaving the cut seat occupied for 3s;
                    // video got the same fix earlier).
                    $data->delete();
                }
                
               return json_encode($response,JSON_UNESCAPED_UNICODE);
        
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    
    
    public function UserData(Request $request)
    {
        $joinresponse = array();
        $response = array();
        $websocket_call = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $host_id = $request->host_id;
        $channelName = $request->channelName;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            
          if($host_id==$user_id){
            $data=User::find($user_id);
             $follow_status=0;
             $is_host=DB::table('host_data')->join('users','users.id','host_data.user_id')->join('agencies','agencies.code','host_data.agency_code')->where('users.is_host_id',1)->where('users.id',$user_id)->select('host_data.hosting_type','agencies.name')->first();
            //Balance
            $host_type=0;
            $agency_name='bp';
            if($is_host)
            {
              $host_type=$is_host->hosting_type;
              $agency_name=$is_host->name;
            }
            $date = Carbon\Carbon::now(); 

                 $start_date = date('Y-m') . '-01';
            
                $end_date = date('Y-m') . '-31';
                
                $query = DB::table('gifts')
                    ->join('users', 'users.id', '=', 'gifts.sander_id')
                     ->whereDate('gifts.date', '>=', $start_date)
                    ->whereDate('gifts.date', '<=', $end_date) 
                    ->where('gifts.reciever_id',$host_id)
                    ->select('users.profile', 'users.name', 'users.id', 'users.level', 'gifts.value');
                
                $total_data =DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('gifts.reciever_id',$host_id)
                ->whereDate('gifts.date', '>=', $start_date)
                ->whereDate('gifts.date', '<=', $end_date) 
                ->select('users.profile', 'users.name', 'users.id', 'users.level', DB::raw('SUM(gifts.value) as total_value'))
                ->groupBy('users.profile', 'users.name', 'users.id', 'users.level')
                ->orderByDesc('total_value')
                ->get();
                 $total_gift_coin=$query->sum('value');
               $total_withdraw=Withdraw::where('host_id',$user_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->sum('total');
               $total_data_sum= ($data->previous_coin+$total_gift_coin);

            
            $agency_name='Bp';
            array_push($response,array('message'=>'User Data Show Successfully ','code'=>'200','data'=>$data,'follow_status'=>$follow_status,'balance'=>$total_data_sum,'agency_name'=>$agency_name,'host_type'=>$host_type,'marchent'=>$data->is_agency,'is_coin_protal_active'=>$data->is_coin_protal_active,'is_vip'=>$data->is_vip,'frame'=>$data->frame,'entry_effect'=>$data->entry));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
          $data = User::find($user_id);
          $follower = User::find($host_id);

          // Check if the user is following the follower
          $isFollowing = $data->following()->where('follower_id', $follower->id)->exists();

          // Check if the follower is following the user
          $isFollowedBy = $data->followers()->where('user_id', $follower->id)->exists();

          // Check if the two users are friends (i.e., if they are following each other)
          $areFriends = $isFollowing && $isFollowedBy;
            if($areFriends)
            {
            $follow_status=2;
            }elseif($isFollowing){
            $follow_status=1;
            }else{
               $follow_status=1;
            }
            $is_host=DB::table('host_data')->join('users','users.id','host_data.user_id')->join('agencies','agencies.code','host_data.agency_code')->where('users.is_host_id',1)->where('users.id',$data->user_id)->select('host_data.hosting_type','agencies.name')->first();
            //Balance
            $host_type=0;
             $agency_name='bp';
            if($is_host)
            {
              $host_type=$is_host->hosting_type;
              $agency_name=$is_host->name;
            }
            //Balance
            $date = Carbon\Carbon::now(); // Replace this with your desired date


                 $start_date = date('Y-m') . '-01';
              
                $end_date = date('Y-m') . '-31';
                
                $query = DB::table('gifts')
                    ->join('users', 'users.id', '=', 'gifts.sander_id')
                   ->whereDate('gifts.date', '>=', $start_date)
                   ->whereDate('gifts.date', '<=', $end_date) 
                   ->where('gifts.reciever_id',$host_id)
                ->select('users.profile', 'users.name', 'users.id', 'users.level', 'gifts.value');
               
  
                 $total_gift_coin=$query->sum('value');
               $total_withdraw=Withdraw::where('host_id',$user_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->sum('total');
               $total_data_sum= $total_gift_coin-$total_withdraw;

            
            
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
      $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['is_vip'] = $host_data->is_vip;
            $host['profile'] = $host_data->profile;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['is_vip'] = $co_host->is_vip;
                $row['profile'] = $co_host->profile;
                $row['balance'] = Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                 $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                 $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
             $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
             $today_gift = Gift::where('reciever_id', $host_id)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
            
            $levels = [
                2000000 => [5, 20000000],
                1000000 => [5, 2000000],
                500000  => [4, 1000000],
                200000  => [3, 500000],
                50000   => [2, 200000],
                0       => [1, 50000],
            ];
            
            foreach ($levels as $threshold => [$level, $nextAmount]) {
                if ($today_gift >= $threshold) {
                    $star = $level;
                    $next_level_amount = $nextAmount;
                    break;
                }
            }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
             $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
             $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
              array_push($joinresponse,array('message'=>'Audio Call Accept List Data Show Successfully From User Data ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
            
              $roomName='audio_call_host_list';
              //self::RealTime($joinresponse,$roomName,$channelName);
             array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$joinresponse,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
             self::Websoket($websocket_call);
             array_push($response,array('message'=>'User Data Show Successfully','code'=>'200','data'=>$data,'follow_status'=>$follow_status,'balance'=>$total_data_sum,'agency_name'=>$agency_name,'host_type'=>$host_type,'marchent'=>$data->is_agency,'is_coin_protal_active'=>$data->is_coin_protal_active,'is_vip'=>$data->is_vip,'frame'=>$data->frame,'entry_effect'=>$data->entry));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
           
          }

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function HostMue(Request $request)
    {
        $response = array();
        $websocket_call = array();
        $token = $request->access_token;
        $host_id = $request->host_id;
        $mute_satus = $request->mute_satus;
        $channelName = $request->channelName;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            if($live)
            {
                $live->mute=$mute_satus;
                $live->save();
            }
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
             $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] =Gift::where('reciever_id', $call->co_host_id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                 $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                 $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
          $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
                         $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
             $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
             $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
              array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from Host Mute Unmute ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
             
              $roomName='audio_call_host_list';
              //self::RealTime($response,$roomName,$channelName);
               array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
              return json_encode($response,JSON_UNESCAPED_UNICODE);
            
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function AudioGiftPush(Request $request)
    {
        $token = $request->access_token;
        $user_id = $request->user_id;
        $value = $request->value;
        $gift_name = $request->giftName;
        $channelName = $request->channelName;
        $music = $request->music;
        $gift_type = $request->gift_type;
        $host_id = $request->host_id;
        $response = array();
        $pusher_response = array();
        $global_txt = array();
        $websocket_call = array();
        $gift_global_websoket = array();
        $global_websoket = array();
        $gift_effect = array();
        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
        $i=0;
        $j=0;
                
              $data = json_decode($request->getContent(), true); // Assuming the request is sent as JSON

                if (isset($data['items']) && is_array($data['items'])) {
                    
                     foreach ($data['items'] as $row) {
                   // return $row['value'];
                    try {
                        $txResult = DB::transaction(function () use ($user_id, $value, $row, $gift_name, $channelName) {
                            $sander = User::where('id', $user_id)->lockForUpdate()->first();
                            if (!$sander) {
                                throw new \RuntimeException('SENDER_NOT_FOUND');
                            }
                            if ($sander->balance < $value) {
                                throw new \RuntimeException('INSUFFICIENT_BALANCE');
                            }
                            $sander->balance -= $value;
                            $sander->save();

                            $gift = new Gift;
                            $gift->sander_id = $user_id;
                            $gift->reciever_id = $row['receiverId'];
                            $gift->name = $gift_name;
                            $gift->value = $value;
                            $gift->channelName = $channelName;
                            $gift->date = Carbon\Carbon::now();
                            $gift->save();

                            return [
                                'sander' => $sander,
                                'gift'   => $gift,
                            ];
                        });
                    } catch (\Throwable $e) {
                        \Log::warning('MultiBrd AudioGiftPush per-item skipped', [
                            'user_id' => $user_id,
                            'value'   => $value,
                            'error'   => $e->getMessage(),
                        ]);
                        continue;
                    }
                    $sander = $txResult['sander'];
                    $gift = $txResult['gift'];

                    // Agent SC1 / 2026-06-28: gift write committed for this item;
                    // invalidate V5 DISPLAY state cache for sender + this receiver.
                    // DISPLAY-ONLY; the money write inside the txn above is untouched.
                    try {
                        \App\Services\V5\V5UserStateCache::bump((string) $user_id);
                        \App\Services\V5\V5UserStateCache::bump((string) $row['receiverId']);
                    } catch (\Throwable $e) {
                        \Log::warning('V5UserStateCache bump (MultiBrd AudioGiftPush) failed', ['error' => $e->getMessage()]);
                    }

                    // remainder of original logic for this item, outside transaction
                    {
                        $top_value_update_user=User::find($row['receiverId']);
                        $check_user_live=UserLive::where('user_id',$top_value_update_user->id)->first();
                        if($check_user_live){
                        $user_total_gift_recived_today=Gift::where('reciever_id',$top_value_update_user->id )->whereDate('date',now()->toDateString())->sum('value');
                        $top_value=$top_value_update_user->top_value+$user_total_gift_recived_today;
                        $check_user_live->top_value=$top_value;
                        $check_user_live->save();
                        }
                        $newFileName = Str::title(str_replace(['_', '.svga'], [' ', ''], $gift->name));
                        if($value>49999 ||$user_id==1111){

                        $sender_name=User::find($user_id);
                        $receiver_name=User::find($row['receiverId']);
                        $message = "$sender_name->name sent $value to $receiver_name->name";

                         array_push($global_txt,array('message'=>$message,'image'=>$sender_name->profile,'receiver_profile'=>$receiver_name->profile,'name'=>$sender_name->name));
                         array_push($gift_global_websoket,array('message'=>'bd_global_gift','channelName'=>$channelName,'data'=>$global_txt,'code'=>'200','channel_type' => '88'));

                        self::Websoket($gift_global_websoket);
                        }
                        $total = Gift::where('sander_id', $user_id)->sum('value') + OldGift::where('sander_id', $user_id)->sum('value');
                        $receiver_name=User::find($row['receiverId']);
                        $forcomment[] = $receiver_name->name;

                         if ($total > 0) {
                        $user = User::find($user_id);
                        $levelBoundaries = [
                                2 => [40000, 50000], 3 => [50001, 100000], 4 => [100001, 150000],
                                5 => [150001, 200000], 6 => [200001, 400000], 7 => [400001, 600000],
                                8 => [600001, 800000], 9 => [800001, 1000000], 10 => [1000001, 1200000],
                                11 => [1200001, 2200000], 12 => [2200001, 3200000], 13 => [3200001, 4200000],
                                14 => [4200001, 5200000], 15 => [5200001, 6200000], 16 => [6200001, 8200000],
                                17 => [8200001, 10200000], 18 => [10200001, 12200000], 19 => [12200001, 14200000],
                                20 => [14200001, 16200000], 21 => [16200001, 19200000], 22 => [19200001, 22200000],
                                23 => [22200001, 25200000], 24 => [25200001, 28200000], 25 => [28200001, 31200000],
                                26 => [31200001, 40000000], 27 => [40000001, 50000000], 28 => [50000001, 60000000],
                                29 => [60000001, 70000000], 30 => [70000001, 80000000], 31 => [80000001, 100000000],
                                32 => [100000001, 120000000], 33 => [120000001, 140000000], 34 => [140000001, 160000000],
                                35 => [160000001, 180000000], 36 => [180000001, 200000000], 37 => [200000001, 220000000],
                                38 => [220000001, 240000000], 39 => [240000001, 260000000], 40 => [260000001, 280000000],
                                41 => [280000001, 330000000], 42 => [330000001, 380000000], 43 => [380000001, 430000000],
                                44 => [430000001, 480000000], 45 => [480000001, 530000000], 46 => [530000001, 580000000],
                                47 => [580000001, 630000000], 48 => [630000001, 680000000], 49 => [680000001, 730000000]
                            ];

                            $level = 1;

                            foreach ($levelBoundaries as $lvl => $boundary) {
                                if ($total >= $boundary[0] && $total < $boundary[1]) {
                                    $level = $lvl;
                                    break;
                                }
                            }

                            // Ensure we only increase the level, not decrease it
                            if ($user->level < $level) {
                                $user->level = $level;
                                $user->save(); // Persist changes
                            }

                          $user->save();
                        }

                    $i++;
                     if($j==0){

                      $balance = Gift::where('reciever_id', $gift->reciever_id)->where('channelName',$channelName)->sum('value');
                      $audience = User::find($user_id);
                      $count = [
                        'channelName' => $channelName,
                         'name' =>  $gift->name,
                         'gift_time' => strval(5),
                        'host_balance' => strval($balance),
                        'music' => strval($music),
                        'audience_balance' => strval($audience->balance),
                        'reciever_id' => strval($gift->reciever_id),
                        'status' => 'active',
                        'gift_type' => strval($gift_type),
                      ];
                   array_push($gift_effect,array('message'=>'Audio gift','channelName'=>$channelName,'data'=>$count,'code'=>'200','channel_type' => '24'));
            self::Websoket($gift_effect);
                    }
                    $j++;
                    }                }
                   
                 $sender_name = User::find($user_id);
                 
                $all_receiver_names = implode(', ', $forcomment);
                $commnet_message = "{$sender_name->name} sent {$newFileName}  ({$value}) to {$all_receiver_names}";
                
                 $data=new Comment;
                $data->user_id=$user_id;
                $data->channelName=$channelName;
                $data->message=$commnet_message;
                $data->reciever_id=$host_id;
                $data->type='message';
                $data->save();
                
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
                        
                        // Reference to the channel comments
                        $comments_ref = $this->database->getReference('NewComments/'.$channelName);
                        
                        // Get the existing comments
                        $existing_comments = $comments_ref->getValue();
                        
                        // Determine the next index
                        $next_index = 0;
                        if (is_array($existing_comments)) {
                            $next_index = count($existing_comments);
                        }
                        
                        // Push the new comment at the next index
                        $next_comment_ref = $this->database->getReference('NewComments/'.$channelName.'/'.$next_index);
                        $next_comment_ref->set($gift_comment);
 
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();

            $host_data=User::find($host_id);
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
      $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = 1;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
             $host['co_host_status'] = strval('Accept');
             $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
             $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] = Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                 $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                 $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
                }
            $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
                        $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
               $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
               $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($pusher_response,array('message'=>'Audio Call Accept List Data Show Successfull come from call Accept ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
              
              $roomName='audio_call_host_list';
               array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$pusher_response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);
               //self::RealTime($pusher_response,$roomName,$channelName);
                $sander_user = User::find($user_id);
                array_push($response, array('message' => 'Gifts Sent Successfully','user_id'=>$sander_user->id,'balance'=>$sander_user->balance,'code' => '200'));

                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                array_push($response, array('message' => 'Must Send at Least One Gift', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    public function LockUnlock(Request $request)
    {
        $token = $request->access_token;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        $response = array();
 
        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
             $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
             if($live){
                if($live->locked==1){
                    $live->locked=0;
                     array_push($response,array('message'=>'Audio Brd Unlock Successfully','code'=>'200'));
                }else{
                    $live->locked=1;
                     array_push($response,array('message'=>'Audio Brd lock Successfully','code'=>'200'));
                }
                $live->save();
                
                 return json_encode($response,JSON_UNESCAPED_UNICODE);
             }else{
                 array_push($response, array('message' => 'Live Removed Already', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
             }
        }else{
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    
 

     
     public function CohostisActive(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $co_host_id = $request->co_host_id;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $is_co_host_active = $request->is_co_host_active;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
         $data=LiveCall::where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('host_id',$host_id)->where('status','Accept')->first();
          if($data){
          $data->is_co_host_active=$is_co_host_active;
           $data->save();
          }
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
            $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';
            $host['super_mute'] = "0";
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                 $row['co_host_status'] = strval($call->is_co_host_active);
                $row['camera_status'] = $call->camera_status;
                 $row['super_mute'] = strval($call->super_mute);
                array_push($list, $row);
            }
             $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift - $monthlyWithdraw;
                             $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }

                 $need_parcent=intval($today_gift/$next_level_amount*100);
            $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
            $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from  call mute ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
             $roomName='audio_call_host_list';
               //self::RealTime($response,$roomName,$channelName);
                array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                self::Websoket($websocket_call);

             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    } 
    public function CameraStatusChange(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $co_host_id = $request->co_host_id;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $camera_status = $request->camera_status;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
          
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();
            $host_data=User::find($host_id);
            $list = array();

            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($host_data->frame);
            $host['co_host_id'] = strval($host_data->id);
            $host['co_host_status'] = strval('Accept');
            $host['camera_status'] = isset($live->host_camera_status) ? $live->host_camera_status : '0';  
             $host['super_mute'] = "0"; 
            
            array_push($list, $host);
            
            foreach ($accept_list as $call) {
                $co_host = User::find($call->co_host_id);
                $row = array();
                $row['channelName'] = $channelName;
                $row['profile'] = $co_host->profile;
                $row['is_vip'] = $co_host->is_vip;
                $row['balance'] =Gift::where('reciever_id', $co_host->id)->where('channelName',$channelName)->sum('value');
                $row['co_host_name'] = $co_host->name;
                $row['set_no'] = $call->set_no;
                $row['mute'] = $call->mute;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                 $row['co_host_status'] = strval($call->is_co_host_active);
                if($co_host_id==$call->co_host_id){
                  $call->camera_status=$camera_status;
                  $call->save();
                $row['camera_status'] = $call->camera_status;
                }else{
                  $row['camera_status'] = $call->camera_status;  
                }
                $row['super_mute'] = strval($call->super_mute);
                
                array_push($list, $row);
            }
             $start_date = now()->startOfMonth();
                            $end_date = now()->endOfMonth();
                        
                            $monthlyGift = Gift::where('reciever_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('value');
                        
                            $monthlyWithdraw = Withdraw::where('host_id', $host_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total');
                        
                            $total_gift_sum = $monthlyGift;
            
             $today_gift = Gift::where('reciever_id', $host_id)
                            ->whereDate('date', now()->toDateString())
                            ->sum('value');
                        
                        $levels = [
                            2000000 => [5, 20000000],
                            1000000 => [5, 2000000],
                            500000  => [4, 1000000],
                            200000  => [3, 500000],
                            50000   => [2, 200000],
                            0       => [1, 50000],
                        ];
                        
                        foreach ($levels as $threshold => [$level, $nextAmount]) {
                            if ($today_gift >= $threshold) {
                                $star = $level;
                                $next_level_amount = $nextAmount;
                                break;
                            }
                        }
                 $need_parcent=intval($today_gift/$next_level_amount*100);
           $total_reward=Gift::where('sander_id',1)->where('reciever_id',$host_id)->sum('value');
           $total_reward = $total_reward == 0 ? '0' : (string) $total_reward;
               array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from  call mute ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'channelName'=>$channelName,'total_reward'=>$total_reward,'code'=>'200'));
              array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','channel_type' => '3'));
                            self::Websoket($websocket_call);

               $roomName='audio_call_host_list';
               //self::RealTime($response,$roomName,$channelName);
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }   
    

     private function RealTime($response,$roomName,$channelName)
    {
      $setting = Setting::find(1);
      $options = array(
              'cluster' => $setting->cluster,
              'useTLS' => true
          );
          $pusher = new Pusher\Pusher(
              $setting->key,
              $setting->secret,
              $setting->app_id,
              $options
          );
        $pusher->trigger($roomName,$channelName,$response);
    }
        private function Websoket($data)
{
    try {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'MultiBrdController']);

    } catch (\Throwable $th) {
        info('Multi named WebSocket dispatch failed: ' . $th->getMessage());
    }
}
function send_ws_notification($host_data, $channelName, $brd_type)
{
    $followers = Follower::where('follower_id', $host_data->id)->get();
    if ($followers->isEmpty()) {
        return;
    }

    $sentences = [
        "I am waiting for you, please join and let's make more friends together.",
        "আমি তোমার জন্য অপেক্ষা করছি, যোগ দাও এবং চল একসঙ্গে আরও বন্ধু তৈরি করি।",
        "Come join me, let's connect and build new friendships.",
        "আমার সাথে যোগ দাও, চল নতুন বন্ধুত্ব তৈরি করি।",
        "Don't miss out, I'm here waiting for you to join and meet more friends.",
        "মিস কোরো না, আমি এখানে তোমার জন্য অপেক্ষা করছি বন্ধুদের সাথে দেখা করার জন্য।",
        "Join me now, and let's make wonderful memories with friends.",
        "এখনই আমার সাথে যোগ দাও, এবং চল বন্ধুদের সাথে চমৎকার স্মৃতি তৈরি করি।",
        "I'm waiting for you! Let's make our circle bigger with new friends.",
        "আমি তোমার জন্য অপেক্ষা করছি! চল আমাদের বন্ধুদের সংখ্যা বাড়াই।",
        "Let's join hands and create a beautiful friendship circle.",
        "চল হাতে হাত রেখে একটি সুন্দর বন্ধুত্বের বৃত্ত তৈরি করি।",
        "Your presence will make it better, join and meet new people.",
        "তোমার উপস্থিতি এটিকে আরও সুন্দর করবে, যোগ দাও এবং নতুন মানুষদের সাথে পরিচিত হও।",
        "Join me today, and let's share laughter with friends.",
        "আজই আমার সাথে যোগ দাও, এবং চল বন্ধুদের সাথে হাসি ভাগাভাগি করি।"
    ];

    $random_sentence = $sentences[array_rand($sentences)];

    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        ['cluster' => config('broadcasting.connections.pusher.options.cluster'), 'useTLS' => true]
    );

    $payload = json_encode([[
        'event_type'   => 'room.share.invite',
        'channelName'  => $channelName,
        'brd_type'     => $brd_type,
        'host_id'      => $host_data->id,
        'host_name'    => $host_data->name,
        'host_profile' => $host_data->profile,
        'message'      => $random_sentence,
    ]], JSON_UNESCAPED_UNICODE);

    foreach (array_chunk($followers->pluck('user_id')->toArray(), 100) as $batch) {
        try {
            $events = [];
            foreach ($batch as $uid) {
                $events[] = [
                    'channel' => 'notification-' . $uid,
                    'name'    => 'room.share.invite',
                    'data'    => $payload,
                ];
            }
            $pusher->triggerBatch($events);
        } catch (\Throwable $e) {
            // silently skip failed triggers
        }
    }
}
private function CacheRemoved()
    {
        try {
            // Remove specific cache keys
            $keys = [
                'live_users_type_1',
                'live_frined_home',
                'live_top_list'
            ];
    
            foreach ($keys as $key) {
                if (Cache::has($key)) {
                    Cache::forget($key);
                }
            }
    
            // Remove paginated live list cache
            for ($i = 1; $i <= 5; $i++) {
                Cache::forget("live_list_page_{$i}");
            }
    
           
            Log::info('Live cache cleared successfully');
    
            return true;
        } catch (\Exception $e) {
            Log::error('CacheRemoved failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
