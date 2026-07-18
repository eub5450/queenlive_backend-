<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Willywes\AgoraSDK\RtcTokenBuilder;
use Illuminate\Support\Facades\Redis;
use App\Models\Setting;

class AgoraController extends Controller
{
    private const TOKEN_TTL_SECONDS = 10800;

    public static function GetToken($user_id, $appID, $appCertificate, $channelName)
    {
        $uid = (int) $user_id;
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = self::TOKEN_TTL_SECONDS;
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }

    public function generateToken(Request $request)
    {
        // Legacy apps may still send appCertificate/app_certificate/appCert.
        // They are intentionally ignored; token generation uses server config.
        $channelName = self::requestValue($request, array('channelName', 'channel_name', 'channel'));
        // Dynamic per-room (boss): sign with the appId+cert stored on this room's
        // user_lives row at go-live; fall back to the global Setting pair for
        // legacy/empty rooms so a token is always valid.
        $roomLive = \App\Models\UserLive::where('channelName', $channelName)->first();
        $roomAppId = $roomLive ? trim((string) $roomLive->appId) : '';
        $roomCert = $roomLive ? trim((string) $roomLive->appCertificate) : '';
        if ($roomAppId !== '' && $roomCert !== '') {
            $appID = $roomAppId; $appCertificate = $roomCert;
        } else {
            [$appID, $appCertificate] = self::resolveAgoraConfig($roomAppId);
        }
        $uid = (int) self::requestValue($request, array('user_id', 'uid'));
        $roomId = self::requestValue($request, array('room_id', 'roomId', 'live_id'));
        $roomType = self::requestValue($request, array('room_type', 'roomType', 'type'));

        if (empty($appID) || empty($appCertificate) || empty($channelName) || empty($uid)) {
            return self::tokenError(
                'Agora server config, channel name, or uid is missing.',
                'agora_token_input_missing',
                $appID,
                $channelName,
                $uid,
                $roomId,
                $roomType
            );
        }

        try {
            $cacheKey = 'queenlive:agora_token:' . md5($appID . '|' . $channelName . '|' . $uid);

            $cachedToken = Redis::get($cacheKey);

            if ($cachedToken) {
                return self::tokenResponse($cachedToken, $appID, $channelName, $uid, $roomId, $roomType);
            }

            $agora_token = self::GetToken($uid, $appID, $appCertificate, $channelName);

            Redis::setex($cacheKey, self::TOKEN_TTL_SECONDS - 120, $agora_token);

            return self::tokenResponse($agora_token, $appID, $channelName, $uid, $roomId, $roomType);
        } catch (\Throwable $e) {
            try {
                $agora_token = self::GetToken($uid, $appID, $appCertificate, $channelName);

                return self::tokenResponse($agora_token, $appID, $channelName, $uid, $roomId, $roomType);
            } catch (\Throwable $tokenError) {
                return self::tokenError(
                    'Agora token generation failed.',
                    'agora_token_generation_failed',
                    $appID,
                    $channelName,
                    $uid,
                    $roomId,
                    $roomType
                );
            }
        }
    }

    private static function resolveAgoraConfig($requestedAppId): array
    {
        $setting = Setting::query()->first();
        $appID = trim((string) ($setting->appId ?? '')); // dynamic: always DB Setting appId (single stored cert only validates this)
        $appCertificate = trim((string) ($setting->appCertificate ?? ''));

        return [$appID, $appCertificate];
    }

    private static function requestValue(Request $request, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private static function tokenResponse($token, $appID, $channelName, $uid, $roomId, $roomType)
    {
        $expiresAt = time() + self::TOKEN_TTL_SECONDS;

        return response()->json(array(
            'success' => true,
            'data' => $token,
            'token' => $token,
            'app_id' => $appID,
            'channel_name' => $channelName,
            'uid' => (int) $uid,
            'token_expire_at' => $expiresAt,
            'room_id' => $roomId,
            'room_type' => $roomType,
            'engine_type' => 'agora',
        ));
    }

    private static function tokenError($message, $code, $appID, $channelName, $uid, $roomId, $roomType)
    {
        return response()->json(array(
            'success' => false,
            'data' => '',
            'message' => $message,
            'code' => $code,
            'app_id' => $appID,
            'channel_name' => $channelName,
            'uid' => (int) $uid,
            'room_id' => $roomId,
            'room_type' => $roomType,
            'engine_type' => 'agora',
        ));
    }
}
