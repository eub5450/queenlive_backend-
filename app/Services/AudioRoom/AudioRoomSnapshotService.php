<?php

namespace App\Services\AudioRoom;

class AudioRoomSnapshotService
{
    protected $seatService;
    protected $giftService;
    protected $adminService;

    public function __construct(
        AudioRoomSeatService $seatService,
        AudioRoomGiftService $giftService,
        AudioRoomAdminService $adminService
    ) {
        $this->seatService = $seatService;
        $this->giftService = $giftService;
        $this->adminService = $adminService;
    }

    public function compose(array $payload)
    {
        $room = trim((string) ($payload['channelName'] ?? $payload['channel_name'] ?? $payload['room'] ?? ''));
        if ($room === '') {
            return null;
        }

        $nested = [];
        $data = array_key_exists('data', $payload) ? $payload['data'] : null;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            $nested = $data[0];
        }

        return [
            'room' => $room,
            'channel_type' => trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? '')),
            'event_id' => trim((string) ($payload['event_id'] ?? $payload['eventId'] ?? '')),
            'event_seq' => (int) ($payload['event_seq'] ?? 0),
            'event_type' => trim((string) ($payload['event_type'] ?? 'audio.room.updated')),
            'created_at' => trim((string) ($payload['created_at'] ?? '')),
            'call_count' => trim((string) ($payload['call_count'] ?? '')),
            'pending_call_list' => $this->seatService->extractPendingCalls($payload),
            'host_list' => $this->seatService->extractHostList($payload),
            'host_balance' => trim((string) ($nested['host_balance'] ?? '')),
            'star' => trim((string) ($nested['star'] ?? '')),
            'star_complete_parcent' => trim((string) ($nested['star_complete_parcent'] ?? '')),
            'total_reward' => trim((string) ($nested['total_reward'] ?? '')),
            'audience_counter' => $payload['audience_counter'] ?? ($nested['audience_counter'] ?? null),
            'audience_profile' => $payload['audience_profile'] ?? ($nested['audience_profile'] ?? null),
            'room_admin_list' => $payload['room_admin_list'] ?? $this->adminService->extractAdminList($payload),
            'last_gift' => $this->giftService->extractGiftState($payload),
            'raw' => $payload,
        ];
    }
}
