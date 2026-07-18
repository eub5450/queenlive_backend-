<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Willywes\AgoraSDK\RtcTokenBuilder;
use App\Models\User;
use App\Models\Setting;
use App\Models\BanDevice;
class AgoraController extends Controller
{
    private const TOKEN_TTL_SECONDS = 10800;
    private const PK_TOKEN_TTL_SECONDS = 31536000;
   
    public static function GetToken($user_id, $appID, $appCertificate, $channelName)
    {
         //$ban_device=BanDevice::where('device_id',$device_id)->first();
            $banned=User::where('ban_type','!=',Null)->where('id',$user_id)->first();
           // if(!$ban_device){
            if(!$banned){
        $appID = $appID;
        $appCertificate = $appCertificate;
        $channelName = $channelName;
        $uid = $user_id;
        $uidStr = ($user_id) . '';
        $user=User::find($user_id);
        $role = RtcTokenBuilder::RoleAttendee;
        if($user_id==29219 && $user_id==25697 ){
             $expireTimeInSeconds = 60;
        }else{
            $expireTimeInSeconds = 10800;
        }
        if(!$user || $user->imei_number!='680ae8ebcc9abdf8'){
        
        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        }
            }else{
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
        // }else{
        //         array_push($response,array('message'=>'Opps !! You Are Permanent Ben','code'=>'404'));
        //         return json_encode($response,JSON_UNESCAPED_UNICODE);
        //     // return response()->json(['message'=>"User Not Found"],404);
        //     }
    }


    public function generateToken(Request $request)
    {
       //  $ban_device=BanDevice::where('device_id',$device_id)->first();
            $banned=User::where('ban_type','!=',Null)->where('id',$request->user_id)->first();
         //   if(!$ban_device){
            if(!$banned){
                // Legacy appCertificate/app_certificate/appCert fields are accepted
                // for request compatibility only and are never trusted.
                [$appID, $appCertificate] = self::resolveAgoraConfig(
                    self::requestValue($request, array('appID', 'app_id', 'appId'))
                );
                $channelName = self::requestValue($request, array('channelName', 'channel_name', 'channel'));
        
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
                    $agora_token = AgoraController::GetToken($uid,  $appID, $appCertificate, $channelName);
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
        
                return self::tokenResponse($agora_token, $appID, $channelName, $uid, $roomId, $roomType, self::TOKEN_TTL_SECONDS);
            }else{
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
        // }else{
        //         array_push($response,array('message'=>'Opps !! You Are Permanent Ben','code'=>'404'));
        //         return json_encode($response,JSON_UNESCAPED_UNICODE);
        //     // return response()->json(['message'=>"User Not Found"],404);
        //     }
    }public static function GetPKToken($user_id, $appID, $appCertificate, $channelName)
    {
         //$ban_device=BanDevice::where('device_id',$device_id)->first();
            $banned=User::where('ban_type','!=',Null)->where('id',$user_id)->first();
           // if(!$ban_device){
            if(!$banned){
        $appID = $appID;
        $appCertificate = $appCertificate;
        $channelName = $channelName;
        $uid = $user_id;
        $uidStr = ($user_id) . '';
        $user=User::find($user_id);
        $role = RtcTokenBuilder::RolePublisher;
      
            $expireTimeInSeconds =  31536000;
        
        if(!$user || $user->imei_number!='680ae8ebcc9abdf8'){
        
        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        }
            }else{
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
        // }else{
        //         array_push($response,array('message'=>'Opps !! You Are Permanent Ben','code'=>'404'));
        //         return json_encode($response,JSON_UNESCAPED_UNICODE);
        //     // return response()->json(['message'=>"User Not Found"],404);
        //     }
    }


    public function generatePKToken(Request $request)
    {
       //  $ban_device=BanDevice::where('device_id',$device_id)->first();
            $banned=User::where('ban_type','!=',Null)->where('id',$request->user_id)->first();
         //   if(!$ban_device){
            if(!$banned){
                // Legacy appCertificate/app_certificate/appCert fields are accepted
                // for request compatibility only and are never trusted.
                [$appID, $appCertificate] = self::resolveAgoraConfig(
                    self::requestValue($request, array('appID', 'app_id', 'appId'))
                );
                $channelName = self::requestValue($request, array('channelName', 'channel_name', 'channel'));
        
                $uid = (int) self::requestValue($request, array('user_id', 'uid'));
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
                    $agora_token = AgoraController::GetPKToken($uid,  $appID, $appCertificate, $channelName);
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
        
                return self::tokenResponse($agora_token, $appID, $channelName, $uid, $roomId, $roomType, self::PK_TOKEN_TTL_SECONDS);
            }else{
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
        // }else{
        //         array_push($response,array('message'=>'Opps !! You Are Permanent Ben','code'=>'404'));
        //         return json_encode($response,JSON_UNESCAPED_UNICODE);
        //     // return response()->json(['message'=>"User Not Found"],404);
        //     }
    }

    private static function resolveAgoraConfig($requestedAppId): array
    {
        $setting = Setting::query()->first();
        $appID = trim((string) ($requestedAppId ?: ($setting->appId ?? '')));
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
}
