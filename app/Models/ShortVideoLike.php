<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortVideoLike extends Model
{
    protected $table = 'short_video_likes';

    protected $fillable = ['video_id', 'user_id'];
}
