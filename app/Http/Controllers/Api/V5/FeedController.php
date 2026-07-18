<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use RedisCacheFunction;

/**
 * Combined feed endpoint replacing two parallel initial-load calls:
 *   GET api/v4/app_home_live_now?page=1
 *   GET api/v4/app_home
 *
 * The Flutter app parses AppHomeLiveNow and AppHomeModel from the same
 * JSON response map — no new model classes needed.
 *
 * Redis keys are shared with the existing controllers so both code paths
 * stay in sync with the same cached data.
 */
class FeedController extends Controller
{
    private string $prefix = 'queenlive:';
    private string $unifiedLiveCachePrefix = 'live_list_v2_page_';

    private array $roomSelect = [
        'users.name', 'users.id', 'users.level', 'users.profile',
        'user_lives.token', 'user_lives.channelName', 'user_lives.type',
        'user_lives.backgorund', 'user_lives.notice', 'user_lives.bullet_notice',
        'user_lives.pin', 'user_lives.audio_brd_design', 'users.host_badge',
        'user_lives.avatar', 'user_lives.sdk', 'user_lives.appId',
        'user_lives.siteNumber',
    ];

    public function Index(Request $request)
    {
        $token  = $request->access_token;
        $userId = $request->user_id;

        if ($token !== '0411f0028cfb768b3a3d96ac3aa37dw3e5') {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $user = RedisCacheFunction::UserfindById($userId);
        if (!$user) {
            return response()->json([['message' => 'User Not Found', 'code' => '404']], 404);
        }

        // Fetch two Redis keys in one round-trip.
        $page1Key    = $this->prefix . $this->unifiedLiveCachePrefix . '1';
        $followerKey = $this->prefix . "followerLive_{$userId}";

        [$rawPage1, $rawFollower] = Redis::pipeline(function ($pipe) use ($page1Key, $followerKey) {
            $pipe->get($page1Key);
            $pipe->get($followerKey);
        });

        // Unified popular rooms page 1 (top section and list share this source).
        if ($rawPage1) {
            $lives = unserialize($rawPage1);
        } else {
            $lives = $this->buildUnifiedLivePaginator(30, 1);
            try { Redis::setex($page1Key, 600, serialize($lives)); } catch (\Throwable $e) {}
        }
        $topLive = $this->extractTopLiveFromPaginator($lives);

        // Follower live (user-specific, max 5).
        if ($rawFollower) {
            $followerLive = unserialize($rawFollower);
        } else {
            $followerLive = $this->getFollowerLive($userId);
            try { Redis::setex($followerKey, 300, serialize($followerLive)); } catch (\Throwable $e) {}
        }

        $setting      = RedisCacheFunction::getSetting();
        $slider       = RedisCacheFunction::getSlider();
        $commentSkips = RedisCacheFunction::getCommentSkips();

        return response()->json([[
            'message'            => 'Feed OK',
            'code'               => '200',
            // AppHomeLiveNow fields
            'top_live'           => $topLive,
            'lives'              => $lives,
            // AppHomeModel fields
            'profile'            => $user->profile,
            'id'                 => $user->id,
            'name'               => $user->name,
            'level'              => $user->level,
            'image'              => $user->profile,
            'is_host_id'         => $user->is_host_id,
            'is_agency'          => $user->is_agency,
            'status'             => $user->status,
            'role'               => $user->role,
            'brd_off_power'      => $user->brd_off_power,
            'can_invisible'      => $user->is_invisible,
            'host_type'          => $user->hosting_type ?? 0,
            'sceen_short_power'  => $user->sceen_short_power,
            'comment_mute_power' => $user->comment_mute_power,
            'kick_power'         => $user->kick_power,
            'slider'             => $slider,
            'invite_popup'       => 1,
            'comment_skips'      => $commentSkips,
            'pusher_key'         => $setting->key ?? '',
            'pusher_app_id'      => $setting->app_id ?? '',
            'pusher_cluster'     => $setting->cluster ?? '',
            'follower_live'      => $followerLive,
        ]], 200, ['options' => JSON_UNESCAPED_UNICODE]);
    }

    private function getFollowerLive(string $userId): array
    {
        try {
            return \App\Models\User::select([
                'users.name', 'users.id', 'users.level', 'users.profile',
                'users.host_badge',
                'user_lives.token', 'user_lives.channelName', 'user_lives.type',
                'user_lives.backgorund', 'user_lives.notice', 'user_lives.bullet_notice',
                'user_lives.pin', 'user_lives.audio_brd_design',
                'user_lives.avatar', 'user_lives.sdk', 'user_lives.appId',
                'user_lives.siteNumber',
            ])
            ->join('followers', 'followers.follower_id', '=', 'users.id')
            ->join('user_lives', 'user_lives.user_id', '=', 'users.id')
            ->where('followers.user_id', $userId)
            ->whereNotNull('user_lives.token')
            ->orderByDesc('users.id')
            ->limit(5)
            ->get()
            ->map(function ($u) {
                return [
                    'id'               => $u->id,
                    'name'             => $u->name,
                    'level'            => $u->level,
                    'profile'          => $u->profile,
                    'host_badge'       => $u->host_badge,
                    'token'            => $u->token,
                    'channel'          => $u->channelName,
                    'channelName'      => $u->channelName,
                    'type'             => $u->type,
                    'sdk'              => $u->sdk,
                    'appId'            => $u->appId,
                    'siteNumber'       => $u->siteNumber,
                    'avatar'           => $u->avatar,
                    'backgorund'       => $u->backgorund,
                    'notice'           => $u->notice,
                    'bullet_notice'    => $u->bullet_notice,
                    'pin'              => $u->pin,
                    'audio_brd_design' => $u->audio_brd_design,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::error('FeedController getFollowerLive failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildUnifiedLivePaginator(int $perPage, int $page)
    {
        return DB::table('user_lives')
            ->join('users', 'users.id', '=', 'user_lives.user_id')
            ->select($this->roomSelect)
            ->orderByDesc('user_lives.is_top')
            ->orderByRaw('CASE WHEN user_lives.is_top = 1 THEN user_lives.top_value ELSE 0 END DESC')
            ->orderByRaw('CASE WHEN user_lives.is_top = 0 THEN user_lives.live_sort_rank ELSE 999999 END ASC')
            ->orderByRaw('CASE WHEN user_lives.is_top = 0 THEN user_lives.top_value ELSE 0 END DESC')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function extractTopLiveFromPaginator($lives, int $limit = 2): array
    {
        if (!$lives || !method_exists($lives, 'items')) {
            return [];
        }

        return array_values(array_slice($lives->items(), 0, $limit));
    }

    public function Sections(Request $request)
    {
        return app(\App\Http\Controllers\Api\V4\FeedController::class)->Sections($request);
    }
}
