<?php

namespace App\Http\Controllers\Api\V4;

use App\Helpers\PerformanceLogger;
use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\Gift;
use App\Models\Kick;
use App\Models\Slider;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use RedisCacheFunction;

class UserDataController extends Controller
{
    private const CACHE_PREFIX = 'queenlive:';

    public static function hostTypeCacheKey($userId)
    {
        return self::CACHE_PREFIX . "host_type_{$userId}";
    }

    public static function profileViewCacheKey($viewerId, $profileId)
    {
        return self::CACHE_PREFIX . "profile_view_{$viewerId}_{$profileId}";
    }

    public static function profileSnapshotCacheKey($viewerId, $profileId)
    {
        return self::CACHE_PREFIX . "profile_snapshot_{$viewerId}_{$profileId}";
    }

    public static function legacyProfileViewCacheKey($userId)
    {
        return self::CACHE_PREFIX . "profile_view_{$userId}";
    }

    public static function forgetProfileCachesForPair($userId, $hostId = null)
    {
        $ids = array_values(array_unique(array_filter([
            trim((string) $userId),
            trim((string) $hostId),
        ])));

        if (empty($ids)) {
            return;
        }

        $keys = [];
        foreach ($ids as $viewerId) {
            $keys[] = self::legacyProfileViewCacheKey($viewerId);
            $keys[] = self::profileViewCacheKey($viewerId, $viewerId);
            $keys[] = self::profileSnapshotCacheKey($viewerId, $viewerId);

            foreach ($ids as $targetId) {
                $keys[] = self::profileViewCacheKey($viewerId, $targetId);
                $keys[] = self::profileSnapshotCacheKey($viewerId, $targetId);
            }
        }

        try {
            foreach (array_values(array_unique($keys)) as $key) {
                Redis::del($key);
            }
        } catch (\Exception $e) {
            Log::warning("Redis profile cache clear failed", [
                'viewer_id' => $userId,
                'target_id' => $hostId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function resolveViewerAndProfileIds(Request $request)
    {
        $viewerId = trim((string) ($request->host_id ?: $request->user_id));
        $profileId = trim((string) ($request->user_id ?: $request->host_id));

        if ($viewerId === '') {
            $viewerId = $profileId;
        }

        if ($profileId === '') {
            $profileId = $viewerId;
        }

        return [$viewerId, $profileId];
    }

    public function HostType(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        $user_id = $request->user_id;

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $cacheKey = self::hostTypeCacheKey($user_id);

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $host_type = $cached;
            } else {
                $hostData = RedisCacheFunction::getHostData($user_id);
                $host_type = $hostData['hosting_type'] ?? '0';
                Redis::setex($cacheKey, 3600, $host_type);
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for host_type", ['error' => $e->getMessage()]);
            $hostData = RedisCacheFunction::getHostData($user_id);
            $host_type = $hostData['hosting_type'] ?? '0';
        }

        $response[] = [
            'message' => 'Host Type Show Successfully',
            'code' => '200',
            'host_type' => $host_type,
            'sdk_type' => 1,
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function Index(Request $request)
    {
        $token = $request->access_token;
        [$viewerId, $profileId] = self::resolveViewerAndProfileIds($request);

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode([['message' => 'Unauthorized', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }

        $perf = new PerformanceLogger();
        $perf->start();

        $cacheKey = self::profileViewCacheKey($viewerId, $profileId);
        $cacheStatus = 'miss';

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $response = unserialize($cached);
                $cacheStatus = 'hit';
            } else {
                $response = $this->generateProfileResponse($viewerId, $profileId);
                Redis::setex($cacheKey, 120, serialize($response));
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for profile view", ['error' => $e->getMessage()]);
            $response = $this->generateProfileResponse($viewerId, $profileId, true);
        }

        $perf->end($viewerId, ['profile_view' => $cacheStatus], $response);

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function ProfileSnapshot(Request $request)
    {
        $token = $request->access_token;
        [$viewerId, $profileId] = self::resolveViewerAndProfileIds($request);

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode([['message' => 'Unauthorized', 'code' => '401']], JSON_UNESCAPED_UNICODE);
        }

        $perf = new PerformanceLogger();
        $perf->start();

        $cacheKey = self::profileSnapshotCacheKey($viewerId, $profileId);
        $cacheStatus = 'miss';

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $response = unserialize($cached);
                $cacheStatus = 'hit';
            } else {
                $response = $this->generateProfileSnapshotResponse($viewerId, $profileId);
                Redis::setex($cacheKey, 120, serialize($response));
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for profile snapshot", ['error' => $e->getMessage()]);
            $response = $this->generateProfileSnapshotResponse($viewerId, $profileId, true);
        }

        $perf->end($viewerId, ['profile_snapshot' => $cacheStatus], $response);

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    private function generateProfileResponse($viewerId, $profileId, $forceDb = false)
    {
        $response = [];
        $data = RedisCacheFunction::UserfindById($profileId);

        if (!$data) {
            return [['message' => 'User Not Found', 'code' => '404']];
        }

        $start_date = date('Y-m') . '-01';
        $end_date = date('Y-m-t');
        $hostData = RedisCacheFunction::getHostData($profileId);
        $host_type = $hostData['hosting_type'] ?? '0';
        $agency_name = $hostData['agency_name'] ?? '';

        if ($forceDb) {
            $total_gift_coin = Gift::where('reciever_id', $profileId)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('value');

            $total_withdraw = Withdraw::where('host_id', $profileId)
                ->whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->sum('total');

            $kick_data = Kick::where('user_id', $profileId)->get();
            $total_sanding = Gift::where('sander_id', $profileId)->sum('value');
            $total_receiving = Gift::where('reciever_id', $profileId)->sum('value');
            $slider = Slider::orderBy('id', 'desc')->get();
        } else {
            $total_gift_coin = RedisCacheFunction::getGiftBetweenSumDates($profileId, $start_date, $end_date);
            $total_withdraw = RedisCacheFunction::getUserWithdrawSumBetweenDates($profileId, $start_date, $end_date);
            $kick_data = RedisCacheFunction::getUserKicks($profileId);
            // Agent SC1 / 2026-06-28: DISPLAY-ONLY gift totals via the V5
            // versioned state cache (etag-style reuse; recomputed only after a
            // gift bump). Same authoritative SUM formula as before, so the
            // served value is byte-identical. NOT a spend check.
            $v5State = \App\Services\V5\V5UserStateCache::get((string) $profileId);
            $total_sanding = $v5State['total_gifts_sent'];
            $total_receiving = $v5State['total_gifts_received'];
            $slider = RedisCacheFunction::getSlider();
        }

        $balance = $total_gift_coin - $total_withdraw;

        if ($viewerId === $profileId) {
            $follow_status = 0;
            $is_i_follow = 0;
            $message = 'User Data Show Successfully won';
        } else {
            $follow_status = FollowerController::followRelationState($viewerId, $profileId);
            $is_i_follow = $follow_status;
            $message = 'User Data Show Successfully user';
        }

        $response[] = [
            'message' => $message,
            'code' => '200',
            'data' => $data,
            'follow_status' => $follow_status,
            'balance' => $balance,
            'total_sanding' => strval($total_sanding),
            'total_receiving' => strval($total_receiving),
            'agency_name' => $agency_name,
            'host_type' => $host_type,
            'marchent' => $data->is_agency,
            'is_coin_protal_active' => $data->is_coin_protal_active,
            'kick_data' => $kick_data,
            'is_i_follow' => $is_i_follow,
            'slider' => $slider,
            'is_vip' => $data->is_vip,
            'frame' => $data->frame,
            'entry_effect' => $data->entry,
            'banned_type' => $data->ban_type,
        ];

        return $response;
    }

    private function generateProfileSnapshotResponse($viewerId, $profileId, $forceDb = false)
    {
        $userInfoResponse = $this->generateProfileResponse($viewerId, $profileId, $forceDb);
        $userInfo = $userInfoResponse[0] ?? null;

        if (!is_array($userInfo) || ($userInfo['code'] ?? '') !== '200') {
            return $userInfoResponse;
        }

        $followData = FollowerController::loadFriendIndexPayload($profileId, $forceDb);

        return [[
            'message' => 'Profile Snapshot Show Successfully',
            'code' => '200',
            'user_info' => $userInfo,
            'follow_data' => array_merge([
                'message' => 'Follower List Showing Successfully',
                'code' => '200',
            ], $followData),
        ]];
    }
}
