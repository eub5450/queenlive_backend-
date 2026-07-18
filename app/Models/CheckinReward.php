<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinReward extends Model
{
    protected $table = 'checkin_rewards';

    protected $fillable = ['day', 'reward_amount', 'is_active'];

    protected $casts = [
        'day' => 'integer',
        'reward_amount' => 'integer',
        'is_active' => 'boolean',
    ];
}
