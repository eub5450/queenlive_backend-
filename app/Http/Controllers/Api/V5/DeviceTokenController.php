<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $authUser = $request->user();
        $userId = trim((string)($request->input('user_id') ?: optional($authUser)->id));
        $token = trim((string)$request->input('token'));
        $platform = strtolower(trim((string)$request->input('platform', 'android')));
        $alertsEnabled = $this->boolFlag($request->input('live_alerts_enabled', '1'));

        if ($userId === '' || $token === '') {
            return response()->json(['code' => '422', 'message' => 'user_id and token are required'], 422);
        }
        if (!in_array($platform, ['android', 'ios', 'web'], true)) {
            $platform = 'android';
        }

        $now = now()->toDateTimeString();
        $tokenHash = sha1($token);
        $payload = [
            'user_id' => $userId,
            'token' => $token,
            'token_hash' => $tokenHash,
            'platform' => $platform,
            'live_alerts_enabled' => $alertsEnabled ? '1' : '0',
            'updated_at' => $now,
        ];

        Redis::hset('queenlive:device_tokens:user:' . $userId, $tokenHash, json_encode($payload, JSON_UNESCAPED_SLASHES));
        Redis::set('queenlive:device_token:' . $tokenHash, json_encode($payload, JSON_UNESCAPED_SLASHES));
        Redis::sadd('queenlive:device_tokens:users', $userId);
        Redis::set('queenlive:live_alerts:user:' . $userId, $alertsEnabled ? '1' : '0');
        $this->bestEffortUserColumnMirror($userId, $token, $platform, $alertsEnabled, $now);

        return response()->json([
            'code' => '200',
            'message' => 'Device token saved',
            'data' => [
                'user_id' => $userId,
                'platform' => $platform,
                'live_alerts_enabled' => $alertsEnabled ? '1' : '0',
                'token_hash' => $tokenHash,
            ],
        ]);
    }

    private function boolFlag($value): bool
    {
        $normalized = strtolower(trim((string)$value));
        return !in_array($normalized, ['', '0', 'false', 'no', 'off', 'null'], true);
    }

    private function bestEffortUserColumnMirror(string $userId, string $token, string $platform, bool $alertsEnabled, string $now): void
    {
        try {
            $updates = [];
            foreach (['fcm_token', 'firebase_token', 'device_token', 'notification_token'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $updates[$column] = $token;
                    break;
                }
            }
            foreach (['device_platform', 'platform'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $updates[$column] = $platform;
                    break;
                }
            }
            foreach (['live_alerts_enabled', 'notification_live_alerts_enabled'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $updates[$column] = $alertsEnabled ? 1 : 0;
                    break;
                }
            }
            if (Schema::hasColumn('users', 'device_token_updated_at')) {
                $updates['device_token_updated_at'] = $now;
            }
            if (!empty($updates)) {
                DB::table('users')->where('id', $userId)->update($updates);
            }
        } catch (\Throwable $e) {
            Log::warning('device_token_user_column_mirror_failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
