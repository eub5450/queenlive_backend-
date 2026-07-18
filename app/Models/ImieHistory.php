<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImieHistory extends Model
{
    use HasFactory;
     protected $fillable = [
        'imie',
        'user_id',
    ];
}
