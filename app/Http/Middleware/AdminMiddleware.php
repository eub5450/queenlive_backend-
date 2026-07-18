<?php

namespace App\Http\Middleware;

use App\Models\AdminParmisiton;
use Auth;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userId = (int) $user->id;
        $adminLevel = (int) ($user->is_admin ?? 0);

        if ($adminLevel !== 1 && !AdminParmisiton::allowed($userId, 'sidebar_access', false)) {
            return redirect()->route('login');
        }

        $requiredPermissions = $this->requiredPermissions($request);
        foreach ($requiredPermissions as $permissionKey) {
            if (!AdminParmisiton::allowed($userId, $permissionKey, false)) {
                abort(403, 'This admin page is not allowed for this account.');
            }
        }

        return $next($request);
    }

    /**
     * Keep direct URL access aligned with the permissions used by the sidebar and pages.
     */
    private function requiredPermissions(Request $request): array
    {
        $path = trim($request->path(), '/');

        $exact = [
            'dashboard' => ['sidebar_menu_dashboard', 'dashboard_access'],
            'comment_data' => ['dashboard_realtime_feeds'],
            'chat/new' => ['dashboard_realtime_feeds'],
            'comment/new' => ['dashboard_realtime_feeds'],
            'chat_data' => ['dashboard_realtime_feeds'],
            'weekly_new_user' => ['dashboard_access'],
            'version_update' => ['dashboard_version_update'],
            'vip_offer' => ['dashboard_vip_offer'],
            'recharhge_offer' => ['dashboard_access'],
            'withdraw_active' => ['dashboard_access'],
            'whithdaw_system_change' => ['dashboard_access'],
            'fruits_id_lock' => ['dashboard_game_data'],
            'realtime_vulter_server_reboot' => ['dashboard_access'],

            'add-host' => ['sidebar_menu_host', 'sidebar_host_add'],
            'host-store' => ['sidebar_menu_host', 'sidebar_host_add'],
            'active_host' => ['sidebar_menu_host', 'sidebar_host_active'],
            'pending_host' => ['sidebar_menu_host', 'sidebar_host_pending'],
            'transfer_host' => ['sidebar_menu_host', 'sidebar_host_transfer'],
            'host-agency-transfer' => ['sidebar_menu_host', 'sidebar_host_transfer'],
            'lucky_star_pending' => ['sidebar_menu_host', 'sidebar_host_pending'],
            'lucky_star_active' => ['sidebar_menu_host', 'sidebar_host_active'],

            'agency_create' => ['sidebar_menu_agency', 'sidebar_agency_create'],
            'agency_store' => ['sidebar_menu_agency', 'sidebar_agency_create'],
            'agency_list' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'master-agency_list' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'admin/child_agency_store' => ['sidebar_menu_agency', 'sidebar_agency_list'],

            'protal_create' => ['sidebar_menu_protal', 'sidebar_protal_create'],
            'protal_active' => ['sidebar_menu_protal', 'sidebar_protal_create'],
            'protal_list' => ['sidebar_menu_protal', 'sidebar_protal_list'],
            'master_recharge' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'master_recharge_store' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'recharge_otp' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'check_recharge_otp' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'recharge' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'protal_recharge_store' => ['sidebar_menu_protal', 'sidebar_protal_recharge'],
            'recharge-list' => ['sidebar_menu_protal', 'sidebar_protal_recharge_list'],
            'recall' => ['sidebar_menu_protal', 'sidebar_protal_new_recall'],
            'protal_recall' => ['sidebar_menu_protal', 'sidebar_protal_new_recall'],
            'protal_recall_submit' => ['sidebar_menu_protal', 'sidebar_protal_new_recall'],
            'recall-list' => ['sidebar_menu_protal', 'sidebar_protal_recall_list'],

            'support' => ['sidebar_menu_support', 'sidebar_support_index'],
            'rankingList' => ['sidebar_menu_ranking', 'sidebar_ranking_list'],
            'user_have_balance' => ['sidebar_menu_user_balance', 'sidebar_user_balance_wallet'],
            'admin/withdraw' => ['sidebar_menu_user_balance', 'sidebar_user_balance_wallet'],

            'ban_id' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'banned_store' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'users/search' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'live-list' => ['sidebar_menu_live', 'sidebar_live_list'],

            'admin-slider' => ['sidebar_menu_setting', 'sidebar_setting_banner'],
            'admin/slider-store' => ['sidebar_menu_setting', 'sidebar_setting_banner'],
            'admin-country' => ['sidebar_menu_setting', 'sidebar_setting_country'],
            'admin-country-store' => ['sidebar_menu_setting', 'sidebar_setting_country'],
            'admin-store' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'effect_store' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'admin-lucky_id' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'admin-lucky_id_store' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'admin-agora_account_setting' => ['sidebar_menu_setting', 'sidebar_setting_agora'],
            'admin-agora_account_store' => ['sidebar_menu_setting', 'sidebar_setting_agora'],
            'admin-exchange-cut-setting' => ['sidebar_menu_setting', 'sidebar_setting_agora'],
            'admin-user-emailchange' => ['sidebar_menu_setting', 'sidebar_setting_email_change'],
            'admin-user-email-change_store' => ['sidebar_menu_setting', 'sidebar_setting_email_change'],
            'admin-user-new-email-change_store' => ['sidebar_menu_setting', 'sidebar_setting_email_change'],
            'admin-gift-data' => ['sidebar_menu_setting', 'sidebar_setting_gift_data'],
            'admin-gift-data-store' => ['sidebar_menu_setting', 'sidebar_setting_gift_data'],
            'admin-audio-brd-background' => ['sidebar_menu_setting', 'sidebar_setting_audio_background'],
            'admin-system-setting' => ['sidebar_menu_setting'],
            'setting/admin' => ['sidebar_menu_setting', 'sidebar_setting_admin', 'setting_admin_manage'],
            'setting/admin-update' => ['sidebar_menu_setting', 'sidebar_setting_admin', 'setting_admin_manage'],
            'setting/country-admin-store' => ['sidebar_menu_setting', 'sidebar_setting_admin', 'setting_admin_manage'],

            'id_search' => ['profile_search'],
            'profile_pending' => ['profile_vip_frames_edit'],
            'game_balance_block' => ['dashboard_game_pro_balance_manage'],
            'lucky_game_balance_block' => ['dashboard_game_data'],
            'five_game_balance_block' => ['dashboard_game_data'],
            'teenpatti_game_balance_block' => ['dashboard_game_data'],
            'greedy_game_balance_block' => ['dashboard_game_data'],
            'greedy_game_third_balance_block' => ['dashboard_game_data'],
            'greedy_game_sec_balance_block' => ['dashboard_game_data'],
            'teenpatti_game_sec_balance_block' => ['dashboard_game_data'],
            'teen_patti_game_third_balance_block' => ['dashboard_game_data'],
            'fruits_game_sec_balance_block' => ['dashboard_game_data'],
            'fruits_game_third_balance_block' => ['dashboard_game_data'],

            // --- role-system hardening: previously-unmapped admin routes ---
            'admin/fanclub-settings' => ['sidebar_menu_setting', 'sidebar_setting_fanclub'],
            'admin/fanclub-tier-save' => ['sidebar_menu_setting', 'sidebar_setting_fanclub'],
            'admin/combo-settings' => ['sidebar_menu_setting', 'sidebar_setting_combo'],
            'admin/combo-settings-save' => ['sidebar_menu_setting', 'sidebar_setting_combo'],
            'admin/checkin-settings' => ['sidebar_menu_setting', 'sidebar_setting_checkin'],
            'admin/checkin-reward-save' => ['sidebar_menu_setting', 'sidebar_setting_checkin'],
            'admin/fun-sticker' => ['sidebar_menu_setting', 'sidebar_setting_fun_sticker'],
            'admin/setting/fun-sticker' => ['sidebar_menu_setting', 'sidebar_setting_fun_sticker'],
            'admin/fun-sticker-save' => ['sidebar_menu_setting', 'sidebar_setting_fun_sticker'],
            'invisibal' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'official_id' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'official_id_active' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'admin/game_pattern_reverse' => ['sidebar_menu_game_control'],
            'admin/game_minus_status' => ['sidebar_menu_game_control'],
            'setting/admin-delete' => ['sidebar_menu_setting', 'sidebar_setting_admin', 'setting_admin_manage'],
            'admin-user-role-store' => ['profile_power_buttons'],
            'admin-user-permission-store' => ['profile_power_buttons'],
        ];

        if (isset($exact[$path])) {
            return $exact[$path];
        }

        $prefixes = [
            'host/view/' => ['sidebar_menu_host', 'sidebar_host_active'],
            'active_host/' => ['sidebar_menu_host', 'sidebar_host_active'],
            'reject_host/' => ['sidebar_menu_host', 'sidebar_host_pending'],
            'lucky_star_actived/' => ['sidebar_menu_host', 'sidebar_host_active'],
            'lucky_star_rejected/' => ['sidebar_menu_host', 'sidebar_host_pending'],
            'get/host_agency_info/' => ['sidebar_menu_host', 'sidebar_host_transfer'],

            'agency_update/' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'admin-agency-' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'get/user_info/' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'admin-master-agency-view/' => ['sidebar_menu_agency', 'sidebar_agency_list'],
            'remove_as_child_agency/' => ['sidebar_menu_agency', 'sidebar_agency_list'],

            'get/user_recall_info/' => ['sidebar_menu_protal', 'sidebar_protal_recall_list'],

            'ban_id_reject/' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'admin-brd-off/' => ['sidebar_menu_live', 'sidebar_live_list'],

            'admin-slider-removed/' => ['sidebar_menu_setting', 'sidebar_setting_banner'],
            'effect_update/' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'admin-lucky_id_removed/' => ['sidebar_menu_setting', 'sidebar_setting_store'],
            'admin-agora_account_active/' => ['sidebar_menu_setting', 'sidebar_setting_agora'],
            'admin-agora_account_pre_active/' => ['sidebar_menu_setting', 'sidebar_setting_agora'],
            'gift_data_delete/' => ['sidebar_menu_setting', 'sidebar_setting_gift_data'],
            'update_gift_data/' => ['sidebar_menu_setting', 'sidebar_setting_gift_data'],
            'admin-audio-brd-background-update/' => ['sidebar_menu_setting', 'sidebar_setting_audio_background'],
            'admin-system-setting/' => ['sidebar_menu_setting'],

            'user_profile_update/' => ['profile_vip_frames_edit'],
            'vips_remove/' => ['profile_vip_frames_edit'],
            'withdraw_active/' => ['profile_power_buttons'],
            'agora_access/' => ['profile_power_buttons'],
            'admin/user-role/' => ['profile_power_buttons'],
            'hosting_type_change/' => ['profile_power_buttons'],
            'brd_off_power_' => ['profile_power_buttons'],
            'invisibal_' => ['profile_power_buttons'],
            'sceenshort_' => ['profile_power_buttons'],
            'active_official_id/' => ['profile_power_buttons'],
            'reject_official_id/' => ['profile_power_buttons'],
            'kick_power_' => ['profile_power_buttons'],
            'comment_mute_power_' => ['profile_power_buttons'],
            'active_special_' => ['profile_vip_frames_edit'],
            'inactive_special_' => ['profile_vip_frames_edit'],
            'user_day_time_add/' => ['profile_password_daytime'],
            'password_change_user/' => ['profile_password_daytime'],
            'active_vip_manual/' => ['profile_vip_frames_edit'],
            'active_effect_manual/' => ['profile_vip_frames_edit'],
            'profile_approved/' => ['profile_vip_frames_edit'],
            'profile_reject/' => ['profile_vip_frames_edit'],
            'active_protal/' => ['profile_power_buttons'],
            'reject_protal/' => ['profile_power_buttons'],
            'admin/top-position/' => ['profile_power_buttons'],
            'admin/check_password' => ['profile_password_daytime'],

            'admin/thomas-game-control' => ['sidebar_menu_game_control', 'sidebar_game_teenpatti_detail'],
            'admin/thomas-lobby' => ['sidebar_menu_game_control', 'sidebar_game_teenpatti_detail'],
            'admin/teen-patti' => ['sidebar_menu_game_control', 'sidebar_game_teenpatti_detail'],
            'admin/five' => ['sidebar_menu_game_control', 'sidebar_game_greedy_detail'],
            'admin/grady' => ['sidebar_menu_game_control', 'sidebar_game_greedy_detail'],
            'admin/greedy' => ['sidebar_menu_game_control', 'sidebar_game_greedy_detail'],
            'admin/gready' => ['sidebar_menu_game_control', 'sidebar_game_greedy_detail'],
            'admin/fruts-game-detail' => ['sidebar_menu_game_control', 'sidebar_game_fruits_detail'],
            'admin/fruits_game_' => ['sidebar_menu_game_control', 'sidebar_game_fruits_detail'],
            'admin/friuts_fetch_data_ajax' => ['sidebar_menu_game_control', 'sidebar_game_fruits_detail'],
            'admin/fruts-game-lock' => ['sidebar_menu_game_control', 'sidebar_game_fruits_lock'],
            'admin/fruts-game-pattarn' => ['sidebar_menu_game_control', 'sidebar_game_fruits_pattern'],
            'fruits_third_setting' => ['sidebar_menu_game_control', 'sidebar_game_fruits_detail'],
            'greedy_setting' => ['sidebar_menu_game_control', 'sidebar_game_greedy_detail'],
            'teen_patti_id_lock' => ['sidebar_menu_game_control', 'sidebar_game_teenpatti_detail'],

            // --- role-system hardening: previously-unmapped admin route prefixes ---
            'admin/level-setting' => ['sidebar_menu_setting', 'sidebar_setting_level'],
            'invisible_id_reject/' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'official_id_reject/' => ['sidebar_menu_ban', 'sidebar_ban_id'],
            'support_replay/' => ['sidebar_menu_support', 'sidebar_support_index'],
            'id_device_banned/' => ['sidebar_menu_ban', 'sidebar_ban_id'],
        ];

        foreach ($prefixes as $prefix => $permissions) {
            if ($this->pathStartsWith($path, $prefix)) {
                return $permissions;
            }
        }

        return [];
    }

    private function pathStartsWith(string $path, string $prefix): bool
    {
        return $path === rtrim($prefix, '/') || strpos($path, $prefix) === 0;
    }
}
