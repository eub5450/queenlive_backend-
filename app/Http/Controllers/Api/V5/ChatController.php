<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use DB;
use Carbon;
use Pusher;
use App\Models\User;
use App\Models\OldGift;
use App\Models\Chat;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
class ChatController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
     public function Gift(Request $request)
    {
        $token = $request->access_token;
        $user_id = $request->user_id;
        $value = $request->value;
        $gift_name = $request->giftName;
        $gift_type = $request->gift_type;
        $host_id = $request->host_id;
        $response = array();

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
             // ATOMIC sender debit + Gift insert. lockForUpdate inside a
             // DB::transaction blocks concurrent Gift calls from this same
             // user — closes the race window that previously let two gifts
             // both pass the balance check and overspend the wallet.
             $__txn = ['ok' => false, 'insufficient' => false];
             DB::transaction(function () use ($user_id, $value, $gift_name, $host_id, &$__txn) {
                 $sander = User::lockForUpdate()->find($user_id);
                 if (!$sander) { return; }
                 if ((int) $sander->balance < (int) $value) {
                     $__txn['insufficient'] = true;
                     return;
                 }
                 $sander->balance = (int) $sander->balance - (int) $value;
                 $sander->save();
                 $g = new Gift;
                 $g->sander_id = $user_id;
                 $g->reciever_id = $host_id;
                 $g->name = $gift_name;
                 $g->value = $value;
                 $g->channelName = 'chat_gifiting';
                 $g->date = Carbon\Carbon::now();
                 $g->save();
                 $__txn['ok'] = true;
             });
             if ($__txn['insufficient']) {
                 array_push($response, array('message' => 'Balance Insufficient for Gift', 'code' => '401'));
                 return json_encode($response, JSON_UNESCAPED_UNICODE);
             }
             if (!$__txn['ok']) {
                 array_push($response, array('message' => 'Gift failed', 'code' => '500'));
                 return json_encode($response, JSON_UNESCAPED_UNICODE);
             }

             // Vestigial structure preserved so existing level-calc + response
             // block runs unchanged. $sander is re-loaded (post-debit) for any
             // downstream reads.
             $sander = User::find($user_id);
             if (true) {
                 // no-op stand-ins for the original debit + Gift insert (now
                 // done atomically above)
                 $gift = new \stdClass;
                 $gift->value = $value;
                       
                        $user=User::find($user_id);
                        $gift_sand = Gift::where('sander_id', $user_id)->sum('value');
                        $old_gift_sand = OldGift::where('sander_id', $user_id)->sum('value');
                        $total=$old_gift_sand+$gift_sand;
                         if ($total > 0) {
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
                         array_push($response, array('message' => 'User Sand A gift Value :- '.$value, 'code' => '200'));
                         return json_encode($response, JSON_UNESCAPED_UNICODE);
                    } else {
                        array_push($response, array('message' => 'Balance Insufficient for Gift', 'code' => '401'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
        public function Store(Request $request)
    {
         $token = $request->access_token;
        $sander_id = $request->sander_id;
        $message = $request->message;
        $receiver_id = $request->receiver_id;
        $response = array();

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $data=new Chat;
            $data->sander_id=$sander_id;
            $data->receiver_id=$receiver_id;
            $data->text=$message;
            $data->save();
            $user=User::find($receiver_id);
             $notificationBody = $message;
             $title = $user->name." Sand A Message.";
                
                 if (empty($user->device_id)) {
  
    return; // Skip sending the notification
}
                 
                  try {
                        $pusher = new \Pusher\Pusher(
                            config('broadcasting.connections.pusher.key'),
                            config('broadcasting.connections.pusher.secret'),
                            config('broadcasting.connections.pusher.app_id'),
                            ['cluster' => config('broadcasting.connections.pusher.options.cluster'), 'useTLS' => true]
                        );
                        $pusher->trigger(
                            'notification-' . $receiver_id,
                            'chat.message.created',
                            [
                                'event_type' => 'chat.message.created',
                                'sender_id'    => $sander_id,
                                'sender_name'  => $user->name ?? '',
                                'message'      => $notificationBody,
                            ]
                        );
                    } catch (\Throwable $e) {
                        // silently skip
                    }
                
                    
                   
                    
            array_push($response, array('message' => 'Successfully Store', 'code' => '200'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
}
