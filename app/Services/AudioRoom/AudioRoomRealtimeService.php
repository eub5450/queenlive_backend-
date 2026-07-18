<?php

namespace App\Services\AudioRoom;

use App\Events\BDEvent;
use Illuminate\Support\Str;

class AudioRoomRealtimeService
{
    protected $stateService;
    protected $seatService;
    protected $keys;

    public function __construct(
        AudioRoomStateService $stateService,
        AudioRoomSeatService $seatService,
        AudioRoomRedisKeyService $keys
    ) {
        $this->stateService = $stateService;
        $this->seatService = $seatService;
        $this->keys = $keys;
    }

    public function broadcastLegacyWithRoomScoped($data, array $context = [])
    {
        $payloads = $this->normalizePayloads($data, $context);
        if (empty($payloads)) {
            return [];
        }

        // Preserve legacy bd_chat fanout until all clients are proven to consume
        // the room-scoped private channels directly.
        try {
            event(new BDEvent($payloads));
        } catch (\Throwable $th) {
            info('Legacy WebSocket dispatch failed: ' . $th->getMessage());
        }

        $publishedPayloads = [];
        foreach ($payloads as $payload) {
            $room = $this->keys->normalizeRoom($payload['channelName'] ?? $payload['room'] ?? '');
            $eventName = trim((string) ($payload['event_type'] ?? 'audio.room.updated'));
            if ($room === '' || $eventName === '') {
                continue;
            }

            $this->stateService->refreshRoomFromPayload($payload);
            $publishedPayload = $payload;
            $publishedPayloads[] = $publishedPayload;
            $this->publishNamedRoomEvent($publishedPayload, $room, $eventName, $context);
        }

        return $publishedPayloads;
    }

    public function normalizePayloads($data, array $context = [])
    {
        $payloads = [];

        if (is_array($data)) {
            $items = $this->isAssoc($data) ? [$data] : $data;
            foreach ($items as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $room = $this->keys->normalizeRoom($row['channelName'] ?? $row['channel_name'] ?? $row['room'] ?? '');
                $channelType = trim((string) ($row['channel_type'] ?? $row['channelType'] ?? ''));
                $eventType = $this->resolveEventType($row, $channelType);
                $signature = $this->buildIdempotencySignature($row, $room, $channelType, $eventType);

                if ($room !== '' && !$this->stateService->acquireIdempotencyLock($room, 'event', $signature)) {
                    continue;
                }

                $payloads[] = $this->enrichPayload($row, array_merge($context, [
                    'idempotency_key' => $signature,
                    'resolved_event_type' => $eventType,
                ]));
            }
        }

        return $payloads;
    }

