<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Services\V5\RoomEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * QueenLive v5 composite room-entry endpoint.
 *
 * POST /api/v5/room/{room_type}/{channel}/enter
 *
 * One round-trip replaces the legacy v4 sequence:
 *   - {video|audio|multi}_brd_user_data  (host_board + room meta)
 *   - agora_video_setting                 (RTC keys)
 *   - generate_live_token                 (Agora RTC token)
 *   - LiveKit join token (audio/multi)
 *   - user_data (own profile slice)
 *
 * Read-side only — additive, never mutates state. The mutation side
 * (audience_join writes, etc.) stays on the existing /join endpoint.
 */
class RoomEntryController extends Controller
{
    /** @var RoomEntryService */
    protected $svc;

    public function __construct(RoomEntryService $svc)
    {
        $this->svc = $svc;
    }

    public function enter(string $room_type, string $channel, Request $request)
    {
        $room_type = strtolower($room_type);
        if (!in_array($room_type, ['audio', 'video', 'multi'], true)) {
            return response()->json([
                'ok' => false,
                'envelope_version' => 1,
                'error' => 'invalid_room_type',
                'code' => 400,
            ], 400);
        }

        $channel = trim($channel);
        if ($channel === '') {
            return response()->json([
                'ok' => false,
                'envelope_version' => 1,
                'error' => 'channel_required',
                'code' => 400,
            ], 400);
        }

        $user = $request->user();
        $clientMetaVersion = $request->header('If-Meta-Version', null);

        try {
            $envelope = $this->svc->compose($room_type, $channel, $user, $clientMetaVersion);
            $status = $envelope['ok'] === false ? (isset($envelope['code']) ? (int) $envelope['code'] : 404) : 200;
            if ($status < 200 || $status > 599) {
                $status = 200;
            }
            return response()->json($envelope, $status);
        } catch (\Throwable $e) {
            Log::error('v5/room.enter failed', [
                'room_type' => $room_type,
                'channel' => $channel,
                'user_id' => $user ? $user->id : null,
                'err' => $e->getMessage(),
            ]);
            return response()->json([
                'ok' => false,
                'envelope_version' => 1,
                'error' => 'server_error',
                'code' => 500,
            ], 500);
        }
    }
}
