<?php

namespace App\Services;

use App\Models\Follower;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class LivePushNotificationService
{
    public function __construct(private Messaging $messaging)
    {
    }

    public function sendHostWentLive($hostData, string $channelName, $roomType, ?string $body = null): array
    {
        $hostId = (string) ($hostData->id ?? '');
        if ($hostId === '' || trim($channelName) === '') {
            return ['followers' => 0, 'tokens' => 0, 'sent_batches' => 0];
        }

        $title = 'QueenLive';
        $messageBody = $body ?: (($hostData->name ?? 'Host') . ' is live now. Tap to join.');
        $data = [
            'event_type' => 'host_went_live',
            'title' => $title,
            'body' => $messageBody,
            'message' => $messageBody,
            'id' => $hostId,
            'host_id' => $hostId,
            'host_name' => (string) ($hostData->name ?? ''),
            'host_profile' => (string) ($hostData->profile ?? ''),
            'channel' => (string) $channelName,
            'channelName' => (string) $channelName,
            'type' => (string) $roomType,
            'brd_type' => (string) $roomType,
        ];

        $stats = ['followers' => 0, 'tokens' => 0, 'sent_batches' => 0];
        $seenTokens = [];

        Follower::where('follower_id', $hostId)
            ->select('id', 'user_id')
            ->orderBy('id')
            ->chunk(100, function ($followers) use (&$stats, &$seenTokens, $title, $messageBody, $data) {
                $messages = [];
                foreach ($followers as $follower) {
                    $stats['followers']++;
                    if (!$this->liveAlertsAllowed((string) $follower->user_id)) {
                        continue;
                    }

                    foreach ($this->tokensForUser((string) $follower->user_id) as $token) {
                        if (isset($seenTokens[$token])) {
                            continue;
                        }
                        $seenTokens[$token] = true;
                        $stats['tokens']++;
                        $messages[] = CloudMessage::fromArray([
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $messageBody,
                            ],
                            'data' => $data,
                            'android' => [
                                'priority' => 'high',
                                'notification' => [
                                    'channel_id' => 'bd_live',
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                ],
                            ],
                        ]);
                    }
                }

                foreach (array_chunk($messages, 100) as $batch) {
                    if (empty($batch)) {
                        continue;
                    }
                    try {
                        $this->messaging->sendAll($batch);
                        $stats['sent_batches']++;
                    } catch (\Throwable $e) {
                        Log::warning('host_live_fcm_batch_failed', ['error' => $e->getMessage()]);
                    }
                }
            });

        return $stats;
    }

    private function liveAlertsAllowed(string $userId): bool
    {
        $flag = Redis::get('queenlive:live_alerts:user:' . $userId);
        return $flag === null || (string) $flag === '1';
    }

    private function tokensForUser(string $userId): array
    {
        $hash = Redis::hgetall('queenlive:device_tokens:user:' . $userId);
        if (!is_array($hash) || empty($hash)) {
            return [];
        }

        $tokens = [];
        foreach ($hash as $payload) {
            $decoded = json_decode((string) $payload, true);
            $token = is_array($decoded) ? trim((string) ($decoded['token'] ?? '')) : '';
            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return array_values(array_unique($tokens));
    }
}
