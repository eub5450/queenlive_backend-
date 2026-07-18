<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Avater extends Model
{
    use HasFactory;
    protected static function booted()
    {
        static::created(function ($avatar) {
            CacheClearHelperFromModelAuto::clearAvatarCaches($avatar, 'created');
        });
        
        static::updated(function ($avatar) {
            CacheClearHelperFromModelAuto::clearAvatarCaches($avatar, 'updated');
        });
        
        static::deleted(function ($avatar) {
            CacheClearHelperFromModelAuto::clearAvatarCaches($avatar, 'deleted');
        });
    }
    protected $fillable = [
        'user_id',
        'image'
        // other fillable fields
    ];

   

    // Your existing relationships...
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
