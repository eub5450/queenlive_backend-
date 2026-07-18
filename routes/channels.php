<?php

use App\Models\Kick;
use App\Models\UserLive;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

$authorizeRoomChannel = function ($user, $channelName) {
    $room = trim((string) $channelName);
    if ($room === '' || empty($user) || empty($user->id)) {
        return false;
    }

    $live = UserLive::where('channelName', $room)->orderByDesc('id')->first();
    if (!$live) {
        return false;
    }

    $userId = (string) $user->id;
    $hostId = (string) ($live->user_id ?? $live->host_id ?? '');
    $recentSince = now()->subMinutes(90);

    $isHost = $hostId !== '' && $hostId === $userId;

    $hasRecentAudienceJoin = DB::table('audience_joins')
        ->where('channelName', $room)
        ->where('user_id', $user->id)
        ->where('updated_at', '>=', $recentSince)
        ->exists();

    $hasRecentLiveCall = DB::table('live_calls')
        ->where('channelName', $room)
        ->where(function ($query) use ($user) {
            $query->where('host_id', $user->id)
                ->orWhere('co_host_id', $user->id);
        })
        ->where('updated_at', '>=', $recentSince)
        ->exists();

    $hasRoomAdmin = false;
    if ($hostId !== '') {
        try {
            $hasRoomAdmin = DB::table('brd_admins')
                ->where('host_id', $hostId)
                ->where('user_id', $user->id)
                ->exists();
        } catch (\Throwable $ignored) {
            $hasRoomAdmin = false;
        }
    }

    if (!($isHost || $hasRecentAudienceJoin || $hasRecentLiveCall || $hasRoomAdmin)) {
        return false;
    }

    $isKicked = Kick::where('channelName', $room)
        ->where('user_id', $user->id)
        ->exists();

    return !$isKicked;
};

Broadcast::channel(
    'audio-room.{channelName}',
    $authorizeRoomChannel,
    ['guards' => ['sanctum']],
);

Broadcast::channel(
    'video-room.{channelName}',
    $authorizeRoomChannel,
    ['guards' => ['sanctum']],
);

Broadcast::channel(
    'multi-room.{channelName}',
    $authorizeRoomChannel,
    ['guards' => ['sanctum']],
);
