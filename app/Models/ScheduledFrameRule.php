<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledFrameRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'entry_frame_id',
        'frame_name',
        'frame_image',
        'frame_effect',
        'top_type',
        'metric_type',
        'condition_type',
        'target_value',
        'top_limit',
        'schedule_type',
        'campaign_starts_at',
        'campaign_ends_at',
        'notes',
        'status',
        'created_by',
        'last_synced_at',
        'last_window_key',
    ];

    protected $casts = [
        'campaign_starts_at' => 'datetime',
        'campaign_ends_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'status' => 'integer',
        'top_limit' => 'integer',
        'target_value' => 'float',
    ];

    public function frame()
    {
        return $this->belongsTo(EntryFrame::class, 'entry_frame_id');
    }

    public function winners()
    {
        return $this->hasMany(ScheduledFrameRuleWinner::class, 'scheduled_frame_rule_id');
    }
}
