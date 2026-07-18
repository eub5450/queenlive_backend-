<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawConvartAgency extends Model
{
    protected $fillable = [
        'trxid',
        'agency_id',
        'date',
        'amount',
    ];
    use HasFactory;
}