    protected function enrichPayload(array $payload, array $context)
    {
        $room = $this->keys->normalizeRoom($payload['channelName'] ?? $payload['channel_name'] ?? $payload['room'] ?? '');
        $channelType = trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? ''));
        $eventType = trim((string) ($context['resolved_event_type'] ?? $this->resolveEventType($payload, $channelType)));
        if ($channelType === '') {
            $channelType = $this->resolveLegacyChannelType($eventType);
        }
        $eventSeq = $room !== '' ? $this->stateService->nextEventSequence($room) : 0;
        $now = now()->toIso8601String();
        $targetUserId = $this->seatService->targetUserIdFromPayload($payload);
        $actorUserId = $this->resolveActorUserId($payload, $context);

        $payload['room'] = $room;
        $payload['room_key'] = $room;
        $payload['event_seq'] = $eventSeq;
        $payload['event_type'] = $eventType;
        $payload['eventType'] = $eventType;
        $payload['eventName'] = $eventType;
        if ($channelType !== '') {
            $payload['channel_type'] = $channelType;
            $payload['channelType'] = $channelType;
        }
        $payload['created_at'] = $payload['created_at'] ?? $now;
        $payload['event_id'] = $payload['event_id'] ?? $this->buildEventId($room, $eventSeq);
        $payload['actor_user_id'] = $payload['actor_user_id'] ?? $actorUserId;
        $payload['target_user_id'] = $payload['target_user_id'] ?? $targetUserId;
        $payload['idempotency_key'] = $payload['idempotency_key'] ?? ($context['idempotency_key'] ?? null);
        $payload['event_source'] = $payload['event_source'] ?? ($context['source'] ?? 'audio_room_bridge');

        return $payload;
    }

    protected function publishNamedRoomEvent(array $payload, $room, $eventName, array $context = [])
    {
        try {
            $setting = \RedisCacheFunction::getSetting();
            $appKey = config('broadcasting.connections.pusher.key') ?: ($setting->key ?? '');
            $appSecret = config('broadcasting.connections.pusher.secret') ?: ($setting->secret ?? '');
            $appId = config('broadcasting.connections.pusher.app_id') ?: ($setting->app_id ?? '');
            $options = config('broadcasting.connections.pusher.options', []);
            if (empty($options['cluster']) && !empty($setting->cluster)) {
                $options['cluster'] = $setting->cluster;
            }
            if (!isset($options['useTLS'])) {
                $options['useTLS'] = true;
            }

            $channels = $this->shouldPublishLegacyBdChat($payload, $eventName, $context)
                ? [] /* RT-P3-12: public bd_chat mirror disabled; private channels only */
                : [];
            $room = trim((string) $room);

            if ($room !== '') {
                foreach ($this->privateRoomChannelsFor($payload, $room, $eventName, $context) as $channel) {
                    $channels[] = $channel;
                }
            }

            $pusher = new \Pusher\Pusher(
                $appKey,
                $appSecret,
                $appId,
                $options
            );

            $pusher->trigger(
                array_values(array_unique($channels)),
                trim((string) $eventName) ?: 'room.updated',
                $payload
            );
        } catch (\Throwable $th) {
            info('Named WebSocket dispatch failed: ' . $th->getMessage());
        }
    }

    protected function shouldPublishLegacyBdChat(array $payload, $eventName, array $context = [])
    {
        $source = trim((string) ($context['source'] ?? $payload['event_source'] ?? ''));
        $event = trim((string) ($eventName ?: ($payload['event_type'] ?? $payload['eventType'] ?? '')));

        if ($source === 'V5RoomBroadcastService' &&
            in_array($event, ['room.comment.muted', 'room.comment.unmuted'], true)) {
            return false;
        }

        return true;
    }

    protected function privateRoomChannelsFor(array $payload, $room, $eventName, array $context = [])
    {
        $roomTypes = $this->resolveRoomTypes($payload, $eventName, $context);
        if (empty($roomTypes)) {
            // Safety fallback for generic room events while old clients are still active.
            $roomTypes = ['audio', 'video', 'multi'];
        }

        $channels = [];
        foreach (array_unique($roomTypes) as $roomType) {
            if ($roomType === 'audio') {
                $channels[] = 'private-audio-room.' . $room;
            } elseif ($roomType === 'video') {
                $channels[] = 'private-video-room.' . $room;
            } elseif ($roomType === 'multi') {
                $channels[] = 'private-multi-room.' . $room;
            }
        }

        return $channels;
    }

    protected function resolveRoomTypes(array $payload, $eventName, array $context = [])
    {
        $types = [];
        foreach ([
            $payload['room_type'] ?? null,
            $payload['roomType'] ?? null,
            $payload['brd_type'] ?? null,
            $payload['brdType'] ?? null,
            $context['room_type'] ?? null,
            $context['roomType'] ?? null,
        ] as $candidate) {
            $roomType = $this->normalizeRoomType($candidate);
            if ($roomType !== '') {
                $types[] = $roomType;
            }
        }

        $source = strtolower(trim((string) ($context['source'] ?? $payload['event_source'] ?? '')));
        if (strpos($source, 'audio') !== false) {
            $types[] = 'audio';
        } elseif (strpos($source, 'video') !== false) {
            $types[] = 'video';
        } elseif (strpos($source, 'multi') !== false) {
            $types[] = 'multi';
        }

        $event = strtolower(trim((string) $eventName));
        if (strpos($event, 'audio.') === 0) {
            $types[] = 'audio';
        } elseif (strpos($event, 'video.') === 0) {
            $types[] = 'video';
        } elseif (strpos($event, 'multi.') === 0) {
            $types[] = 'multi';
        }

        $channelType = trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? ''));
        $typeFromChannel = $this->roomTypeForLegacyChannelType($channelType, $source);
        if ($typeFromChannel !== '') {
            $types[] = $typeFromChannel;
        }

        if (empty($types)) {
            $typeFromLive = $this->roomTypeFromActiveLive($payload['channelName'] ?? $payload['room'] ?? '');
            if ($typeFromLive !== '') {
                $types[] = $typeFromLive;
            }
        }

        return array_values(array_unique(array_filter($types)));
    }

    protected function normalizeRoomType($value)
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return '';
        }
        if (in_array($normalized, ['audio', 'audio_room', 'audioroom', '1'], true)) {
            return 'audio';
        }
        if (in_array($normalized, ['video', 'video_room', 'videoroom', '2'], true)) {
            return 'video';
        }
        if (in_array($normalized, ['multi', 'multi_room', 'multiroom', '3'], true)) {
            return 'multi';
        }
        return '';
    }

    protected function roomTypeForLegacyChannelType($channelType, $source)
    {
        $channelType = trim((string) $channelType);
        if ($channelType === '1') {
            return 'audio';
        }
        if ($channelType === '2') {
            return 'video';
        }
        if ($channelType === '3') {
            return 'multi';
        }
        if (in_array($channelType, ['13', '17', '19', '22', '26'], true)) {
            return 'video';
        }
        if (in_array($channelType, ['18', '23', '24', '25'], true)) {
            return 'audio';
        }
        if (in_array($channelType, ['11', '12', '14', '15', '16', '20', '21', '47', '49', '55', '66', '88', '98', '101', '102', '103'], true)) {
            if (strpos($source, 'video') !== false) {
                return 'video';
            }
            if (strpos($source, 'multi') !== false) {
                return 'multi';
            }
            if (strpos($source, 'audio') !== false) {
                return 'audio';
            }
        }
        return '';
    }

    protected function roomTypeFromActiveLive($channelName)
    {
        $room = $this->keys->normalizeRoom($channelName);
        if ($room === '') {
            return '';
        }

        try {
            $live = \App\Models\UserLive::where('channelName', $room)
                ->select('type')
                ->first();
            if (!$live) {
                return '';
            }

            return $this->normalizeRoomType($live->type ?? '');
        } catch (\Throwable $th) {
            return '';
        }
    }

    protected function resolveActorUserId(array $payload, array $context)
    {
        $direct = [
            $payload['kick_by'] ?? null,
            $payload['user_id'] ?? null,
            $payload['host_id'] ?? null,
            $context['actor_user_id'] ?? null,
        ];

        foreach ($direct as $value) {
            $normalized = trim((string) $value);
            if ($normalized !== '' && strtolower($normalized) !== 'null') {
                return $normalized;
            }
        }

        $data = $payload['data'] ?? null;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            foreach (['user_id', 'id', 'host_id'] as $key) {
                $normalized = trim((string) ($data[0][$key] ?? ''));
                if ($normalized !== '' && strtolower($normalized) !== 'null') {
                    return $normalized;
                }
            }
        }

        return null;
    }

    protected function buildEventId($room, $eventSeq)
    {
        return trim((string) $room) . ':' . (string) $eventSeq . ':' . (string) Str::uuid();
    }

    protected function buildIdempotencySignature(array $payload, $room, $channelType, $eventType)
    {
        $nestedPayload = isset($payload['payload']) && is_array($payload['payload'])
            ? $payload['payload']
            : [];

        $explicitEventId = trim((string) (
            $payload['event_id']
                ?? $payload['eventId']
                ?? $nestedPayload['event_id']
                ?? $nestedPayload['eventId']
                ?? ''
        ));

        if ($explicitEventId !== '') {
            return md5(json_encode([
                'room' => trim((string) $room),
                'event_type' => trim((string) $eventType),
                'event_id' => $explicitEventId,
            ], JSON_UNESCAPED_UNICODE));
        }

        $actualMessage = trim((string) (
            $nestedPayload['message']
                ?? $nestedPayload['text']
                ?? $payload['actual_message']
                ?? $payload['text']
                ?? $payload['message']
                ?? ''
        ));

        $signature = [
            'room' => trim((string) $room),
            'channel_type' => trim((string) $channelType),
            'event_type' => trim((string) $eventType),
            'message' => $actualMessage,
            'comment_id' => trim((string) ($nestedPayload['id'] ?? $payload['comment_id'] ?? '')),
            'actor' => trim((string) ($payload['actor_user_id'] ?? $nestedPayload['user_id'] ?? $payload['user_id'] ?? '')),
            'call_count' => trim((string) ($payload['call_count'] ?? '')),
            'target' => $this->seatService->targetUserIdFromPayload($payload),
            'data' => $payload['data'] ?? null,
        ];

        return md5(json_encode($signature, JSON_UNESCAPED_UNICODE));
    }

    protected function resolveEventType(array $payload, $channelType)
    {
        foreach (['event_type', 'eventType', 'event_name', 'eventName'] as $key) {
            $explicit = trim((string) ($payload[$key] ?? ''));
            if ($explicit !== '') {
                return $explicit;
            }
        }

        switch ($channelType) {
            case '11':
                if ($this->looksLikeCommentPayload($payload)) {
                    return 'room.comment.created';
                }
                return 'audio.call.pending_list';
            case '12':
                return 'room.admin.updated';
            case '13':
                return 'video.call.pending_list';
            case '14':
                return 'room.comment.muted';
            case '15':
                return 'room.member.entered';
            case '16':
                return 'room.comment.flying';
            case '17':
                return 'room.gift.sent';
            case '18':
                return 'audio.room.snapshot';
            case '19':
                return 'video.room.snapshot';
            case '20':
                return 'room.member.kicked';
            case '21':
                return 'room.comment.unmuted';
            case '22':
                return 'video.call.ended';
            case '23':
                return 'audio.seat.updated';
            case '24':
                return 'room.gift.sent';
            case '25':
                return 'audio.cohost.invited';
            case '26':
                return 'video.cohost.invited';
            case '44':
                return 'room.hourly_top.updated';
            case '47':
                return 'game.banner.updated';
            case '49':
                return 'room.reward.updated';
            case '55':
                return 'room.coin_bag.updated';
            case '66':
                return 'room.coin_bag.result';
            case '77':
                return 'room.share.invite';
            case '88':
                return 'gift.global';
            case '98':
                return 'room.cohost.mute_changed';
            case '101':
                return 'room.pk.requested';
            case '102':
                return 'room.pk.accepted';
            case '103':
                return 'room.board.closed';
            case '3':
                return 'multi.call.updated';
            default:
                return 'room.updated';
        }
    }

    protected function resolveLegacyChannelType($eventType)
    {
        $value = strtolower(trim((string) $eventType));

        if ($value === '') {
            return '';
        }

        if (strpos($value, 'comment.created') !== false) {
            return '11';
        }

        if (strpos($value, 'audio.call.pending') !== false) {
            return '11';
        }

        if (strpos($value, 'video.call.pending') !== false) {
            return '13';
        }

        if (strpos($value, 'comment.muted') !== false) {
            return '14';
        }

        if (strpos($value, 'comment.unmuted') !== false) {
            return '21';
        }

        if (strpos($value, 'member.entered') !== false) {
            return '15';
        }

        if (strpos($value, 'comment.flying') !== false) {
            return '16';
        }

        if (strpos($value, 'audio.room.snapshot') !== false) {
            return '18';
        }

        if (strpos($value, 'video.room.snapshot') !== false) {
            return '19';
        }

        if (strpos($value, 'member.kicked') !== false) {
            return '20';
        }

        if (strpos($value, 'audio.seat.updated') !== false) {
            return '23';
        }

        if (strpos($value, 'gift.global') !== false) {
            return '88';
        }

        if (strpos($value, 'gift.sent') !== false) {
            return '24';
        }

        if (strpos($value, 'audio.cohost.invited') !== false) {
            return '25';
        }

        if (strpos($value, 'video.cohost.invited') !== false) {
            return '26';
        }

        if (strpos($value, 'game_banner') !== false || strpos($value, 'game.banner') !== false) {
            return '47';
        }

        if (strpos($value, 'reward.updated') !== false) {
            return '49';
        }

        if (strpos($value, 'coin_bag.result') !== false || strpos($value, 'coin.bag.result') !== false) {
            return '66';
        }

        if (strpos($value, 'coin_bag') !== false || strpos($value, 'coin.bag') !== false) {
            return '55';
        }

        if (strpos($value, 'mute_changed') !== false) {
            return '98';
        }

        if (strpos($value, 'share.invite') !== false) {
            return '77';
        }

        return '';
    }

    protected function looksLikeCommentPayload(array $payload)
    {
        $message = trim((string) ($payload['message'] ?? ''));
        $type = trim((string) ($payload['type'] ?? ''));

        return $type === 'message'
            || $message !== ''
                && stripos($message, 'call request') === false
                && !isset($payload['call_count']);
    }

    protected function isAssoc(array $value)
    {
        if ([] === $value) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}
