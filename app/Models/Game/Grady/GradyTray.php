<?php

namespace App\Models\Game\Grady;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradyTray extends Model
{
    use HasFactory;
     protected $fillable = [
        'tray_id',
        // Add other fillable fields here as needed
    ];
}
