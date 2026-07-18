<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\GameBannner;
use App\Models\Gift;
use App\Models\PusherKey;
use App\Models\HourlyRanking;
use App\Models\LiveCall;
use App\Models\FortuneLock;
use App\Models\PortalTransfer;
use App\Models\TopReward;
use App\Models\Notification;
use App\Services\ScheduledFrameRuleService;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RunTaskEvery4Seconds extends Command
{
    protected $signature = 'task:run-every-4-seconds';
    protected $description = 'Run optimized tasks every 4 seconds';

  public function handle()
    {
        $now = Carbon::now();
        $hour = $now->format('H');
        $minute = $now->format('i');
    
        Log::channel('cornjob')->info("Cron Run At: ".$now->toDateTimeString());
    
        // prevent duplicate execution within same minute
        $lockKey = 'cron_lock_'.$hour.'_'.$minute;
        if (Cache::has($lockKey)) {
            Log::channel('cornjob')->info("Skipped (Already executed this minute)");
            return;
        }
        Cache::put($lockKey, true, 55); // lock for 55 seconds
    
        try {
       
    
            if ($minute === '32') {
                //Log::channel('cornjob')->info("Running: removeDuplicateRewards()");
                $this->removeDuplicateRewards();
            }
    
            if ($hour === '00' && $minute === '15') {
                //Log::channel('cornjob')->info("Running: resetPusherKey()");
                $this->resetPusherKey($now);
            }
    
            if ($hour === '00' && $minute === '05') {
               // Log::channel('cornjob')->info("Running: dailyTopSenderReward()");
                $this->dailyTopSenderReward();
            }
    
            if ($minute === '01') {
              //  Log::channel('cornjob')->info("Running: calculateHourlyRanking()");
                $this->calculateHourlyRanking();
            }
    
            if (in_array($minute, ['02','03','04'])) {
              //  Log::channel('cornjob')->info("Running: pushHourlyRanking()");
                $this->pushHourlyRanking($minute);
            }
    
         //   Log::channel('cornjob')->info("Running: removeExpiredMuteCalls()");
            $this->removeExpiredMuteCalls();
            
    
            if ($minute % 10 === 0) {
               // Log::channel('cornjob')->info("Running: unlockBannedUsers()");
                $this->unlockBannedUsers();
            }
    
            if ($minute % 30 === 0) {
               // Log::channel('cornjob')->info("Running: weeklyUserLock()");
                $this->weeklyUserLock();
            }
    
            if ($minute % 2 === 0) {
              //  Log::channel('cornjob')->info("Running: cleanupPendingLiveCalls()");
                $this->cleanupPendingLiveCalls();
            }
    
            if ($minute % 30 === 0) {
              //  Log::channel('cornjob')->info("Running: updateUserFramesAndIMEI()");
                $this->updateUserFramesAndIMEI();
            }

            $this->syncScheduledFrameRules();

            // Boss 2026-07-04: server-side stale-host sweep — backstop for the
            // client 3-min background auto-end when the OS KILLS the host app
            // (client timer can't fire). Heartbeat-driven, two-strike, and only
            // for rooms whose host app ever sent a heartbeat (new APKs), so
            // legacy-client rooms are never touched.
            $this->sweepStaleHostRooms();

 
    
        //    Log::channel('cornjob')->info("Cron Completed Successfully");
    
        } catch (\Throwable $e) {
            Log::channel('cornjob')->error("Cron Error: ".$e->getMessage());
        }
    }


    // --- Remove duplicate rewards efficiently ---
    private function removeDuplicateRewards()
    {
        $today = Carbon::today()->format('Y-m-d');

        foreach ([1,2] as $type) {
            DB::table('gifts')
                ->where('sander_id', 1)
                ->where('reward_type', $type)
                ->whereDate('date', $today)
                ->select('id', 'reciever_id')
                ->orderBy('id')
                ->chunkById(200, function($gifts) {
                    $grouped = $gifts->groupBy('reciever_id');
                    foreach ($grouped as $recieverId => $giftGroup) {
                        $giftGroup->shift(); // Keep first
                        $ids = $giftGroup->pluck('id')->toArray();
                        if (!empty($ids)) Gift::whereIn('id', $ids)->delete();
                    }
                });
        }
    }

    // --- Reset Pusher keys ---
    private function resetPusherKey($now)
    {
        PusherKey::where('pusher_status','!=',1)
            ->update([
                'pusher_status'=>0,
                'pusher_active_time'=>null,
                'pusher_deactive_time'=>null,
                'last_reset_date'=>$now
            ]);
    }

    // --- Daily top sender reward ---
    private function dailyTopSenderReward()
    {
        $yesterday = Carbon::yesterday();

        $topSander = Gift::where('sander_id','!=',1)
            ->whereDate('created_at', $yesterday)
            ->select('sander_id', DB::raw('SUM(value) as total_value'))
            ->groupBy('sander_id')
            ->orderByDesc('total_value')
            ->first();

        if ($topSander && $topSander->total_value > 0) {
            $reward = $topSander->total_value * 0.015;
            $this->bannerSandReward($topSander->sander_id, $reward, $topSander->total_value);
        }
    }

    // --- Hourly ranking calculation ---
    private function calculateHourlyRanking()
    {
        $end = Carbon::now()->copy()->startOfHour();
        $start = $end->copy()->subHour();

        // Top sender
        $topSander = Gift::where('sander_id','!=',1)
            ->whereBetween('created_at', [$start, $end])
            ->select('sander_id', DB::raw('SUM(value) as total_value'))
            ->groupBy('sander_id')
            ->orderByDesc('total_value')
            ->first();

        // Top receiver
        $topReceiver = Gift::whereBetween('created_at', [$start, $end])
            ->select('reciever_id', DB::raw('SUM(value) as total_value'))
            ->groupBy('reciever_id')
            ->orderByDesc('total_value')
            ->first();

        // Top hourly gamer
        $topGamer = DB::table('furits_pots_backups as f')
            ->join('users as u','u.id','=','f.user_id')
            ->whereNotIn('f.user_id',[23825,23826,23827])
            ->whereBetween('f.date', [$start, $end])
            ->select('f.user_id', DB::raw('SUM(f.amount) as total_amount'))
            ->groupBy('f.user_id')
            ->orderByDesc('total_amount')
            ->first();

        if ($topSander) $this->saveHourlyRanking($topSander->sander_id,1,'Top Hourly Sender ',$topSander->total_value);
        if ($topReceiver) $this->saveHourlyRanking($topReceiver->reciever_id,2,'Top Hourly Receiver ',$topReceiver->total_value);
        if ($topGamer) $this->saveHourlyRanking($topGamer->user_id,3,'Top Hourly Gamer ',$topGamer->total_amount);
    }

    // --- Push hourly ranking over WebSocket ---
    private function pushHourlyRanking($minute)
    {
        $typeMap = ['02'=>1,'03'=>3,'04'=>2];
        $type = $typeMap[$minute] ?? null;
        if (!$type) return;

        $ranking = HourlyRanking::where('type',$type)->first();
        if (!$ranking) return;

        $payload = [
            'message'=>'hourly_top',
            'data'=>[['message'=>$ranking->name,'image'=>$ranking->image,'type'=>$ranking->type]],
            'code'=>'200',
            'channel_type'=>'44'
        ];
        // Fan out the hourly-top banner to EVERY live audio + multi room's own
        // private channel (was one dead hardcoded channel). event_type
        // room.hourly_top.updated is whitelisted + client-routed to the hourly handler.
        $svc = app(\App\Services\V5\RoomBroadcastService::class);
        $rooms = UserLive::whereIn('type', [1, 3])->get(['channelName', 'type', 'user_id']);
        foreach ($rooms as $room) {
            $ch = (string) $room->channelName;
            if ($ch === '') { continue; }
            $rt = ((int) $room->type === 3) ? 'multi' : 'audio';
            try {
                $svc->broadcast($rt, $ch, (string) $room->user_id, 'room.hourly_top.updated', $payload, ['actor_user_id' => null]);
            } catch (\Throwable $th) { /* best-effort per room */ }
        }
        $ranking->delete();
    }

    // --- Stale-host room sweep (Boss 2026-07-04) ---
    // Ends rooms whose HOST app was killed by the OS (so the client-side 3-min
    // background auto-end never fired). New-APK hosts beat v5/host_heartbeat
    // every 60s (Redis bd:host_hb:{channel}, TTL 210s). Rules:
    //   * only rooms older than 5 min (creation grace)
    //   * only rooms that EVER heartbeat (bd:host_hb_seen, 24h) — legacy-client
    //     rooms are never touched (no regression)
    //   * TWO-STRIKE: first missing beat only marks bd:host_hb_miss (120s);
    //     the room ends on the SECOND consecutive miss — a Redis flush or a
    //     momentary blip can never end live rooms
    // Ending goes through LiveOffService::offRoom (bd_bdr_off ch103) so every
    // audience gets the instant "Room Ended" cover + 5s redirect.
    private function sweepStaleHostRooms()
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();
            // Cluster-wide throttle: at most one sweep per 25s across nodes.
            if (!$redis->set('bd:host_hb_sweep_lock', '1', 'EX', 25, 'NX')) {
                return;
            }
            $rooms = \App\Models\UserLive::where(
                    'created_at', '<', Carbon::now()->subMinutes(5)
                )->get(['channelName', 'user_id']);
            if ($rooms->isEmpty()) {
                return;
            }
            $svc = null;
            foreach ($rooms as $room) {
                $ch = trim((string) $room->channelName);
                if ($ch === '') { continue; }
                if (!$redis->exists('bd:host_hb_seen:' . $ch)) { continue; }
                if ($redis->exists('bd:host_hb:' . $ch)) { continue; }
                // First miss -> mark and wait for the next sweep.
                if ($redis->set('bd:host_hb_miss:' . $ch, '1', 'EX', 120, 'NX')) {
                    continue;
                }
                // Second consecutive miss -> host is gone, end the room.
                try {
                    $svc = $svc ?: app(\App\Services\LiveOffService::class);
                    $svc->offRoom($ch, (string) $room->user_id, null, 'stale_host_sweep');
                    Log::channel('cornjob')->info('stale_host_sweep ended room ' . $ch);
                } catch (\Throwable $e) {
                    Log::channel('cornjob')->error('stale_host_sweep offRoom ' . $ch . ': ' . $e->getMessage());
                }
                $redis->del('bd:host_hb_miss:' . $ch);
            }
        } catch (\Throwable $e) {
            Log::channel('cornjob')->error('sweepStaleHostRooms: ' . $e->getMessage());
        }
    }

    // --- Remove expired mute calls ---
    private function removeExpiredMuteCalls()
    {
        $threshold = Carbon::now()->subMinutes(5);
        LiveCall::where('mute_time','<',$threshold)
            ->chunkById(50,function($calls){
                foreach($calls as $call){
                    $payload = [
                        'message'=>'bd_audio_call_mute_system_remove',
                        'user_id'=>$call->co_host_id,
                        'set_no'=>$call->set_no,
                        'channelName'=>$call->channelName,
                        'code'=>'200',
                        'channel_type'=>'98'
                    ];
                    $this->Websoket($payload);
                    $call->delete();
                }
            });
    } 
    
    // --- Add Agency Code ---
