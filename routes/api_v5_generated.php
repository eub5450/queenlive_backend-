<?php

use Illuminate\Support\Facades\Route;

// Generated v5 route aliases for new Flutter clients. v4 remains legacy-safe.
Route::group(['namespace' => 'Api\V5'], function () {
Route::get('v5/vip_packages','VipController@vipPackages');
Route::post('v5/vip_packages','VipController@vipPackages');
Route::post('v5/room_comment_ws','RoomCommentController@Send');


Route::match(['get', 'post'], 'v5/app_error_report', function (\Illuminate\Http\Request $request) {
    try {
        $line = json_encode([
            'ts'          => now()->toIso8601String(),
            'user_id'     => $request->input('user_id'),
            'platform'    => $request->input('platform'),
            'app_version' => $request->input('app_version'),
            'route'       => $request->input('route'),
            'error_type'  => $request->input('error_type'),
            'library'     => $request->input('library'),
            'message'     => mb_substr((string) $request->input('message'), 0, 1000),
            'stack'       => mb_substr((string) $request->input('stack'), 0, 6000),
            'device'      => $request->input('device'),
            'ip'          => $request->ip(),
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents(storage_path('logs/app_errors.log'), $line, FILE_APPEND | LOCK_EX);
    } catch (\Throwable $e) {
    }
    return json_encode([['message' => 'ok', 'code' => '200']], JSON_UNESCAPED_UNICODE);
});

});

Route::group(['namespace' => 'Api\V5', 'middleware' => ['auth:sanctum', 'check.ban']], function () {
Route::get('v5/active_coinbeg','CoinBegController@ActiveCoinBeg');
Route::get('v5/clim_coin_beg','CoinBegController@Claim');
Route::get('v5/logout','AuthController@Logout');
Route::get('v5/login_user','AuthController@UserData');
Route::get('v5/change_password','AuthController@ChangePassword');
Route::get('v5/rank','RankingController@RankList');
Route::get('v5/top_list','RankingController@TopList');
Route::get('v5/generate_pk_live_token','AgoraController@generatePKToken');
Route::get('v5/comment_skip_word_list','CommentSkipController@WordList');
Route::get('v5/user_live_store','UserLiveController@Store');
Route::get('v5/user_live_home','UserLiveController@Index')->middleware('throttle:home_data_limit');
Route::get('v5/user_friend_live','UserLiveController@FriendsLive');
Route::get('v5/comments_socket_io_store','CommentController@Store');
Route::get('v5/entry_realtime','CommentController@CheckEntry');
Route::get('v5/fly_comment','CommentController@FlyComment');
Route::get('v5/join_socket_io_store','CommentController@JoinStore');
Route::post('v5/gift_socket_io_store','CommentController@GiftPush')->middleware('legacy.mutation.no_cache');
Route::post('v5/audio_gift_socket_io_store','CommentController@AudioGiftPush')->middleware('legacy.mutation.no_cache');
Route::get('v5/audience_leave','CommentController@AudienceLeave');
Route::get('v5/audience_list','CommentController@AudienceList');
Route::get('v5/comment_mute_add','CommentController@CommentMute');
Route::get('v5/comment_mute_removed','CommentController@CommentMuteRemove');
Route::get('v5/online_pay','OnlinePaymentController@OnlinePayment');
Route::get('v5/check_balance_monthly','GiftController@HostBalanceChack');
Route::get('v5/gift_file_data','GiftController@GiftData');
Route::get('v5/exchange','GiftController@exchange');
Route::post('v5/exchange-store','GiftController@exchangestore');
Route::get('v5/profile/live_data','ProfileController@ProfileLiveData');
Route::get('v5/live_data','ProfileController@LiveData');
Route::get('v5/profile/update','ProfileController@ProfileUpdate');
Route::get('v5/profile/visit','ProfileController@Visitor');
Route::get('v5/co_host_request','LiveCoHostController@JoinCall')->middleware('legacy.mutation.no_cache');
Route::post('v5/co_host_request','LiveCoHostController@JoinCall')->middleware('legacy.mutation.no_cache');
Route::get('v5/co_host_request_list','LiveCoHostController@CallList');
Route::get('v5/co_host_request_accept','LiveCoHostController@CallAccept');
Route::get('v5/co_host_call_remove','LiveCoHostController@CallRemoved');
Route::get('v5/co_host_call_accept_list','LiveCoHostController@CallAcceptList');
Route::get('v5/co_host_call_mute_audio_brd_firebase','LiveCoHostController@AudioCallMuteFirebase');
Route::get('v5/multi_brd_host_mute','MultiBrdController@HostMue');
Route::get('v5/multi_brd_lock_unlock','MultiBrdController@LockUnlock');
Route::get('v5/multi_brd_pending_call_remove','MultiBrdController@PendingCallRemoved');
Route::get('v5/multi_check_co_host_active_or_inactive_call','MultiBrdController@CohostisActive');
Route::get('v5/multi_camera_status_change','MultiBrdController@CameraStatusChange');
Route::get('v5/host_multi_camera_status_change','MultiBrdController@HostCallCamera');
Route::get('v5/co_host_call_mute','VideoBrdController@CallMute');
Route::get('v5/video_brd_pending_call_removed','VideoBrdController@PendingCallRemoved');
Route::get('v5/follow','FollowerController@Follow')->middleware('legacy.mutation.no_cache');
Route::post('v5/follow','FollowerController@Follow')->middleware('legacy.mutation.no_cache');
Route::get('v5/un_follow','FollowerController@UnFollow')->middleware('legacy.mutation.no_cache');
Route::post('v5/un_follow','FollowerController@UnFollow')->middleware('legacy.mutation.no_cache');
Route::get('v5/follow_request_sand','FollowerController@Store')->middleware('legacy.mutation.no_cache');
Route::post('v5/follow_request_sand','FollowerController@Store')->middleware('legacy.mutation.no_cache');
Route::get('v5/friend_list','FollowerController@FriendIndex');
  Route::get('v5/search','SearchController@Search');
  Route::post('v5/chat_gift','ChatController@Gift')->middleware('legacy.mutation.no_cache');
  Route::get('v5/chat_store','ChatController@Store');
  Route::post('v5/lucky_gift','LuckyGiftController@Store')->middleware('legacy.mutation.no_cache');
Route::get('v5/block','BlockController@Store');
Route::get('v5/block_list','BlockController@Index');
Route::get('v5/unblock','BlockController@UnBlock');
  Route::get('v5/profile_snapshot','UserDataController@ProfileSnapshot');
Route::get('v5/my-host-list','AgencyController@MyHost');
Route::get('v5/my-host-data','AgencyController@MyHostData');
Route::get('v5/my-host-profile','AgencyController@MyHostProfile');
Route::get('v5/add-host','AgencyController@AddHost');
Route::get('v5/host_verify','AgencyController@HostVerify');
Route::get('v5/help-store','HelpController@Store');
Route::get('v5/slider','SliderController@Index');
Route::get('v5/protal_data','PortalController@Index');
Route::get('v5/protal_balance_transfer','PortalController@Transfer')->middleware('throttle:home_data_limit');
Route::get('v5/protal_to_protal_transfer','PortalController@ProtalTransfer')->middleware('throttle:home_data_limit');
Route::get('v5/invisibal_active_inactive','SettingController@ActiveInvisible');
Route::get('v5/offical_brd_off_action','SettingController@LiveOff');
Route::get('v5/my_vip','VipController@Index');
Route::get('v5/vip_active','VipController@Active')->middleware('legacy.mutation.no_cache');
Route::post('v5/vip_active','VipController@Active')->middleware('legacy.mutation.no_cache');
Route::get('v5/vip_active_inactive','VipController@VIPActive');
Route::get('v5/buy_entry','VipController@BuyEntry');
Route::get('v5/store_items','VipController@EntryFrame');
Route::get('v5/vip_buy-store_items','VipController@VipIdBuy');
Route::get('v5/store_items_active_inactive','VipController@EntryFrameActiveInactive');
Route::get('v5/lavel_list','LavelController@Index');
Route::get('v5/invite_deshbord','InviteController@Index');
Route::get('v5/invite_reward_withdraw','InviteController@Withdaw');
Route::get('v5/invite_confirm','InviteController@Invite')->middleware('legacy.mutation.no_cache');
Route::post('v5/invite_confirm','InviteController@Invite')->middleware('legacy.mutation.no_cache');
Route::get('v5/invite_cancel','InviteController@InviteCancel');
Route::get('v5/host_request_call_audience','HostCallController@CallRequest');
Route::get('v5/host_request_call_accept_audience','HostCallController@CallAccept');
Route::get('v5/audio_host_request_call_audience','HostCallController@AudioCallRequest');
Route::get('v5/audio_host_request_call_accept_audience','HostCallController@AudioCallAccept');
Route::get('v5/host_withdraw_page','WithdrawController@Index');
Route::get('v5/host_withdraw_request','WithdrawController@SuperAgencyWithdraw')->middleware('legacy.mutation.no_cache');
Route::post('v5/host_withdraw_request','WithdrawController@SuperAgencyWithdraw')->middleware('legacy.mutation.no_cache');
Route::get('v5/agency_withdraw_wallet','WithdrawController@AgencyWallet');
Route::get('v5/agency_withdraw_convart','WithdrawController@Convart');
Route::get('v5/agency_withdraw_approved','WithdrawController@Approved');
Route::get('v5/add_brd_admin','BrdAdminController@Store');
Route::get('v5/removed_brd_admin','BrdAdminController@Remove');
Route::get('v5/online_coin_product','OnlineCoinPurchaseController@GetCoinList');
Route::get('v5/video_privat_call','CallController@VideoCall');
Route::get('v5/audio_privat_call','CallController@AudioCall');
Route::get('v5/random_live_data','HomeController@HomeIndex')->middleware('throttle:home_data_limit');
Route::get('v5/home_live_now','HomeController@LivesNowIndex')->middleware('throttle:home_data_limit');
Route::get('v5/pk_user_list','PkController@PkUserList')->middleware('throttle:home_data_limit');
Route::get('v5/pk_play_request','PkController@PkPlayRequest')->middleware('throttle:home_data_limit');
Route::get('v5/pk_player_search_profile','PkController@PkPlayerSearchProfile')->middleware('throttle:home_data_limit');
Route::get('v5/pk_request_accept','PkController@PkRequestAccept')->middleware('throttle:home_data_limit');


Route::post('v5/short_video_upload', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Upload']);
Route::post('v5/short_video_feed', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Feed']);
Route::post('v5/short_video_my', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'MyVideos']);
Route::post('v5/short_video_like', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Like']);
Route::post('v5/short_video_view', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'View']);
Route::post('v5/short_video_comments', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Comments']);
Route::post('v5/short_video_comment_add', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'CommentAdd']);
Route::post('v5/short_video_gift', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Gift']);
Route::post('v5/short_video_delete', [\App\Http\Controllers\Api\V5\ShortVideoController::class, 'Delete']);

Route::post('v5/host_agency_contact', function (\Illuminate\Http\Request $request) {
    $hostId = (int) $request->input('host_id', 0);
    if ($hostId <= 0) {
        return response()->json([['message' => 'host_id required', 'code' => '400']]);
    }
    $row = \DB::table('host_data as hd')
        ->leftJoin('agencies as a', 'a.code', '=', 'hd.agency_code')
        ->where('hd.user_id', (string) $hostId)
        ->select('a.name as agency_name', 'a.phone as agency_phone')
        ->first();
    return response()->json([[
        'message' => 'OK',
        'code' => '200',
        'agency_name' => $row->agency_name ?? '',
        'agency_phone' => $row->agency_phone ?? '',
    ]]);
});

// The canonical protected V5 room dispatcher is registered in routes/api.php.
// Do not register a second generated catch-all here; it creates route collisions
// and can shadow future room-specific routes.
Route::get('v5/meta/bootstrap','MetaBootstrapController@bootstrap');
});

// --- Boss 2026-07-03: in-app admin/official "Banned" management (V5). Actor
// authority (is_admin / is_bd_admin / is_official_id) is enforced in the
// controller on every action.
Route::group(['namespace' => 'Api\V5'], function () {
    Route::get('v5/admin/banned_list',  'AdminBanController@bannedList');
    Route::post('v5/admin/banned_list', 'AdminBanController@bannedList');
    Route::get('v5/admin/user_search',  'AdminBanController@userSearch');
    Route::post('v5/admin/user_search', 'AdminBanController@userSearch');
    Route::post('v5/admin/ban_user',    'AdminBanController@banUser');
    Route::post('v5/admin/unban_user',  'AdminBanController@unbanUser');
});

Route::group(['namespace' => 'Api\\V5'], function () {
});

Route::group(['namespace' => 'Api\V5'], function () {
    Route::get('v5/host_heartbeat','HostHeartbeatController@Beat');
    Route::post('v5/host_heartbeat','HostHeartbeatController@Beat');
});

Route::group(['namespace' => 'Api\V5'], function () {
    Route::post('v5/report_adult_upload','AdultUploadReportController@Report')->middleware('legacy.mutation.no_cache');
    Route::get('v5/report_adult_upload','AdultUploadReportController@Report')->middleware('legacy.mutation.no_cache');
});


Route::group(['namespace' => 'Api\V5'], function () {
    Route::get('v5/admin/profile_index',  'AdminProfileController@index');
    Route::post('v5/admin/profile_index', 'AdminProfileController@index');
});

Route::group(['namespace' => 'Api\V5'], function () {
    Route::match(['get', 'post'], 'v5/admin/system_setting', 'AdminSystemSettingController@index');
    Route::post('v5/admin/system_setting/reward_setup', 'AdminSystemSettingController@updateRewardSetup');
    Route::post('v5/admin/system_setting/portal_setup', 'AdminSystemSettingController@updatePortalSetup');
    Route::post('v5/admin/system_setting/recall_setup', 'AdminSystemSettingController@updateRecallSetup');
    Route::post('v5/admin/system_setting/withdraw_setup', 'AdminSystemSettingController@updateWithdrawSetup');
    Route::post('v5/admin/system_setting/frame_rule/store', 'AdminSystemSettingController@storeFrameRule');
    Route::post('v5/admin/system_setting/frame_rule/toggle/{id}', 'AdminSystemSettingController@toggleFrameRule');
    Route::post('v5/admin/system_setting/frame_rule/delete/{id}', 'AdminSystemSettingController@deleteFrameRule');
    Route::post('v5/admin/system_setting/frame_rule/sync', 'AdminSystemSettingController@syncFrameRules');
    Route::post('v5/admin/system_setting_reward_setup', 'AdminSystemSettingController@updateRewardSetup');
    Route::post('v5/admin/system_setting_portal_setup', 'AdminSystemSettingController@updatePortalSetup');
    Route::post('v5/admin/system_setting_recall_setup', 'AdminSystemSettingController@updateRecallSetup');
    Route::post('v5/admin/system_setting_withdraw_setup', 'AdminSystemSettingController@updateWithdrawSetup');
    Route::post('v5/admin/system_setting_scheduled_frame_store', 'AdminSystemSettingController@storeFrameRule');
    Route::post('v5/admin/system_setting_scheduled_frame_toggle/{id?}', 'AdminSystemSettingController@toggleFrameRule');
    Route::post('v5/admin/system_setting_scheduled_frame_delete/{id?}', 'AdminSystemSettingController@deleteFrameRule');
    Route::post('v5/admin/system_setting_scheduled_frame_sync', 'AdminSystemSettingController@syncFrameRules');

    Route::match(['get', 'post'], 'v5/admin/gift_data', 'AdminGiftDataController@index');
    Route::match(['get', 'post'], 'v5/admin/audio_brd_background', 'AdminGiftDataController@audioBackgroundIndex');
    Route::post('v5/admin/gift_data/store', 'AdminGiftDataController@store');
    Route::post('v5/admin/gift_data/update/{id?}', 'AdminGiftDataController@update');
    Route::post('v5/admin/gift_data/delete/{id?}', 'AdminGiftDataController@delete');
    Route::match(['post', 'put'], 'v5/admin/audio_brd_background/update/{id}', 'AdminGiftDataController@audioBackgroundUpdate');

    Route::match(['get', 'post'], 'v5/admin/support', 'AdminSupportController@index');
    Route::post('v5/admin/support/reply/{id?}', 'AdminSupportController@reply');

    Route::match(['get', 'post'], 'v5/admin/user_tools', 'AdminUserToolsController@index');
    Route::match(['get', 'post'], 'v5/admin/user_lookup', 'AdminUserToolsController@lookup');
    Route::post('v5/admin/country_admin/store', 'AdminUserToolsController@countryAdminStore');
    Route::post('v5/admin/email_change', 'AdminUserToolsController@emailChange');
    Route::post('v5/admin/email_change/store', 'AdminUserToolsController@emailChangeStore');
    Route::post('v5/admin/email_change/new_id', 'AdminUserToolsController@newIdGive');
});
