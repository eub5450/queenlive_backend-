<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\jambo\JamboController;

use App\Http\Controllers\Api\V4\DiagnosticsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Database backup (every 12 hours)
Route::get('/cron/backup/database/{token}', [BackupController::class, 'databaseBackup']);

// Custom file backup (daily at 1 AM) - backs up config, app, routes, resources, .env, .htaccess
Route::get('/cron/backup/files/{token}', [BackupController::class, 'fileBackup']);

Route::post('v4/mobile/diagnostics', [DiagnosticsController::class, 'ingest']);
Route::post('v4/mobile/diagnostics/github-relay', [DiagnosticsController::class, 'githubRelay']);
Route::get('v4/mobile/diagnostics/recent', [DiagnosticsController::class, 'recent']);
Route::get('v4/mobile/diagnostics/summary', [DiagnosticsController::class, 'summary']);
Route::get('v4/mobile/diagnostics/dashboard', [DiagnosticsController::class, 'dashboard']);
Route::get('v4/mobile/diagnostics/download-md', [DiagnosticsController::class, 'downloadMarkdown']);
Route::group(['namespace' => 'Api'], function () {

Route::post('/login','AuthController@login');
Route::post('/register','AuthController@UserRegister');
Route::post('/google_auth','AuthController@GoogleLogin');
Route::post('/setting_info','AuthController@VarsionInfo');
// v5 setting_info: same body as live v4 but JSON Content-Type + ETag/Cache-Control
// (304 on repeat). GET+POST so trailing-slash 301 downgrades don't 405. New app
// build targets this; live v4 POST /setting_info is left exactly as-is.
Route::post('v5/setting_info','AuthController@VarsionInfoV5');
Route::get('v5/setting_info','AuthController@VarsionInfoV5');
Route::post('/forget_password','AuthController@ForgetPassword');
Route::get('Noti','AudioBrdController@send_push_notification');
Route::get('play_generate_live_token','AgoraController@generateToken');

Route::post('/jambo/sync', [JamboController::class, 'sync'])->name('jambo.api.sync');
Route::post('/jambo/parse-images', [JamboController::class, 'parseImages'])->name('jambo.api.parse_images');
Route::post('/jambo/export', [JamboController::class, 'apiExport'])->name('jambo.api.export');

});
Route::group(['namespace' => 'Api\V4'], function () {
Route::get('v4/vip_packages','VipController@vipPackages');
Route::post('v4/vip_packages','VipController@vipPackages');
Route::get('v4/app_home','UserLiveController@HomeIndex')->middleware('throttle:home_data_limit');
Route::get('v5/app_home',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'HomeIndex'])->middleware('throttle:home_data_limit');
Route::post('v5/app_home',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'HomeIndex'])->middleware('throttle:home_data_limit');
Route::get('v4/feed','FeedController@Index')->middleware('throttle:home_data_limit');
Route::get('v5/feed',[\App\Http\Controllers\Api\V5\FeedController::class, 'Index'])->middleware('throttle:home_data_limit');
Route::post('v5/feed',[\App\Http\Controllers\Api\V5\FeedController::class, 'Index'])->middleware('throttle:home_data_limit');
Route::get('v5/feed/sections',[\App\Http\Controllers\Api\V5\FeedController::class, 'Sections'])->middleware('throttle:home_data_limit');
Route::post('v5/feed/sections',[\App\Http\Controllers\Api\V5\FeedController::class, 'Sections'])->middleware('throttle:home_data_limit');
Route::get('v4/v4/feed','FeedController@Index')->middleware('throttle:home_data_limit');
Route::get('v4/app_home_live_now','UserLiveController@LivesNowIndex')->middleware('throttle:home_data_limit');
Route::get('v5/app_home_live_now',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'LivesNowIndex'])->middleware('throttle:home_data_limit');
Route::post('v5/app_home_live_now',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'LivesNowIndex'])->middleware('throttle:home_data_limit');
Route::get('v4/user_data','UserDataController@Index');
Route::get('v5/user_data',[\App\Http\Controllers\Api\V5\UserDataController::class, 'Index']);
Route::post('v5/user_data',[\App\Http\Controllers\Api\V5\UserDataController::class, 'Index']);
Route::post('v4/room_comment_ws','RoomCommentController@Send');
});
Route::get('v5/room-fun-emoji', function () {
    $asset = function ($path) {
        $p = trim((string) $path);
        if ($p === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $p)) {
            return $p;
        }
        return url('/' . ltrim($p, '/'));
    };

    $default = [
        ['id' => 'fun_offer_pop', 'type' => 'webp', 'url' => $asset('store/banner/offer.webp')],
        ['id' => 'fun_eid_spark', 'type' => 'webp', 'url' => $asset('store/banner/eid.png.webp')],
        ['id' => 'fun_gold_wave', 'type' => 'webp', 'url' => $asset('store/banner/681313218872c.webp')],
        ['id' => 'fun_game_pop', 'type' => 'webp', 'url' => $asset('game/greedy.png.webp')],
        ['id' => 'fun_fruit_flash', 'type' => 'webp', 'url' => $asset('game/fruitsloops.png.webp')],
        ['id' => 'fun_smile_card', 'type' => 'image', 'url' => $asset('backend/it-solutionsbd/assets/dist/img/wow-slider-logo.png')],
    ];

    $normalizeCatalog = function ($raw) use ($asset) {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $type = strtolower(trim((string) ($row['type'] ?? 'image')));
            $url = $asset($row['url'] ?? '');
            if ($id !== '' && $url !== '' && in_array($type, ['webp', 'gif', 'svga', 'image'], true)) {
                $rows[] = ['id' => $id, 'type' => $type, 'url' => $url];
            }
        }
        return $rows;
    };

    try {
        $store = \App\Support\SystemSettingRuntimeStore::all();
        if (array_key_exists('fun_sticker_catalog', $store)) {
            $rows = $normalizeCatalog($store['fun_sticker_catalog']);
            if (!empty($rows)) {
                \Illuminate\Support\Facades\Cache::forever('fun_sticker_catalog', $rows);
                return response()->json($rows, 200, [], JSON_UNESCAPED_SLASHES);
            }

            \Illuminate\Support\Facades\Cache::forget('fun_sticker_catalog');
            return response()->json($default, 200, [], JSON_UNESCAPED_SLASHES);
        }
    } catch (\Throwable $e) {
        // Fall back to cache/default.
    }

    try {
        $rows = $normalizeCatalog(\Illuminate\Support\Facades\Cache::get('fun_sticker_catalog'));
        if (!empty($rows)) {
            return response()->json($rows, 200, [], JSON_UNESCAPED_SLASHES);
        }
    } catch (\Throwable $e) {
        // Fall back to the built-in catalog.
    }

    return response()->json($default, 200, [], JSON_UNESCAPED_SLASHES);
});
Route::group(['namespace' => 'Api\V4','middleware' => ['auth:sanctum', 'check.ban']], function () {
Route::post('v4/broadcasting/auth','AudioRoomBroadcastAuthController@authenticate');
Route::post('v5/broadcasting/auth',[\App\Http\Controllers\Api\V5\AudioRoomBroadcastAuthController::class, 'authenticate']);

Route::get('v4/active_coinbeg','CoinBegController@ActiveCoinBeg');
Route::get('v4/clim_coin_beg','CoinBegController@Claim');
  
Route::get('Notification','VideoBrdController@Notification');
Route::get('v4/logout','AuthController@Logout');
Route::get('v4/login_user','AuthController@UserData');
Route::get('v4/change_password','AuthController@ChangePassword');
//Product
Route::get('v4/rank','RankingController@RankList');
Route::get('v4/top_list','RankingController@TopList');
Route::get('v4/generate_live_token','AgoraController@generateToken');
Route::get('v5/generate_live_token',[\App\Http\Controllers\Api\V5\AgoraController::class, 'generateToken']);
Route::post('v5/generate_live_token',[\App\Http\Controllers\Api\V5\AgoraController::class, 'generateToken']);
Route::get('v4/generate_pk_live_token','AgoraController@generatePKToken');
Route::get('v4/comment_skip_word_list','CommentSkipController@WordList');

Route::get('v4/user_live_store','UserLiveController@Store');
Route::get('v4/user_live_home','UserLiveController@Index')->middleware('throttle:home_data_limit');
Route::get('v4/party_index','UserLiveController@PartyIndex');
Route::get('v5/party_index',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'PartyIndex']);
Route::post('v5/party_index',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'PartyIndex']);
Route::get('v4/user_friend_live','UserLiveController@FriendsLive');
  
