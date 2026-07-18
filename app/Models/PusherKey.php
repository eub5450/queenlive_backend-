<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PusherKey extends Model
{
    use HasFactory;
     protected $fillable = [
        'puser_app_id',
        'pusher_key',
        'pusher_secret',
        'pusher_cluster',
        'pusher_email',
        'pusher_status',        // 1 = active, 2 = used
        'pusher_active_time',
        'pusher_deactive_time',
    ];

    protected $dates = [
        'pusher_active_time',
        'pusher_deactive_time',
    ];
}
