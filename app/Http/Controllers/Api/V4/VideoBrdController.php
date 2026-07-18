<?php

namespace App\Http\Controllers\Api\V4;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\LiveCall;
use App\Models\User;
use App\Models\Gift;
use App\Models\Kick;
use App\Models\Comment;
use App\Models\UserLive;
use App\Models\DayTime;
use App\Models\Follower;
use Carbon;
use DB;
use RedisCacheFunction;
use App\Traits\CacheClearTrait;
use Illuminate\Support\Facades\Redis;
class VideoBrdController extends Controller
{
    use CacheClearTrait;
    private $prefix = 'queenlive:';
    private const BUSINESS_TIMEZONE = 'Asia/Dhaka';
    private const VIDEO_DAYTIME_MIN_COUNT_SECONDS = 1800;
    private const VIDEO_DAYTIME_REWARD_TARGET_SECONDS = 3600;
    private const VIDEO_DAYTIME_REWARD_POINTS = 4000;
    private const VIDEO_DAYTIME_REWARD_BLOCK_START = '06:00:00';
    private const VIDEO_DAYTIME_REWARD_BLOCK_END = '11:59:19';

    private function businessNow()
    {
        return Carbon\Carbon::now(self::BUSINESS_TIMEZONE);
    }

    private function businessTodayDate()
    {
        return $this->businessNow()->toDateString();
    }

    private function businessToday()
    {
        return $this->businessNow()->startOfDay();
    }

    private function isVideoRewardBlockedNow(Carbon\Carbon $currentTime)
    {
        list($startHour, $startMinute, $startSecond) = array_map('intval', explode(':', self::VIDEO_DAYTIME_REWARD_BLOCK_START));
        list($endHour, $endMinute, $endSecond) = array_map('intval', explode(':', self::VIDEO_DAYTIME_REWARD_BLOCK_END));

        $start = $currentTime->copy()->setTime($startHour, $startMinute, $startSecond);
        $end = $currentTime->copy()->setTime($endHour, $endMinute, $endSecond);

        return $currentTime->between($start, $end);
    }

    private function videoDayTimeToSeconds($dayTimes)
    {
        if (!is_string($dayTimes) || !preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $dayTimes)) {
            return 0;
        }

        list($hours, $minutes, $seconds) = array_map('intval', explode(':', $dayTimes));

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function upsertVideoDayTime($userId, $channelName, $brdType, $dayTimes)
    {
        $dayTime = DayTime::where('user_id', $userId)
            ->where('channelName', $channelName)
            ->first();

        if ($dayTime) {
            $dayTime->day_times = $dayTimes;
            $dayTime->brd_type = $brdType;
            $dayTime->live_time = $this->businessTodayDate();
            $dayTime->save();
            return $dayTime;
        }

        $dayTime = new DayTime;
        $dayTime->user_id = $userId;
        $dayTime->channelName = $channelName;
        $dayTime->day_times = $dayTimes;
        $dayTime->brd_type = $brdType;
        $dayTime->live_time = $this->businessTodayDate();
        $dayTime->save();

        return $dayTime;
    }

    private function issueVideoDayTimeReward($hostId, $channelName, $value, $rewardType, $message)
    {
        $sander_user = RedisCacheFunction::UserfindById(1);
        $reciever = RedisCacheFunction::UserfindById($hostId);

        if (!$sander_user || !$reciever) {
            return 0;
        }

        $gift = new Gift;
        $gift->sander_id = $sander_user->id;
        $gift->reciever_id = $hostId;
        $gift->name = 'gift_rose.svga';
        $gift->value = $value;
        $gift->reward_type = $rewardType;
        $gift->channelName = $channelName;
        $gift->date = $this->businessNow();

        if (!$gift->save()) {
            return 0;
        }

        $sander_user->balance -= $value;
        $sander_user->save();

        $comment = new Comment;
        $comment->user_id = 1;
        $comment->channelName = $channelName;
        $comment->message = $message;
        $comment->reciever_id = $hostId;
        $comment->type = 'message';
        $comment->save();

        $gift_comment = $this->buildRealtimeCommentPayload(
            $sander_user,
            (string) $channelName,
            $message
        );
        $this->emitRealtimeComment($gift_comment);

        $global_txt = array(array(
            'message' => $message,
            'image' => $sander_user->profile,
            'name' => $sander_user->name,
        ));

        $global_websoket = array(array(
            'message'       => 'bp_golbal_gift_banner',
            'channelName'   => $channelName,
            'data'          => $global_txt,
            'code'          => '200',
            'channel_type'  => '17'
        ));

        self::Websoket($global_websoket);

        return $value;
    }

    private function businessMonthRange()
    {
        $now = $this->businessNow();

        return array(
            $now->copy()->startOfMonth()->toDateString(),
            $now->copy()->endOfMonth()->toDateString(),
        );
    }

