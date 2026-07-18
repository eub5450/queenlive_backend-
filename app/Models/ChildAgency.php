<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildAgency extends Model
{
    use HasFactory;
       protected $fillable = [
        'master_agency_id',
        'child_agency_id'
    ];
}
