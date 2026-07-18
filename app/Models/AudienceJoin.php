<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class AudienceJoin extends Model
{
    protected static function booted()
    {
        static::created(function ($audienceJoin) {
            CacheClearHelperFromModelAuto::clearAudienceJoinCaches($audienceJoin, 'created');
        });
        
        static::deleted(function ($audienceJoin) {
            CacheClearHelperFromModelAuto::clearAudienceJoinCaches($audienceJoin, 'deleted');
        });
    }
    use HasFactory;
    protected $fillable = [
        'user_id',
        'host_id',
        'channelName',
        'profile',
        'admin_power',
        'entry_show',
    ];

    protected $casts = [
        'admin_power' => 'integer',
        'entry_show' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
