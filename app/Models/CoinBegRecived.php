<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinBegRecived extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'channelName',
        'user_name',
        'beg_id',
    ];
}
