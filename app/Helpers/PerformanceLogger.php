<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class PerformanceLogger
{
    protected $startTime;
    protected $startMemory;
    protected $slowQueries = [];
    protected $endpoint;
    protected $dbTotalTime = 0;
    protected $queryCount = 0;
    protected $cpuCores = 16;
    
    // Configuration
    protected $config = [
        'slow_query_threshold' => 100,
        'sample_rate' => 100,
        'storage_path' => 'performance-data',
        'max_records' => 1000,
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->ensureStorageExists();
    }

    /**
     * Ensure storage directory exists
     */
    protected function ensureStorageExists()
    {
        $path = storage_path($this->config['storage_path']);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Start performance tracking
     */
    public function start()
    {
        if (!$this->shouldTrack()) {
            return false;
        }

        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
        $this->endpoint = $this->getEndpoint();

        DB::listen(function ($query) {
            $this->queryCount++;
            $this->dbTotalTime += $query->time;
            
            if ($query->time > $this->config['slow_query_threshold']) {
                $this->slowQueries[] = [
                    'sql' => $query->sql,
                    'time' => $query->time,
                    'bindings' => $query->bindings,
                    'timestamp' => now()->toIso8601String()
                ];
            }
        });

        return true;
    }

    /**
     * End tracking and store data as JSON
     */
    public function end($userId, array $cacheResults = [], $response = null)
    {
        if (!$this->startTime) {
            return;
        }

        $metrics = $this->collectMetrics($userId, $cacheResults, $response);
        
        // Store the record
        $this->storeRecord($metrics);
    }

    /**
     * Collect all metrics
     */
    protected function collectMetrics($userId, $cacheResults, $response)
    {
        $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);
        $memoryUsed = round((memory_get_usage() - $this->startMemory) / 1024, 2);
        
        // Cache stats
        $hitCount = 0;
        $missCount = 0;
        foreach ($cacheResults as $status) {
            if (strtolower($status) === 'hit') $hitCount++;
            elseif (strtolower($status) === 'miss') $missCount++;
        }

        return [
            'id' => uniqid(),
            'timestamp' => now()->toIso8601String(),
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'user_id' => $userId,
            'endpoint' => $this->endpoint,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'peak_memory' => round(memory_get_peak_usage() / 1024, 2),
            'cache_hits' => $hitCount,
            'cache_misses' => $missCount,
            'estimated_saved_ms' => $hitCount * 250,
            'query_count' => $this->queryCount,
            'slow_query_count' => count($this->slowQueries),
            'slow_queries' => $this->slowQueries,
            'db_total_time' => round($this->dbTotalTime, 2),
        ];
    }

    /**
     * Store record as JSON
     */
    protected function storeRecord($metrics)
    {
        $date = now()->format('Y-m-d');
        $file = storage_path($this->config['storage_path'] . "/{$date}.json");
        
        // Read existing records
        $records = [];
        if (File::exists($file)) {
            $records = json_decode(File::get($file), true) ?? [];
        }
        
        // Add new record
        array_unshift($records, $metrics);
        
        // Keep only last max_records
        $records = array_slice($records, 0, $this->config['max_records']);
        
        // Save back
        File::put($file, json_encode($records, JSON_PRETTY_PRINT));
    }

    /**
     * Get statistics for dashboard
     */
    public static function getStats($days = 7)
    {
        $instance = new self();
        $storagePath = storage_path($instance->config['storage_path']);
        $stats = [
            'today' => [],
            'history' => [],
            'alerts' => [],
            'recent_requests' => [],
            'problems' => [],
            'savings_total' => [],
            'with_cache_total' => ['queries' => 0, 'time' => 0, 'ram' => 0, 'bandwidth' => 0, 'io' => 0],
            'without_cache_total' => ['queries' => 0, 'time' => 0, 'ram' => 0, 'bandwidth' => 0, 'io' => 0],
        ];
        
        // Get aggregates if they exist
        $aggregatesFile = $storagePath . '/aggregates.json';
        if (File::exists($aggregatesFile)) {
            $stats['history'] = json_decode(File::get($aggregatesFile), true) ?? [];
            
            // Get today's stats
            $today = now()->format('Y-m-d');
            $stats['today'] = $stats['history'][$today] ?? [
                'total_requests' => 0,
                'total_cache_hits' => 0,
                'total_cache_misses' => 0,
                'total_queries' => 0,
                'total_slow_queries' => 0,
                'total_execution_time' => 0,
                'total_memory_used' => 0,
                'peak_execution_time' => 0,
                'peak_memory' => 0,
                'avg_execution_time' => 0,
                'cache_efficiency' => 0,
                'estimated_saved_ms' => 0,
                'avg_query_time' => 0,
            ];
            
            // Calculate averages
            if (($stats['today']['total_requests'] ?? 0) > 0) {
                $stats['today']['avg_execution_time'] = round(($stats['today']['total_execution_time'] ?? 0) / $stats['today']['total_requests'], 2);
                $totalCache = ($stats['today']['total_cache_hits'] ?? 0) + ($stats['today']['total_cache_misses'] ?? 0);
                $stats['today']['cache_efficiency'] = $totalCache > 0 ? round((($stats['today']['total_cache_hits'] ?? 0) / $totalCache) * 100) : 0;
                $stats['today']['estimated_saved_ms'] = ($stats['today']['total_cache_hits'] ?? 0) * 250;
                
                if (($stats['today']['total_queries'] ?? 0) > 0) {
                    $stats['today']['avg_query_time'] = round(($stats['today']['total_execution_time'] ?? 0) / ($stats['today']['total_queries'] ?? 1), 2);
                }
            }
        }
        
        // Get alerts
        $alertsFile = $storagePath . '/alerts.json';
        if (File::exists($alertsFile)) {
            $stats['alerts'] = json_decode(File::get($alertsFile), true) ?? [];
        }
        
        // Get recent requests (last 100 from today and yesterday)
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        
        $todayFile = $storagePath . "/{$today}.json";
        $yesterdayFile = $storagePath . "/{$yesterday}.json";
        
        $recentRequests = [];
        
        if (File::exists($todayFile)) {
            $todayRecords = json_decode(File::get($todayFile), true) ?? [];
            $recentRequests = array_merge($recentRequests, $todayRecords);
        }
        
        if (File::exists($yesterdayFile)) {
            $yesterdayRecords = json_decode(File::get($yesterdayFile), true) ?? [];
            $recentRequests = array_merge($recentRequests, $yesterdayRecords);
        }
        
        // Sort by timestamp descending
        usort($recentRequests, function($a, $b) {
            return strtotime($b['timestamp'] ?? '') <=> strtotime($a['timestamp'] ?? '');
        });
        
        $stats['recent_requests'] = array_slice($recentRequests, 0, 100);
        
        // Calculate cache vs no-cache totals
        foreach ($stats['recent_requests'] as $request) {
            // With cache (actual)
            $stats['with_cache_total']['queries'] += $request['query_count'] ?? 0;
            $stats['with_cache_total']['time'] += $request['execution_time'] ?? 0;
            $stats['with_cache_total']['ram'] += $request['memory_used'] ?? 0;
            
            // Without cache (estimated)
            $multiplier = 1 + (($request['cache_hits'] ?? 0) * 0.5); // Each cache hit saves ~50% resources
            $stats['without_cache_total']['queries'] += ($request['query_count'] ?? 0) * $multiplier;
            $stats['without_cache_total']['time'] += ($request['execution_time'] ?? 0) * $multiplier;
            $stats['without_cache_total']['ram'] += ($request['memory_used'] ?? 0) * $multiplier;
        }
        
        // Calculate savings
        $stats['savings_total'] = [
            'db_queries' => max(0, $stats['without_cache_total']['queries'] - $stats['with_cache_total']['queries']),
            'time' => max(0, $stats['without_cache_total']['time'] - $stats['with_cache_total']['time']),
            'ram' => max(0, $stats['without_cache_total']['ram'] - $stats['with_cache_total']['ram']),
            'bandwidth' => max(0, ($stats['without_cache_total']['queries'] - $stats['with_cache_total']['queries']) * 2 * 1024), // 2KB per query
        ];
        
        // Identify problems
        $stats['problems'] = [];
        foreach ($stats['recent_requests'] as $request) {
            if (($request['execution_time'] ?? 0) > 1000) {
                $stats['problems'][] = [
                    'type' => 'slow_response',
                    'message' => 'Slow response: ' . round($request['execution_time'] ?? 0, 2) . 'ms',
                    'endpoint' => $request['endpoint'] ?? 'unknown',
                    'timestamp' => $request['timestamp'] ?? '',
                ];
            }
            if (($request['slow_query_count'] ?? 0) > 0) {
                $stats['problems'][] = [
                    'type' => 'slow_query',
                    'message' => ($request['slow_query_count'] ?? 0) . ' slow queries',
                    'endpoint' => $request['endpoint'] ?? 'unknown',
                    'timestamp' => $request['timestamp'] ?? '',
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Get raw data for analysis (called by controller)
     */
    public static function getRawData($days = 30)
    {
        $instance = new self();
        $path = storage_path($instance->config['storage_path']);
        $data = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $file = $path . "/{$date}.json";
            
            if (File::exists($file)) {
                $records = json_decode(File::get($file), true) ?? [];
                $data[$date] = $records;
            }
        }
        
        return $data;
    }

    /**
     * Determine if we should track this request
     */
    protected function shouldTrack()
    {
        if ($this->config['sample_rate'] >= 100) {
            return true;
        }
        return mt_rand(1, 100) <= $this->config['sample_rate'];
    }

    /**
     * Get endpoint efficiently
     */
    protected function getEndpoint()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? $trace[1] ?? [];
        
        if (isset($caller['class'])) {
            return class_basename($caller['class']) . '@' . ($caller['function'] ?? 'unknown');
        }
        
        return request()->path() ?: 'unknown';
    }
}