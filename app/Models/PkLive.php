<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkLive extends Model
{
    use HasFactory;
     protected $fillable = ['host_id', 'opponent_id', 'host_channelName', 'opponent_channelName', 'host_coin', 'opponent_coin', 'pk_start_time', 'pk_end_time', 'winner', 'status'];

    protected $casts = [
        'pk_start_time' => 'datetime',
        'pk_end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }
}
