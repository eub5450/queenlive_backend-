<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Setting extends Model
{
    use HasFactory;
    protected static function booted()
    {
        static::updated(function ($setting) {
            CacheClearHelperFromModelAuto::clearSettingCaches($setting, 'updated');
        });
    }
  
}
