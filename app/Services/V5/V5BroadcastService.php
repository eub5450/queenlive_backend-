<?php

namespace App\Services\V5;

use Illuminate\Support\Str;

/**
 * V5 unified room broadcast service.
 *
 * Wraps the existing AudioRoomRealtimeService (which despite the name handles
 * audio + video + multi rooms) and enforces the v5 envelope shape for every
 * outgoing realtime event.
 *
 * Boss 2026-06-28: replaces the channel_type integer zoo (11..103) with a
 * stable `event_type` string and a uniform envelope so audio/video/multi
 * rooms can share one Flutter dispatcher and one /snapshot/ poll path.
 */
class V5BroadcastService
{
    /**
     * Whitelist of v5 event types.
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
        'room.gift.global',
        'room.cohost.requested',
        'room.cohost.accepted',
        'room.cohost.rejected',
        'room.cohost.cut',
        'room.cohost.kicked',
        'room.cohost.mute_changed',
        'room.cohost.invited',
        'room.admin.added',
        'room.admin.removed',
        'room.snapshot',
        'room.ended',
    ];

    public function broadcast(
        $roomType,
        $channelName,
        $hostId,
        $eventType,
        array $payload,
        array $context = array()
    ) {
        $roomType    = strtolower(trim((string) $roomType));
        $channelName = trim((string) $channelName);
        $hostId      = (string) $hostId;
        $eventType   = $this->canonicalEventType(trim((string) $eventType));

        if (!in_array($roomType, ['audio', 'video', 'multi'], true)) {
            throw new \InvalidArgumentException("v5 broadcast: unknown room_type '$roomType'");
        }
        if ($channelName === '' || $hostId === '') {
            throw new \InvalidArgumentException("v5 broadcast: empty channel or host");
        }
        if (!in_array($eventType, self::EVENT_TYPES, true)) {
            throw new \InvalidArgumentException("v5 broadcast: unknown event_type '$eventType'");
        }

        $nowMs = (int) round(microtime(true) * 1000);
        $payload = array_merge(array(
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
        ), $payload);
        if (isset($context['actor_user_id']) && !array_key_exists('actor_user_id', $payload)) {
            $payload['actor_user_id'] = (string) $context['actor_user_id'];
        }
        if (isset($context['target_user_id']) && !array_key_exists('target_user_id', $payload)) {
            $payload['target_user_id'] = (string) $context['target_user_id'];
        }

        $envelope = array(
            'event_id'       => isset($context['event_id']) ? $context['event_id'] : (string) Str::uuid(),
            'room_id'        => $channelName,
            'room_type'      => $roomType,
            'roomType'       => $roomType,
            'channel'        => $channelName,
            'channelName'    => $channelName,
            'channel_name'   => $channelName,
            'host_id'        => $hostId,
            'event_type'     => $eventType,
            'eventType'      => $eventType,
            'actor_user_id'  => isset($context['actor_user_id'])  ? (string) $context['actor_user_id']  : null,
            'target_user_id' => isset($context['target_user_id']) ? (string) $context['target_user_id'] : null,
            'payload'        => $payload,
            'ts'             => $nowMs,
            'ts_ms'          => $nowMs,
            'timestamp'      => $nowMs,

            // Legacy fields so the existing AudioRoomRealtimeService routes the
            // payload to the correct private channel + named Pusher event.
            'message'        => $this->legacyMessageFor($eventType),
            'channel_type'   => $this->legacyChannelTypeFor($eventType),
        );

        try {
            $state = app(\App\Services\AudioRoom\AudioRoomStateService::class);
            $envelope['seq'] = $state->nextEventSequence($channelName);
        } catch (\Throwable $t) {
            $envelope['seq'] = 0;
        }

        try {
            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped(array($envelope), array(
                    'source'              => 'V5BroadcastService',
                    'resolved_event_type' => $eventType,
                ));
        } catch (\Throwable $t) {
            \Log::warning('V5 broadcast dispatch failed', array(
                'event_type' => $eventType,
                'channel'    => $channelName,
                'error'      => $t->getMessage(),
            ));
        }

        return $envelope;
    }

    /**
     * v5 event_type -> legacy `message` string for downstream compatibility.
     */
    protected function legacyMessageFor($eventType)
    {
        $map = array(
            'room.comment.added'        => 'bd_comment',
            'room.comment.muted'        => 'bd_comment_mute',
            'room.comment.unmuted'      => 'bd_comment_unmute',
            'room.comment.flying'       => 'bd_fly_comment',
            'room.gift.sent'            => 'bd_gift',
            'room.gift.global'          => 'bd_global_gift',
            'room.member.entered'       => 'bd_entry',
            'room.member.left'          => 'bd_leave',
            'room.member.kicked'        => 'bd_kick',
            'room.cohost.requested'     => 'bd_call_request',
            'room.cohost.accepted'      => 'bd_call_accept',
            'room.cohost.rejected'      => 'bd_call_reject',
            'room.cohost.cut'           => 'bd_call_cut',
            'room.cohost.kicked'        => 'bd_call_remove_by_host',
            'room.cohost.mute_changed'  => 'bd_call_mute',
            'room.cohost.invited'       => 'bd_cohost_invite',
            'room.snapshot'             => 'bd_room_snapshot',
            'room.ended'                => 'bd_bdr_off',
            'room.admin.added'          => 'bd_admin_grant',
            'room.admin.removed'        => 'bd_admin_revoke',
        );
        return isset($map[$eventType]) ? $map[$eventType] : $eventType;
    }

    protected function legacyChannelTypeFor($eventType)
    {
        $map = array(
            'room.comment.added'        => '11',
            'room.admin.added'          => '12',
            'room.admin.removed'        => '12',
            'room.comment.muted'        => '14',
            'room.member.entered'       => '15',
            'room.comment.flying'       => '16',
            'room.gift.sent'            => '24',
            'room.member.kicked'        => '20',
            'room.comment.unmuted'      => '21',
            'room.cohost.cut'           => '22',
            'room.cohost.mute_changed'  => '98',
            'room.gift.global'          => '88',
            'room.snapshot'             => '19',
            'room.ended'                => '103',
            'room.cohost.requested'     => '13',
            'room.cohost.accepted'      => '13',
            'room.cohost.rejected'      => '13',
        );
        return isset($map[$eventType]) ? $map[$eventType] : '0';
    }

    protected function canonicalEventType($eventType)
    {
        $aliases = array(
            'room.member.joined' => 'room.member.entered',
            'room.closed'        => 'room.ended',
            'room.admin.granted' => 'room.admin.added',
            'room.admin.revoked' => 'room.admin.removed',
        );

        return isset($aliases[$eventType]) ? $aliases[$eventType] : $eventType;
    }
}
