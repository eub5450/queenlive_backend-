<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class BedWord extends Model
{
    use HasFactory;
    protected static function booted()
    {
        static::created(function () {
            CacheClearHelperFromModelAuto::clearBedWordCaches('created');
        });
        
        static::updated(function () {
            CacheClearHelperFromModelAuto::clearBedWordCaches('updated');
        });
        
        static::deleted(function () {
            CacheClearHelperFromModelAuto::clearBedWordCaches('deleted');
        });
    }
}
