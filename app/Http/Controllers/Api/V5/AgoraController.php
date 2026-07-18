<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Willywes\AgoraSDK\RtcTokenBuilder;

class AgoraController extends Controller
{
    private const TOKEN_TTL_SECONDS = 10800;
    private const PK_TOKEN_TTL_SECONDS = 31536000;

    public static function GetToken($user_id, $appID, $appCertificate, $channelName)
    {
        $banned = User::where('ban_type', '!=', null)->where('id', $user_id)->first();
        if ($banned) {
            return self::bannedResponse($banned);
        }

        $user = User::find($user_id);
        $expireTimeInSeconds = ($user_id == 29219 && $user_id == 25697)
            ? 60
            : self::TOKEN_TTL_SECONDS;

        if ($user && $user->imei_number == '680ae8ebcc9abdf8') {
            return null;
        }

        $currentTimestamp = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            (int) $user_id,
            RtcTokenBuilder::RoleAttendee,
            $privilegeExpiredTs
        );
    }

    public function generateToken(Request $request)
    {
        $userId = self::requestValue($request, array('user_id', 'uid'));
        $banned = User::where('ban_type', '!=', null)->where('id', $userId)->first();
        if ($banned) {
            return self::bannedResponse($banned);
        }

        // Legacy appCertificate/app_certificate/appCert fields are accepted
        // for request compatibility only and are never trusted.
        [$appID, $appCertificate] = self::resolveAgoraConfig(
            self::requestValue($request, array('appID', 'app_id', 'appId'))
        );
        $channelName = self::requestValue($request, array('channelName', 'channel_name', 'channel'));
        $uid = (int) $userId;
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
            $agoraToken = self::GetToken($uid, $appID, $appCertificate, $channelName);
        } catch (\Throwable $e) {
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

        if (!self::hasUsableToken($agoraToken)) {
            return self::tokenError(
                'Agora token is unavailable for this user.',
                'agora_token_empty',
                $appID,
                $channelName,
                $uid,
                $roomId,
                $roomType
            );
        }

        return self::tokenResponse($agoraToken, $appID, $channelName, $uid, $roomId, $roomType, self::TOKEN_TTL_SECONDS);
    }

    public static function GetPKToken($user_id, $appID, $appCertificate, $channelName)
    {
        $banned = User::where('ban_type', '!=', null)->where('id', $user_id)->first();
        if ($banned) {
            return self::bannedResponse($banned);
        }

        $user = User::find($user_id);
        if ($user && $user->imei_number == '680ae8ebcc9abdf8') {
            return null;
        }

        $currentTimestamp = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + self::PK_TOKEN_TTL_SECONDS;

        return RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            (int) $user_id,
            RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );
    }

    public function generatePKToken(Request $request)
    {
        $userId = self::requestValue($request, array('user_id', 'uid'));
        $banned = User::where('ban_type', '!=', null)->where('id', $userId)->first();
        if ($banned) {
            return self::bannedResponse($banned);
        }

        // Legacy appCertificate/app_certificate/appCert fields are accepted
        // for request compatibility only and are never trusted.
        [$appID, $appCertificate] = self::resolveAgoraConfig(
            self::requestValue($request, array('appID', 'app_id', 'appId'))
        );
        $channelName = self::requestValue($request, array('channelName', 'channel_name', 'channel'));
        $uid = (int) $userId;
        $roomId = self::requestValue($request, array('room_id', 'roomId', 'live_id'));
        $roomType = self::requestValue($request, array('room_type', 'roomType', 'type'));

        if (empty($appID) || empty($appCertificate) || empty($channelName) || empty($uid)) {
            return self::tokenError(
                'Agora server config, channel name, or uid is missing.',
                'agora_pk_token_input_missing',
                $appID,
                $channelName,
                $uid,
                $roomId,
                $roomType
            );
        }

        try {
            $agoraToken = self::GetPKToken($uid, $appID, $appCertificate, $channelName);
        } catch (\Throwable $e) {
            return self::tokenError(
                'Agora token generation failed.',
                'agora_pk_token_generation_failed',
                $appID,
                $channelName,
                $uid,
                $roomId,
                $roomType
            );
        }

        if (!self::hasUsableToken($agoraToken)) {
            return self::tokenError(
                'Agora token is unavailable for this user.',
                'agora_pk_token_empty',
                $appID,
                $channelName,
                $uid,
                $roomId,
                $roomType
            );
        }

        return self::tokenResponse($agoraToken, $appID, $channelName, $uid, $roomId, $roomType, self::PK_TOKEN_TTL_SECONDS);
    }

    private static function resolveAgoraConfig($requestedAppId): array
    {
        $setting = Setting::query()->first();
        $configuredAppID = trim((string) ($setting->appId ?? ''));
        $appID = $configuredAppID !== '' ? $configuredAppID : trim((string) $requestedAppId);
        $appCertificate = trim((string) ($setting->appCertificate ?? ''));

        return [$appID, $appCertificate];
    }

    private static function hasUsableToken($token): bool
    {
        if (!is_string($token)) {
            return false;
        }

        $token = trim($token);
        return $token !== '' && $token[0] !== '{' && $token[0] !== '[';
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

    private static function tokenResponse($token, $appID, $channelName, $uid, $roomId, $roomType, $ttlSeconds)
    {
        $expiresAt = time() + $ttlSeconds;

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

    private static function bannedResponse($banned)
    {
        $response = array();
        if ($banned->ban_type == 'B') {
            array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
        } elseif ($banned->ban_type == 'C') {
            array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
        } elseif ($banned->ban_type == 'D') {
            array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
        } else {
            array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
