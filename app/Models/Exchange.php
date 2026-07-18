<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'convert_amount',
        'cut_percent',
        'receive_amount',
        'month_key',
        'status',
        'message',
    ];
}
