<?php
namespace App\Models\jambo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class JamboSetting extends Model {
    use HasFactory;
    protected $fillable = ['setting_key','setting_value'];
}
