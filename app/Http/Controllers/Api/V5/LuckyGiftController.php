<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use Kreait\Firebase\Contract\Database;
use Pusher;
use Carbon;
use DB;
use App\Models\LiveCall;
use App\Models\LuckyGiftSetting;
use App\Models\User;
use App\Models\UserLive;
class LuckyGiftController extends Controller
{
     public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function Store(Request $request)
    {
        $response = array();
        $pusher_response = array();
          $token = $request->access_token;
        $user_id = $request->user_id;
        $value = $request->value;
        $gift_name = $request->giftName;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        $gift_type = $request->gift_type;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
               $i=0;
              $data = json_decode($request->getContent(), true); // Assuming the request is sent as JSON
                $reward=0;
                if (isset($data['items']) && is_array($data['items'])) {
                     $push_call_request_ref = $this->database->getReference('gifts/' . $channelName);
		            $push_call_request_ref->remove();
		            $svga_remove = $this->database->getReference('svga_videogifts/' . $channelName);
		            $svga_remove->remove();
                     foreach ($data['items'] as $row) {
                   // return $row['value'];
                    $sander = User::find($user_id);
                    if ($sander && $sander->balance >=$value) {
                       $reward=0;
                       if($value==500){
                        $host_amount=300;   
                        $game_balance=200;
                        $for_five_h=[0,500,400,600,1000,2000,3000,5000,0,6000];
                        $reward = $for_five_h[array_rand($for_five_h)];
                       }elseif($value==1000){
                        $host_amount=700;   
                        $game_balance=300;
                        $for_one_thousand=[0,1000,1500,2500,3700,4250,5700,0,8000,8888];
                        $reward = $for_one_thousand[array_rand($for_one_thousand)];
                       }elseif($value==1500){
                        $host_amount=1000;   
                        $game_balance=500;
                        $for_third_thousand=[0,1000,1500,2700,4700,6250,8700,0,9000,10888];
                        $reward = $for_third_thousand[array_rand($for_third_thousand)];
                       }elseif($value==3000){
                        $host_amount=2200;   
                        $game_balance=800;
                         $for_three_thousand=[0,2000,2500,3700,4700,12250,16000,0,37000,30888,40000];
                        $reward = $for_three_thousand[array_rand($for_three_thousand)];
                       }else{
                        $host_amount=$value;   
                        $game_balance=0;
                       }
                        
                        // P0-B3 FIX (Agent SF 2026-06-28): unlocked User::find + lucky
                        // pool with gift-saved-before-debit ordering and a reward
                        // double-credit risk (no lock/txn). Atomic locked debit,
                        // gift row, pool credit, and reward credit in one txn.
                        try {
                            $txn = DB::transaction(function () use ($user_id, $row, $gift_name, $host_amount, $channelName, $value, $game_balance, $reward) {
                                $lockedSander = User::where('id', $user_id)->lockForUpdate()->first();
                                if (!$lockedSander || (float) $lockedSander->balance < $value) {
                                    throw new \RuntimeException('insufficient_balance');
                                }
                                $lockedPool = LuckyGiftSetting::where('id', 1)->lockForUpdate()->first();

                                $g = new Gift;
                                $g->sander_id = $user_id;
                                $g->reciever_id = $row['receiverId'];
                                $g->name = $gift_name;
                                $g->value = $host_amount;
                                $g->channelName = $channelName;
                                $g->date = Carbon\Carbon::now();
                                $g->save();

                                // Debit the gift cost and credit the lucky pool.
                                $lockedSander->balance -= $value;
                                if ($lockedPool) {
                                    $lockedPool->balance += $game_balance;
                                }

                                // Award reward only if the pool can cover it.
                                $awarded = 0;
                                if ($reward != 0 && $lockedPool && $lockedPool->balance > $reward) {
                                    $lockedPool->balance -= $reward;
                                    $lockedSander->balance += $reward;
                                    $awarded = $reward;
                                }

                                $lockedSander->save();
                                if ($lockedPool) {
                                    $lockedPool->save();
                                }

                                return ['gift' => $g, 'pool' => $lockedPool, 'reward' => $awarded];
                            });
                        } catch (\RuntimeException $e) {
                            // insufficient balance -> skip this lucky-gift item.
                            continue;
                        }
                        $gift = $txn['gift'];
                        $lucky_balance = $txn['pool'];
                        $reward = $txn['reward'];
                         
                        
                         $sender_name=User::find($user_id);
                         $receiver_name=User::find($row['receiverId']);
                         $forcomment[] = $receiver_name->name;
                        
                         $user=User::find($user_id);
                        
                        $music=0;
                     
                      	$balance = Gift::where('reciever_id', $gift->reciever_id)->where('channelName',$channelName)->sum('value');
                    	$audience = User::find($user_id);
                   		 $key = $channelName;
                    	$count = [
                        'channelName' => $channelName,
                         'name' =>  $gift->name,
                        'host_balance' => strval($balance),
                        'audience_balance' => strval($audience->balance),
                        'music' => strval($music),
                        'status' => 'active',
                    	];

                      $push_count_ref = $this->database->getReference('gifts/' . $key . '/linda/'.$gift->reciever_id);
                      $push_count_ref->set($count);
                       $count = [
                        'channelName' => $channelName,
                         'name' =>  $gift->name,
                        'host_balance' => strval($balance),
                        'music' => strval($music),
                        'audience_balance' => strval($audience->balance),
                        'reciever_id' => strval($gift->reciever_id),
                        'status' => 'active',
                        'gift_type' => strval($gift_type),
                      ];
                      $new_gift_push = $this->database->getReference('svga_audiogifts/' . $key . '/linda/'.$gift->reciever_id);
                     $new_gift_push->set($count);
                      
                      
                   
                    
                    } else {
                       // array_push($response, array('message' => 'Balance Insufficient for Gift: ' . $gift_name, 'code' => '401'));
                    }
                }
              
                 $sander_user = User::find($user_id);
                $all_receiver_names = implode(', ', $forcomment);
                $commnet_message = "$sender_name->name sent Lucky $value to $all_receiver_names";
                
                $gift_comment = [
                    'balance' => strval($sender_name->balance),
                    'channelName' => strval($channelName),
                    'id' => $sender_name->id,
                    'message' => strval('@'.$commnet_message),
                    'level' => strval($sender_name->level),
                    'name' => strval($sender_name->name),
                    'profile' => strval($sender_name->profile),
                    'is_vip' => strval($sender_name->is_vip),
                    'is_vip' => strval($sender_name->is_vip),
                   'is_official_id' => strval($sender_name->is_official_id),
                    'is_agency' => strval($sender_name->is_agency),
                    'is_host_id' => strval($sender_name->is_host_id),
                    'type' => "message",
                ];
                
                $push_gift_comment = $this->database->getReference('Comments/'.$channelName );
                    $push_gift_comment->push($gift_comment);
            $accept_list=LiveCall::where('channelName',$channelName)->where('host_id',$host_id)->where('status','Accept')->get();

            $host_data=User::find($host_id);
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
			$list = array();

            $co_host_list = array();
            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
            $host['is_vip'] = $host_data->is_vip;
            $host['balance'] = Gift::where('reciever_id', $host_data->id)->where('channelName',$channelName)->sum('value');
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
            $host['mute'] = 1;
            $host['co_host_id'] = strval($host_data->id);
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
                array_push($list, $row);
                array_push($co_host_list, $row);
                }

               
                $date = Carbon\Carbon::now(); // Replace this with your desired date

            if ($date->day <= 15) {
                 $start_date = date('Y-m') . '-01';
                $end_date = date('Y-m') . '-15';
                
               $query = DB::table('gifts')
                    ->join('users', 'users.id', '=', 'gifts.sander_id')
                    ->whereDate('gifts.date', '>=', $start_date)
                    ->whereDate('gifts.date', '<=', $end_date) 
                    ->where('gifts.reciever_id',$host_id)
                    ->select('users.profile', 'users.name', 'users.id', 'users.level', 'gifts.value');

                $total_gift_sum = $query->sum('value');
            } else {
                $start_date = date('Y-m') . '-16';
                $end_date = date('Y-m') . '-31';
                
                $query = DB::table('gifts')
                    ->join('users', 'users.id', '=', 'gifts.sander_id')
                   ->whereDate('gifts.date', '>=', $start_date)
                   ->whereDate('gifts.date', '<=', $end_date) 
                   ->where('gifts.reciever_id',$host_id)
                ->select('users.profile', 'users.name', 'users.id', 'users.level', 'gifts.value');
                $total_gift_sum = $query->sum('value');
            }
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
               array_push($pusher_response,array('message'=>'lucky Gift data ','host_list'=>$list,'co_host_list'=>$co_host_list,'host_balance'=>$total_gift_sum,'star'=>$star,'star_complete_parcent'=>$need_parcent,'code'=>'200'));
              // Was `$pusher->trigger(...)` on an UNDEFINED $pusher (no client was
              // ever instantiated) -> fatal "trigger() on null" whenever this path
              // ran. Replaced with the async queue job, which carries its own
              // Pusher client. Same channel/event/data, now off the request thread.
              \App\Jobs\PublishRoomChannelEventJob::dispatch(['video_call_host_list'], $channelName, $pusher_response);
               if($reward!=0){
                  
                array_push($response, array('message' => 'Congratulations! You Win $reward Points','user_id'=>$sander_user->id,'balance'=>$sander_user->balance,'reaward'=>$reward,'code' => '200'));
               }else{
                 array_push($response, array('message' => 'Lucky Gifts Sent Successfully','user_id'=>$sander_user->id,'balance'=>$sander_user->balance,'reaward'=>0,'code' => '200'));  
               }
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                array_push($response, array('message' => 'Must Send at Least One Gift', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    private function RealTime($response,$roomName)
    {
      $channel_id = "dek61rVgyOVxddHC";
       $channel_secret = "Twh6kh9EPag77ubM4ZMznNL5fsGgcPJ7";
       $room_name = $roomName;

        $auth = base64_encode("$channel_id:$channel_secret");

        $url = "https://api2.scaledrone.com/$channel_id/$room_name/publish";
        $data = $response;
        $options = array(
            'http' => array(
                'header'  => array(
                    "Content-type: application/json",
                    "Authorization: Basic $auth",
                ),
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
    }
}
