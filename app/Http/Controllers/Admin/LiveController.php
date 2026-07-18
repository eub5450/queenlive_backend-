<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudienceJoin;
use App\Models\LiveCall;
use App\Models\User;
use App\Models\UserLive;
use App\Services\V5\RoomActionService;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Database;
use Willywes\AgoraSDK\RtcTokenBuilder;

class LiveController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function Index()
    {
        $lives = UserLive::orderBy('id', 'desc')->get();
        $seatStats = $this->buildSeatStats($lives);

        return view('backend.live.index', compact('lives', 'seatStats'));
    }

    public function Preview($id)
    {
        $live = UserLive::find($id);
        if (!$live) {
            return response()->json(array('success' => false, 'message' => 'Live room not found'), 404);
        }

        $host = User::find($live->user_id);
        $seatStats = $this->buildSeatStats(collect(array($live)));
        $roomSeatStats = isset($seatStats[$live->id]) ? $seatStats[$live->id] : array(
            'seat_limit' => (int) $live->siteNumber,
            'active_seats' => 0,
            'audience_count' => 0,
        );
        $roomType = ((int) $live->type === 2) ? 'video' : 'audio';

        $payload = array(
            'success' => true,
            'room' => array(
                'id' => (int) $live->id,
                'type' => $roomType,
                'channelName' => (string) $live->channelName,
                'name' => (string) $live->name,
                'host_id' => (int) $live->user_id,
                'host_name' => $host ? (string) $host->name : (string) $live->name,
                'host_profile' => $host ? (string) $host->profile : '',
                'created_at' => optional($live->created_at)->toDateTimeString(),
                'seat_limit' => (int) $roomSeatStats['seat_limit'],
                'active_seats' => (int) $roomSeatStats['active_seats'],
                'audience_count' => (int) $roomSeatStats['audience_count'],
            ),
        );

        if ($roomType === 'video') {
            if (empty($live->appId) || empty($live->appCertificate)) {
                return response()->json(array('success' => false, 'message' => 'Agora app config missing for this video room'), 422);
            }

            $viewerUid = (int) (Auth::id() ?: 999999);
            $payload['provider'] = 'agora';
            $payload['agora'] = array(
                'appId' => (string) $live->appId,
                'channelName' => (string) $live->channelName,
                'uid' => $viewerUid,
                'token' => $this->buildAgoraViewerToken($viewerUid, (string) $live->appId, (string) $live->appCertificate, (string) $live->channelName),
            );
        } else {
            $viewerUid = (int) (Auth::id() ?: 999999);
            $livekitToken = $this->buildLiveKitViewerToken($viewerUid, (string) $live->channelName);
            if (empty($livekitToken)) {
                return response()->json(array('success' => false, 'message' => 'Fresh LiveKit preview token unavailable'), 422);
            }

            $payload['provider'] = 'livekit';
            $payload['livekit'] = array(
                'url' => config('services.livekit.url', env('LIVEKIT_URL', 'wss://rtc.bplive.online')),
                'token' => $livekitToken,
                'roomName' => (string) $live->channelName,
            );
        }

        return response()->json($payload);
    }

    public function Off($id)
    {
        $live = UserLive::find($id);
        if (!$live) {
            return Redirect()->back()->with(array('messege' => 'Live not found (already off)', 'alert-type' => 'warning'));
        }

        $row = array();
        $row['channelName'] = $live->channelName;
        $row['status'] = strval(0);
        $row['host_id'] = strval($live->user_id);
        $push_count_ref = $this->database->getReference('official_brd_off/' . $live->channelName . '/' . $live->user_id);
        $push_count_ref->set($row);

        try {
            app(\App\Services\LiveOffService::class)->offRoom((string) $live->channelName, (string) $live->user_id, (string) (auth()->id() ?: $live->user_id), 'admin_panel');
        } catch (\Throwable $e) {
            \Log::warning('admin Off LiveOffService failed: ' . $e->getMessage());
            try {
                $live->delete();
            } catch (\Throwable $e2) {
            }
        }

        return Redirect()->back()->with(array('messege' => 'Live Remove SuccessFully', 'alert-type' => 'success'));
    }

    public function CohostMute($liveId, $coHostId, \Illuminate\Http\Request $request, RoomActionService $roomActionService)
    {
        $live = UserLive::find($liveId);
        if (!$live) {
            return Redirect()->back()->with(array('messege' => 'Live room not found', 'alert-type' => 'warning'));
        }

        $call = LiveCall::where('channelName', $live->channelName)
            ->where('host_id', $live->user_id)
            ->where('co_host_id', $coHostId)
            ->where('status', 'Accept')
            ->first();

        if (!$call) {
            return Redirect()->back()->with(array('messege' => 'Cohost not found in this live room', 'alert-type' => 'warning'));
        }

        $mute = (int) $request->input('mute', (int) $call->mute);
        if (!in_array($mute, array(0, 1), true)) {
            $mute = (int) $call->mute === 0 ? 1 : 0;
        }

        $result = $roomActionService->muteCohost(
            $this->resolveRoomType($live),
            (string) $live->channelName,
            (string) $live->user_id,
            array(
                'co_host_id' => (string) $coHostId,
                'user_id' => (string) $coHostId,
                'mute' => $mute,
                'super_mute' => (int) ($call->super_mute ?? 0),
            )
        );

        if (!is_array($result) || !($result['ok'] ?? false)) {
            $message = is_array($result) && !empty($result['error'])
                ? 'Cohost mute update failed: ' . $result['error']
                : 'Cohost mute update failed';

            return Redirect()->back()->with(array('messege' => $message, 'alert-type' => 'error'));
        }

        return Redirect()->back()->with(array(
            'messege' => $mute === 0 ? 'Cohost muted successfully' : 'Cohost unmuted successfully',
            'alert-type' => 'success',
        ));
    }

    private function buildSeatStats($lives)
    {
        $stats = array();
        foreach ($lives as $live) {
            $activeSeats = LiveCall::where('channelName', $live->channelName)
                ->where('host_id', $live->user_id)
                ->where('status', 'Accept')
                ->count();
            $audienceCount = AudienceJoin::where('channelName', $live->channelName)
                ->where('host_id', $live->user_id)
                ->count();
            $cohosts = LiveCall::leftJoin('users', 'users.id', '=', 'live_calls.co_host_id')
                ->where('live_calls.channelName', $live->channelName)
                ->where('live_calls.host_id', $live->user_id)
                ->where('live_calls.status', 'Accept')
                ->where('live_calls.is_co_host_active', 'Accept')
                ->orderByRaw('CASE WHEN live_calls.set_no IS NULL THEN 999 ELSE live_calls.set_no END')
                ->select(
                    'live_calls.co_host_id',
                    'live_calls.set_no',
                    'live_calls.mute',
                    'live_calls.super_mute',
                    'users.name',
                    'users.profile'
                )
                ->get()
                ->map(function ($row) {
                    return array(
                        'co_host_id' => (int) $row->co_host_id,
                        'set_no' => $row->set_no,
                        'mute' => (int) $row->mute,
                        'super_mute' => (int) ($row->super_mute ?? 0),
                        'name' => (string) ($row->name ?? 'Unknown User'),
                        'profile' => (string) ($row->profile ?? ''),
                    );
                })
                ->all();

            $stats[$live->id] = array(
                'seat_limit' => (int) $live->siteNumber,
                'active_seats' => (int) $activeSeats,
                'audience_count' => (int) $audienceCount,
                'cohosts' => $cohosts,
            );
        }

        return $stats;
    }

    private function resolveRoomType(UserLive $live)
    {
        if ((int) $live->type === 2) {
            return 'video';
        }

        if ((int) $live->type === 3) {
            return 'multi';
        }

        return 'audio';
    }

    private function buildAgoraViewerToken($viewerUid, $appId, $appCertificate, $channelName)
    {
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appId, $appCertificate, $channelName, $viewerUid, RtcTokenBuilder::RoleAttendee, $privilegeExpiredTs);
    }


    private function buildLiveKitViewerToken($viewerUid, $roomName)
    {
        $apiKey = $this->readPrivateEnvValue('LIVEKIT_QueenLive_API_KEY');
        $apiSecret = $this->readPrivateEnvValue('LIVEKIT_QueenLive_API_SECRET');
        if (empty($apiKey) || empty($apiSecret) || empty($roomName)) {
            return null;
        }

        $now = time();
        $identity = (string) $viewerUid;
        $claims = array(
            'exp' => $now + 3600,
            'nbf' => $now - 10,
            'iss' => $apiKey,
            'sub' => $identity,
            'identity' => $identity,
            'video' => array(
                'room' => $roomName,
                'roomJoin' => true,
                'canSubscribe' => true,
                'canPublish' => false,
            ),
        );

        return $this->encodeJwt($claims, $apiSecret);
    }

    private function readPrivateEnvValue($key)
    {
        $value = env($key);
        if (!empty($value)) {
            return $value;
        }

        $envPath = base_path('.env');
        if (!is_readable($envPath)) {
            return null;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, $key . '=') !== 0) {
                continue;
            }

            $value = substr($line, strlen($key) + 1);
            return trim($value, " \t\n\r\0\x0B\"'");
        }

        return null;
    }

    private function encodeJwt($claims, $secret)
    {
        $header = array('alg' => 'HS256', 'typ' => 'JWT');
        $segments = array(
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES)),
        );
        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
