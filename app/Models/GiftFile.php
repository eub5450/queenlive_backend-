<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class GiftFile extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::saved(function () {
            self::clearGiftDataCache();
        });

        static::deleted(function () {
            self::clearGiftDataCache();
        });
    }

    public static function clearGiftDataCache()
    {
        Cache::forget('gift_data_propulars');
        Cache::forget('gift_data_luxerys');
        Cache::forget('gift_data_fastival');
    }
}
