<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedEmail extends Model
{
    public $timestamps = false; // Add this line
    use HasFactory;
     protected $fillable = [
        'original_email',
        'generated_email',
        'is_used',
        'login_password'
    ];
}
