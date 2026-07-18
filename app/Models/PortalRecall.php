<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalRecall extends Model
{
    use HasFactory;
    protected $fillable = [
        'protal_id',
        'amount',
        'date',
        'user_id',
    ];
}
