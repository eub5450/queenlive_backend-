<?php

namespace App\Models\Battle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeenPattiTray extends Model
{
    use HasFactory;
    protected $fillable = [
        'tray_id', 
        'winner', 
        'status'
    ];
}
