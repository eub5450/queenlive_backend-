<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class DayTime extends Model
{
    use HasFactory;
    
     protected static function booted()
    {
        static::created(function ($dayTime) {
            CacheClearHelperFromModelAuto::clearDayTimeCaches($dayTime, 'created');
        });
        
        static::updated(function ($dayTime) {
            CacheClearHelperFromModelAuto::clearDayTimeCaches($dayTime, 'updated');
        });
    }
     protected $fillable = [
        'user_id',
        'channelName',
        'day_times',
        'live_time',
        'brd_type',
    ];
}
