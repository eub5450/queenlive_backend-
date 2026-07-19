# QueenLive Admin Panel Bug Report (2026-07-20)

---

## HIGH priority

### BUG-01: Vultr server API key hardcoded in source code
**File**: `app/Http/Controllers/Admin/DashbordController.php:57-58`
**Issue**: The Vultr cloud API key and instance ID are stored as string literals inside the controller method body. Any developer with repo access can extract these values and reboot or otherwise control the production server via the Vultr API.
```php
$apiKey = '6R2EUIZTIAIB3UN4VMBSJ74UCF5226BVCFIA';
$instanceId = '12a0e3a6-a7e1-4f3d-9a86-95f34e076792';
```
**Risk**: External credential exposure / unauthorized server control
**Fix**: Move to `.env` as `VULTR_API_KEY` and `VULTR_INSTANCE_ID`, read with `env()`. Rotate the key immediately after the fix lands.

---

### BUG-02: Pusher app secret hardcoded in source code
**File**: `app/Http/Controllers/Admin/DashbordController.php:655-659`
**Issue**: The Pusher app key, secret, and app ID are hardcoded inside `EmargencyIDBanned()` instead of reading from the `.env`/settings table.
```php
$pusher = new Pusher\Pusher(
    '9ce9d96701d6600b426e',
    '71aedfa829b4eb09c453',
    '1618585',
    $options
);
```
**Risk**: Credential leak — anyone with read access to the repo can push events to the Pusher channel.
**Fix**: Read from `config('broadcasting.connections.pusher.*')` or `env()`. The values are already stored in the main settings table for other callers.

---

### BUG-03: `PasswordChange` always resets password to hardcoded `123456`
**File**: `app/Http/Controllers/Admin/ProfileController.php:653`
**Issue**: The method ignores the request body entirely and always hashes the integer literal `123456` as the new password. Every time an admin clicks "change password" for any user, that user's password becomes `123456`.
```php
$user->password=Hash::make(123456);
```
**Risk**: All user accounts get set to a trivially-guessable password on every admin click.
**Fix**: Accept a `password` field from the request, validate it (min 6 chars), and hash the actual input value.

---

### BUG-04: `AddDayTime` stores the CSRF token as `channelName`
**File**: `app/Http/Controllers/Admin/ProfileController.php:1209`
**Issue**: The `AddDayTime` method saves `$request->_token` (the CSRF token, not a channel name) as the `channelName` column in the `day_times` table. Every manually-added day-time row gets the current session's CSRF token as the channel, meaning the data is corrupt and channel lookups will always fail.
```php
$data->channelName=$request->_token;
```
**Risk**: Data corruption in `day_times` records; host day-time lookups by channel will never match.
**Fix**: Remove the `channelName` assignment entirely, or supply a real channel name from `$request->channelName` with validation.

---

### BUG-05: `/socket-tools/*` diagnostic routes are publicly accessible without authentication
**File**: `routes/web.php:26-28, 30-478`
**Issue**: The routes `/socket-tools/test`, `/socket-tools/send`, `/socket-tools/health`, and `/socket-tools/live-data` are declared OUTSIDE any `auth` or `admin` middleware group. Any unauthenticated visitor can access them. The `live-data` endpoint returns recent live room rows (user IDs, channel names, co-host IDs), recent audience joins, recent comments with gift values, and DB table row counts — all without logging in. It also accepts a `?format=json` parameter.
**Risk**: Information disclosure — user IDs, live room state, and comment data are publicly readable.
**Fix**: Wrap all four routes in the existing `['middleware' => ['auth','admin']]` group, or add `->middleware('auth')` to each route definition. Do the same for `/drive/*` and `/oauth/*` routes which are also outside any auth group.

---

### BUG-06: `Rank()` mutates `gifts` table on every GET request (massive N+1 side effect)
**File**: `app/Http/Controllers/Admin/ProfileController.php:922-932`
**Issue**: The `Rank()` method (route: `GET rankingList`) fetches ALL gifts where `agency_code IS NULL` and updates each one inside a `foreach` loop — once per request. This is an unbounded write operation triggered by every page load. As gifts accumulate, this will run hundreds of thousands of UPDATE queries on every ranking page view.
```php
$gifts=Gift::where('agency_code',null)->get();
foreach($gifts as $gift){
    $host_data=HostData::where('user_id',$gift->reciever_id)->first(); // N+1
    $gift->agency_code=$host_data->agency_code;
    $gift->save(); // write on GET
}
```
**Risk**: Performance (query storm on every page load); unexpected data mutation on a read-only page.
**Fix**: Move this backfill into a one-time migration or an artisan command. Remove it from the controller entirely. If back-fill must run inline, gate it behind a flag so it only fires once.

