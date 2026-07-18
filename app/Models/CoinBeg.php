<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinBeg extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'count',
        'channelName',
        'given_count',
        'amount_clime',
        'type',
        'host_id',
        'result',
        'click',
    ];
}
