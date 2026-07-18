<?php

namespace App\Services\V5;

use App\Jobs\PublishRoomChannelEventJob;
use App\Services\AudioRoom\AudioRoomStateService;
use Illuminate\Support\Facades\Log;

class V5RoomRealtimeService
{
    /** @var AudioRoomStateService */
    protected $stateService;

    public function __construct(AudioRoomStateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * Publish one V5 room envelope to the room-private Soketi channel only.
     * Legacy realtime fanout stays with old callers; V5 room actions publish
     * only private room-channel events.
     *
     * @param array<string,mixed> $envelope
     * @param array<string,mixed> $context
     * @return array<string,mixed>|null
     */
    public function publish(array $envelope, array $context = [])
    {
        $roomType = strtolower(trim((string) ($envelope['room_type'] ?? $envelope['roomType'] ?? $context['room_type'] ?? '')));
        $channelName = trim((string) ($envelope['channel_name'] ?? $envelope['channelName'] ?? $envelope['room'] ?? ''));
        $eventType = trim((string) ($envelope['event_type'] ?? $envelope['eventType'] ?? $context['event_type'] ?? ''));

        if ($roomType === '' || $channelName === '' || $eventType === '') {
            Log::warning('V5 realtime publish skipped: missing routing key', [
                'room_type' => $roomType,
                'channel' => $channelName,
                'event_type' => $eventType,
            ]);
            return null;
        }

        $channel = $this->privateRoomChannel($roomType, $channelName);
        if ($channel === null) {
            Log::warning('V5 realtime publish skipped: unknown room type', [
                'room_type' => $roomType,
                'channel' => $channelName,
                'event_type' => $eventType,
            ]);
            return null;
        }

        $clientPayload = $this->clientPayload($envelope, $roomType, $channelName, $eventType);
        $this->refreshState($clientPayload, $eventType);

        PublishRoomChannelEventJob::dispatch([$channel], $eventType, $clientPayload);

        return $clientPayload;
    }

    protected function privateRoomChannel($roomType, $channelName)
    {
        if (!in_array($roomType, ['audio', 'video', 'multi'], true)) {
            return null;
        }

        return 'private-' . $roomType . '-room.' . $channelName;
    }

    protected function clientPayload(array $envelope, $roomType, $channelName, $eventType)
    {
        unset($envelope['message']);

        $envelope['room_type'] = $roomType;
        $envelope['channel_name'] = $channelName;
        $envelope['event_type'] = $eventType;

        if (!array_key_exists('ts_ms', $envelope) && array_key_exists('ts', $envelope)) {
            $envelope['ts_ms'] = $envelope['ts'];
        }

        return $envelope;
    }

    protected function refreshState(array $clientPayload, $eventType)
    {
        try {
            $this->stateService->refreshRoomFromPayload(
                $this->statePayloadFor($clientPayload, $eventType)
            );
        } catch (\Throwable $th) {
            Log::warning('V5 realtime state refresh failed', [
                'event_type' => $eventType,
                'channel' => $clientPayload['channel_name'] ?? '',
                'error' => $th->getMessage(),
            ]);
        }
    }

    protected function statePayloadFor(array $clientPayload, $eventType)
    {
        $statePayload = $clientPayload;
        $eventPayload = $clientPayload['payload'] ?? [];
        if ($eventPayload instanceof \JsonSerializable) {
            $eventPayload = $eventPayload->jsonSerialize();
        }
        if (!is_array($eventPayload)) {
            $eventPayload = [];
        }

        $statePayload['channelName'] = $clientPayload['channel_name'] ?? '';
        $statePayload['room'] = $clientPayload['channel_name'] ?? '';
        $statePayload['eventType'] = $eventType;
        $statePayload['eventName'] = $eventType;

        if (!array_key_exists('data', $statePayload)) {
            if (array_key_exists('data', $eventPayload) && is_array($eventPayload['data'])) {
                $statePayload['data'] = $eventPayload['data'];
            } elseif (!empty($eventPayload)) {
                $statePayload['data'] = [$eventPayload];
            }
        }

        return $statePayload;
    }
}

