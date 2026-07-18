<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    use HasFactory;

    protected $table = 'task_progress';

    protected $fillable = [
        'user_id',
        'task_key',
        'cycle_key',
        'progress',
        'meta',
        'last_tracked_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'progress' => 'integer',
        'meta' => 'array',
        'last_tracked_at' => 'datetime',
    ];
}
