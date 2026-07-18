<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agency;
use Hash;
use DB;
use Auth;
class UserController extends Controller
{
    private const AGENCY_CODE_START = 1000;

    public function Info($id)
    {
        $user = User::find(trim((string) $id));
        $next_agency_code = $this->nextAgencyCode();

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'user' => null,
                'next_agency_code' => $next_agency_code,
            ]);
        }

        return response()->json(['success' => 'User Find','user'=>$user,'next_agency_code'=>$next_agency_code]);
    }

    private function nextAgencyCode()
    {
        $latest = Agency::query()
            ->whereNotNull('code')
            ->orderByRaw('CAST(code AS UNSIGNED) DESC')
            ->first();

        $highestCode = $latest ? max((int) $latest->code, self::AGENCY_CODE_START - 1) : self::AGENCY_CODE_START - 1;
        $next = $highestCode + 1;
        while (Agency::where('code', (string) $next)->exists()) {
            $next++;
        }

        return (string) $next;
    }
    public function UserEmailChange()
    {
        return view('backend.user.user_details_change');
    }

    public function AdminRoleStore(Request $request)
    {
        $request->validate([
            'admin_role_target_user' => 'required|string|max:190',
            'admin_role' => 'required|integer|in:0,1,2,3',
        ]);

        $target = trim((string) $request->admin_role_target_user);
        $role = (int) $request->admin_role;
        $user = User::where('id', $target)->orWhere('email', $target)->first();

        if (!$user) {
            return back()->withInput()->with([
                'messege' => 'Admin role target user not found.',
                'alert-type' => 'error',
            ]);
        }

        if ((int) $user->id === (int) Auth::id() && (int) $user->is_admin === 1 && $role !== 1) {
            return back()->withInput()->with([
                'messege' => 'You cannot remove your own main admin access.',
                'alert-type' => 'error',
            ]);
        }

        $user->is_admin = $role;
        $user->role = $role === 0 ? 2 : $role;
        $user->status = 1;

        if ($role === 1) {
            $user->is_bd_admin = 1;
            $user->is_app_admin = 1;
            $user->can_banned = 1;
            $user->can_call_cut = 1;
            $user->brd_off_power = 1;
            $user->comment_mute_power = 1;
            $user->kick_power = 1;
            $user->agora_access = 1;
        } elseif ($role === 0) {
            $user->is_bd_admin = 0;
            $user->is_app_admin = 0;
        }

        $user->save();
        \App\RedisCache\CacheClearHelperFromModelAuto::clearUserCaches($user->id, 'admin_role_updated');

        $labels = [0 => 'Normal User', 1 => 'Main Admin', 2 => 'Country Admin', 3 => 'Sub Admin'];
        return back()->with([
            'messege' => 'Admin role changed to ' . $labels[$role] . ' for user ' . $user->id . '.',
            'alert-type' => 'success',
        ]);
    }
    public function AdminPermissionStore(Request $request)
    {
        $request->validate([
            'admin_permission_target_user' => 'required|string|max:190',
            'permissions' => 'required|array',
        ]);

        $target = trim((string) $request->admin_permission_target_user);
        $user = User::where('id', $target)->orWhere('email', $target)->first();
        if (!$user) {
            return back()->withInput()->with([
                'messege' => 'Permission target user not found.',
                'alert-type' => 'error',
            ]);
        }

        $allowed = [
            'agora_access' => 'Agora Access',
            'brd_off_power' => 'Board Off Power',
            'sceen_short_power' => 'Screenshot Power',
            'kick_power' => 'Kick Power',
            'comment_mute_power' => 'Comment Mute Power',
            'is_invisible' => 'Invisible Power',
            'withdraw_active' => 'Withdraw Active',
            'is_host_id' => 'Host Active',
            'is_coin_protal_active' => 'Portal Active',
            'is_official_id' => 'Official ID',
            'is_official_frame' => 'Official Frame',
            'is_admin_frame' => 'Admin Frame',
            'lock_brd_entry' => 'Lock Room Entry',
            'auto_lock_status' => 'Auto Lock Status',
            'can_banned' => 'Can Ban',
            'can_call_cut' => 'Can Cut Call',
            'is_app_admin' => 'App Admin Flag',
            'is_bd_admin' => 'BD Admin Flag',
        ];

        $changed = [];
        foreach ((array) $request->input('permissions', []) as $column => $value) {
            if (!array_key_exists($column, $allowed) || $value === '' || $value === null) {
                continue;
            }
            $normalized = (int) $value === 1 ? 1 : 0;
            $user->{$column} = $normalized;
            $changed[] = $allowed[$column] . '=' . $normalized;
        }

        if (empty($changed)) {
            return back()->withInput()->with([
                'messege' => 'Select at least one permission to update.',
                'alert-type' => 'error',
            ]);
        }

        $user->status = 1;
        $user->save();
        \App\RedisCache\CacheClearHelperFromModelAuto::clearUserCaches($user->id, 'admin_permissions_updated');

        return back()->with([
            'messege' => 'Permission system updated for user ' . $user->id . ': ' . implode(', ', $changed),
            'alert-type' => 'success',
        ]);
    }
    public function CountryAdminStore(Request $request)
    {
        $request->validate([
            'country_target_user' => 'nullable|string|max:190',
            'country_id' => 'required|integer|in:1,2,3',
            'country_name' => 'nullable|string|max:190',
            'country_email' => 'nullable|email|max:190',
            'country_phone' => 'nullable|string|max:50',
            'country_password' => 'nullable|string|min:6',
        ]);

        $countryId = (int) $request->country_id;
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
                $user->status = 1;
                $user->save();
            });
        } catch (\RuntimeException $e) {
            $messages = [
                '__COUNTRY_ADMIN_TARGET_NOT_FOUND__' => 'Country admin target user not found.',
                '__COUNTRY_ADMIN_NEW_REQUIRED__' => 'Name, email, and password are required for a new country admin.',
                '__COUNTRY_ADMIN_EMAIL_EXISTS__' => 'This country admin email already exists.',
            ];

            return back()->withInput()->with([
                'messege' => $messages[$e->getMessage()] ?? 'Unable to save country admin.',
                'alert-type' => 'error',
            ]);
        }

        return back()->with([
            'messege' => 'Country admin saved successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function UserEmailChangeStore(Request $request)
    {
        $new_email = $request->email;
        $new_id = $request->user_id;
        
        // Find target user by ID
        $is_id_exist = User::find($new_id);
        
        // Find user who already has this email (if any)
        $is_email_exist = User::where('email', $new_email)->first();
        
        if ($is_id_exist) {
            if ($is_email_exist) {
                $tamp=$is_email_exist->email;
                $is_email_exist->email=$is_email_exist->id.'@bdlive.com';
                $is_email_exist->save();
                $is_id_exist->email = $new_email;
                $is_id_exist->save();
                
            } else {
                // Case: email does not exist anywhere, safe to update
                $is_id_exist->email = $new_email;
                $is_id_exist->save();
            }
        } else {
           $lastId = User::latest('id')->value('id');
            $pass = 123456;
            
            // Create new user using mass assignment
            $new_user = User::create([
                
                'name' => $name,
                'device_id' => '',
                'imei_number' => '',
                'phone' => $lastId + 1,
                'email' => $request->email,
                'level' => 1,
                'is_vip' => 0,
                'is_agency' => 0,
                'comment_mute_power' => 0,
                'sceen_short_power' => 0,
                'is_coin_protal_active' => 0,
                'kick_power' => 0,
                'is_host_id' => 0,
                'profile' => 'https://queenlive.site/store/profile/default.png',
                'balance' => 0,
                'entry_level' => 0,
                'role' => 2,
                'status' => 1,
                'password' => Hash::make($pass),
            ]);
        }
        $notification=array(
                'messege'=>'User Change Successfully! ',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
    }
    public function newIdGive(Request $request)
{
    $request->validate([
        'email'   => ['required', 'email'],
        'user_id' => ['required', 'integer', 'min:1'],
    ]);

    $newEmail = $request->email;
    $newId    = (int) $request->user_id;

    try {
        DB::transaction(function () use ($newEmail, $newId, $request) {

            // Lock potentially conflicting rows to prevent races
            $targetUser = User::where('id', $newId)->lockForUpdate()->first();       // user for newId (if any)
            $emailUser  = User::where('email', $newEmail)->lockForUpdate()->first();  // user holding newEmail (if any)

            if ($targetUser && $emailUser && $targetUser->id !== $emailUser->id) {
                // --- SWAP CASE: id exists & email belongs to someone else ---
                $tmpEmail = $targetUser->email;

                $targetUser->email = $newEmail;
                $emailUser->email  = $tmpEmail;

                $targetUser->save();
                $emailUser->save();

                // early return inside transaction closure by throwing known exception or set a flag
                throw new \RuntimeException('__SWAPPED__');
            }

            if ($targetUser && (!$emailUser || $emailUser->id === $targetUser->id)) {
                // --- UPDATE CASE: id exists and email is unused OR already on same user ---
                $targetUser->email = $newEmail;
                $targetUser->save();
                throw new \RuntimeException('__UPDATED__');
            }

            if (!$targetUser && !$emailUser) {
                // --- CREATE CASE: id free AND email free ---
                // (ensure 'id' is fillable if you manually set it; otherwise, rethink custom IDs)
                $pass = '123456';

                $u = new User();
                $u->id                    = $newId;             // explicit custom ID
                $u->name                  = 'Custom ID';
                $u->device_id             = '';
                $u->imei_number           = '';
                $u->phone                 = $newId;             // check uniqueness!
                $u->email                 = $newEmail;
                $u->level                 = 1;
                $u->is_vip                = 0;
                $u->is_agency             = 0;
                $u->comment_mute_power    = 0;
                $u->sceen_short_power     = 0;                  // consider renaming column later
                $u->is_coin_protal_active = 0;                  // consider renaming column later
                $u->kick_power            = 0;
                $u->is_host_id            = 0;
                $u->profile               = 'https://queenlive.site/store/profile/default.png';
                $u->balance               = 0;
                $u->entry_level           = 0;
                $u->role                  = 2;
                $u->status                = 1;
                $u->password              = Hash::make($pass);
        
                $u->save();

                throw new \RuntimeException('__CREATED__');
            }

            if (!$targetUser && $emailUser) {
                // ID free but email taken by someone else -> cannot create; suggest swap with that user ID
                throw new \RuntimeException('__EMAIL_TAKEN__');
            }

            if ($targetUser && !$emailUser) {
                // ID exists but email is new -> just set it above, but we would have hit UPDATE CASE
                // This branch is unreachable due to earlier conditions; kept for clarity.
            }

            throw new \RuntimeException('__UNKNOWN__');
        });
    } catch (\RuntimeException $e) {
        $code = $e->getMessage();

        if ($code === '__SWAPPED__') {
            return back()->with([
                'messege'    => 'Emails swapped successfully.',
                'alert-type' => 'success',
            ]);
        }
        if ($code === '__UPDATED__') {
            return back()->with([
                'messege'    => 'Email updated successfully.',
                'alert-type' => 'success',
            ]);
        }
        if ($code === '__CREATED__') {
            return back()->with([
                'messege'    => 'ID created successfully!',
                'alert-type' => 'success',
            ]);
        }
        if ($code === '__EMAIL_TAKEN__') {
            return back()->with([
                'messege'    => 'This email already exists on another user. Use swap with that user ID.',
                'alert-type' => 'error',
            ]);
        }
        if ($code === '__UNKNOWN__') {
            return back()->with([
                'messege'    => 'Unable to process request due to an unexpected state.',
                'alert-type' => 'error',
            ]);
        }
        // If it's not our control-flow exception, rethrow to be logged
        throw $e;
    } catch (\Throwable $e) {
        // Log the error for debugging
        report($e);
        return back()->with([
            'messege'    => 'Something went wrong.',
            'alert-type' => 'error',
        ]);
    }
}
    // public function NewIDGive(Request $request){
    //     $new_email = $request->email;
    //     $new_id = $request->user_id;
    //     // Find target user by ID
    //     $is_id_exist = User::find($new_id);
        
    //     // Find user who already has this email (if any)
    //     $is_email_exist = User::where('email', $new_email)->first();
    //     if($is_email_exist){
    //          $notification=array(
    //             'messege'=>'This Email Already Exits',
    //             'alert-type'=>'error'
    //         );
    //         return Redirect()->back()->with($notification);
    //     }
    //     if($is_id_exist){
    //          $notification=array(
    //             'messege'=>'This Id Already Exits Please Use Sawp' ,
    //             'alert-type'=>'error'
    //         );
    //         return Redirect()->back()->with($notification);
    //     }
        
    //      $lastId = User::latest('id')->value('id');
    //      if($lastId>$new_id){
    //         $pass = 123456;
    //         $new_user = User::create([
                
    //             'id' => $new_id,
    //             'name' => 'Custom ID',
    //             'device_id' => '',
    //             'imei_number' => '',
    //             'phone' => $new_id + 1,
    //             'email' => $request->email,
    //             'level' => 1,
    //             'is_vip' => 0,
    //             'is_agency' => 0,
    //             'comment_mute_power' => 0,
    //             'sceen_short_power' => 0,
    //             'is_coin_protal_active' => 0,
    //             'kick_power' => 0,
    //             'is_host_id' => 0,
    //             'profile' => 'https://queenlive.site/store/profile/default.png',
    //             'balance' => 0,
    //             'entry_level' => 0,
    //             'role' => 2,
    //             'status' => 1,
    //             'password' => Hash::make($pass),
    //         ]);
    //          $notification=array(
    //             'messege'=>'ID Create Successfully!' ,
    //             'alert-type'=>'error'
    //         );
    //         return Redirect()->back()->with($notification);
    //      }else{
    //           $notification=array(
    //             'messege'=>'Something Wrong' ,
    //             'alert-type'=>'error'
    //         );
    //         return Redirect()->back()->with($notification);
    //      }
    // }
}