---

## MEDIUM priority

### BUG-07: `invisibal` and `official_id` routes gated by `sidebar_ban_id` instead of their own permission keys
**File**: `app/Http/Middleware/AdminMiddleware.php:151-153`
**Issue**: The middleware maps the `invisibal` route to `['sidebar_menu_ban', 'sidebar_ban_id']` and `official_id`/`official_id_active` to the same two keys, instead of `sidebar_invisible_id` and `sidebar_official_id` respectively. These permission keys exist in `groups()` but are never enforced at the route level.
```php
'invisibal' => ['sidebar_menu_ban', 'sidebar_ban_id'],
'official_id' => ['sidebar_menu_ban', 'sidebar_ban_id'],
'official_id_active' => ['sidebar_menu_ban', 'sidebar_ban_id'],
```
**Risk**: An admin with only Ban ID permission gets full access to Invisible ID and Official ID management pages, bypassing any intended separation.
**Fix**: Change the mapped arrays to use the correct keys:
```php
'invisibal' => ['sidebar_menu_ban', 'sidebar_invisible_id'],
'invisible_id_reject/' => ['sidebar_menu_ban', 'sidebar_invisible_id'],
'official_id' => ['sidebar_menu_ban', 'sidebar_official_id'],
'official_id_active' => ['sidebar_menu_ban', 'sidebar_official_id'],
'official_id_reject/' => ['sidebar_menu_ban', 'sidebar_official_id'],
```

---

### BUG-08: `AdminPermissionStore` allows any sub-admin to escalate in-app admin flags for any user
**File**: `app/Http/Controllers/Admin/UserController.php:107-168`
**Issue**: The `AdminPermissionStore` method (route: `POST admin-user-permission-store`, gated by `profile_power_buttons`) allows setting `is_app_admin=1`, `is_bd_admin=1`, `can_banned=1`, `kick_power=1`, etc. on ANY user including main admins and the superuser account. A sub-admin with `profile_power_buttons` (which is in `subadminPreset()`) can use this to grant themselves or any user full in-app moderation powers without going through the main admin permission flow. There is no check preventing modification of protected users.
**Risk**: Privilege escalation in mobile app; sub-admin can grant themselves `can_banned`, `kick_power`, and admin frame flags.
**Fix**: Add a guard: if the target user's `is_admin > 0` and the current user's `is_admin < 1`, abort(403). Also block setting `is_app_admin`/`is_bd_admin` to 1 unless the caller is a main admin.

---

### BUG-09: Superuser ID 1111120 can be demoted by any main admin
**File**: `app/Http/Controllers/Admin/AdminSettingController.php:168-170`, `app/Http/Controllers/Admin/UserController.php:70-75`, `app/Http/Controllers/Admin/ProfileController.php:558-560`
**Issue**: All three role-change code paths only block self-demotion. They do not block demoting user ID 1111120. While the `AdminParmisiton::allowed()` check still returns `true` for that ID even if `is_admin=0`, other parts of the codebase that check `is_admin` directly (e.g., DashboardController queries, mobile API checks) would see a demoted record.
```php
// AdminSettingController.php:168
if ((int) $user->id === (int) Auth::id() && $mode === 'normal') { ... }
// Missing: if ((int) $user->id === 1111120) abort/return error
```
**Risk**: Logic error — the declared superuser account could have its `is_admin` column zeroed out by another main admin.
**Fix**: Add a guard in all three methods:
```php
if ((int) $user->id === 1111120) {
    return back()->with(['messege' => 'Cannot modify superuser account.', 'alert-type' => 'error']);
}
```

---

