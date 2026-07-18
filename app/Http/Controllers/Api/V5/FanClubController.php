<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Services\FanClubService;
use App\Services\V5\RoomBroadcastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * v5/fanclub/* endpoints. All routes are Sanctum + check.ban middleware
 * (see routes/api.php). Boss 2026-07-07.
 */
class FanClubController extends Controller
{
    private FanClubService $service;

    public function __construct()
    {
        $this->service = new FanClubService(app(RoomBroadcastService::class));
    }

    public function tiers(Request $request): JsonResponse
    {
        return $this->ok(['tiers' => $this->service->tiers()]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            $result = $this->service->subscribe(
                (string) ($body['user_id'] ?? $body['subscriber_id'] ?? ''),
                (string) ($body['host_id'] ?? ''),
                (string) ($body['tier'] ?? '')
            );
            return $this->fromResult($result);
        } catch (Throwable $e) {
            Log::warning('fanclub.subscribe.failed', ['msg' => $e->getMessage()]);
            return $this->fail('400', $e->getMessage());
        }
    }

    public function mine(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            return $this->ok($this->service->mine((string) ($body['user_id'] ?? '')));
        } catch (Throwable $e) {
            return $this->fail('400', $e->getMessage());
        }
    }

    public function host(Request $request, string $hostId): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            $viewer = (string) ($body['user_id'] ?? '');
            return $this->ok($this->service->host($hostId, $viewer !== '' ? $viewer : null));
        } catch (Throwable $e) {
            return $this->fail('400', $e->getMessage());
        }
    }

    public function renew(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            $result = $this->service->renew(
                (string) ($body['user_id'] ?? ''),
                (string) ($body['subscription_id'] ?? '')
            );
            return $this->fromResult($result);
        } catch (Throwable $e) {
            return $this->fail('400', $e->getMessage());
        }
    }

    public function cancel(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            $result = $this->service->cancel(
                (string) ($body['user_id'] ?? ''),
                (string) ($body['subscription_id'] ?? '')
            );
            return $this->fromResult($result);
        } catch (Throwable $e) {
            return $this->fail('400', $e->getMessage());
        }
    }

    // ---- Admin tier CRUD (guarded by static access_token — matches the
    // existing pattern used by admin_controller_v5). Boss 2026-07-07. ----

    public function adminTiers(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('fan_club_tier_config')) {
            return $this->ok(['tiers' => []]);
        }
        $rows = \Illuminate\Support\Facades\DB::table('fan_club_tier_config')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
        return $this->ok(['tiers' => $rows]);
    }

    public function adminUpsertTier(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('fan_club_tier_config')) {
            return $this->fail('412', 'fan_club_tier_config table missing');
        }
        $tier = strtolower(trim((string) ($body['tier'] ?? '')));
        if ($tier === '') {
            return $this->fail('400', 'tier required');
        }
        $data = [
            'price' => (int) ($body['price'] ?? 0),
            'duration_days' => max(1, (int) ($body['duration_days'] ?? 30)),
            'perks_json' => (string) ($body['perks_json'] ?? '{}'),
            'enabled' => (int) ((bool) ($body['enabled'] ?? true)),
            'sort_order' => (int) ($body['sort_order'] ?? 0),
            'updated_at' => now(),
        ];
        $existing = \Illuminate\Support\Facades\DB::table('fan_club_tier_config')
            ->where('tier', $tier)
            ->first();
        if ($existing) {
            \Illuminate\Support\Facades\DB::table('fan_club_tier_config')
                ->where('tier', $tier)
                ->update($data);
        } else {
            $data['tier'] = $tier;
            $data['created_at'] = now();
            \Illuminate\Support\Facades\DB::table('fan_club_tier_config')->insert($data);
        }
        return $this->ok(['ok' => true, 'tier' => $tier]);
    }

    // ---- Admin check-in ladder CRUD — Boss 2026-07-07: same panel gets
    // an editor for the checkin_rewards table so the Setting -> Check-in
    // page can adjust the reward ladder without a release. ----

    public function adminCheckinLadder(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('checkin_rewards')) {
            return $this->ok(['ladder' => []]);
        }
        $rows = \Illuminate\Support\Facades\DB::table('checkin_rewards')
            ->orderBy('day')
            ->get();
        return $this->ok(['ladder' => $rows]);
    }

    public function adminUpsertCheckinDay(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('checkin_rewards')) {
            return $this->fail('412', 'checkin_rewards table missing');
        }
        $day = max(1, (int) ($body['day'] ?? 0));
        if ($day <= 0 || $day > 30) {
            return $this->fail('400', 'day must be 1..30');
        }
        $data = [
            'reward_amount' => max(0, (int) ($body['reward_amount'] ?? 0)),
            'is_active' => (int) ((bool) ($body['is_active'] ?? true)),
            'updated_at' => now(),
        ];
        $existing = \Illuminate\Support\Facades\DB::table('checkin_rewards')
            ->where('day', $day)
            ->first();
        if ($existing) {
            \Illuminate\Support\Facades\DB::table('checkin_rewards')
                ->where('day', $day)
                ->update($data);
        } else {
            $data['day'] = $day;
            $data['created_at'] = now();
            \Illuminate\Support\Facades\DB::table('checkin_rewards')->insert($data);
        }
        return $this->ok(['ok' => true, 'day' => $day]);
    }

    // ---- helpers ----

    private function body(Request $request): array
    {
        $body = $request->all();
        if (empty($body)) {
            $raw = $request->getContent();
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $body = $decoded;
                }
            }
        }
        return $body;
    }

    private function authGate(array $body): bool
    {
        $token = (string) ($body['access_token'] ?? '');
        return $token === '0411f0028cfb768b3a3d96ac3aa37dw3e5';
    }

    private function ok(array $payload): JsonResponse
    {
        return response()->json([array_merge(['code' => '200'], $payload)]);
    }

    private function fromResult(array $result): JsonResponse
    {
        $code = (string) ($result['code'] ?? ($result['ok'] ?? false ? '200' : '400'));
        return response()->json([array_merge(['code' => $code], $result)]);
    }

    private function fail(string $code, string $message): JsonResponse
    {
        return response()->json([['code' => $code, 'message' => $message]]);
    }
}
