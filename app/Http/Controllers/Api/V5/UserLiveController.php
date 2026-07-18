<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLive;
use Carbon;
use DB;
use App\Models\LiveCall;
use App\Models\Comment;
use App\Models\User;
use App\Models\Gift;
use App\Models\AudienceJoin;
use App\Models\DayTime;
use App\Models\BanDevice;
use App\Models\DeviceLockInvite;
use App\Models\Slider;
use App\Models\Setting;
use App\Models\BedWord;
use Auth;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\PerformanceLogger;
use Illuminate\Support\Facades\Redis;
use RedisCacheFunction;

class UserLiveController extends Controller
{
    private $prefix = 'queenlive:';
    private string $unifiedLiveCachePrefix = 'live_list_v2_page_';
    
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Legacy route compatibility for /user_live_home.
     */
    public function Index(Request $request)
    {
        return $this->LivesNowIndex($request);
    }
    
    /**
     * Home Index - Optimized with Redis Direct
     */
    public function HomeIndex(Request $request)
    {
        
        $token = $request->access_token;
        $userId = $request->user_id;
        // info('Home Page Time Portal Transfer', [
        //                 'user_id' => $request->user()->id,
        //                 'ip' => $request->header('CF-Connecting-IP') ?? $request->ip(),
        //                 'user_agent' => $request->header('User-Agent'),
        //                 'time' => now()->toDateTimeString()
        //             ]);
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }
        
        $user = RedisCacheFunction::UserfindById($userId);
     
        if (!$user) {
            return response()->json([
                ['message' => 'User Not Found', 'code' => '404']
            ], 404, ['options' => JSON_UNESCAPED_UNICODE]);
        }
    
        // Boss 2026-07-11: when a host returns to app_home without a clean room-end request,
        // remove their stale room before building home/list payloads so they never see
        // their own dead room in Random/Party/other room lists.
        $this->cleanupSelfLiveRows($userId);
        
        $isHost = $user ? $user->hosting_type : 0;
        $invitePopup = 1; 
        
        $comment_skips = RedisCacheFunction::getCommentSkips();
        $setting = RedisCacheFunction::getSetting();
        $slider = RedisCacheFunction::getSlider();
    
        $followerCacheKey = $this->prefix . "followerLive_{$userId}";
        
