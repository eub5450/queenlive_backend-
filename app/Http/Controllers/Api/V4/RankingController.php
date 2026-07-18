<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\FuritsPotsBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    private $prefix = 'queenlive:';
    
    /**
     * RankList API - Monthly Rankings
     */
    public function RankList(Request $request)
    {
        // Auth check
        if ($request->access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $today = Carbon::now();
        
        // Date ranges
        $currentMonth = [
            'start' => $today->copy()->startOfMonth()->toDateString(),
            'end' => $today->copy()->endOfMonth()->toDateString()
        ];
        
        $previousMonth = [
            'start' => $today->copy()->subMonth()->startOfMonth()->toDateString(),
            'end' => $today->copy()->subMonth()->endOfMonth()->toDateString()
        ];

        // Cache TTLs (seconds)
        $shortTTL = 900; // 15 minutes
        $longTTL = 1296000; // 15 days

        // Fetch all 5 ranking blocks in ONE Redis round-trip (was 5 sequential
        // GETs). Each block falls back to its DB query + setex only on a miss.
        // Cache keys + DB queries are identical to the previous per-call helpers.
        $blocks = [
            'sander' => [
                'key' => $this->prefix . "ranking_sander_{$currentMonth['start']}_{$currentMonth['end']}_27",
                'ttl' => $shortTTL,
                'cb'  => function () use ($currentMonth) {
                    return Gift::sanderRanking($currentMonth['start'], $currentMonth['end'], 0, 27);
                },
            ],
            'receiver' => [
                'key' => $this->prefix . "ranking_receiver_{$currentMonth['start']}_{$currentMonth['end']}_27",
                'ttl' => $shortTTL,
                'cb'  => function () use ($currentMonth) {
                    return Gift::receiverRanking($currentMonth['start'], $currentMonth['end'], 0, 27);
                },
            ],
            'family' => [
                'key' => $this->prefix . "ranking_family_{$currentMonth['start']}_{$currentMonth['end']}_27",
                'ttl' => $shortTTL,
                'cb'  => function () use ($currentMonth) {
                    return Gift::familyRanking($currentMonth['start'], $currentMonth['end'], 0, 27);
                },
            ],
            'prevFamily' => [
                'key' => $this->prefix . "ranking_family_{$previousMonth['start']}_{$previousMonth['end']}_27",
                'ttl' => $longTTL,
                'cb'  => function () use ($previousMonth) {
                    return Gift::familyRanking($previousMonth['start'], $previousMonth['end'], 0, 27);
                },
            ],
            'game' => [
                'key' => $this->prefix . "game_ranking_{$currentMonth['start']}_{$currentMonth['end']}_27",
                'ttl' => $longTTL,
                'cb'  => function () use ($currentMonth) {
                    return FuritsPotsBackup::gameRanking($currentMonth['start'], $currentMonth['end'], 0, 27);
                },
            ],
        ];
        $resolved   = $this->resolveRankingBlocks($blocks);
        $sander     = $resolved['sander'];
        $receiver   = $resolved['receiver'];
        $family     = $resolved['family'];
        $prevFamily = $resolved['prevFamily'];
        $game       = $resolved['game'];

        // Prepare response - EXACT same structure as before
        return response()->json([[
            'message' => 'Host Data Show',
            'code' => '200',
            
            // Sender rankings
            'top_three_sander' => array_slice($sander, 0, 3),
            'sander_list' => array_slice($sander, 3),
            'sander_source' => 'cache',
            
            // Receiver rankings
            'top_three_reciver' => array_slice($receiver, 0, 3),
            'reciver_list' => array_slice($receiver, 3),
            'receiver_source' => 'cache',
            
            // Family rankings
            'top_three_family' => array_slice($family, 0, 3),
            'family_list' => array_slice($family, 3),
            'family_source' => 'cache',
            
            // Previous month family
            'topThreefamillyRecived_prvious' => array_slice($prevFamily, 0, 3),
            'totalfamillyRecived_prvious' => array_slice($prevFamily, 3),
            'prev_family_source' => 'cache',
            
            // Game rankings
            'top_three_gamer' => array_slice($game, 0, 3),
            'gamer_top' => array_slice($game, 3),
            'game_source' => 'cache',
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * TopList API - Daily & Weekly Rankings
     */
    public function TopList(Request $request)
    {
        // Auth check
        if ($request->access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $today = Carbon::now()->toDateString();
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        // Date ranges
        $dailyRange = ['start' => $today, 'end' => $today];
        $weeklyRange = ['start' => $sevenDaysAgo, 'end' => $today];

        // Cache TTLs (seconds)
        $dailyTTL = 120; // 2 minutes
        $weeklyTTL = 900; // 15 minutes

        // Fetch rankings using Redis direct
        $sanderDaily = $this->getCachedRankingRedis('sander', $dailyRange, 27, $dailyTTL);
        $receiverDaily = $this->getCachedRankingRedis('receiver', $dailyRange, 27, $dailyTTL);
        $familyDaily = $this->getCachedRankingRedis('family', $dailyRange, 27, $dailyTTL);
        
        $sanderWeekly = $this->getCachedRankingRedis('sander', $weeklyRange, 27, $weeklyTTL);
        $receiverWeekly = $this->getCachedRankingRedis('receiver', $weeklyRange, 27, $weeklyTTL);
        $familyWeekly = $this->getCachedRankingRedis('family', $weeklyRange, 27, $weeklyTTL);

        // Prepare response - EXACT same structure as before
        return response()->json([[
            'message' => 'Host Data Show',
            'code' => '200',
            
            // Daily rankings
            'topthreetodaySander' => array_slice($sanderDaily, 0, 3),
            'toptodaySander' => array_slice($sanderDaily, 3),
            'sander_today_source' => 'cache',
            
            'topthreetodayreciver' => array_slice($receiverDaily, 0, 3),
            'toptodayreciver' => array_slice($receiverDaily, 3),
            'receiver_today_source' => 'cache',
            
            'topthreetodayfamily' => array_slice($familyDaily, 0, 3),
            'toptodayfamily' => array_slice($familyDaily, 3),
            'family_today_source' => 'cache',
            
            // Weekly rankings
            'topthreeweeklySander' => array_slice($sanderWeekly, 0, 3),
            'topweeklySander' => array_slice($sanderWeekly, 3),
            'sander_week_source' => 'cache',
            
            'topthreeweeklyreciver' => array_slice($receiverWeekly, 0, 3),
            'topweeklyreciver' => array_slice($receiverWeekly, 3),
            'receiver_week_source' => 'cache',
            
            'topthreeweeklyfamily' => array_slice($familyWeekly, 0, 3),
            'topweeklyfamily' => array_slice($familyWeekly, 3),
            'family_week_source' => 'cache',
        ]], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resolve N ranking blocks with a single Redis pipeline read.
     * $blocks: name => ['key'=>..., 'ttl'=>..., 'cb'=>callable():Collection|array]
     * Cache HIT path = 1 Redis round-trip total; misses run their cb + setex.
     */
    private function resolveRankingBlocks(array $blocks)
    {
        $rawByName = [];
        try {
            $orderedKeys = [];
            foreach ($blocks as $b) {
                $orderedKeys[] = $b['key'];
            }
            $piped = Redis::pipeline(function ($pipe) use ($orderedKeys) {
                foreach ($orderedKeys as $k) {
                    $pipe->get($k);
                }
            });
            $i = 0;
            foreach ($blocks as $name => $b) {
                $rawByName[$name] = isset($piped[$i]) ? $piped[$i] : null;
                $i++;
            }
        } catch (\Throwable $e) {
            Log::error('Ranking pipeline get failed', ['error' => $e->getMessage()]);
        }

        $out = [];
        foreach ($blocks as $name => $b) {
            $raw = isset($rawByName[$name]) ? $rawByName[$name] : null;
            if (!empty($raw)) {
                $val = @unserialize($raw);
                if ($val !== false) {
                    $out[$name] = $val;
                    continue;
                }
            }
            $data   = $b['cb']();
            $result = ($data instanceof \Illuminate\Support\Collection) ? $data->toArray() : $data;
            try {
                Redis::setex($b['key'], $b['ttl'], serialize($result));
            } catch (\Throwable $e) {
                Log::error('Ranking cache set failed', ['key' => $b['key'], 'error' => $e->getMessage()]);
            }
            $out[$name] = $result;
        }
        return $out;
    }

    /**
     * Get cached ranking data using Redis direct - PHP 7.4 compatible
     */
    private function getCachedRankingRedis($type, $dateRange, $limit, $ttl)
    {
        $cacheKey = $this->prefix . "ranking_{$type}_{$dateRange['start']}_{$dateRange['end']}_{$limit}";
        
        try {
            // Try Redis cache first
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return unserialize($cached);
            }
        } catch (\Exception $e) {
            Log::error("Redis get failed for ranking", [
                'error' => $e->getMessage(),
                'type' => $type,
                'key' => $cacheKey
            ]);
        }
        
        // Cache miss - get from database
        // PHP 7.4 compatible if-else instead of match
        if ($type === 'sander') {
            $data = Gift::sanderRanking($dateRange['start'], $dateRange['end'], 0, $limit);
        } elseif ($type === 'receiver') {
            $data = Gift::receiverRanking($dateRange['start'], $dateRange['end'], 0, $limit);
        } elseif ($type === 'family') {
            $data = Gift::familyRanking($dateRange['start'], $dateRange['end'], 0, $limit);
        } else {
            $data = collect([]);
        }
        
        // Convert to array if it's a collection
        $result = ($data instanceof \Illuminate\Support\Collection) ? $data->toArray() : $data;
        
        // Store in Redis cache
        try {
            Redis::setex($cacheKey, $ttl, serialize($result));
        } catch (\Exception $e) {
            Log::error("Redis set failed for ranking", [
                'error' => $e->getMessage(),
                'type' => $type,
                'key' => $cacheKey
            ]);
        }
        
        return $result;
    }

    /**
     * Get cached game ranking using Redis direct - PHP 7.4 compatible
     */
    private function getCachedGameRankingRedis($dateRange, $limit, $ttl)
    {
        $cacheKey = $this->prefix . "game_ranking_{$dateRange['start']}_{$dateRange['end']}_{$limit}";
        
        try {
            // Try Redis cache first
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return unserialize($cached);
            }
        } catch (\Exception $e) {
            Log::error("Redis get failed for game ranking", [
                'error' => $e->getMessage(),
                'key' => $cacheKey
            ]);
        }
        
        // Cache miss - get from database
        $data = FuritsPotsBackup::gameRanking($dateRange['start'], $dateRange['end'], 0, $limit);
        
        // Convert to array if it's a collection
        $result = ($data instanceof \Illuminate\Support\Collection) ? $data->toArray() : $data;
        
        // Store in Redis cache
        try {
            Redis::setex($cacheKey, $ttl, serialize($result));
        } catch (\Exception $e) {
            Log::error("Redis set failed for game ranking", [
                'error' => $e->getMessage(),
                'key' => $cacheKey
            ]);
        }
        
        return $result;
    }

    /**
     * Optional: Clear ranking caches when data changes
     */
    public function clearRankingCaches($date = null)
    {
        $date = $date ?? Carbon::now()->toDateString();
        $month = Carbon::now()->format('Y-m');
        
        try {
            $patterns = [
                $this->prefix . "ranking_sander_*",
                $this->prefix . "ranking_receiver_*",
                $this->prefix . "ranking_family_*",
                $this->prefix . "game_ranking_*"
            ];
            
            $totalDeleted = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys($pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalDeleted += $deleted;
                }
            }
            
            Log::info("Ranking caches cleared", ['keys_deleted' => $totalDeleted]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear ranking caches", ['error' => $e->getMessage()]);
            return false;
        }
    }
}