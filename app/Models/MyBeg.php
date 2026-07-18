<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyBeg extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'store_id',
        'active_time',
        'name',
        'image',
        'expaire_time',
        'effect',
        'status',
        'type',
    ];
}
