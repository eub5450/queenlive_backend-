<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * Boss 2026-07-04: host liveness heartbeat.
 *
 * The host app beats v5/host_heartbeat every 60s while a room is live. The
 * RunTaskEvery4Seconds stale-host sweep ends any room whose (previously
 * beating) host stops beating — the backstop for the client-side 3-minute
 * background auto-end when the OS kills the host app outright.
 *
 * Keys (central Redis):
 *   bd:host_hb:{channel}       TTL 210s — host alive marker
 *   bd:host_hb_seen:{channel}  TTL 24h  — this room's host DOES heartbeat
 *                                          (legacy clients never set it, so the
 *                                          sweep never touches their rooms)
 */
class HostHeartbeatController extends Controller
{
    public function Beat(Request $request)
    {
        $access_token = $request->access_token;
        if ($access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode([
                ['message' => 'Unauthorized access_token', 'code' => '401'],
            ], JSON_UNESCAPED_UNICODE);
        }

        $channel = trim((string) ($request->channelName ?? $request->channel_name ?? ''));
        $hostId  = trim((string) ($request->host_id ?? $request->user_id ?? ''));
        if ($channel === '' || $hostId === '') {
            return json_encode([
                ['message' => 'channelName and host_id required', 'code' => '400'],
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $redis = Redis::connection();
            $redis->setex('bd:host_hb:' . $channel, 210, $hostId);
            $redis->setex('bd:host_hb_seen:' . $channel, 86400, '1');
        } catch (\Throwable $e) {
            return json_encode([
                ['message' => 'heartbeat store failed', 'code' => '500'],
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            ['message' => 'ok', 'code' => '200'],
        ], JSON_UNESCAPED_UNICODE);
    }
}
