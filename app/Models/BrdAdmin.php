<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class BrdAdmin extends Model
{
    use HasFactory;
    protected static function booted()
    {
        static::created(function ($brdAdmin) {
            CacheClearHelperFromModelAuto::clearBrdAdminCaches($brdAdmin, 'created');
        });
        
        static::updated(function ($brdAdmin) {
            CacheClearHelperFromModelAuto::clearBrdAdminCaches($brdAdmin, 'updated');
        });
        
        static::deleted(function ($brdAdmin) {
            CacheClearHelperFromModelAuto::clearBrdAdminCaches($brdAdmin, 'deleted');
        });
    }
    protected $fillable = [
        'user_id',
        'admin_id',
        'type',
        // other fillable fields
    ];

   

    // Your existing relationships...
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
