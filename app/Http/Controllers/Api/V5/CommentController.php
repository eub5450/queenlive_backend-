<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Gift;
use App\Models\User;
use App\Models\AudienceJoin;
use App\Models\UserLive;
use App\Models\LiveCall;
use App\Models\DayTime;
use App\Models\Setting;
use App\Models\Kick;
use App\Models\CommentMute;
use App\Models\Follower;
use Kreait\Firebase\Contract\Database;
use DB;
use App\Models\BrdAdmin;
use Carbon;
use Pusher;
use Illuminate\Support\Facades\Cache;
class CommentController extends Controller
{
    private const AUDIENCE_CACHE_SECONDS = 3;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    private function audienceDirectoryCacheKey($channelName)
    {
        return 'queenlive:v4:audience_directory:' . trim((string) $channelName);
    }

    private function audienceSummaryCacheKey($channelName)
    {
        return 'queenlive:v4:audience_summary:' . trim((string) $channelName);
    }

    private function forgetAudienceCaches($channelName)
    {
        $normalizedChannel = trim((string) $channelName);
        if ($normalizedChannel === '') {
            return;
        }

        $redisCache = Cache::store('redis');
        $redisCache->forget($this->audienceDirectoryCacheKey($normalizedChannel));
        $redisCache->forget($this->audienceSummaryCacheKey($normalizedChannel));
    }

    private function cachedAudienceDirectoryPayload($channelName)
    {
        $normalizedChannel = trim((string) $channelName);
        if ($normalizedChannel === '') {
            return array(
                'data' => array(),
                'vip_data' => array(),
                'admin_data' => array(),
            );
        }

        return Cache::store('redis')->remember(
            $this->audienceDirectoryCacheKey($normalizedChannel),
            now()->addSeconds(self::AUDIENCE_CACHE_SECONDS),
            function () use ($normalizedChannel) {
                $data = DB::table('audience_joins')
                    ->join('users', 'users.id', 'audience_joins.user_id')
                    ->where('users.is_vip', 0)
                    ->where('audience_joins.admin_power', 0)
                    ->select('users.name', 'users.level', 'users.id', 'users.profile', 'users.is_vip', 'users.frame', 'audience_joins.admin_power')
                    ->where('audience_joins.channelName', $normalizedChannel)
                    ->orderby('audience_joins.id', 'desc')
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->values()
                    ->all();

                $vip_data = DB::table('audience_joins')
                    ->join('users', 'users.id', 'audience_joins.user_id')
                    ->where('users.is_vip', '!=', 0)
                    ->where('audience_joins.admin_power', 0)
                    ->select('users.name', 'users.level', 'users.id', 'users.profile', 'users.is_vip', 'users.frame', 'audience_joins.admin_power')
                    ->where('audience_joins.channelName', $normalizedChannel)
                    ->orderby('audience_joins.id', 'desc')
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->values()
                    ->all();

                $admin_data = DB::table('audience_joins')
                    ->join('users', 'users.id', 'audience_joins.user_id')
                    ->where('audience_joins.admin_power', '!=', 0)
                    ->select('users.name', 'users.level', 'users.id', 'users.profile', 'users.is_vip', 'users.frame', 'audience_joins.admin_power')
                    ->where('audience_joins.channelName', $normalizedChannel)
                    ->orderby('audience_joins.id', 'desc')
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->values()
                    ->all();

                return array(
                    'data' => $data,
                    'vip_data' => $vip_data,
                    'admin_data' => $admin_data,
                );
            }
        );
    }

