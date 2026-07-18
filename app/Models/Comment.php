<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Comment extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::created(function ($comment) {
            CacheClearHelperFromModelAuto::clearCommentCaches($comment, 'created');
        });
    }
    protected $fillable = [
        'user_id',
        'reciever_id',
        'channelName',
        'message',
        'type',
        'gift_name',
        'gift_value',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function receiver()
    {
        return $this->belongsTo(User::class, 'reciever_id');
    }
}