### BUG-10: `getOldeReceivedSum` and `getOldeSendingSum` column filters are swapped
**File**: `app/Http/Controllers/Admin/ProfileController.php:391-412`
**Issue**: `getOldeReceivedSum` queries by `sander_id` (the sender), and `getOldeSendingSum` queries by `reciever_id` (the receiver). The method names say the opposite of what the query does.
```php
protected function getOldeReceivedSum($userId)  // name says "received"
{
    return OldGift::where('sander_id', $userId)  // but filters by sender ← WRONG
        ->sum('value');
}
protected function getOldeSendingSum($userId)   // name says "sending"
{
    return OldGift::where('reciever_id', $userId) // but filters by receiver ← WRONG
        ->sum('value');
}
```
Both values are passed to the view as `old_sum_sending_historys` and `old_sum_reciving_historys` respectively. The displayed totals on the profile page are inverted for old gifts.
**Risk**: Data accuracy — admins see the wrong old-gift totals on user profiles.
**Fix**: Swap the filter columns to match the method names, or rename the methods to match the actual filter being applied.

---

### BUG-11: `chat()` loads ALL chat records with no limit
**File**: `app/Http/Controllers/Admin/DashbordController.php:614-620`
**Issue**: The `chat()` method (route: `GET chat_data`) uses `Chat::all()` with no pagination or limit. As the chat table grows, this will eventually exhaust PHP memory or cause a very slow response.
```php
$chat_data_all=Chat::all();
```
**Risk**: Performance / potential 500 on large datasets.
**Fix**: Apply `->latest('id')->limit(500)->get()` or add Laravel pagination.

---

### BUG-12: `ChangeHostingType` has no country scope — country admins can modify any host
**File**: `app/Http/Controllers/Admin/ProfileController.php:1051-1073`
**Issue**: `ChangeHostingType($id)` finds `HostData` by the raw `host_data.id`, with no join/filter on the user's `country_id`. Because country admins have `profile_power_buttons` permission by default (via `countryAdminPreset()`), they can navigate to `hosting_type_change/{any_host_data_id}` and flip the hosting type for hosts in other countries. There is no check inside the method or via the middleware prefix match.
**Risk**: A country admin can change the hosting type (and therefore compensation/day-time calculation) for hosts belonging to other countries.
**Fix**: Load the associated user and verify `country_id` matches the current admin's country before saving.

---

## LOW priority

### BUG-13: `UserEmailChangeStore` references undefined variable `$name`, causing a fatal error
**File**: `app/Http/Controllers/Admin/UserController.php:276`
**Issue**: In the branch where a target user is not found (`$is_id_exist` is null) and the code attempts to create a new user, the array includes `'name' => $name` but `$name` is never defined anywhere in the method. Hitting this code path throws `Undefined variable $name` and returns a 500 error.
```php
$new_user = User::create([
    'name' => $name,  // $name is undefined
    ...
]);
```
**Risk**: Fatal PHP error / 500 response when trying to create a new user via the email-change route.
**Fix**: Either read `$name` from the request (`$request->name`) with a validation rule, or remove this dead creation branch — `newIdGive()` already handles the creation case and is what the route `admin-user-new-email-change_store` actually calls.

---

### BUG-14: `ProfileController::ChangePass` (admin self-password change) has no input validation
**File**: `app/Http/Controllers/Admin/ProfileController.php:1145-1155`
**Issue**: The `ChangePass` method (route: `POST admin/check_password`) accepts `$request->password` without any validation rules. An empty string or a single-character string will be hashed and saved, locking the admin out.
```php
$data->password=Hash::make($request->password);
```
**Risk**: Admin lockout if empty or trivially short password is submitted.
**Fix**: Add `$request->validate(['password' => 'required|string|min:6']);` before hashing.

---

### BUG-15: `UserController::CountryAdminStore` has a hardcoded `in:1,2,3` country ID whitelist
**File**: `app/Http/Controllers/Admin/UserController.php:174`
**Issue**: This older `CountryAdminStore` method (not the one in `AdminSettingController`) validates `country_id` with `'required|integer|in:1,2,3'`. This is a hardcoded list of three country IDs and would silently block any country with ID > 3. The route for this method does not appear in `web.php`, so it may be dead code — but if ever wired up or accidentally called via a form, it will reject valid countries.
**Risk**: Logic error / silent rejection of valid countries if this method is used.
**Fix**: Either delete the method entirely (the `AdminSettingController::countryAdminStore` is the canonical version with a proper `validCountryId()` lookup), or replace `in:1,2,3` with `exists:countries,id`.

---

*Report generated 2026-07-20. Excludes bugs already flagged for concurrent fixes: permission gates on profile tables, sidebar key collisions, country admin data scoping, DashboardController email-string gate, missing countryAdminPreset(), and missing profile_sensitive_info from groups().*
