<?php
// app/RedisCache/RedisCache.php

namespace App\RedisCache;

use App\Models\User;
use App\Models\BedWord;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Gift;
use App\Models\Withdraw;
use App\Models\LiveCall;
use App\Models\UserLive;
use App\Models\OldGift;
use App\Models\BrdAdmin;
use App\Models\Avater;
use App\Models\Kick;
use DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisCache
{
    private static $prefix = 'queenlive:';
    
    // ==================== USER RELATED METHODS ====================
    
    /**
     * Find user by ID - STATIC METHOD
     * ব্যবহার: RedisCacheFunction::findbyId(1)
     */
    public static function findbyId($id)
    {
        try {
            $key = self::$prefix . "user:{$id}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $user = User::find($id);
            if ($user) {
                Redis::setex($key, 300, serialize($user));
            }
            
            return $user;
        } catch (\Exception $e) {
            Log::error("RedisCache::findbyId() failed for ID: {$id}", [
                'error' => $e->getMessage()
            ]);
            return User::find($id);
        }
    }
    
    /**
     * Alias for findbyId (যদি UserfindById নামেও চান)
     */
    public static function UserfindById($id)
    {
        return self::findbyId($id);
    }
    
    /**
     * Get host data - STATIC METHOD
     */
    public static function getHostData($userId)
    {
        try {
            $key = self::$prefix . "host_data:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $hostData = DB::table('host_data')
                ->join('users', 'users.id', '=', 'host_data.user_id')
                ->join('agencies', 'agencies.code', '=', 'host_data.agency_code')
                ->where('users.is_host_id', 1)
                ->where('users.id', $userId)
                ->select('host_data.hosting_type', 'agencies.name')
                ->first();
        
            $result = [
                'hosting_type' => $hostData->hosting_type ?? 0,
                'agency_name' => $hostData->name ?? 'QueenLive'
            ];
            
            Redis::setex($key, 300, serialize($result));
            
            return $result;
        } catch (\Exception $e) {
            Log::error("RedisCache::getHostData() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $hostData = DB::table('host_data')
                ->join('users', 'users.id', '=', 'host_data.user_id')
                ->join('agencies', 'agencies.code', '=', 'host_data.agency_code')
                ->where('users.is_host_id', 1)
                ->where('users.id', $userId)
                ->select('host_data.hosting_type', 'agencies.name')
                ->first();
        
            return [
                'hosting_type' => $hostData->hosting_type ?? 0,
                'agency_name' => $hostData->name ?? 'QueenLive'
            ];
        }
    }
    
    /**
     * Get hosting type - STATIC METHOD
     */
    public static function getHostingType($userId)
    {
        try {
            $key = self::$prefix . "hosting_type:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $user = self::findbyId($userId);
            $type = $user ? $user->hosting_type : 0;
            
            Redis::setex($key, 300, $type);
            
            return $type;
        } catch (\Exception $e) {
            Log::error("RedisCache::getHostingType() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $user = User::find($userId);
            return $user ? $user->hosting_type : 0;
        }
    }
    
    // ==================== SETTINGS & CONFIGURATION ====================
    
    /**
     * Get setting - STATIC METHOD
     */
    public static function getSetting()
    {
        try {
            $key = self::$prefix . "setting";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $setting = Setting::find(1);
            if ($setting) {
                Redis::setex($key, 18000, serialize($setting));
            }
            
            return $setting;
        } catch (\Exception $e) {
            Log::error("RedisCache::getSetting() failed", [
                'error' => $e->getMessage()
            ]);
            return Setting::find(1);
        }
    }
    
    // ==================== SLIDER ====================
    
    /**
     * Get slider - STATIC METHOD
     */
    public static function getSlider()
    {
        try {
            $key = self::$prefix . "slider";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $slider = Slider::orderBy('id', 'desc')->get();
            Redis::setex($key, 2592000, serialize($slider));
            
            return $slider;
        } catch (\Exception $e) {
            Log::error("RedisCache::getSlider() failed", [
                'error' => $e->getMessage()
            ]);
            return Slider::orderBy('id', 'desc')->get();
        }
    }
    
    // ==================== BAD WORDS ====================
    
    /**
     * Get comment skips - STATIC METHOD
     */
    public static function getCommentSkips()
    {
        try {
            $key = self::$prefix . "bad_words";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $words = BedWord::select('word')->get();
            Redis::setex($key, 2592000, serialize($words));
            
            return $words;
        } catch (\Exception $e) {
            Log::error("RedisCache::getCommentSkips() failed", [
                'error' => $e->getMessage()
            ]);
            return BedWord::select('word')->get();
        }
    }
    
    // ==================== GIFT STATISTICS ====================
    
    /**
     * Get total sent gifts - STATIC METHOD
     */
    public static function getTotalUserSandingGift($userId)
    {
        try {
            $key = self::$prefix . "sent_gift:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('sander_id', $userId)->sum('value');
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getTotalUserSandingGift() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            return Gift::where('sander_id', $userId)->sum('value');
        }
    }
    public static function getTotalRecivedChannelWise($userId,$channelName)
    {
        try {
            $key = self::$prefix . "channalwise_recived_gift:{$userId}:{$channelName}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('reciever_id', $userId)
                    ->where('channelName', $channelName)
                    ->sum('value');
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getTotalRecivedChannelWise() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            return Gift::where('reciever_id', $userId)
                    ->where('channelName', $channelName)
                    ->sum('value');
        }
    }
    
    /**
     * Get total received gifts - STATIC METHOD
     */
    public static function getTotalUserReceivingGift($userId)
    {
        try {
            $key = self::$prefix . "received_gift:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('reciever_id', $userId)->sum('value');
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getTotalUserReceivingGift() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            return Gift::where('reciever_id', $userId)->sum('value');
        }
    }
    
    /**
     * Get gifts between dates - STATIC METHOD
     */
    public static function getGiftBetweenSumDates($userId, $startDate, $endDate)
    {
        try {
            $key = self::$prefix . "gift_range:{$userId}:{$startDate}:{$endDate}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('reciever_id', $userId)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('value');
                
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getGiftBetweenSumDates() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Gift::where('reciever_id', $userId)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('value');
        }
    }
    
    /**
     * Get today's gift sum - STATIC METHOD
     */
    public static function getUserTodayGiftSum($userId)
    {
        try {
            $today = now()->format('Y-m-d');
            $key = self::$prefix . "today_gift:{$userId}:{$today}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('reciever_id', $userId)
                ->whereDate('date', $today)
                ->sum('value');
                
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserTodayGiftSum() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Gift::where('reciever_id', $userId)
                ->whereDate('date', now()->toDateString())
                ->sum('value');
        }
    }
    
    // ==================== WITHDRAW STATISTICS ====================
    
    /**
     * Get withdraw between dates - STATIC METHOD
     */
    public static function getUserWithdrawSumBetweenDates($userId, $startDate, $endDate)
    {
        try {
            $key = self::$prefix . "withdraw_range:{$userId}:{$startDate}:{$endDate}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Withdraw::where('host_id', $userId)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('total');
                
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserWithdrawSumBetweenDates() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Withdraw::where('host_id', $userId)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('total');
        }
    }
    
    /**
     * Get today's withdraw - STATIC METHOD
     */
    public static function getUserTodayWithdrawSum($userId)
    {
        try {
            $today = now()->format('Y-m-d');
            $key = self::$prefix . "today_withdraw:{$userId}:{$today}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Withdraw::where('host_id', $userId)
                ->whereDate('date', $today)
                ->sum('total');
                
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserTodayWithdrawSum() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Withdraw::where('host_id', $userId)
                ->whereDate('date', now()->toDateString())
                ->sum('total');
        }
    }
    
    // ==================== KICK DATA ====================
    
    /**
     * Get user kicks - STATIC METHOD
     */
    public static function getUserKicks($userId)
    {
        try {
            $key = self::$prefix . "kicks:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $kicks = Kick::where('user_id', $userId)->get();
            Redis::setex($key, 300, serialize($kicks));
            
            return $kicks;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserKicks() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            return Kick::where('user_id', $userId)->get();
        }
    }
    
    /**
     * Get user kicks count - STATIC METHOD
     */
    public static function getUserKicksCount($userId)
    {
        try {
            $key = self::$prefix . "kicks_count:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $kicks = self::getUserKicks($userId);
            $count = $kicks->count();
            
            Redis::setex($key, 300, $count);
            
            return $count;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserKicksCount() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $kicks = Kick::where('user_id', $userId)->get();
            return $kicks->count();
        }
    }
    
    // ==================== COMBINED METHODS ====================
    
    /**
     * Get user profile data - STATIC METHOD
     */
    public static function getUserProfileData($userId)
    {
        try {
            $key = self::$prefix . "profile:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $user = self::findbyId($userId);
            
            if (!$user) {
                return null;
            }
            
            $data = [
                'user' => $user,
                'hosting_type' => $user->hosting_type,
                'total_sent_gift' => self::getTotalUserSandingGift($userId),
                'total_received_gift' => self::getTotalUserReceivingGift($userId),
                'kicks_count' => self::getUserKicksCount($userId),
            ];
            
            Redis::setex($key, 300, serialize($data));
            
            return $data;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserProfileData() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $user = User::find($userId);
            if (!$user) return null;
            
            return [
                'user' => $user,
                'hosting_type' => $user->hosting_type,
                'total_sent_gift' => Gift::where('sander_id', $userId)->sum('value'),
                'total_received_gift' => Gift::where('reciever_id', $userId)->sum('value'),
                'kicks_count' => Kick::where('user_id', $userId)->count(),
            ];
        }
    }
    
    /**
     * Get live stats - STATIC METHOD
     */
    public static function getLiveStats($userId, $channelName, $startDate, $endDate)
    {
        try {
            $key = self::$prefix . "live_stats:{$userId}:{$channelName}:{$startDate}:{$endDate}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $stats = [
                'total_gift' => self::getGiftBetweenSumDates($userId, $startDate, $endDate),
                'today_gift' => self::getUserTodayGiftSum($userId),
                'total_withdraw' => self::getUserWithdrawSumBetweenDates($userId, $startDate, $endDate),
                'today_withdraw' => self::getUserTodayWithdrawSum($userId),
            ];
            
            Redis::setex($key, 300, serialize($stats));
            
            return $stats;
        } catch (\Exception $e) {
            Log::error("RedisCache::getLiveStats() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_gift' => Gift::where('reciever_id', $userId)
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->sum('value'),
                'today_gift' => Gift::where('reciever_id', $userId)
                    ->whereDate('date', now()->toDateString())
                    ->sum('value'),
                'total_withdraw' => Withdraw::where('host_id', $userId)
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->sum('total'),
                'today_withdraw' => Withdraw::where('host_id', $userId)
                    ->whereDate('date', now()->toDateString())
                    ->sum('total'),
            ];
        }
    }
    
    /**
     * Get top profile - STATIC METHOD
     */
    public static function TopProfile($userId)
    {
        try {
            $key = self::$prefix . "top_profile:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return $cached;
            }
            
            $start_date = now()->startOfMonth()->toDateString();
            $end_date = now()->endOfMonth()->toDateString();
            
            $top_total_data = DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('gifts.reciever_id', $userId)
                ->whereDate('gifts.date', '>=', $start_date)
                ->whereDate('gifts.date', '<=', $end_date)
                ->select('users.profile', DB::raw('SUM(gifts.value) as total_value'))
                ->groupBy('users.profile')
                ->orderByDesc('total_value')
                ->limit(1)
                ->first();
        
            $profile = $top_total_data ? $top_total_data->profile : '';
            Redis::setex($key, 300, $profile);
            
            return $profile;
        } catch (\Exception $e) {
            Log::error("RedisCache::TopProfile() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $start_date = now()->startOfMonth()->toDateString();
            $end_date = now()->endOfMonth()->toDateString();
            
            $top_total_data = DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('gifts.reciever_id', $userId)
                ->whereDate('gifts.date', '>=', $start_date)
                ->whereDate('gifts.date', '<=', $end_date)
                ->select('users.profile', DB::raw('SUM(gifts.value) as total_value'))
                ->groupBy('users.profile')
                ->orderByDesc('total_value')
                ->limit(1)
                ->first();
        
            return $top_total_data ? $top_total_data->profile : '';
        }
    }
    
    /**
     * Get total reward - STATIC METHOD
     */
    public static function getTotalReward($userId)
    {
        try {
            $key = self::$prefix . "total_reward:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return $cached;
            }
            
            $total = Gift::where('sander_id', 1)
                ->where('reciever_id', $userId)
                ->sum('value');
        
            $total = (string) max($total, 0);
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getTotalReward() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $total = Gift::where('sander_id', 1)
                ->where('reciever_id', $userId)
                ->sum('value');
        
            return (string) max($total, 0);
        }
    }
    
    /**
     * Get user live - STATIC METHOD
     */
    public static function getUserLive($hostId, $channelName)
    {
        try {
            $key = self::$prefix . "live:{$hostId}:{$channelName}";
            
            $cached = Redis::get($key);
            if ($cached) {
                return unserialize($cached);
            }
            
            $live = UserLive::where('channelName', $channelName)
                ->where('user_id', $hostId)
                ->first();
                
            if ($live) {
                Redis::setex($key, 2100, serialize($live));
            }
            
            return $live;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserLive() failed for host: {$hostId}", [
                'error' => $e->getMessage()
            ]);
            
            return UserLive::where('channelName', $channelName)
                ->where('user_id', $hostId)
                ->first();
        }
    }
    
    /**
     * Get live call accept count - STATIC METHOD
     */
    public static function getLiveCallAcceptCount($hostId, $channelName)
    {
        try {
            $key = self::$prefix . "live_accept:{$hostId}:{$channelName}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $count = LiveCall::where('host_id', $hostId)
                ->where('channelName', $channelName)
                ->where('status', 'Accept')
                ->count();
                
            Redis::setex($key, 600, $count);
            
            return $count;
        } catch (\Exception $e) {
            Log::error("RedisCache::getLiveCallAcceptCount() failed for host: {$hostId}", [
                'error' => $e->getMessage()
            ]);
            
            return LiveCall::where('host_id', $hostId)
                ->where('channelName', $channelName)
                ->where('status', 'Accept')
                ->count();
        }
    }
    
    /**
     * Check if BRD admin - STATIC METHOD
     */
    public static function isBrdAdmin($hostId, $adminId, $type)
    {
        try {
            $key = self::$prefix . "brd_admin:{$hostId}:{$adminId}:{$type}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return $cached === '1';
            }
            
            $exists = BrdAdmin::where('user_id', $hostId)
                ->where('admin_id', $adminId)
                ->where('type', $type)
                ->exists();
                
            Redis::setex($key, 604800, $exists ? '1' : '0');
            
            return $exists;
        } catch (\Exception $e) {
            Log::error("RedisCache::isBrdAdmin() failed for host: {$hostId}", [
                'error' => $e->getMessage()
            ]);
            
            return BrdAdmin::where('user_id', $hostId)
                ->where('admin_id', $adminId)
                ->where('type', $type)
                ->exists();
        }
    }
    
    /**
     * Get user avatar - STATIC METHOD
     */
    public static function getUserAvatar($userId)
    {
        try {
            if (!$userId) return null;
            
            $key = self::$prefix . "avatar:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return $cached ?: null;
            }
            
            $avatar = Avater::where('user_id', $userId)->value('image');
            Redis::setex($key, 604800, $avatar ?: '');
            
            return $avatar;
        } catch (\Exception $e) {
            Log::error("RedisCache::getUserAvatar() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Avater::where('user_id', $userId)->value('image');
        }
    }
    
    /**
     * Get sender total gift - STATIC METHOD
     */
    public static function getSanderTotalGift($userId)
    {
        try {
            if (!$userId) return 0;
            
            $key = self::$prefix . "sander_total:{$userId}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $new_gift = Gift::where('sander_id', $userId)->sum('value') ?? 0;
            $archive = OldGift::where('sander_id', $userId)->sum('value') ?? 0;
            $total = $new_gift + $archive;
            
            Redis::setex($key, 600, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getSanderTotalGift() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            $new_gift = Gift::where('sander_id', $userId)->sum('value') ?? 0;
            $archive = OldGift::where('sander_id', $userId)->sum('value') ?? 0;
            return $new_gift + $archive;
        }
    }
    
    /**
     * Get channel wise gift received - STATIC METHOD
     */
    public static function getChannalWiseGiftRecived($userId, $channelName)
    {
        try {
            $key = self::$prefix . "channel_gift:{$userId}:{$channelName}";
            
            $cached = Redis::get($key);
            if ($cached !== null) {
                return (int) $cached;
            }
            
            $total = Gift::where('reciever_id', $userId)
                ->where('channelName', $channelName)
                ->sum('value');
                
            Redis::setex($key, 300, $total);
            
            return $total;
        } catch (\Exception $e) {
            Log::error("RedisCache::getChannalWiseGiftRecived() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
            
            return Gift::where('reciever_id', $userId)
                ->where('channelName', $channelName)
                ->sum('value');
        }
    }
    
    // ==================== CACHE CLEAR METHODS ====================
    
    /**
     * Clear user cache - STATIC METHOD
     */
    public static function clearUserCache($userId)
    {
        try {
            $patterns = [
                "user:{$userId}",
                "host_data:{$userId}",
                "hosting_type:{$userId}",
                "profile:{$userId}",
                "avatar:{$userId}",
                "sent_gift:{$userId}",
                "received_gift:{$userId}",
                "sander_total:{$userId}",
                "kicks:{$userId}",
                "kicks_count:{$userId}",
                "top_profile:{$userId}",
                "total_reward:{$userId}",
                "live:{$userId}:*",
                "live_accept:{$userId}:*"
            ];
            
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::error("RedisCache::clearUserCache() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Clear user gift cache - STATIC METHOD
     */
    public static function clearUserGiftCache($userId)
    {
        try {
            $patterns = [
                "sent_gift:{$userId}",
                "received_gift:{$userId}",
                "sander_total:{$userId}",
                "gift_range:{$userId}:*",
                "today_gift:{$userId}:*",
                "channel_gift:{$userId}:*",
                "top_profile:{$userId}",
                "total_reward:{$userId}",
                "live_stats:{$userId}:*"
            ];
            
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::error("RedisCache::clearUserGiftCache() failed for user: {$userId}", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Clear live cache - STATIC METHOD
     */
    public static function clearLiveCache($hostId, $channelName)
    {
        try {
            $patterns = [
                "live:{$hostId}:{$channelName}",
                "live_accept:{$hostId}:{$channelName}",
                "live_stats:{$hostId}:{$channelName}:*"
            ];
            
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::error("RedisCache::clearLiveCache() failed for host: {$hostId}", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Clear channel cache - STATIC METHOD
     */
    public static function clearChannelCache($channelName)
    {
        try {
            $pattern = "*:{$channelName}*";
            $keys = Redis::keys(self::$prefix . $pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } catch (\Exception $e) {
            Log::error("RedisCache::clearChannelCache() failed for channel: {$channelName}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}