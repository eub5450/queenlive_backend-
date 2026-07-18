<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortVideoComment extends Model
{
    protected $table = 'short_video_comments';

    protected $fillable = ['video_id', 'user_id', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
