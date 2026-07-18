<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDefinition extends Model
{
    use HasFactory;

    public const RECURRENCE_ONCE = 'once';
    public const RECURRENCE_DAILY = 'daily';
    public const RECURRENCE_WEEKLY = 'weekly';
    public const RECURRENCE_MONTHLY = 'monthly';

    protected $table = 'task_definitions';

    protected $fillable = [
        'task_key',
        'title',
        'description',
        'reward_amount',
        'goal',
        'unit',
        'category',
        'recurrence',
        'progress_resolver',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'reward_amount' => 'integer',
        'goal' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