Route::get('v4/user_live_remove','UserLiveController@Delete')->middleware('legacy.mutation.no_cache');
Route::post('v4/user_live_remove','UserLiveController@Delete')->middleware('legacy.mutation.no_cache');
Route::get('v5/user_live_remove',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'Delete'])->middleware('legacy.mutation.no_cache');
Route::post('v5/user_live_remove',[\App\Http\Controllers\Api\V5\UserLiveController::class, 'Delete'])->middleware('legacy.mutation.no_cache');
Route::get('v4/comments_socket_io_store','CommentController@Store');
Route::get('v4/entry_realtime','CommentController@CheckEntry');
Route::get('v4/fly_comment','CommentController@FlyComment');
Route::get('v4/join_socket_io_store','CommentController@JoinStore');
Route::get('v4/gift_socket_io_store','CommentController@GiftPush');
Route::get('v4/audio_gift_socket_io_store','CommentController@AudioGiftPush');
Route::get('v4/audience_leave','CommentController@AudienceLeave');
Route::get('v4/audience_list','CommentController@AudienceList');
Route::get('v4/comment_mute_add','CommentController@CommentMute');
Route::get('v4/comment_mute_removed','CommentController@CommentMuteRemove');

Route::get('v4/online_pay','OnlinePaymentController@OnlinePayment');

Route::get('v4/check_balance_monthly','GiftController@HostBalanceChack');
Route::get('v4/gift_file_data','GiftController@GiftData');
Route::get('v4/exchange','GiftController@exchange');
Route::post('v4/exchange-store','GiftController@exchangestore');

//profile
Route::get('v4/profile/live_data','ProfileController@ProfileLiveData');
Route::get('v4/live_data','ProfileController@LiveData');
Route::get('v4/profile/update','ProfileController@ProfileUpdate');
Route::get('v4/profile/visit','ProfileController@Visitor');

