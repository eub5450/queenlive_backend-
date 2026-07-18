<?php

namespace App\Services\V5\Adapters;

/**
 * Video-room adapter stub. Agent C fills in cohost grid + RTC token handoff.
 */
class VideoAdapter implements AdapterInterface
{
    public function requestCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function acceptCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function rejectCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function cutCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function kickCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function muteCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function inviteCohost($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function sendComment($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function muteComment($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function kickAudience($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function joinAudience($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function leaveAudience($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function lockRoom($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function closeRoom($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function setAdmin($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function sendGift($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function fetchSnapshot($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }

    public function fetchCommentsSince($channel, $hostId, array $body)
    {
        return ['ok' => true, 'envelope' => null];
    }
}
