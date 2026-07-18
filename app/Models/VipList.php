<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipList extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id', 'vip_no', 'image', 'active_date', 'end_date', 'is_active'
];

}