    private function cachedAudienceSummaryPayload($channelName)
    {
        $normalizedChannel = trim((string) $channelName);
        if ($normalizedChannel === '') {
            return array(
                'count_inlive' => 0,
                'audience_list' => array(),
                'admin_list' => array(),
            );
        }

        return Cache::store('redis')->remember(
            $this->audienceSummaryCacheKey($normalizedChannel),
            now()->addSeconds(self::AUDIENCE_CACHE_SECONDS),
            function () use ($normalizedChannel) {
                $count_inlive = AudienceJoin::where('channelName', $normalizedChannel)->count();
                $audience_list = DB::table('audience_joins')
                    ->join('users', 'users.id', 'audience_joins.user_id')
                    ->where('audience_joins.channelName', $normalizedChannel)
                    ->select('users.profile', 'users.is_vip', 'users.frame')
                    ->orderby('users.is_vip', 'desc')
                    ->limit(2)
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->values()
                    ->all();

                $admin_list = DB::table('audience_joins')
                    ->join('users', 'users.id', 'audience_joins.user_id')
                    ->where('audience_joins.channelName', $normalizedChannel)
                    ->where('audience_joins.admin_power', '!=', 0)
                    ->select('users.profile', 'users.frame', 'users.id', 'audience_joins.admin_power')
                    ->orderby('audience_joins.admin_power', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->values()
                    ->all();

                return array(
                    'count_inlive' => $count_inlive,
                    'audience_list' => $audience_list,
                    'admin_list' => $admin_list,
                );
            }
        );
    }

    public function Store(Request $request)
    {
     
         $token = $request->access_token;
         $user_id = $request->user_id;
         $reciever_id = $request->reciever_id;
         $channelName = $request->channelName;
         $type = $request->type;
         $message = $request->message;
         $gift_name = $request->gift_name;
         $gift_value = $request->gift_value;
         $date = Carbon\Carbon::now();
        $response = array();
      $global_banner = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
          
            $data=new Comment;
            $data->user_id=$user_id;
            $data->channelName=$channelName;
            $data->message=$message;
            $data->reciever_id=$reciever_id;
            $data->type=$type;
            if($request->brd_type==2){
            $data->gift_name=$gift_name;
            $data->gift_value=$gift_value;
            }
            //$data->date=$date;
          if ($request->type!='gift') {
            $data->save();
          }
          if($message!='Has Joined'){
            self::commentWebsoketHit($channelName,$reciever_id);
          }
        
       self::CheckIDBadge($user_id,$channelName,$reciever_id);
        
        $audience=User::find($user_id);
          $host=User::find($reciever_id);
            $list_view=DB::table('comments')->join('users','users.id','comments.user_id')->select('users.name','users.id','users.profile','comments.message','comments.type','comments.channelName','users.balance','users.level','users.is_vip','users.is_official_id','users.is_agency','users.is_host_id','users.frame','users.comment_badge')->where('comments.channelName',$channelName)->get();
            
      $count_inlive=AudienceJoin::where('channelName',$channelName)->count();
         // $audience_list=AudienceJoin::where('channelName',$channelName)->select('profile')->limit(4)->get();
            $audience_list=DB::table('audience_joins')->join('users','users.id','audience_joins.user_id')->where('audience_joins.channelName',$channelName)->select('users.profile','users.is_vip','users.frame')->orderby('users.is_vip','desc')->limit(2)->get();
          $key = $channelName;
          
          $use=$count_inlive+rand(1, 15);
          $count=[
           'count'=>strval($use),
            'host_name'=>$host->name,
           ];
        
           
            array_push($response,array('message'=>'Comment Successfully ','data'=>$list_view,'audience_balance'=>$audience->balance,'code'=>'200'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function FlyComment(Request $request){
        $token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
         $message = $request->message;
         $response = array();
         $websoket_entry = array();
        $global_txt = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
             $user=User::find($user_id);
               if($user){
                           
             array_push($global_txt,array('message'=>$message,'image'=>$user->profile,'name'=>$user->name,'channelName'=>$channelName));
             $roomName='fly_comment';
            // self::RealTime($global_txt,$roomName,$channelName);
             array_push($websoket_entry,array('message'=>'bd_fly','channelName'=>$channelName,'data'=>$global_txt,'code'=>'200','event_type' => 'room.comment.flying'));
            self::Websoket($websoket_entry);
             array_push($response,array('message'=>'Successfully Fly Comment Show','code'=>'200'));
             return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function CheckEntry(Request $request){
        $token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
         $response = array();
        $global_txt = array();
        $websoket_entry = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
             $user=User::find($user_id);
            $audience=AudienceJoin::where('channelName',$channelName)->where('user_id',$user_id)->first();
             if($audience){
                        if($user->is_invisible_active==0 && $audience->entry_show==0){ // Boss 2026-06-27: show join comment for all non-invisible users (was gated on paid entry-effect only)
                        $message = "$user->name Arrived";
                      
                         // D: carry the joiner's power-pill fields so audio (and all rooms) render
                         // the official/badge/level pill on entry. decodeLiveCommentData reads
                         // name/level/is_official_id/comment_badge from this payload.
                         array_push($global_txt,array('message'=>$message,'image'=>$user->profile,'name'=>$user->name,'level'=>$user->level,'is_official_id'=>$user->is_official_id,'comment_badge'=>$user->comment_badge,'vip_lavel'=>$user->is_vip,'channelName'=>$channelName,'entry_effect'=>$user->entry));
                          $roomName='entry_effect_realtime';
                     //   self::RealTime($global_txt,$roomName,$channelName);
                        array_push($websoket_entry,array('message'=>'bd_entry','channelName'=>$channelName,'data'=>$global_txt,'code'=>'200','event_type' => 'room.member.entered'));
                        self::Websoket($websoket_entry);
                          array_push($response,array('message'=>'Successfully Entry SHow','code'=>'200'));
                           $audience->entry_show=1;
                            $audience->save();
                         return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
                }
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function JoinStore(Request $request)
    {
         $token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
         $type = $request->type;
         $message = $request->message;
         $reciever_id = $request->reciever_id;
         $date = Carbon\Carbon::now();
        $response = array();
        $global_txt = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){

           $audience_data=AudienceJoin::where('user_id',$user_id)->first();
          if($audience_data){
            $audience_data->delete();
          }
          // Boss 2026-06-27: any LiveCall row the user left behind in this same
          // room is stale on a fresh join (they either explicitly exited or were
          // disconnected long enough that a re-join is happening). Drop it so the
          // host snapshot doesn't keep showing them as cohost and the next
          // 'Be Cohost' tap broadcasts a fresh request.
          // Boss 2026-06-28 refined: only nuke PENDING rows. Accept rows mean the user
          // is currently a seated cohost (or just got accepted) — deleting that one
          // wipes them from the host's seat list mid-session, which is the new bug
          // we just hit during the 4-loop drive. Pending rows are still cleared so
          // the original 'exit+rejoin then Be Cohost' fix still works.
          \App\Models\LiveCall::where('co_host_id', $user_id)
              ->where('channelName', $channelName)
              ->where('status', 'pending')
              ->delete();
          $check=Kick::where('user_id',$user_id)->where('channelName',$channelName)->first();
          if (!$check) {
            $user=User::find($user_id);
            if($user){
                $check_admin=BrdAdmin::where('admin_id',$user_id)->where('user_id',$reciever_id)->first();
                $audience=new AudienceJoin;
                $audience->user_id=$user_id;
                $audience->host_id=$reciever_id;
                if($check_admin){
                    $audience->admin_power=$check_admin->type;
                }else{
                    $audience->admin_power=0;
                }
                $audience->channelName=$channelName;
                $audience->profile=$user->profile;
                $audience->save();
                 
            }
            $this->forgetAudienceCaches($channelName);
            self::CheckIDBadge($user_id,$channelName,$reciever_id);
        $follow=Follower::where('user_id',$user_id)->where('follower_id',$reciever_id)->first();
        if($follow){
            //Yes Following
            $friend=Follower::where('user_id',$reciever_id)->where('follower_id',$user_id)->first();
            if( $friend){
               $is_i_follow=2;
            }else{
              // NOt Friend
              $is_i_follow=1;
            }
          }
          else{
            // Not Following
            $is_i_follow=0;
          }
          
          self::commentWebsoketHit($channelName,$reciever_id);
             
            $list_view=DB::table('comments')->join('users','users.id','comments.user_id')->select('users.name','users.profile','comments.message','comments.type','comments.channelName','users.is_vip','users.level','users.frame')->where('comments.channelName',$channelName)->first();
            $audienceSummary = $this->cachedAudienceSummaryPayload($channelName);
            $count_inlive = $audienceSummary['count_inlive'];
            $audience_list = $audienceSummary['audience_list'];
          $host=User::find($reciever_id);
           $key = $channelName;
          $use=$count_inlive+rand(1, 15);
          $count=[
           'count'=>strval($use),
            'host_name'=>$host->name,
           ];
           
           //self::commentWebsoketHit($channelName,$reciever_id);
           
          
          $host_name=User::find($reciever_id);
           $live_is_running=UserLive::where('user_id',$reciever_id)->where('channelName',$channelName)->first();
          $day_time_last_update=1;
          $now = Carbon\Carbon::now(); // Current timestamp
          $sevenMinutesAgo = Carbon\Carbon::now()->subMinutes(6);
          
          $admin_list=DB::table('audience_joins')->join('users','users.id','audience_joins.user_id')->where('audience_joins.channelName',$channelName)->where('audience_joins.admin_power','!=',0)->select('users.profile','users.frame','users.id','audience_joins.admin_power')->orderby('audience_joins.admin_power','desc')->limit(3)->get();
         
          array_push($response,array('message'=>'Comment Successfully ','data'=>$list_view,'count_inlive'=>$count_inlive,'audience_list'=>$audience_list,'host_name'=>$host_name->name,'is_i_follow'=>$is_i_follow,'code'=>'200','day_time_last_update'=>$day_time_last_update));
            

           
          
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
            array_push($response,array('message'=>'Your Are Kick From This Room','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }


        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function CheckIDBadge($user_id,$channelName,$reciever_id){
        $check_user=User::find($user_id);
        $check_user->comment_badge=0;
            $check_user->save();
          $startOfMonth = Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Query to get the total sum of gifts for each sander_id with the same reciever_id between the start and end of the month
        $topStores = DB::table('gifts')
            ->select(DB::raw('SUM(value) as total_sum, reciever_id, sander_id'))
            ->where('reciever_id', $reciever_id)  // We're looking for the same receiver
            ->whereDate('gifts.date', '>=', $startOfMonth)
             ->whereDate('gifts.date', '<=', $endOfMonth) 
            ->groupBy('reciever_id', 'sander_id')
            ->orderBy('total_sum', 'desc')
            ->limit(3)
            ->get();
            
            $badge = '0';  // Default badge value
            $check_user->comment_badge=$badge;
            $check_user->save();
        foreach ($topStores as $index => $store) {
            if ($store->sander_id == $user_id && $store->total_sum > 0) {
                // Assign the badge based on the position (index starts at 0)
                if ($index == 0) {
                    $badge = 'Top 1';
                    $check_user->comment_badge=$badge;
            $check_user->save();
                } elseif ($index == 1) {
                    $badge = 'Top 2';
                    $check_user->comment_badge=$badge;
            $check_user->save();
                } elseif ($index == 2) {
                    $badge = 'Top 3';
                    $check_user->comment_badge=$badge;
                    $check_user->save();
                }
                break;  // Exit loop once the match is found
            }
        }
        

         $admin_list=DB::table('audience_joins')->join('users','users.id','audience_joins.user_id')->where('audience_joins.channelName',$channelName)->where('audience_joins.user_id',$user_id)->first();
         if($admin_list){
             if($admin_list->admin_power!=0){
                 $check_user->comment_badge="Admin";
                 $check_user->save();
             }
         }elseif($check_user->is_official_id!=0){
             $check_user->comment_badge='Official';
            $check_user->save(); 
         }elseif($check_user->is_agency!=0){
             $check_user->comment_badge='Merchant';
            $check_user->save(); 
         }else{
              $check_user->comment_badge=0;
            $check_user->save();
         }
    }
     public function GiftPush(Request $request)
    {
         $token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
         $name = $request->gift_name;
         $reciever_id = $request->reciever_id;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
       
                
          $balance=Gift::where('reciever_id',$reciever_id)->sum('value');
      $audience=User::find($user_id);
      if($request->brd_type==2){
          $key = $channelName;
          $count=[
           'name'=>$name,
           'channelName'=>$channelName,
            'host_balance'=>strval($balance),
            'audience_balance'=>strval($audience->balance),
             'status'=>'active',
           ];
          //$push_count_ref = $this->database->getReference('gifts/' . $key);
            //$push_count_ref->set($count);
          $push_count_ref = $this->database->getReference('gifts/' . $key .'/linda');
        $push_count_ref->set($count);
      }

          
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
        $response = array();

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
      
                
          //   array_push($response, array('message' => 'Gifts Sent Successfully','data'=>$request->items, 'code' => '200'));

              $data = json_decode($request->getContent(), true); // Assuming the request is sent as JSON

                if (isset($data['items']) && is_array($data['items'])) {
                     $push_call_request_ref = $this->database->getReference('audiogifts/' . $channelName);
                $push_call_request_ref->remove();
                     foreach ($data['items'] as $row) {
                   // return $row['value'];
                    // ATOMIC per-item sender debit + Gift insert.
                    // lockForUpdate inside DB::transaction blocks concurrent
                    // gift items from this sender — closes the race window
                    // that previously let two items both pass the balance
                    // check and overspend the wallet.
                    $__txn = ['ok' => false, 'insufficient' => false];
                    DB::transaction(function () use ($user_id, $value, $gift_name, $channelName, $row, &$__txn) {
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
                        $g->reciever_id = $row['receiverId'];
                        $g->name = $gift_name;
                        $g->value = $value;
                        $g->channelName = $channelName;
                        $g->date = Carbon\Carbon::now();
                        $g->save();
                        $__txn['ok'] = true;
                    });
                    if (!$__txn['ok']) {
                        // Insufficient (or transient txn failure) — skip the
                        // rest of THIS item's level-calc + push and move on
                        // to the next item in the loop, matching the legacy
                        // else-branch behavior.
                        continue;
                    }
                    // Vestigial structure so existing per-item level-calc +
                    // push block runs unchanged. $sander is re-loaded
                    // post-debit for any downstream reads.
                    $sander = User::find($user_id);
                    if (true) {
                        $gift = new \stdClass;
                        $gift->value = $value;
                        
                         $user=User::find($user_id);
                        $total = Gift::where('sander_id', $user_id)->sum('value');
                        
                        
                        if ($total > 0) {
                        $user = User::find($user_id);
                        $level = 1; // Starting level is 2
                        if($total == 50000){
                            $level = 2;
                        }
                        elseif ($total >= 50001 && $total < 100000) {
                            $level = 3;
                        } elseif ($total >= 100001 && $total < 150000) {
                            $level = 4;
                        } elseif ($total >= 150001 && $total < 200000) {
                            $level = 5;
                        } elseif ($total >= 200001 && $total < 400000) {
                            $level = 6;
                        } elseif ($total >= 400001 && $total < 600000) {
                            $level = 7;
                        } elseif ($total >= 600001 && $total < 800000) {
                            $level = 8;
                        } elseif ($total >= 800001 && $total < 1000000) {
                            $level = 9;
                        } elseif ($total >= 1000001 && $total < 1200000) {
                            $level = 10;
                        } elseif ($total >= 1200001 && $total < 2200000) {
                            $level = 11;
                        } elseif ($total >= 2200001 && $total < 3200000) {
                            $level = 12;
                        } elseif ($total >= 3200001 && $total < 4200000) {
                            $level = 13;
                        } elseif ($total >= 4200001 && $total < 5200000) {
                            $level = 14;
                        } elseif ($total >= 5200001 && $total < 6200000) {
                            $level = 15;
                        } elseif ($total >= 6200001 && $total < 8200000) {
                            $level = 16;
                        } elseif ($total >= 8200001 && $total < 10200000) {
                            $level = 17;
                        } elseif ($total >= 10200001 && $total < 12200000) {
                            $level = 18;
                        } elseif ($total >= 12200001 && $total < 14200000) {
                            $level = 19;
                        } elseif ($total >= 14200001 && $total < 16200000) {
                            $level = 20;
                        } elseif ($total >= 16200001 && $total < 19200000) {
                            $level = 21;
                        } elseif ($total >= 19200001 && $total < 22200000) {
                            $level = 22;
                        } elseif ($total >= 22200001 && $total < 25200000) {
                            $level = 23;
                        } elseif ($total >= 25200001 && $total < 28200000) {
                            $level = 24;
                        } elseif ($total >= 28200001 && $total < 31200000) {
                            $level = 25;
                        } elseif ($total >= 31200001 && $total < 40000000) {
                            $level = 26;
                        } elseif ($total >= 40000001 && $total < 50000000) {
                            $level = 27;
                        } elseif ($total >= 50000001 && $total < 60000000) {
                            $level = 28;
                        } elseif ($total >= 60000001 && $total < 70000000) {
                            $level = 29;
                        } elseif ($total >= 70000001 && $total < 80000000) {
                            $level = 30;
                        } elseif ($total >= 80000001 && $total < 100000000) {
                            $level = 31;
                        } elseif ($total >= 100000001 && $total < 120000000) {
                            $level = 32;
                        } elseif ($total >= 120000001 && $total < 140000000) {
                            $level = 33;
                        } elseif ($total >= 140000001 && $total < 160000000) {
                            $level = 34;
                        } elseif ($total >= 160000001 && $total < 180000000) {
                            $level = 35;
                        } elseif ($total >= 180000001 && $total < 200000000) {
                            $level = 36;
                        } elseif ($total >= 200000001 && $total < 220000000) {
                            $level = 37;
                        } elseif ($total >= 220000001 && $total < 240000000) {
                            $level = 38;
                        } elseif ($total >= 240000001 && $total < 260000000) {
                            $level = 39;
                        } elseif ($total >= 260000001 && $total < 280000000) {
                            $level = 40;
                        } elseif ($total >= 280000001 && $total < 330000000) {
                            $level = 41;
                        } elseif ($total >= 330000001 && $total < 380000000) {
                            $level = 42;
                        } elseif ($total >= 380000001 && $total < 430000000) {
                            $level = 43;
                        } elseif ($total >= 430000001 && $total < 480000000) {
                            $level = 44;
                        } elseif ($total >= 480000001 && $total < 530000000) {
                            $level = 45;
                        } elseif ($total >= 530000001 && $total < 580000000) {
                            $level = 46;
                        } elseif ($total >= 580000001 && $total < 630000000) {
                            $level = 47;
                        } elseif ($total >= 630000001 && $total < 680000000) {
                            $level = 48;
                        } elseif ($total >= 680000001 && $total < 730000000) {
                            $level = 49;
                        } else {
                              // For values greater than or equal to 780000000
                              $level = 50;
                          }
                            $user->level=$level;
                        
                          $user->save();
                        }
                        $balance = Gift::where('reciever_id', $gift->reciever_id)->where('channelName',$channelName)->sum('value');
                      $audience = User::find($user_id);
                       $key = $channelName;
                      $count = [
                        'channelName' => $channelName,
                         'name' =>  $gift->name,
                        'host_balance' => strval($balance),
                        'audience_balance' => strval($audience->balance),
                        'status' => 'active',
                      ];

                     $push_count_ref = $this->database->getReference('audiogifts/' . $key . '/linda/'.$gift->reciever_id);
                    $push_count_ref->set($count);
                   
                    
                    } else {
                        array_push($response, array('message' => 'Balance Insufficient for Gift: ' . $gift_name, 'code' => '401'));
                    }
                }
                   $accept_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.channelName',$channelName)->where('live_calls.status','Accept')->get();
                  $key = $channelName;
      
          
                    $row = array();
              foreach ($accept_list as $call) {
                      $co_host=User::find($call->co_host_id);
                $row['channelName'] = $channelName;
                    $row['profile'] = $call->profile;
                     $row['balance'] =  Gift::where('reciever_id', $call->co_host_id)->where('channelName',$channelName)->sum('value');
                    $row['co_host_name'] = $co_host->name;
                    $row['set_no'] = $call->set_no;
                $row['frame'] = strval($co_host->frame);
                $row['co_host_id'] = strval($call->co_host_id);
                $push_count_ref = $this->database->getReference('call_accept/' . $key .'/'. $call->co_host_id);
                $push_count_ref->set($row);
              }
               
                array_push($response, array('message' => 'Gifts Sent Successfully', 'code' => '200'));

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
    public function AudienceLeave(Request $request)
    {
        $token = $request->access_token;
         $user_id = $request->user_id;
         $channelName = $request->channelName;
       
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){


            $data=AudienceJoin::where('user_id',$user_id)->where('channelName',$channelName)->first();
          if($data){
             $incall=LiveCall::where('co_host_id',$user_id)->where('channelName',$channelName)->first();
            if($incall)
            {
              $incall->delete();
            }
            $data->delete();
            // Broadcast a "left the room" member event so every room type (audio,
            // video, multi) shows a leave comment — mirrors the join/entry
            // broadcast (bd_entry / legacy type 15). Previously AudienceLeave
            // emitted nothing, so the video room had no leave comment at all.
            try {
                $leave_user = User::find($user_id);
                if ($leave_user) {
                    $leave_txt = array();
                    array_push($leave_txt, array(
                        'message'        => "$leave_user->name left the room",
                        'image'          => $leave_user->profile,
                        'name'           => $leave_user->name,
                        'level'          => $leave_user->level,
                        'is_official_id' => $leave_user->is_official_id,
                        'comment_badge'  => $leave_user->comment_badge,
                        'vip_lavel'      => $leave_user->is_vip,
                        'channelName'    => $channelName,
                    ));
                    $websoket_leave = array();
                    array_push($websoket_leave, array(
                        'message'      => 'bd_entry',
                        'channelName'  => $channelName,
                        'data'         => $leave_txt,
                        'code'         => '200',
                        'event_type' => 'room.member.entered',
                    ));
                    self::Websoket($websoket_leave);
                }
            } catch (\Throwable $leaveBroadcastError) {
                info('Audience leave broadcast failed: ' . $leaveBroadcastError->getMessage());
            }
          }
          $this->forgetAudienceCaches($channelName);
          $callAcceptRef = $this->database->getReference('call_accept/' . $channelName .'/'. $user_id);
       $callAcceptRef->remove();
       $audienceSummary = $this->cachedAudienceSummaryPayload($channelName);
       $admin_list = $audienceSummary['admin_list'];
          
            $next_comment_ref = $this->database->getReference('room_admin_list/'.$channelName);
            $next_comment_ref->set($admin_list);
          
          array_push($response,array('message'=>'Audience Leave Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
  public function AudienceList(Request $request)
    {
        $token = $request->access_token;
         $channelName = $request->channelName;
         
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){

           $audienceDirectory = $this->cachedAudienceDirectoryPayload($channelName);
           $data = $audienceDirectory['data'];
           $vip_data = $audienceDirectory['vip_data'];
           $admin_data = $audienceDirectory['admin_data'];
          
        
          array_push($response,array('message'=>'Audience List Showing Successfully ','data'=>$data,'vip_data'=>$vip_data,'admin_data'=>$admin_data,'code'=>'200'));
            

 
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
        public function CommentMute(Request $request)
    { 
        $token = $request->access_token;
         $channelName = $request->channelName;
         $user_id=$request->user_id;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            // BE-1 hardening: legacy comment-mute had NO actor check. Derive the
            // real actor from the Bearer token; require room authority; protect
            // host/officials. Fails OPEN when actor or host is unresolved so a
            // legitimate moderator request is never broken.
            $__cmActor = '';
            $__cmBearer = $request->bearerToken();
            if (!empty($__cmBearer)) {
                $__cmPat = \Laravel\Sanctum\PersonalAccessToken::findToken($__cmBearer);
                if ($__cmPat && $__cmPat->tokenable_id) { $__cmActor = (string) $__cmPat->tokenable_id; }
            }
            $__cmLive = UserLive::where('channelName', $channelName)->orderByDesc('id')->first();
            $__cmHost = $__cmLive ? (string) $__cmLive->user_id : '';
            if ($__cmActor !== '' && $__cmHost !== '') {
                $__cmActorU = \App\Models\User::find($__cmActor);
                $__cmActorOfficial = $__cmActorU && ((int)($__cmActorU->is_official_id ?? 0) !== 0 || (int)($__cmActorU->is_admin ?? 0) === 1 || (int)($__cmActorU->is_bd_admin ?? 0) === 1);
                $__cmAllowed = ($__cmActor === $__cmHost) || $__cmActorOfficial
                    || BrdAdmin::where('user_id', $__cmHost)->where('admin_id', $__cmActor)->exists();
                if (!$__cmAllowed) {
                    array_push($response, array('message' => 'You are not allowed to mute comments in this room', 'code' => '403'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                $__cmTargetU = \App\Models\User::find($user_id);
                $__cmTargetProtected = ((string) $user_id === $__cmHost) || ($__cmTargetU && ((int)($__cmTargetU->is_official_id ?? 0) !== 0 || (int)($__cmTargetU->is_admin ?? 0) === 1 || (int)($__cmTargetU->is_bd_admin ?? 0) === 1));
                if ($__cmTargetProtected) {
                    array_push($response, array('message' => 'This user cannot be muted', 'code' => '403'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
            try{
            // 2026-07-03 moderation fix: no duplicate mute rows on repeat mute;
            // carry target user id top-level for the client parsers.
            $mute_exists=CommentMute::where('channelName',$channelName)->where('user_id',$user_id)->first();
            if(!$mute_exists){
                $new=new CommentMute;
                $new->user_id=$user_id;
                $new->channelName=$channelName;
                $new->save();
            }
            $data=CommentMute::where('channelName',$channelName)->get();
             array_push($response,array('message'=>'bd_commentmute','channelName'=>$channelName,'data'=>$data,'user_id'=>(string)$user_id,'target_user_id'=>(string)$user_id,'code'=>'200','event_type' => 'room.comment.muted'));
             self::Websoket($response);
          return json_encode($response,JSON_UNESCAPED_UNICODE);
            }catch (\Exception $e) {
        array_push($response, array('message' => 'Internal Server Error', 'code' => '500', 'error' => $e->getMessage()));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function CommentMuteRemove(Request $request){
        $token = $request->access_token;
         $channelName = $request->channelName;
         $user_id=$request->user_id;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            // BE-1 hardening: legacy unmute had NO actor check (a muted user could
            // self-unmute; anyone could clear anyone's mute). Require room authority,
            // derived from the Bearer token. Fails OPEN when actor/host unresolved.
            $__cmActor = '';
            $__cmBearer = $request->bearerToken();
            if (!empty($__cmBearer)) {
                $__cmPat = \Laravel\Sanctum\PersonalAccessToken::findToken($__cmBearer);
                if ($__cmPat && $__cmPat->tokenable_id) { $__cmActor = (string) $__cmPat->tokenable_id; }
            }
            $__cmLive = UserLive::where('channelName', $channelName)->orderByDesc('id')->first();
            $__cmHost = $__cmLive ? (string) $__cmLive->user_id : '';
            if ($__cmActor !== '' && $__cmHost !== '') {
                $__cmActorU = \App\Models\User::find($__cmActor);
                $__cmActorOfficial = $__cmActorU && ((int)($__cmActorU->is_official_id ?? 0) !== 0 || (int)($__cmActorU->is_admin ?? 0) === 1 || (int)($__cmActorU->is_bd_admin ?? 0) === 1);
                $__cmAllowed = ($__cmActor === $__cmHost) || $__cmActorOfficial
                    || BrdAdmin::where('user_id', $__cmHost)->where('admin_id', $__cmActor)->exists();
                if (!$__cmAllowed) {
                    array_push($response, array('message' => 'You are not allowed to unmute comments in this room', 'code' => '403'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
         // 2026-07-03 moderation fix: actually DELETE the mute row (unmute was
         // broadcast-only, so the user stayed muted in DB and on rejoin).
         CommentMute::where('channelName',$channelName)->where('user_id',$user_id)->delete();
         array_push($response,array('message'=>'bd_commentmute','data'=>array(array('user_id'=>(string)$user_id,'channelName'=>$channelName)),'channelName'=>$channelName,'user_id'=>(string)$user_id,'target_user_id'=>(string)$user_id,'code'=>'200','event_type' => 'room.comment.unmuted'));
        // Send the WebSocket message
        self::Websoket($response);
          return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
           array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE); 
        }
    }
     private function RealTime($global_txt,$roomName,$channelName)
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
        $pusher->trigger($roomName,$channelName,$global_txt);
    }
    
     private function commentWebsoketHit($channelName,$user_id){
        $comment_websoket=array();
            $list_view = DB::table('comments')
                        ->join('users', 'users.id', '=', 'comments.user_id')
                        ->select(
                            'users.name', 
                            'users.id', 
                            'users.profile', 
                            'comments.message', 
                            'comments.type', 
                            'comments.channelName', 
                            'users.balance', 
                            'users.level', 
                            'users.is_vip', 
                            'users.is_official_id', 
                            'users.is_agency', 
                            'users.is_host_id', 
                            'users.frame', 
                            'users.comment_badge'
                        )
                        ->where('comments.channelName', $channelName) // Filter by channelName
                        ->orderBy('comments.id', 'desc')
                        ->take(10) // Limit the results to 5
                        ->get()
                        ->sortBy('comments.id') // Sort the fetched results in ascending order based on 'id'
                        ->values();
                    
           
            
                       // $this->Websoket($comment_websoket);
                        $audienceSummary = $this->cachedAudienceSummaryPayload($channelName);
                        $count_inlive = $audienceSummary['count_inlive'];
                        $audience_list = $audienceSummary['audience_list'];
                         $use=$count_inlive+rand(1, 15);
                         $host=User::find($user_id);
                          $count=[
                           'count'=>strval($use),
                            'host_name'=>$host->name,
                           ];
                            $admin_list = $audienceSummary['admin_list'];
                          
                        // Prepare the WebSocket message
                         array_push($comment_websoket, array(
                            'message' => 'bd_comment',
                            'channelName' => $channelName,
                            'data' => $list_view,
                            'audience_counter' => $count,
                            'audience_profile' => $audience_list,
                            'admin_list' => $admin_list,
                            'code' => '200',
                            'event_type' => 'room.comment.list'
                        ));
                    
                    // Send the WebSocket message
                    self::Websoket($comment_websoket);
    }
    
   
    private function Websoket($data) {
    try {
        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'CommentController']);
    } catch (\Throwable $th) {
        info('Comment named websocket exception: ' . $th->getMessage());
    }
}
}
