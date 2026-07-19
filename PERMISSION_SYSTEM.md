# QueenLive Admin Permission System

Full reference for the granular RBAC permission system. Last updated: 2026-07-20.

---

## How It Works

1. Every admin user has a row in `adminparmisiton` table: `user_id`, `permissions` (JSON array of key strings), `admin_mode`, `updated_by`
2. `AdminParmisiton::allowed($userId, $key)` returns `true/false`, cached 60s per user in Redis (`queenlive:adminparmisiton:{userId}`)
3. All blade views call `$adminCan('key')` to show/hide elements
4. `AdminMiddleware` checks permissions at the route level before any controller runs
5. Special bypasses:
   - `user_id = 1111120` → always `true` (hardcoded superuser)
   - `is_admin = 1` (Main Admin) → always `true`

---

## Role Presets

When a user is assigned a role, their permission row is seeded with the matching preset. Extra per-user grants can be added on top.

### Main Admin (is_admin = 1)
Bypasses all checks — `AdminParmisiton::allowed()` always returns `true`. No permission row needed.

### Country Admin Preset (is_admin = 2) — ~30 keys
```
sidebar_access, sidebar_menu_dashboard,
sidebar_menu_host, sidebar_host_add, sidebar_host_active, sidebar_host_pending, sidebar_host_transfer,
sidebar_menu_agency, sidebar_agency_list,
sidebar_menu_protal, sidebar_protal_create, sidebar_protal_list,
sidebar_protal_recharge, sidebar_protal_recharge_list,
sidebar_protal_new_recall, sidebar_protal_recall_list,
sidebar_menu_support, sidebar_support_index,
sidebar_menu_ranking, sidebar_ranking_list,
sidebar_menu_user_balance, sidebar_user_balance_wallet,
sidebar_menu_ban, sidebar_ban_id, sidebar_invisible_id, sidebar_official_id,
sidebar_menu_live, sidebar_live_list,
dashboard_access,
profile_search, profile_balance, profile_email_info, profile_phone_info,
profile_f_level, profile_f_join_date, profile_f_country,
profile_f_hosting_type, profile_f_day, profile_f_time,
profile_power_buttons, profile_vip_frames_edit,
profile_table_host_data, profile_table_daytime_history,
profile_btn_active_host, profile_btn_reject_host,
profile_btn_active_official, profile_btn_reject_official,
profile_sensitive_info
```
**Data scope**: All list/search queries are filtered by `users.country_id = admin.country_id` automatically.

### Sub Admin Preset (is_admin = 3) — ~44 keys
```
sidebar_access, sidebar_menu_dashboard,
sidebar_menu_host, sidebar_host_add, sidebar_host_active, sidebar_host_pending, sidebar_host_transfer,
sidebar_menu_agency, sidebar_agency_create, sidebar_agency_list,
sidebar_menu_protal, sidebar_protal_create, sidebar_protal_list,
sidebar_protal_recall_create, sidebar_protal_recall_history,
sidebar_protal_recharge, sidebar_protal_recharge_list,
sidebar_protal_new_recall, sidebar_protal_recall_list,
sidebar_menu_support, sidebar_support_index,
sidebar_menu_ranking, sidebar_ranking_list,
sidebar_menu_ban, sidebar_ban_id, sidebar_invisible_id, sidebar_official_id,
sidebar_menu_live, sidebar_live_list,
sidebar_menu_setting, sidebar_setting_banner, sidebar_setting_store,
dashboard_access, dashboard_game_pro_balance,
profile_search, profile_balance, profile_power_buttons, profile_vip_frames_edit,
profile_table_portal_history, profile_table_host_data, profile_table_game_history,
profile_table_daytime_history, profile_table_portal_transfer, profile_table_convert_history,
profile_table_recharge_history, profile_table_gift_history
```

---

## All Permission Keys by Group

### Sidebar — Main Menu (12 keys)
| Key | Controls |
|-----|---------|
| `sidebar_access` | Entire admin sidebar (master gate) |
| `sidebar_menu_dashboard` | Dashboard menu item |
| `sidebar_menu_host` | Host section header |
| `sidebar_menu_agency` | Agency section header |
| `sidebar_menu_protal` | Portal section header |
| `sidebar_menu_support` | Support section header |
| `sidebar_menu_ranking` | Ranking section header |
| `sidebar_menu_user_balance` | Finance section header |
| `sidebar_menu_ban` | Ban/ID section header |
| `sidebar_menu_live` | Live section header |
| `sidebar_menu_game_control` | Game Control section header |
| `sidebar_menu_setting` | Settings section header |

### Sidebar — Host (4 keys)
| Key | Controls |
|-----|---------|
| `sidebar_host_add` | Add Host page |
| `sidebar_host_active` | Active Hosts page |
| `sidebar_host_pending` | Pending Hosts page |
| `sidebar_host_transfer` | Host Transfer page |

