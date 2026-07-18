<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\RedisCache\CacheClearHelperFromModelAuto;
class LiveCall extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::created(function ($liveCall) {
            CacheClearHelperFromModelAuto::clearLiveCallCaches($liveCall, 'created');
        });

        static::updated(function ($liveCall) {
            CacheClearHelperFromModelAuto::clearLiveCallCaches($liveCall, 'updated');
        });

        static::deleted(function ($liveCall) {
            CacheClearHelperFromModelAuto::clearLiveCallCaches($liveCall, 'deleted');
        });
    }
    // In LiveCall model
    // protected static function booted()
    // {
    //     // When a LiveCall is created
    //     static::created(function ($liveCall) {
    //         self::clearLiveCallCaches($liveCall);
    //     });

    //     // When a LiveCall is updated
    //     static::updated(function ($liveCall) {
    //         self::clearLiveCallCaches($liveCall);
    //     });

    //     // When a LiveCall is deleted
    //     static::deleted(function ($liveCall) {
    //         self::clearLiveCallCaches($liveCall);
    //     });
    // }

    // /**
    //  * Clear all LiveCall-related caches
    //  */
    // protected static function clearLiveCallCaches($liveCall)
    // {
    //     $hostId = $liveCall->host_id;
    //     $channelName = $liveCall->channelName;
    //     $coHostId = $liveCall->co_host_id;
        
    //     // Clear specific cache keys (wildcards don't work, so we need specific keys)
    //     $cacheKeys = [
    //         // Accept count cache
    //         "live_call_accept_count_{$hostId}_{$channelName}",
            
    //         // Pending count cache
    //         "pending_call_count_{$channelName}",
            
    //         // Video call details cache (if exists)
    //         "Video_Brd_Call_Details_{$hostId}_{$channelName}",
            
    //         // Individual call status caches
    //         "live_call_status_{$hostId}_{$channelName}_{$coHostId}",
    //     ];
        
    //     foreach ($cacheKeys as $cacheKey) {
    //         if (Cache::has($cacheKey)) {
    //             Cache::forget($cacheKey);
    //         }
    //     }
        
    //     // Optional: Log cache clearing for debugging
    //     // Log::info("LiveCall caches cleared for host: {$hostId}, channel: {$channelName}");
    // }

    protected $fillable = [
        'host_id',
        'co_host_id',
        'channelName',
        'status',
        'is_co_host_active',
        'mute',
        'set_no',
        'type',
        'super_mute',
        'mute_time',
    ];
   
    protected $casts = [
        'is_co_host_active' => 'integer',
        'mute' => 'integer',
        'super_mute' => 'integer',
        'set_no' => 'integer',
        'mute_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function userLive()
    {
        return $this->belongsTo(UserLive::class, 'host_id', 'user_id');
    }
    public function coHost()
    {
        return $this->belongsTo(User::class, 'co_host_id');
    }
     public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
