<?php

namespace App\Services\AudioRoom;

class AudioRoomRedisKeyService
{
    const ACTIVE_ROOM_TTL = 21600;
    const LOCK_TTL = 8;
    const IDEMPOTENCY_TTL = 90;

    public function normalizeRoom($channelName)
    {
        return trim((string) $channelName);
    }

    public function snapshot($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':snapshot';
    }

    public function members($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':members';
    }

    public function seats($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':seats';
    }

    public function admins($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':admins';
    }

    public function mutedUsers($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':muted_users';
    }

    public function kickedUsers($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':kicked_users';
    }

    public function pendingCalls($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':pending_calls';
    }

    public function commentMutedUsers($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':comment_muted_users';
    }

    public function giftSeq($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':gift_seq';
    }

    public function eventSeq($channelName)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':event_seq';
    }

    public function lock($channelName, $action)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':locks:' . trim((string) $action);
    }

    public function idempotency($channelName, $action, $signature)
    {
        return 'audio:room:' . $this->normalizeRoom($channelName) . ':idempotency:' . trim((string) $action) . ':' . trim((string) $signature);
    }
}
