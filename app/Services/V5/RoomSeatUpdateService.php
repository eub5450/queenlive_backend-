<?php

namespace App\Services\V5;

use Illuminate\Support\Facades\Redis;

/**
 * V5 per-seat delta broadcaster.
 *
 * Agent S design (2026-06-28): each seat in a room is keyed by
 * (channelName, seat_no) and carries a monotonic `seat_version`
 * driven by a Redis INCR. The new envelope `room.seat.updated`
 * carries only the changed fields so new clients can subscribe
 * to a single seat without rebuilding everything.
 *
 * Staged behind a feature flag at the call site. Additive on top of
 * the existing `room.snapshot` path — old clients ignore the new
 * event_type via the standard whitelist filter.
 */
class RoomSeatUpdateService
{
    /** @var RoomBroadcastService */
    protected $broadcast;

    public function __construct(RoomBroadcastService $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    /**
     * Emit a `room.seat.updated` envelope for a single seat.
     *
     * @param string      $roomType    'audio' | 'video' | 'multi'
     * @param string      $channel     Room channel name
     * @param int         $seatNo      Seat slot number
     * @param array       $delta       Changed fields only (mute / locked / co_host_id / ...)
     * @param string|null $actorUserId Actor user id, if any
     * @return array                    The envelope that was broadcast (or [] on failure)
     */
    public function emitSeatChange(
        $roomType,
        $channel,
        $seatNo,
        array $delta,
        $actorUserId = null
    ) {
        $seatNo = (int) $seatNo;
        $versionKey = "v5:seat_version:{$channel}:{$seatNo}";

        $seatVersion = 0;
        try {
            $seatVersion = (int) Redis::incr($versionKey);
            // Keep the key warm for 24h so a long-running room keeps growing
            // its version; rooms that go idle eventually expire.
            Redis::expire($versionKey, 86400);
        } catch (\Throwable $t) {
            // If Redis is unavailable, fall back to a millisecond stamp so
            // ordering still holds within the same process.
            $seatVersion = (int) round(microtime(true) * 1000);
        }

        $hostId = isset($delta['host_id']) ? (string) $delta['host_id'] : '0';

        $payload = array_merge($delta, [
            'seat_no'      => $seatNo,
            'seat_version' => $seatVersion,
        ]);

        try {
            return $this->broadcast->broadcast(
                $roomType,
                $channel,
                (string) $hostId,
                'room.seat.updated',
                $payload,
                [
                    'actor_user_id'  => $actorUserId,
                    'target_user_id' => isset($delta['co_host_id']) ? (string) $delta['co_host_id'] : null,
                ]
            );
        } catch (\Throwable $t) {
            // Per design: emit failures are best-effort — never abort the
            // parent room action.
            return [];
        }
    }
}
