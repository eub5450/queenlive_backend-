<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DB;
use Illuminate\Support\Facades\Log;
class FuritsPotsBackup extends Model
{
      protected $fillable = [
        'tray_id',
        'user_id',
        'amount',
        'pot_no',
        'status',
        'date',
        'serve_balance',
        'game_name',
        'created_at',
        'updated_at',
    ];
    use HasFactory;
    
   public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    public static function gameRanking($start, $end, $skip = 0, $take = 3)
    {
        

        // Fetch from DB
        $data = self::with('user')
            ->whereNotIn('user_id', [23825, 23826, 23827])
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->selectRaw('user_id, SUM(amount) as total_amount')
            ->groupBy('user_id')
            ->orderByDesc('total_amount')
            ->skip($skip)
            ->take($take)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id'      => $item->user_id,
                    'total_amount' => $item->total_amount,
                    'name'         => $item->user->name ?? null,
                    'profile'      => $item->user->profile ?? null,
                ];
            });

        
       
        return $data;
    }
}
