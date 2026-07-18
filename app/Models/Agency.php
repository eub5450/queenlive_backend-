<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Agency extends Model
{
    protected static function booted()
    {
        static::updated(function ($agency) {
            CacheClearHelperFromModelAuto::clearAgencyCaches($agency, 'updated');
        });
    }
    use HasFactory;
    public function gifts()
    {
        return $this->hasMany(Gift::class, 'agency_code', 'code');
    }
}
