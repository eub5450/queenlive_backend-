<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Withdraw extends Model
{
    protected $fillable = [
        'txid',
        'host_id',
        'host_name',
        'super_agency_id',
        'agency_id',
        'withdraw_agency_id',
        'is_super_agency_withdraw',
        'basic_coin',
        'agency_profit',
        'apps_profit',
        'status',
        'date',
        'total',
    ];
    use HasFactory;
    
    // protected static function booted()
    // {
    //     static::created(function ($withdraw) {
    //         Cache::forget("user_today_withdraw_{$withdraw->host_id}_" . now()->format('Y-m-d'));
    //         Cache::forget("user_profile_complete_{$withdraw->host_id}");
    //     });
        
    //     static::updated(function ($withdraw) {
    //         Cache::forget("user_today_withdraw_{$withdraw->host_id}_" . now()->format('Y-m-d'));
    //         Cache::forget("user_profile_complete_{$withdraw->host_id}");
    //     });
        
    //     static::deleted(function ($withdraw) {
    //         Cache::forget("user_today_withdraw_{$withdraw->host_id}_" . now()->format('Y-m-d'));
    //         Cache::forget("user_profile_complete_{$withdraw->host_id}");
    //     });
    // }
    protected static function booted()
    {
        static::created(function ($withdraw) {
            CacheClearHelperFromModelAuto::clearWithdrawCaches($withdraw, 'created');
        });
        
        static::updated(function ($withdraw) {
            CacheClearHelperFromModelAuto::clearWithdrawCaches($withdraw, 'updated');
        });
    }
    
    
}
