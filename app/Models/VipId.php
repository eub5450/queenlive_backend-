<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipId extends Model
{
    protected $fillable = [
        'id_number',
        'price',
        'is_purchase',
        'email',
    ];
    use HasFactory;
}
