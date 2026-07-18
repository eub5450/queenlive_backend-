<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FortuneLock extends Model
{
    use HasFactory;
      protected $fillable = [
        'user_id',
        'imei_number',
        'auto_lock_active',
        'parcentage',  // Note: Field name appears to be misspelled (should be 'percentage')
        'type',
        // Add other fields you need to mass assign here
    ];
}
