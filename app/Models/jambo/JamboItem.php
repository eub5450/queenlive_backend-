<?php
namespace App\Models\jambo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class JamboItem extends Model {
    use HasFactory;
    protected $fillable = ['module','external_id','title','content','category','amount','meta_kind','meta_json','fingerprint','source'];
}
