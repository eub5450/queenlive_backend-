<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\MyBeg;
use App\Models\User;
use App\Models\VipList;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Kreait\Firebase\Contract\Database;

class FollowerController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public static function friendCacheKey($userId)
    {
        return "queenlive:user_friend_data_{$userId}";
    }

    public static function forgetFriendCaches($userId, $otherUserId = null)
    {
        $ids = array_values(array_unique(array_filter([
            trim((string) $userId),
            trim((string) $otherUserId),
        ])));

        if (empty($ids)) {
            return;
        }

        try {
            foreach ($ids as $id) {
                Redis::del(self::friendCacheKey($id));
                Cache::forget(self::friendCacheKey($id));
            }
        } catch (\Exception $e) {
            Log::warning("Follower cache clear failed", [
                'user_id' => $userId,
                'other_user_id' => $otherUserId,
                'error' => $e->getMessage(),
            ]);
        }

        UserDataController::forgetProfileCachesForPair($userId, $otherUserId);
    }

    public static function buildFriendIndexPayload($userId)
    {
        $follows = Follower::where('user_id', $userId)->pluck('follower_id')->toArray();
        $friendIds = Follower::whereIn('user_id', $follows)
            ->where('follower_id', $userId)
            ->pluck('user_id')
            ->toArray();

        $friendDetails = User::whereIn('id', $friendIds)
            ->select('name', 'id', 'level', 'is_vip', 'frame', 'profile')
            ->get();

        $follower = DB::table('followers')
            ->join('users', 'users.id', '=', 'followers.follower_id')
            ->select('users.name', 'users.id', 'users.level', 'users.is_vip', 'users.frame', 'users.profile')
            ->where('followers.user_id', $userId)
            ->whereNotIn('users.id', $friendIds)
            ->orderByDesc('followers.id')
            ->get();

        $following = DB::table('followers')
            ->join('users', 'users.id', '=', 'followers.user_id')
            ->select('users.name', 'users.id', 'users.level', 'users.is_vip', 'users.frame', 'users.profile')
            ->where('followers.follower_id', $userId)
            ->whereNotIn('users.id', $friendIds)
            ->orderByDesc('followers.id')
            ->get();

        $visitors = DB::table('visitors')
            ->join('users', 'users.id', '=', 'visitors.user_id')
            ->select('users.name', 'users.id', 'users.level', 'users.is_vip', 'users.frame', 'users.profile')
            ->where('visitors.receiver_id', $userId)
            ->orderByDesc('visitors.id')
            ->get();

        $is_live = DB::table('user_lives')
            ->join('users', 'users.id', '=', 'user_lives.user_id')
            ->select('users.name', 'users.balance', 'users.id', 'users.level', 'users.is_vip', 'users.frame', 'users.profile', 'user_lives.pin', 'user_lives.sdk')
            ->where('user_lives.user_id', $userId)
            ->orderByDesc('user_lives.id')
            ->first();

        $vip_lists = VipList::where('user_id', $userId)
            ->select('image', 'vip_no')
            ->get();

        $my_stores = MyBeg::where('user_id', $userId)
            ->select('image', 'name')
            ->get();

        return [
            'friends' => $friendDetails,
            'follower' => $follower,
            'following' => $following,
            'live_data' => $is_live,
            'vip_lists' => $vip_lists,
            'visitors' => $visitors,
            'my_stores' => $my_stores,
        ];
    }

    public static function loadFriendIndexPayload($userId, $forceDb = false)
    {
        if ($forceDb) {
            return self::buildFriendIndexPayload($userId);
        }

        $cacheKey = self::friendCacheKey($userId);

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return unserialize($cached);
            }
        } catch (\Exception $e) {
            Log::warning("Redis friend payload read failed", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        $data = self::buildFriendIndexPayload($userId);

        try {
            Redis::setex($cacheKey, 300, serialize($data));
        } catch (\Exception $e) {
            Log::warning("Redis friend payload write failed", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    public static function followRelationState($viewerId, $targetId)
    {
        $viewerId = trim((string) $viewerId);
        $targetId = trim((string) $targetId);

        if ($viewerId === '' || $targetId === '' || $viewerId === $targetId) {
            return 0;
        }

        $viewerFollows = Follower::where('user_id', $viewerId)
            ->where('follower_id', $targetId)
            ->exists();

        if (!$viewerFollows) {
            return 0;
        }

        $targetFollows = Follower::where('user_id', $targetId)
            ->where('follower_id', $viewerId)
            ->exists();

        return $targetFollows ? 2 : 1;
    }

    private static function followStatePayload($message, $state)
    {
        return [[
            'message' => $message,
            'code' => '200',
            'is_follow' => $state,
        ]];
    }

    public function Store(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        $user_id = $request->user_id;
        $follower_id = $request->follower_id;
        $channelName = $request->channelName ?: "offline";

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $check_old_follow = Follower::where('user_id', $user_id)
            ->where('follower_id', $follower_id)
            ->first();

        if ($check_old_follow) {
            self::forgetFriendCaches($user_id, $follower_id);
            $is_i_follow = self::followRelationState($user_id, $follower_id);
            return json_encode(self::followStatePayload('Already Followed', $is_i_follow), JSON_UNESCAPED_UNICODE);
        }

        $follower = new Follower;
        $follower->user_id = $user_id;
        $follower->follower_id = $follower_id;
        $follower->date = date('Y-m-d');
        $follower->save();
        self::forgetFriendCaches($user_id, $follower_id);

        $sender_name = User::find($user_id);
        $user_main = User::find($follower_id);
        $commnet_message = "$sender_name->name. follow $user_main->name 💛";

        $comment = new Comment;
        $comment->user_id = $user_id;
        $comment->channelName = $channelName;
        $comment->message = $commnet_message;
        $comment->reciever_id = $follower_id;
        $comment->type = 'message';
        $comment->save();

        $gift_comment = [
            'balance' => strval($sender_name->balance),
            'channelName' => strval($channelName),
            'id' => $sender_name->id,
            'message' => strval('@' . $commnet_message),
            'level' => strval($sender_name->level),
            'name' => strval($sender_name->name),
            'profile' => strval($sender_name->profile),
            'is_vip' => strval($sender_name->is_vip),
            'frame' => strval($sender_name->frame),
            'is_official_id' => strval($sender_name->is_official_id),
            'is_agency' => strval($sender_name->is_agency),
            'is_host_id' => strval($sender_name->is_host_id),
            'comment_badge' => strval($sender_name->comment_badge),
            'type' => 'message',
        ];

// [Firebase dead]         $comments_ref = $this->database->getReference('NewComments/' . $channelName);
// [Firebase dead]         $existing_comments = $comments_ref->getValue();
// [Firebase dead]         $next_index = is_array($existing_comments) ? count($existing_comments) : 0;
// [Firebase dead]         $next_comment_ref = $this->database->getReference('NewComments/' . $channelName . '/' . $next_index);
// [Firebase dead]         $next_comment_ref->set($gift_comment);

        $is_i_follow = self::followRelationState($user_id, $follower_id);
        return json_encode(self::followStatePayload('An Audience Followed Successfully', $is_i_follow), JSON_UNESCAPED_UNICODE);
    }

    public function UnFollow(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        $user_id = $request->user_id;
        $follower_id = $request->follower_id;

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $check_old_follow = Follower::where('user_id', $user_id)
            ->where('follower_id', $follower_id)
            ->first();

        if ($check_old_follow) {
            $check_old_follow->delete();
            self::forgetFriendCaches($user_id, $follower_id);
            $is_i_follow = self::followRelationState($user_id, $follower_id);
            return json_encode(self::followStatePayload('An Audience UnFollowed Successfully', $is_i_follow), JSON_UNESCAPED_UNICODE);
        }

        $is_i_follow = self::followRelationState($user_id, $follower_id);
        return json_encode(self::followStatePayload('Already Unfollowed', $is_i_follow), JSON_UNESCAPED_UNICODE);
    }

    public function Follow(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        $user_id = $request->user_id;
        $follower_id = $request->follower_id;

        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response[] = ['message' => 'Unauthorized', 'code' => '401'];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $check_old_follow = Follower::where('user_id', $user_id)
            ->where('follower_id', $follower_id)
            ->first();

        if ($check_old_follow) {
            self::forgetFriendCaches($user_id, $follower_id);
            $is_i_follow = self::followRelationState($user_id, $follower_id);
            return json_encode(self::followStatePayload('Already Followed', $is_i_follow), JSON_UNESCAPED_UNICODE);
        }

        $follower = new Follower;
        $follower->user_id = $user_id;
        $follower->follower_id = $follower_id;
        $follower->date = date('Y-m-d');
        $follower->save();
        self::forgetFriendCaches($user_id, $follower_id);

        $is_i_follow = self::followRelationState($user_id, $follower_id);
        return json_encode(self::followStatePayload('An Audience Followed Successfully', $is_i_follow), JSON_UNESCAPED_UNICODE);
    }

    public function FriendIndex(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        $user_id = $request->user_id;

        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $data = self::loadFriendIndexPayload($user_id);
        $response[] = array_merge(['message' => 'Follower List Showing Successfully', 'code' => '200'], $data);

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
