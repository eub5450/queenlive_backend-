<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCheckin extends Model
{
    protected $table = 'user_checkins';

    protected $fillable = ['user_id', 'streak', 'last_checkin_date', 'total_claimed'];

    protected $casts = [
        'user_id' => 'integer',
        'streak' => 'integer',
        'total_claimed' => 'integer',
        'last_checkin_date' => 'date',
    ];
}
