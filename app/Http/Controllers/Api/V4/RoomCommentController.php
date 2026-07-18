<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\UserLive;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomCommentController extends Controller
{
    public function Send(Request $request)
    {
        $token = $request->access_token;
        $channelName = trim($request->channelName ?? '');
        $userId = trim($request->user_id ?? '');
        $message = trim($request->message ?? '');
        $roomType = trim((string) ($request->room_type ?? $request->roomType ?? ''));
        $response = array();

        if ($token !== '0411f0028cfb768b3a3d96ac3aa37dw3e5') {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        if ($channelName === '' || $userId === '' || $message === '') {
            array_push($response, array('message' => 'Missing required fields', 'code' => '400'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        // The client sends the user's GLOBAL comment_badge (set on join), so a
        // "Top 1/2/3" pill can be stale or belong to another host's room. Recompute
        // the Top-gifter rank for THIS room's host so the Top pill is always correct;
        // Admin/Official/Merchant badges are left as-is. Safe: errors fall back to
        // the client value and the comment still broadcasts.
        $commentBadge = $this->resolveRoomCommentBadge(
            $channelName,
            $userId,
            $request->comment_badge ?? ''
        );

        try {
            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped([[
                    'event_type' => 'room.comment.created',
                    'eventType' => 'room.comment.created',
                    'channel_type' => '11',
                    'channelType' => '11',
                    'room_type' => $roomType,
                    'roomType' => $roomType,
                    'channelName' => $channelName,
                    'id' => $userId,
                    'message' => $message,
                    'name' => $request->name ?? '',
                    'profile' => $request->profile ?? '',
                    'level' => $request->level ?? '1',
                    'is_vip' => $request->is_vip ?? '0',
                    'frame' => $request->frame ?? '',
                    'is_official_id' => $request->is_official_id ?? '0',
                    'is_agency' => $request->is_agency ?? '0',
                    'is_host_id' => $request->is_host_id ?? '0',
                    'is_bd_admin' => $request->is_bd_admin ?? '0',
                    'room_admin_level' => $request->room_admin_level ?? '0',
                    'comment_badge' => $commentBadge,
                    'event_id' => $request->event_id ?? '',
                    'balance' => $request->balance ?? '0',
                    'type' => $request->type ?? 'message',
                ]], ['source' => 'RoomCommentController', 'room_type' => $roomType]);
        } catch (\Throwable $e) {
            // Client still shows its local optimistic comment if websocket fails.
        }

        array_push($response, array('message' => 'ok', 'code' => '200'));
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resolve the comment badge for the CURRENT room. The "Top 1/2/3" pill is
     * host-specific and time-sensitive, but the client only knows the user's
     * stale global badge, so recompute the Top rank against this room's host.
     * Admin/Official/Merchant badges (anything not "top") are passed through.
     * The host->id and host top-3 gifters are cached 60s to keep the comment
     * path cheap. Any failure falls back to the client-sent badge.
     */
    private function resolveRoomCommentBadge($channelName, $userId, $clientBadge)
    {
        try {
            $incoming = strtolower(trim((string) $clientBadge));
            // Only the Top pill is room/host-specific. Keep other badges as-is.
            if ($incoming !== '' && strncmp($incoming, 'top', 3) !== 0) {
                return $clientBadge;
            }

            $hostId = Cache::remember('queenlive:room_host:' . $channelName, 60, function () use ($channelName) {
                return optional(UserLive::where('channelName', $channelName)->first())->user_id;
            });
            if (empty($hostId)) {
                return '';
            }

            $topIds = Cache::remember('queenlive:room_top3:' . $hostId, 60, function () use ($hostId) {
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->endOfMonth()->format('Y-m-d');
                return DB::table('gifts')
                    ->select(DB::raw('SUM(value) as total_sum'), 'sander_id')
                    ->where('reciever_id', $hostId)
                    ->whereDate('date', '>=', $start)
                    ->whereDate('date', '<=', $end)
                    ->groupBy('sander_id')
                    ->havingRaw('SUM(value) > 0')
                    ->orderByDesc('total_sum')
                    ->limit(3)
                    ->pluck('sander_id')
                    ->map(function ($v) { return (string) $v; })
                    ->values()
                    ->all();
            });

            $idx = array_search((string) $userId, $topIds, true);
            // If the client claimed a Top badge but the user isn't currently
            // top-3 for this host, $idx is false and the stale Top pill is dropped.
            return $idx === false ? '' : ('Top ' . ($idx + 1));
        } catch (\Throwable $e) {
            return $clientBadge;
        }
    }
}
