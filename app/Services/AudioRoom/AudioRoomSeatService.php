<?php

namespace App\Services\AudioRoom;

class AudioRoomSeatService
{
    public function extractHostList(array $payload)
    {
        $snapshot = $this->firstNestedMap($payload);
        $hostList = $this->arrayValue($snapshot, 'host_list');
        return is_array($hostList) ? array_values($hostList) : [];
    }

    public function extractPendingCalls(array $payload)
    {
        $data = $this->arrayValue($payload, 'data');
        return is_array($data) ? array_values($data) : [];
    }

    public function memberIdsFromPayload(array $payload)
    {
        $memberIds = [];

        foreach ($this->extractHostList($payload) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $memberId = $this->stringValue($row, ['co_host_id', 'user_id', 'id']);
            if ($memberId !== '') {
                $memberIds[] = $memberId;
            }
        }

        return array_values(array_unique($memberIds));
    }

    public function seatMapFromPayload(array $payload)
    {
        $seatMap = [];

        foreach ($this->extractHostList($payload) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $setNo = $this->stringValue($row, ['set_no']);
            if ($setNo === '') {
                continue;
            }

            $seatMap[$setNo] = [
                'set_no' => $setNo,
                'co_host_id' => $this->stringValue($row, ['co_host_id', 'user_id', 'id']),
                'co_host_name' => $this->stringValue($row, ['co_host_name', 'name']),
                'mute' => $this->stringValue($row, ['mute']),
                'super_mute' => $this->stringValue($row, ['super_mute']),
                'status' => $this->stringValue($row, ['co_host_status', 'status']),
                'profile' => $this->stringValue($row, ['profile']),
                'emoji' => $this->stringValue($row, ['emoji']),
            ];
        }

        return $seatMap;
    }

    public function mutedUserIdsFromPayload(array $payload)
    {
        $mutedIds = [];

        foreach ($this->extractHostList($payload) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $mute = $this->stringValue($row, ['mute']);
            $superMute = $this->stringValue($row, ['super_mute']);
            if ($mute === '0' || $superMute === '1') {
                $memberId = $this->stringValue($row, ['co_host_id', 'user_id', 'id']);
                if ($memberId !== '') {
                    $mutedIds[] = $memberId;
                }
            }
        }

        return array_values(array_unique($mutedIds));
    }

    public function kickedUserIdsFromPayload(array $payload)
    {
        $nested = $this->firstNestedMap($payload);
        $targetUserId = $this->stringValue($nested, ['user_id', 'target_user_id']);

        return $targetUserId !== '' ? [$targetUserId] : [];
    }

    public function targetUserIdFromPayload(array $payload)
    {
        $nested = $this->firstNestedMap($payload);

        return $this->stringValue($payload, ['target_user_id', 'co_host_id', 'user_id'])
            ?: $this->stringValue($nested, ['target_user_id', 'co_host_id', 'user_id']);
    }

    protected function firstNestedMap(array $payload)
    {
        $data = $this->arrayValue($payload, 'data');
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            return $data[0];
        }

        return [];
    }

    protected function arrayValue(array $payload, $key)
    {
        return array_key_exists($key, $payload) ? $payload[$key] : null;
    }

    protected function stringValue(array $payload, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = trim((string) $payload[$key]);
            if ($value !== '' && strtolower($value) !== 'null') {
                return $value;
            }
        }

        return '';
    }
}
