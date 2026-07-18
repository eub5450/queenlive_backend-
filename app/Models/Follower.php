<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Follower extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::created(function ($follower) {
            CacheClearHelperFromModelAuto::clearFollowerCaches($follower, 'created');
        });
        
        static::deleted(function ($follower) {
            CacheClearHelperFromModelAuto::clearFollowerCaches($follower, 'deleted');
        });
    }
   public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who is following.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
}
