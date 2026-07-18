<?php

namespace App\Services\V5\Adapters;

/**
 * V5 room-type adapter contract.
 *
 * Each room type (audio / video / multi) implements this interface so the
 * shared RoomActionService can delegate room-specific behavior (seat layout,
 * realtime fan-out shape, etc.) without growing per-type branches.
 *
 * Methods return an array of the shape:
 *   ['ok' => bool, 'envelope' => array|null, 'extra' => array (optional)]
 *
 * Agent C is responsible for filling in the real implementations; the stubs
 * shipped alongside this interface return ['ok' => true, 'envelope' => null]
 * so the shared service is usable end-to-end while wiring continues.
 */
interface AdapterInterface
{
    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function requestCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function acceptCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function rejectCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function cutCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function kickCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function muteCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function inviteCohost($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function sendComment($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function muteComment($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function kickAudience($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function joinAudience($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function leaveAudience($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function lockRoom($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function closeRoom($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function setAdmin($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function sendGift($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function fetchSnapshot($channel, $hostId, array $body);

    /**
     * @param string $channel
     * @param string $hostId
     * @param array  $body
     * @return array
     */
    public function fetchCommentsSince($channel, $hostId, array $body);
}
