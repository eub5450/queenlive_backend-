<?php

namespace App\Http\Controllers\Api\V5;

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
use Illuminate\Support\Facades\Cache;
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
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use RedisCacheFunction;
use App\Traits\CacheClearTrait;
class AudioBrdController extends Controller
{
    use CacheClearTrait;
   public function __construct(Database $database)
    {
        $this->database = $database;
        $this->middleware('auth:sanctum');
    }
   public function HostList(Request $request)
{
    $access_token = $request->access_token;
    $host_id = $request->host_id;
    $channelName = $request->channelName;
    $response = array();

    if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){

        $legacyList=DB::table('live_calls')
            ->join('users','users.id','live_calls.co_host_id')
            ->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')
            ->where('live_calls.host_id',$host_id)
            ->where('live_calls.channelName',$channelName)
            ->where('live_calls.status','Accept')
            ->get();

        $snapshot = $this->buildAudioRoomHostSnapshotPayload($host_id, $channelName, 'Host List Data Show Successfully');
        $snapshot['data'] = $legacyList;
        array_push($response,$snapshot);
        return json_encode($response,JSON_UNESCAPED_UNICODE);

    }else{
        array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
}
    private function buildAudioRoomSeatBalanceMap($channelName, array $userIds)
    {
        $normalizedIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($normalizedIds)) {
            return array();
        }

