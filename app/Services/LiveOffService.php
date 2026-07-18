<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Models\UserLive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Unified force-close-live-room entry point.
 *
 * Replaces the duplicated logic that previously lived in
 *   - Api\V4\SettingController::LiveOff (app path, gated on brd_off_power)
 *   - SubAdmin\DashbordController::LiveOff (web admin path, Firebase only)
 *
 * Single source of truth for ending a room:
 *   1. Delete the UserLive row.
 *   2. Insert a system comment ("Room ended by admin").
 *   3. Flush Redis feed caches covering audio + video + multi feeds.
 *   4. Broadcast bd_bdr_off (channel_type=103) to the room over the
 *      AudioRoomRealtimeService named-WS pipeline so newer clients close
 *      instantly (avoiding the 30s no-snapshot fallback timer).
 *
 * NO sleep().  Caller provides $source for log breadcrumbs.
 */
class LiveOffService
{
    /** @var string */
    private $prefix = 'queenlive:';

    /** @var string */
    private $unifiedLiveCachePrefix = 'live_list_v2_page_';

    /**
     * @param string   $channel       channelName of the room being closed
     * @param string   $hostId        user_id of the host whose room is being closed
     * @param int|null $actorUserId   user_id of the admin/subadmin invoking the action (null if N/A)
     * @param string   $source        e.g. 'app_setting' | 'subadmin_dashboard'
     *
     * @return array{ok:bool, message:string}
     */
    public function offRoom($channel, $hostId, $actorUserId, $source)
    {
        $channel = (string) $channel;
        $hostId  = (string) $hostId;

        $result = ['ok' => false, 'message' => 'Room close did nothing'];

        try {
            $live = UserLive::where('channelName', $channel)
                ->where('user_id', $hostId)
                ->first();

            // 1) Insert a system comment when we can resolve the host
            $hostUser = null;
            try {
                $hostUser = User::find($hostId);
            } catch (\Throwable $e) {
                Log::warning('LiveOffService: host lookup failed', [
                    'source' => $source, 'channel' => $channel, 'host' => $hostId,
                    'error' => $e->getMessage(),
                ]);
            }

            $actor = null;
            if ($actorUserId !== null) {
                try {
                    $actor = User::find($actorUserId);
                } catch (\Throwable $e) {
                    // non-fatal
                }
            }

            $hostName  = $hostUser->name ?? 'host';
            $actorName = $actor->name ?? 'Admin';
            $actorId   = $actor->id ?? 0;

            $commentMessage = "⚠️ Warning {$hostName}, আপনার লাইভ নিয়ম ভঙ্গের কারণে -- {$actorName} -{$actorId} -- অফিসিয়ালি অফ করে দিয়েছেন।";

            // Broadcast the comment first so audience sees the reason
            if ($actor) {
                $giftComment = [
                    'balance'         => strval($actor->balance),
                    'channelName'     => strval($channel),
                    'id'              => $actor->id,
                    'message'         => strval('@' . $commentMessage),
                    'level'           => strval($actor->level),
                    'name'            => strval($actor->name),
                    'profile'         => strval($actor->profile),
                    'is_vip'          => strval($actor->is_vip),
                    'frame'           => strval($actor->frame),
                    'is_official_id'  => strval($actor->is_official_id),
                    'is_agency'       => strval($actor->is_agency),
                    'is_host_id'      => strval($actor->is_host_id),
                    'comment_badge'   => strval($actor->comment_badge),
                    'type'            => 'message',
                ];

                $this->dispatchWebsocket([
                    array_merge($giftComment, [
                        'code'         => '200',
                        'channel_type' => '11',
                    ]),
                ], $source);
            }

            // Persist the system comment row (best-effort)
            try {
                $comment = new Comment;
                $comment->user_id     = $actorUserId ?: ($hostUser->id ?? 0);
                $comment->channelName = $channel;
                $comment->message     = $commentMessage;
                $comment->reciever_id = $hostUser->id ?? 0;
                $comment->type        = 'message';
                $comment->save();
            } catch (\Throwable $e) {
                Log::warning('LiveOffService: comment persist failed', [
                    'source' => $source, 'channel' => $channel, 'error' => $e->getMessage(),
                ]);
            }

            // 2) Broadcast bd_bdr_off (channel_type 103) so clients close instantly
            $list = [[
                'channelName' => $channel,
                'status'      => strval(0),
                'host_id'     => strval($hostId),
            ]];

            $this->dispatchWebsocket([[
                'message'      => 'bd_bdr_off',
                'channelName'  => $channel,
                'data'         => $list,
                'code'         => '200',
                'channel_type' => '103',
            ]], $source);

            // 3) Purge per-room cache + feed caches (audio + video + multi)
            $this->purgeRoomCaches($channel, $hostId);

            // 4) Delete the live row (mark room ended)
            if ($live) {
                $live->delete();
            }

            $result = ['ok' => true, 'message' => 'Room closed'];

            Log::info('LiveOffService: room closed', [
                'source' => $source,
                'channel' => $channel,
                'host' => $hostId,
                'actor' => $actorUserId,
            ]);
        } catch (\Throwable $e) {
            Log::error('LiveOffService: offRoom failed', [
                'source' => $source, 'channel' => $channel, 'host' => $hostId,
                'error' => $e->getMessage(),
            ]);
            $result = ['ok' => false, 'message' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * Flush every Redis key feeding audio/video/multi room feeds.
     */
    private function purgeRoomCaches($channel, $hostId)
    {
        $keys = [
            // per-room scoped cache (video legacy path)
            $this->prefix . "Video_Brd_Call_Details_{$hostId}_{$channel}",
            // feed caches covering audio (type_3), video (type_1), multi (type_2)
            $this->prefix . 'live_users_type_1',
            $this->prefix . 'live_users_type_2',
            $this->prefix . 'live_users_type_3',
            $this->prefix . 'live_frined_home',
            $this->prefix . 'live_top_list',
            $this->prefix . 'global_lives',
            $this->prefix . "followerLive_{$hostId}",
        ];

        for ($i = 1; $i <= 10; $i++) {
            $keys[] = $this->prefix . "live_list_page_{$i}";
            $keys[] = $this->prefix . $this->unifiedLiveCachePrefix . $i;
        }

        foreach ($keys as $key) {
            try {
                Redis::del($key);
            } catch (\Throwable $e) {
                Log::warning('LiveOffService: redis purge failed', [
                    'key' => $key, 'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Forward an envelope batch into the v5-aligned named-WS pipeline.
     */
    private function dispatchWebsocket(array $envelopes, $source)
    {
        try {
            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped($envelopes, [
                    'source' => 'LiveOffService:' . $source,
                ]);
        } catch (\Throwable $e) {
            Log::warning('LiveOffService: websocket dispatch failed', [
                'source' => $source, 'error' => $e->getMessage(),
            ]);
        }
    }
}
