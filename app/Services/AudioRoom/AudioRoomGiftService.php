<?php

namespace App\Services\AudioRoom;

class AudioRoomGiftService
{
    public function extractGiftState(array $payload)
    {
        $channelType = trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? ''));
        $eventType = trim((string) ($payload['event_type'] ?? $payload['eventType'] ?? ''));
        if (!in_array($channelType, ['24', '88'], true) && !in_array($eventType, ['room.gift.sent', 'room.gift.global'], true)) {
            return null;
        }

        $data = array_key_exists('data', $payload) ? $payload['data'] : null;
        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        if (!is_array($data)) {
            return null;
        }

        return [
            'message' => trim((string) ($payload['message'] ?? '')),
            'data' => $data,
        ];
    }
}
