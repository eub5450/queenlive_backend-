<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledFrameRuleWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_frame_rule_id',
        'user_id',
        'my_beg_id',
        'previous_my_beg_id',
        'previous_frame_effect',
        'frame_effect',
        'frame_name',
        'frame_image',
        'metric_value',
        'agency_code',
        'period_key',
        'window_starts_at',
        'window_ends_at',
        'applied_at',
        'removed_at',
        'status',
    ];

    protected $casts = [
        'metric_value' => 'float',
        'window_starts_at' => 'datetime',
        'window_ends_at' => 'datetime',
        'applied_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function rule()
    {
        return $this->belongsTo(ScheduledFrameRule::class, 'scheduled_frame_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
