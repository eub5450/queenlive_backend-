<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgoraKeys extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'appId',
        'appCertificate',
        'AgoraEmail',
        'AgoraEmailPassword',
        'Status',
        'type',
        'main_email',
    ];
}
