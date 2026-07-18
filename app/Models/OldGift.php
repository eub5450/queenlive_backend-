<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class OldGift extends Model
{
    use HasFactory;
   
    protected static function booted()
    {
        static::created(function ($oldGift) {
            CacheClearHelperFromModelAuto::clearOldGiftCaches($oldGift, 'created');
        });
    }
    protected $fillable = [
        'sander_id',
        'reciever_id',
        'name',
        'value',
        'date',
        'channelName',
        'reaward_time',
        'created_at',
        'updated_at',
    ];

}
