<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Admin\ProfileController as AdminProfileBase;
use App\Models\Agency;
use App\Models\EntryFrame;
use App\Models\MyBeg;
use App\Models\User;
use App\Models\VipList;
use App\Models\Withdraw;
use App\Support\MediaPathHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProfileController extends AdminProfileBase
{
    private const ACCESS_TOKEN = '0411f0028cfb768b3a3d96ac3aa37dw3e5';

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

    public function index(Request $request)
    {
        $actor = $this->authorizedActor($request);
        if (!$actor) {
            return $this->unauthorized();
        }

        $targetId = $request->target_id ?? $request->profile_user_id ?? $request->id ?? $request->search_id;
        if (empty($targetId)) {
            return response()->json([['message' => 'id required', 'code' => '400']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $user = User::find($targetId);
        if (!$user) {
            return response()->json([['message' => 'User Not Found!!', 'code' => '404']], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $agency = Agency::where('user_id', $user->id)->first();
        $portalRecharge = (float) $this->getPortalRecharge($user->id);
        $portalRecall = (float) $this->getPortalRecall($user->id);
        $portalTransfer = (float) $this->getPortalTransfer($user->id);
        $portalSendRows = $this->getPortalToPortalTransfer($user->id);
        $portalReceiveRows = $this->getPortalToPortalTransferReceived($user->id);
        $portalSend = (float) $portalSendRows->sum('amount');
        $portalReceive = (float) $portalReceiveRows->sum('amount');
        $approvedBalance = (float) $this->getApprovedBalance($user->id);
        $agencyConvert = (float) $this->getAgencyConvartBalance($user->id);
        $otherDevices = $this->otherDeviceUsers($user);
        $myBegs = MyBeg::where('user_id', $user->id)->orderByDesc('id')->get();
        $myVips = VipList::where('user_id', $user->id)->get();
        $rechargeHistory = $this->buildRechargeHistory($user->id);

        $payload = [
            'user' => $user,
            'agency' => $agency,
            'agency_info' => $this->getAgencyInfo($user->id),
            'protal_recharge' => $portalRecharge,
            'recall_protal_recharge' => $portalRecall,
            'protal_transfer' => $portalTransfer,
            'protal_recharge_details' => $this->getPortalRechargeDetails($user->id),
            'protal_transfer_details' => $this->getPortalTransferDetails($user->id),
            'recharge_history' => $rechargeHistory,
            'recharge_historys' => $rechargeHistory,
            'monthly_recharge_historys' => $this->getMonthlyRechargeHistory($user->id),
            'info' => $this->getHostData($user->id),
            'type' => $this->getHostType($user->id),
            'day_time_data' => $this->getDayTimeData($user->id),
            'convart_history' => $this->getConvartHistory($user->id),
            'agency_commisiion' => $this->getAgencyCommission($user->id),
            'approved_balance' => $approvedBalance,
            'agency_convart_balance' => $agencyConvert,
            'protal_to_protal_transfer' => $portalSendRows,
            'entry_frame_list' => $this->buildEntryFrameList($user, $myBegs),
            'my_begs' => $myBegs,
            'my_vips' => $myVips,
            'protal_to_protal_transfer_recived' => $portalReceiveRows,
            'check_host_balance' => $this->getHostBalanceCount($user->id),
            'game_history' => $this->getGameHistory($user->id),
            'sanding_historys' => $this->getGiftHistory($user->id),
            'reciving_historys' => $this->getReceivedGifts($user->id),
            'old_sum_sending_historys' => $this->getOldeSendingSum($user->id),
            'old_sum_reciving_historys' => $this->getOldeReceivedSum($user->id),
            'host_lists' => $agency ? $this->getHostLists($agency->code) : collect(),
            'summary' => [
                'portal_balance' => $portalRecharge + $portalReceive - $portalTransfer - $portalRecall - $portalSend,
                'agency_available_balance' => $approvedBalance - $agencyConvert,
                'live' => $this->buildLiveSummary($user),
                'other_devices_count' => $otherDevices->count(),
            ],
            'other_devices' => $otherDevices,
            'actions' => $this->buildActionFlags($user, $myVips->count(), $myBegs->count(), $otherDevices->isNotEmpty()),
            'meta' => [
                'web_match_url' => url('id_search?id=' . $user->id),
                'actor_id' => (string) $actor->id,
                'target_id' => (string) $user->id,
            ],
        ];

        $payload = $this->normalizeMediaPayload($payload);

        return response()->json([[
            'message' => 'Profile Index Data Show Successfully',
            'code' => '200',
            'data' => $payload,
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function otherDeviceUsers(User $user)
    {
        return User::where('id', '!=', $user->id)
            ->where(function ($query) use ($user) {
                if (!empty($user->imei_number)) {
                    $query->orWhere('imei_number', $user->imei_number);
                }
                if (!empty($user->device_id)) {
                    $query->orWhere('device_id', $user->device_id);
                }
            })
            ->get(['id', 'name', 'profile', 'device_id', 'imei_number']);
    }

    private function buildLiveSummary(User $user)
    {
        $type = $this->getHostType($user->id);
        if (!$type) {
            return [
                'hosting_type' => null,
                'hosting_type_label' => null,
                'running_day_count' => 0,
                'total_duration' => '00:00:00',
                'monthly_point_collect' => 0,
                'monthly_total_withdraw' => 0,
                'previous_points' => (float) ($user->previous_coin ?? 0),
                'current_points' => (float) ($user->previous_coin ?? 0),
            ];
        }

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        $dayTimeRows = DB::table('day_times')
            ->where('user_id', $user->id)
            ->where('brd_type', $type->hosting_type)
            ->where('day_times', '>', '00:14:59')
            ->select('live_time', 'day_times')
            ->orderBy('live_time')
            ->get();

        $totalDurationSeconds = 0;
        $perDateDuration = [];

        foreach ($dayTimeRows as $row) {
            $seconds = $this->durationToSeconds($row->day_times);
            $totalDurationSeconds += $seconds;
            $dateKey = Carbon::parse($row->live_time)->toDateString();
            $perDateDuration[$dateKey] = ($perDateDuration[$dateKey] ?? 0) + $seconds;
        }

        $runningDayCount = 0;
        foreach ($perDateDuration as $seconds) {
            if ($seconds >= 3600) {
                $runningDayCount++;
            }
        }

        $totalCoin = (float) DB::table('gifts')
            ->where('reciever_id', $user->id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('value');

        $totalWithdraw = (float) Withdraw::where('host_id', $user->id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('total');

        $previousPoints = (float) ($user->previous_coin ?? 0);

        return [
            'hosting_type' => (int) $type->hosting_type,
            'hosting_type_label' => (int) $type->hosting_type === 2 ? 'Video' : 'Audio',
            'running_day_count' => $runningDayCount,
            'total_duration' => $this->secondsToDuration($totalDurationSeconds),
            'monthly_point_collect' => $totalCoin,
            'monthly_total_withdraw' => $totalWithdraw,
            'previous_points' => $previousPoints,
            'current_points' => $totalCoin + $previousPoints - $totalWithdraw,
        ];
    }

    private function buildActionFlags(User $user, $vipCount, $frameCount, $hasOtherDevices)
    {
        return [
            'clear_other_device_ids_available' => $hasOtherDevices,
            'top_position' => (int) ($user->prosss_top ?? 0),
            'prosss_top' => (int) ($user->prosss_top ?? 0),
            'board_off_power' => (int) ($user->brd_off_power ?? 0),
            'brd_off_power' => (int) ($user->brd_off_power ?? 0),
            'screenshot_power' => (int) ($user->sceen_short_power ?? 0),
            'sceen_short_power' => (int) ($user->sceen_short_power ?? 0),
            'kick_power' => (int) ($user->kick_power ?? 0),
            'comment_mute_power' => (int) ($user->comment_mute_power ?? 0),
            'invisible_power' => (int) ($user->is_invisible ?? 0),
            'is_invisible' => (int) ($user->is_invisible ?? 0),
            'withdraw_active' => (int) ($user->withdraw_active ?? 0),
            'agora_access' => (int) ($user->agora_access ?? 0),
            'host_status' => (int) ($user->is_host_id ?? 0),
            'host_active' => (int) ($user->is_host_id ?? 0),
            'is_host_id' => (int) ($user->is_host_id ?? 0),
            'portal_status' => (int) ($user->is_coin_protal_active ?? 0),
            'portal_active' => (int) ($user->is_coin_protal_active ?? 0),
            'is_coin_protal_active' => (int) ($user->is_coin_protal_active ?? 0),
            'official_id_status' => (int) ($user->is_official_id ?? 0),
            'official_id_active' => (int) ($user->is_official_id ?? 0),
            'is_official_id' => (int) ($user->is_official_id ?? 0),
            'official_frame_active' => (int) ($user->is_official_frame ?? 0),
            'is_official_frame' => (int) ($user->is_official_frame ?? 0),
            'admin_frame_active' => (int) ($user->is_admin_frame ?? 0),
            'is_admin_frame' => (int) ($user->is_admin_frame ?? 0),
            'vip_active' => (int) ($user->is_vip ?? 0),
            'is_vip' => (int) ($user->is_vip ?? 0),
            'lock_brd_entry' => (int) ($user->lock_brd_entry ?? 0),
            'auto_lock_status' => (int) ($user->auto_lock_status ?? 0),
            'can_banned' => (int) ($user->can_banned ?? 0),
            'can_call_cut' => (int) ($user->can_call_cut ?? 0),
            'is_app_admin' => (int) ($user->is_app_admin ?? 0),
            'vip_count' => (int) $vipCount,
            'frame_count' => (int) $frameCount,
        ];
    }

    private function buildEntryFrameList(User $user, $myBegs)
    {
        if ($myBegs->isEmpty()) {
            return [];
        }

        $entryFrames = EntryFrame::whereIn('id', $myBegs->pluck('store_id')->filter()->unique()->values())->get()->keyBy('id');

        return $myBegs->map(function ($item) use ($entryFrames) {
            $frame = $entryFrames->get((int) ($item->store_id ?? 0));
            $typeValue = $frame->type ?? $item->type ?? null;

            return [
                'id' => (int) $item->id,
                'store_id' => $item->store_id !== null ? (int) $item->store_id : null,
                'name' => $item->name ?: optional($frame)->name,
                'title' => $item->name ?: optional($frame)->name,
                'image' => MediaPathHelper::publicUrl($item->image ?: optional($frame)->image),
                'effect' => $item->effect ?: optional($frame)->effect,
                'type' => $typeValue,
                'category' => $typeValue,
                'price' => $frame ? $frame->price : null,
                'value' => $frame ? $frame->price : null,
                'status' => (int) ($item->status ?? 0),
                'active_time' => $item->active_time,
                'expire_time' => $item->expaire_time,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        })->values()->all();
    }

    private function buildRechargeHistory($userId)
    {
        return $this->getRechargeHistory($userId)->map(function ($row) {
            $status = property_exists($row, 'status') && $row->status !== null ? (string) $row->status : 'success';

            return [
                'id' => (int) $row->id,
                'amount' => $row->amount !== null ? (float) $row->amount : null,
                'date' => $row->date ?? optional($row->created_at)->toDateTimeString(),
                'created_at' => $row->created_at,
                'method' => $row->payment_method ?? null,
                'payment_method' => $row->payment_method ?? null,
                'transaction_id' => $row->trxid ?? null,
                'order_id' => $row->trxid ?? null,
                'status' => $status,
            ];
        })->values()->all();
    }

    private function normalizeMediaPayload($value)
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->map(function ($item) {
                return $this->normalizeMediaPayload($item);
            })->values();
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if (is_string($item) && $this->isImageLikeKey($key)) {
                    $value[$key] = MediaPathHelper::publicUrl($item);
                    continue;
                }

                $value[$key] = $this->normalizeMediaPayload($item);
            }

            return $value;
        }

        if (is_object($value)) {
            foreach ($value as $key => $item) {
                if (is_string($item) && $this->isImageLikeKey($key)) {
                    $value->{$key} = MediaPathHelper::publicUrl($item);
                    continue;
                }

                $value->{$key} = $this->normalizeMediaPayload($item);
            }
        }

        return $value;
    }

    private function isImageLikeKey($key)
    {
        return in_array((string) $key, ['image', 'profile', 'logo', 'photo_id', 'selfie', 'frame_image'], true);
    }

    private function durationToSeconds($duration)
    {
        $parts = array_map('intval', explode(':', (string) $duration));
        $parts = array_pad($parts, 3, 0);
        return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
    }

    private function secondsToDuration($seconds)
    {
        $seconds = max(0, (int) $seconds);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
