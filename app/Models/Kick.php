<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Kick extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'channelName',
        'host_id',
        'kick_by',
    ];
  protected static function booted()
    {
        static::created(function ($kick) {
            CacheClearHelperFromModelAuto::clearKickCaches($kick, 'created');
        });

        static::deleted(function ($kick) {
            CacheClearHelperFromModelAuto::clearKickCaches($kick, 'deleted');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function kicker()
    {
        return $this->belongsTo(User::class, 'kick_by');
    }
}
