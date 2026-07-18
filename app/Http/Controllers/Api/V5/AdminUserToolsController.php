<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V5\Concerns\AdminActorAuthorization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserToolsController extends Controller
{
    use AdminActorAuthorization;

    private const PERMISSION_COLUMN_MAP = [
        'agora_access' => 'agora_access',
        'board_off_power' => 'brd_off_power',
        'brd_off_power' => 'brd_off_power',
        'top_position' => 'prosss_top',
        'prosss_top' => 'prosss_top',
        'screenshot_power' => 'sceen_short_power',
        'sceen_short_power' => 'sceen_short_power',
        'kick_power' => 'kick_power',
        'comment_mute_power' => 'comment_mute_power',
        'invisible_power' => 'is_invisible',
        'is_invisible' => 'is_invisible',
        'withdraw_active' => 'withdraw_active',
        'host_status' => 'is_host_id',
        'host_active' => 'is_host_id',
        'is_host_id' => 'is_host_id',
        'portal_status' => 'is_coin_protal_active',
        'portal_active' => 'is_coin_protal_active',
        'is_coin_protal_active' => 'is_coin_protal_active',
        'official_id_status' => 'is_official_id',
        'official_id_active' => 'is_official_id',
        'is_official_id' => 'is_official_id',
        'official_frame_active' => 'is_official_frame',
        'is_official_frame' => 'is_official_frame',
        'admin_frame_active' => 'is_admin_frame',
        'is_admin_frame' => 'is_admin_frame',
        'vip_active' => 'is_vip',
        'is_vip' => 'is_vip',
        'lock_brd_entry' => 'lock_brd_entry',
        'auto_lock_status' => 'auto_lock_status',
        'can_banned' => 'can_banned',
        'can_call_cut' => 'can_call_cut',
        'is_app_admin' => 'is_app_admin',
        'admin_status' => 'is_admin',
        'make_admin' => 'is_admin',
        'is_admin' => 'is_admin',
        'admin_role' => 'role',
        'role' => 'role',
    ];

    public function index(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        if ($this->shouldHandlePermissionUpdate($request)) {
            return $this->updatePermission($request);
        }

        return $this->success('Admin user tools loaded successfully', [
            'data' => [
                'countries' => $this->countryOptions(),
                'actions' => [
                    'country_admin_store' => 'v5/admin/country_admin/store',
                    'email_change' => 'v5/admin/email_change',
                    'email_change_store' => 'v5/admin/email_change/store',
                    'email_change_new_id' => 'v5/admin/email_change/new_id',
                    'user_lookup' => 'v5/admin/user_lookup',
                ],
                'permission_keys' => array_values(array_unique(array_keys(self::PERMISSION_COLUMN_MAP))),
            ],
        ]);
    }

    public function lookup(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $query = trim((string) $request->input('query', $request->input('search', $request->input('id', ''))));
        if ($query === '') {
            return $this->error('query required', '422');
        }

        $user = User::where('id', $query)->orWhere('email', $query)->first();
        if (!$user) {
            return $this->error('User not found', '404');
        }

        return $this->success('User found', ['data' => $user]);
    }

    public function countryAdminStore(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $request->validate([
            'country_target_user' => 'nullable|string|max:190',
            'country_id' => 'required|integer',
            'country_name' => 'nullable|string|max:190',
            'country_email' => 'nullable|email|max:190',
            'country_phone' => 'nullable|string|max:50',
            'country_password' => 'nullable|string|min:6',
        ]);

        $countryId = (int) $request->country_id;
        if (!$this->validCountryId($countryId)) {
            return $this->error('Select a valid country for Country Admin.', '422');
        }

        $target = trim((string) $request->country_target_user);

        try {
            DB::transaction(function () use ($request, $target, $countryId) {
                if ($target !== '') {
                    $user = User::where('id', $target)->orWhere('email', $target)->lockForUpdate()->first();
                    if (!$user) {
                        throw new \RuntimeException('__COUNTRY_ADMIN_TARGET_NOT_FOUND__');
                    }
                } else {
                    if (!$request->filled('country_name') || !$request->filled('country_email') || !$request->filled('country_password')) {
                        throw new \RuntimeException('__COUNTRY_ADMIN_NEW_REQUIRED__');
                    }
                    if (User::where('email', trim((string) $request->country_email))->exists()) {
                        throw new \RuntimeException('__COUNTRY_ADMIN_EMAIL_EXISTS__');
                    }

                    $user = new User();
                    $user->name = trim((string) $request->country_name);
                    $user->email = trim((string) $request->country_email);
                    $user->phone = trim((string) $request->country_phone);
                    $user->password = Hash::make((string) $request->country_password);
                    $user->balance = 0;
                    $user->hold_balance = 0;
                    $user->level = 1;
                    $user->is_vip = 0;
                    $user->entry_level = 0;
                    $user->profile = 'https://queenlive.site/store/profile/default.png';
                    $user->date_wise_balance = 0;
                    $user->game_balance_date = date('Y-m-d');
                }

                if ($request->filled('country_phone')) {
                    $user->phone = trim((string) $request->country_phone);
                }
                if ($request->filled('country_password') && $user->exists) {
                    $user->password = Hash::make((string) $request->country_password);
                }
                $user->country_id = $countryId;
                $user->is_admin = 2;
                $user->role = 2;
                $user->is_bd_admin = 0;
                $user->is_app_admin = 0;
                $user->status = 1;
                $user->save();
            });
        } catch (\RuntimeException $e) {
            $messages = [
                '__COUNTRY_ADMIN_TARGET_NOT_FOUND__' => 'Country admin target user not found.',
                '__COUNTRY_ADMIN_NEW_REQUIRED__' => 'Name, email, and password are required for a new country admin.',
                '__COUNTRY_ADMIN_EMAIL_EXISTS__' => 'This country admin email already exists.',
            ];

            return $this->error($messages[$e->getMessage()] ?? 'Unable to save country admin.');
        }

        return $this->success('Country admin saved successfully.');
    }

    private function countryOptions()
    {
        return DB::table('countries')
            ->select('id', 'name')
            ->orderBy('id')
            ->get()
            ->map(function ($country) {
                return [
                    'id' => (int) $country->id,
                    'name' => (string) $country->name,
                ];
            })
            ->values()
            ->all();
    }

    private function validCountryId($countryId)
    {
        return DB::table('countries')->where('id', (int) $countryId)->exists();
    }

    public function emailChange(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $request->validate([
            'email' => ['required', 'email'],
            'user_id' => ['required', 'integer', 'min:1'],
        ]);

        $newEmail = $request->email;
        $newId = (int) $request->user_id;

        try {
            $result = DB::transaction(function () use ($newEmail, $newId) {
                $targetUser = User::where('id', $newId)->lockForUpdate()->first();
                $emailUser = User::where('email', $newEmail)->lockForUpdate()->first();

                if ($targetUser && $emailUser && $targetUser->id !== $emailUser->id) {
                    $tmpEmail = $targetUser->email;
                    $targetUser->email = $newEmail;
                    $emailUser->email = $tmpEmail;
                    $targetUser->save();
                    $emailUser->save();

                    return [
                        'result' => 'swapped',
                        'target_user' => $targetUser->fresh(),
                        'email_owner' => $emailUser->fresh(),
                    ];
                }

                if ($targetUser && (!$emailUser || $emailUser->id === $targetUser->id)) {
                    $targetUser->email = $newEmail;
                    $targetUser->save();

                    return [
                        'result' => 'updated',
                        'target_user' => $targetUser->fresh(),
                    ];
                }

                if (!$targetUser && !$emailUser) {
                    $pass = '123456';
                    $u = new User();
                    $u->id = $newId;
                    $u->name = 'Custom ID';
                    $u->device_id = '';
                    $u->imei_number = '';
                    $u->phone = $newId;
                    $u->email = $newEmail;
                    $u->level = 1;
                    $u->is_vip = 0;
                    $u->is_agency = 0;
                    $u->comment_mute_power = 0;
                    $u->sceen_short_power = 0;
                    $u->is_coin_protal_active = 0;
                    $u->kick_power = 0;
                    $u->is_host_id = 0;
                    $u->profile = 'https://queenlive.site/store/profile/default.png';
                    $u->balance = 0;
                    $u->entry_level = 0;
                    $u->role = 2;
                    $u->status = 1;
                    $u->password = Hash::make($pass);
                    $u->save();

                    return [
                        'result' => 'created',
                        'target_user' => $u->fresh(),
                        'default_password' => $pass,
                    ];
                }

                if (!$targetUser && $emailUser) {
                    throw new \RuntimeException('__EMAIL_TAKEN__');
                }

                throw new \RuntimeException('__UNKNOWN__');
            });
        } catch (\RuntimeException $e) {
            $messages = [
                '__EMAIL_TAKEN__' => 'This email already exists on another user. Use swap with that user ID.',
                '__UNKNOWN__' => 'Unable to process request due to an unexpected state.',
            ];
            return $this->error($messages[$e->getMessage()] ?? 'Something went wrong.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong.', '500');
        }

        return $this->success('Email change processed successfully.', [
            'data' => $result,
        ]);
    }

    public function emailChangeStore(Request $request)
    {
        return $this->emailChange($request);
    }

    public function newIdGive(Request $request)
    {
        return $this->emailChange($request);
    }

    private function shouldHandlePermissionUpdate(Request $request)
    {
        if (!$request->isMethod('post')) {
            return false;
        }

        return $this->extractTargetId($request) !== null
            && $this->extractPermissionKey($request) !== null
            && $this->extractPermissionValue($request) !== null;
    }

    private function updatePermission(Request $request)
    {
        $targetId = $this->extractTargetId($request);
        $permissionKey = $this->extractPermissionKey($request);
        $value = $this->extractPermissionValue($request);

        if ($targetId === null) {
            return response()->json([['code' => 400, 'message' => 'Target user required']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if ($permissionKey === null) {
            return response()->json([['code' => 400, 'message' => 'Permission key required']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if ($value === null) {
            return response()->json([['code' => 400, 'message' => 'Permission value required']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $column = self::PERMISSION_COLUMN_MAP[$permissionKey] ?? null;
        if ($column === null) {
            return response()->json([['code' => 404, 'message' => 'Permission key not supported']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $target = User::find($targetId);
        if (!$target) {
            return response()->json([['code' => 404, 'message' => 'User not found']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if (in_array($permissionKey, ['admin_status', 'make_admin', 'is_admin', 'admin_role', 'role'], true)) {
            $adminRole = (int) $value;
            if (!in_array($adminRole, [0, 1, 2, 3], true)) {
                return response()->json([['code' => 422, 'message' => 'Invalid admin role']], 200, [], JSON_UNESCAPED_UNICODE);
            }
            $target->is_admin = $adminRole;
            $target->role = $adminRole === 0 ? 2 : $adminRole;
            $target->status = 1;
            if ($adminRole === 1) {
                $target->is_bd_admin = 1;
                $target->is_app_admin = 1;
                $target->can_banned = 1;
                $target->can_call_cut = 1;
                $target->brd_off_power = 1;
                $target->comment_mute_power = 1;
                $target->kick_power = 1;
                $target->agora_access = 1;
            } elseif ($adminRole === 0) {
                $target->is_bd_admin = 0;
                $target->is_app_admin = 0;
            }
        } else {
            $target->{$column} = $value;
        }
        $target->save();

        return response()->json([['code' => 200, 'message' => 'Permission updated']], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function extractTargetId(Request $request)
    {
        foreach (['target_id', 'target_user_id', 'user_id'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                return (int) $value;
            }
        }

        return null;
    }

    private function extractPermissionKey(Request $request)
    {
        foreach (['action', 'action_key', 'key', 'name'] as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $normalized = $this->normalizePermissionKey($value);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        foreach (array_keys(self::PERMISSION_COLUMN_MAP) as $candidate) {
            if ($request->has($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractPermissionValue(Request $request)
    {
        foreach (['value', 'status', 'state', 'enabled', 'enable'] as $field) {
            $normalized = $this->normalizeToggleValue($request->input($field));
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $permissionKey = $this->extractPermissionKey($request);
        if ($permissionKey !== null && $request->has($permissionKey)) {
            return $this->normalizeToggleValue($request->input($permissionKey));
        }

        return null;
    }

    private function normalizePermissionKey($key)
    {
        $normalized = strtolower(trim((string) $key));
        return array_key_exists($normalized, self::PERMISSION_COLUMN_MAP) ? $normalized : null;
    }

    private function normalizeToggleValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'on', 'yes', 'main_admin', 'main-admin', 'admin'], true)) {
            return 1;
        }
        if (in_array($normalized, ['2', 'country_admin', 'country-admin', 'author'], true)) {
            return 2;
        }
        if (in_array($normalized, ['3', 'sub_admin', 'sub-admin', 'subadmin'], true)) {
            return 3;
        }
        if (in_array($normalized, ['0', 'false', 'off', 'no', 'user', 'normal'], true)) {
            return 0;
        }

        return null;
    }
}