Route::get('v4/co_host_request','LiveCoHostController@JoinCall')->middleware('legacy.mutation.no_cache');
Route::post('v4/co_host_request','LiveCoHostController@JoinCall')->middleware('legacy.mutation.no_cache');
Route::get('v4/co_host_request_list','LiveCoHostController@CallList');
Route::get('v4/co_host_request_accept','LiveCoHostController@CallAccept');
Route::get('v4/co_host_call_remove','LiveCoHostController@CallRemoved');
Route::get('v4/co_host_call_accept_list','LiveCoHostController@CallAcceptList');
//avater
Route::get('v4/aveter','AvaterController@Avater');
Route::get('v5/aveter',[\App\Http\Controllers\Api\V5\AvaterController::class, 'Avater']);
Route::post('v5/aveter',[\App\Http\Controllers\Api\V5\AvaterController::class, 'Avater']);
Route::get('v4/aveter-store','AvaterController@store');
Route::get('v5/aveter-store',[\App\Http\Controllers\Api\V5\AvaterController::class, 'store']);
Route::post('v5/aveter-store',[\App\Http\Controllers\Api\V5\AvaterController::class, 'store']);
Route::get('v4/co_host_call_mute_audio_brd_firebase','LiveCoHostController@AudioCallMuteFirebase');

