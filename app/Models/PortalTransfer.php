<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalTransfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'portal_user_id',
        'master_protal_id',
        'user_id',
        'trxid',
        'amount',
        'date',
    ];
}