        try {
            $cachedFollower = Redis::get($followerCacheKey);
            if ($cachedFollower) {
                $followerLive = unserialize($cachedFollower);
            } else {
                $followerLive = $this->getFollowerLiveFromDB($userId);
                Redis::setex($followerCacheKey, 300, serialize($followerLive));
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for followerLive", ['error' => $e->getMessage()]);
            $followerLive = $this->getFollowerLiveFromDB($userId);
        }
        
        // Avoid DB writes on the hot home endpoint.
    
        $response = [
            'message' => 'Home Page Data Show',
            'profile' => $user->profile,
            'id' => $user->id,
            'name' => $user->name,
            'balance' => $user->balance,
            'level' => $user->level,
            'is_host_id' => $user->is_host_id,
            'is_agency' => $user->is_agency,
            'status' => $user->status,
            'role' => $user->role,
            'image' => $user->profile,
            'brd_off_power' => $user->brd_off_power,
            'can_invisible' => $user->is_invisible,
            'host_type' => $isHost,
            'sceen_short_power' => $user->sceen_short_power,
            'comment_mute_power' => $user->comment_mute_power,
            'kick_power' => $user->kick_power,
            'slider' => $slider,
            'invite_popup' => $invitePopup,
            'comment_skips' => $comment_skips,
            'pusher_key' => $setting->key,
            'pusher_app_id' => $setting->app_id,
            'pusher_cluster' => $setting->cluster,
            'follower_live' => $followerLive,
            'code' => '200',
        ];
    
        return response()->json([$response], 200, ['options' => JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Legacy route compatibility for /user_live_store.
     */
    public function Store(Request $request)
    {
        $response = array();
        $accessToken = $request->access_token;
        $userId = $request->user_id;
        $channelName = $request->channelName;

        if ($accessToken !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        if (!$userId || !$channelName) {
            return response()->json([['message' => 'Missing user_id or channelName', 'code' => '422']], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $user = RedisCacheFunction::UserfindById($userId);
        if (!$user || $user->ban_type !== null) {
            return response()->json([['message' => 'Sorry Your ID Banned', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        LiveCall::where('co_host_id', $userId)->delete();

        $todayGift = Gift::where('reciever_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->sum('value');

        UserLive::storeOneActiveForUser(array(
            'user_id' => $userId,
            'channelName' => $channelName,
            'name' => $user->name,
            'date' => \Carbon\Carbon::now(),
            'token' => $request->token,
            'type' => $request->type,
            'mute' => 0,
            'top_value' => $user->top_value + $todayGift,
            'audio_brd_design' => $request->audio_brd_design ?: '1',
            'notice' => $request->notice,
            'bullet_notice' => $request->bullet_notice,
            'pin' => in_array((int) $userId, [1111, 22401]) ? 5450 : $request->pin,
            'avatar' => $user->profile,
            'sdk' => $request->sdk,
            'backgorund' => $request->image ?: $request->backgorund,
            'appId' => $request->app_id,
            'siteNumber' => $request->siteNumber,
        ));

        $this->clearHomeLiveCaches($userId);

        $host = array(
            'channelName' => $channelName,
            'profile' => $user->profile,
            'is_vip' => $user->is_vip,
            'balance' => 0,
            'co_host_name' => $user->name,
            'set_no' => '0',
            'mute' => '1',
            'frame' => strval($user->frame),
            'co_host_id' => strval($user->id),
            'co_host_status' => 'Accept',
            'super_mute' => '0',
        );

        array_push($response, array(
            'message' => 'Live Data Store Successfully',
            'host_list' => array($host),
            'channelName' => $channelName,
            'code' => '200',
        ));

        $this->Websoket(array(array(
            'message' => 'bd_live_store',
            'data' => $response,
            'channelName' => $channelName,
            'code' => '200',
            'room_type' => strval($request->type),
        )));

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Party Index - Redis Direct
     */
    public function PartyIndex(Request $request)
    {
        $token = $request->access_token;
        $response = [];
    
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, ['message' => 'Unauthorized', 'code' => '401']);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    
        $user_id = $request->user_id;
    
        if (!RedisCacheFunction::UserfindById($user_id)) {
            array_push($response, ['message' => 'User Not Found', 'code' => '404']);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    
        $channelNames = UserLive::where('user_id', $user_id)->pluck('channelName');
    
        if ($channelNames->isNotEmpty()) {
            AudienceJoin::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            LiveCall::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            Comment::where('reciever_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            UserLive::where('user_id', $user_id)->delete();
            $this->clearHomeLiveCaches($user_id);
           
        }
    
        $cacheKey = $this->prefix . "live_users_type_1";
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $lives = unserialize($cached);
            } else {
                $lives = DB::table('user_lives')
                    ->join('users', 'users.id', 'user_lives.user_id')
                    ->select(
                        'users.name',
                        'users.id',
                        'users.level',

                        'users.profile',
                        'user_lives.token',
                        'user_lives.channelName',
                        'user_lives.notice',
                        'user_lives.bullet_notice',
                        'user_lives.pin',
                        'user_lives.type',
                        'user_lives.backgorund',
                        'user_lives.audio_brd_design',
                        'users.host_badge', 'users.country_id',
                        'user_lives.avatar',
                        'user_lives.sdk',
                        'user_lives.appId',
                        'user_lives.siteNumber'
                    )
                    ->where('user_lives.type', 1)
                    ->orderBy('users.prosss_top', 'desc')
                    ->get();
                
                Redis::setex($cacheKey, 900, serialize($lives));
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for PartyIndex", ['error' => $e->getMessage()]);
            $lives = DB::table('user_lives')
                ->join('users', 'users.id', 'user_lives.user_id')
                ->select(
                    'users.name',
                    'users.id',
                    'users.level',

                    'users.profile',
                    'user_lives.token',
                    'user_lives.channelName',
                    'user_lives.notice',
                    'user_lives.bullet_notice',
                    'user_lives.pin',
                    'user_lives.type',
                    'user_lives.backgorund',
                    'user_lives.audio_brd_design',
                    'users.host_badge', 'users.country_id',
                    'user_lives.avatar',
                    'user_lives.sdk',
                    'user_lives.appId',
                    'user_lives.siteNumber'
                )
                ->where('user_lives.type', 1)
                ->orderBy('users.prosss_top', 'desc')
                ->get();
        }
    
        array_push($response, ['message' => 'Live Data Store Successfully', 'lives_now' => $lives, 'code' => '200']);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Friends Live - Redis Direct
     */
    public function FriendsLive(Request $request)
    {
        $token = $request->access_token;
        $response = [];
    
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, ['message' => 'Unauthorized', 'code' => '401']);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    
        $user_id = $request->user_id;
        $user = RedisCacheFunction::UserfindById($user_id);
        
        if (!$user) {
            array_push($response, ['message' => 'User Not Found', 'code' => '404']);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    
        $channelNames = UserLive::where('user_id', $user_id)->pluck('channelName');
    
        if ($channelNames->isNotEmpty()) {
            AudienceJoin::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            LiveCall::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            Comment::where('reciever_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            UserLive::where('user_id', $user_id)->delete();
            $this->clearHomeLiveCaches($user_id);
          
        }
    
        $cacheKey = $this->prefix . "live_frined_home";
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $lives = unserialize($cached);
            } else {
                $lives = DB::table('user_lives')
                    ->join('users', 'users.id', 'user_lives.user_id')
                    ->select(
                        'users.name',
                        'users.id',
                        'users.level',

                        'users.profile',
                        'user_lives.token',
                        'user_lives.channelName',
                        'user_lives.notice',
                        'user_lives.bullet_notice',
                        'user_lives.pin',
                        'user_lives.type',
                        'user_lives.backgorund',
                        'user_lives.audio_brd_design',
                        'users.host_badge', 'users.country_id',
                        'user_lives.avatar',
                        'user_lives.sdk',
                        'user_lives.appId',
                        'user_lives.siteNumber'
                    )
                    ->orderBy('users.prosss_top', 'desc')
                    ->get();
                
                Redis::setex($cacheKey, 900, serialize($lives));
            }
        } catch (\Exception $e) {
            Log::error("Redis failed for FriendsLive", ['error' => $e->getMessage()]);
            $lives = DB::table('user_lives')
                ->join('users', 'users.id', 'user_lives.user_id')
                ->select(
                    'users.name',
                    'users.id',
                    'users.level',

                    'users.profile',
                    'user_lives.token',
                    'user_lives.channelName',
                    'user_lives.notice',
                    'user_lives.bullet_notice',
                    'user_lives.pin',
                    'user_lives.type',
                    'user_lives.backgorund',
                    'user_lives.audio_brd_design',
                    'users.host_badge', 'users.country_id',
                    'user_lives.avatar',
                    'user_lives.sdk',
                    'user_lives.appId',
                    'user_lives.siteNumber'
                )
                ->orderBy('users.prosss_top', 'desc')
                ->get();
        }
    
        array_push($response, ['message' => 'Live Data Store Successfully', 'lives_now' => $lives, 'code' => '200']);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Lives Now Index - Redis Direct with Pagination
     */
    public function LivesNowIndex(Request $request)
    {
        $token = $request->access_token;
        $userId = $request->user_id;
        $page = (int) $request->get('page', 1);
        $perPage = max(1, min((int) $request->get('per_page', 30), 50));

        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }

        $user = RedisCacheFunction::UserfindById($userId);
        if (!$user) {
            return response()->json([
                ['message' => 'User Not Found', 'code' => '404']
            ], 404, ['options' => JSON_UNESCAPED_UNICODE]);
        }

        $this->cleanupSelfLiveRows($userId);

        $lives = $this->getCachedUnifiedLivePage($page, $perPage);
        $topSourceLives = $page === 1
            ? $lives
            : $this->getCachedUnifiedLivePage(1, $perPage);
        $topLive = $this->extractTopLiveFromPaginator($topSourceLives);

        return response()->json([[
            'message' => 'Home Page Data Show (Cached)',
            'top_live' => $topLive,
            'lives' => $lives,
            'code' => '200',
        ]], 200, ['options' => JSON_UNESCAPED_UNICODE]);
    }
    
    /**
     * Delete - Clear Redis cache
     */
    public function Delete(Request $request)
    {
        $token = $request->access_token;
        $id = $request->id;
        $day_times = $request->day_times;
        $channelName = $request->channelName;
        $response = array();
     
        
        $hasLegacyAccess = hash_equals('0411f0028cfb768b3a3d96ac3aa37dw3e5', (string) $token);
        $authUser = $request->user();

        if ($authUser && (int) $authUser->id !== (int) $id) {
            array_push($response, array('message' => 'Forbidden user mismatch', 'code' => '403'));
            return response()->json($response, 403, [], JSON_UNESCAPED_UNICODE);
        }

        if($authUser || $hasLegacyAccess){
            $data = UserLive::where('user_id',$id)
                ->where('channelName', $channelName)
                ->first();
            if (!$data) {
                $data = UserLive::where('user_id',$id)->first();
            }
          
            if($data){
                $check_day_time = DayTime::where('user_id',$id)->where('channelName',$channelName)->first();
                if($check_day_time){
                    $check_day_time->day_times = $day_times;
                    $check_day_time->save();
                } else {
                    $daytime = new DayTime;
                    $daytime->user_id = $id;
                    $daytime->channelName = $channelName;
                    $daytime->day_times = $day_times;
                    $daytime->brd_type = $data->type;
                    $daytime->live_time = now()->toDateString();
                    $daytime->save(); 
                }
                
                LiveCall::where('host_id', $id)->delete();
                AudienceJoin::where('host_id', $id)->delete();
                $data->delete();

                // Full feed-cache purge on room end / brd-off / auto-close.
                // Was: only live_list_page_1..5 + 3 list keys; missed pages 6-10
                // and followerLive_{id} -> ended rooms lingered in feeds.
                $this->clearHomeLiveCaches($id);
                
                array_push($response, array('message' => 'A Live Data Removed Successfully', 'code' => '200'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                array_push($response, array('message' => 'User Not In Live', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    
    private function cleanupSelfLiveRows($userId)
    {
        $userId = trim((string) $userId);
        if ($userId === '') {
            return 0;
        }

        $channelNames = UserLive::where('user_id', $userId)->pluck('channelName');
        if ($channelNames->isEmpty()) {
            return 0;
        }

        AudienceJoin::where('host_id', $userId)->whereIn('channelName', $channelNames)->delete();
        LiveCall::where('host_id', $userId)->whereIn('channelName', $channelNames)->delete();
        Comment::where('reciever_id', $userId)->whereIn('channelName', $channelNames)->delete();
        $removed = UserLive::where('user_id', $userId)->delete();
        $this->clearHomeLiveCaches($userId);

        return (int) $removed;
    }

    /**
     * Helper: Get follower live from DB
     */
    private function getFollowerLiveFromDB($userId)
    {
        return User::select(
            'users.name', 'users.id', 'users.level', 'users.profile',
            'user_lives.token', 'user_lives.channelName', 'user_lives.type',
            'user_lives.backgorund', 'user_lives.notice', 'user_lives.bullet_notice',
            'user_lives.pin', 'user_lives.audio_brd_design', 'users.host_badge', 'users.country_id',
            'user_lives.avatar', 'user_lives.sdk','user_lives.appId','user_lives.siteNumber'
        )
        ->join('followers', 'followers.follower_id', '=', 'users.id')
        ->join('user_lives', 'user_lives.user_id', '=', 'users.id')
        ->where('followers.user_id', $userId)
        ->whereNotNull('user_lives.token')
        ->orderByDesc('users.id')
        ->limit(5)
        ->get()
        ->map(function ($user) {
            return [
                'id'                => $user->id,
                'name'              => $user->name,
                'level'             => $user->level,
                'balance'           => $user->balance,
                'profile'           => $user->profile,
                'host_badge'        => $user->host_badge,
                'country_id'        => $user->country_id ?? null,
                'token'             => $user->token,
                'channel'           => $user->channelName,
                'type'              => $user->type,
                'sdk'               => $user->sdk,
                'appId'             => $user->appId,
                'avatar'            => $user->avatar,
                'backgorund'        => $user->backgorund,
                'notice'            => $user->notice,
                'bullet_notice'     => $user->bullet_notice,
                'pin'               => $user->pin,
                'audio_brd_design'  => $user->audio_brd_design,
            ];
        })->toArray();
    }

    private function getCachedUnifiedLivePage(int $page, int $perPage)
    {
        $liveCacheKey = $this->prefix . $this->unifiedLiveCachePrefix . $page;

        try {
            $cachedLives = Redis::get($liveCacheKey);
            if ($cachedLives) {
                return unserialize($cachedLives);
            }

            $lives = $this->buildUnifiedLivePaginator($perPage, $page);
            Redis::setex($liveCacheKey, 600, serialize($lives));
            return $lives;
        } catch (\Exception $e) {
            Log::error("Redis failed for unified live list", ['error' => $e->getMessage(), 'page' => $page]);
            return $this->buildUnifiedLivePaginator($perPage, $page);
        }
    }

    private function buildUnifiedLivePaginator(int $perPage, int $page)
    {
        return DB::table('user_lives')
            ->join('users', 'users.id', '=', 'user_lives.user_id')
            ->select(
                'users.name', 'users.id', 'users.level', 'users.profile',
                'user_lives.token', 'user_lives.channelName', 'user_lives.type',
                'user_lives.backgorund', 'user_lives.notice', 'user_lives.bullet_notice',
                'user_lives.pin', 'user_lives.audio_brd_design', 'users.host_badge', 'users.country_id',
                'user_lives.avatar', 'user_lives.sdk', 'user_lives.appId',
                'user_lives.siteNumber'
            )
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

    private function clearHomeLiveCaches($userId = null)
    {
        $keys = array(
            $this->prefix . 'live_users_type_1',
            $this->prefix . 'live_frined_home',
            $this->prefix . 'live_top_list',
        );

        if ($userId) {
            $keys[] = $this->prefix . "followerLive_{$userId}";
        }

        for ($i = 1; $i <= 10; $i++) {
            $keys[] = $this->prefix . "live_list_page_{$i}";
            $keys[] = $this->prefix . $this->unifiedLiveCachePrefix . $i;
        }

        foreach ($keys as $key) {
            try {
                Redis::del($key);
            } catch (\Throwable $e) {
                Log::warning('Unable to clear live cache key', array('key' => $key, 'error' => $e->getMessage()));
            }
        }
    }

    private function Websoket($data)
    {
        try {
            if (!is_array($data)) {
                $data = (array) $data;
            }

            app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
                ->broadcastLegacyWithRoomScoped($data, ['source' => 'UserLiveController']);
        } catch (\Throwable $e) {
            Log::warning('Named WebSocket dispatch failed', array('error' => $e->getMessage()));
        }
    }
    
    /**
     * Check ID Badge
     */
    public function CheckIDBadge($id)
    {
        $user = RedisCacheFunction::UserfindById($id);
        if ($user) {
            $badge = $user->is_official_id ? 'Official' : ($user->is_agency ? 'Merchant' : '0');
            if ($user->comment_badge !== $badge) {
                $user->update(['comment_badge' => $badge]);
                
            }
        }
        
        UserLive::select('id', 'user_id')
            ->orderBy('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($records) {
                $deletes = [];
                $currentUserId = null;
                foreach ($records as $record) {
                    if ($record->user_id === $currentUserId) {
                        $deletes[] = $record->id;
                    } else {
                        $currentUserId = $record->user_id;
                    }
                }
    
                if (!empty($deletes)) {
                    UserLive::whereIn('id', $deletes)->delete();
                }
            });
    }
}
