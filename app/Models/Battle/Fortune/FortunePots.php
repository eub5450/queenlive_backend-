<?php

namespace App\Models\Battle\Fortune;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FortunePots extends Model
{
    use HasFactory;
     protected $fillable = [
        'tray_id',
        'user_id',
        'amount',
        'win_balance',
        'pot_no',
        'now_user_balance',
        'status',
        // Add any other fields you need to mass assign
    ];
}
