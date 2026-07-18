<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveCall;
use App\Models\Setting;
use App\Models\Gift;
use App\Models\UserLive;
use App\Models\User;
use Kreait\Firebase\Contract\Database;
use Pusher;
use Carbon;
use App\Models\Withdraw;
use DB;
use App\Models\AudienceJoin;
use Illuminate\Support\Facades\Cache;
class HostCallController extends Controller
{
    public function CallRequest(Request $request)
    {
    	$access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $co_host_id = $request->co_host_id;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
    	$live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            if($live){
            	$is_co_host_have=AudienceJoin::where('channelName',$channelName)->where('host_id',$host_id)->where('user_id',$co_host_id)->first();
            	if ($is_co_host_have) {
            	
            	$check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
            	if (!$check_call) {
            	
                array_push($response,array('message'=>'Call Request Sand Successfully','channelName'=>$channelName,'host_id'=>$host_id,'co_host_id'=>$co_host_id,'code'=>'200'));
                 
                    $roomName='host_call_request_for_audience';
                   // self::RealTime($response,$roomName,$channelName);
                       array_push($websocket_call,array('message'=>'bd_host_video_call_request_for_audience','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'video.cohost.invited'));
                        self::Websoket($websocket_call);
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            	}else{
            	 array_push($response,array('message'=>'User Already In Your Call','code'=>'401'));
                 return json_encode($response,JSON_UNESCAPED_UNICODE);
            	}

            }else{
            array_push($response,array('message'=>'User Not Active In Your Live','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

            }else{
            array_push($response,array('message'=>'Host Live Not Actived','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
        	array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }

    public function CallAccept(Request $request)
    {
    	$access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $co_host_id = $request->co_host_id;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$remove_old_call=LiveCall::where('co_host_id',$co_host_id)->first();
            if($remove_old_call){
            $remove_old_call->delete();
            }
        	$check_accept_count=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','Accept')->count();
            if($check_accept_count<3){
            	 $check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
		          if($check_call){
		             array_push($response,array('message'=>'You Are Already In Call ','code'=>'401'));
		            return json_encode($response,JSON_UNESCAPED_UNICODE);
		        }else{
		        	$live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            		if($live){
            			$data=new LiveCall;
			            $data->co_host_id=$co_host_id;
			            $data->channelName=$channelName;
			            $data->type=2;
			            $data->host_id=$host_id;
			            $data->set_no=0;
			            $data->status='Accept';
			            $data->is_co_host_active='Accept';
			            $data->super_mute='0';
			            $data->save();
			            $accept_list=DB::table('live_calls')->where('host_id',$host_id)->where('channelName','=',$channelName)->where('status','Accept')->get();
			           	$key = $channelName;
			          	$host_data=User::find($host_id);
						 $list = array();
                          $co_host_list = array();
                    
                                $host = array();
                                $host['channelName'] = $channelName;
                                $host['profile'] = $host_data->profile;
                                $host['is_vip'] = $host_data->is_vip;
                                $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
                                $host['co_host_name'] = $host_data->name;
                                $host['set_no'] = "0";
                                $host['mute'] = $live->mute;
                                $host['co_host_id'] = strval($host_data->id);
                                $host['co_host_status'] = strval('Accept');
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
                                    $row['set_no'] = "0";
                                    $row['mute'] = $call->mute;
                                    $row['co_host_id'] = strval($call->co_host_id);
                                    $row['co_host_status'] = strval($call->is_co_host_active);
                                     $row['super_mute'] = strval($call->super_mute);
                                    array_push($list, $row);
                                    array_push($co_host_list, $row);
                                }
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
               $total_withdraw=Withdraw::where('host_id',$host_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->sum('total');
               $total_gift_sum= $total_gift_coin-$total_withdraw;
			              $today_gift = Gift::where('reciever_id',$host_id)
                  ->whereDate('date', now()->toDateString())
                  ->sum('value');
                $next_level_amount=50000;
                $star = 1;

                if ($today_gift < 50000) {
                    $star = 1;
                    $next_level_amount=50000;
                } elseif ($today_gift >= 50000 && $today_gift < 200000) {
                    $star = 2;
                    $next_level_amount=200000;
                } elseif ($today_gift >= 200000 && $today_gift < 500000) {
                    $star = 3;
                    $next_level_amount=500000;
                } elseif ($today_gift >= 500000 && $today_gift < 1000000) {
                    $star = 4;
                    $next_level_amount=1000000;
                } elseif ($today_gift >= 1000000 && $today_gift < 2000000) {
                    $star = 5;
                    $next_level_amount=2000000;
                } elseif ($today_gift >= 2000000) {
                    $next_level_amount=20000000;
                    $star = 5; // Adjusted for values equal to or greater than 2000000
                }
                 $need_parcent=intval($today_gift/$next_level_amount*100);
			           
			               array_push($response,array('message'=>'Video Call Accept List Data Show Successfull come from Audience Accept Call ','host_list'=>$list,'co_host_list'=>$co_host_list,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'set_no_remove'=>4,'channelName'=>$channelName,'code'=>'200'));
			         
                           $roomName='video_call_host_list';
                           // self::RealTime($response,$roomName,$channelName);
                              array_push($websocket_call,array('message'=>'bd_video_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'video.room.snapshot'));;
               self::Websoket($websocket_call);
			               return json_encode($response,JSON_UNESCAPED_UNICODE);
            		}else{
            	     array_push($response,array('message'=>'Host Live Not Actived','code'=>'401'));
                     return json_encode($response,JSON_UNESCAPED_UNICODE);
            		}
		        }
            }else{
            array_push($response,array('message'=>'Already Cross Set Limit','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
        	array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
     public function AudioCallRequest(Request $request)
    {
    	$access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $co_host_id = $request->co_host_id;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
    	$live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            if($live){
            	$is_co_host_have=AudienceJoin::where('channelName',$channelName)->where('host_id',$host_id)->where('user_id',$co_host_id)->first();
            	if ($is_co_host_have) {
            	
            	$check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
            	if (!$check_call) {
            		
                array_push($response,array('message'=>'Call Request Sand Successfully','channelName'=>$channelName,'host_id'=>$host_id,'co_host_id'=>$co_host_id,'channelName'=>$channelName,'code'=>'200'));
                  
                $roomName='host_call_request_for_audience';
               // self::RealTime($response,$roomName,$channelName);
                array_push($websocket_call,array('message'=>'bd_audio_host_call_request_for_audience','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'audio.cohost.invited'));
                        self::Websoket($websocket_call);
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            	}else{
            	 array_push($response,array('message'=>'User Already In Your Call','code'=>'401'));
                 return json_encode($response,JSON_UNESCAPED_UNICODE);
            	}

            }else{
            array_push($response,array('message'=>'User Not Active In Your Live','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

            }else{
            array_push($response,array('message'=>'Host Live Not Actived','code'=>'401'));
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
         $channelName = $request->channelName;
         $co_host_id = $request->co_host_id;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$remove_old_call=LiveCall::where('co_host_id',$co_host_id)->first();
            if($remove_old_call){
            $remove_old_call->delete();
            }
        	$check_accept_count=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('status','Accept')->count();
            if($check_accept_count<15){
            	 $check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
		          if($check_call){
		             array_push($response,array('message'=>'You Are Already In Call ','code'=>'401'));
		            return json_encode($response,JSON_UNESCAPED_UNICODE);
		        }else{
		        	$live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            		if($live){
                         $maxSeats = $this->audioRoomMaxSeats($live, $request);
                         $nextSeatNo = $this->nextAvailableAudioSeat($host_id, $channelName, $maxSeats);
                         if ($nextSeatNo === null) {
                             array_push($response,array('message'=>'Already Cross Set Limit','code'=>'401','max_seats'=>strval($maxSeats)));
                             return json_encode($response,JSON_UNESCAPED_UNICODE);
                         }
           
            			$data=new LiveCall;
			            $data->co_host_id=$co_host_id;
			            $data->channelName=$channelName;
			            $data->type=2;
			            $data->host_id=$host_id;
			            $data->set_no=$nextSeatNo;
			            $data->status='Accept';
			            $data->is_co_host_active='Accept';
			            // BUG FIX: seat the invited cohost UNMUTED (mute=1). Without this the
			            // new row had mute=NULL -> audience rendered it as muted while the host
			            // showed it unmuted.
			            $data->mute=1;
			            $data->save();
			            $accept_list=DB::table('live_calls')->where('host_id',$host_id)->where('channelName','=',$channelName)->where('status','Accept')->get();
			           	$key = $channelName;
						
			          	$host_data=User::find($host_id);
						$list = array();
						$co_host_list = array();

			            
			            $host = array();
                        $host['channelName'] = $channelName;
                        $host['profile'] = $host_data->profile;
                        $host['is_vip'] = $host_data->is_vip;
                        $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
                        $host['co_host_name'] = $host_data->name;
                        $host['set_no'] = "0";
                        $host['mute'] =$live->mute;
                        $host['co_host_id'] = strval($host_data->id);
                        $host['co_host_status'] = strval('Accept');
                        $host['emoji'] = "0";
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
                            $row['co_host_id'] = strval($call->co_host_id);
                             $row['co_host_status'] = strval($call->is_co_host_active);
                             $row['emoji'] = "0";
                             $row['super_mute'] = strval($call->super_mute);
                            array_push($list, $row);
                        }
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
                        $total_withdraw=Withdraw::where('host_id',$host_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->sum('total');
                        $total_gift_sum= $total_gift_coin-$total_withdraw;
			             $today_gift = Gift::where('reciever_id',$host_id)
			                  ->whereDate('date', now()->toDateString())
			                  ->sum('value');

			                   $next_level_amount=50000;
                                $star = 1;
                
                                if ($today_gift < 50000) {
                                    $star = 1;
                                    $next_level_amount=50000;
                                } elseif ($today_gift >= 50000 && $today_gift < 200000) {
                                    $star = 2;
                                    $next_level_amount=200000;
                                } elseif ($today_gift >= 200000 && $today_gift < 500000) {
                                    $star = 3;
                                    $next_level_amount=500000;
                                } elseif ($today_gift >= 500000 && $today_gift < 1000000) {
                                    $star = 4;
                                    $next_level_amount=1000000;
                                } elseif ($today_gift >= 1000000 && $today_gift < 2000000) {
                                    $star = 5;
                                    $next_level_amount=2000000;
                                } elseif ($today_gift >= 2000000) {
                                    $next_level_amount=20000000;
                                    $star = 5; // Adjusted for values equal to or greater than 2000000
                                }
                                 $need_parcent=intval($today_gift/$next_level_amount*100);
			         
                           
			               array_push($response,array('message'=>'Audio Call Accept List Data Show Successfully come from  call mute ','host_list'=>$list,'set_remove'=>11,'host_balance'=>$total_gift_sum,'star_complete_parcent'=>$need_parcent,'star'=>$star,'channelName'=>$channelName,'siteNumber'=>strval($maxSeats),'code'=>'200'));
                             
                             $roomName='audio_call_host_list';
                           // self::RealTime($response,$roomName,$channelName);
                               array_push($websocket_call,array('message'=>'bd_audio_call','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'audio.room.snapshot'));
                            self::Websoket($websocket_call);
			               return json_encode($response,JSON_UNESCAPED_UNICODE);
            		}else{
            	     array_push($response,array('message'=>'Host Live Not Actived','code'=>'401'));
                     return json_encode($response,JSON_UNESCAPED_UNICODE);
            		}
		        }
            }else{
            array_push($response,array('message'=>'Already Cross Set Limit','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
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
    private function normalizedAudioRoomSeatCount($value, $default = 8)
    {
        $seatCount = intval($value);
        if ($seatCount < 2) {
            $seatCount = intval($default);
        }
        if ($seatCount < 2) {
            $seatCount = 8;
        }
        return max(2, min($seatCount, 15));
    }

    private function audioRoomMaxSeats($live, Request $request)
    {
        if (!$live) {
            $requestedSeatCount = $request->siteNumber ?? $request->site_number ?? $request->seatNumber ?? $request->seat_number;
            return $this->normalizedAudioRoomSeatCount($requestedSeatCount);
        }

        $storedSeatCount = Cache::remember('queenlive_audio_room_max_seats_' . $live->channelName, 30, function () use ($live) {
            foreach (array('siteNumber', 'site_number', 'seatNumber', 'seat_number') as $column) {
                if (isset($live->{$column}) && intval($live->{$column}) >= 2) {
                    return $this->normalizedAudioRoomSeatCount($live->{$column});
                }
            }
            return null;
        });
        if ($storedSeatCount !== null) {
            return $storedSeatCount;
        }

        $requestedSeatCount = $request->siteNumber ?? $request->site_number ?? $request->seatNumber ?? $request->seat_number;
        return $this->normalizedAudioRoomSeatCount($requestedSeatCount);
    }

    private function nextAvailableAudioSeat($host_id, $channelName, $maxSeats)
    {
        $maxSeats = $this->normalizedAudioRoomSeatCount($maxSeats);
        $occupiedSeats = LiveCall::where('host_id',$host_id)
            ->where('channelName',$channelName)
            ->where('status','Accept')
            ->pluck('set_no')
            ->map(function ($seatNo) {
                return intval($seatNo);
            })
            ->filter(function ($seatNo) {
                return $seatNo > 0;
            })
            ->values()
            ->all();

        for ($seatNo = 1; $seatNo <= $maxSeats; $seatNo++) {
            if (!in_array($seatNo, $occupiedSeats)) {
                return $seatNo;
            }
        }

        return null;
    }

    private function Websoket($data) {
        try {
            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped($data, ['source' => 'HostCallController']);
        } catch (\Throwable $th) {
            info('HostCall named websocket exception: ' . $th->getMessage());
        }
    }

    private function publishHostCallFanoutTargets($channels, $data)
    {
        $targets = $this->hostCallFanoutTargets();
        if (empty($targets)) {
            return;
        }

        foreach ($targets as $target) {
            try {
                $this->publishHostCallFanoutTarget($target, $channels, $data);
                info('HostCall cross-node websocket broadcast target: ' . $target['label']);
            } catch (\Throwable $th) {
                info('HostCall cross-node websocket exception target ' . $target['label'] . ': ' . $th->getMessage());
            }
        }
    }

    private function hostCallFanoutTargets()
    {
        $rawTargets = getenv('QueenLive_PUSHER_FANOUT_TARGETS');
        if (!$rawTargets) {
            $rawTargets = env('QueenLive_PUSHER_FANOUT_TARGETS', '');
        }
        if (!$rawTargets) {
            $rawTargets = getenv('HOST_CALL_PUSHER_FANOUT_TARGETS');
        }
        if (!$rawTargets) {
            $rawTargets = env('HOST_CALL_PUSHER_FANOUT_TARGETS', '');
        }
        if (!$rawTargets) {
            $rawTargets = 'https://queenlive.site/apps';
        }

        $targets = array();
        foreach (explode(',', $rawTargets) as $rawTarget) {
            $rawTarget = trim($rawTarget);
            if ($rawTarget === '') {
                continue;
            }

            $hostHeader = null;
            if (strpos($rawTarget, '|') !== false) {
                $parts = explode('|', $rawTarget, 2);
                $rawTarget = trim($parts[0]);
                $hostHeader = trim($parts[1]);
            }

            if (strpos($rawTarget, '://') === false) {
                $rawTarget = 'http://' . $rawTarget;
            }

            $parsed = parse_url($rawTarget);
            if (!$parsed || empty($parsed['host'])) {
                continue;
            }

            $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'http';
            $port = isset($parsed['port']) ? intval($parsed['port']) : ($scheme === 'https' ? 443 : 80);
            $path = isset($parsed['path']) && $parsed['path'] !== '' ? rtrim($parsed['path'], '/') : '/apps';
            $targets[] = array(
                'scheme' => $scheme,
                'host' => $parsed['host'],
                'port' => $port,
                'path' => $path,
                'host_header' => $hostHeader,
                'label' => $parsed['host'] . ':' . $port . $path,
            );
        }

        return $targets;
    }

    private function publishHostCallFanoutTarget($target, $channels, $data)
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('curl extension missing');
        }

        $appId = config('broadcasting.connections.pusher.app_id');
        $key = config('broadcasting.connections.pusher.key');
        $secret = config('broadcasting.connections.pusher.secret');
        $path = $target['path'] . '/' . $appId . '/events';

        $dataEncoded = json_encode($data);
        $postValue = json_encode(array(
            'name' => 'room.updated',
            'data' => $dataEncoded,
            'channels' => array_values($channels),
        ));

        if ($dataEncoded === false || $postValue === false) {
            throw new \RuntimeException('payload json encode failed');
        }

        $queryParams = array('body_md5' => md5($postValue));
        $signedParams = Pusher\Pusher::build_auth_query_params($key, $secret, 'POST', $path, $queryParams);
        $url = $target['scheme'] . '://' . $target['host'] . ':' . $target['port'] . $path . '?' . http_build_query(array_merge($signedParams, $queryParams));

        $headers = array(
            'Content-Type: application/json',
            'X-Pusher-Library: queenlive-host-call-fanout',
        );
        if (!empty($target['host_header'])) {
            $headers[] = 'Host: ' . $target['host_header'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postValue);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException($curlError);
        }
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('HTTP ' . $status . ' ' . substr($body, 0, 120));
        }
    }
}
