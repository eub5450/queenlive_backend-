<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\BanDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\RedisCache\CacheClearHelperFromModelAuto;
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    // app/Models/User.php


   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = [ 'name', 'email', 'password', 'balance', 'level', 'frame', 'is_vip', 'entry', 'imei_number', 'hold_balance', 'vip_timeline', 'is_admin', 'status', 'entry_level', 'role', 'phone', 'device_id', 'day_count', 'hours_count', 'is_host_id', 'date_of_birth', 'gender', 'api_token', 'brd_off_power', 'comment_mute_power', 'kick_power', 'is_coin_protal_active', 'is_invisible', 'is_official_id', 'ban_type', 'open_time', 'is_device_ban', 'ban_proved', 'ban_by', 'is_invisible_active', 'prosss_top', 'country_id', 'master_protal_id', 'main_id_number', 'invited_by', 'host_badge', 'profile', 'top_value', 'bio', 'previous_coin', 'recharge_reward_provide', 'top_gamer', 'comment_badge', 'game_balance_date', 'date_wise_balance', 'total_receiving', 'total_sanding', 'lock_brd_entry', 'ip_address', 'withdraw_active', 'is_agency', 'is_official_frame', 'is_admin_frame', 'is_bd_admin', 'game_priority', 'sceen_short_power', 'invite_done', 'invite_recharge_reward_provide', 'transfer_profile', 'new_profile', 'auto_lock_status', 'agora_access', 'can_banned', 'can_call_cut', 'is_app_admin', ];

    /**
     * Route Cloudflare profile images through the apex /cdn-img/ path so BD users
     * get them cached at the Dhaka (BDIX) edge. Applies to every Eloquent write
     * (registration default, profile upload, admin set), keeping new rows
     * consistent with the one-time DB migration of existing profiles.
     */
    public function setProfileAttribute($value)
    {
        $this->attributes['profile'] = is_string($value)
            ? str_replace('https://imagedelivery.net/', 'https://queenlive.site/cdn-img/', $value)
            : $value;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public function followers()
    {
        return $this->hasMany(Follower::class, 'user_id');
    }

    /**
     * Get the users who are following the user.
     */
    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }
    
    public function audienceJoins()
    {
        return $this->hasMany(AudienceJoin::class, 'user_id');
    }
    // app/Models/User.php
    public function portalTransfers()
    {
        return $this->hasMany(PortalTransfer::class);
    }

  /* =========================================
        RELATIONSHIPS
    ========================================= */

    // Gifts Sent by User
    public function sentGifts()
    {
        return $this->hasMany(Gift::class, 'sander_id');
    }

    // Gifts Received by User
    public function receivedGifts()
    {
        return $this->hasMany(Gift::class, 'reciever_id');
    }
    public function hostData()
    {
        return $this->hasOne(HostData::class, 'user_id');
    }
    
    public function getHostingTypeAttribute()
    {
        if ($this->is_host_id != 1) {
            return '0';
        }
    
        return $this->hostData->hosting_type ?? '0';
    }
    
    
    public function live()
    {
        return $this->hasOne(UserLive::class, 'user_id');
    }
   
    /**
     * Check if user is banned
     */
    public function isBanned()
    {
        return $this->ban_type !== null;
    }

    /**
     * Get ban message based on ban type
     */
    public function getBanMessage()
    {
        $banMessages = [
            "B" => "Your ID Banned For One Month. Violation Rules B",
            "C" => "Your ID Banned For 24 Hours. Violation Rules C",
            "D" => "Your ID Banned For 1 Hour. Violation Rules D",
            "A" => "You Are Permanently Banned. Violation Rules A",
        ];
        
        return $banMessages[$this->ban_type] ?? "User is banned.";
    }

    /**
     * Check if device is banned
     */
    public function isDeviceBanned()
    {
        return BanDevice::where('device_id', $this->imei_number)->exists();
    }

    /**
     * Relationship with BanDevice (if you want to use Eloquent relationship)
     */
    public function bannedDevices()
    {
        return $this->hasMany(BanDevice::class, 'device_id', 'imei_number');
    }

    /**
     * Check if device is banned using relationship (alternative method)
     */
    public function isDeviceBannedViaRelation()
    {
        return $this->bannedDevices()->exists();
    }
    
     /**
     * 🛡️ Model Events - Automatic Cache Clear
     */
     protected static function booted()
    {
        static::updated(function ($user) {
             if (request()->is('api/*')) {
            // Came from API route
             CacheClearHelperFromModelAuto::clearUserCaches($user, 'updated');
            
            } else {
                 CacheClearHelperFromModelAuto::clearUserBalance($user, 'updated');
                
            }
           
        });

        static::deleted(function ($user) {
            CacheClearHelperFromModelAuto::clearUserCaches($user, 'deleted');
        });
    }
    // protected static function booted()
    // {
    //     // Updated event
    //     static::updated(function ($user) {
    //         $cacheKeys = [
    //             "auth_user_{$user->id}",
    //             "user_profile_complete_{$user->id}",
    //             "user_hosting_type_{$user->id}",
    //             "host_data_{$user->id}",
    //             "top_profile_brd_{$user->id}",
    //             "total_reward_{$user->id}",
    //             "user_kicks_{$user->id}",
    //             "user_kicks_count_{$user->id}",
    //             "user_avatar_{$user->id}",
    //             "user_sent_gift_total_{$user->id}",
    //             "user_received_gift_total_{$user->id}",
    //             "sander_total_gift_{$user->id}",
    //         ];
            
    //         $cleared = 0;
    //         foreach ($cacheKeys as $key) {
    //             if (Cache::has($key)) {
    //                 Cache::forget($key);
    //                 $cleared++;
    //             }
    //         }
            
    //         Log::channel('cache_clear')->info('User cache cleared', [
    //             'user_id' => $user->id,
    //     'event' => 'updated',
    //             'keys_cleared' => $cleared,
    //             'changed_fields' => $user->getChanges()
    //         ]);
    //     });

    //     // Deleted event
    //     static::deleted(function ($user) {
    //         $cacheKeys = [
    //             "auth_user_{$user->id}",
    //             "user_profile_complete_{$user->id}",
    //             "user_hosting_type_{$user->id}",
    //             "host_data_{$user->id}",
    //             "top_profile_brd_{$user->id}",
    //             "total_reward_{$user->id}",
    //             "user_kicks_{$user->id}",
    //             "user_kicks_count_{$user->id}",
    //             "user_avatar_{$user->id}",
    //             "user_sent_gift_total_{$user->id}",
    //             "user_received_gift_total_{$user->id}",
    //             "sander_total_gift_{$user->id}",
    //         ];
            
    //         $cleared = 0;
    //         foreach ($cacheKeys as $key) {
    //             if (Cache::has($key)) {
    //                 Cache::forget($key);
    //                 $cleared++;
    //             }
    //         }
            
    //         Log::channel('cache_clear')->info('User cache cleared', [
    //             'user_id' => $user->id,
    //             'event' => 'deleted',
    //             'keys_cleared' => $cleared
    //         ]);
    //     });
    // }
    
}
