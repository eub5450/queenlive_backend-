<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminParmisiton;
use App\Models\User;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    private function ensureCanManage(): void
    {
        if (!AdminParmisiton::allowed(Auth::id(), 'setting_admin_manage', false)) {
            abort(403, 'Admin permission management is not allowed for this account.');
        }
    }

    private static function groups()
    {
        return [
            'Sidebar Base' => ['sidebar_access' => 'Open Admin Sidebar / Panel', 'sidebar_menu_dashboard' => 'Dashboard Menu'],
            'Sidebar Host' => ['sidebar_menu_host' => 'Host Parent Menu', 'sidebar_host_add' => 'Add Host', 'sidebar_host_active' => 'Active Host', 'sidebar_host_pending' => 'Pending Host', 'sidebar_host_transfer' => 'Transfer Host'],
            'Sidebar Agency' => ['sidebar_menu_agency' => 'Agency Parent Menu', 'sidebar_agency_create' => 'Create Agency', 'sidebar_agency_list' => 'Agency List'],
            'Sidebar Protal' => ['sidebar_menu_protal' => 'Protal Parent Menu', 'sidebar_protal_create' => 'Create Protal', 'sidebar_protal_list' => 'Protal List', 'sidebar_protal_recall_create' => 'Protal Recall Create', 'sidebar_protal_recall_history' => 'Protal Recall History', 'sidebar_protal_recharge' => 'Recharge', 'sidebar_protal_recharge_list' => 'Recharge List', 'sidebar_protal_new_recall' => 'New ReCall', 'sidebar_protal_recall_list' => 'ReCall List'],
            'Sidebar Support' => ['sidebar_menu_support' => 'Support Parent Menu', 'sidebar_support_index' => 'Support'],
            'Sidebar Ranking' => ['sidebar_menu_ranking' => 'Ranking Parent Menu', 'sidebar_ranking_list' => 'Ranking'],
            'Sidebar Finance' => ['sidebar_menu_user_balance' => 'User Balance Parent Menu', 'sidebar_user_balance_wallet' => 'Wallet'],
            'Sidebar Moderation' => ['sidebar_menu_ban' => 'Ban ID Parent Menu', 'sidebar_ban_id' => 'Ban', 'sidebar_invisible_id' => 'Invisible ID Menu', 'sidebar_official_id' => 'Official ID Menu', 'sidebar_withdraw' => 'Withdraw Section', 'sidebar_menu_live' => 'Live Parent Menu', 'sidebar_live_list' => 'Live List'],
            'Sidebar Game Control' => ['sidebar_menu_game_control' => 'Game Control Parent Menu', 'sidebar_game_fruits_detail' => 'Fruits Game', 'sidebar_game_fruits_lock' => 'Fruits Lock', 'sidebar_game_teenpatti_detail' => 'Teenpati Game', 'sidebar_game_greedy_detail' => 'Greedy Game', 'sidebar_game_fruits_pattern' => 'Fruits Pattan'],
            'Sidebar Setting' => ['sidebar_menu_setting' => 'Setting Parent Menu', 'sidebar_setting_banner' => 'Banner', 'sidebar_setting_country' => 'Country', 'sidebar_setting_gift_data' => 'Gift Data', 'sidebar_setting_agora' => 'Agora Setting', 'sidebar_setting_email_change' => 'Email Change', 'sidebar_setting_admin' => 'Admin', 'sidebar_setting_audio_background' => 'Audio Brd Background', 'sidebar_setting_store' => 'Store', 'sidebar_setting_fanclub' => 'Fan Club / Guardian', 'sidebar_setting_combo' => 'Gift Combo', 'sidebar_setting_checkin' => 'Daily Check-in', 'sidebar_setting_level' => 'Level Setting', 'sidebar_setting_fun_sticker' => 'Fun Sticker', 'sidebar_setting_system' => 'System Setting'],
            'Dashboard' => ['dashboard_access' => 'Open Dashboard', 'dashboard_vip_offer' => 'VIP Offer Button', 'dashboard_version_update' => 'Android Version Update Button', 'dashboard_profit_loss' => 'Profit / Loss Card', 'dashboard_total_serve_coin' => 'Total Serve Coin Card', 'dashboard_coin_generate_game' => 'Coin Generate Card', 'dashboard_game_data' => 'Game Data Cards', 'dashboard_realtime_feeds' => 'Comment / Chat Feed', 'dashboard_game_pro_balance' => 'Game Pro Balance Card', 'dashboard_game_pro_balance_manage' => 'Game Pro Deposit / Withdraw', 'dashboard_total_users' => 'Total Users Card', 'dashboard_user_wallets' => 'User Wallets Card', 'dashboard_game_profit' => 'Game Profit Card', 'dashboard_today_game_profit' => 'Today Game Profit Panel', 'dashboard_thomas_settlements' => 'Thomas Settlements Panel', 'dashboard_today_recharge' => 'Today Recharge Card', 'dashboard_today_sending' => 'Today Sending Card', 'dashboard_today_receiving' => 'Today Receiving Card', 'dashboard_today_gift' => 'Today Gift Sum Card', 'dashboard_withdraw_commission' => 'Withdraw Commission Card', 'dashboard_users_agents' => 'Users & Agents Card', 'dashboard_coin_metrics' => 'Coin Metrics Card', 'dashboard_today_transactions' => 'Today Transactions Card', 'dashboard_withdraw_profit' => 'Withdraw Profit Card', 'dashboard_portal_balance' => 'Portal Balance Card', 'dashboard_portal_send' => 'Portal Send Card', 'dashboard_total_receiving' => 'Total Receiving Card'],
            'Profile' => ['profile_search' => 'Profile Search', 'profile_balance' => 'Profile Balance', 'profile_email_info' => 'Profile Email', 'profile_phone_info' => 'Profile Phone', 'profile_nid' => 'Profile NID + Documents', 'profile_vip_info' => 'Profile VIP Info', 'profile_entry_frame' => 'Profile Entry Frame', 'profile_other_ids' => 'Other IDs From Same IMEI', 'profile_power_buttons' => 'Power Buttons', 'profile_vip_frames_edit' => 'VIP / Entry / Special Frame / Profile Edit', 'profile_password_daytime' => 'Change Password / Add Day Time', 'profile_sensitive_info' => 'Sensitive/PII Info Badge'],
            'Profile Fields' => ['profile_f_level' => 'Field: Level', 'profile_f_entry' => 'Field: Entry', 'profile_f_join_date' => 'Field: Join Date', 'profile_f_country' => 'Field: Country', 'profile_f_join_agency_name' => 'Field: Agency Name', 'profile_f_code' => 'Field: Code', 'profile_f_agency_phone' => 'Field: Agency Phone', 'profile_f_recharge' => 'Field: Recharge', 'profile_f_transfer' => 'Field: Transfer', 'profile_f_recall' => 'Field: Recall', 'profile_f_portal_transfer_send' => 'Field: Portal Transfer Send', 'profile_f_portal_transfer_received' => 'Field: Portal Transfer Received', 'profile_f_name' => 'Field: Name', 'profile_f_withdraw_commission' => 'Field: Withdraw Commission', 'profile_f_convert' => 'Field: Convert', 'profile_f_available' => 'Field: Available', 'profile_f_hosting_type' => 'Field: Hosting Type', 'profile_f_day' => 'Field: Day', 'profile_f_time' => 'Field: Time', 'profile_f_point_collect' => 'Field: Point Collect', 'profile_f_total_withdraw' => 'Field: Total Withdraw', 'profile_f_previous_points' => 'Field: Previous Points', 'profile_f_now_points_have' => 'Field: Now Points Have', 'profile_f_vip_activation' => 'Field: VIP Activation', 'profile_f_special_frame' => 'Field: Special Frame'],
            'Profile Tables' => [
                'profile_table_portal_history'   => 'Table: Portal Recharge/Transfer History',
                'profile_table_host_data'        => 'Table: Host Data',
                'profile_table_game_history'     => 'Table: Game History',
                'profile_table_daytime_history'  => 'Table: Day Time History',
                'profile_table_portal_transfer'  => 'Table: Portal Transfer Sent/Received',
                'profile_table_convert_history'  => 'Table: Convert History',
                'profile_table_recharge_history' => 'Table: Recharge History',
                'profile_table_gift_history'     => 'Table: Gift Sending/Receiving History',
            ],
            'Profile Buttons' => ['profile_btn_user_role' => 'Button: Change Admin Role', 'profile_btn_top_position' => 'Button: Top Position On/Off', 'profile_btn_active_host' => 'Button: Activate Host', 'profile_btn_reject_host' => 'Button: Reject Host', 'profile_btn_active_protal' => 'Button: Activate Protal', 'profile_btn_reject_protal' => 'Button: Reject Protal', 'profile_btn_active_official' => 'Button: Activate Official ID', 'profile_btn_reject_official' => 'Button: Reject Official ID', 'profile_btn_gift_recall' => 'Button: Gift Recall', 'profile_btn_withdraw_active' => 'Button: Withdraw Active'],
            'Admin Setting' => ['setting_admin_manage' => 'Manage Admin Permissions'],
        ];
    }

    private static function keys()
    {
        $keys = [];
        foreach (self::groups() as $items) {
            $keys = array_merge($keys, array_keys($items));
        }

        return array_values(array_unique($keys));
    }

    private static function adminPreset()
    {
        return self::keys();
    }

    private static function subadminPreset()
    {
        return ['sidebar_access', 'sidebar_menu_dashboard', 'sidebar_menu_host', 'sidebar_host_add', 'sidebar_host_active', 'sidebar_host_pending', 'sidebar_host_transfer', 'sidebar_menu_agency', 'sidebar_agency_create', 'sidebar_agency_list', 'sidebar_menu_protal', 'sidebar_protal_create', 'sidebar_protal_list', 'sidebar_protal_recall_create', 'sidebar_protal_recall_history', 'sidebar_protal_recharge', 'sidebar_protal_recharge_list', 'sidebar_protal_new_recall', 'sidebar_protal_recall_list', 'sidebar_menu_support', 'sidebar_support_index', 'sidebar_menu_ranking', 'sidebar_ranking_list', 'sidebar_menu_ban', 'sidebar_ban_id', 'sidebar_invisible_id', 'sidebar_official_id', 'sidebar_menu_live', 'sidebar_live_list', 'sidebar_menu_setting', 'sidebar_setting_banner', 'sidebar_setting_store', 'dashboard_access', 'dashboard_game_pro_balance', 'dashboard_today_game_profit', 'dashboard_thomas_settlements', 'profile_search', 'profile_balance', 'profile_power_buttons', 'profile_vip_frames_edit', 'profile_table_portal_history', 'profile_table_host_data', 'profile_table_game_history', 'profile_table_daytime_history', 'profile_table_portal_transfer', 'profile_table_convert_history', 'profile_table_recharge_history', 'profile_table_gift_history'];
    }

    private static function countryAdminPreset(): array
    {
        return [
            'sidebar_access', 'sidebar_menu_dashboard',
            'sidebar_menu_host', 'sidebar_host_add', 'sidebar_host_active', 'sidebar_host_pending', 'sidebar_host_transfer',
            'sidebar_menu_agency', 'sidebar_agency_list',
            'sidebar_menu_protal', 'sidebar_protal_create', 'sidebar_protal_list',
            'sidebar_protal_recharge', 'sidebar_protal_recharge_list',
            'sidebar_protal_new_recall', 'sidebar_protal_recall_list',
            'sidebar_menu_support', 'sidebar_support_index',
            'sidebar_menu_ranking', 'sidebar_ranking_list',
            'sidebar_menu_user_balance', 'sidebar_user_balance_wallet',
            'sidebar_menu_ban', 'sidebar_ban_id', 'sidebar_invisible_id', 'sidebar_official_id',
            'sidebar_menu_live', 'sidebar_live_list',
            'dashboard_access',
            'profile_search', 'profile_balance', 'profile_email_info', 'profile_phone_info',
            'profile_f_level', 'profile_f_join_date', 'profile_f_country',
            'profile_f_hosting_type', 'profile_f_day', 'profile_f_time',
            'profile_power_buttons', 'profile_vip_frames_edit',
            'profile_table_host_data', 'profile_table_daytime_history',
            'profile_btn_active_host', 'profile_btn_reject_host',
            'profile_btn_active_official', 'profile_btn_reject_official',
            'profile_sensitive_info',
        ];
    }

    private static function countries()
    {
        return DB::table('countries')->select('id', 'name')->orderBy('id')->get();
    }

    private static function validCountryId($countryId)
    {
        return DB::table('countries')->where('id', (int) $countryId)->exists();
    }

    public function index(Request $request)
    {
        $this->ensureCanManage();

        $rows = AdminParmisiton::query()->get()->keyBy('user_id');
        $ids = $rows->keys()->all();
        $q = trim((string) $request->get('q'));

        $users = User::query()
            ->where(function ($query) use ($ids) {
                $query->whereIn('is_admin', [1, 2, 3]);
                if ($ids) {
                    $query->orWhereIn('id', $ids);
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('id', $q)
                        ->orWhere('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderByRaw('FIELD(is_admin,1,2,3,0)')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return view('backend.setting.admin', [
            'users' => $users,
            'permissionRows' => $rows,
            'permissionGroups' => self::groups(),
            'adminPreset' => self::adminPreset(),
            'subadminPreset' => self::subadminPreset(),
            'countryAdminPreset' => self::countryAdminPreset(),
            'countries' => self::countries(),
            'q' => $q,
        ]);
    }

    public function update(Request $request)
    {
        $this->ensureCanManage();

        $request->validate([
            'target_user' => 'required|string|max:190',
            'admin_mode' => 'required|in:normal,admin,subadmin,country',
            'country_id' => 'nullable|integer',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        $user = User::where('id', trim($request->target_user))
            ->orWhere('email', trim($request->target_user))
            ->first();
        if (!$user) {
            return back()->withInput()->with(['messege' => 'Admin target user not found.', 'alert-type' => 'error']);
        }

        $mode = $request->admin_mode;
        if ((int) $user->id === 1111120) {
            return back()->with(['messege' => 'Cannot modify superuser account.', 'alert-type' => 'error']);
        }
        if ((int) $user->id === (int) Auth::id() && $mode === 'normal') {
            return back()->withInput()->with(['messege' => 'You cannot remove your own admin access.', 'alert-type' => 'error']);
        }

        $countryId = $request->filled('country_id') ? (int) $request->country_id : (int) ($user->country_id ?: 0);
        if ($mode === 'country' && !self::validCountryId($countryId)) {
            return back()->withInput()->with(['messege' => 'Select a valid country for Country Admin.', 'alert-type' => 'error']);
        }

        $perms = array_values(array_intersect((array) $request->input('permissions', []), self::keys()));
        if ($mode === 'country' && empty($perms)) {
            $perms = self::countryAdminPreset();
        }

        DB::transaction(function () use ($request, $user, $mode, $perms, $countryId) {
            if ($request->filled('password')) {
                $user->password = Hash::make((string) $request->password);
            }

            if ($mode === 'normal') {
                $user->is_admin = 0;
                $user->role = 2;
                $user->is_bd_admin = 0;
                $user->is_app_admin = 0;
                AdminParmisiton::query()->where('user_id', $user->id)->delete();
            } else {
                $user->status = 1;
                $user->is_bd_admin = 0;
                $user->is_app_admin = 0;

                if ($mode === 'admin') {
                    $user->is_admin = 1;
                    $user->role = 1;
                    $user->is_bd_admin = 1;
                    $user->is_app_admin = 1;
                    $user->can_banned = 1;
                    $user->can_call_cut = 1;
                    $user->brd_off_power = 1;
                    $user->comment_mute_power = 1;
                    $user->kick_power = 1;
                    $user->agora_access = 1;
                } elseif ($mode === 'country') {
                    $user->is_admin = 2;
                    $user->role = 2;
                    $user->country_id = $countryId;
                } else {
                    $user->is_admin = 3;
                    $user->role = 3;
                }

                AdminParmisiton::query()->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'admin_mode' => $mode,
                        'permissions' => json_encode($perms),
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            $user->save();
        });

        AdminParmisiton::forgetUser($user->id);
        \App\RedisCache\CacheClearHelperFromModelAuto::clearUserCaches($user->id, 'admin_setting_permissions_updated');
        return back()->with(['messege' => 'Admin permission saved for user ' . $user->id . '.', 'alert-type' => 'success']);
    }

    /**
     * Keep the adminparmisiton permission row in sync whenever a user's admin
     * role changes from ANY entry point (this controller, the profile "Change
     * Admin Role" button, or AdminRoleStore).
     *
     * Bug this fixes: the role buttons set users.is_admin (0/1/2/3) but never
     * created a permission row. So making someone Sub Admin / Country from the
     * profile button gave them NO permissions -> allowed() = false everywhere
     * -> locked out of the panel, and the only way to grant access was Main
     * Admin (is_admin = 1), which BYPASSES every permission ("whole panel
     * ignores perms"). Now a limited role always gets a working permission row
     * (existing custom permissions are preserved; a brand-new admin gets the
     * preset for their mode). Normal (0) clears the row.
     */
    public static function applyRolePermissions($user, int $role): void
    {
        if (!$user) {
            return;
        }

        if ($role === 0) {
            AdminParmisiton::query()->where('user_id', $user->id)->delete();
            AdminParmisiton::forgetUser($user->id);
            return;
        }

        $mode = $role === 1 ? 'admin' : ($role === 2 ? 'country' : 'subadmin');
        $existing = AdminParmisiton::query()->where('user_id', $user->id)->first();

        if ($existing) {
            // Preserve the admin's existing custom selection; only refresh mode.
            $perms = array_values(array_intersect((array) $existing->permissions, self::keys()));
        } else {
            $perms = $mode === 'admin' ? self::adminPreset() : ($mode === 'country' ? self::countryAdminPreset() : self::subadminPreset());
        }

        AdminParmisiton::query()->updateOrInsert(
            ['user_id' => $user->id],
            [
                'admin_mode' => $mode,
                'permissions' => json_encode($perms),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        AdminParmisiton::forgetUser($user->id);
    }

    public function countryAdminStore(Request $request)
    {
        $this->ensureCanManage();

        $request->validate([
            'target_user' => 'nullable|string|max:190',
            'country_id' => 'required|integer',
            'name' => 'nullable|string|max:190',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        $countryId = (int) $request->country_id;
        if (!self::validCountryId($countryId)) {
            return back()->withInput()->with(['messege' => 'Select a valid country for Country Admin.', 'alert-type' => 'error']);
        }

        $target = trim((string) $request->target_user);
        $changedUserId = null;
        try {
            DB::transaction(function () use ($request, $target, $countryId, &$changedUserId) {
                if ($target !== '') {
                    $user = User::where('id', $target)->orWhere('email', $target)->lockForUpdate()->first();
                    if (!$user) {
                        throw new \RuntimeException('notfound');
                    }
                } else {
                    if (!$request->filled('name') || !$request->filled('email') || !$request->filled('password')) {
                        throw new \RuntimeException('required');
                    }
                    if (User::where('email', trim($request->email))->exists()) {
                        throw new \RuntimeException('exists');
                    }

                    $user = new User();
                    $user->name = trim($request->name);
                    $user->email = trim($request->email);
                    $user->password = Hash::make((string) $request->password);
                    $user->balance = 0;
                    $user->hold_balance = 0;
                    $user->level = 1;
                    $user->is_vip = 0;
                    $user->entry_level = 0;
                    $user->profile = 'https://queenlive.site/store/profile/default.png';
                    $user->date_wise_balance = 0;
                    $user->game_balance_date = date('Y-m-d');
                }

                if ($request->filled('phone')) {
                    $user->phone = trim($request->phone);
                }
                if ($request->filled('password') && $user->exists) {
                    $user->password = Hash::make((string) $request->password);
                }

                $user->country_id = $countryId;
                $user->is_admin = 2;
                $user->role = 2;
                $user->is_bd_admin = 0;
                $user->is_app_admin = 0;
                $user->status = 1;
                $user->save();

                AdminParmisiton::query()->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'admin_mode' => 'country',
                        'permissions' => json_encode(self::countryAdminPreset()),
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $changedUserId = $user->id;
            });
        } catch (\RuntimeException $e) {
            $messages = [
                'notfound' => 'Country admin target user not found.',
                'required' => 'Name, email, and password are required for a new country admin.',
                'exists' => 'This country admin email already exists.',
            ];

            return back()->withInput()->with(['messege' => $messages[$e->getMessage()] ?? 'Unable to save country admin.', 'alert-type' => 'error']);
        }

        if ($changedUserId) {
            AdminParmisiton::forgetUser($changedUserId);
            \App\RedisCache\CacheClearHelperFromModelAuto::clearUserCaches($changedUserId, 'country_admin_permissions_updated');
        }

        return back()->with(['messege' => 'Country admin saved successfully.', 'alert-type' => 'success']);
    }

    public function delete(Request $request)
    {
        $this->ensureCanManage();

        $request->validate(['target_user' => 'required|string|max:190']);

        $user = User::where('id', trim($request->target_user))
            ->orWhere('email', trim($request->target_user))
            ->first();
        if (!$user) {
            return back()->with(['messege' => 'Admin permission target user not found.', 'alert-type' => 'error']);
        }
        if ((int) $user->id === (int) Auth::id()) {
            return back()->with(['messege' => 'You cannot delete your own admin permission.', 'alert-type' => 'error']);
        }

        DB::transaction(function () use ($user) {
            AdminParmisiton::query()->where('user_id', $user->id)->delete();
            $user->is_admin = 0;
            $user->role = 2;
            $user->is_bd_admin = 0;
            $user->is_app_admin = 0;
            $user->save();
        });

        AdminParmisiton::forgetUser($user->id);
        \App\RedisCache\CacheClearHelperFromModelAuto::clearUserCaches($user->id, 'admin_setting_permissions_deleted');
        return back()->with(['messege' => 'Admin permission removed for user ' . $user->id . '.', 'alert-type' => 'success']);
    }

    public static function perms($row)
    {
        if (!$row || !isset($row->permissions)) {
            return [];
        }

        if (is_array($row->permissions)) {
            return $row->permissions;
        }

        $data = json_decode((string) $row->permissions, true);
        return is_array($data) ? $data : [];
    }
}
