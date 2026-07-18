<?php
namespace App\Models\jambo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class JamboSyncLog extends Model {
    use HasFactory;
    protected $fillable = ['module','payload_count','saved_count','duplicate_count','request_ip','meta_json'];
}
