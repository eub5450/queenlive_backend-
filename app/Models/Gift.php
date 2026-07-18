<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\RedisCache\CacheClearHelperFromModelAuto;
class Gift extends Model
{
    use HasFactory;
    
    protected $table = 'gifts';
    
    protected $fillable = [
        'sander_id',
        'reciever_id',
        'name',
        'value',
        'date',
        'channelName',
        'reaward_time',
        'checked',
        'agency_code',
        'reward_type',
        'imie',
    ];
  
    // Relationships
    public function sender() 
    { 
        return $this->belongsTo(User::class, 'sander_id'); 
    }
    
    public function receiver() 
    { 
        return $this->belongsTo(User::class, 'reciever_id'); 
    }
    
    public function agency() 
    { 
        return $this->belongsTo(Agency::class, 'agency_code', 'code'); 
    }

    // Scope
    public function scopeBetweenDates($query, $start, $end)
    {
        $startAt = Carbon::parse($start, config('app.timezone', 'Europe/London'))->startOfDay()->toDateTimeString();
        $endAt = Carbon::parse($end, config('app.timezone', 'Europe/London'))->endOfDay()->toDateTimeString();

        return $query->where('date', '>=', $startAt)
                     ->where('date', '<=', $endAt);
    }
/* =========================
       FAMILY / AGENCY RANKINGS
    ========================= */
    public static function familyRanking($start, $end, $skip = 0, $take = 3)
    {
        // Input validation
        if (empty($start) || empty($end)) {
            return collect([]);
        }

        try {
            $data = DB::table('gifts as g')
                ->join('agencies as a', 'g.agency_code', '=', 'a.code') // agency_code = a.code
                ->whereDate('g.date', '>=', $start)
                 ->whereDate('g.date', '<=', $end)
                ->whereNotNull('g.agency_code')
                ->where('g.agency_code', '>', 0)
                ->select(
                    DB::raw('SUM(g.value) as total_sand'),
                    'a.name',
                    'a.logo'
                )
                ->groupBy('g.agency_code', 'a.name', 'a.logo')
                ->orderByDesc('total_sand')
                ->skip($skip)
                ->take($take)
                ->get()
                ->map(function ($item) {
                    return [
                        'total_sand' => $item->total_sand,
                        'name'       => $item->name ?? null,
                        'logo'       => $item->logo ?? null,
                    ];
                });

            return $data;

        } catch (\Exception $e) {
            Log::error('Family ranking error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /* =========================
       SENDER RANKINGS
    ========================= */
    public static function sanderRanking($start, $end, $skip = 0, $take = 3)
    {
        // Input validation
        if (empty($start) || empty($end)) {
            return collect([]);
        }

        try {
            $data = DB::table('gifts as g')
                ->join('users as u', 'g.sander_id', '=', 'u.id')
                ->whereDate('g.date', '>=', $start)
                 ->whereDate('g.date', '<=', $end)
                ->where('g.sander_id', '!=', 1)
                ->whereNotNull('g.sander_id')
                ->select(
                    'g.sander_id',
                    DB::raw('SUM(g.value) as total_sand'),
                    'u.name',
                    'u.profile',
                    'u.is_vip',
                    'u.frame'
                )
                ->groupBy('g.sander_id', 'u.name', 'u.profile', 'u.is_vip', 'u.frame')
                ->orderByDesc('total_sand')
                ->skip($skip)
                ->take($take)
                ->get()
                ->map(function ($item) {
                    return [
                        'sander_id'  => $item->sander_id,
                        'total_sand' => $item->total_sand,
                        'name'       => $item->name ?? null,
                        'profile'    => $item->profile ?? null,
                        'is_vip'     => $item->is_vip ?? 0,
                        'frame'      => $item->frame ?? null,
                    ];
                });

            return $data;

        } catch (\Exception $e) {
            Log::error('Sender ranking error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /* =========================
       RECEIVER RANKINGS
    ========================= */
    public static function receiverRanking($start, $end, $skip = 0, $take = 3)
    {
        // Input validation
        if (empty($start) || empty($end)) {
            return collect([]);
        }

        try {
            $data = DB::table('gifts as g')
                ->join('users as u', 'g.reciever_id', '=', 'u.id')
               ->whereDate('g.date', '>=', $start)
                 ->whereDate('g.date', '<=', $end)
                ->whereNotNull('g.reciever_id')
                ->select(
                    'g.reciever_id',
                    DB::raw('SUM(g.value) as total_sand'),
                    'u.name',
                    'u.profile',
                    'u.is_vip',
                    'u.frame'
                )
                ->groupBy('g.reciever_id', 'u.name', 'u.profile', 'u.is_vip', 'u.frame')
                ->orderByDesc('total_sand')
                ->skip($skip)
                ->take($take)
                ->get()
                ->map(function ($item) {
                    return [
                        'reciever_id'=> $item->reciever_id,
                        'total_sand' => $item->total_sand,
                        'name'       => $item->name ?? null,
                        'profile'    => $item->profile ?? null,
                        'is_vip'     => $item->is_vip ?? 0,
                        'frame'      => $item->frame ?? null,
                    ];
                });

            return $data;

        } catch (\Exception $e) {
            Log::error('Receiver ranking error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /* =========================
       BONUS: GET MULTIPLE RANKINGS AT ONCE
    ========================= */
    public static function getAllRankings($start, $end, $skip = 0, $take = 3)
    {
        return [
            'family'    => self::familyRanking($start, $end, $skip, $take),
            'senders'   => self::sanderRanking($start, $end, $skip, $take),
            'receivers' => self::receiverRanking($start, $end, $skip, $take),
        ];
    }
    
    
   
    
    /**
     * গিফট রিলেটেড সব ক্যাশ ক্লিয়ার করুন
     */
     protected static function booted()
    {
        static::created(function ($gift) {
            CacheClearHelperFromModelAuto::clearGiftCaches($gift, 'created');
        });

        static::updated(function ($gift) {
            CacheClearHelperFromModelAuto::clearGiftCaches($gift, 'updated');
        });

        static::deleted(function ($gift) {
            CacheClearHelperFromModelAuto::clearGiftCaches($gift, 'deleted');
        });
    }
    // private static function clearGiftCaches($gift)
    // {
    //     $sander_id = $gift->sander_id;
    //     $reciever_id = $gift->reciever_id;
    //     $channelName = $gift->channelName;
    //     $today = now()->format('Y-m-d');
        
    //     // মাসের প্রথম ও শেষ তারিখ
    //     $startDate = now()->startOfMonth()->toDateString();
    //     $endDate = now()->endOfMonth()->toDateString();
        
    //     // যে ক্যাশ গুলো ডিলিট করবেন
    //     $cacheKeys = [
    //         // সেন্ডার রিলেটেড
    //         "auth_user_{$sander_id}",
    //         "user_sent_gift_total_{$sander_id}",
    //         "user_profile_complete_{$sander_id}",
    //         "sander_total_gift_{$sander_id}",
            
    //         // রিসিভার রিলেটেড
    //         "auth_user_{$reciever_id}",
    //         "user_received_gift_total_{$reciever_id}",
    //         "user_profile_complete_{$reciever_id}",
    //         "user_today_gift_{$reciever_id}_{$today}",
    //         "top_profile_brd_{$reciever_id}",
    //         "total_reward_{$reciever_id}",
    //         "user_gift_{$reciever_id}_{$startDate}_{$endDate}",
            
    //         // হোস্ট ক্যাশ (ভিডিও/অডিও কল)
    //         "host_data_{$reciever_id}",
    //     ];
        
    //     // চ্যানেল নাম থাকলে
    //     if ($channelName) {
    //         $cacheKeys[] = "user_gift_recived_throw_channal_{$reciever_id}_{$channelName}";
    //         $cacheKeys[] = "user_live_{$reciever_id}_{$channelName}";
    //         $cacheKeys[] = "Video_Brd_Call_Details_{$reciever_id}_{$channelName}";
    //         $cacheKeys[] = "Audio_Brd_Call_Details_{$reciever_id}_{$channelName}";
    //     }
        
    //     // ডুপ্লিকেট রিমুভ
    //     $cacheKeys = array_unique($cacheKeys);
        
    //     // সব ক্যাশ ডিলিট
    //     foreach ($cacheKeys as $key) {
    //         if ($key) {
    //             Cache::forget($key);
    //         }
    //     }
    // }
}