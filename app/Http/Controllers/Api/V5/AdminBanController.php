<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BanDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * V5 admin ban management (Boss 2026-07-03). Mirrors the web admin-panel ban
 * system (Admin\BanController) for the in-app "Banned" menu shown to apps-admins
 * and officials. Actor authority is enforced on every action.
 *
 * Ban types (same as the panel):
 *   A -> permanent + device ban (BanDevice on the user's imei_number)
 *   B -> 30 days      C -> 24 hours      D -> 6 hours
 */
class AdminBanController extends Controller
{
    private const ACCESS_TOKEN = '0411f0028cfb768b3a3d96ac3aa37dw3e5';

    /** Resolve + authorize the acting admin/official. Returns the actor User or null. */
    private function authorizedActor(Request $request)
    {
        if ($request->access_token !== self::ACCESS_TOKEN) {
            return null;
        }
        $actorId = $request->actor_id ?? $request->admin_id ?? $request->user_id;
        if (empty($actorId)) {
            return null;
        }
        $actor = User::find($actorId);
        if (!$actor) {
            return null;
        }
        $isAdmin = (int) ($actor->is_admin ?? 0) >= 1;
        $isBdAdmin = (int) ($actor->is_bd_admin ?? 0) === 1;
        $isOfficial = (int) ($actor->is_official_id ?? 0) !== 0;
        return ($isAdmin || $isBdAdmin || $isOfficial) ? $actor : null;
    }

    private function unauthorized()
    {
        return response()->json([['message' => 'Unauthorized', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function banMessageFor($type)
    {
        $map = [
            'A' => 'You Are Permanently Banned. Violation Rules A',
            'B' => 'Your ID Banned For One Month. Violation Rules B',
            'C' => 'Your ID Banned For 24 Hours. Violation Rules C',
            'D' => 'Your ID Banned For 6 Hours. Violation Rules D',
        ];
        return $map[$type] ?? 'User is banned.';
    }

    /** GET v5/admin/banned_list - banned users (optional search). */
    public function bannedList(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        $search = trim((string) $request->search);
        $query = User::where('status', 0)->whereNotNull('ban_type');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }
        $rows = $query->orderByDesc('open_time')
            ->limit(200)
            ->get(['id', 'name', 'profile', 'level', 'ban_type', 'ban_proved', 'open_time', 'ban_by']);
        return response()->json([[
            'message' => 'OK',
            'code' => '200',
            'data' => $rows,
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /** GET v5/admin/user_search - lookup a user to ban by id/name. */
    public function userSearch(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        $search = trim((string) $request->search);
        if ($search === '') {
            return response()->json([['message' => 'OK', 'code' => '200', 'data' => []]], 200, [], JSON_UNESCAPED_UNICODE);
        }
        $users = User::where('id', 'LIKE', "%{$search}%")
            ->orWhere('name', 'LIKE', "%{$search}%")
            ->limit(15)
            ->get(['id', 'name', 'email', 'phone', 'balance', 'profile', 'level', 'ban_type', 'status']);
        return response()->json([['message' => 'OK', 'code' => '200', 'data' => $users]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /** POST v5/admin/ban_user - ban a user. Mirrors Admin\BanController@Active. */
    public function banUser(Request $request)
    {
        $actor = $this->authorizedActor($request);
        if (!$actor) {
            return $this->unauthorized();
        }
        $targetId = $request->target_id ?? $request->id_number;
        $banType = strtoupper(trim((string) $request->ban_type));
        if (empty($targetId) || !in_array($banType, ['A', 'B', 'C', 'D'], true)) {
            return response()->json([['message' => 'target_id and valid ban_type (A/B/C/D) required', 'code' => '400']], 200, [], JSON_UNESCAPED_UNICODE);
        }
        $data = User::find($targetId);
        if (!$data) {
            return response()->json([['message' => 'User not found', 'code' => '404']], 200, [], JSON_UNESCAPED_UNICODE);
        }
        // Never allow banning another admin/official from the in-app tool.
        if ((int) ($data->is_admin ?? 0) >= 1 || (int) ($data->is_bd_admin ?? 0) === 1 || (int) ($data->is_official_id ?? 0) !== 0) {
            return response()->json([['message' => 'Cannot ban an admin / official', 'code' => '403']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Optional base64 proof image (parity with the panel's file upload).
        $provedUrl = null;
        $proofB64 = $request->proved_base64 ?? $request->proof;
        if (!empty($proofB64) && is_string($proofB64)) {
            try {
                $clean = preg_replace('#^data:image/[a-zA-Z0-9.+-]+;base64,#', '', $proofB64);
                $bin = base64_decode($clean, true);
                if ($bin !== false && strlen($bin) <= 5 * 1024 * 1024) {
                    $dir = base_path('store/bannedproved');
                    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                    $name = uniqid('ban_') . '.jpg';
                    file_put_contents($dir . '/' . $name, $bin);
                    $provedUrl = 'store/bannedproved/' . $name;
                }
            } catch (\Throwable $e) { $provedUrl = null; }
        }

        // Also accept a multipart evidence image (app sends field "image";
        // the web panel uses "proved"). Stored the same way as ban_proved.
        if ($provedUrl === null) {
            $file = $request->file('image') ?: $request->file('proved');
            if ($file) {
                $dir = base_path('store/bannedproved');
                if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $name = uniqid('ban_') . '.' . $ext;
                try { $file->move($dir, $name); $provedUrl = 'store/bannedproved/' . $name; }
                catch (\Throwable $e) { $provedUrl = null; }
            }
        }

        $data->is_invisible = 0;
        $data->status = 0;
        $data->ban_type = $banType;
        if ($provedUrl !== null) { $data->ban_proved = $provedUrl; }
        $data->ban_by = $actor->id;

        if ($banType === 'A') {
            if (!empty($data->imei_number) && !BanDevice::where('device_id', $data->imei_number)->exists()) {
                $device = new BanDevice;
                $device->device_id = $data->imei_number;
                $device->save();
            }
            $data->open_time = null;
        } elseif ($banType === 'B') {
            $data->open_time = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
        } elseif ($banType === 'C') {
            $data->open_time = Carbon::now()->addDay()->format('Y-m-d H:i:s');
        } else {
            $data->open_time = Carbon::now()->addHours(6)->format('Y-m-d H:i:s');
        }
        $data->save();

        return response()->json([[
            'message' => 'ID Banned Successfully',
            'code' => '200',
            'target_id' => (string) $data->id,
            'ban_type' => $banType,
            'ban_message' => $this->banMessageFor($banType),
            'ban_proved' => $provedUrl,
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /** POST v5/admin/unban_user - reactivate a banned user. Mirrors Admin\BanController@Reject. */
    public function unbanUser(Request $request)
    {
        $actor = $this->authorizedActor($request);
        if (!$actor) {
            return $this->unauthorized();
        }
        $targetId = $request->target_id ?? $request->id;
        if (empty($targetId)) {
            return response()->json([['message' => 'target_id required', 'code' => '400']], 200, [], JSON_UNESCAPED_UNICODE);
        }
        $data = User::find($targetId);
        if (!$data) {
            return response()->json([['message' => 'User not found', 'code' => '404']], 200, [], JSON_UNESCAPED_UNICODE);
        }
        $imei = $data->imei_number;
        $data->status = 1;
        $data->ban_proved = null;
        $data->ban_type = null;
        $data->ban_by = null;
        $data->open_time = null;
        $data->save();
        if (!empty($imei)) {
            BanDevice::where('device_id', $imei)->delete();
        }
        return response()->json([[
            'message' => 'ID Activated Successfully',
            'code' => '200',
            'target_id' => (string) $data->id,
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
