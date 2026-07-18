<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoinBeg;
use App\Models\CoinBegRecived;
use App\Models\User;
use App\Models\Comment;
use DB;
use App\Models\UserLive;
use App\Models\BanDevice;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Contract\Database;
class CoinBegController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function ActiveCoinBeg(Request $request){
        $response = [];
        $websocket_call = [];
        $token = $request->access_token;
        $userId = $request->user_id;
        $amount = $request->amount;
        $count = $request->clam_count;
        $host_id = $request->host_id;
        $type = $request->type;
        $channelName = $request->channelName;
       
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        $user = User::find($userId);
        if($user->balance<$amount){
             return response()->json([
                ['message' => 'Inseficent Balance', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
        if (!$user) {
            return response()->json([
                ['message' => 'User Not Found', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        // Check for banned users or devices
        if (User::where('ban_type', '!=', null)->where('id', $userId)->exists()) {
            $bannedUser = User::select('ban_type')->where('id', $userId)->first();
            $banMessages = [
                "B" => "Your ID Banned For One Month. Violation Rules B",
                "C" => "Your ID Banned For 24 Hours. Violation Rules C",
                "D" => "Your ID Banned For 1 Hour. Violation Rules D",
                "A" => "You Are Permanently Banned. Violation Rules A",
            ];
            $message = $banMessages[$bannedUser->ban_type] ?? "User is banned.";
            return response()->json([
                ['message' => $message, 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        if (BanDevice::where('device_id', $user->imei_number)->exists()) {
            return response()->json([
                ['message' => 'Device Banned', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
        
       $coinBeg_run = CoinBeg::where('host_id', $host_id)->where('channelName', $channelName)->where('created_at', '>=', now()->subMinutes(2))  // Fetch records from the last 1 min
        ->first();
        if($coinBeg_run){
             return response()->json([
                ['message' => 'Try Again', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
        
    //   $check_lock_brd = UserLive::where('host_id', $host_id)
    //     ->where('channelName', $channelName)
    //     ->first();

    //     if ($check_lock_brd->pin != 0) {
    //         return response()->json([
    //             'message' => 'Try Again',
    //             'code' => 401
    //         ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
    //     }

        $user->balance-=$amount;
       $user->save();
        //info('Hit From Coin Beg: ' . $type);
        
        $coinBeg=CoinBeg::create([
        'user_id' => $userId,
        'amount' => $amount,
        'count' => $count,
        'host_id' => $host_id,
        'type' => $type,
        'given_count' => $count,
        'amount_clime' => $amount,
        'channelName' => $channelName,
        ]);
         $beg_push_lives = DB::table('user_lives as ul')
            ->join('users as u', 'u.id', '=', 'ul.user_id')
            ->where('ul.user_id', $host_id)
            ->where('ul.channelName', $channelName)
            ->limit(1)
            ->select([
                'u.name', 'u.id', 'u.level', 'u.balance', 'u.profile',
                'ul.token', 'ul.channelName', 'ul.type',
                'ul.backgorund', 'ul.notice', 'ul.bullet_notice',
                'ul.pin', 'ul.audio_brd_design', 'u.host_badge',
                'ul.avatar', 'ul.sdk'
            ])
            ->first();
    $banner = $user->name . ' send Coin Beg' . $amount . ' Points. Click to collect Points !! ';

        
     array_push($response,array('message'=>$banner, 'user_id' => $coinBeg->user_id,
                'amount' => $coinBeg->amount,
                'channelName' => $coinBeg->channelName,
                'name' => $user->name,
                'level' => $user->level,
                'beg_id' => $coinBeg->id,
                'created_at' => $coinBeg->created_at,
                'live_data' => $beg_push_lives,
                'code'=>'200'));
     $roomName='coin_beg';
     array_push($websocket_call,array('message'=>$banner,'data'=>$response,'channelName'=>$channelName,'host_id'=>$host_id,'type'=>$type,'code'=>'200','event_type' => 'room.coin_bag.updated'));;
    
     self::Websoket($websocket_call);
     
     
                $data=new Comment;
                $data->user_id=$user->id;
                $data->channelName=$channelName;
                $data->message=$banner;
                $data->reciever_id=$host_id;
                $data->type='message';
                $data->save();
                
                $beg_comment = [
                            'balance' => strval($user->balance),
                            'channelName' => strval($channelName),
                            'id' => $user->id,
                            'message' => strval('@'.$banner),
                            'level' => strval($user->level),
                            'name' => strval($user->name),
                            'profile' => strval($user->profile),
                            'is_vip' => strval($user->is_vip),
                            'frame' => strval($user->frame),
                            'is_official_id' => strval($user->is_official_id),
                            'is_agency' => strval($user->is_agency),
                            'is_host_id' => strval($user->is_host_id),
                            'comment_badge' => strval($user->comment_badge),
                            'type' => "message",
                        ];
                        
                        // Reference to the channel comments
// [Firebase dead]                         $comments_ref = $this->database->getReference('Comments/'.$channelName);
                        
                        // Get the existing comments
// [Firebase dead]                         $existing_comments = $comments_ref->getValue();
                        
                        // Determine the next index
                        $next_index = 0;
                        if (is_array($existing_comments)) {
                            $next_index = count($existing_comments);
                        }
                        
                        // Push the new comment at the next index
// [Firebase dead]                          $next_comment_ref = $this->database->getReference('Comments/'.$channelName.'/'.$next_index);
// [Firebase dead]                         $next_comment_ref->set($beg_comment);
                         $this->profitcalculation($coinBeg->id,$amount);
     return json_encode($websocket_call,JSON_UNESCAPED_UNICODE);
    }
    
    public function Claim(Request $request)
    {
        $response = [];
        $websocket_call = [];
        $token = $request->access_token;
        $userId = $request->user_id;
        $beg_id = $request->beg_id;
        $channelName = $request->channelName;
      if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                ['message' => 'User Not Found', 'code' => '404']
            ], 404, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        // Check for banned users or devices
        if (User::where('ban_type', '!=', null)->where('id', $userId)->exists()) {
            $bannedUser = User::select('ban_type')->where('id', $userId)->first();
            $banMessages = [
                "B" => "Your ID Banned For One Month. Violation Rules B",
                "C" => "Your ID Banned For 24 Hours. Violation Rules C",
                "D" => "Your ID Banned For 1 Hour. Violation Rules D",
                "A" => "You Are Permanently Banned. Violation Rules A",
            ];
            $message = $banMessages[$bannedUser->ban_type] ?? "User is banned.";
            return response()->json([
                ['message' => $message, 'code' => '404']
            ], 404, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        if (BanDevice::where('device_id', $user->imei_number)->exists()) {
            return response()->json([
                ['message' => 'Device Banned', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
            $blockedUsers = [29456, 38129, 40342, 42620, 39897,27555,38195];

            if (in_array($userId, $blockedUsers)) {
                return response()->json([
                    'message' => 'some',
                    'code' => 401
                ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
            }
        
        $coinBeg=CoinBeg::find($beg_id);
        
        if ($coinBeg->count > 0) {
        $coinBeg->count -= 1;
    
        $coinBeg->save();
        $user=User::find($userId);
       $coinBegReceived = CoinBegRecived::firstOrCreate(
            ['user_id' => $userId, 'beg_id' => $beg_id], // Search criteria
            [
                'user_name' => $user->name,
                'amount' => 0,
                'channelName' => $channelName,
            ] // Only inserted if no match is found
        );
        
        // 2026-07-03 perf fix: the inline sleep(12) pinned a PHP-FPM worker for
        // 12s per coin-bag opener; a busy room could exhaust the pool and hang
        // the whole API. The 12s game delay now runs on the queue.
        \App\Jobs\CoinBegResultJob::dispatch($userId, $beg_id, $channelName, 'V5')
            ->delay(now()->addSeconds(12));
        }
        return response()->json([
                'success' => true,
                'message' => 'CoinBeg Opened'
            ], 200);
        
    }
    
  public function CoinBegResult($userId, $beg_id, $channelName)
{
    $coinBeg = CoinBeg::find($beg_id);
    $coinBeg->update(['click' => 1]);
    $coinBegReceived = CoinBegRecived::where('beg_id', $beg_id);
    
    $coinBegReceivedCount = $coinBegReceived->count();
    // if ($coinBegReceivedCount > $coinBeg->given_count) {
    //     $excess = $coinBegReceivedCount - $coinBeg->given_count;
    //     $coinBegReceived->orderBy('id', 'asc')->limit($excess)->delete();
    // }
    if ($coinBegReceivedCount > $coinBeg->given_count) {
    $excess = $coinBegReceivedCount - $coinBeg->given_count;

    // Get excess records, prioritizing newer users first
    $excessRecords = $coinBegReceived
        ->orderBy('user_id', 'desc') // Prioritize new users
        ->orderBy('id', 'asc') // Delete older records first within each user
        ->limit($excess)
        ->get();

    // Delete the selected records
    $excessRecords->each->delete();
}
    
    $coinBegReceived = $coinBegReceived->orderBy('id', 'asc')->get();
    
    if ($coinBeg->amount > 0 && $coinBegReceived->isNotEmpty()) {
        $percentages = $this->generateDecreasingPercentages($coinBegReceived->count());
        $totalAmount = $coinBeg->amount;

        foreach ($coinBegReceived as $index => $record) {
            $record->update(['amount' => round(($totalAmount * $percentages[$index]) / 100, 2)]);
        }
    }
    sleep(2);
    if ($coinBeg->result == 0) {
        
         $coinBeg->update(['result' => 1]);
        $this->PushResult($userId, $beg_id, $channelName);
    }
   

    
}
private function profitcalculation($beg_id, $amount)
{
    // Ensure beg_id and amount are valid
    if (!$beg_id || !$amount || $amount <= 0) {
        return false; // Prevent invalid calculations
    }

    // Deduct 5% and round down
    $now_balance = floor($amount - ($amount * 0.20));

    // Find the record
    $coinBeg = CoinBeg::find($beg_id);

    if ($coinBeg) { // Check if the record exists
        $coinBeg->amount = $now_balance;
        $coinBeg->amount_clime = $now_balance;
        $coinBeg->save();
        return true;
    }

    return false; // Return false if no record found
}


    private function PushResult($userId, $beg_id, $channelName){
        $response = array();
        $websocket_call = array();
          $coinBeg = CoinBeg::find($beg_id);
          
        if ($coinBeg->push == 0) {
            DB::beginTransaction(); // Start transaction
            
            // Re-check inside transaction to prevent race condition
            $coinBeg = CoinBeg::lockForUpdate()->find($coinBeg->id);
            
            if ($coinBeg->push == 0) {
              $coinBeg->push=1;
          $coinBeg->save();
           
            $coinBegReceived = CoinBegRecived::where('beg_id', $beg_id)
                ->select('user_name', 'amount', 'user_id')->orderby('id','asc')
                ->get();
            
        foreach($coinBegReceived as $balacne_add){
            $user=User::find($balacne_add->user_id);
            if($user){
                $user->balance+=floor($balacne_add->amount);
                $user->save();
              // info('Hit From Coin Beg: ' . $balacne_add->amount .' User_id' .$balacne_add->user_id);
            }
        }
            $sender = User::where('id', $coinBeg->user_id)
                ->select('name', 'profile')
                ->first();
                
         array_push($response,array('message'=>'Coin Beg Result ','winner_list'=>$coinBegReceived,'sender'=>$sender,'channelName'=>$channelName,'code'=>'200'));
         $roomName='coin_beg_result';
         array_push($websocket_call,array('message'=>'Coin Beg Result','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'room.coin_bag.result'));
         DB::commit();
         self::Websoket($websocket_call);
          } 
            
        } else {
        DB::rollBack(); // Rollback if another process updated the `push` flag
    }
    }
   private function generateDecreasingPercentages($count)
{
    $percentages = [];
    $remaining = 100;

    // Ensure the first percentage is 40%
    if ($count > 1) {
        array_push($percentages, 40);
        $remaining -= 40;
        $count--; // Reduce count since one is assigned
    } else {
        return [100]; // If only one value is needed, it must be 100%
    }

    for ($i = 0; $i < $count; $i++) {
        if ($i === $count - 1) {
            // Assign the remaining percentage to the last element
            array_push($percentages, $remaining);
        } else {
            // Generate a random percentage ensuring it's not too large or too small
            $max_limit = min($remaining - ($count - $i - 1) * 5, 50); // Ensure space for remaining values
            $random_percentage = rand(5, $max_limit);
            array_push($percentages, $random_percentage);
            $remaining -= $random_percentage;
        }
    }

    return $percentages;
}





    private function Websoket($data) {
    try {
        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'CoinBegController']);
    } catch (\Throwable $th) {
        info('CoinBeg named websocket exception: ' . $th->getMessage());
    }
}
}
