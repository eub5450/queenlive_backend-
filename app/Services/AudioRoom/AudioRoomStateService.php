<?php

namespace App\Services\AudioRoom;

use Illuminate\Support\Facades\Redis;

class AudioRoomStateService
{
    protected $keys;
    protected $snapshotService;
    protected $seatService;
    protected $giftService;
    protected $adminService;

    public function __construct(
        AudioRoomRedisKeyService $keys,
        AudioRoomSnapshotService $snapshotService,
        AudioRoomSeatService $seatService,
        AudioRoomGiftService $giftService,
        AudioRoomAdminService $adminService
    ) {
        $this->keys = $keys;
        $this->snapshotService = $snapshotService;
        $this->seatService = $seatService;
        $this->giftService = $giftService;
        $this->adminService = $adminService;
    }

    public function nextEventSequence($channelName)
    {
        $room = $this->keys->normalizeRoom($channelName);
        if ($room === '') {
            return 0;
        }

        $seq = (int) Redis::incr($this->keys->eventSeq($room));
        Redis::expire($this->keys->eventSeq($room), AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);

        return $seq;
    }

    public function nextGiftSequence($channelName)
    {
        $room = $this->keys->normalizeRoom($channelName);
        if ($room === '') {
            return 0;
        }

        $seq = (int) Redis::incr($this->keys->giftSeq($room));
        Redis::expire($this->keys->giftSeq($room), AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);

        return $seq;
    }

    public function acquireActionLock($channelName, $action, array $fingerprintParts = [], $ttl = AudioRoomRedisKeyService::LOCK_TTL)
    {
        $room = $this->keys->normalizeRoom($channelName);
        if ($room === '') {
            return true;
        }

        $signature = md5(json_encode(array_values($fingerprintParts)));
        $lockKey = $this->keys->lock($room, $action) . ':' . $signature;

        return (bool) Redis::set($lockKey, '1', 'EX', max(1, (int) $ttl), 'NX');
    }

    public function acquireIdempotencyLock($channelName, $action, $signature, $ttl = AudioRoomRedisKeyService::IDEMPOTENCY_TTL)
    {
        $room = $this->keys->normalizeRoom($channelName);
        if ($room === '' || trim((string) $signature) === '') {
            return true;
        }

        return (bool) Redis::set(
            $this->keys->idempotency($room, $action, $signature),
            '1',
            'EX',
            max(1, (int) $ttl),
            'NX'
        );
    }

    public function refreshRoomFromPayload(array $payload)
    {
        $room = $this->keys->normalizeRoom($payload['channelName'] ?? $payload['channel_name'] ?? $payload['room'] ?? '');
        if ($room === '') {
            return;
        }

        $eventType = trim((string) ($payload['event_type'] ?? $payload['eventType'] ?? ''));
        $channelType = trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? ''));

        $snapshot = $this->snapshotService->compose($payload);
        if ($snapshot !== null) {
            $this->storeJson($this->keys->snapshot($room), $snapshot, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }

        $memberIds = $this->seatService->memberIdsFromPayload($payload);
        if (!empty($memberIds)) {
            $this->replaceSet($this->keys->members($room), $memberIds, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }

        $seatMap = $this->seatService->seatMapFromPayload($payload);
        if ($this->isSeatSnapshotEvent($eventType, $channelType) || !empty($seatMap)) {
            $this->storeJson($this->keys->seats($room), $seatMap, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }

        $pendingCalls = $this->seatService->extractPendingCalls($payload);
        if ($this->isPendingCallEvent($eventType, $channelType)) {
            $this->storeJson($this->keys->pendingCalls($room), $pendingCalls, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }

        $adminIds = $this->adminService->extractAdminIds($payload);
        if (!empty($adminIds)) {
            $this->replaceSet($this->keys->admins($room), $adminIds, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        } elseif ($this->isAdminListEvent($eventType, $channelType)) {
            Redis::del($this->keys->admins($room));
        }

        $mutedIds = $this->seatService->mutedUserIdsFromPayload($payload);
        if (!empty($mutedIds)) {
            $this->replaceSet($this->keys->mutedUsers($room), $mutedIds, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        } elseif ($this->isSeatSnapshotEvent($eventType, $channelType)) {
            Redis::del($this->keys->mutedUsers($room));
        }

        $commentMutedIds = $this->adminService->extractCommentMutedUserIds($payload);
        if (!empty($commentMutedIds)) {
            $this->replaceSet($this->keys->commentMutedUsers($room), $commentMutedIds, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        } elseif ($this->isCommentUnmuteEvent($eventType, $channelType)) {
            Redis::del($this->keys->commentMutedUsers($room));
        }

        $kickedIds = $this->seatService->kickedUserIdsFromPayload($payload);
        if (!empty($kickedIds)) {
            $this->appendSet($this->keys->kickedUsers($room), $kickedIds, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }

        $giftState = $this->giftService->extractGiftState($payload);
        if ($giftState !== null) {
            $giftState['gift_seq'] = $this->nextGiftSequence($room);
            $snapshot = $snapshot ?: [];
            $snapshot['last_gift'] = $giftState;
            $this->storeJson($this->keys->snapshot($room), $snapshot, AudioRoomRedisKeyService::ACTIVE_ROOM_TTL);
        }
    }

    protected function isSeatSnapshotEvent($eventType, $channelType)
    {
        return $channelType === '18' || in_array($eventType, [
            'audio.room.snapshot',
            'video.room.snapshot',
            'room.snapshot',
            'audio.seat.updated',
            'room.seat.updated',
            'multi.call.updated',
        ], true);
    }

    protected function isPendingCallEvent($eventType, $channelType)
    {
        return $channelType === '11' || in_array($eventType, [
            'audio.call.pending_list',
            'video.call.pending_list',
            'room.cohost.requested',
        ], true);
    }

    protected function isAdminListEvent($eventType, $channelType)
    {
        return $channelType === '12' || in_array($eventType, [
            'room.admin.added',
            'room.admin.removed',
        ], true);
    }

    protected function isCommentUnmuteEvent($eventType, $channelType)
    {
        return $channelType === '21' || $eventType === 'room.comment.unmuted';
    }
    protected function storeJson($key, $value, $ttl)
    {
        Redis::setex($key, max(1, (int) $ttl), json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    protected function replaceSet($key, array $values, $ttl)
    {
        Redis::del($key);
        $this->appendSet($key, $values, $ttl);
    }

    protected function appendSet($key, array $values, $ttl)
    {
        $filtered = array_values(array_unique(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, $values))));

        if (empty($filtered)) {
            return;
        }

        foreach ($filtered as $value) {
            Redis::sadd($key, $value);
        }
        Redis::expire($key, max(1, (int) $ttl));
    }
}