//  private function AddagencyCode()
//     {
//         ignore_user_abort(true);
//         set_time_limit(0);
    
//         $totalUpdated = 0;
//         $batchSize = 1000;
    
//         do {
//             $updated = DB::statement("
//                 UPDATE gifts g
//                 JOIN host_data h 
//                     ON h.user_id = g.reciever_id
//                     AND h.user_id REGEXP '^[0-9]+$'
//                 SET g.agency_code = h.agency_code
//                 WHERE g.agency_code IS NULL
//                 LIMIT {$batchSize}
//             ");
    
//             // DB::statement returns true/false, not affected rows
//             // To get affected rows, use DB::affectingStatement
//             $updated = DB::affectingStatement("
//                 UPDATE gifts g
//                 JOIN host_data h 
//                     ON h.user_id = g.reciever_id
//                     AND h.user_id REGEXP '^[0-9]+$'
//                 SET g.agency_code = h.agency_code
//                 WHERE g.agency_code IS NULL
//                 LIMIT {$batchSize}
//             ");
    
//             $totalUpdated += $updated;
    
//             // Optional sleep to reduce DB pressure
//             usleep(200000); // 0.2 sec
    
//         } while ($updated > 0);
    
//         Log::channel('cornjob')->info("Agency code update completed. Updated: {$totalUpdated}");
    