    /// Toggle a video room's locked state. Mirrors Audio/Multi LockUnlock —
    /// operates on UserLive.locked by channelName + host_id so a video host can
    /// lock/unlock their own room (previously video could only be locked by
    /// remote moderation, never by the host).
    public function LockUnlock(Request $request)
    {
        $token = $request->access_token;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        $response = array();

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $live = UserLive::where('channelName', '=', $channelName)
                ->where('user_id', $host_id)
                ->first();
            if ($live) {
                if ($live->locked == 1) {
                    $live->locked = 0;
                    array_push($response, array('message' => 'Video Brd Unlock Successfully', 'locked' => 0, 'code' => '200'));
                } else {
                    $live->locked = 1;
                    array_push($response, array('message' => 'Video Brd lock Successfully', 'locked' => 1, 'code' => '200'));
                }
                $live->save();

                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                array_push($response, array('message' => 'Live Removed Already', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    public function CallRequest(Request $request)
    {
        $response = [];
        $websoket_call_request = [];

        // Validate access token
        if ($request->access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized access_token', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        // Clear ANY same-room row (pending OR a stale Accept left over from a
        // previous cohost session) so the next tap recreates and rebroadcasts
        // the host request. Without this, an audience who was a cohost
        // earlier (status='Accept' never cleaned up) gets a permanent 401
        // "Call Already Sand" and the host never receives a realtime
        // notification — i.e. call_request silently fails in real time.
        // The audience-side cohost button is already hidden for users who
        // are currently active cohosts, so clearing an Accept row here only
        // affects users who left the seat without their row being cleaned.
        LiveCall::where('co_host_id', $request->co_host_id)
            ->where('channelName', $request->channelName)
            ->where('host_id', $request->host_id)
            ->delete();

        // Check if live exists
        $live = RedisCacheFunction::getUserLive($request->host_id, $request->channelName);

        if (!$live) {
            $response[] = ['message' => 'Live Off Already', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        // Clean up old calls
        LiveCall::where('co_host_id', $request->co_host_id)->delete();

        // Create new call
        LiveCall::create([
            'co_host_id'        => $request->co_host_id,
            'channelName'       => $request->channelName,
            'type'              => $live->type,
            'host_id'           => $request->host_id,
            'set_no'            => "0",
            'status'            => 'pending',
            'is_co_host_active' => 'pending',
            'super_mute'        => '0',
        ]);

        // Prepare response data
        $call_count = LiveCall::where('status', 'pending')
            ->where('channelName', $request->channelName)
            ->count();

        $call_list = LiveCall::join('users', 'users.id', 'live_calls.co_host_id')
            ->select('users.name', 'users.profile', 'live_calls.channelName',
                'live_calls.co_host_id', 'live_calls.status', 'live_calls.set_no')
            ->where('live_calls.host_id', $request->host_id)
            ->where('live_calls.channelName', $request->channelName)
            ->where('live_calls.status', 'pending')
            ->get();

        $websoket_call_request[] = [
            'message'       => 'Video Call Request',
            'channelName'   => $request->channelName,
            'call_count'    => $call_count,
            'data'          => $call_list,
            'code'          => '200',
            'channel_type'  => '13'
        ];

        self::Websoket($websoket_call_request);

        $response[] = [
            'message' => 'Call Request Sand Successfully',
            'data'    => [],
            'code'    => '200'
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function Kick(Request $request)
    {
        $response = [];
        $websoket_kick = [];
        $joinresponse = [];
        $websocket_call = [];

        // Validate access token
        if ($request->access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized access_token', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        // Extract request parameters
        $host_id = $request->host_id;
        $channelName = $request->channelName;
        $user_id = $request->user_id;
        $kick_by = $request->kick_by;

        // Check kick permissions
        $check_offical_user = RedisCacheFunction::UserfindById($kick_by);
        $check_admin = RedisCacheFunction::isBrdAdmin($host_id, $kick_by, 1);

        $has_kick_permission = ($kick_by == $host_id ||
            ($check_offical_user && $check_offical_user->kick_power == 1) ||
            $check_admin);

        if (!$has_kick_permission) {
            $response[] = ['message' => 'Kick permission denied', 'code' => '403'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $kick_user_id = $user_id;
        $target_user = RedisCacheFunction::UserfindById($kick_user_id);
        $actor_is_elevated = $check_admin || ($check_offical_user && (
            $check_offical_user->kick_power == 1 ||
            $check_offical_user->is_official_id != 0 ||
            $check_offical_user->is_admin == 1 ||
            $check_offical_user->is_bd_admin == 1
        ));
        if ($target_user && (
            $target_user->is_official_id != 0 ||
            $target_user->is_admin == 1 ||
            $target_user->is_bd_admin == 1 ||
            ((int) ($target_user->is_vip ?? 0) >= 7 && !$actor_is_elevated)
        )) {
            $response[] = ['message' => 'Protected user cannot be kicked', 'code' => '403'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        // Remove old call and create kick record
        LiveCall::where('host_id', $host_id)
            ->where('channelName', $channelName)
            ->where('co_host_id', $kick_user_id)
            ->delete();

        $kick = new Kick;
        $kick->user_id = $kick_user_id;
        $kick->channelName = $channelName;
        $kick->host_id = $host_id;
        $kick->kick_by = $kick_by;
        $kick->save();

        // ЁЯФ┤ Manual clear needed
        $this->clearJustVideoCall($host_id, $channelName);

        // Prepare response
        $user_by_kick = RedisCacheFunction::UserfindById($kick_by);
        $response[] = [
            'message'       => 'Kick Successfully',
            'channelName'   => $channelName,
            'user_id'       => $kick_user_id,
            'user_by_kick'  => $user_by_kick ? $user_by_kick->name : '',
            'code'          => '200'
        ];

        // Prepare websocket messages
        $websoket_kick[] = [
            'message'       => 'bd_kick',
            'data'          => $response,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '20'
        ];
        self::Websoket($websoket_kick);

        // Prepare additional call details
        $live = RedisCacheFunction::getUserLive($host_id, $channelName);
        $top_profile = RedisCacheFunction::TopProfile($host_id);
        $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

        $joinresponse[] = [
            'message'               => 'Video Call Mute',
            'host_list'             => $call_details['host_list'],
            'co_host_list'          => $call_details['co_host_list'],
            'host_balance'          => $call_details['host_balance'],
            'star'                  => $call_details['star'],
            'star_complete_parcent' => $call_details['star_complete_parcent'],
            'top_profile'           => $top_profile,
            'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
            'channelName'           => $channelName,
            'code'                  => '200'
        ];

        $websocket_call[] = [
            'message'       => 'bd_video_call',
            'data'          => $joinresponse,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ];
        self::Websoket($websocket_call);

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function PendingCallRemoved(Request $request)
    {
        // Early return for unauthorized access
        if ($request->access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode(
                [['message' => 'Unauthorized access_token', 'code' => '401']],
                JSON_UNESCAPED_UNICODE
            );
        }

        // Delete pending call if exists
        LiveCall::where('host_id', $request->host_id)
            ->where('channelName', $request->channelName)
            ->where('co_host_id', $request->co_host_id)
            ->where('status', 'pending')
            ->delete();

      $call_list = LiveCall::with('user:id,name,profile')
            ->where('host_id', $request->host_id)
            ->where('channelName', $request->channelName)
            ->where('status', 'pending')
            ->get(['channelName', 'co_host_id', 'status', 'set_no']);
        
        // Prepare websocket message
        $websoket_call_request = [[
            'message'       => 'Video Call Request',
            'channelName'   => $request->channelName,
            'call_count'    => $call_list->count(),
            'data'          => $call_list->map(function ($item) {
                return [
                    'name'          => optional($item->user)->name ?? 'Unknown User',
                    'profile'       => optional($item->user)->profile ?? null,
                    'channelName'   => $item->channelName,
                    'co_host_id'    => $item->co_host_id,
                    'status'        => $item->status,
                    'set_no'        => $item->set_no
                ];
            }),
            'code'          => '200',
            'channel_type'  => '13'
        ]];

        self::Websoket($websoket_call_request);

        // Return response
        return json_encode(
            [['message' => 'Call Request Removed Successfully', 'data' => [], 'code' => '200']],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function VideoCallAccept(Request $request)
    {
        $response = [];

        // Early return for unauthorized access
        if ($request->access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized access_token', 'code' => '401'];
            return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Check accept count limit
        $acceptCount = LiveCall::where('host_id', $request->host_id)
            ->where('channelName', $request->channelName)
            ->where('status', 'Accept')
            ->count();

        if ($acceptCount >= 3) {
            $response[] = ['message' => 'Already Three Co-Host Accepted', 'code' => '401'];
            return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Update call status. Do not report success when the pending request
        // already expired/was removed; otherwise the host sees "accepted" while
        // the audience keeps polling an empty accepted list.
        $updated = LiveCall::where('host_id', $request->host_id)
            ->where('channelName', $request->channelName)
            ->where('co_host_id', $request->co_host_id)
            ->where('status', 'pending')
            ->update(['status' => 'Accept', 'is_co_host_active' => 'Accept']);

        if ($updated < 1) {
            $alreadyAccepted = LiveCall::where('host_id', $request->host_id)
                ->where('channelName', $request->channelName)
                ->where('co_host_id', $request->co_host_id)
                ->where('status', 'Accept')
                ->exists();

            if (!$alreadyAccepted) {
                $response[] = [
                    'message' => 'Call request no longer pending',
                    'code' => '409'
                ];
                return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
            }
        }

        // ЁЯФ┤ Manual clear needed
        $this->clearVideoCallAndLists($request->host_id, $request->channelName);

        // Prepare response data
        $live = RedisCacheFunction::getUserLive($request->host_id, $request->channelName);
        $call_details = $this->prepareCallDetails($request->host_id, $request->channelName, $live);

        $response[] = [
            'message'               => 'Video Call Accept List Data Show Successfull come from call Accept',
            'host_list'             => $call_details['host_list'],
            'co_host_list'          => $call_details['co_host_list'],
            'host_balance'          => $call_details['host_balance'],
            'star'                  => $call_details['star'],
            'star_complete_parcent' => $call_details['star_complete_parcent'],
            'top_profile'           => RedisCacheFunction::TopProfile($request->host_id),
            'total_reward'          => RedisCacheFunction::getTotalReward($request->host_id),
            'channelName'           => $request->channelName,
            'code'                  => '200'
        ];

        // Send websocket messages
        $this->sendWebsocketMessages($request->host_id, $request->channelName, $response);

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected function sendWebsocketMessages($host_id, $channelName, $response)
    {
        // First websocket message
        self::Websoket([[
            'message'       => 'bd_video_call',
            'data'          => $response,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ]]);

        // Second websocket message
        $call_list = LiveCall::with(['coHost' => function ($query) {
            $query->select('id', 'name', 'profile');
        }])
            ->where('host_id', $host_id)
            ->where('channelName', $channelName)
            ->where('status', 'pending')
            ->select(
                'channelName',
                'co_host_id',
                'status',
                'set_no'
            )
            ->get();

        self::Websoket([[
            'message'       => 'Video Call Request',
            'channelName'   => $channelName,
            'call_count'    => $call_list->count(),
            'data'          => $call_list,
            'code'          => '200',
            'channel_type'  => '13'
        ]]);
    }

    public function CallMute(Request $request)
    {
        $response = [];

        // Early return for unauthorized access
        if ($request->access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized access_token', 'code' => '401'];
            return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Officials and app-admins are protected from a host force-mute
        // ("speaker off"). Only blocks the force-mute (super_mute=1); unmute and
        // regular cohost mute are unaffected.
        if ($request->super_mute == 1) {
            $muteTarget = \App\Models\User::find($request->co_host_id);
            if ($muteTarget && ($muteTarget->is_official_id != 0 || $muteTarget->is_admin == 1 || $muteTarget->is_bd_admin == 1)) {
                $response[] = ['message' => 'Official / admin cannot be speaker-muted', 'code' => '403'];
                return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
            }
        }

        // Update mute status
        LiveCall::where('channelName', $request->channelName)
            ->where('co_host_id', $request->co_host_id)
            ->where('host_id', $request->host_id)
            ->where('status', 'Accept')
            ->update([
                'mute'       => $request->mute_satus,
                'super_mute' => $request->super_mute == 1 ? 1 : 0
            ]);

        // ЁЯФ┤ Manual clear needed
        $this->clearJustVideoCall($request->host_id, $request->channelName);

        // Prepare response data
        $live = RedisCacheFunction::getUserLive($request->host_id, $request->channelName);
        $call_details = $this->prepareCallDetails($request->host_id, $request->channelName, $live);

        $response[] = [
            'message'               => 'Video Call Mute',
            'host_list'             => $call_details['host_list'],
            'co_host_list'          => $call_details['co_host_list'],
            'host_balance'          => $call_details['host_balance'],
            'star'                  => $call_details['star'],
            'star_complete_parcent' => $call_details['star_complete_parcent'],
            'top_profile'           => RedisCacheFunction::TopProfile($request->host_id),
            'total_reward'          => RedisCacheFunction::getTotalReward($request->host_id),
            'channelName'           => $request->channelName,
            'code'                  => '200'
        ];

        // Send websocket message
        self::Websoket([[
            'message'       => 'bd_video_call',
            'data'          => $response,
            'channelName'   => $request->channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ]]);

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function Store(Request $request)
    {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $channelName = $request->channelName;
        $token = $request->token;
        $type = $request->type;
        $notice = $request->notice;
        $bullet_notice = $request->bullet_notice;
        $appId = $request->app_id;
        $date = $this->businessNow();
        $sdk = $request->sdk;
        $response = array();
        $websocket_call = array();

        if ($access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $oldCall = LiveCall::where('co_host_id', $user_id)->first();
        if ($oldCall) {
            $oldCall->delete();
        }

        $user = RedisCacheFunction::UserfindById($user_id);
        if (!$user || $user->ban_type !== null) {
            return response()->json([['message' => 'Sorry Your ID Banned', 'code' => '401']], 401);
        }

        $todayGiftSum = RedisCacheFunction::getUserTodayGiftSum($user_id);
        $top_value = $user->top_value + $todayGiftSum;
        $avatar = RedisCacheFunction::getUserAvatar($user_id) ?? $user->profile;

        $liveData = [
            'user_id'        => $user_id,
            'channelName'    => $channelName,
            'name'           => $user->name,
            'top_value'      => $top_value,
            'type'           => $type,
            'token'          => $token,
            'sdk'            => $sdk,
            'mute'           => 0,
            'date'           => $date,
            'notice'         => $notice,
            'bullet_notice'  => $bullet_notice,
            'avatar'         => $avatar,
            'appId'          => $appId,
        ];

        // тЬЕ Pin condition
        if (in_array((int)$user_id, [1111, 22401])) {
            $liveData['pin'] = 5450;
        }

        UserLive::storeOneActiveForUser($liveData);

        // ЁЯФ┤ Manual clear needed
        $this->clearVideoCallAndHome($user_id, $channelName);

        $call_details = $this->prepareCallDetails($user_id, $channelName, $liveData);

        array_push($response, array(
            'message'               => 'Video Brd Store ',
            'host_list'             => $call_details['host_list'],
            'co_host_list'          => $call_details['co_host_list'],
            'host_balance'          => $call_details['host_balance'],
            'star'                  => $call_details['star'],
            'star_complete_parcent' => $call_details['star_complete_parcent'],
            'top_profile'           => RedisCacheFunction::TopProfile($user_id),
            'total_reward'          => RedisCacheFunction::getTotalReward($user_id),
            'channelName'           => $channelName,
            'code'                  => '200'
        ));

        array_push($websocket_call, array(
            'message'       => 'bd_video_call',
            'data'          => $response,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ));

        self::Websoket($websocket_call);
        self::send_ws_notification($user, $channelName, $type);

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function CallRemoved(Request $request)
    {
        $access_token = $request->access_token;
        $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
        $channelName = $request->channelName;
        $response = array();
        $websocket_call = array();

        if ($access_token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            LiveCall::where([
                'host_id'    => $host_id,
                'channelName' => $channelName,
                'co_host_id' => $co_host_id,
                'status'     => 'Accept'
            ])->delete();

            // ЁЯФ┤ Manual clear needed
            $this->clearVideoCallAndLists($host_id, $channelName);

            $live = RedisCacheFunction::getUserLive($host_id, $channelName);
            $top_profile = RedisCacheFunction::TopProfile($host_id);
            $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

            array_push($response, array(
                'message'               => 'Video Call Removed ',
                'host_list'             => $call_details['host_list'],
                'co_host_list'          => $call_details['co_host_list'],
                'host_balance'          => $call_details['host_balance'],
                'star'                  => $call_details['star'],
                'star_complete_parcent' => $call_details['star_complete_parcent'],
                'top_profile'           => $top_profile,
                'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
                'channelName'           => $channelName,
                'code'                  => '200'
            ));

            array_push($websocket_call, array(
                'message'       => 'bd_video_call',
                'data'          => $response,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '19'
            ));

            self::Websoket($websocket_call);
            self::CalllistReload($channelName, $host_id);

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    private function CalllistReload($channelName, $host_id)
    {
        $response = array();
        $websocket_call = array();

        sleep(2);
        $live = RedisCacheFunction::getUserLive($host_id, $channelName);
        $top_profile = RedisCacheFunction::TopProfile($host_id);
        $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

        array_push($response, array(
            'message'               => 'Video Call Removed ',
            'host_list'             => $call_details['host_list'],
            'co_host_list'          => $call_details['co_host_list'],
            'host_balance'          => $call_details['host_balance'],
            'star'                  => $call_details['star'],
            'star_complete_parcent' => $call_details['star_complete_parcent'],
            'top_profile'           => $top_profile,
            'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
            'channelName'           => $channelName,
            'code'                  => '200'
        ));

        array_push($websocket_call, array(
            'message'       => 'bd_video_call',
            'data'          => $response,
            'channelName'   => $channelName,
            'code'          => '200',
            'channel_type'  => '19'
        ));

        self::Websoket($websocket_call);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function HostCallRemove(Request $request)
    {
        $access_token = $request->access_token;
        $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
        $channelName = $request->channelName;
        $response = array();
        $websocket_call = array();

        if ($access_token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            // Delete the cut co-host's accepted seat immediately (was delayed by
            // two sleep(3) blocks = ~6s, leaving the seat occupied on both sides).
            LiveCall::where('host_id', $host_id)
                ->where('channelName', $channelName)
                ->where('co_host_id', $co_host_id)
                ->where('status', 'Accept')
                ->delete();

            array_push($response, array(
                'message'     => 'Video Call Removed By Host ',
                'co_host_id'  => $co_host_id,
                'host_id'     => $host_id,
                'channelName' => $channelName,
                'code'        => '200'
            ));

            // Legacy cut event (channel_type 22).
            array_push($websocket_call, array(
                'message'       => 'video_host_call_remove',
                'data'          => $response,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '22'
            ));
            self::Websoket($websocket_call);

            // Fresh co-host-list snapshot (bd_video_call / channel_type 19) AFTER
            // deletion so EVERY client (host + audience) rebuilds seats without
            // the cut co-host and the seat shows empty — mirrors the video Kick
            // handler and the audio cut snapshot. Without this, the cut event 22
            // is dropped by the app's named-event filter and the seat lingered.
            $live = RedisCacheFunction::getUserLive($host_id, $channelName);
            $top_profile = RedisCacheFunction::TopProfile($host_id);
            $call_details = $this->prepareCallDetails($host_id, $channelName, $live);
            $snapshot_response = array(array(
                'message'               => 'Video Call Remove',
                'host_list'             => $call_details['host_list'],
                'co_host_list'          => $call_details['co_host_list'],
                'host_balance'          => $call_details['host_balance'],
                'star'                  => $call_details['star'],
                'star_complete_parcent' => $call_details['star_complete_parcent'],
                'top_profile'           => $top_profile,
                'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
                'channelName'           => $channelName,
                'code'                  => '200'
            ));
            self::Websoket(array(array(
                'message'       => 'bd_video_call',
                'data'          => $snapshot_response,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '19'
            )));

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    public function VideoGiftPush(Request $request)
    {
        $startTime = microtime(true);
        $response = [];

        $token = $request->access_token;
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized', 'code' => '401'];
            return response()->json($response, 401);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['items']) || !is_array($data['items'])) {
            $response[] = ['message' => 'Must Send at Least One Gift', 'code' => '401'];
            return response()->json($response, 401);
        }

        $user_id = $request->user_id;
        $value = $request->value;
        $gift_name = $request->giftName;
        $channelName = $request->channelName;
        $host_id = $request->host_id;
        $gift_type = $request->gift_type;

        if (in_array($user_id, [79861, 43836])) {
            $response[] = ['message' => 'Must Send at Least One Gift', 'code' => 401];
            return response()->json($response, 401);
        }

        $sender = RedisCacheFunction::UserfindById($user_id);
        if (!$sender) {
            $response[] = ['message' => 'Sender not found', 'code' => '404'];
            return response()->json($response, 404);
        }
         $this->clearAllVideoCachesWithGift($host_id, $channelName,$user_id);
        $receiverIds = array_column($data['items'], 'receiverId');
        $receivers = User::whereIn('id', $receiverIds)->get()->keyBy('id');
        $userLives = UserLive::whereIn('user_id', $receiverIds)->get()->keyBy('user_id');

        $giftEntries = [];
        $globalTxt = [];
        $giftGlobalWebsocket = [];
        $giftEffect = [];
        $forCommentNames = [];
        $successfulReceivers = [];

        foreach ($data['items'] as $index => $row) {
            $receiverId = $row['receiverId'];

            if ($sender->balance < $value) {
                continue;
            }

            $receiver = $receivers[$receiverId] ?? null;
            if (!$receiver) {
                continue;
            }

            $sender->balance -= $value;
            // ЁЯФ┤ Manual clear needed
       
            $giftEntries[] = [
                'sander_id'   => $user_id,
                'reciever_id' => $receiverId,
                'name'        => $gift_name,
                'value'       => $value,
                'channelName' => $channelName,
                'date'        => now(),
            ];

            if (isset($userLives[$receiverId])) {
                $todaySum = RedisCacheFunction::getUserTodayGiftSum($receiverId);
                $userLives[$receiverId]->top_value = $receiver->top_value + $todaySum;
                $userLives[$receiverId]->save();
            }

            if ($value > 39999 || $user_id == 33401) {
                $message = "{$sender->name} sent {$value} to {$receiver->name}";
                $globalTxt[] = [
                    'message'          => $message,
                    'image'            => $sender->profile,
                    'receiver_profile' => $receiver->profile,
                    'name'             => $sender->name
                ];
                $giftGlobalWebsocket[] = [
                    'message'      => 'bd_global_gift',
                    'channelName'  => $channelName,
                    'data'         => $globalTxt,
                    'code'         => '200',
                    'channel_type' => '88'
                ];
            }

            $forCommentNames[] = $receiver->name;
            $successfulReceivers[] = $receiverId;

            if (count($successfulReceivers) === 1) {
                $balance = Gift::where('reciever_id', $receiverId)
                    ->where('channelName', $channelName)
                    ->sum('value');

                $giftEffect[] = [
                    'message'      => 'video gift',
                    'channelName'  => $channelName,
                    'data'         => [
                        'channelName'      => $channelName,
                        'name'             => $gift_name,
                        'gift_time'        => '5',
                        'host_balance'     => (string)$balance,
                        'music'            => '',
                        'audience_balance' => (string)$sender->balance,
                        'reciever_id'      => (string)$receiverId,
                        'status'           => 'active',
                        'gift_type'        => (string)$gift_type,
                    ],
                    'code'         => '200',
                    'channel_type' => '24'
                ];
            }
        }

        if (empty($giftEntries)) {
            $response[] = ['message' => 'No gifts sent - insufficient balance or invalid receivers', 'code' => '403'];
            return response()->json($response, 403);
        }

        $sender->save();
        Gift::insert($giftEntries);
            $this->clearAllVideoCachesWithGift($host_id, $channelName,$user_id);
       

        $total = RedisCacheFunction::getSanderTotalGift($user_id);

        if ($total > 0) {
            $levelBoundaries = [
                2  => [40000, 50000], 3  => [50001, 100000], 4  => [100001, 150000],
                5  => [150001, 200000], 6  => [200001, 400000], 7  => [400001, 600000],
                8  => [600001, 800000], 9  => [800001, 1000000], 10 => [1000001, 1200000],
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

            if ($sender->level < $level) {
                $sender->level = $level;
                $sender->save();
            }
        }

        if ($giftGlobalWebsocket) {
            self::Websoket($giftGlobalWebsocket);
        }
        if ($giftEffect) {
            self::Websoket($giftEffect);
        }

        $live = RedisCacheFunction::getUserLive($host_id, $channelName);
        $topProfile = RedisCacheFunction::TopProfile($host_id);
        $callDetails = $this->prepareCallDetails($host_id, $channelName, $live);

        self::Websoket([[
            'message'      => 'bd_video_call',
            'data'         => [[
                'message'                => 'Video Call Gift',
                'host_list'              => $callDetails['host_list'],
                'co_host_list'           => $callDetails['co_host_list'],
                'host_balance'           => $callDetails['host_balance'],
                'star'                   => $callDetails['star'],
                'star_complete_parcent'  => $callDetails['star_complete_parcent'],
                'top_profile'            => $topProfile,
                'total_reward'           => RedisCacheFunction::getTotalReward($host_id),
                'channelName'            => $channelName,
                'code'                   => '200'
            ]],
            'channelName'  => $channelName,
            'code'         => '200',
            'channel_type' => '19'
        ]]);

        $allReceivers = implode(', ', $forCommentNames);
        $commentMsg = "{$sender->name} sent {$gift_name} ({$value}) to {$allReceivers}";

        Comment::create([
            'user_id'     => $user_id,
            'channelName' => $channelName,
            'message'     => $commentMsg,
            'reciever_id' => $host_id,
            'type'        => 'message',
            'date'        => now()
        ]);

        $gift_comment = $this->buildRealtimeCommentPayload(
            $sender,
            (string) $channelName,
            $commentMsg
        );
        $this->emitRealtimeComment($gift_comment);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $response[] = [
            'message'           => 'Gifts Sent Successfully',
            'user_id'           => $sender->id,
            'balance'           => $sender->balance,
            'sent_to'           => $successfulReceivers,
            'execution_time_ms' => $executionTime,
            'code'              => '200'
        ];

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function HostMue(Request $request)
    {
        $response = array();
        $websocket_call = array();
        $token = $request->access_token;
        $host_id = $request->host_id;
        $mute_satus = $request->mute_satus;
        $channelName = $request->channelName;

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $live = RedisCacheFunction::getUserLive($host_id, $channelName);
            if ($live) {
                $live->mute = $mute_satus;
                $live->save();
            }

            // ЁЯФ┤ Manual clear needed
            $this->clearJustVideoCall($host_id, $channelName);

            $top_profile = RedisCacheFunction::TopProfile($host_id);
            $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

            array_push($response, array(
                'message'               => 'Video Call Mute ',
                'host_list'             => $call_details['host_list'],
                'co_host_list'          => $call_details['co_host_list'],
                'host_balance'          => $call_details['host_balance'],
                'star'                  => $call_details['star'],
                'star_complete_parcent' => $call_details['star_complete_parcent'],
                'top_profile'           => $top_profile,
                'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
                'channelName'           => $channelName,
                'code'                  => '200'
            ));

            array_push($websocket_call, array(
                'message'       => 'bd_video_call',
                'data'          => $response,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '19'
            ));

            self::Websoket($websocket_call);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    public function AgoraSetting(Request $request)
    {
        $response = array();
        $token = $request->access_token;

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $setting = RedisCacheFunction::getSetting();
            array_push($response, array(
                'message'        => 'Agora Setting Data',
                'appId'          => $setting->appId,
                'code'           => '200'
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
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
                "ржЖржорж┐ рждрзЛржорж╛рж░ ржЬржирзНржп ржЕржкрзЗржХрзНрж╖рж╛ ржХрж░ржЫрж┐, ржпрзЛржЧ ржжрж╛ржУ ржПржмржВ ржЪрж▓ ржПржХрж╕ржЩрзНржЧрзЗ ржЖрж░ржУ ржмржирзНржзрзБ рждрзИрж░рж┐ ржХрж░рж┐ред",
                "Come join me, letтАЩs connect and build new friendships.",
                "ржЖржорж╛рж░ рж╕рж╛ржерзЗ ржпрзЛржЧ ржжрж╛ржУ, ржЪрж▓ ржирждрзБржи ржмржирзНржзрзБрждрзНржм рждрзИрж░рж┐ ржХрж░рж┐ред",
                "DonтАЩt miss out, IтАЩm here waiting for you to join and meet more friends.",
                "ржорж┐рж╕ ржХрзЛрж░рзЛ ржирж╛, ржЖржорж┐ ржПржЦрж╛ржирзЗ рждрзЛржорж╛рж░ ржЬржирзНржп ржЕржкрзЗржХрзНрж╖рж╛ ржХрж░ржЫрж┐ ржмржирзНржзрзБржжрзЗрж░ рж╕рж╛ржерзЗ ржжрзЗржЦрж╛ ржХрж░рж╛рж░ ржЬржирзНржпред",
                "Join me now, and letтАЩs make wonderful memories with friends.",
                "ржПржЦржиржЗ ржЖржорж╛рж░ рж╕рж╛ржерзЗ ржпрзЛржЧ ржжрж╛ржУ, ржПржмржВ ржЪрж▓ ржмржирзНржзрзБржжрзЗрж░ рж╕рж╛ржерзЗ ржЪржорзОржХрж╛рж░ рж╕рзНржорзГрждрж┐ рждрзИрж░рж┐ ржХрж░рж┐ред",
                "IтАЩm waiting for you! LetтАЩs make our circle bigger with new friends.",
                "ржЖржорж┐ рждрзЛржорж╛рж░ ржЬржирзНржп ржЕржкрзЗржХрзНрж╖рж╛ ржХрж░ржЫрж┐! ржЪрж▓ ржЖржорж╛ржжрзЗрж░ ржмржирзНржзрзБржжрзЗрж░ рж╕ржВржЦрзНржпрж╛ ржмрж╛ржбрж╝рж╛ржЗред",
                "LetтАЩs join hands and create a beautiful friendship circle.",
                "ржЪрж▓ рж╣рж╛рждрзЗ рж╣рж╛ржд рж░рзЗржЦрзЗ ржПржХржЯрж┐ рж╕рзБржирзНржжрж░ ржмржирзНржзрзБрждрзНржмрзЗрж░ ржмрзГрждрзНржд рждрзИрж░рж┐ ржХрж░рж┐ред",
                "Your presence will make it better, join and meet new people.",
                "рждрзЛржорж╛рж░ ржЙржкрж╕рзНржерж┐рждрж┐ ржПржЯрж┐ржХрзЗ ржЖрж░ржУ рж╕рзБржирзНржжрж░ ржХрж░ржмрзЗ, ржпрзЛржЧ ржжрж╛ржУ ржПржмржВ ржирждрзБржи ржорж╛ржирзБрж╖ржжрзЗрж░ рж╕рж╛ржерзЗ ржкрж░рж┐ржЪрж┐ржд рж╣ржУред",
                "Join me today, and letтАЩs share laughter with friends.",
                "ржЖржЬржЗ ржЖржорж╛рж░ рж╕рж╛ржерзЗ ржпрзЛржЧ ржжрж╛ржУ, ржПржмржВ ржЪрж▓ ржмржирзНржзрзБржжрзЗрж░ рж╕рж╛ржерзЗ рж╣рж╛рж╕рж┐ ржнрж╛ржЧрж╛ржнрж╛ржЧрж┐ ржХрж░рж┐ред"
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
                $events = array_map(fn($uid) => [
                    'channel' => 'notification-' . $uid,
                    'name'    => 'room.share.invite',
                    'data'    => $payload,
                ], $batch);
                $pusher->triggerBatch($events);
            } catch (\Throwable $e) {
                // silently skip failed triggers
            }
        }
    }

    private function buildRealtimeCommentPayload(User $sender, string $channelName, string $commentMessage): array
    {
        return [
            'balance' => (string) $sender->balance,
            'channelName' => (string) $channelName,
            'id' => (string) $sender->id,
            'message' => '@' . $commentMessage,
            'level' => (string) $sender->level,
            'name' => (string) $sender->name,
            'profile' => (string) $sender->profile,
            'is_vip' => (string) $sender->is_vip,
            'frame' => (string) $sender->frame,
            'is_official_id' => (string) $sender->is_official_id,
            'is_agency' => (string) $sender->is_agency,
            'is_host_id' => (string) $sender->is_host_id,
            'comment_badge' => (string) $sender->comment_badge,
            'type' => 'message',
        ];
    }

    private function emitRealtimeComment(array $payload): void
    {
        self::Websoket([array_merge($payload, [
            'code' => '200',
            'channel_type' => '11',
        ])]);
    }

    public function UserData(Request $request)
    {
        $token = $request->access_token;
        $user_id = $request->user_id;
        $host_id = $request->host_id;
        $channelName = $request->channelName;

        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json(['message' => 'Unauthorized', 'code' => 401], 401);
        }

        list($start_date, $end_date) = $this->businessMonthRange();

        if ($host_id == $user_id) {
            $user = RedisCacheFunction::UserfindById($user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found', 'code' => 404], 404);
            }

            $hostData = DB::table('host_data')
                ->join('users', 'users.id', '=', 'host_data.user_id')
                ->join('agencies', 'agencies.code', '=', 'host_data.agency_code')
                ->where('users.is_host_id', 1)
                ->where('users.id', $user_id)
                ->select('host_data.hosting_type', 'agencies.name')
                ->first();

            $host_type = $hostData->hosting_type ?? 0;
            $agency_name = $hostData->name ?? 'Bp';
            $balance = RedisCacheFunction::getGiftBetweenSumDates($user_id, $start_date, $end_date);

            return response()->json([
                'message'               => 'User Data Show Successfully',
                'code'                  => 200,
                'data'                  => $user,
                'follow_status'         => 0,
                'balance'               => $balance,
                'agency_name'           => $agency_name,
                'host_type'             => $host_type,
                'marchent'              => $user->is_agency,
                'is_coin_protal_active' => $user->is_coin_protal_active,
                'frame'                 => $user->frame,
                'entry_effect'          => $user->entry,
            ]);
        }

        $user = RedisCacheFunction::UserfindById($user_id);
        $hostUser = RedisCacheFunction::UserfindById($host_id);

        if (!$user || !$hostUser) {
            return response()->json(['message' => 'User or Host not found', 'code' => 404], 404);
        }

        $isFollowing = $user->following()->where('follower_id', $host_id)->exists();
        $isFollowedBy = $user->followers()->where('user_id', $host_id)->exists();
        $follow_status = ($isFollowing && $isFollowedBy) ? 2 : 1;

        $hostData = DB::table('host_data')
            ->join('users', 'users.id', '=', 'host_data.user_id')
            ->join('agencies', 'agencies.code', '=', 'host_data.agency_code')
            ->where('users.is_host_id', 1)
            ->where('users.id', $host_id)
            ->select('host_data.hosting_type', 'agencies.name')
            ->first();

        $host_type = $hostData->hosting_type ?? 0;
        $agency_name = $hostData->name ?? 'Bp';
        $balance = RedisCacheFunction::getGiftBetweenSumDates($host_id, $start_date, $end_date);

        $live = RedisCacheFunction::getUserLive($host_id, $channelName);
        $top_profile = RedisCacheFunction::TopProfile($host_id);
        $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

        $joinresponse = [
            [
                'message'               => 'Video Call Mute',
                'host_list'             => $call_details['host_list'],
                'co_host_list'          => $call_details['co_host_list'],
                'host_balance'          => $call_details['host_balance'],
                'star'                  => $call_details['star'],
                'star_complete_parcent' => $call_details['star_complete_parcent'],
                'top_profile'           => $top_profile,
                'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
                'channelName'           => $channelName,
                'code'                  => '200'
            ]
        ];

        self::Websoket([
            [
                'message'       => 'bd_video_call',
                'data'          => $joinresponse,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '19'
            ]
        ]);

        return response()->json([
            'message'               => 'User Data Show Successfully',
            'code'                  => 200,
            'data'                  => $user,
            'follow_status'         => $follow_status,
            'balance'               => $balance,
            'agency_name'           => $agency_name,
            'host_type'             => $host_type,
            'marchent'              => $user->is_agency,
            'is_coin_protal_active' => $user->is_coin_protal_active,
            'frame'                 => $user->frame,
            'entry_effect'          => $user->entry,
        ]);
    }

    public function VideoBrdDayTimeRequest(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $id = $request->host_id;
        $channelName = $request->channelName;
        $brd_type = $request->brd_type;
        $day_times = $request->day_times;
        $reward_amount = 0;

        if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $this->upsertVideoDayTime($id, $channelName, $brd_type, $day_times);

            $currentTime = $this->businessNow();
            $currentSessionSeconds = $this->videoDayTimeToSeconds((string) $day_times);
            $shouldCheckReward = (string) $brd_type === '2'
                && !$this->isVideoRewardBlockedNow($currentTime)
                && $currentSessionSeconds >= self::VIDEO_DAYTIME_REWARD_TARGET_SECONDS;

            if ($shouldCheckReward) {
                $existingReward = Gift::where('reciever_id', $id)
                    ->whereDate('date', $this->businessTodayDate())
                    ->where('sander_id', 1)
                    ->where('reward_type', 1)
                    ->first();

                if (!$existingReward) {
                    $reciever = RedisCacheFunction::UserfindById($id);
                    if ($reciever) {
                        $commnet_message = "{$reciever->name} Got " . self::VIDEO_DAYTIME_REWARD_POINTS . " Points Reward From QueenLive For 1 Hour Live Completion.";
                        $reward_amount = $this->issueVideoDayTimeReward(
                            $id,
                            $channelName,
                            self::VIDEO_DAYTIME_REWARD_POINTS,
                            1,
                            $commnet_message
                        );
                    }
                }
            }

            array_push($response, array('message' => 'Data Store', 'reward_amount' => $reward_amount, 'code' => '200'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    public function CohostisActive(Request $request)
    {
        $access_token = $request->access_token;
        $co_host_id = $request->co_host_id;
        $host_id = $request->host_id;
        $channelName = $request->channelName;
        $is_co_host_active = $request->is_co_host_active;
        $response = array();
        $websocket_call = array();

        if ($access_token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $data = LiveCall::where('channelName', $channelName)->where('co_host_id', $co_host_id)->where('host_id', $host_id)->where('status', 'Accept')->first();
            if (!$data) {
                array_push($response, array('message' => 'Accepted co-host not found', 'code' => '409'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }

            $data->is_co_host_active = $is_co_host_active;
            $data->save();

            // ЁЯФ┤ Manual clear needed
            $this->clearVideoCallAndStatus($host_id, $channelName, $co_host_id);

            $live = RedisCacheFunction::getUserLive($host_id, $channelName);
            $top_profile = RedisCacheFunction::TopProfile($host_id);
            $call_details = $this->prepareCallDetails($host_id, $channelName, $live);

            array_push($response, array(
                'message'               => 'Video Call Mute ',
                'host_list'             => $call_details['host_list'],
                'co_host_list'          => $call_details['co_host_list'],
                'host_balance'          => $call_details['host_balance'],
                'star'                  => $call_details['star'],
                'star_complete_parcent' => $call_details['star_complete_parcent'],
                'top_profile'           => $top_profile,
                'total_reward'          => RedisCacheFunction::getTotalReward($host_id),
                'channelName'           => $channelName,
                'code'                  => '200'
            ));

            array_push($websocket_call, array(
                'message'       => 'bd_video_call',
                'data'          => $response,
                'channelName'   => $channelName,
                'code'          => '200',
                'channel_type'  => '19'
            ));

            self::Websoket($websocket_call);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    // private function prepareCallDetails($host_id, $channelName, $live)
    // {
    //     $cacheKey = "Video_Brd_Call_Details_{$host_id}_{$channelName}";
    //     $ttl = 200;

    //     return Cache::remember($cacheKey, $ttl, function () use ($host_id, $channelName, $live) {
    //         $today = date('Y-m-d');
    //         $start_date = date('Y-m') . '-01';
    //         $end_date = date('Y-m') . '-31';

    //         $host_data = RedisCacheFunction::UserfindById($host_id);
    //         if (!$host_data) return [];

    //         $gift_values = Gift::where('channelName', $channelName)
    //             ->whereIn('reciever_id', function ($query) use ($host_id, $channelName) {
    //                 $query->select('id')
    //                     ->from('users')
    //                     ->where('id', $host_id);
    //             })
    //             ->orWhere('reciever_id', $host_id)
    //             ->select('reciever_id', DB::raw('SUM(value) as total_value'))
    //             ->groupBy('reciever_id')
    //             ->pluck('total_value', 'reciever_id');

    //         $host_balance = $gift_values[$host_id] ?? 0;

    //         $host = [
    //             'channelName'       => $channelName,
    //             'profile'           => $host_data->profile,
    //             'is_vip'            => $host_data->is_vip,
    //             'balance'           => $host_balance,
    //             'co_host_name'      => $host_data->name,
    //             'set_no'            => "0",
    //             'mute'              => $live->mute ?? 0,
    //             'frame'             => (string)$host_data->frame,
    //             'co_host_id'        => (string)$host_data->id,
    //             'co_host_status'    => 'Accept',
    //             'super_mute'        => "0"
    //         ];

    //         $list = [$host];
    //         $co_host_list = [];

    //         $accept_list = DB::table('live_calls')
    //             ->where('host_id', $host_id)
    //             ->where('channelName', $channelName)
    //             ->where('status', 'Accept')
    //             ->get();

    //         $co_host_ids = $accept_list->pluck('co_host_id')->unique();
    //         $co_hosts = User::whereIn('id', $co_host_ids)->get()->keyBy('id');

    //         $co_host_gifts = Gift::where('channelName', $channelName)
    //             ->whereIn('reciever_id', $co_host_ids)
    //             ->groupBy('reciever_id')
    //             ->select('reciever_id', DB::raw('SUM(value) as total_value'))
    //             ->pluck('total_value', 'reciever_id');

    //         foreach ($accept_list as $call) {
    //             $co_host = $co_hosts->get($call->co_host_id);
    //             if (!$co_host) continue;

    //             $co_host_balance = $co_host_gifts[$call->co_host_id] ?? 0;

    //             $co_host_data = [
    //                 'channelName'       => $channelName,
    //                 'profile'           => $co_host->profile,
    //                 'is_vip'            => $co_host->is_vip,
    //                 'balance'           => $co_host_balance,
    //                 'co_host_name'      => $co_host->name,
    //                 'set_no'            => "0",
    //                 'mute'              => $call->mute,
    //                 'frame'             => (string)$co_host->frame,
    //                 'co_host_id'        => (string)$call->co_host_id,
    //                 'co_host_status'    => (string)$call->is_co_host_active,
    //                 'super_mute'        => (string)$call->super_mute,
    //             ];

    //             $list[] = $co_host_data;
    //             $co_host_list[] = $co_host_data;
    //         }

    //         $monthly_gift = Gift::where('reciever_id', $host_id)
    //                 ->whereDate('date', '>=', $start_date)
    //                 ->whereDate('date', '<=', $end_date)
    //                 ->sum('value');
    //         $total_gift_sum = ($host_data->previous_coin + $monthly_gift);
    //         $today_gift = Gift::where('reciever_id', $host_id)
    //                 ->whereDate('date', now()->toDateString())
    //                 ->sum('value');

    //         $levels = [
    //             [0, 50000, 1, 50000],
    //             [50000, 200000, 2, 200000],
    //             [200000, 500000, 3, 500000],
    //             [500000, 1000000, 4, 1000000],
    //             [1000000, 2000000, 5, 2000000],
    //             [2000000, PHP_INT_MAX, 5, 20000000]
    //         ];

    //         $star = 0;
    //         $next_level_amount = 1;
    //         foreach ($levels as $level) {
    //             if ($today_gift >= $level[0] && $today_gift < $level[1]) {
    //                 $star = $level[2];
    //                 $next_level_amount = $level[3];
    //                 break;
    //             }
    //         }

    //         $need_percent = intval(($today_gift / $next_level_amount) * 100);

    //         return [
    //             'host_list'              => $list,
    //             'co_host_list'           => $co_host_list,
    //             'host_balance'           => $total_gift_sum,
    //             'star'                   => $star,
    //             'star_complete_parcent'  => $need_percent,
    //         ];
    //     });
    // }
    
    
    private function prepareCallDetails($host_id, $channelName, $live)
    {
         $prefix = 'queenlive:';
        $cacheKey = $prefix . "Video_Brd_Call_Details_{$host_id}_{$channelName}";
        
        // Try Redis cache first
        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return unserialize($cached);
            }
        } catch (\Exception $e) {
            Log::error("Redis get failed for prepareCallDetails", [
                'error' => $e->getMessage(),
                'host_id' => $host_id,
                'channel' => $channelName
            ]);
        }
        
        // Cache miss - calculate from database
        $today = $this->businessTodayDate();
        list($start_date, $end_date) = $this->businessMonthRange();
    
        // Get host data
        $host_data = RedisCacheFunction::findbyId($host_id);
        if (!$host_data) {
            return [
                'host_list' => [],
                'co_host_list' => [],
                'host_balance' => 0,
                'star' => 0,
                'star_complete_parcent' => 0
            ];
        }
    
        // Host seat balance = host's MONTHLY gift earnings + carried previous_coin,
        // matching the top-level host_balance ($total_gift_sum below) and the audio
        // room semantics. The old $gift_values query had an `orWhere` precedence bug:
        // `WHERE channelName=X AND reciever=host OR reciever=host` collapses to
        // `reciever=host`, dropping the channel+date scope and returning the host's
        // ALL-TIME total — so the video host top bar (which reads this seat balance
        // from the host_list) showed a wrong/inflated number.
        $host_monthly_gift = Gift::where('reciever_id', $host_id)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('value');

        $host_balance = ($host_data->previous_coin ?? 0) + $host_monthly_gift;
    
        $host = [
            'channelName'       => $channelName,
            'profile'           => $host_data->profile,
            'is_vip'            => $host_data->is_vip,
            'balance'           => $host_balance,
            'co_host_name'      => $host_data->name,
            'set_no'            => "0",
            'mute'              => $live->mute ?? 0,
            'frame'             => (string)$host_data->frame,
            'co_host_id'        => (string)$host_data->id,
            'co_host_status'    => 'Accept',
            'super_mute'        => "0"
        ];
    
        $list = [$host];
        $co_host_list = [];
    
        // Get accepted co-hosts
        $accept_list = DB::table('live_calls')
            ->where('host_id', $host_id)
            ->where('channelName', $channelName)
            ->where('status', 'Accept')
            ->get();
    
        if ($accept_list->isNotEmpty()) {
            $co_host_ids = $accept_list->pluck('co_host_id')->unique();
            
            // Get co-hosts
            $co_hosts = User::whereIn('id', $co_host_ids)->get()->keyBy('id');
    
            // Get co-host gifts
            $co_host_gifts = Gift::where('channelName', $channelName)
                ->whereIn('reciever_id', $co_host_ids)
                ->groupBy('reciever_id')
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->pluck('total_value', 'reciever_id');
    
            foreach ($accept_list as $call) {
                $co_host = $co_hosts->get($call->co_host_id);
                if (!$co_host) continue;
    
                $co_host_balance = $co_host_gifts[$call->co_host_id] ?? 0;
    
                $co_host_data = [
                    'channelName'       => $channelName,
                    'profile'           => $co_host->profile,
                    'is_vip'            => $co_host->is_vip,
                    'balance'           => $co_host_balance,
                    'co_host_name'      => $co_host->name,
                    'set_no'            => "0",
                    'mute'              => $call->mute,
                    'frame'             => (string)$co_host->frame,
                    'co_host_id'        => (string)$call->co_host_id,
                    'co_host_status'    => (string)$call->is_co_host_active,
                    'super_mute'        => (string)$call->super_mute,
                ];
    
                $list[] = $co_host_data;
                $co_host_list[] = $co_host_data;
            }
        }
    
        // 🟢 FIXED: Calculate monthly gift from DB (not from RedisCache)
        $monthly_gift = Gift::where('reciever_id', $host_id)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('value');
        
        // 🟢 FIXED: Add previous_coin like old code
        $total_gift_sum = ($host_data->previous_coin + $monthly_gift);
        
        // 🟢 FIXED: Calculate today's gift from DB
        $today_gift = Gift::where('reciever_id', $host_id)
            ->whereDate('date', $today)
            ->sum('value');
    
        // Star level calculation
        $levels = [
            [0, 50000, 1, 50000],
            [50000, 200000, 2, 200000],
            [200000, 500000, 3, 500000],
            [500000, 1000000, 4, 1000000],
            [1000000, 2000000, 5, 2000000],
            [2000000, PHP_INT_MAX, 5, 20000000]
        ];
    
        $star = 0;
        $next_level_amount = 1;
        foreach ($levels as $level) {
            if ($today_gift >= $level[0] && $today_gift < $level[1]) {
                $star = $level[2];
                $next_level_amount = $level[3];
                break;
            }
        }
    
        $need_percent = ($next_level_amount > 0) ? intval(($today_gift / $next_level_amount) * 100) : 0;
    
        $result = [
            'host_list'              => $list,
            'co_host_list'           => $co_host_list,
            'host_balance'           => $total_gift_sum,  // 🟢 Now includes previous_coin
            'star'                   => $star,
            'star_complete_parcent'  => $need_percent,
        ];
        
        // Save to Redis cache
        try {
            Redis::setex($cacheKey, 200, serialize($result));
        } catch (\Exception $e) {
            Log::error("Redis set failed for prepareCallDetails", [
                'error' => $e->getMessage(),
                'host_id' => $host_id,
                'channel' => $channelName
            ]);
        }
    
        return $result;
    }
   private function Websoket($data)
    {
        try {
            if (!is_array($data)) {
                $data = (array) $data;
            }
    
            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped($data, ['source' => 'VideoBrdController']);
    
        } catch (\Throwable $th) {
            info('Video named WebSocket dispatch failed: ' . $th->getMessage());
        }
    }

    private function CacheRemoved()
    {
        return $this->clearJustHomeLists();
    }
}
