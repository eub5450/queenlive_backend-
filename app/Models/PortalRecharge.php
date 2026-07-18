<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalRecharge extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'master_protal_id',
        'trxid',
        'amount',
        'date',
        'recharge_by',
        'status',
        'is_recall',
        'is_withdraw',
    ];
}