        return Gift::query()
            ->select('reciever_id', DB::raw('SUM(value) as total_value'))
            ->where('channelName', $channelName)
            ->whereIn('reciever_id', $normalizedIds)
            ->groupBy('reciever_id')
            ->pluck('total_value', 'reciever_id')
            ->map(function ($value) {
                return strval($value ?? 0);
            })
            ->toArray();
    }

    private function buildAudioRoomProgressStats($host_id)
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
        $todayDate = now()->toDateString();

        $giftStats = Gift::query()
            ->where('reciever_id', $host_id)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN DATE(date) BETWEEN ? AND ? THEN value ELSE 0 END), 0) as monthly_gift,
                 COALESCE(SUM(CASE WHEN DATE(date) = ? THEN value ELSE 0 END), 0) as today_gift,
                 COALESCE(SUM(CASE WHEN sander_id = 1 THEN value ELSE 0 END), 0) as total_reward',
                [$startDate, $endDate, $todayDate]
            )
            ->first();

        $totalGiftSum = intval(optional($giftStats)->monthly_gift ?? 0);
        $todayGift = intval(optional($giftStats)->today_gift ?? 0);
        $totalReward = strval(optional($giftStats)->total_reward ?? 0);

        $levels = [
            2000000 => [5, 20000000],
            1000000 => [5, 2000000],
            500000  => [4, 1000000],
            200000  => [3, 500000],
            50000   => [2, 200000],
            0       => [1, 50000],
        ];

        $star = 1;
        $nextLevelAmount = 50000;

        foreach ($levels as $threshold => $levelData) {
            if ($todayGift >= $threshold) {
                $star = $levelData[0];
                $nextLevelAmount = $levelData[1];
                break;
            }
        }

        return array(
            'host_balance' => $totalGiftSum,
            'star' => $star,
            'star_complete_parcent' => $nextLevelAmount > 0 ? intval(($todayGift / $nextLevelAmount) * 100) : 0,
            'total_reward' => $totalReward,
        );
    }

    private function hasMeaningfulRequestValue($value)
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        return $value !== null;
    }

    private function requestValue(Request $request, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            $queryValue = $request->query($key);
            if ($this->hasMeaningfulRequestValue($queryValue)) {
                return $queryValue;
            }
        }

        foreach ($keys as $key) {
            $inputValue = $request->input($key);
            if ($this->hasMeaningfulRequestValue($inputValue)) {
                return $inputValue;
            }
        }

        $jsonPayload = $request->json()->all();
        if (is_array($jsonPayload)) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $jsonPayload) &&
                    $this->hasMeaningfulRequestValue($jsonPayload[$key])) {
                    return $jsonPayload[$key];
                }
            }
        }

        $rawBody = $request->getContent();
        if (is_string($rawBody) && trim($rawBody) !== '') {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $decoded) &&
                        $this->hasMeaningfulRequestValue($decoded[$key])) {
                        return $decoded[$key];
                    }
                }
            }

            parse_str($rawBody, $parsedBody);
            if (is_array($parsedBody)) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $parsedBody) &&
                        $this->hasMeaningfulRequestValue($parsedBody[$key])) {
                        return $parsedBody[$key];
                    }
                }
            }
        }

        return $default;
    }

    private function normalizedGiftItems(Request $request)
    {
        $items = $this->requestValue($request, array('items'), array());

        if (is_string($items) && trim($items) !== '') {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $items = $decoded;
            }
        }

        if (!is_array($items)) {
            return array();
        }

        $normalized = array();
        foreach ($items as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            if (!is_array($row)) {
                continue;
            }

            $receiverId = trim((string) (
                $row['receiverId'] ??
                $row['receiver_id'] ??
                $row['reciever_id'] ??
                ''
            ));
            if ($receiverId === '') {
                continue;
            }

            $normalized[] = array('receiverId' => $receiverId);
        }

        return $normalized;
    }

    private function missingFieldResponse($field, array $acceptedFields)
    {
        return response()->json([[
            'message' => "Missing required field: {$field}",
            'field' => $field,
            'accepted_fields' => $acceptedFields,
            'code' => '422',
        ]], 422);
    }

    private function buildAudioRoomHostSnapshotPayload($host_id, $channelName, $message = 'Host List Data Show Successfully', array $options = array())
    {
        $setRemove = array_key_exists('set_remove', $options) ? strval($options['set_remove']) : '11';
        $emojiUserId = array_key_exists('emoji_user_id', $options) ? strval($options['emoji_user_id']) : '';
        $emojiValue = array_key_exists('emoji', $options) ? strval($options['emoji']) : '0';
        $snapshotUpdatedAt = strval((int) round(microtime(true) * 1000));

        $list = array();
        $hostData = RedisCacheFunction::UserfindById($host_id);
        $live = UserLive::where('channelName', '=', $channelName)->where('user_id', $host_id)->first();
        $acceptList = LiveCall::where('channelName', $channelName)
            ->where('host_id', $host_id)
            ->where(function ($query) {
                $query->where('status', 'Accept')
                    ->orWhere('is_co_host_active', 'Accept');
            })
            ->orderBy('set_no')
            ->get();

        $seatUserIds = [$host_id];
        foreach ($acceptList as $call) {
            $seatUserIds[] = $call->co_host_id;
        }
        $seatBalances = $this->buildAudioRoomSeatBalanceMap($channelName, $seatUserIds);
        $progressStats = $this->buildAudioRoomProgressStats($host_id);

        // BUG FIX (audience showed 8 seats on join): carry the host's AUTHORITATIVE
        // seat count in the snapshot. Without this the snapshot had no siteNumber, so
        // the audience fell back to the feed model default (8) and never corrected.
        $seatCount = 8;
        if ($live) {
            foreach (array('siteNumber', 'site_number', 'seatNumber', 'seat_number') as $column) {
                if (isset($live->{$column}) && intval($live->{$column}) >= 2) {
                    $seatCount = max(2, min(15, intval($live->{$column})));
                    break;
                }
            }
        }

        // Persisted per-seat locks. The DB user has no ALTER/CREATE right, so each
        // locked seat is stored as a ROW in the existing live_calls table with a
        // status='locked' sentinel (isolated from the Accept/pending queries). The
        // snapshot reads them so host, audience and rejoiners render the same locks.
        $lockedSeatRows = LiveCall::where('channelName', $channelName)
            ->where('host_id', $host_id)
            ->where('status', 'locked')
            ->orderBy('set_no')
            ->pluck('set_no')
            ->map(function ($v) { return intval($v); })
            ->filter(function ($v) { return $v >= 1 && $v <= 15; })
            ->unique()
            ->values()
            ->all();
        $lockedSeats = implode(',', $lockedSeatRows);
        $allSeatsLocked = strval($live ? $live->locked : 0);

        if ($hostData) {
            $host = array();
            $host['channelName'] = $channelName;
            $host['profile'] = $hostData->profile;
            $host['is_vip'] = $hostData->is_vip;
            $host['balance'] = $seatBalances[intval($hostData->id)] ?? '0';
            $host['co_host_name'] = $hostData->name;
            $host['set_no'] = "0";
            $host['mute'] = $live ? $live->mute : 0;
            $host['frame'] = strval($hostData->frame);
            $host['co_host_id'] = strval($hostData->id);
            $host['co_host_status'] = 'Accept';
            $host['emoji'] = ($emojiUserId !== '' && $emojiUserId === strval($hostData->id)) ? $emojiValue : "0";
            $host['super_mute'] = "0";
            array_push($list, $host);
        }

        foreach ($acceptList as $call) {
            $coHost = RedisCacheFunction::UserfindById($call->co_host_id);
            if (!$coHost) {
                continue;
            }

            $row = array();
            $row['channelName'] = $channelName;
            $row['profile'] = $coHost->profile;
            $row['is_vip'] = $coHost->is_vip;
            $row['balance'] = $seatBalances[intval($coHost->id)] ?? '0';
            $row['co_host_name'] = $coHost->name;
            $row['set_no'] = $call->set_no;
            $row['mute'] = $call->mute;
            $row['frame'] = strval($coHost->frame);
            $row['co_host_id'] = strval($call->co_host_id);
            $row['co_host_status'] = strval($call->status === 'Accept'
                ? 'Accept'
                : ($call->is_co_host_active ?: $call->status));
            $row['emoji'] = ($emojiUserId !== '' && $emojiUserId === strval($call->co_host_id)) ? $emojiValue : "0";
            $row['super_mute'] = strval($call->super_mute);
            array_push($list, $row);
        }

        return array(
            'message' => $message,
            'host_list' => $list,
            'set_remove' => $setRemove,
            'host_balance' => $progressStats['host_balance'],
            'star' => $progressStats['star'],
            'star_complete_parcent' => $progressStats['star_complete_parcent'],
            'channelName' => $channelName,
            'siteNumber' => strval($seatCount),
            'locked' => strval($live ? $live->locked : 0),
            'locked_seats' => strval($lockedSeats),
            'all_seats_locked' => strval($allSeatsLocked),
            'total_reward' => $progressStats['total_reward'],
            'snapshot_updated_at' => $snapshotUpdatedAt,
            'code' => '200',
        );
    }

    private function broadcastAudioRoomSnapshot(array $snapshot)
    {
        self::Websoket([array(
            'message' => 'bd_audio_call',
            'data' => array($snapshot),
            'channelName' => $snapshot['channelName'] ?? '',
            'code' => '200',
            'event_type' => 'audio.room.snapshot',
        )]);
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
                
                 $call_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                array_push($websoket_kick,array('message'=>'Call Request','channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list,'code'=>'200','event_type' => 'audio.call.pending_list'));
                self::Websoket($websoket_kick);
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                   
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
        // BE-2 hardening: derive the real actor from the Bearer token so a client
        // cannot spoof kick_by to escalate. Legit callers send their own bearer
        // (== the kick_by they'd pass), so behavior is unchanged; a spoofer's real
        // id replaces the forged value and fails the permission check. Falls back
        // to the client value only when no bearer is present (no behavior change).
        $__kbBearer = $request->bearerToken();
        if (!empty($__kbBearer)) {
            $__kbPat = \Laravel\Sanctum\PersonalAccessToken::findToken($__kbBearer);
            if ($__kbPat && $__kbPat->tokenable_id) {
                $kick_by = (string) $__kbPat->tokenable_id;
            }
        }
        $response = array();
        $websoket_kick = array();

        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $check_offical_user=RedisCacheFunction::UserfindById($kick_by);
            $check_admin=BrdAdmin::where('user_id',$host_id)->where('admin_id',$kick_by)->first(); // 2026-07-03: any room-admin slot (1/2/3) may kick
            
            if($kick_by==$host_id || ($check_offical_user && ($check_offical_user->kick_power==1 || $check_offical_user->is_official_id != 0 || $check_offical_user->is_admin == 1 || $check_offical_user->is_bd_admin == 1)) || $check_admin ){
            // BE-3: never kick the host / officials / app-admins (target protection).
            $__ktT = \App\Models\User::find($user_id);
            if ((string) $user_id === (string) $host_id || ($__ktT && ($__ktT->is_official_id != 0 || $__ktT->is_admin == 1 || $__ktT->is_bd_admin == 1))) {
                array_push($response, array('message' => 'This user cannot be kicked', 'code' => '403'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
            $remove_old_call=LiveCall::where('co_host_id',$user_id)->where('host_id',$host_id)->where('channelName',$channelName)->first(); // BE-4: scope kick delete to THIS room
            if($remove_old_call){
            $remove_old_call->delete();
            }
            $kick=new Kick;
            $kick->user_id=$user_id;
            $kick->channelName=$channelName;
            $kick->host_id=$host_id;
            $kick->kick_by=$kick_by;
            $kick->save();
            $user_by_kick=RedisCacheFunction::UserfindById($kick_by);
                array_push($response,array('message'=>'Kick Successfully ','channelName'=>$channelName,'user_id'=>$user_id,'user_by_kick'=>$user_by_kick->name,'channelName'=>$channelName,'code'=>'200'));
           
               array_push($websoket_kick,array('message'=>'bd_kick','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'room.member.kicked'));
                self::Websoket($websoket_kick);
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
            if (!app(\App\Services\AudioRoom\AudioRoomStateService::class)->acquireActionLock(
                $channelName,
                'call_accept',
                [$host_id, $co_host_id, $set_no]
            )) {
                array_push($response,array('message'=>'Duplicate call accept ignored','code'=>'202'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
            $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','pending')->first();
            $seatConflict = LiveCall::where('host_id', $host_id)
                ->where('channelName', $channelName)
                ->where('set_no', $set_no)
                ->where('status', 'Accept')
                ->where('co_host_id', '!=', $co_host_id)
                ->exists();
            if ($seatConflict) {
                array_push($response,array('message'=>'Set Allready Booked','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
          if($data){
            $data->status='Accept';
            $data->is_co_host_active='Accept';
            // BUG FIX (cohost showed muted on audience but unmuted on host): the
            // pending row was created without `mute`, so an approval-accepted cohost
            // had mute=NULL -> audience rendered it as muted. Seat it unmuted like the
            // auto-accept path (CallRequest unlocked branch sets mute=1).
            if ($data->mute === null || $data->mute === '') {
                $data->mute = 1;
            }
            $data->save();
            }
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfull come from call Accept '
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);
       $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
          
                $call_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                array_push($websoket_kick,array('message'=>'Call Request','channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list,'code'=>'200','event_type' => 'audio.call.pending_list'));
                self::Websoket($websoket_kick);
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
   
    

    // Boss 2026-07-03: cohost shifts from their current seat to another FREE,
    // UNLOCKED seat. Broadcasts a fly-animation hint + the authoritative
    // snapshot so the old seat empties and the new seat books on every client.
    public function SeatMove(Request $request)
    {
        $access_token = $request->access_token;
        $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
        $channelName = $request->channelName;
        $to_set_no = $request->to_set_no ?? $request->set_no;
        $response = array();

        if ($access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode([['message' => 'Unauthorized access_token', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }
        if (empty($co_host_id) || empty($channelName) || !is_numeric($to_set_no)) {
            return json_encode([['message' => 'co_host_id, channelName, to_set_no required', 'code' => '400']], JSON_UNESCAPED_UNICODE);
        }
        $toSeat = (int) $to_set_no;
        if ($toSeat < 1) {
            return json_encode([['message' => 'Invalid seat', 'code' => '400']], JSON_UNESCAPED_UNICODE);
        }
        if (!app(\App\Services\AudioRoom\AudioRoomStateService::class)->acquireActionLock(
            $channelName, 'seat_move', [$host_id, $co_host_id, $toSeat]
        )) {
            return json_encode([['message' => 'Duplicate seat move ignored', 'code' => '202']], JSON_UNESCAPED_UNICODE);
        }

        $data = LiveCall::where('host_id', $host_id)->where('channelName', $channelName)
            ->where('co_host_id', $co_host_id)
            ->where(function ($query) {
                $query->where('status', 'Accept')
                    ->orWhere('is_co_host_active', 'Accept');
            })
            ->first();
        if (!$data) {
            return json_encode([['message' => 'You are not seated', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }
        $fromSeat = (int) $data->set_no;
        if ($fromSeat === $toSeat) {
            return json_encode([['message' => 'Already on this seat', 'code' => '200']], JSON_UNESCAPED_UNICODE);
        }

        // Target seat must be FREE (no other accepted occupant).
        $occupied = LiveCall::where('host_id', $host_id)->where('channelName', $channelName)
            ->where('set_no', $toSeat)
            ->where(function ($query) {
                $query->where('status', 'Accept')
                    ->orWhere('is_co_host_active', 'Accept');
            })
            ->where('co_host_id', '!=', $co_host_id)->exists();
        if ($occupied) {
            return json_encode([['message' => 'Seat already booked', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }
        // Target seat must NOT be locked (per-seat lock row or whole-room lock).
        $locked = LiveCall::where('host_id', $host_id)->where('channelName', $channelName)
            ->where('set_no', $toSeat)->where('status', 'locked')->exists();
        $live = \App\Models\UserLive::where('channelName', $channelName)->first();
        $allLocked = $live && (string) $live->locked === '1';
        if ($locked || $allLocked) {
            return json_encode([['message' => 'Seat is locked', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }

        $data->set_no = $toSeat;
        $data->status = 'Accept';
        $data->is_co_host_active = 'Accept';
        $data->save();

        // Fly-animation hint for all clients (old -> new seat).
        self::Websoket([[
            'message' => 'bd_audio_seat_move',
            'channelName' => $channelName,
            'co_host_id' => (string) $co_host_id,
            'from_set_no' => (string) $fromSeat,
            'to_set_no' => (string) $toSeat,
            'code' => '200',
            'event_type' => 'audio.seat.moved',
        ]]);

        try {
            $moveId = 'legacy_audio_seat_move:' . $channelName . ':' . $co_host_id . ':' . $fromSeat . ':' . $toSeat . ':' . round(microtime(true) * 1000);
            $seatBalances = $this->buildAudioRoomSeatBalanceMap($channelName, array($co_host_id));
            $coHost = RedisCacheFunction::UserfindById($co_host_id);

            app(\App\Services\V5\RoomSeatUpdateService::class)->emitSeatChange(
                'audio',
                $channelName,
                (int) $fromSeat,
                array(
                    'cleared' => true,
                    'co_host_id' => (string) $co_host_id,
                    'cohost_id' => (string) $co_host_id,
                    'user_id' => (string) $co_host_id,
                    'from_set_no' => (string) $fromSeat,
                    'to_set_no' => (string) $toSeat,
                    'move_id' => $moveId,
                ),
                (string) $host_id
            );

            app(\App\Services\V5\RoomSeatUpdateService::class)->emitSeatChange(
                'audio',
                $channelName,
                (int) $toSeat,
                array(
                    'channelName' => (string) $channelName,
                    'profile' => $coHost ? $coHost->profile : null,
                    'is_vip' => $coHost ? (string) $coHost->is_vip : '0',
                    'balance' => $seatBalances[(int) $co_host_id] ?? '0',
                    'co_host_name' => $coHost ? (string) $coHost->name : '',
                    'set_no' => (string) $toSeat,
                    'mute' => (string) ($data->mute ?? '1'),
                    'frame' => $coHost ? (string) $coHost->frame : '',
                    'co_host_id' => (string) $co_host_id,
                    'cohost_id' => (string) $co_host_id,
                    'user_id' => (string) $co_host_id,
                    'co_host_status' => 'Accept',
                    'status' => 'Accept',
                    'super_mute' => (string) ($data->super_mute ?? '0'),
                    'from_set_no' => (string) $fromSeat,
                    'to_set_no' => (string) $toSeat,
                    'move_id' => $moveId,
                ),
                (string) $host_id
            );
        } catch (\Throwable $t) {
            Log::warning('Legacy audio seat move delta emit failed', array(
                'channel' => $channelName,
                'host_id' => $host_id,
                'co_host_id' => $co_host_id,
                'from_set_no' => $fromSeat,
                'to_set_no' => $toSeat,
                'error' => $t->getMessage(),
            ));
        }

        // Authoritative snapshot (empties old seat, books new seat).
        $snapshot = $this->buildAudioRoomHostSnapshotPayload($host_id, $channelName, 'Audio Seat Moved');
        array_push($response, $snapshot);
        $this->broadcastAudioRoomSnapshot($snapshot);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
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
            // Boss 2026-06-27: acquireActionLock removed from CallRequest so audience can
            //   re-tap Be Cohost after a cut without waiting 8s. The row-delete-and-recreate
            //   below is already idempotent, so this Redis lock just blocks legit retries.
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
                // SEAT_RACE_ATOMIC (Boss 2026-07-07): seat resolved server-side; see apply.php.
                if ($live->locked != 0) {
                    // LOCKED room: host approves. Keep the seat-taken guard but
                    // ignore 'locked' sentinel rows.
                    // SEAT_BOUNDS_LOCKED_v1 (2026-07-18): set_no was never
                    // validated against the room's configured seat count on
                    // this branch (only the unlocked/atomic branch below
                    // bounds it), so a stale/out-of-range request (e.g. sent
                    // before the host switched the room to a 2-seat heart /
                    // 3-seat butterfly layout) could be accepted onto a seat
                    // outside the room's real capacity -- a genuinely
                    // "Accept" row the client can't treat as a stale ghost,
                    // knocking the heart/butterfly shape back to a plain
                    // round layout on every client until the host manually
                    // removed it. Reject out-of-range requests up front.
                    $seatCount = 8;
                    foreach (array('siteNumber','site_number','seatNumber','seat_number') as $col) {
                        if (isset($live->{$col}) && (int)$live->{$col} >= 2) {
                            $seatCount = max(2, min(15, (int)$live->{$col}));
                            break;
                        }
                    }
                    $reqSeat = (int) $set_no;
                    if ($reqSeat < 1 || $reqSeat > $seatCount) {
                        array_push($response, array('message'=>'Invalid Seat Number','code'=>'401'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    $check_call_set = LiveCall::where('channelName','=',$channelName)
                        ->where('type',1)->where('host_id',$host_id)
                        ->where('set_no',$set_no)->where('status','!=','locked')->first();
                    if ($check_call_set) {
                        array_push($response, array('message'=>'Set Allready Booked','code'=>'401'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    $data = new LiveCall;
                    $data->co_host_id = $co_host_id;
                    $data->channelName = $channelName;
                    $data->type = $live->type;
                    $data->host_id = $host_id;
                    $data->set_no = $set_no;
                    $data->status = 'pending';
                    $data->super_mute = '0';
                    $data->save();

                    $call_count = DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
                    $call_list = DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

                    array_push($websoket_kick, array('message'=>'Call Request','channelName'=>$channelName,'call_count'=>$call_count,'data'=>$call_list,'code'=>'200','event_type' => 'audio.call.pending_list'));
                    self::Websoket($websoket_kick);
                    array_push($response, array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    // UNLOCKED room: ATOMIC server-authoritative seat resolution.
                    // Two concurrent "Be Cohost" taps serialise on lockForUpdate
                    // and each gets a distinct lowest-free, unlocked seat, so no
                    // two cohosts share a set_no (root cause of "one joins, the
                    // other auto-downs"). Client $set_no is only a hint.
                    $resolvedSeat = DB::transaction(function () use ($channelName, $host_id, $co_host_id, $set_no, $live) {
                        $rows = LiveCall::where('channelName',$channelName)
                            ->where('host_id',$host_id)
                            ->whereIn('status',['Accept','locked'])
                            ->lockForUpdate()->get();
                        $occupied = array();
                        $lockedSeats = array();
                        $selfSeat = null;
                        foreach ($rows as $r) {
                            $n = (int) $r->set_no;
                            if ($r->status === 'locked') {
                                $lockedSeats[$n] = true;
                            } else {
                                if ((string)$r->co_host_id === (string)$co_host_id) {
                                    $selfSeat = $n; // idempotent re-tap
                                }
                                $occupied[$n] = true;
                            }
                        }
                        if ($selfSeat !== null) {
                            return $selfSeat;
                        }
                        $seatCount = 8;
                        foreach (array('siteNumber','site_number','seatNumber','seat_number') as $col) {
                            if (isset($live->{$col}) && (int)$live->{$col} >= 2) {
                                $seatCount = max(2, min(15, (int)$live->{$col}));
                                break;
                            }
                        }
                        $req = (int) $set_no;
                        $seat = null;
                        if ($req >= 1 && $req <= $seatCount && empty($occupied[$req]) && empty($lockedSeats[$req])) {
                            $seat = $req;
                        } else {
                            for ($i = 1; $i <= $seatCount; $i++) {
                                if (empty($occupied[$i]) && empty($lockedSeats[$i])) { $seat = $i; break; }
                            }
                        }
                        if ($seat === null) {
                            return 0; // room full
                        }
                        $data = new LiveCall;
                        $data->co_host_id = $co_host_id;
                        $data->channelName = $channelName;
                        $data->type = $live->type;
                        $data->host_id = $host_id;
                        $data->set_no = $seat;
                        $data->mute = 1;
                        $data->status = 'Accept';
                        $data->is_co_host_active = 'Accept';
                        $data->save();
                        return $seat;
                    });
                    if ($resolvedSeat === 0) {
                        array_push($response, array('message'=>'Room Full','code'=>'401'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                        $host_id,
                        $channelName,
                        'Audio Call Accept List Data Show Successfully come from  Call Request UnLockBrd '
                    );
                    array_push($response, $snapshot);
                    $this->broadcastAudioRoomSnapshot($snapshot);
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
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

            $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

            array_push($response,array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
     public function Store(Request $request)
{
    $response = array();

    try {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $channelName = $request->channelName;
        $token = $request->token;
        $type = $request->type;
        $image = $request->image;
        $pin = $request->pin;
        $notice = $request->notice;

        $date = \Carbon\Carbon::now();

        $list = array();
        $websocket_call = array();

        if ($access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = array('message' => 'Unauthorized22', 'code' => '401');
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $remove_old_call = LiveCall::where('co_host_id', $user_id)->first();
        if ($remove_old_call) {
            $remove_old_call->delete();
        }

        $user = RedisCacheFunction::UserfindById($user_id);

        if (!$user) {
            $response[] = array('message' => 'User not found', 'code' => '404');
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $user_total_gift_recived_today = Gift::where('reciever_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->sum('value');

        $top_value = $user->top_value + $user_total_gift_recived_today;

        $avater = Avater::where('user_id', $user_id)->first();

        // BUG FIX (host showed e.g. 2 seats but audience showed 8): the host's
        // chosen seat count was never persisted, so user_lives.siteNumber kept a
        // stale/default value (8) that the audience snapshot then read, while the
        // host rendered its own go-live selection. Persist siteNumber here so the
        // DB (and therefore the audience) matches the host's real seat count.
        $requestedSeatCount = $request->siteNumber
            ?? $request->site_number
            ?? $request->seatNumber
            ?? $request->seat_number;
        $seatCount = max(2, min(15, intval($requestedSeatCount ?: 2)));

        UserLive::storeOneActiveForUser(array(
            'user_id' => $user_id,
            'channelName' => $channelName,
            'name' => $user->name,
            'top_value' => $top_value,
            'type' => $type,
            'notice' => $notice,
            'pin' => $pin,
            'siteNumber' => $seatCount,
            'avatar' => $avater ? $avater->image : $user->profile,
            'backgorund' => $image ? $image : '',
            'audio_brd_design' => $request->audio_brd_design ? $request->audio_brd_design : '1',
            'token' => $token,
            'date' => $date,
        ));

        $this->CacheRemoved();

        $host_data = RedisCacheFunction::UserfindById($user_id);

        if (!$host_data) {
            $response[] = array('message' => 'Host data not found', 'code' => '404');
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $snapshot = $this->buildAudioRoomHostSnapshotPayload(
            $user_id,
            $channelName,
            'Host List come from BRD start '
        );
        $response[] = $snapshot;
        $this->broadcastAudioRoomSnapshot($snapshot);
        self::send_ws_notification($host_data, $channelName, $type);

        return json_encode($response, JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        $response[] = array(
            'message' => 'Internal Server Error',
            'code' => '500',
            'error' => $e->getMessage()
        );

        return json_encode($response, JSON_UNESCAPED_UNICODE);
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
         // ("speaker off"). A host may mute regular cohosts/audience but not an
         // Official or apps-admin. Only blocks the force-mute (super_mute=1);
         // unmute and self-mute paths are unaffected.
         if ($super_mute == 1) {
             $muteTarget = \App\Models\User::find($co_host_id);
             if ($muteTarget && ($muteTarget->is_official_id != 0 || $muteTarget->is_admin == 1 || $muteTarget->is_bd_admin == 1)) {
                 array_push($response, array('message' => 'Official / admin cannot be speaker-muted', 'code' => '403'));
                 return json_encode($response, JSON_UNESCAPED_UNICODE);
             }
         }
         if (!app(\App\Services\AudioRoom\AudioRoomStateService::class)->acquireActionLock(
             $channelName,
             'call_mute',
             [$host_id, $co_host_id, $mute_satus, $super_mute]
         )) {
             array_push($response,array('message'=>'Duplicate call mute ignored','code'=>'202'));
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         }
         $data=LiveCall::where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('host_id',$host_id)->where('status','Accept')->first();
// BE-5: only an authorized moderator (host/official/app-admin) may CLEAR an
         // active super_mute. Blocks a speaker-off cohost from self-unmuting via
         // super_mute=0. Fails open when the actor can't be resolved (no bearer).
         if ($data && (int)$data->super_mute === 1 && (int)$super_mute !== 1) {
             $__smActor = '';
             $__smBearer = $request->bearerToken();
             if (!empty($__smBearer)) {
                 $__smPat = \Laravel\Sanctum\PersonalAccessToken::findToken($__smBearer);
                 if ($__smPat && $__smPat->tokenable_id) { $__smActor = (string) $__smPat->tokenable_id; }
             }
             if ($__smActor !== '') {
                 $__smU = \App\Models\User::find($__smActor);
                 $__smAuth = ($__smActor === (string) $host_id)
                     || ($__smU && ($__smU->is_official_id != 0 || $__smU->is_admin == 1 || $__smU->is_bd_admin == 1))
                     || \App\Models\BrdAdmin::where('user_id', $host_id)->where('admin_id', $__smActor)->exists();
                 if (!$__smAuth) {
                     array_push($response, array('message' => 'Only the host can remove speaker-off', 'code' => '403'));
                     return json_encode($response, JSON_UNESCAPED_UNICODE);
                 }
             }
         }
          if($data){
          $data->mute=$mute_satus;
          $data->mute_time = $mute_satus == 0 ? Carbon\Carbon::now() : null;
          $data->super_mute = $super_mute == 1 ? 1 : 0;
          $data->save();
          }
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Call Mute Successfully'
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);
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
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfully come from remove call ',
                array('set_remove' => $set_no)
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);
             
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
              if (!app(\App\Services\AudioRoom\AudioRoomStateService::class)->acquireActionLock(
                  $channelName,
                  'host_call_remove',
                  [$host_id, $co_host_id, $set_no]
              )) {
                  array_push($response,array('message'=>'Duplicate host call remove ignored','code'=>'202'));
                  return json_encode($response,JSON_UNESCAPED_UNICODE);
              }
              array_push($response,array('message'=>'Audio Call Removed By Host ','co_host_id'=>$co_host_id,'set_remove'=>$set_no,'host_id'=>$host_id,'channelName'=>$channelName,'code'=>'200'));
              
              $roomName='audio_host_call_remove';
             
              array_push($websocket_call,array('message'=>'bd_audio_host_call_remove','data'=>$response,'channelName'=>$channelName,'code'=>'200','event_type' => 'audio.seat.updated'));
                self::Websoket($websocket_call);
                
            $data = LiveCall::where('host_id', $host_id)
                    ->where('channelName', $channelName)
                    ->where('co_host_id', $co_host_id)
                    ->where('status', 'Accept')
                    ->first();
                
                if ($data) {
                    $data->delete();
                }
                $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                    $host_id,
                    $channelName,
                    'Audio Call Accept List Data Show Successfully come from remove call ',
                    array('set_remove' => $set_no)
                );
                $this->broadcastAudioRoomSnapshot($snapshot);
                
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
            $data=RedisCacheFunction::UserfindById($user_id);
            if (!$data) {
                $data = User::find($user_id);
            }
            if (!$data) {
                array_push($response,array('message'=>'User not found','code'=>'404'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
            $follow_status=0;
            $is_host=DB::table('host_data')
                ->join('users','users.id','host_data.user_id')
                ->join('agencies','agencies.code','host_data.agency_code')
                ->where('users.is_host_id',1)
                ->where('users.id',$user_id)
                ->select('host_data.hosting_type','agencies.name')
                ->first();

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
            $total_withdraw=Withdraw::where('host_id',$user_id)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('total');

            $previous_coin = is_numeric($data->previous_coin ?? null) ? $data->previous_coin : 0;
            $total_data_sum= ($previous_coin+$total_gift_coin);

            $agency_name='Bp';

            if(!empty($channelName) && !empty($host_id)){
                $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                    $host_id,
                    $channelName,
                    'Audio Call Accept List Data Show Successfully From User Data '
                );
                array_push($joinresponse, $snapshot);
                $this->broadcastAudioRoomSnapshot($snapshot);
            }

            array_push($response,array(
                'message'=>'User Data Show Successfully ',
                'code'=>'200',
                'data'=>$data,
                'follow_status'=>$follow_status,
                'balance'=>$total_data_sum,
                'agency_name'=>$agency_name,
                'host_type'=>$host_type,
                'marchent'=>$data->is_agency,
                'is_coin_protal_active'=>$data->is_coin_protal_active,
                'is_vip'=>$data->is_vip,
                'frame'=>$data->frame,
                'entry_effect'=>$data->entry
            ));

            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            $data = RedisCacheFunction::UserfindById($user_id);
            $follower = RedisCacheFunction::UserfindById($host_id);

            $isFollowing = $data->following()->where('follower_id', $follower->id)->exists();
            $isFollowedBy = $data->followers()->where('user_id', $follower->id)->exists();

            $areFriends = $isFollowing && $isFollowedBy;
            if($areFriends)
            {
                $follow_status=2;
            }elseif($isFollowing){
                $follow_status=1;
            }else{
                $follow_status=1;
            }

            $is_host=DB::table('host_data')
                ->join('users','users.id','host_data.user_id')
                ->join('agencies','agencies.code','host_data.agency_code')
                ->where('users.is_host_id',1)
                ->where('users.id',$data->user_id)
                ->select('host_data.hosting_type','agencies.name')
                ->first();

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

            $total_gift_coin=$query->sum('value');

            $total_withdraw=Withdraw::where('host_id',$user_id)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('total');

            $hostGiftIncome = (int) $total_gift_coin;
            $hostWithdrawTotal = (int) $total_withdraw;
            $hostNetGiftBalance = $hostGiftIncome - $hostWithdrawTotal;

            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfully From User Data '
            );
            array_push($joinresponse, $snapshot);
            array_push($response,array(
                'message'=>'User Data Show Successfully',
                'code'=>'200',
                'data'=>$data,
                'follow_status'=>$follow_status,
                'balance'=>(int) $data->balance,
                'host_gift_income'=>$hostGiftIncome,
                'host_withdraw_total'=>$hostWithdrawTotal,
                'host_net_gift_balance'=>$hostNetGiftBalance,
                'agency_name'=>$agency_name,
                'host_type'=>$host_type,
                'marchent'=>$data->is_agency,
                'is_coin_protal_active'=>$data->is_coin_protal_active,
                'is_vip'=>$data->is_vip,
                'frame'=>$data->frame,
                'entry_effect'=>$data->entry
            ));

            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }

    }else{
        Log::warning('UserData unauthorized', array(
            'host_id' => $host_id,
            'audience_id' => $user_id,
            'channelName' => $channelName
        ));

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
            // BE-7: only the host (or an official/app-admin) may toggle the host mic.
            // Bearer-derived actor; fails open when the actor can't be resolved.
            $__hmBearer = $request->bearerToken();
            if (!empty($__hmBearer)) {
                $__hmPat = \Laravel\Sanctum\PersonalAccessToken::findToken($__hmBearer);
                $__hmActor = ($__hmPat && $__hmPat->tokenable_id) ? (string) $__hmPat->tokenable_id : '';
                if ($__hmActor !== '' && $__hmActor !== (string) $host_id) {
                    $__hmU = \App\Models\User::find($__hmActor);
                    $__hmOK = ($__hmU && ($__hmU->is_official_id != 0 || $__hmU->is_admin == 1 || $__hmU->is_bd_admin == 1))
                        || \App\Models\BrdAdmin::where('user_id', $host_id)->where('admin_id', $__hmActor)->exists();
                    if (!$__hmOK) {
                        array_push($response, array('message' => 'Not allowed to change host mic', 'code' => '403'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                }
            }
            $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
            if($live)
            {
                $live->mute=$mute_satus;
                $live->save();
            }
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfully come from Host Mute Unmute '
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);
              return json_encode($response,JSON_UNESCAPED_UNICODE);
            
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function AudioGiftPush(Request $request)
    {
        $token = trim((string) $this->requestValue($request, array('access_token'), ''));
        $user_id = trim((string) $this->requestValue($request, array('user_id', 'id'), ''));
        $value = intval($this->requestValue($request, array('value'), 0));
        $gift_name = trim((string) $this->requestValue($request, array('giftName', 'gift_name', 'name'), ''));
        $channelName = trim((string) $this->requestValue($request, array('channelName', 'channel_name', 'room_name'), ''));
        $music = trim((string) $this->requestValue($request, array('music'), ''));
        $gift_type = trim((string) $this->requestValue($request, array('gift_type', 'giftType'), ''));
        $host_id = trim((string) $this->requestValue($request, array('host_id', 'live_id'), ''));
        $response = array();
        $pusher_response = array();
        $global_txt = array();
        $websocket_call = array();
        $gift_global_websoket = array();
        $global_websoket = array();
        $gift_effect = array();
        $items = $this->normalizedGiftItems($request);
        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
        $i=0;
        $j=0;
               if ($user_id === '') {
                    return $this->missingFieldResponse('user_id', array('user_id', 'id'));
                }
               if ($host_id === '') {
                    return $this->missingFieldResponse('host_id', array('host_id', 'live_id'));
                }
               if ($channelName === '') {
                    return $this->missingFieldResponse('channelName', array('channelName', 'channel_name', 'room_name'));
                }
               if ($gift_name === '') {
                    return $this->missingFieldResponse('giftName', array('giftName', 'gift_name', 'name'));
                }
               if ($gift_type === '') {
                    return $this->missingFieldResponse('gift_type', array('gift_type', 'giftType'));
                }
               if ($value <= 0) {
                    return response()->json([[
                        'message' => 'Gift value must be greater than 0',
                        'field' => 'value',
                        'accepted_fields' => array('value'),
                        'code' => '422',
                    ]], 422);
                }
               if (empty($items)) {
                    return $this->missingFieldResponse('items', array('items'));
                }
               if (in_array($user_id, [79861, 43836])) {
                    return response()->json([
                        'message' => 'Must Send at Least One Gift',
                        'code' => 401
                    ], 401);
                }

              $normalizedReceivers = array();
              foreach ($items as $row) {
                  $receiverModel = RedisCacheFunction::UserfindById($row['receiverId']);
                  if (!$receiverModel) {
                      return response()->json([[
                          'message' => 'Invalid gift receiver',
                          'field' => 'items',
                          'accepted_fields' => array('items.receiverId'),
                          'code' => '422',
                      ]], 422);
                  }
                  $normalizedReceivers[] = array(
                      'receiverId' => $row['receiverId'],
                      'receiver' => $receiverModel,
                  );
              }

              $sander = RedisCacheFunction::UserfindById($user_id);
              if (!$sander) {
                  return response()->json([[
                      'message' => 'Gift sender not found',
                      'field' => 'user_id',
                      'accepted_fields' => array('user_id', 'id'),
                      'code' => '422',
                  ]], 422);
              }

              $totalCost = $value * count($normalizedReceivers);
              if ($sander->balance < $totalCost) {
                  return response()->json([[
                      'message' => 'Insufficient balance',
                      'field' => 'value',
                      'accepted_fields' => array('value', 'items'),
                      'code' => '422',
                  ]], 422);
              }

              $giftFingerprint = [
                  $user_id,
                  $host_id,
                  $channelName,
                  $gift_name,
                  $value,
                  $gift_type,
                  $items,
              ];
              if (!app(\App\Services\AudioRoom\AudioRoomStateService::class)->acquireActionLock(
                  $channelName,
                  'gift_push',
                  $giftFingerprint,
                  10
              )) {
                  array_push($response, array('message' => 'Duplicate gift ignored', 'code' => '202'));
                  return json_encode($response, JSON_UNESCAPED_UNICODE);
              }

                if (!empty($normalizedReceivers)) {
                    $newFileName = Str::title(str_replace(['_', '.svga'], [' ', ''], $gift_name));

                    try {
                        $transactionResult = DB::transaction(function () use (
                            $user_id,
                            $host_id,
                            $channelName,
                            $gift_name,
                            $value,
                            $normalizedReceivers,
                            $newFileName
                        ) {
                            $sender = User::where('id', $user_id)->lockForUpdate()->first();
                            if (!$sender) {
                                throw new \RuntimeException('Gift sender not found');
                            }

                            $transactionCost = $value * count($normalizedReceivers);
                            if ((int) $sender->balance < $transactionCost) {
                                throw new \RuntimeException('Insufficient balance');
                            }

                            $sender->balance -= $transactionCost;
                            $sender->save();

                            $receiverNames = array();
                            $globalNotifications = array();
                            $firstReceiverId = null;

                            foreach ($normalizedReceivers as $row) {
                                $receiverId = $row['receiverId'];
                                $receiverModel = $row['receiver'];

                                if ($firstReceiverId === null) {
                                    $firstReceiverId = $receiverId;
                                }

                                $receiverNames[] = $receiverModel->name;

                                $gift = new Gift;
                                $gift->sander_id = $user_id;
                                $gift->reciever_id = $receiverId;
                                $gift->name = $gift_name;
                                $gift->value = $value;
                                $gift->channelName = $channelName;
                                $gift->date = Carbon\Carbon::now();
                                $gift->save();

                                $check_user_live = UserLive::where('user_id', $receiverModel->id)->first();
                                if ($check_user_live) {
                                    $user_total_gift_recived_today = Gift::where('reciever_id', $receiverModel->id)
                                        ->whereDate('date', now()->toDateString())
                                        ->sum('value');
                                    $top_value = $receiverModel->top_value + $user_total_gift_recived_today;
                                    $check_user_live->top_value = $top_value;
                                    $check_user_live->save();
                                }

                                if ($value > 49999 || $user_id == 1111) {
                                    $globalNotifications[] = array(
                                        'message' => "{$sender->name} sent {$value} to {$receiverModel->name}",
                                        'image' => $sender->profile,
                                        'receiver_profile' => $receiverModel->profile,
                                        'name' => $sender->name,
                                    );
                                }
                            }

                            $total = Gift::where('sander_id', $user_id)->sum('value') + OldGift::where('sander_id', $user_id)->sum('value');
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

                                if ((int) $sender->level < $level) {
                                    $sender->level = $level;
                                    $sender->save();
                                }
                            }

                            $comment = new Comment;
                            $comment->user_id = $user_id;
                            $comment->channelName = $channelName;
                            $comment->message = "{$sender->name} sent {$newFileName}  ({$value}) to " . implode(', ', $receiverNames);
                            $comment->reciever_id = $host_id;
                            $comment->type = 'message';
                            $comment->save();

                            return array(
                                'sender_id' => $sender->id,
                                'sender_balance' => (int) $sender->balance,
                                'global_notifications' => $globalNotifications,
                                'first_receiver_id' => $firstReceiverId,
                            );
                        });
                    } catch (\RuntimeException $e) {
                        return response()->json([[
                            'message' => $e->getMessage(),
                            'code' => '422',
                        ]], 422);
                    } catch (\Throwable $e) {
                        Log::error('AudioGiftPush transaction failed', array(
                            'user_id' => $user_id,
                            'host_id' => $host_id,
                            'channelName' => $channelName,
                            'error' => $e->getMessage(),
                        ));

                        return response()->json([[
                            'message' => 'Gift send failed',
                            'code' => '500',
                        ]], 500);
                    }

                    foreach ($transactionResult['global_notifications'] as $notification) {
                        $global_txt[] = $notification;
                        $gift_global_websoket = array(array(
                            'message' => 'bd_global_gift',
                            'channelName' => $channelName,
                            'data' => $global_txt,
                            'code' => '200',
                            'event_type' => 'gift.global'
                        ));
                        self::Websoket($gift_global_websoket);
                    }

                    if ($transactionResult['first_receiver_id']) {
                        $receiverBalance = Gift::where('reciever_id', $transactionResult['first_receiver_id'])
                            ->where('channelName', $channelName)
                            ->sum('value');
                        $progressStats = $this->buildAudioRoomProgressStats($host_id);

                        $giftReceiverNames = array();
                        foreach ($normalizedReceivers as $rrow) {
                            if (isset($rrow['receiver']) && isset($rrow['receiver']->name)) {
                                $giftReceiverNames[] = $rrow['receiver']->name;
                            }
                        }
                        $giftReceiverNameStr = implode(', ', $giftReceiverNames);
                        $count = [
                            'channelName' => $channelName,
                            'name' => $gift_name,
                            'gift_name' => strval($newFileName),
                            'value' => strval($value),
                            'sender_id' => strval($sander->id),
                            'sender_name' => strval($sander->name),
                            'sender_profile' => strval($sander->profile),
                            'receiver_name' => strval($giftReceiverNameStr),
                            'gift_time' => strval(5),
                            'host_balance' => strval($progressStats['host_balance']),
                            'host_balance_after_gift' => strval($progressStats['host_balance']),
                            'room_balance' => strval($progressStats['host_balance']),
                            'receiver_balance' => strval($receiverBalance),
                            'star' => strval($progressStats['star']),
                            'star_complete_parcent' => strval($progressStats['star_complete_parcent']),
                            'total_reward' => strval($progressStats['total_reward']),
                            'music' => strval($music),
                            'audience_balance' => strval($transactionResult['sender_balance']),
                            'reciever_id' => strval($transactionResult['first_receiver_id']),
                            'items' => $items,
                            'status' => 'active',
                            'gift_type' => strval($gift_type),
                        ];

                        array_push($gift_effect, array(
                            'message' => 'Audio gift',
                            'channelName' => $channelName,
                            'data' => $count,
                            'code' => '200',
                            'event_type' => 'room.gift.sent'
                        ));
                        self::Websoket($gift_effect);
                    }

                    $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                        $host_id,
                        $channelName,
                        'Audio Call Accept List Data Show Successfull come from call Accept '
                    );
                    array_push($pusher_response, $snapshot);
                    $this->broadcastAudioRoomSnapshot($snapshot);
                    array_push($response, array(
                        'message' => 'Gifts Sent Successfully',
                        'user_id' => $transactionResult['sender_id'],
                        'balance' => $transactionResult['sender_balance'],
                        'code' => '200'
                    ));

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
                // Broadcast the new lock state so the AUDIENCE updates its lock UI
                // in real time (it previously only changed on the host). The
                // snapshot carries `locked`, so every client renders the same.
                $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                    $host_id,
                    $channelName,
                    'Audio Brd lock state changed'
                );
                $this->broadcastAudioRoomSnapshot($snapshot);

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

    // Persist a host's seat-count change so it survives (was calling a route that
    // did not exist, so the change was never saved = not permanent), and rebroadcast
    // the snapshot (which carries siteNumber) so host + audience show the same count.
    public function SeatCountUpdate(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        $requested = $request->seat_count
            ?? $request->siteNumber
            ?? $request->site_number
            ?? $request->seatNumber;
        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        $seatCount = max(2, min(15, intval($requested ?: 2)));
        $live = UserLive::where('channelName', '=', $channelName)->where('user_id', $host_id)->first();
        if (!$live) {
            array_push($response, array('message' => 'Live Removed Already', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        $live->siteNumber = $seatCount;
        $live->save();

        // SEATSHRINK_CUT_v1: when the seat count shrinks, any ACCEPTED cohost on
        // a now out-of-range seat (set_no > $seatCount) becomes a GHOST — the
        // client render drops it but the LiveCall row + RTC publish survive and
        // the very next snapshot would re-introduce it. Really CUT them here:
        // broadcast the same host-call-remove frame (channel_type 23) each side
        // uses to clear the seat + stop the RTC publish, delete the LiveCall row
        // so the rebuilt snapshot below can never re-add the ghost.
        $ghostRows = LiveCall::where('host_id', $host_id)
            ->where('channelName', $channelName)
            ->where('status', 'Accept')
            ->where('set_no', '>', $seatCount)
            ->get();
        foreach ($ghostRows as $ghost) {
            $ghostCoHostId = strval($ghost->co_host_id);
            $ghostSetNo = strval($ghost->set_no);
            $cutResponse = array(array(
                'message'    => 'Audio Call Removed By Host ',
                'co_host_id' => $ghostCoHostId,
                'set_remove' => $ghostSetNo,
                'host_id'    => strval($host_id),
                'channelName'=> $channelName,
                'code'       => '200',
            ));
            self::Websoket(array(array(
                'message'      => 'bd_audio_host_call_remove',
                'data'         => $cutResponse,
                'channelName'  => $channelName,
                'code'         => '200',
                'channel_type' => '23',
            )));
            // V5 parity: cutCohost emits the typed room.cohost.cut + a cleared
            // seat delta on the private channel AND deletes the LiveCall row, so
            // V5 audiences (legacy socket disconnected) clear the seat + drop the
            // RTC publish, and the rebuilt snapshot below can never re-add it.
            try {
                app(\App\Services\V5\RoomActionService::class)
                    ->cutCohost('audio', $channelName, strval($host_id), array(
                        'co_host_id' => $ghostCoHostId,
                        'user_id'    => $ghostCoHostId,
                        'set_no'     => $ghostSetNo,
                    ));
            } catch (\Throwable $e_cut) {
                info('seatshrink v5 cut failed: '.$e_cut->getMessage());
                // Fallback: ensure the ghost row is gone even if the V5 cut threw.
                $ghost->delete();
            }
        }
        $snapshot = $this->buildAudioRoomHostSnapshotPayload(
            $host_id,
            $channelName,
            'Audio Brd seat count updated'
        );
        $this->broadcastAudioRoomSnapshot($snapshot);
        array_push($response, array('message' => 'Seat count updated', 'siteNumber' => strval($seatCount), 'code' => '200'));
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    // Persist per-seat locks for the room so they survive rejoin/refresh and the
    // audience renders the same locked seats as the host. Stored in the room cache
    // (the app DB user has no ALTER right to add a column) and carried by the
    // snapshot which is fetched on join and rebroadcast here.
    public function SeatLockUpdate(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        // Normalise to a sorted, de-duped CSV of seat numbers (e.g. "2,5,7").
        $raw = (string) ($request->locked_seats ?? '');
        $seats = array();
        foreach (preg_split('/[,\s]+/', $raw) as $part) {
            $n = intval(trim($part));
            if ($n >= 1 && $n <= 15) { $seats[$n] = $n; }
        }
        ksort($seats);
        $csv = implode(',', array_values($seats));

        // Persist each locked seat as a live_calls ROW with a status='locked'
        // sentinel (DDL is denied, but INSERT/DELETE on existing tables are
        // allowed). status='locked' rows are isolated from every Accept/pending
        // query, so they never act as cohosts. Rebuild the set each call.
        $live = UserLive::where('channelName', $channelName)->where('user_id', $host_id)->first();
        $type = $live ? $live->type : 1;
        LiveCall::where('channelName', $channelName)
            ->where('host_id', $host_id)
            ->where('status', 'locked')
            ->delete();
        foreach (array_values($seats) as $setNo) {
            $row = new LiveCall;
            $row->co_host_id = '0';
            $row->channelName = $channelName;
            $row->type = $type;
            $row->host_id = $host_id;
            $row->set_no = $setNo;
            $row->status = 'locked';
            $row->super_mute = '0';
            $row->save();
        }

        $snapshot = $this->buildAudioRoomHostSnapshotPayload(
            $host_id,
            $channelName,
            'Audio Brd seat locks updated'
        );
        $this->broadcastAudioRoomSnapshot($snapshot);
        array_push($response, array('message' => 'Seat locks updated', 'locked_seats' => $csv, 'code' => '200'));
        return json_encode($response, JSON_UNESCAPED_UNICODE);
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
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfully come from  call mute '
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);

             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    } 
    public function SandEmoji(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $co_host_id = $request->co_host_id;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
         $emoji = $request->emoji;
        $response = array();
        $websocket_call = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $snapshot = $this->buildAudioRoomHostSnapshotPayload(
                $host_id,
                $channelName,
                'Audio Call Accept List Data Show Successfully come from  call mute ',
                array(
                    'emoji_user_id' => $co_host_id,
                    'emoji' => $emoji,
                )
            );
            array_push($response, $snapshot);
            $this->broadcastAudioRoomSnapshot($snapshot);
             return json_encode($response,JSON_UNESCAPED_UNICODE);
         
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    
    

    private function CacheRemoved()
    {
        return $this->clearJustHomeLists();
    }
 private function Websoket($data)
{
    try {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'AudioBrdController']);

    } catch (\Throwable $th) {
        info('Local WebSocket dispatch failed: ' . $th->getMessage());
    }
}

//     private function Websoket($data) {
//     $response_json = json_encode($data);

//     try {
//         // Initialize cURL
//         $curl = curl_init();
        
//         curl_setopt_array($curl, array(
//             CURLOPT_URL => 'http://bdlive.org/api/bd_recieve-two',
//             CURLOPT_RETURNTRANSFER => true,
//             CURLOPT_ENCODING => '',
//             CURLOPT_MAXREDIRS => 10,
//             CURLOPT_TIMEOUT => 15,
//             CURLOPT_FOLLOWLOCATION => true,
//             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//             CURLOPT_CUSTOMREQUEST => 'POST',
//             CURLOPT_POSTFIELDS => $response_json,
//             CURLOPT_HTTPHEADER => array(
//                 'Accept: application/json',
//                 'Content-Type: application/json'
//             ),
//         ));
        
//         // Execute the request
//         $response = curl_exec($curl);

//         // Check for cURL errors
//         if (curl_errno($curl)) {
//             // Log cURL error details
//             info('cURL error audio: ' . curl_error($curl));
//         } else {
//             // Optionally, log response code for debugging
//             $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//           // info('HTTP response code For Audio Brd recieve-one: ' . $http_code);
//         }
        
//         // Close the cURL session
//         curl_close($curl);

//     } catch (\Throwable $th) {
//         // Log any other errors
//         info('Exception: ' . $th->getMessage());
//     }
// } 
function send_ws_notification($host_data, $channelName, $brd_type)
{
    $followersQuery = Follower::where('follower_id', $host_data->id);
    if (!$followersQuery->exists()) {
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

        app(\App\Services\LivePushNotificationService::class)
        ->sendHostWentLive($host_data, (string) $channelName, (string) $brd_type, $random_sentence);

$payload = json_encode([[
        'event_type'   => 'room.share.invite',
        'channelName'  => $channelName,
        'brd_type'     => $brd_type,
        'host_id'      => $host_data->id,
        'host_name'    => $host_data->name,
        'host_profile' => $host_data->profile,
        'message'      => $random_sentence,
    ]], JSON_UNESCAPED_UNICODE);

    $followersQuery
        ->select('id', 'user_id')
        ->orderBy('id')
        ->chunk(100, function ($rows) use ($pusher, $payload) {
            try {
                $events = [];
                foreach ($rows as $row) {
                    $events[] = [
                        'channel' => 'notification-' . $row->user_id,
                        'name'    => 'room.share.invite',
                        'data'    => $payload,
                    ];
                }

                if (!empty($events)) {
                    $pusher->triggerBatch($events);
                }
            } catch (\Throwable $e) {
                // silently skip failed triggers
            }
        });
}



    public function RoomInfo(Request $request)
    {
        return app(\App\Http\Controllers\Api\V4\AudioBrdController::class)->RoomInfo($request);
    }

    public function AudioCallAcceptViaAudience(Request $request)
    {
        return app(\App\Http\Controllers\Api\V4\AudioBrdController::class)->AudioCallAcceptViaAudience($request);
    }
}
