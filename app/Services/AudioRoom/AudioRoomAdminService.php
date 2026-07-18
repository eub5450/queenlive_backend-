<?php

namespace App\Services\AudioRoom;

class AudioRoomAdminService
{
    public function extractAdminList(array $payload)
    {
        $direct = array_key_exists('room_admin_list', $payload) ? $payload['room_admin_list'] : null;
        if (is_array($direct)) {
            return array_values($direct);
        }

        $data = array_key_exists('data', $payload) ? $payload['data'] : null;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            $nested = array_key_exists('room_admin_list', $data[0]) ? $data[0]['room_admin_list'] : null;
            if (is_array($nested)) {
                return array_values($nested);
            }
        }

        return [];
    }

    public function extractAdminIds(array $payload)
    {
        $adminIds = [];
        foreach ($this->extractAdminList($payload) as $admin) {
            if (!is_array($admin)) {
                continue;
            }

            $adminId = trim((string) ($admin['id'] ?? $admin['admin_id'] ?? ''));
            if ($adminId !== '') {
                $adminIds[] = $adminId;
            }
        }

        return array_values(array_unique($adminIds));
    }

    public function extractCommentMutedUserIds(array $payload)
    {
        $channelType = trim((string) ($payload['channel_type'] ?? $payload['channelType'] ?? ''));
        $eventType = trim((string) ($payload['event_type'] ?? $payload['eventType'] ?? ''));
        if ($channelType !== '14' && $eventType !== 'room.comment.muted') {
            return [];
        }

        $data = array_key_exists('data', $payload) ? $payload['data'] : null;
        if (!is_array($data)) {
            return [];
        }

        $mutedIds = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            $userId = trim((string) ($row['user_id'] ?? ''));
            if ($userId !== '') {
                $mutedIds[] = $userId;
            }
        }

        return array_values(array_unique($mutedIds));
    }
}
