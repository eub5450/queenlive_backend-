<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortVideo extends Model
{
    protected $table = 'short_videos';

    protected $fillable = [
        'user_id', 'video_url', 'thumb_url', 'caption', 'duration',
        'width', 'height', 'views_count', 'likes_count', 'comments_count',
        'gifts_count', 'gift_value', 'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'gifts_count' => 'integer',
        'gift_value' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
