<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamboRecord extends Model
{
    use HasFactory;

    protected $table = 'jambo_records';

    protected $fillable = [
        'record_type',
        'title',
        'content',
        'category',
        'tags',
        'amount',
        'amount_type',
        'note',
        'meta_json',
    ];

    protected $casts = [
        'amount' => 'float',
    ];
}