### Sidebar — Agency (2 keys)
| Key | Controls |
|-----|---------|
| `sidebar_agency_create` | Create Agency page |
| `sidebar_agency_list` | Agency List page |

### Sidebar — Portal (6 keys)
| Key | Controls |
|-----|---------|
| `sidebar_protal_create` | Create Portal page |
| `sidebar_protal_list` | Portal List page |
| `sidebar_protal_recall_create` | Create Recall page |
| `sidebar_protal_recall_history` | Recall History page |
| `sidebar_protal_recharge` | Portal Recharge page |
| `sidebar_protal_recharge_list` | Recharge List page |
| `sidebar_protal_new_recall` | New Recall page |
| `sidebar_protal_recall_list` | Recall List page |

### Sidebar — Support, Ranking (2 keys)
| Key | Controls |
|-----|---------|
| `sidebar_support_index` | Support page |
| `sidebar_ranking_list` | Ranking list page |

### Sidebar — Finance (2 keys)
| Key | Controls |
|-----|---------|
| `sidebar_user_balance_wallet` | User Balance page |
| `sidebar_withdraw` | Withdraw section |

### Sidebar — ID Management (3 keys)
| Key | Controls |
|-----|---------|
| `sidebar_ban_id` | Ban ID parent menu + child link |
| `sidebar_invisible_id` | Invisible ID parent menu + child link (gates both — no empty parent) |
| `sidebar_official_id` | Official ID parent menu + child link (gates both — no empty parent) |

> **Important**: `sidebar_invisible_id` controls the parent `<li>` AND the child link together. Granting only `sidebar_ban_id` does NOT show the Invisible ID or Official ID menus. Each of the three ID menus is fully independent.

### Sidebar — Live, Settings (8 keys)
| Key | Controls |
|-----|---------|
| `sidebar_live_list` | Live rooms list |
| `sidebar_setting_admin` | Admin permission page |
| `sidebar_setting_banner` | Banner settings |
| `sidebar_setting_store` | Store settings |
| `sidebar_setting_level` | Level settings |
| `sidebar_setting_gift_data` | Gift data settings |
| `sidebar_setting_system` | System settings |
| `sidebar_setting_country` | Country settings |
| `sidebar_setting_audio_background` | Audio background settings |
| `sidebar_setting_agora` | Agora settings |
| `sidebar_setting_email_change` | Email change settings |
| `sidebar_setting_checkin` | Check-in settings |
| `sidebar_setting_combo` | Gift combo settings |
| `sidebar_setting_fanclub` | Fan club settings |
| `sidebar_setting_fun_sticker` | Fun sticker settings |

### Dashboard (26 keys)
| Key | Controls |
|-----|---------|
| `dashboard_access` | Entire dashboard (master gate) |
| `dashboard_vip_offer` | VIP Offer button |
| `dashboard_version_update` | Android version update button |
| `dashboard_profit_loss` | Profit/Loss card |
| `dashboard_total_serve_coin` | Total Serve Coin card |
| `dashboard_coin_generate_game` | Coin Generate card |
| `dashboard_game_data` | Game data cards |
| `dashboard_realtime_feeds` | Comment/Chat feed |
| `dashboard_game_pro_balance` | Game Pro Balance card |
| `dashboard_game_pro_balance_manage` | GamePro Calculation + Deposit/Withdraw panel |
| `dashboard_total_users` | Total Users card |
| `dashboard_user_wallets` | User Wallets card |
| `dashboard_game_profit` | Game Profit card |
| `dashboard_today_recharge` | Today Recharge card |
| `dashboard_today_sending` | Today Sending card |
| `dashboard_today_receiving` | Today Receiving card |
| `dashboard_today_gift` | Today Gift Sum card |
| `dashboard_withdraw_commission` | Withdraw Commission card |
| `dashboard_users_agents` | Users & Agents card |
| `dashboard_coin_metrics` | Coin Metrics card |
| `dashboard_today_transactions` | Today Transactions card |
| `dashboard_withdraw_profit` | Withdraw Profit card |
| `dashboard_portal_balance` | Portal Balance card |
| `dashboard_portal_send` | Portal Send card |
| `dashboard_total_receiving` | Total Receiving card |

### Profile — Core (3 keys)
| Key | Controls |
|-----|---------|
| `profile_search` | Profile search page (master gate) |
| `profile_balance` | Balance section |
| `profile_sensitive_info` | Sensitive/PII badge |
| `profile_email_info` | Email field visible |
| `profile_phone_info` | Phone field visible |
| `profile_power_buttons` | Power Controls section (host/role/moderation buttons) |
| `profile_vip_frames_edit` | VIP frame editor |

