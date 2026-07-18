<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Performs the room websocket fan-out (Pusher trigger) on the queue instead of
 * inline in the HTTP request.
 *
 * Background: AudioRoomRealtimeService::publishNamedRoomEvent() used to build a
 * Pusher client and call $pusher->trigger() synchronously for every comment /
 * gift / mute / seat event, blocking PHP-FPM on a websocket HTTP round-trip.
 * The legacy bd_chat fanout (BDEvent) was already queued (ShouldBroadcast); this
 * brings the private room-channel fanout onto the same async model so requests
 * return immediately.
 *
 * Runs on the dedicated `realtime` queue (see supervisor bdlive-realtime-queue)
 * so it never queues behind heavy default jobs. Channels/payload are computed
 * by the service and passed in unchanged — delivery is identical, just async.
 */
class PublishRoomChannelEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var array<int,string> */
    public $channels;
    public $eventName;
    /** @var array<string,mixed> */
    public $payload;

    public $tries = 2;
    public $timeout = 15;

    public function __construct(array $channels, $eventName, array $payload)
    {
        $this->channels = $channels;
        $this->eventName = (string) $eventName;
        $this->payload = $payload;
        $this->onQueue('realtime');
    }

    public function handle()
    {
        try {
            $channels = array_values(array_unique(array_filter(
                $this->channels,
                static function ($c) {
                    return is_string($c) && $c !== '';
                }
            )));
            if (empty($channels)) {
                return;
            }

            $setting = \RedisCacheFunction::getSetting();
            $appKey = config('broadcasting.connections.pusher.key') ?: ($setting->key ?? '');
            $appSecret = config('broadcasting.connections.pusher.secret') ?: ($setting->secret ?? '');
            $appId = config('broadcasting.connections.pusher.app_id') ?: ($setting->app_id ?? '');
            $baseOptions = config('broadcasting.connections.pusher.options', []);
            if (empty($baseOptions['cluster']) && !empty($setting->cluster)) {
                $baseOptions['cluster'] = $setting->cluster;
            }

            $eventName = trim((string) $this->eventName) ?: 'room.updated';

            // Publish to the LOCAL soketi instance only (config host/port =
            // 127.0.0.1:6004). soketi's Redis adapter propagates the event to
            // every other soketi node, so all clients on all nodes receive it
            // with no duplicates — replacing the old per-node fan-out (which was
            // needed only for laravel-websockets' LocalChannelManager).
            $pusher = new \Pusher\Pusher($appKey, $appSecret, $appId, $baseOptions);
            $pusher->trigger($channels, $eventName, $this->payload);
        } catch (\Throwable $th) {
            \Log::warning('PublishRoomChannelEventJob failed', [
                'event_name' => $this->eventName,
                'channels' => $this->channels,
                'error' => $th->getMessage(),
            ]);
            throw $th;
        }
    }
}