//         return $totalUpdated;
//     }
    // --- Unlock banned users ---
    private function unlockBannedUsers()
    {
        User::whereNotNull('ban_type')
            ->where('ban_type','!=','A')
            ->where('open_time','<',Carbon::now())
            ->chunkById(50,function($users){
                foreach($users as $user){
                    $user->update([
                        'ban_type'=>null,
                        'open_time'=>null,
                        'status'=>1
                    ]);
                }
            });
    }

    // --- Weekly user lock optimized ---
    private function weeklyUserLock()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Users with at least one recharge and game_priority = 0
        $users = User::where('created_at','>=',$sevenDaysAgo)
            ->where('game_priority',0)
            ->whereIn('id', function($q){
                $q->select('user_id')->from('portal_transfers');
            })
            ->select('id','imei_number')
            ->get();

        if ($users->isEmpty()) return;

        $imeiList = $users->pluck('imei_number')->toArray();
        $existingImeis = FortuneLock::whereIn('imei_number',$imeiList)->pluck('imei_number')->toArray();

        $locksToInsert = [];
        $userIdsToUpdate = [];

        foreach($users as $user){
            if (in_array($user->imei_number,$existingImeis)) continue;

            $locksToInsert[] = [
                'user_id'=>$user->id,
                'type'=>1,
                'imei_number'=>$user->imei_number,
                'auto_lock_active'=>'Auto Lock New User & Priority',
                'parcentage'=>1,
                'created_at'=>now(),
                'updated_at'=>now()
            ];

            $userIdsToUpdate[] = $user->id;
        }

        if(!empty($locksToInsert)) DB::table('fortune_locks')->insert($locksToInsert);
        if(!empty($userIdsToUpdate)) DB::table('users')->whereIn('id',$userIdsToUpdate)->update(['game_priority'=>1]);
    }

    // --- Cleanup pending live calls ---
    private function cleanupPendingLiveCalls()
    {
        $twoMinutesAgo = Carbon::now()->subMinutes(2);
        LiveCall::where('status','pending')->where('created_at','<',$twoMinutesAgo)->delete();
    }

    // --- Bulk update frames and IMEI ---
    private function updateUserFramesAndIMEI()
    {
       
        
        // Check the agency update specifically
        $toUpdate = User::where('is_admin_frame', 0)
            ->where('is_bd_admin', 0)
            ->where('is_agency', 1)
            ->where('is_official_frame', 0)
            ->where('frame', '!=', 'marchant.svga')
            ->get();
        
       
        
        // Perform updates
        User::where('is_admin_frame', true)->where('frame','!=','admin.svga')->update(['frame'=>'admin.svga']);
        User::where('is_bd_admin', true)->where('frame','!=','frame_11.svga')->update(['frame'=>'frame_11.svga']);
        User::where('is_official_frame', true)->where('frame','!=','official.svga')->update(['frame'=>'official.svga']);
        
       $updated = User::where('is_agency', 1)
    ->where(function($q) {
        $q->where('frame', '!=', 'marchant.svga')
          ->orWhereNull('frame');
    })
    ->where('is_admin_frame', 0)  // Add these back if needed
    ->where('is_bd_admin', 0)
    ->where('is_official_frame', 0)
    ->update(['frame' => 'marchant.svga']);

        
        User::whereNull('imei_number')->update(['imei_number'=>DB::raw('id')]);
        
      
    }
   
    private function syncScheduledFrameRules()
    {
        try {
            app(ScheduledFrameRuleService::class)->syncAllActiveRules();
        } catch (\Throwable $throwable) {
            Log::channel('cornjob')->error("Scheduled Frame Sync Error: " . $throwable->getMessage());
        }
    }

    // --- Save hourly ranking ---
    private function saveHourlyRanking($userId,$type,$title,$amount)
    {
        $user = User::find($userId);
        if (!$user) return;

        $entry = HourlyRanking::create([
            'image'=>$user->profile,
            'name'=>$title.$user->name,
            'type'=>$type,
            'amount'=>$amount,
            'reward'=> $type===1 ? $amount*0.015 : 0
        ]);
    }

    // --- Push reward via banner ---
    private function bannerSandReward($userId,$reward,$amount)
    {
        $user = User::find($userId);
        if (!$user) return;

        TopReward::create(['user_id'=>$userId,'amount'=>$reward,'date'=>date('Y-m-d')]);
        PortalTransfer::create([
            'portal_user_id'=>1,
            'user_id'=>$userId,
            'amount'=>$reward,
            'trxid'=>uniqid('Daily_Top_Sender_'.$amount.'_Recharge_'),
            'date'=>date('Y-m-d')
        ]);
        $user->increment('balance',$reward);

        Notification::create([
            'user_id'=>$userId,
            'date'=>date('Y-m-d'),
            'message'=>$reward.' Points Reward Got Successfully For Top Daily Sending From QueenLive'
        ]);

        $payload = [
            'message'=>'top_reward_banner',
            'channelName'=>'68a9765cdee148948a70f431d6346f12',
            'data'=>[['message'=>$user->name.' Reward '.$reward.' Points Daily Sending']],
            'code'=>'200',
            'channel_type'=>'49'
        ];
        $this->Websoket($payload);
    }

    // --- WebSocket push ---
    private function Websoket($data)
{
    try {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'RunTaskEvery4Seconds']);

    } catch (\Throwable $th) {
        info('Scheduled named WebSocket dispatch failed: ' . $th->getMessage());
    }
}
}
 
