<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Slider extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'url',
    ];
    
   protected static function booted()
    {
        static::created(function ($slider) {
            CacheClearHelperFromModelAuto::clearSliderCaches($slider, 'created');
        });
        
        static::updated(function ($slider) {
            CacheClearHelperFromModelAuto::clearSliderCaches($slider, 'updated');
        });
        
        static::deleted(function ($slider) {
            CacheClearHelperFromModelAuto::clearSliderCaches($slider, 'deleted');
        });
    }
}
