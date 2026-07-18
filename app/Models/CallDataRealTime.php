<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallDataRealTime extends Model
{
    use HasFactory;
     protected $fillable = [
        'host_id',
        'channelName',
        'data'
    ];
    protected $casts = [
    'data' => 'array' // Auto-converts between array/JSON
];
}
