<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskClaim extends Model
{
    use HasFactory;

    protected $table = 'task_claims';

    protected $fillable = [
        'user_id',
        'task_definition_id',
        'task_key',
        'cycle_key',
        'reward_amount',
        'balance_before',
        'balance_after',
        'claim_ip',
        'claim_user_agent',
        'claimed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'task_definition_id' => 'integer',
        'reward_amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'claimed_at' => 'datetime',
    ];
}