### Profile — Data Fields (25 keys, prefix: profile_f_)
| Key | Controls |
|-----|---------|
| `profile_f_name` | Name field |
| `profile_f_level` | Level field |
| `profile_f_join_date` | Join date |
| `profile_f_country` | Country field |
| `profile_f_hosting_type` | Hosting type |
| `profile_f_day` | Day field |
| `profile_f_time` | Time field |
| `profile_f_available` | Available status |
| `profile_f_code` | Code field |
| `profile_f_recharge` | Recharge total |
| `profile_f_recall` | Recall total |
| `profile_f_convert` | Convert total |
| `profile_f_transfer` | Transfer total |
| `profile_f_withdraw_commission` | Withdraw commission |
| `profile_f_total_withdraw` | Total withdraw |
| `profile_f_portal_transfer_send` | Portal send total |
| `profile_f_portal_transfer_received` | Portal received total |
| `profile_f_vip_activation` | VIP activation date |
| `profile_f_special_frame` | Special frame |
| `profile_f_entry` | Entry field |
| `profile_f_point_collect` | Points collected |
| `profile_f_previous_points` | Previous points |
| `profile_f_now_points_have` | Current points |
| `profile_f_join_agency_name` | Joined agency name |
| `profile_f_agency_phone` | Agency phone |

### Profile — Tables (8 keys, prefix: profile_table_)
Each key gates the ENTIRE table section including heading card.

| Key | Controls |
|-----|---------|
| `profile_table_portal_history` | Portal Recharge + Transfer History tables |
| `profile_table_host_data` | Host Data table |
| `profile_table_game_history` | Game History table |
| `profile_table_daytime_history` | Day Time History table |
| `profile_table_portal_transfer` | Portal Transfer Send + Received tables |
| `profile_table_convert_history` | Convert History table |
| `profile_table_recharge_history` | Monthly Recharge + Full Recharge History |
| `profile_table_gift_history` | Gift Sending + Receiving History |

### Profile — Action Buttons (10 keys, prefix: profile_btn_)
| Key | Controls |
|-----|---------|
| `profile_btn_user_role` | Role change buttons (+ Demote to Normal User) |
| `profile_btn_active_host` | Activate Host button |
| `profile_btn_reject_host` | Reject Host button |
| `profile_btn_active_official` | Activate Official ID button |
| `profile_btn_reject_official` | Reject Official ID button |
| `profile_btn_active_protal` | Activate Portal button |
| `profile_btn_reject_protal` | Reject Portal button |
| `profile_btn_top_position` | Top Position button |
| `profile_btn_gift_recall` | Gift Recall button |
| `profile_btn_withdraw_active` | Withdraw Activate button |

### Profile — Visual / Frame
| Key | Controls |
|-----|---------|
| `profile_entry_frame` | Entry frame editor |

---

## Country Admin Data Scoping

When `Auth::user()->is_admin === 2`, these controllers auto-filter all list/search queries by `users.country_id`:

| Controller | Method(s) | Scope mechanism |
|------------|-----------|-----------------|
| `DashbordController` | `getDashboardData()` | `->when($countryId, fn($q) => $q->where('country_id', $countryId))` on User queries; cache key includes country |
| `ProfileController` | `Index()`, `User()`, `Rank()` | Direct `country_id` filter on users |
| `HostController` | `index()`, `Pending()`, `Transfer()` | Direct or via `users.country_id` join |
| `AgencyController` | `Index()` | Direct `country_id` column on agencies |
| `LiveController` | `Index()` | `whereHas('user', fn => where country_id)` |
| `WithdrawController` | `Index()` | `whereHas('host', fn => where country_id)` |

Country admins **cannot**:
- See users from other countries in any list
- Modify hosting type for hosts from other countries (`ChangeHostingType` country guard)
- Promote themselves or other users past their own role level

---

## Adding New Permissions

1. Add the key + label to the correct group in `AdminSettingController::groups()`
2. Add to `subadminPreset()` and/or `countryAdminPreset()` if it should be on by default
3. Add `@if($adminCan('new_key'))..@endif` in the relevant blade
4. If it gates a route, add the route→key mapping in `AdminMiddleware`
5. After deploying, flush Redis: `redis-cli -h 10.104.0.9 keys 'queenlive:adminparmisiton:*' | xargs redis-cli -h 10.104.0.9 del`

---

## Security Invariants (must never be broken)

1. User 1111120 always returns `true` from `AdminParmisiton::allowed()` — `AdminMiddleware` and blade checks all depend on this
2. User 1111120 is blocked from demotion in `AdminSettingController::update()`, `UserController::AdminRoleStore()`, and `ProfileController::AdminRoleUpdate()`
3. Sub-admins (`is_admin=3`) cannot call `AdminPermissionStore` on any user with `is_admin > 0`
4. `socket-tools/*`, `drive/*`, and `oauth/*` routes are inside the `['auth','admin']` middleware group
5. `PasswordChange` validates min 6 characters before hashing
6. No credentials in PHP source — Vultr and Pusher credentials must stay in `.env` only
