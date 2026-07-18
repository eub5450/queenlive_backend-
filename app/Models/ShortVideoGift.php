<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortVideoGift extends Model
{
    protected $table = 'short_video_gifts';

    protected $fillable = [
        'video_id', 'sender_id', 'receiver_id', 'gift_id', 'coin', 'quantity',
    ];
}
