<?php

namespace App\Http\Controllers;

use App\Helpers\PerformanceLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ServerStatusController extends Controller
{
    protected $password = '#jambo';
    protected $serverCostUSD = 288;
    protected $bdtExchangeRate = 120;
    
    public function markTargetComplete(Request $request)
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $targetId = $request->input('target_id');
        $userId = Session::getId();
        
        // Store completed targets in session or database
        $completedTargets = Session::get('completed_targets', []);
        if (!in_array($targetId, $completedTargets)) {
            $completedTargets[] = $targetId;
            Session::put('completed_targets', $completedTargets);
        }
        
        return response()->json(['success' => true, 'message' => 'Target marked as complete']);
    }

    public function dashboard()
    {
        // Check if authenticated
        if (!Session::has('server_status_authenticated')) {
            return view('server-status', ['showLogin' => true]);
        }

        // Check timeout (30 minutes)
        $lastActivity = Session::get('server_status_last_activity', 0);
        if (time() - $lastActivity > 1800) {
            Session::forget('server_status_authenticated');
            Session::forget('server_status_last_activity');
            return view('server-status', ['showLogin' => true, 'error' => 'Session expired. Please login again.']);
        }

        // Update last activity
        Session::put('server_status_last_activity', time());

        // Get stats from JSON storage
        $stats = PerformanceLogger::getStats(7);
        
        // Get system info
        $system = $this->getSystemInfo();
        
        // Calculate active users
        $activeUsers = $this->calculateActiveUsers($stats['recent_requests'] ?? []);
        
        // Calculate comparison
        $comparison = $this->calculateComparison($stats);
        
        // Get AI suggestions
        $aiSuggestions = $this->getAISuggestions($stats);
        
        // Calculate server cost in BDT
        $serverCostBDT = $this->serverCostUSD * $this->bdtExchangeRate;
        
        // Calculate cost per request
        $totalRequests = $stats['today']['total_requests'] ?? 1;
        $costPerRequestUSD = $this->serverCostUSD / max($totalRequests, 1);
        $costPerRequestBDT = $costPerRequestUSD * $this->bdtExchangeRate;
        
        // Calculate savings in money
        $cacheHits = $stats['today']['total_cache_hits'] ?? 0;
        $queriesSaved = $cacheHits * 5; // Each cache hit saves ~5 queries
        $moneySavedUSD = ($queriesSaved / 1000) * 0.001;
        $moneySavedBDT = $moneySavedUSD * $this->bdtExchangeRate;
        
        // Calculate bandwidth savings (estimate: 2KB per query saved)
        $bandwidthSavedGB = ($queriesSaved * 2 * 1024) / (1024 * 1024 * 1024);
        $bandwidthCostSaved = $bandwidthSavedGB * 0.01; // $0.01 per GB estimate
        
        // Get raw data for week navigation
        $rawData = PerformanceLogger::getRawData(35);
        $availableWeeks = $this->getAllWeeks($rawData);
        
        return view('server-status', [
            'stats' => $stats,
            'system' => $system,
            'active_users' => $activeUsers,
            'today' => $stats['today'] ?? [],
            'alerts' => array_slice($stats['alerts'] ?? [], 0, 5),
            'problems' => array_slice($stats['problems'] ?? [], 0, 5),
            'recent' => array_slice($stats['recent_requests'] ?? [], 0, 10),
            'savings' => $stats['savings_total'] ?? [],
            'comparison' => $comparison,
            'with_cache' => $stats['with_cache_total'] ?? [],
            'without_cache' => $stats['without_cache_total'] ?? [],
            'ai_suggestions' => array_slice($aiSuggestions, 0, 3),
            'session_timeout' => 1800,
            'showLogin' => false,
            'server_info' => [
                'cpu_cores' => 16,
                'cpu_model' => 'AMD EPYC 7713',
                'cpu_speed' => '2.0 GHz',
                'cpu_cache' => '512 KB/core',
                'total_memory' => '32 GB',
                'memory_details' => '32.7GB Total / 4.1GB Used',
                'disk' => '640 GB SSD',
                'disk_details' => '/dev/sda: 640GB',
                'os' => 'AlmaLinux 8.10',
                'kernel' => '4.18.0-553.27.1.el8_10.x86_64',
                'hostname' => '139-162-49-107.ip.linodeusercontent.com',
                'server_cost_usd' => $this->serverCostUSD,
                'server_cost_bdt' => $serverCostBDT,
                'cost_per_request_usd' => round($costPerRequestUSD, 8),
                'cost_per_request_bdt' => round($costPerRequestBDT, 6),
                'money_saved_usd' => round($moneySavedUSD, 4),
                'money_saved_bdt' => round($moneySavedBDT, 2),
                'bandwidth_saved_gb' => round($bandwidthSavedGB, 2),
                'bandwidth_cost_saved' => round($bandwidthCostSaved, 2),
            ],
            'available_weeks' => $availableWeeks,
            'current_week' => Carbon::now()->weekOfYear,
            'current_year' => Carbon::now()->year,
        ]);
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);
        
        if ($request->password === $this->password) {
            Session::put('server_status_authenticated', true);
            Session::put('server_status_last_activity', time());
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Invalid password'], 401);
    }
    
    public function logout(Request $request)
    {
        Session::forget('server_status_authenticated');
        Session::forget('server_status_last_activity');
        return response()->json(['success' => true]);
    }
    
    public function checkSession()
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['authenticated' => false]);
        }
        
        $lastActivity = Session::get('server_status_last_activity', 0);
        if (time() - $lastActivity > 1800) {
            Session::forget('server_status_authenticated');
            Session::forget('server_status_last_activity');
            return response()->json(['authenticated' => false, 'expired' => true]);
        }
        
        Session::put('server_status_last_activity', time());
        return response()->json(['authenticated' => true]);
    }

    public function weekAnalysis(Request $request)
    {
        // Check if authenticated
        if (!Session::has('server_status_authenticated')) {
            return redirect()->route('server.status.dashboard');
        }

        $selectedWeek = $request->get('week', Carbon::now()->weekOfYear);
        $selectedYear = $request->get('year', Carbon::now()->year);
        
        // Get raw data from PerformanceLogger (only when viewing)
        $rawData = PerformanceLogger::getRawData(35); // Get last 35 days for week analysis
        
        // Perform analysis in controller
        $weekAnalysis = $this->analyzeWeekData($rawData, $selectedWeek, $selectedYear);
        $weekComparison = $this->getWeekComparison($rawData);
        $performanceTrend = $this->getPerformanceTrend($rawData, 8);
        $dailyBreakdown = $this->getDailyBreakdown($rawData, $selectedWeek, $selectedYear);
        
        // Get all available weeks
        $allWeeks = $this->getAllWeeks($rawData);
        
        // Current week summary
        $currentWeek = $weekAnalysis['performance_rating'] ?? [];
        $cacheEfficiency = $weekAnalysis['cache_performance']['efficiency'] ?? 0;
        
        // Calculate cache impact
        $totalRequests = $weekAnalysis['total_requests'] ?? 0;
        $cacheHits = $weekAnalysis['cache_performance']['hits'] ?? 0;
        $savedTime = $weekAnalysis['cache_performance']['saved_time'] ?? 0;
        $savedCost = ($savedTime / (1000 * 3600)) * 0.05 * $this->bdtExchangeRate;
        
        // Get top slow queries
        $topSlowQueries = $this->getTopSlowQueries($weekAnalysis);
        
        // Performance by day of week
        $performanceByDay = $this->getPerformanceByDay($weekAnalysis);
        
        return view('server-status-week', [
            'week_analysis' => $weekAnalysis,
            'week_comparison' => $weekComparison,
            'performance_trend' => $performanceTrend,
            'daily_breakdown' => $dailyBreakdown,
            'all_weeks' => $allWeeks,
            'current_week' => $currentWeek,
            'cache_efficiency' => $cacheEfficiency,
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'saved_time' => $savedTime,
            'saved_cost' => $savedCost,
            'top_slow_queries' => $topSlowQueries,
            'performance_by_day' => $performanceByDay,
            'selected_week' => $selectedWeek,
            'selected_year' => $selectedYear,
            'server_info' => [
                'cpu_cores' => 16,
                'cpu_model' => 'AMD EPYC 7713',
                'total_memory' => '32 GB',
                'server_cost_usd' => $this->serverCostUSD,
                'server_cost_bdt' => $this->serverCostUSD * $this->bdtExchangeRate,
            ]
        ]);
    }

    /**
     * Get system information
     */
    protected function getSystemInfo()
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        
        // Memory info from /proc/meminfo
        $memInfo = $this->getMemoryInfo();
        
        // Disk info
        $diskInfo = $this->getDiskInfo();
        
        // OS info
        $osInfo = $this->getOSInfo();
        
        return [
            'cpu' => [
                'model' => 'AMD EPYC 7713',
                'cores' => 16,
                'speed' => '2.0 GHz',
                'cache' => '512 KB/core',
                'load' => round($load[0], 2),
                'load_5min' => round($load[1], 2),
                'load_15min' => round($load[2], 2),
                'percent' => min(100, round(($load[0] / 16) * 100)),
                'status' => $this->getStatus(min(100, round(($load[0] / 16) * 100)), 60, 80),
            ],
            'memory' => $memInfo,
            'disk' => $diskInfo,
            'os' => $osInfo
        ];
    }

    /**
     * Get memory information
     */
    protected function getMemoryInfo()
    {
        $memInfo = [];
        if (File::exists('/proc/meminfo')) {
            $content = File::get('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $content, $total);
            preg_match('/MemFree:\s+(\d+)/', $content, $free);
            preg_match('/MemAvailable:\s+(\d+)/', $content, $available);
            preg_match('/Buffers:\s+(\d+)/', $content, $buffers);
            preg_match('/Cached:\s+(\d+)/', $content, $cached);
            
            $total_kb = isset($total[1]) ? (int)$total[1] : 32913500;
            $free_kb = isset($free[1]) ? (int)$free[1] : 17104808;
            $available_kb = isset($available[1]) ? (int)$available[1] : 26483880;
            $buffers_kb = isset($buffers[1]) ? (int)$buffers[1] : 0;
            $cached_kb = isset($cached[1]) ? (int)$cached[1] : 11467496;
            
            $used_kb = $total_kb - $free_kb - $buffers_kb - $cached_kb;
        } else {
            // Default values if can't read from /proc
            $total_kb = 32913500;
            $used_kb = 4341196;
            $free_kb = 17104808;
            $available_kb = 26483880;
            $buffers_kb = 0;
            $cached_kb = 11467496;
        }
        
        return [
            'total' => $this->formatBytes($total_kb * 1024),
            'used' => $this->formatBytes($used_kb * 1024),
            'free' => $this->formatBytes($free_kb * 1024),
            'available' => $this->formatBytes($available_kb * 1024),
            'cached' => $this->formatBytes($cached_kb * 1024),
            'total_kb' => $total_kb,
            'used_kb' => $used_kb,
            'free_kb' => $free_kb,
            'percent' => round(($used_kb / $total_kb) * 100),
            'status' => $this->getStatus(round(($used_kb / $total_kb) * 100), 70, 85),
        ];
    }

    /**
     * Get disk information
     */
    protected function getDiskInfo()
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        
        return [
            'total' => $this->formatBytes($diskTotal),
            'used' => $this->formatBytes($diskUsed),
            'free' => $this->formatBytes($diskFree),
            'total_gb' => round($diskTotal / (1024 * 1024 * 1024), 2),
            'used_gb' => round($diskUsed / (1024 * 1024 * 1024), 2),
            'free_gb' => round($diskFree / (1024 * 1024 * 1024), 2),
            'percent' => round(($diskUsed / $diskTotal) * 100),
            'status' => $this->getStatus(round(($diskUsed / $diskTotal) * 100), 80, 90),
            'device' => '/dev/sda',
        ];
    }

    /**
     * Get OS information
     */
    protected function getOSInfo()
    {
        return [
            'hostname' => gethostname(),
            'kernel' => php_uname('r'),
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'uptime' => $this->getUptime(),
        ];
    }

    /**
     * Get system uptime
     */
    protected function getUptime()
    {
        if (File::exists('/proc/uptime')) {
            $uptime = (int)explode(' ', File::get('/proc/uptime'))[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return 'Unknown';
    }

    /**
     * Get AI suggestions
     */
    protected function getAISuggestions($stats)
    {
        $suggestions = [];
        
        // CPU suggestions
        if (($stats['today']['avg_execution_time'] ?? 0) > 500) {
            $suggestions[] = [
                'title' => '🚀 Optimize for AMD EPYC',
                'problem' => 'High response time on EPYC processor',
                'solutions' => [
                    'Enable AMD OPcache JIT',
                    'Use parallel processing (16 cores)',
                    'Implement Redis caching',
                    'Optimize database queries',
                    'Use queue workers'
                ],
                'impact' => 'High',
                'effort' => 'Medium',
            ];
        }
        
        // Memory suggestions
        if (($stats['today']['memory_percent'] ?? 0) > 70) {
            $suggestions[] = [
                'title' => '💾 Memory Optimization',
                'problem' => 'High memory usage on 32GB RAM',
                'solutions' => [
                    'Increase PHP memory_limit',
                    'Use Redis for caching',
                    'Optimize MySQL buffer pool',
                    'Enable PHP-FPM dynamic',
                    'Monitor with: free -h'
                ],
                'impact' => 'High',
                'effort' => 'Low',
            ];
        }
        
        // Cache suggestions
        if (($stats['today']['cache_efficiency'] ?? 100) < 70) {
            $suggestions[] = [
                'title' => '⚡ Cache Optimization',
                'problem' => 'Low cache efficiency: ' . ($stats['today']['cache_efficiency'] ?? 0) . '%',
                'solutions' => [
                    'Increase cache TTL',
                    'Pre-cache popular data',
                    'Use Redis instead of file cache',
                    'Implement cache tags',
                    'Add cache warming'
                ],
                'impact' => 'High',
                'effort' => 'Medium',
            ];
        }
        
        // Database suggestions
        if (($stats['today']['total_slow_queries'] ?? 0) > 10) {
            $suggestions[] = [
                'title' => '🗄️ Database Optimization',
                'problem' => 'High number of slow queries',
                'solutions' => [
                    'Add missing indexes',
                    'Optimize complex JOINs',
                    'Implement query caching',
                    'Use database read replicas',
                    'Archive old data'
                ],
                'impact' => 'High',
                'effort' => 'Medium',
            ];
        }
        
        // Cost optimization
        $suggestions[] = [
            'title' => '💰 Cost Optimization',
            'problem' => "Server cost: ৳" . number_format(288 * 120) . "/month",
            'solutions' => [
                'Implement aggressive caching',
                'Use CDN for static assets',
                'Optimize image sizes',
                'Enable GZIP compression',
                'Monitor bandwidth usage'
            ],
            'impact' => 'Medium',
            'effort' => 'Low',
        ];
        
        return $suggestions;
    }

    /**
     * Calculate comparison between with and without cache
     */
    protected function calculateComparison($stats)
    {
        $withCache = $stats['with_cache_total'] ?? ['queries' => 0, 'time' => 0, 'ram' => 0, 'bandwidth' => 0, 'io' => 0];
        $withoutCache = $stats['without_cache_total'] ?? ['queries' => 0, 'time' => 0, 'ram' => 0, 'bandwidth' => 0, 'io' => 0];
        
        return [
            'queries_saved_percent' => $withoutCache['queries'] > 0 ? 
                round((($withoutCache['queries'] - $withCache['queries']) / $withoutCache['queries']) * 100, 2) : 0,
            'time_saved_percent' => $withoutCache['time'] > 0 ? 
                round((($withoutCache['time'] - $withCache['time']) / $withoutCache['time']) * 100, 2) : 0,
            'ram_saved_percent' => $withoutCache['ram'] > 0 ? 
                round((($withoutCache['ram'] - $withCache['ram']) / $withoutCache['ram']) * 100, 2) : 0,
            'bandwidth_saved_percent' => $withoutCache['bandwidth'] > 0 ? 
                round((($withoutCache['bandwidth'] - $withCache['bandwidth']) / $withoutCache['bandwidth']) * 100, 2) : 0,
            'io_saved_percent' => $withoutCache['io'] > 0 ? 
                round((($withoutCache['io'] - $withCache['io']) / $withoutCache['io']) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate active users
     */
    protected function calculateActiveUsers($recentRequests)
    {
        $activeUsers = [];
        $now = time();
        
        foreach ($recentRequests as $request) {
            $timestamp = strtotime($request['timestamp'] ?? '');
            if ($timestamp && ($now - $timestamp) < 900) { // Last 15 minutes
                if (!empty($request['user_id']) && $request['user_id'] != 0) {
                    $activeUsers[$request['user_id']] = true;
                }
            }
        }
        
        return count($activeUsers);
    }

    /**
     * Get status based on value thresholds
     */
    protected function getStatus($value, $warning, $critical)
    {
        if ($value >= $critical) return 'critical';
        if ($value >= $warning) return 'warning';
        return 'good';
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Analyze week data from raw records
     */
    protected function analyzeWeekData($rawData, $targetWeek, $targetYear)
    {
        $weekData = $this->initializeWeekData($targetWeek, $targetYear);
        
        foreach ($rawData as $date => $records) {
            $carbonDate = Carbon::parse($date);
            
            // Check if record belongs to target week
            if ($carbonDate->weekOfYear == $targetWeek && $carbonDate->year == $targetYear) {
                foreach ($records as $record) {
                    $this->aggregateRecordToWeek($weekData, $record, $date);
                }
            }
        }
        
        // Calculate final metrics
        $weekData = $this->calculateWeekMetrics($weekData);
        
        return $weekData;
    }

    /**
     * Initialize week data structure
     */
    protected function initializeWeekData($weekNumber, $year)
    {
        $startDate = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek();
        $endDate = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek();
        
        return [
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'daily' => [],
            'total_requests' => 0,
            'total_cache_hits' => 0,
            'total_cache_misses' => 0,
            'total_queries' => 0,
            'total_slow_queries' => 0,
            'total_execution_time' => 0,
            'total_memory_used' => 0,
            'peak_execution_time' => 0,
            'peak_memory' => 0,
            'cache_performance' => [
                'hits' => 0,
                'misses' => 0,
                'efficiency' => 0,
                'saved_time' => 0,
                'estimated_cost_saved' => 0
            ],
            'database_performance' => [
                'query_count' => 0,
                'slow_queries' => 0,
                'total_time' => 0,
                'avg_query_time' => 0
            ],
            'slow_queries_analysis' => [],
            'performance_rating' => [
                'overall' => 'Excellent',
                'cache_rating' => 'Excellent',
                'db_rating' => 'Excellent',
                'response_rating' => 'Excellent',
                'score' => 100,
                'recommendations' => []
            ]
        ];
    }

    /**
     * Initialize daily data structure
     */
    protected function initializeDailyData($date)
    {
        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->englishDayOfWeek,
            'total_requests' => 0,
            'total_cache_hits' => 0,
            'total_cache_misses' => 0,
            'total_queries' => 0,
            'total_slow_queries' => 0,
            'total_execution_time' => 0,
            'total_memory_used' => 0,
            'peak_execution_time' => 0,
            'peak_memory' => 0,
            'cache_performance' => [
                'hits' => 0,
                'misses' => 0,
                'efficiency' => 0,
                'saved_time' => 0
            ],
            'database_performance' => [
                'query_count' => 0,
                'slow_queries' => 0,
                'total_time' => 0,
                'avg_query_time' => 0
            ]
        ];
    }

    /**
     * Aggregate a single record to week data
     */
    protected function aggregateRecordToWeek(&$weekData, $record, $date)
    {
        // Initialize daily data if not exists
        if (!isset($weekData['daily'][$date])) {
            $weekData['daily'][$date] = $this->initializeDailyData($date);
        }
        
        $daily = &$weekData['daily'][$date];
        
        // Update daily metrics
        $daily['total_requests']++;
        $daily['total_cache_hits'] += $record['cache_hits'] ?? 0;
        $daily['total_cache_misses'] += $record['cache_misses'] ?? 0;
        $daily['total_queries'] += $record['query_count'] ?? 0;
        $daily['total_slow_queries'] += $record['slow_query_count'] ?? 0;
        $daily['total_execution_time'] += $record['execution_time'] ?? 0;
        $daily['total_memory_used'] += $record['memory_used'] ?? 0;
        $daily['peak_execution_time'] = max($daily['peak_execution_time'], $record['execution_time'] ?? 0);
        $daily['peak_memory'] = max($daily['peak_memory'], $record['memory_used'] ?? 0);
        
        // Update cache performance
        $daily['cache_performance']['hits'] += $record['cache_hits'] ?? 0;
        $daily['cache_performance']['misses'] += $record['cache_misses'] ?? 0;
        $daily['cache_performance']['saved_time'] += $record['estimated_saved_ms'] ?? 0;
        
        // Update database performance
        $daily['database_performance']['query_count'] += $record['query_count'] ?? 0;
        $daily['database_performance']['slow_queries'] += $record['slow_query_count'] ?? 0;
        $daily['database_performance']['total_time'] += $record['db_total_time'] ?? 0;
        
        // Update week totals
        $weekData['total_requests']++;
        $weekData['total_cache_hits'] += $record['cache_hits'] ?? 0;
        $weekData['total_cache_misses'] += $record['cache_misses'] ?? 0;
        $weekData['total_queries'] += $record['query_count'] ?? 0;
        $weekData['total_slow_queries'] += $record['slow_query_count'] ?? 0;
        $weekData['total_execution_time'] += $record['execution_time'] ?? 0;
        $weekData['total_memory_used'] += $record['memory_used'] ?? 0;
        $weekData['peak_execution_time'] = max($weekData['peak_execution_time'], $record['execution_time'] ?? 0);
        $weekData['peak_memory'] = max($weekData['peak_memory'], $record['memory_used'] ?? 0);
        
        // Update week cache performance
        $weekData['cache_performance']['hits'] += $record['cache_hits'] ?? 0;
        $weekData['cache_performance']['misses'] += $record['cache_misses'] ?? 0;
        $weekData['cache_performance']['saved_time'] += $record['estimated_saved_ms'] ?? 0;
        
        // Update week database performance
        $weekData['database_performance']['query_count'] += $record['query_count'] ?? 0;
        $weekData['database_performance']['slow_queries'] += $record['slow_query_count'] ?? 0;
        $weekData['database_performance']['total_time'] += $record['db_total_time'] ?? 0;
        
        // Track slow queries
        if (!empty($record['slow_queries'])) {
            foreach ($record['slow_queries'] as $slowQuery) {
                $sqlHash = md5($slowQuery['sql']);
                if (!isset($weekData['slow_queries_analysis'][$sqlHash])) {
                    $weekData['slow_queries_analysis'][$sqlHash] = [
                        'sql' => $slowQuery['sql'],
                        'total_time' => 0,
                        'count' => 0,
                        'avg_time' => 0,
                        'days' => []
                    ];
                }
                
                $weekData['slow_queries_analysis'][$sqlHash]['total_time'] += $slowQuery['time'];
                $weekData['slow_queries_analysis'][$sqlHash]['count']++;
                $weekData['slow_queries_analysis'][$sqlHash]['avg_time'] = 
                    $weekData['slow_queries_analysis'][$sqlHash]['total_time'] / 
                    $weekData['slow_queries_analysis'][$sqlHash]['count'];
                
                if (!in_array($date, $weekData['slow_queries_analysis'][$sqlHash]['days'])) {
                    $weekData['slow_queries_analysis'][$sqlHash]['days'][] = $date;
                }
            }
        }
    }

    /**
     * Calculate final week metrics
     */
    protected function calculateWeekMetrics($weekData)
    {
        // Calculate daily efficiencies
        foreach ($weekData['daily'] as $date => &$day) {
            $totalCache = $day['cache_performance']['hits'] + $day['cache_performance']['misses'];
            $day['cache_performance']['efficiency'] = $totalCache > 0 ? 
                round(($day['cache_performance']['hits'] / $totalCache) * 100, 2) : 0;
            
            $day['database_performance']['avg_query_time'] = $day['database_performance']['query_count'] > 0 ? 
                round($day['database_performance']['total_time'] / $day['database_performance']['query_count'], 2) : 0;
        }
        
        // Calculate week cache efficiency
        $totalCache = $weekData['cache_performance']['hits'] + $weekData['cache_performance']['misses'];
        $weekData['cache_performance']['efficiency'] = $totalCache > 0 ? 
            round(($weekData['cache_performance']['hits'] / $totalCache) * 100, 2) : 0;
        
        // Calculate week database metrics
        $weekData['database_performance']['avg_query_time'] = $weekData['database_performance']['query_count'] > 0 ? 
            round($weekData['database_performance']['total_time'] / $weekData['database_performance']['query_count'], 2) : 0;
        
        // Calculate performance rating
        $weekData['performance_rating'] = $this->calculatePerformanceRating($weekData);
        
        return $weekData;
    }

    /**
     * Calculate performance rating based on cache data
     */
    protected function calculatePerformanceRating($weekData)
    {
        // Cache Efficiency Rating
        $cacheEfficiency = $weekData['cache_performance']['efficiency'];
        
        if ($cacheEfficiency >= 90) {
            $cacheRating = 'Excellent';
            $cacheScore = 100;
        } elseif ($cacheEfficiency >= 75) {
            $cacheRating = 'Good';
            $cacheScore = 80;
        } elseif ($cacheEfficiency >= 50) {
            $cacheRating = 'Average';
            $cacheScore = 60;
        } else {
            $cacheRating = 'Poor';
            $cacheScore = 40;
        }
        
        // Database Rating (based on slow query percentage)
        $slowQueryPercentage = $weekData['total_queries'] > 0 ? 
            ($weekData['total_slow_queries'] / $weekData['total_queries']) * 100 : 0;
        
        if ($slowQueryPercentage <= 1) {
            $dbRating = 'Excellent';
            $dbScore = 100;
        } elseif ($slowQueryPercentage <= 5) {
            $dbRating = 'Good';
            $dbScore = 80;
        } elseif ($slowQueryPercentage <= 10) {
            $dbRating = 'Average';
            $dbScore = 60;
        } else {
            $dbRating = 'Poor';
            $dbScore = 40;
        }
        
        // Response Time Rating
        $avgResponseTime = $weekData['total_requests'] > 0 ? 
            $weekData['total_execution_time'] / $weekData['total_requests'] : 0;
        
        if ($avgResponseTime < 100) {
            $responseRating = 'Excellent';
            $responseScore = 100;
        } elseif ($avgResponseTime < 250) {
            $responseRating = 'Good';
            $responseScore = 80;
        } elseif ($avgResponseTime < 500) {
            $responseRating = 'Average';
            $responseScore = 60;
        } else {
            $responseRating = 'Poor';
            $responseScore = 40;
        }
        
        // Overall Score (weighted: Cache 40%, DB 30%, Response 30%)
        $overallScore = ($cacheScore * 0.4) + ($dbScore * 0.3) + ($responseScore * 0.3);
        
        if ($overallScore >= 90) {
            $overallRating = 'Excellent';
        } elseif ($overallScore >= 75) {
            $overallRating = 'Good';
        } elseif ($overallScore >= 60) {
            $overallRating = 'Average';
        } else {
            $overallRating = 'Poor';
        }
        
        // Generate recommendations
        $recommendations = [];
        
        if ($cacheEfficiency < 75) {
            $recommendations[] = [
                'issue' => 'ক্যাশ এফিসিয়েন্সি কম',
                'current' => $cacheEfficiency . '%',
                'target' => '90%',
                'action' => 'ক্যাশ টাইম বাড়ান এবং জনপ্রিয় ডেটা প্রি-ক্যাশ করুন',
                'impact' => 'উচ্চ'
            ];
        }
        
        if ($slowQueryPercentage > 5) {
            $recommendations[] = [
                'issue' => 'ধীর কোয়েরির হার বেশি',
                'current' => round($slowQueryPercentage, 2) . '%',
                'target' => '< 5%',
                'action' => 'ইনডেক্স যোগ করুন এবং কোয়েরি অপ্টিমাইজ করুন',
                'impact' => 'উচ্চ'
            ];
        }
        
        if ($avgResponseTime > 250) {
            $recommendations[] = [
                'issue' => 'রেসপন্স টাইম বেশি',
                'current' => round($avgResponseTime, 2) . 'ms',
                'target' => '< 250ms',
                'action' => 'ক্যাশিং ইমপ্লিমেন্ট করুন এবং ডাটাবেজ অপ্টিমাইজ করুন',
                'impact' => 'মাঝারি'
            ];
        }
        
        // Calculate cost savings
        $savedTimeHours = $weekData['cache_performance']['saved_time'] / (1000 * 3600);
        $savedCost = $savedTimeHours * 0.05; // $0.05 per hour saved
        
        return [
            'overall' => $overallRating,
            'cache_rating' => $cacheRating,
            'db_rating' => $dbRating,
            'response_rating' => $responseRating,
            'score' => round($overallScore, 2),
            'metrics' => [
                'cache_efficiency' => round($cacheEfficiency, 2),
                'slow_query_percentage' => round($slowQueryPercentage, 2),
                'avg_response_time' => round($avgResponseTime, 2)
            ],
            'recommendations' => $recommendations,
            'savings' => [
                'time_saved_ms' => $weekData['cache_performance']['saved_time'],
                'time_saved_hours' => round($savedTimeHours, 2),
                'cost_saved_usd' => round($savedCost, 2),
                'cost_saved_bdt' => round($savedCost * 120, 2)
            ]
        ];
    }

    /**
     * Get week comparison
     */
    protected function getWeekComparison($rawData)
    {
        $weeks = [];
        $currentDate = Carbon::now();
        
        // Get last 2 weeks
        for ($i = 0; $i < 2; $i++) {
            $weekNumber = $currentDate->copy()->subWeeks($i)->weekOfYear;
            $year = $currentDate->copy()->subWeeks($i)->year;
            $weekData = $this->analyzeWeekData($rawData, $weekNumber, $year);
            if ($weekData['total_requests'] > 0) {
                $weeks[] = $weekData;
            }
        }
        
        if (count($weeks) < 2) {
            return [];
        }
        
        $currentWeek = $weeks[0];
        $previousWeek = $weeks[1];
        
        $comparison = [
            'current_week' => $currentWeek,
            'previous_week' => $previousWeek,
            'changes' => []
        ];
        
        // Calculate changes
        $comparison['changes']['requests'] = [
            'current' => $currentWeek['total_requests'],
            'previous' => $previousWeek['total_requests'],
            'change' => $previousWeek['total_requests'] > 0 ? 
                round((($currentWeek['total_requests'] - $previousWeek['total_requests']) / $previousWeek['total_requests']) * 100, 2) : 0
        ];
        
        $comparison['changes']['cache_efficiency'] = [
            'current' => $currentWeek['cache_performance']['efficiency'],
            'previous' => $previousWeek['cache_performance']['efficiency'],
            'change' => round($currentWeek['cache_performance']['efficiency'] - $previousWeek['cache_performance']['efficiency'], 2)
        ];
        
        $comparison['changes']['avg_response_time'] = [
            'current' => $currentWeek['total_requests'] > 0 ? 
                round($currentWeek['total_execution_time'] / $currentWeek['total_requests'], 2) : 0,
            'previous' => $previousWeek['total_requests'] > 0 ? 
                round($previousWeek['total_execution_time'] / $previousWeek['total_requests'], 2) : 0,
            'change' => $previousWeek['total_requests'] > 0 ? 
                round((($currentWeek['total_execution_time'] / $currentWeek['total_requests']) - 
                      ($previousWeek['total_execution_time'] / $previousWeek['total_requests'])), 2) : 0
        ];
        
        return $comparison;
    }

    /**
     * Get performance trend
     */
    protected function getPerformanceTrend($rawData, $weeks = 8)
    {
        $trend = [];
        $currentDate = Carbon::now();
        
        for ($i = 0; $i < $weeks; $i++) {
            $weekNumber = $currentDate->copy()->subWeeks($i)->weekOfYear;
            $year = $currentDate->copy()->subWeeks($i)->year;
            $weekData = $this->analyzeWeekData($rawData, $weekNumber, $year);
            
            if ($weekData['total_requests'] > 0) {
                $trend[] = [
                    'week' => "Week {$weekNumber}",
                    'year' => $year,
                    'requests' => $weekData['total_requests'],
                    'cache_efficiency' => $weekData['cache_performance']['efficiency'],
                    'avg_response_time' => $weekData['total_requests'] > 0 ? 
                        round($weekData['total_execution_time'] / $weekData['total_requests'], 2) : 0,
                    'performance_score' => $weekData['performance_rating']['score']
                ];
            }
        }
        
        return array_reverse($trend);
    }

    /**
     * Get daily breakdown for a specific week
     */
    protected function getDailyBreakdown($rawData, $weekNumber, $year)
    {
        $weekData = $this->analyzeWeekData($rawData, $weekNumber, $year);
        $daily = [];
        
        foreach ($weekData['daily'] as $date => $dayData) {
            $totalCache = $dayData['cache_performance']['hits'] + $dayData['cache_performance']['misses'];
            $daily[] = [
                'date' => Carbon::parse($date)->format('D, M d'),
                'day_name' => $dayData['day_name'],
                'requests' => $dayData['total_requests'],
                'cache_hits' => $dayData['cache_performance']['hits'],
                'cache_misses' => $dayData['cache_performance']['misses'],
                'cache_efficiency' => $totalCache > 0 ? 
                    round(($dayData['cache_performance']['hits'] / $totalCache) * 100, 2) : 0,
                'queries' => $dayData['database_performance']['query_count'],
                'slow_queries' => $dayData['database_performance']['slow_queries'],
                'avg_response_time' => $dayData['total_requests'] > 0 ? 
                    round($dayData['total_execution_time'] / $dayData['total_requests'], 2) : 0,
                'saved_time' => $dayData['cache_performance']['saved_time']
            ];
        }
        
        // Sort by date
        usort($daily, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });
        
        return $daily;
    }

    /**
     * Get all available weeks from data
     */
    protected function getAllWeeks($rawData)
    {
        $weeks = [];
        
        foreach ($rawData as $date => $records) {
            if (!empty($records)) {
                $carbonDate = Carbon::parse($date);
                $weekKey = $carbonDate->year . '-W' . $carbonDate->weekOfYear;
                
                if (!isset($weeks[$weekKey])) {
                    $startDate = $carbonDate->copy()->startOfWeek();
                    $endDate = $carbonDate->copy()->endOfWeek();
                    
                    $weeks[$weekKey] = [
                        'key' => $weekKey,
                        'number' => $carbonDate->weekOfYear,
                        'year' => $carbonDate->year,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'label' => "Week {$carbonDate->weekOfYear} ({$startDate->format('M d')} - {$endDate->format('M d, Y')})"
                    ];
                }
            }
        }
        
        // Sort by year-week descending
        uasort($weeks, function($a, $b) {
            return $b['year'] . str_pad($b['number'], 2, '0', STR_PAD_LEFT) <=> 
                   $a['year'] . str_pad($a['number'], 2, '0', STR_PAD_LEFT);
        });
        
        return array_values($weeks);
    }

    /**
     * Get top slow queries
     */
    protected function getTopSlowQueries($weekAnalysis)
    {
        $slowQueries = $weekAnalysis['slow_queries_analysis'] ?? [];
        
        // Sort by total time descending
        uasort($slowQueries, function($a, $b) {
            return $b['total_time'] <=> $a['total_time'];
        });
        
        return array_slice($slowQueries, 0, 10, true);
    }

    /**
     * Get performance by day of week
     */
    protected function getPerformanceByDay($weekAnalysis)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $performance = [];
        
        foreach ($days as $day) {
            $performance[$day] = [
                'avg_response_time' => 0,
                'avg_cache_efficiency' => 0,
                'total_requests' => 0,
                'count' => 0
            ];
        }
        
        foreach ($weekAnalysis['daily'] ?? [] as $date => $dayData) {
            $dayName = $dayData['day_name'];
            if (isset($performance[$dayName])) {
                $performance[$dayName]['avg_response_time'] += $dayData['total_requests'] > 0 ? 
                    $dayData['total_execution_time'] / $dayData['total_requests'] : 0;
                
                $totalCache = $dayData['cache_performance']['hits'] + $dayData['cache_performance']['misses'];
                $performance[$dayName]['avg_cache_efficiency'] += $totalCache > 0 ? 
                    ($dayData['cache_performance']['hits'] / $totalCache) * 100 : 0;
                
                $performance[$dayName]['total_requests'] += $dayData['total_requests'];
                $performance[$dayName]['count']++;
            }
        }
        
        // Calculate averages
        foreach ($performance as $day => &$data) {
            if ($data['count'] > 0) {
                $data['avg_response_time'] = round($data['avg_response_time'] / $data['count'], 2);
                $data['avg_cache_efficiency'] = round($data['avg_cache_efficiency'] / $data['count'], 2);
            }
        }
        
        return $performance;
    }
}