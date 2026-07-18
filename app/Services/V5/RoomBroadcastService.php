<?php

namespace App\Services\V5;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * V5 unified room broadcast service.
 *
 * Single source of truth for the v5 envelope shape. Uses the V5-only
     * private-room realtime service and enforces the event_type whitelist for
     * every outgoing realtime payload.
 *
 * Boss 2026-06-28 consolidation: uses stable `event_type` strings and a
     * uniform envelope so audio/video/multi rooms share one Flutter dispatcher
     * and one /snapshot/ poll path.
 */
class RoomBroadcastService
{
    /**
     * Whitelist of v5 event types. Anything not on this list is rejected.
     */
    const EVENT_TYPES = [
        'room.member.entered',
        'room.member.left',
        'room.member.kicked',
        'room.comment.added',
        'room.comment.muted',
        'room.comment.unmuted',
        'room.comment.flying',
        'room.gift.sent',
        'room.magic_heart.sent',
        'room.fun_sticker.sent',
        'room.fanclub.updated',
        'room.gift.global',
        'room.cohost.requested',
        'room.cohost.accepted',
        'room.cohost.rejected',
        'room.cohost.cut',
        'room.cohost.kicked',
        'room.cohost.mute_changed',
        'room.cohost.invited',
        'room.seat.updated',
        'room.seat.emoji',
        'room.admin.added',
        'room.admin.removed',
        'room.snapshot',
        'room.ended',
        'audio.seat.updated',
        'audio.room.snapshot',
        'video.room.snapshot',
        'video.call.pending_list',
        'video.call.ended',
        'video.cohost.invited',
        'audio.call.pending_list',
        'audio.cohost.invited',
        'multi.call.updated',
        'multi.call.pending_list',
        'room.seat.locked',
        'room.seat.unlocked',
        'room.seat.count_changed',
        'room.seat.taken',
        'room.seat.vacated',
        'room.speaker.toggled',
        'room.super_mute',
        'room.reaction',
        'room.heart',
        'room.pk.updated',
        'room.pk.requested',
        'room.pk.accepted',
        'room.board.closed',
        'room.coin_bag.updated',
        'room.coin_bag.result',
        'game.banner.updated',
        'room.hourly_top.updated',
        'room.reward.updated',
        'room.share.invite',
    ];

    /**
     * Build the v5 envelope and dispatch via the V5 private realtime service.
     *
     * @param string $roomType    'audio' | 'video' | 'multi'
     * @param string $channelName Room channel name (host channel)
     * @param string $hostId      Host user id (as string)
     * @param string $eventType   Member of self::EVENT_TYPES
     * @param array  $payload     v5 event payload (event-type specific)
     * @param array  $context     Optional context: actor_user_id, target_user_id, event_id
     * @return array              The envelope that was dispatched
     */
    public function broadcast($roomType, $channelName, $hostId, $eventType, array $payload, array $context = [])
    {
        $roomType    = strtolower(trim((string) $roomType));
        $channelName = trim((string) $channelName);
        $hostId      = (string) $hostId;
        $eventType   = trim((string) $eventType);

        if (!in_array($roomType, ['audio', 'video', 'multi'], true)) {
            throw new \InvalidArgumentException("v5 broadcast: unknown room_type '{$roomType}'");
        }
        if ($channelName === '' || $hostId === '') {
            throw new \InvalidArgumentException('v5 broadcast: empty channel or host');
        }
        if (!in_array($eventType, self::EVENT_TYPES, true)) {
            throw new \InvalidArgumentException("v5 broadcast: unknown event_type '{$eventType}'");
        }
        // Sequence comes from the shared room state service so reconnect
        // snapshots and live events preserve monotonic room ordering.
        $seq = 0;
        try {
            $seq = (int) app(\App\Services\AudioRoom\AudioRoomStateService::class)
                ->nextEventSequence($channelName);
        } catch (\Throwable $t) {
            $seq = 0;
        }

        $eventId  = isset($context['event_id']) && trim((string) $context['event_id']) !== ''
            ? (string) $context['event_id']
            : (string) Str::uuid();
        $actorId  = array_key_exists('actor_user_id', $context) && $context['actor_user_id'] !== null
            ? (string) $context['actor_user_id']
            : null;
        $targetId = array_key_exists('target_user_id', $context) && $context['target_user_id'] !== null
            ? (string) $context['target_user_id']
            : null;
        $nowMs = (int) round(microtime(true) * 1000);
        $payload = array_merge([
            'room_id'      => $channelName,
            'room_type'    => $roomType,
            'roomType'     => $roomType,
            'channel'      => $channelName,
            'channelName'  => $channelName,
            'channel_name' => $channelName,
            'host_id'      => $hostId,
            'event_type'   => $eventType,
            'eventType'    => $eventType,
            'timestamp'    => $nowMs,
            'ts_ms'        => $nowMs,
        ], $payload);
        if ($actorId !== null && !array_key_exists('actor_user_id', $payload)) {
            $payload['actor_user_id'] = $actorId;
        }
        if ($targetId !== null && !array_key_exists('target_user_id', $payload)) {
            $payload['target_user_id'] = $targetId;
        }

        $envelope = [
            'event_id'       => $eventId,
            'room_id'        => $channelName,
            'room_type'      => $roomType,
            'roomType'       => $roomType,
            'channel'        => $channelName,
            'channelName'    => $channelName,
            'channel_name'   => $channelName,
            'host_id'        => $hostId,
            'event_type'     => $eventType,
            'eventType'      => $eventType,
            'actor_user_id'  => $actorId,
            'target_user_id' => $targetId,
            'payload'        => $payload,
            'seq'            => $seq,
            'ts'             => $nowMs,
            'ts_ms'          => $nowMs,
            'timestamp'      => $nowMs,
            'created_at'     => now()->toIso8601String(),
        ];

        try {
            app(\App\Services\V5\V5RoomRealtimeService::class)
                ->publish($envelope, [
                    'source'     => 'V5RoomBroadcastService',
                    'event_type' => $eventType,
                    'room_type'  => $roomType,
                ]);
        } catch (\Throwable $t) {
            Log::warning('V5 RoomBroadcastService dispatch failed', [
                'event_type' => $eventType,
                'room_type'  => $roomType,
                'channel'    => $channelName,
                'host_id'    => $hostId,
                'error'      => $t->getMessage(),
            ]);
            throw $t;
        }

        return $envelope;
    }
}