//Audio BRD
Route::get('v4/audio_co_host_request','AudioBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::post('v4/audio_co_host_request','AudioBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_room_info',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'RoomInfo']);
Route::post('v5/audio_room_info',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'RoomInfo']);
Route::get('v5/audio_user_live_store',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'Store']);
Route::post('v5/audio_user_live_store',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'Store']);
Route::get('v5/audio_brd_host_list',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostList']);
Route::post('v5/audio_brd_host_list',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostList']);
Route::get('v5/audio_brd_user_data',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'UserData']);
Route::post('v5/audio_brd_user_data',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'UserData']);
Route::get('v5/audio_brd_seat_count_update',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatCountUpdate']);
Route::post('v5/audio_brd_seat_count_update',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatCountUpdate']);
Route::get('v5/audio_seat_move', [\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatMove'])->middleware('legacy.mutation.no_cache');
Route::post('v5/audio_seat_move', [\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatMove'])->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_brd_call_request_list',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallList']);
Route::post('v5/audio_brd_call_request_list',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallList']);
Route::get('v5/audio_co_host_request',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::post('v5/audio_co_host_request',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_call_accept',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'AudioCallAccept'])->middleware('legacy.mutation.no_cache');
Route::post('v5/audio_call_accept',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'AudioCallAccept'])->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_host_call_accept_via_audience',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'AudioCallAcceptViaAudience']);
Route::post('v5/audio_host_call_accept_via_audience',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'AudioCallAcceptViaAudience']);
Route::get('v5/audio_call_mute',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallMute']);
Route::post('v5/audio_call_mute',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallMute']);
Route::get('v5/audio_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallRemoved']);
Route::post('v5/audio_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CallRemoved']);
Route::get('v5/audio_brd_host_mute',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostMue']);
Route::post('v5/audio_brd_host_mute',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostMue']);
Route::post('v5/audio_gift_push',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'AudioGiftPush'])->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_kick',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'Kick'])->middleware('legacy.mutation.no_cache');
Route::post('v5/audio_kick',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'Kick'])->middleware('legacy.mutation.no_cache');
Route::get('v5/audio_host_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostCallRemove']);
Route::post('v5/audio_host_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'HostCallRemove']);
Route::get('v5/audio_brd_lock_unlock',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'LockUnlock']);
Route::post('v5/audio_brd_lock_unlock',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'LockUnlock']);
Route::get('v5/audio_brd_seat_lock_update',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatLockUpdate']);
Route::post('v5/audio_brd_seat_lock_update',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SeatLockUpdate']);
Route::get('v5/audio_brd_pending_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'PendingCallRemoved']);
Route::post('v5/audio_brd_pending_call_remove',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'PendingCallRemoved']);
Route::get('v5/audio_check_co_host_active_or_inactive_call',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CohostisActive']);
Route::post('v5/audio_check_co_host_active_or_inactive_call',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'CohostisActive']);
Route::get('v5/audio_sand_emoji',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SandEmoji']);
Route::post('v5/audio_sand_emoji',[\App\Http\Controllers\Api\V5\AudioBrdController::class, 'SandEmoji']);
Route::get('v4/audio_brd_host_list','AudioBrdController@HostList');
Route::get('v4/audio_user_live_store','AudioBrdController@Store');
Route::get('v4/audio_brd_call_request_list','AudioBrdController@CallList');
Route::get('v4/audio_call_accept','AudioBrdController@AudioCallAccept')->middleware('legacy.mutation.no_cache');
Route::post('v4/audio_call_accept','AudioBrdController@AudioCallAccept')->middleware('legacy.mutation.no_cache');
Route::get('v4/audio_host_call_accept_via_audience','AudioBrdController@AudioCallAcceptViaAudience');
Route::get('v4/audio_call_mute','AudioBrdController@CallMute');
Route::get('v4/audio_call_remove','AudioBrdController@CallRemoved');
Route::get('v4/audio_brd_user_data','AudioBrdController@UserData');
Route::get('v4/audio_brd_host_mute','AudioBrdController@HostMue');
Route::get('v4/audio_gift_push','AudioBrdController@AudioGiftPush')->middleware('legacy.mutation.no_cache');
Route::post('v4/audio_gift_push','AudioBrdController@AudioGiftPush')->middleware('legacy.mutation.no_cache');
Route::get('v4/audio_kick','AudioBrdController@Kick')->middleware('legacy.mutation.no_cache');
Route::post('v4/audio_kick','AudioBrdController@Kick')->middleware('legacy.mutation.no_cache');
Route::get('v4/audio_host_call_remove','AudioBrdController@HostCallRemove');
Route::get('v4/audio_brd_lock_unlock','AudioBrdController@LockUnlock');
Route::get('v4/audio_brd_seat_count_update','AudioBrdController@SeatCountUpdate');
Route::post('v4/audio_brd_seat_count_update','AudioBrdController@SeatCountUpdate');
Route::get('v4/audio_brd_seat_lock_update','AudioBrdController@SeatLockUpdate');
Route::post('v4/audio_brd_seat_lock_update','AudioBrdController@SeatLockUpdate');
Route::get('v4/audio_brd_pending_call_remove','AudioBrdController@PendingCallRemoved');
Route::get('v4/audio_check_co_host_active_or_inactive_call','AudioBrdController@CohostisActive');
Route::get('v4/audio_sand_emoji','AudioBrdController@SandEmoji');

//Multi BRD
Route::get('v5/multi_co_host_request',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::post('v5/multi_co_host_request',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::get('v5/multi_brd_host_list',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'HostList']);
Route::post('v5/multi_brd_host_list',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'HostList']);
Route::get('v5/multi_user_live_store',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'Store']);
Route::post('v5/multi_user_live_store',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'Store']);
Route::get('v5/multi_brd_call_request_list',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallList']);
Route::post('v5/multi_brd_call_request_list',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallList']);
Route::get('v5/multi_call_accept',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'AudioCallAccept'])->middleware('legacy.mutation.no_cache');
Route::post('v5/multi_call_accept',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'AudioCallAccept'])->middleware('legacy.mutation.no_cache');
Route::get('v5/multi_call_mute',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallMute']);
Route::post('v5/multi_call_mute',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallMute']);
Route::get('v5/multi_call_remove',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallRemoved']);
Route::post('v5/multi_call_remove',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'CallRemoved']);
Route::get('v5/multi_host_call_remove',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'HostCallRemove']);
Route::post('v5/multi_host_call_remove',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'HostCallRemove']);
Route::get('v5/multi_brd_user_data',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'UserData']);
Route::post('v5/multi_brd_user_data',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'UserData']);
Route::post('v5/multi_gift_push',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'AudioGiftPush'])->middleware('legacy.mutation.no_cache');
Route::get('v5/multi_kick',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'Kick'])->middleware('legacy.mutation.no_cache');
Route::post('v5/multi_kick',[\App\Http\Controllers\Api\V5\MultiBrdController::class, 'Kick'])->middleware('legacy.mutation.no_cache');
Route::get('v4/multi_co_host_request','MultiBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::post('v4/multi_co_host_request','MultiBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::get('v4/multi_brd_host_list','MultiBrdController@HostList');
Route::get('v4/multi_user_live_store','MultiBrdController@Store');
Route::get('v4/multi_brd_call_request_list','MultiBrdController@CallList');
Route::get('v4/multi_call_accept','MultiBrdController@AudioCallAccept');
Route::get('v4/multi_host_call_accept_via_audience','MultiBrdController@AudioCallAcceptViaAudience');
Route::get('v4/multi_call_mute','MultiBrdController@CallMute');
Route::get('v4/multi_call_remove','MultiBrdController@CallRemoved');
Route::get('v4/multi_brd_user_data','MultiBrdController@UserData');
Route::get('v4/multi_brd_host_mute','MultiBrdController@HostMue');
Route::get('v4/multi_gift_push','MultiBrdController@AudioGiftPush');
Route::get('v4/multi_kick','MultiBrdController@Kick');
Route::get('v4/multi_host_call_remove','MultiBrdController@HostCallRemove');
Route::get('v4/multi_brd_lock_unlock','MultiBrdController@LockUnlock');
Route::get('v4/multi_brd_pending_call_remove','MultiBrdController@PendingCallRemoved');
Route::get('v4/multi_check_co_host_active_or_inactive_call','MultiBrdController@CohostisActive');
Route::get('v4/multi_camera_status_change','MultiBrdController@CameraStatusChange');
Route::get('v4/host_multi_camera_status_change','MultiBrdController@HostCallCamera');


//Video Brd
Route::get('agora_video_setting','VideoBrdController@AgoraSetting');
Route::get('v5/agora_video_setting',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'AgoraSetting']);
Route::get('v4/agora_video_setting','VideoBrdController@AgoraSetting');
Route::get('v4/co_host_call_mute','VideoBrdController@CallMute');
Route::get('v4/video_co_host_request','VideoBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::post('v4/video_co_host_request','VideoBrdController@CallRequest')->middleware('legacy.mutation.no_cache');
Route::get('v5/video_co_host_request',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::post('v5/video_co_host_request',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallRequest'])->middleware('legacy.mutation.no_cache');
Route::get('v4/video_call_accept','VideoBrdController@VideoCallAccept')->middleware('legacy.mutation.no_cache');
Route::post('v4/video_call_accept','VideoBrdController@VideoCallAccept')->middleware('legacy.mutation.no_cache');
Route::get('v5/video_call_accept',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'VideoCallAccept'])->middleware('legacy.mutation.no_cache');
Route::post('v5/video_call_accept',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'VideoCallAccept'])->middleware('legacy.mutation.no_cache');
Route::get('v4/host_call_accept_via_audience','VideoBrdController@CallAcceptViaAudience');
Route::get('v5/host_call_accept_via_audience',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallAcceptViaAudience']);
Route::post('v5/host_call_accept_via_audience',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallAcceptViaAudience']);
Route::get('v4/video_call_mute','VideoBrdController@CallMute');
Route::get('v5/video_call_mute',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallMute']);
Route::post('v5/video_call_mute',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallMute']);
Route::get('v4/video_host_call_mute','VideoBrdController@HostMue');
Route::get('v5/video_host_call_mute',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'HostMue']);
Route::post('v5/video_host_call_mute',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'HostMue']);
Route::get('v4/video_user_live_store','VideoBrdController@Store');
Route::get('v5/video_user_live_store',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'Store']);
Route::post('v5/video_user_live_store',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'Store']);
Route::get('v4/video_call_remove_by_host','VideoBrdController@HostCallRemove');
Route::get('v5/video_call_remove_by_host',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'HostCallRemove']);
Route::post('v5/video_call_remove_by_host',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'HostCallRemove']);
Route::get('v4/video_call_remove','VideoBrdController@CallRemoved');
Route::get('v5/video_call_remove',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallRemoved']);
Route::post('v5/video_call_remove',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CallRemoved']);
Route::get('v4/video_gift_push','VideoBrdController@VideoGiftPush')->middleware('legacy.mutation.no_cache');
Route::post('v4/video_gift_push','VideoBrdController@VideoGiftPush')->middleware('legacy.mutation.no_cache');
Route::post('v5/video_gift_push',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'VideoGiftPush'])->middleware('legacy.mutation.no_cache');
Route::get('v4/video_brd_user_data','VideoBrdController@UserData');
Route::get('v5/video_brd_user_data',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'UserData']);
Route::post('v5/video_brd_user_data',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'UserData']);
// New endpoint kept off the live v4 surface — app currently runs on v4, so the
// video host lock ships as v5 to avoid touching in-flight v4 behavior.
Route::get('v5/video_brd_lock_unlock',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'LockUnlock']);
Route::post('v5/video_brd_lock_unlock',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'LockUnlock']);
Route::get('v4/video_brd_pending_call_removed','VideoBrdController@PendingCallRemoved');
Route::get('v4/video_day_time_request','VideoBrdController@VideoBrdDayTimeRequest');
Route::get('v5/video_day_time_request',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'VideoBrdDayTimeRequest']);
Route::post('v5/video_day_time_request',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'VideoBrdDayTimeRequest']);
Route::get('v4/video_kick','VideoBrdController@Kick');
Route::get('v5/video_kick',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'Kick']);
Route::post('v5/video_kick',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'Kick']);
Route::get('v4/check_co_host_active_or_inactive_call','VideoBrdController@CohostisActive');
Route::get('v5/check_co_host_active_or_inactive_call',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CohostisActive']);
Route::post('v5/check_co_host_active_or_inactive_call',[\App\Http\Controllers\Api\V5\VideoBrdController::class, 'CohostisActive']);

  
//friend request
Route::get('v4/follow','FollowerController@Follow')->middleware('legacy.mutation.no_cache');
Route::post('v4/follow','FollowerController@Follow')->middleware('legacy.mutation.no_cache');
Route::get('v4/un_follow','FollowerController@UnFollow')->middleware('legacy.mutation.no_cache');
Route::post('v4/un_follow','FollowerController@UnFollow')->middleware('legacy.mutation.no_cache');
Route::get('v4/follow_request_sand','FollowerController@Store')->middleware('legacy.mutation.no_cache');
Route::post('v4/follow_request_sand','FollowerController@Store')->middleware('legacy.mutation.no_cache');
Route::get('v4/friend_list','FollowerController@FriendIndex');

  
//search 
  Route::get('v4/search','SearchController@Search');
  //chat
  Route::get('v4/chat_gift','ChatController@Gift');
  Route::get('v4/chat_store','ChatController@Store');
  Route::get('v4/lucky_gift','LuckyGiftController@Store');
//blocked 
Route::get('v4/block','BlockController@Store');
Route::get('v4/block_list','BlockController@Index');
Route::get('v4/unblock','BlockController@UnBlock');
  //user Profile
  Route::get('v4/profile_snapshot','UserDataController@ProfileSnapshot');
  Route::get('v4/host_type','UserDataController@HostType');
  Route::get('v5/host_type',[\App\Http\Controllers\Api\V5\UserDataController::class, 'HostType']);
  Route::post('v5/host_type',[\App\Http\Controllers\Api\V5\UserDataController::class, 'HostType']);
  //Agency/HostControll
Route::get('v4/my-host-list','AgencyController@MyHost');
Route::get('v4/my-host-data','AgencyController@MyHostData');
Route::get('v4/my-host-profile','AgencyController@MyHostProfile');
Route::get('v4/add-host','AgencyController@AddHost');
Route::get('v4/host_verify','AgencyController@HostVerify');

//Help
Route::get('v4/help-store','HelpController@Store');
//slider
Route::get('v4/slider','SliderController@Index');
//Transfer
Route::get('v4/protal_data','PortalController@Index');
Route::get('v4/protal_balance_transfer','PortalController@Transfer')->middleware('throttle:home_data_limit');
Route::get('v4/protal_to_protal_transfer','PortalController@ProtalTransfer')->middleware('throttle:home_data_limit');
  
Route::get('v4/invisibal_active_inactive','SettingController@ActiveInvisible');
Route::get('v4/offical_brd_off_action','SettingController@LiveOff');
  
Route::get('v4/global_live','GlobalController@Index');
Route::get('v5/global_live',[\App\Http\Controllers\Api\V5\GlobalController::class, 'Index']);
Route::post('v5/global_live',[\App\Http\Controllers\Api\V5\GlobalController::class, 'Index']);
Route::get('v4/global_live_country_wise','GlobalController@CountryWiseData');
Route::get('v5/global_live_country_wise',[\App\Http\Controllers\Api\V5\GlobalController::class, 'CountryWiseData']);
Route::post('v5/global_live_country_wise',[\App\Http\Controllers\Api\V5\GlobalController::class, 'CountryWiseData']);
Route::post('v5/agency/apply',[\App\Http\Controllers\Api\V5\AgencyController::class, 'ApplyHosting']);
Route::post('v5/agency/apply_agency',[\App\Http\Controllers\Api\V5\AgencyController::class, 'ApplyAgency']);

Route::get('v4/my_vip','VipController@Index');
Route::get('v4/vip_active','VipController@Active')->middleware('legacy.mutation.no_cache');
Route::post('v4/vip_active','VipController@Active')->middleware('legacy.mutation.no_cache');
Route::get('v4/vip_active_inactive','VipController@VIPActive');
Route::get('v4/notification','VipController@Notification');
Route::get('v5/notification',[\App\Http\Controllers\Api\V5\VipController::class, 'Notification']);
Route::post('v5/notification',[\App\Http\Controllers\Api\V5\VipController::class, 'Notification']);
Route::get('v4/notification_read_state','VipController@UpdateNotificationState');
Route::post('v4/notification_read_state','VipController@UpdateNotificationState');
Route::get('v5/notification_read_state',[\App\Http\Controllers\Api\V5\VipController::class, 'UpdateNotificationState']);
Route::post('v5/notification_read_state',[\App\Http\Controllers\Api\V5\VipController::class, 'UpdateNotificationState']);
Route::get('v4/buy_entry','VipController@BuyEntry');
Route::get('v4/store_items','VipController@EntryFrame');
Route::get('v4/vip_buy-store_items','VipController@VipIdBuy');
Route::get('v4/store_items_active_inactive','VipController@EntryFrameActiveInactive');
Route::get('v4/lavel_list','LavelController@Index');

//Invite
Route::get('v4/invite_deshbord','InviteController@Index');
Route::get('v4/invite_reward_withdraw','InviteController@Withdaw');
Route::get('v4/invite_confirm','InviteController@Invite')->middleware('legacy.mutation.no_cache');
Route::post('v4/invite_confirm','InviteController@Invite')->middleware('legacy.mutation.no_cache');
Route::get('v4/invite_cancel','InviteController@InviteCancel');

Route::get('v4/host_request_call_audience','HostCallController@CallRequest');
Route::get('v4/host_request_call_accept_audience','HostCallController@CallAccept');
Route::get('v4/audio_host_request_call_audience','HostCallController@AudioCallRequest');
Route::get('v4/audio_host_request_call_accept_audience','HostCallController@AudioCallAccept');

Route::get('v4/host_withdraw_page','WithdrawController@Index');
Route::get('v4/host_withdraw_request','WithdrawController@SuperAgencyWithdraw')->middleware('legacy.mutation.no_cache');
Route::post('v4/host_withdraw_request','WithdrawController@SuperAgencyWithdraw')->middleware('legacy.mutation.no_cache');
Route::get('v4/agency_withdraw_wallet','WithdrawController@AgencyWallet');
Route::get('v4/agency_withdraw_convart','WithdrawController@Convart');
Route::get('v4/agency_withdraw_approved','WithdrawController@Approved');

Route::get('v4/brd_image_list','BrdImageController@Index');
Route::get('v5/brd_image_list',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Index']);
Route::post('v5/brd_image_list',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Index']);
Route::get('v4/brd_image_user_remove','BrdImageController@Delete');
Route::get('v5/brd_image_user_remove',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Delete']);
Route::post('v5/brd_image_user_remove',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Delete']);
Route::get('v4/brd_image_upload','BrdImageController@Store');
Route::get('v5/brd_image_upload',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Store']);
Route::post('v5/brd_image_upload',[\App\Http\Controllers\Api\V5\BrdImageController::class, 'Store']);

Route::get('v4/add_brd_admin','BrdAdminController@Store');
Route::get('v4/removed_brd_admin','BrdAdminController@Remove');


Route::get('v4/online_coin_product','OnlineCoinPurchaseController@GetCoinList');


Route::get('v4/video_privat_call','CallController@VideoCall');
Route::get('v4/audio_privat_call','CallController@AudioCall');


Route::get('v4/random_live_data','HomeController@HomeIndex')->middleware('throttle:home_data_limit');
Route::get('v4/home_live_now','HomeController@LivesNowIndex')->middleware('throttle:home_data_limit');
//pk
Route::get('v4/pk_user_list','PkController@PkUserList')->middleware('throttle:home_data_limit');
Route::get('v4/pk_play_request','PkController@PkPlayRequest')->middleware('throttle:home_data_limit');
Route::get('v4/pk_player_search_profile','PkController@PkPlayerSearchProfile')->middleware('throttle:home_data_limit');
Route::get('v4/pk_request_accept','PkController@PkRequestAccept')->middleware('throttle:home_data_limit');

Route::get('v4/tasks/dashboard', 'TaskController@dashboard');
    Route::post('v4/tasks/claim', 'TaskController@claim');
    Route::post('v4/tasks/checkin/claim', 'TaskController@checkinClaim');
});

Route::group(['middleware' => ['auth:sanctum', 'check.ban']], function () {
    Route::post('v5/room/magic_heart_send', [\App\Http\Controllers\Api\V5\RoomActionController::class, 'magicHeartSend']);
    Route::match(['get', 'post'], 'v5/room/{roomType}/{channel}/{action}', [\App\Http\Controllers\Api\V5\RoomActionController::class, 'handle'])
        ->where('action', '.*');

    Route::get('v5/tasks/dashboard', [\App\Http\Controllers\Api\V5\TaskController::class, 'dashboard']);
    Route::post('v5/tasks/dashboard', [\App\Http\Controllers\Api\V5\TaskController::class, 'dashboard']);
    Route::post('v5/tasks/claim', [\App\Http\Controllers\Api\V5\TaskController::class, 'claim']);
    Route::post('v5/tasks/checkin/claim', [\App\Http\Controllers\Api\V5\TaskController::class, 'checkinClaim']);

    Route::post('v5/fanclub/tiers', [\App\Http\Controllers\Api\V5\FanClubController::class, 'tiers']);
    Route::post('v5/fanclub/subscribe', [\App\Http\Controllers\Api\V5\FanClubController::class, 'subscribe']);
    Route::post('v5/fanclub/mine', [\App\Http\Controllers\Api\V5\FanClubController::class, 'mine']);
    Route::post('v5/fanclub/host/{hostId}', [\App\Http\Controllers\Api\V5\FanClubController::class, 'host']);
    Route::post('v5/fanclub/renew', [\App\Http\Controllers\Api\V5\FanClubController::class, 'renew']);
    Route::post('v5/fanclub/cancel', [\App\Http\Controllers\Api\V5\FanClubController::class, 'cancel']);
    Route::post('v5/admin/fanclub/tiers', [\App\Http\Controllers\Api\V5\FanClubController::class, 'adminTiers']);
    Route::post('v5/admin/fanclub/tiers/upsert', [\App\Http\Controllers\Api\V5\FanClubController::class, 'adminUpsertTier']);
    Route::post('v5/admin/checkin/ladder', [\App\Http\Controllers\Api\V5\FanClubController::class, 'adminCheckinLadder']);
    Route::post('v5/admin/checkin/ladder/upsert', [\App\Http\Controllers\Api\V5\FanClubController::class, 'adminUpsertCheckinDay']);
    Route::post('v5/admin/combo/settings', 'Api\V5\FanClubController@adminComboSettings');
    Route::post('v5/admin/combo/settings/upsert', 'Api\V5\FanClubController@adminUpsertComboSettings');
});


Route::group(['namespace' => 'Api','middleware' => ['auth:sanctum', 'check.ban']], function () {
Route::post('logout','AuthController@Logout');
Route::post('login_user','AuthController@UserData');
Route::post('change_password','AuthController@ChangePassword');
//Product
Route::post('rank','RankingController@RankList');
Route::post('generate_live_token','AgoraController@generateToken');
Route::post('comment_skip_word_list','CommentSkipController@WordList');

Route::post('user_live_store','UserLiveController@Store');
Route::post('user_live_home','UserLiveController@Index')->middleware('throttle:home_data_limit');
Route::post('party_index','UserLiveController@PartyIndex');
Route::post('user_friend_live','UserLiveController@FriendsLive');
  
Route::post('user_live_remove','UserLiveController@Delete');
Route::post('comments_socket_io_store','CommentController@Store');
Route::post('join_socket_io_store','CommentController@JoinStore');
Route::post('gift_socket_io_store','CommentController@GiftPush');
Route::post('audio_gift_socket_io_store','CommentController@AudioGiftPush');
Route::post('audience_leave','CommentController@AudienceLeave');
Route::post('audience_list','CommentController@AudienceList');


Route::post('check_balance_monthly','GiftController@HostBalanceChack');
//profile
Route::post('profile/live_data','ProfileController@ProfileLiveData');
Route::post('profile/update','ProfileController@ProfileUpdate');

Route::post('co_host_request','LiveCoHostController@JoinCall');
Route::post('co_host_request_list','LiveCoHostController@CallList');
Route::post('co_host_request_accept','LiveCoHostController@CallAccept');
Route::post('co_host_call_remove','LiveCoHostController@CallRemoved');
Route::post('co_host_call_accept_list','LiveCoHostController@CallAcceptList');
Route::post('co_host_call_mute','LiveCoHostController@CallMute');
Route::post('co_host_call_mute_audio_brd_firebase','LiveCoHostController@AudioCallMuteFirebase');

//Audio BRD
Route::post('audio_co_host_request','AudioBrdController@CallRequest');
Route::post('audio_brd_host_list','AudioBrdController@HostList');
Route::post('audio_user_live_store','AudioBrdController@Store');
Route::post('audio_brd_call_request_list','AudioBrdController@CallList');
Route::post('audio_call_accept','AudioBrdController@AudioCallAccept');
Route::post('audio_call_mute','AudioBrdController@CallMute');
Route::post('audio_call_remove','AudioBrdController@CallRemoved');
Route::post('audio_brd_user_data','AudioBrdController@UserData');
Route::post('audio_brd_host_mute','AudioBrdController@HostMue');
Route::post('audio_gift_push','AudioBrdController@AudioGiftPush');
Route::post('audio_kick','AudioBrdController@Kick');
Route::post('audio_host_call_remove','AudioBrdController@HostCallRemove');
Route::post('audio_brd_lock_unlock','AudioBrdController@LockUnlock');
Route::post('audio_brd_pending_call_remove','AudioBrdController@PendingCallRemoved');
Route::post('audio_check_co_host_active_or_inactive_call','AudioBrdController@CohostisActive');
Route::post('audio_sand_emoji','AudioBrdController@SandEmoji');


//Video Brd
Route::post('video_co_host_request','VideoBrdController@CallRequest');
Route::post('video_call_accept','VideoBrdController@VideoCallAccept');
Route::post('video_call_mute','VideoBrdController@CallMute');
Route::post('video_host_call_mute','VideoBrdController@HostMue');
Route::post('video_user_live_store','VideoBrdController@Store');
Route::post('video_call_remove_by_host','VideoBrdController@HostCallRemove');
Route::post('video_call_remove','VideoBrdController@CallRemoved');
Route::post('video_gift_push','VideoBrdController@VideoGiftPush');
Route::post('video_brd_user_data','VideoBrdController@UserData');
Route::post('video_brd_pending_call_removed','VideoBrdController@PendingCallRemoved');
Route::post('video_day_time_request','VideoBrdController@VideoBrdDayTimeRequest');
Route::post('video_kick','VideoBrdController@Kick');

Route::post('check_co_host_active_or_inactive_call','VideoBrdController@CohostisActive');

  
//friend request
Route::post('follow','FollowerController@Follow');
Route::post('un_follow','FollowerController@UnFollow');
Route::post('follow_request_sand','FollowerController@Store');
Route::post('follower_list','FollowerController@FollowerIndex');
Route::post('friend_list','FollowerController@FriendIndex');
Route::post('following_list','FollowerController@FollowingIndex');
  
//search 
  Route::post('search','SearchController@Search');
  //chat
  Route::post('chat_gift','ChatController@Gift');
  Route::post('chat_store','ChatController@Store');
  Route::post('lucky_gift','LuckyGiftController@Store');
//blocked 
Route::post('block','BlockController@Store');
Route::post('block_list','BlockController@Index');
Route::post('unblock','BlockController@UnBlock');
  //user Profile
  Route::post('user_data','UserDataController@Index');
  //Agency/HostControll
Route::post('my-host-list','AgencyController@MyHost');
Route::post('my-host-profile','AgencyController@MyHostProfile');
Route::post('add-host','AgencyController@AddHost');
Route::post('host_verify','AgencyController@HostVerify');

//Help
Route::post('help-store','HelpController@Store');
//slider
Route::post('slider','SliderController@Index');
//Transfer
Route::post('protal_data','PortalController@Index');
Route::post('protal_balance_transfer','PortalController@Transfer');
Route::post('protal_to_protal_transfer','PortalController@ProtalTransfer');
  
Route::post('invisibal_active_inactive','SettingController@ActiveInvisible');
Route::post('offical_brd_off_action','SettingController@LiveOff');
  
Route::post('global_live','GlobalController@Index');
Route::post('global_live_country_wise','GlobalController@CountryWiseData');

Route::post('my_vip','VipController@Index');
Route::post('vip_active','VipController@Active');
Route::post('notification','VipController@Notification');
Route::post('lavel_list','LavelController@Index');

//Invite
Route::post('invite_deshbord','InviteController@Index');
Route::post('invite_reward_withdraw','InviteController@Withdaw');
Route::post('invite_confirm','InviteController@Invite');
Route::post('invite_cancel','InviteController@InviteCancel');

Route::post('host_request_call_audience','HostCallController@CallRequest');
Route::post('host_request_call_accept_audience','HostCallController@CallAccept');
Route::post('audio_host_request_call_audience','HostCallController@AudioCallRequest');
Route::post('audio_host_request_call_accept_audience','HostCallController@AudioCallAccept');

Route::post('host_withdraw_page','WithdrawController@Index');
Route::post('host_withdraw_request','WithdrawController@SuperAgencyWithdraw');
Route::post('agency_withdraw_wallet','WithdrawController@AgencyWallet');
Route::post('agency_withdraw_convart','WithdrawController@Convart');
Route::post('agency_withdraw_approved','WithdrawController@Approved');

// --- QueenLive Moments (TikTok-style short videos) ---------------------------
Route::post('short_video_upload', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Upload']);
Route::post('short_video_feed', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Feed']);
Route::post('short_video_my', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'MyVideos']);
Route::post('short_video_like', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Like']);
Route::post('short_video_view', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'View']);
Route::post('short_video_comments', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Comments']);
Route::post('short_video_comment_add', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'CommentAdd']);
Route::post('short_video_gift', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Gift']);
Route::post('short_video_delete', [\App\Http\Controllers\Api\V4\ShortVideoController::class, 'Delete']);


});

// Restored V5 generated aliases for Flutter clients.
if (file_exists(__DIR__ . '/api_v5_generated.php')) {
    require __DIR__ . '/api_v5_generated.php';
}
