<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtalToPTransfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'portal_user_id',
        'amount',
        'date',
        'trxid',
    ];
}
