<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\RedisCache\CacheClearHelperFromModelAuto;
class UserLive extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::created(function ($userLive) {
            CacheClearHelperFromModelAuto::clearUserLiveCaches($userLive, 'created');
        });

        static::updated(function ($userLive) {
            CacheClearHelperFromModelAuto::clearUserLiveCaches($userLive, 'updated');
        });

        static::deleted(function ($userLive) {
            CacheClearHelperFromModelAuto::clearUserLiveCaches($userLive, 'deleted');
        });
    }
    
    
    protected $fillable = [
        'user_id',
        'channelName',
        'name',
        'date',
        'token',
        'type',
        'mute',
        'locked',
        'top_value',
        'audio_brd_design',
        'notice',
        'bullet_notice',
        'pin',
        'avatar',
        'sdk',
        'backgorund',
	        'appId',
	        'appCertificate',
	        'host_camera_status',
	        'siteNumber',
	    ];

    protected $casts = [
        'mute' => 'integer',
        'locked' => 'integer',
        'siteNumber' => 'integer',
        'host_camera_status' => 'integer',
        'top_value' => 'integer',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function storeOneActiveForUser(array $attributes)
    {
        $userId = $attributes['user_id'] ?? null;

        if ($userId === null || $userId === '') {
            throw new \InvalidArgumentException('user_id is required to store user live data');
        }

        $write = function () use ($attributes, $userId) {
            return DB::transaction(function () use ($attributes, $userId) {
                $live = static::where('user_id', $userId)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($live) {
                    static::where('user_id', $userId)
                        ->where('id', '<>', $live->id)
                        ->delete();

                    $live->fill($attributes);
                    $live->save();

                    return $live;
                }

                return static::create($attributes);
            });
        };

        return Cache::lock('user_live_store:' . sha1((string) $userId), 10)->block(5, $write);
    }
    
//   protected static function booted()
//     {
//         // When a UserLive is created
//         static::created(function ($userLive) {
//             self::clearUserLiveCaches($userLive);
//         });

//         // When a UserLive is updated
//         static::updated(function ($userLive) {
//             self::clearUserLiveCaches($userLive);
//         });

//         // When a UserLive is deleted
//         static::deleted(function ($userLive) {
//             self::clearUserLiveCaches($userLive);
//         });
//     }

//     /**
//      * Clear all UserLive-related caches
//      */
//     protected static function clearUserLiveCaches($userLive)
//     {
//         $userId = $userLive->user_id;
//         $channelName = $userLive->channelName;
        
//         // Clear specific cache keys (wildcards don't work, so we need specific keys)
//         $cacheKeys = [
//             // Home page lists
//             "live_top_list",
//             "live_frined_home",
//             "live_users_type_1",
            
//             // Specific user live cache
//             "user_live_{$userId}_{$channelName}",
            
//             // Video call details cache
//             "Video_Brd_Call_Details_{$userId}_{$channelName}",
            
//             // User profile related caches
//             "top_profile_brd_{$userId}",
//             "total_reward_{$userId}",
//             "user_profile_complete_{$userId}",
//         ];
        
//         foreach ($cacheKeys as $cacheKey) {
//             Cache::forget($cacheKey);
//         }
        
//         // Clear paginated live list caches
//         for ($i = 1; $i <= 5; $i++) {
//             Cache::forget("live_list_page_{$i}");
//         }
        
       
        
//         // Optional: Log cache clearing for debugging
//         // Log::info("UserLive caches cleared for user: {$userId}, channel: {$channelName}");
//     }
    public function liveCalls()
    {
        return $this->hasMany(LiveCall::class, 'host_id', 'user_id');
    }
    
    
    public function scopeSetTopUsers($query, $limit = 2)
    {
        $query->update(['is_top' => 0]);
    
        return $query->orderByDesc('top_value')
            ->limit($limit)
            ->update(['is_top' => 1]);
    }
    

}
