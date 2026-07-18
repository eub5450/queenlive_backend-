<?php

namespace App\Http\Controllers;

use App\Helpers\PerformanceLogger;
use App\Helpers\SecurityScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServerHealthController extends Controller
{
    protected $password = '#jambo';
    protected $serverCostUSD = 288;
    protected $bdtExchangeRate = 120;
    
    // Track slow requests
    protected $slowRequestThreshold = 500; // ms
    protected $slowQueryThreshold = 100; // ms

    /**
     * Comprehensive server health check
     */
    public function healthCheck(Request $request)
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $checks = [
            'server' => $this->checkServerHealth(),
            'database' => $this->checkDatabaseHealth(),
            'redis' => $this->checkRedisHealth(),
            'cache' => $this->checkCacheHealth(),
            'queue' => $this->checkQueueHealth(),
            'storage' => $this->checkStorageHealth(),
            'security' => $this->checkSecurityHealth(),
            'performance' => $this->checkPerformanceHealth(),
            'timestamp' => Carbon::now(),
        ];

        // Calculate overall health
        $checks['overall'] = $this->calculateOverallHealth($checks);

        return response()->json($checks);
    }

    /**
     * Check server health
     */
    protected function checkServerHealth()
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cores = 16; // Your server cores
        
        return [
            'status' => $this->getHealthStatus(($load[0] / $cores) * 100, 70, 90),
            'cpu' => [
                'load' => $load,
                'usage_percent' => round(($load[0] / $cores) * 100, 2),
                'cores' => $cores,
            ],
            'memory' => $this->getMemoryHealth(),
            'uptime' => $this->getUptime(),
            'disk' => $this->getDiskHealth(),
        ];
    }

    /**
     * Check database health
     */
    protected function checkDatabaseHealth()
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $start) * 1000;

            // Get MySQL status
            $status = DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_connected', 'Max_used_connections', 'Slow_queries', 'Questions')");
            $variables = DB::select("SHOW VARIABLES WHERE Variable_name IN ('max_connections', 'innodb_buffer_pool_size')");

            $statusData = [];
            foreach ($status as $row) {
                $statusData[$row->Variable_name] = $row->Value;
            }

            $variablesData = [];
            foreach ($variables as $row) {
                $variablesData[$row->Variable_name] = $row->Value;
            }

            $connections = $statusData['Threads_connected'] ?? 0;
            $maxConnections = $variablesData['max_connections'] ?? 100;
            $connectionPercent = ($connections / $maxConnections) * 100;

            return [
                'status' => $this->getHealthStatus($queryTime, 100, 500),
                'connected' => true,
                'response_time' => round($queryTime, 2) . 'ms',
                'connections' => [
                    'current' => $connections,
                    'max' => $maxConnections,
                    'percent' => round($connectionPercent, 2),
                    'status' => $this->getHealthStatus($connectionPercent, 70, 90),
                ],
                'slow_queries' => $statusData['Slow_queries'] ?? 0,
                'total_queries' => $statusData['Questions'] ?? 0,
                'buffer_pool' => $this->formatBytes($variablesData['innodb_buffer_pool_size'] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis health
     */
    protected function checkRedisHealth()
    {
        try {
            if (!class_exists('Redis')) {
                return [
                    'status' => 'not_installed',
                    'active' => false,
                    'message' => 'Redis PHP extension not installed',
                ];
            }

            $redis = Redis::connection();
            $ping = $redis->ping();
            
            if ($ping === 'PONG' || $ping === true) {
                // Get Redis info
                $info = $redis->info();
                
                $usedMemory = $info['used_memory'] ?? 0;
                $totalMemory = $info['maxmemory'] ?? 0;
                $memoryPercent = $totalMemory > 0 ? ($usedMemory / $totalMemory) * 100 : 0;
                
                $connectedClients = $info['connected_clients'] ?? 0;
                $uptime = $info['uptime_in_seconds'] ?? 0;
                
                return [
                    'status' => 'active',
                    'active' => true,
                    'version' => $info['redis_version'] ?? 'unknown',
                    'memory' => [
                        'used' => $this->formatBytes($usedMemory),
                        'used_percent' => round($memoryPercent, 2),
                        'peak' => $this->formatBytes($info['used_memory_peak'] ?? 0),
                        'fragmentation' => round($info['mem_fragmentation_ratio'] ?? 1, 2),
                    ],
                    'stats' => [
                        'connected_clients' => $connectedClients,
                        'total_commands' => $info['total_commands_processed'] ?? 0,
                        'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                        'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                        'hit_ratio' => $this->calculateHitRatio($info),
                    ],
                    'uptime' => $this->formatUptime($uptime),
                    'role' => $info['role'] ?? 'master',
                ];
            } else {
                return [
                    'status' => 'inactive',
                    'active' => false,
                    'message' => 'Redis connected but ping failed',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'inactive',
                'active' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache health
     */
    protected function checkCacheHealth()
    {
        try {
            $key = 'health_check_' . uniqid();
            $value = 'ok';
            
            $start = microtime(true);
            Cache::put($key, $value, 1);
            $writeTime = (microtime(true) - $start) * 1000;
            
            $start = microtime(true);
            $retrieved = Cache::get($key);
            $readTime = (microtime(true) - $start) * 1000;
            
            Cache::forget($key);
            
            return [
                'status' => $retrieved === $value ? 'active' : 'failed',
                'write_time' => round($writeTime, 2) . 'ms',
                'read_time' => round($readTime, 2) . 'ms',
                'driver' => config('cache.default'),
                'working' => $retrieved === $value,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue health
     */
    protected function checkQueueHealth()
    {
        try {
            $connection = config('queue.default');
            
            return [
                'status' => 'active',
                'connection' => $connection,
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'jobs_pending' => DB::table('jobs')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage health
     */
    protected function checkStorageHealth()
    {
        $paths = [
            'storage' => storage_path(),
            'public' => public_path(),
            'bootstrap' => base_path('bootstrap/cache'),
        ];

        $health = [];
        foreach ($paths as $name => $path) {
            if (File::exists($path)) {
                $total = disk_total_space($path);
                $free = disk_free_space($path);
                $used = $total - $free;
                $percent = ($used / $total) * 100;
                
                $health[$name] = [
                    'path' => $path,
                    'total' => $this->formatBytes($total),
                    'used' => $this->formatBytes($used),
                    'free' => $this->formatBytes($free),
                    'used_percent' => round($percent, 2),
                    'status' => $this->getHealthStatus($percent, 80, 90),
                    'writable' => is_writable($path),
                ];
            }
        }

        return $health;
    }

    /**
     * Check security health
     */
    protected function checkSecurityHealth()
    {
        $scanResults = Cache::get('security_scan_results');
        
        // If scan results are stale (> 12 hours), trigger a new scan
        if (!$scanResults || Carbon::parse($scanResults['scanned_at'])->diffInHours() > 12) {
            $scanner = new SecurityScanner();
            $scanResults = $scanner->scanProject();
            Cache::put('security_scan_results', $scanResults, now()->addHours(12));
        }

        return [
            'risk_level' => $scanResults['risk_level'],
            'last_scan' => $scanResults['scanned_at'],
            'files_scanned' => $scanResults['scanned_files'],
            'suspicious_files' => count($scanResults['suspicious_files']),
            'malicious_files' => count($scanResults['malicious_files']),
            'modified_files_24h' => count($scanResults['modified_files']),
            'permission_issues' => $scanResults['permission_issues'] ?? [],
            'suspicious_logs' => $scanResults['suspicious_logs'] ?? [],
            'details' => $scanResults,
        ];
    }

    /**
     * Check performance health
     */
    protected function checkPerformanceHealth()
    {
        $stats = PerformanceLogger::getStats(1);
        
        return [
            'slow_requests' => $this->getSlowRequests(),
            'slow_queries' => $this->getSlowQueries(),
            'cache_efficiency' => $stats['today']['cache_efficiency'] ?? 0,
            'avg_response_time' => $stats['today']['avg_execution_time'] ?? 0,
            'peak_memory' => $this->formatBytes(($stats['today']['peak_memory'] ?? 0) * 1024),
        ];
    }

    /**
     * Get slow requests (controller and function)
     */
    protected function getSlowRequests($limit = 20)
    {
        $logs = [];
        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if (preg_match('/\[(.*?)\].*?duration:(\d+).*?(?:controller|route):([^\s]+)/i', $line, $matches)) {
                    if ($matches[2] > $this->slowRequestThreshold) {
                        $logs[] = [
                            'timestamp' => $matches[1],
                            'duration' => (int)$matches[2],
                            'controller' => $matches[3],
                            'path' => $this->extractPathFromLog($line),
                            'method' => $this->extractMethodFromLog($line),
                        ];
                    }
                }
            }
        }
        
        // Also check performance logger
        $stats = PerformanceLogger::getStats(1);
        foreach ($stats['recent_requests'] ?? [] as $request) {
            if (($request['execution_time'] ?? 0) > $this->slowRequestThreshold) {
                $logs[] = [
                    'timestamp' => $request['timestamp'],
                    'duration' => $request['execution_time'],
                    'controller' => $request['controller'] ?? 'unknown',
                    'path' => $request['path'] ?? '/',
                    'method' => $request['method'] ?? 'GET',
                ];
            }
        }
        
        // Sort by duration and get top
        usort($logs, function($a, $b) {
            return $b['duration'] <=> $a['duration'];
        });
        
        return array_slice($logs, 0, $limit);
    }

    /**
     * Get slow queries
     */
    protected function getSlowQueries($limit = 20)
    {
        $slowQueries = [];
        
        // Check MySQL slow query log if available
        $slowLogPath = '/var/log/mysql/mysql-slow.log';
        if (File::exists($slowLogPath)) {
            $content = File::get($slowLogPath);
            $entries = explode("# Time:", $content);
            
            foreach ($entries as $entry) {
                if (preg_match('/Query_time:\s+([0-9.]+).*?\n(.*?);/s', $entry, $matches)) {
                    $queryTime = (float)$matches[1] * 1000; // Convert to ms
                    if ($queryTime > $this->slowQueryThreshold) {
                        $slowQueries[] = [
                            'time' => $queryTime,
                            'sql' => trim($matches[2]),
                            'timestamp' => $this->extractTimestampFromSlowLog($entry),
                        ];
                    }
                }
            }
        }
        
        // Get from performance logger
        $stats = PerformanceLogger::getStats(7);
        foreach ($stats['slow_queries'] ?? [] as $query) {
            $slowQueries[] = [
                'time' => $query['time'] ?? 0,
                'sql' => $query['sql'] ?? '',
                'count' => $query['count'] ?? 1,
                'avg_time' => $query['avg_time'] ?? 0,
            ];
        }
        
        // Sort by time
        usort($slowQueries, function($a, $b) {
            return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
        });
        
        return array_slice($slowQueries, 0, $limit);
    }

    /**
     * Find hacking scripts
     */
    public function findHackingScripts()
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $scanner = new SecurityScanner();
        $results = $scanner->scanProject();
        
        // Specific hack script patterns
        $hackPatterns = [
            'c99shell', 'r57shell', 'wso', 'webshell', 'backdoor',
            'cmd.php', 'shell.php', 'eval\(', 'base64_decode\(', 'gzinflate\(',
        ];

        $foundHacks = [];
        
        foreach ($results['suspicious_files'] as $file) {
            $score = 0;
            $matches = [];
            
            $content = File::get($file['path']);
            foreach ($hackPatterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $score += 10;
                    $matches[] = $pattern;
                }
            }
            
            if ($score > 20) {
                $foundHacks[] = [
                    'path' => $file['path'],
                    'score' => $score,
                    'matches' => $matches,
                    'last_modified' => $file['last_modified'],
                ];
            }
        }

        return response()->json([
            'scan_time' => Carbon::now(),
            'found_hacks' => $foundHacks,
            'total_suspicious' => count($results['suspicious_files']),
            'risk_level' => $results['risk_level'],
            'recommendations' => $this->getSecurityRecommendations($foundHacks),
        ]);
    }

    /**
     * Schedule security scan (to be called from kernel)
     */
    public function scheduleSecurityScan()
    {
        $scanner = new SecurityScanner();
        $results = $scanner->scanProject();
        
        // Store results
        Cache::put('security_scan_results', $results, now()->addHours(12));
        
        // Check for critical issues
        if ($results['risk_level'] === 'CRITICAL' || !empty($results['malicious_files'])) {
            // Send notification
            Log::critical('Security scan found critical issues', [
                'risk_level' => $results['risk_level'],
                'malicious_files' => $results['malicious_files'],
                'suspicious_files' => count($results['suspicious_files']),
            ]);
            
            // You can add email notification here
            // Mail::to('admin@example.com')->send(new SecurityAlert($results));
        }
        
        return response()->json([
            'status' => 'completed',
            'results' => $results,
        ]);
    }

    /**
     * Get recommendations based on security findings
     */
    protected function getSecurityRecommendations($foundHacks)
    {
        $recommendations = [];
        
        if (!empty($foundHacks)) {
            $recommendations[] = [
                'severity' => 'CRITICAL',
                'action' => 'Immediately remove the following files: ' . implode(', ', array_column($foundHacks, 'path')),
            ];
        }
        
        $recommendations[] = [
            'severity' => 'HIGH',
            'action' => 'Update all passwords and API keys',
        ];
        
        $recommendations[] = [
            'severity' => 'MEDIUM',
            'action' => 'Review file permissions for storage and bootstrap/cache directories',
        ];
        
        $recommendations[] = [
            'severity' => 'MEDIUM',
            'action' => 'Enable Laravel security packages: Laravel Security, Laravel Auth, etc.',
        ];
        
        $recommendations[] = [
            'severity' => 'LOW',
            'action' => 'Keep Laravel and all packages updated to latest versions',
        ];
        
        return $recommendations;
    }

    /**
     * Enhanced week analysis with health checks
     */
    public function weekAnalysis(Request $request)
    {
        // Check if authenticated
        if (!Session::has('server_status_authenticated')) {
            return redirect()->route('server.status.dashboard');
        }

        $selectedWeek = $request->get('week', Carbon::now()->weekOfYear);
        $selectedYear = $request->get('year', Carbon::now()->year);
        
        // Get raw data from PerformanceLogger
        $rawData = PerformanceLogger::getRawData(35);
        
        // Perform analysis
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
        
        // Get health checks
        $healthChecks = [
            'redis' => $this->checkRedisHealth(),
            'database' => $this->checkDatabaseHealth(),
            'server' => $this->checkServerHealth(),
            'cache' => $this->checkCacheHealth(),
        ];
        
        // Get slow requests with controller and function
        $slowRequests = $this->getSlowRequests(50);
        
        // Get hacking scripts
        $securityScanner = new SecurityScanner();
        $securityScan = Cache::remember('security_scan_quick', 3600, function() use ($securityScanner) {
            return $securityScanner->scanProject();
        });
        
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
            ],
            // New data for enhanced monitoring
            'health_checks' => $healthChecks,
            'slow_requests' => $slowRequests,
            'security_scan' => $securityScan,
            'redis_active' => $healthChecks['redis']['active'] ?? false,
            'redis_details' => $healthChecks['redis'],
        ]);
    }

    // Helper methods (keeping existing ones from your controller)
    
    protected function getHealthStatus($value, $warning, $critical)
    {
        if ($value >= $critical) return 'critical';
        if ($value >= $warning) return 'warning';
        return 'good';
    }

    protected function getMemoryHealth()
    {
        $memInfo = $this->getMemoryInfo();
        return [
            'total' => $memInfo['total'],
            'used' => $memInfo['used'],
            'used_percent' => $memInfo['percent'],
            'status' => $memInfo['status'],
        ];
    }

    protected function getDiskHealth()
    {
        $diskInfo = $this->getDiskInfo();
        return [
            'total' => $diskInfo['total'],
            'used' => $diskInfo['used'],
            'used_percent' => $diskInfo['percent'],
            'status' => $diskInfo['status'],
        ];
    }

    protected function calculateHitRatio($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    protected function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }

    protected function calculateOverallHealth($checks)
    {
        $score = 100;
        $issues = [];
        
        if (isset($checks['redis']['status']) && $checks['redis']['status'] !== 'active') {
            $score -= 20;
            $issues[] = 'Redis is down';
        }
        
        if (isset($checks['database']['status']) && $checks['database']['status'] !== 'good') {
            $score -= 30;
            $issues[] = 'Database issues detected';
        }
        
        if (isset($checks['security']['risk_level']) && in_array($checks['security']['risk_level'], ['CRITICAL', 'HIGH'])) {
            $score -= 40;
            $issues[] = 'Security risk detected';
        }
        
        if (isset($checks['server']['memory']['status']) && $checks['server']['memory']['status'] === 'critical') {
            $score -= 20;
            $issues[] = 'Memory usage critical';
        }
        
        if (isset($checks['server']['disk']['used_percent']) && $checks['server']['disk']['used_percent'] > 90) {
            $score -= 20;
            $issues[] = 'Disk usage critical';
        }
        
        return [
            'score' => max(0, $score),
            'status' => $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'warning' : 'critical')),
            'issues' => $issues,
        ];
    }

    protected function extractPathFromLog($line)
    {
        if (preg_match('/path:([^\s]+)/', $line, $matches)) {
            return $matches[1];
        }
        return '/';
    }

    protected function extractMethodFromLog($line)
    {
        if (preg_match('/method:([^\s]+)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/"(GET|POST|PUT|DELETE|PATCH) /', $line, $matches)) {
            return $matches[1];
        }
        return 'GET';
    }

    protected function extractTimestampFromSlowLog($entry)
    {
        if (preg_match('/# Time: (\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})/', $entry, $matches)) {
            return $matches[1];
        }
        return Carbon::now()->toDateTimeString();
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function getMemoryInfo()
    {
        // Your existing getMemoryInfo method
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
            'status' => $this->getHealthStatus(round(($used_kb / $total_kb) * 100), 70, 85),
        ];
    }

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
            'status' => $this->getHealthStatus(round(($diskUsed / $diskTotal) * 100), 80, 90),
            'device' => '/dev/sda',
        ];
    }

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

    // Keep all your existing analysis methods
    protected function analyzeWeekData($rawData, $targetWeek, $targetYear)
    {
        // Your existing analyzeWeekData method
        $weekData = $this->initializeWeekData($targetWeek, $targetYear);
        
        foreach ($rawData as $date => $records) {
            $carbonDate = Carbon::parse($date);
            
            if ($carbonDate->weekOfYear == $targetWeek && $carbonDate->year == $targetYear) {
                foreach ($records as $record) {
                    $this->aggregateRecordToWeek($weekData, $record, $date);
                }
            }
        }
        
        return $this->calculateWeekMetrics($weekData);
    }

    protected function initializeWeekData($weekNumber, $year)
    {
        // Your existing initializeWeekData method
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

    protected function aggregateRecordToWeek(&$weekData, $record, $date)
    {
        // Your existing aggregateRecordToWeek method
        if (!isset($weekData['daily'][$date])) {
            $weekData['daily'][$date] = $this->initializeDailyData($date);
        }
        
        $daily = &$weekData['daily'][$date];
        
        $daily['total_requests']++;
        $daily['total_cache_hits'] += $record['cache_hits'] ?? 0;
        $daily['total_cache_misses'] += $record['cache_misses'] ?? 0;
        $daily['total_queries'] += $record['query_count'] ?? 0;
        $daily['total_slow_queries'] += $record['slow_query_count'] ?? 0;
        $daily['total_execution_time'] += $record['execution_time'] ?? 0;
        $daily['total_memory_used'] += $record['memory_used'] ?? 0;
        $daily['peak_execution_time'] = max($daily['peak_execution_time'], $record['execution_time'] ?? 0);
        $daily['peak_memory'] = max($daily['peak_memory'], $record['memory_used'] ?? 0);
        
        $daily['cache_performance']['hits'] += $record['cache_hits'] ?? 0;
        $daily['cache_performance']['misses'] += $record['cache_misses'] ?? 0;
        $daily['cache_performance']['saved_time'] += $record['estimated_saved_ms'] ?? 0;
        
        $daily['database_performance']['query_count'] += $record['query_count'] ?? 0;
        $daily['database_performance']['slow_queries'] += $record['slow_query_count'] ?? 0;
        $daily['database_performance']['total_time'] += $record['db_total_time'] ?? 0;
        
        $weekData['total_requests']++;
        $weekData['total_cache_hits'] += $record['cache_hits'] ?? 0;
        $weekData['total_cache_misses'] += $record['cache_misses'] ?? 0;
        $weekData['total_queries'] += $record['query_count'] ?? 0;
        $weekData['total_slow_queries'] += $record['slow_query_count'] ?? 0;
        $weekData['total_execution_time'] += $record['execution_time'] ?? 0;
        $weekData['total_memory_used'] += $record['memory_used'] ?? 0;
        $weekData['peak_execution_time'] = max($weekData['peak_execution_time'], $record['execution_time'] ?? 0);
        $weekData['peak_memory'] = max($weekData['peak_memory'], $record['memory_used'] ?? 0);
        
        $weekData['cache_performance']['hits'] += $record['cache_hits'] ?? 0;
        $weekData['cache_performance']['misses'] += $record['cache_misses'] ?? 0;
        $weekData['cache_performance']['saved_time'] += $record['estimated_saved_ms'] ?? 0;
        
        $weekData['database_performance']['query_count'] += $record['query_count'] ?? 0;
        $weekData['database_performance']['slow_queries'] += $record['slow_query_count'] ?? 0;
        $weekData['database_performance']['total_time'] += $record['db_total_time'] ?? 0;
        
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

    protected function calculateWeekMetrics($weekData)
    {
        foreach ($weekData['daily'] as $date => &$day) {
            $totalCache = $day['cache_performance']['hits'] + $day['cache_performance']['misses'];
            $day['cache_performance']['efficiency'] = $totalCache > 0 ? 
                round(($day['cache_performance']['hits'] / $totalCache) * 100, 2) : 0;
            
            $day['database_performance']['avg_query_time'] = $day['database_performance']['query_count'] > 0 ? 
                round($day['database_performance']['total_time'] / $day['database_performance']['query_count'], 2) : 0;
        }
        
        $totalCache = $weekData['cache_performance']['hits'] + $weekData['cache_performance']['misses'];
        $weekData['cache_performance']['efficiency'] = $totalCache > 0 ? 
            round(($weekData['cache_performance']['hits'] / $totalCache) * 100, 2) : 0;
        
        $weekData['database_performance']['avg_query_time'] = $weekData['database_performance']['query_count'] > 0 ? 
            round($weekData['database_performance']['total_time'] / $weekData['database_performance']['query_count'], 2) : 0;
        
        $weekData['performance_rating'] = $this->calculatePerformanceRating($weekData);
        
        return $weekData;
    }

    protected function calculatePerformanceRating($weekData)
    {
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
        
        $recommendations = [];
        
        if ($cacheEfficiency < 75) {
            $recommendations[] = [
                'issue' => 'Cache efficiency low',
                'current' => $cacheEfficiency . '%',
                'target' => '90%',
                'action' => 'Increase cache TTL and pre-cache popular data',
                'impact' => 'High'
            ];
        }
        
        if ($slowQueryPercentage > 5) {
            $recommendations[] = [
                'issue' => 'High slow query percentage',
                'current' => round($slowQueryPercentage, 2) . '%',
                'target' => '< 5%',
                'action' => 'Add indexes and optimize queries',
                'impact' => 'High'
            ];
        }
        
        if ($avgResponseTime > 250) {
            $recommendations[] = [
                'issue' => 'High response time',
                'current' => round($avgResponseTime, 2) . 'ms',
                'target' => '< 250ms',
                'action' => 'Implement caching and optimize database',
                'impact' => 'Medium'
            ];
        }
        
        $savedTimeHours = $weekData['cache_performance']['saved_time'] / (1000 * 3600);
        $savedCost = $savedTimeHours * 0.05;
        
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

    protected function getWeekComparison($rawData)
    {
        $weeks = [];
        $currentDate = Carbon::now();
        
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
        
        usort($daily, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });
        
        return $daily;
    }

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
        
        uasort($weeks, function($a, $b) {
            return $b['year'] . str_pad($b['number'], 2, '0', STR_PAD_LEFT) <=> 
                   $a['year'] . str_pad($a['number'], 2, '0', STR_PAD_LEFT);
        });
        
        return array_values($weeks);
    }

    protected function getTopSlowQueries($weekAnalysis)
    {
        $slowQueries = $weekAnalysis['slow_queries_analysis'] ?? [];
        
        uasort($slowQueries, function($a, $b) {
            return $b['total_time'] <=> $a['total_time'];
        });
        
        return array_slice($slowQueries, 0, 10, true);
    }

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
        
        foreach ($performance as $day => &$data) {
            if ($data['count'] > 0) {
                $data['avg_response_time'] = round($data['avg_response_time'] / $data['count'], 2);
                $data['avg_cache_efficiency'] = round($data['avg_cache_efficiency'] / $data['count'], 2);
            }
        }
        
        return $performance;
    }
    /**
 * Start manual security scan
 */
public function startManualScan(Request $request)
{
    if (!Session::has('server_status_authenticated')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $sessionId = Session::getId();
    
    // Clear previous scan results
    Cache::forget('security_scan_results_' . $sessionId);
    Cache::forget('security_scan_progress_' . $sessionId);
    
    // Start scan (using sync dispatch for immediate execution)
    dispatch(function () use ($sessionId) {
        $scanner = new \App\Helpers\SecurityScanner();
        $scanner->scanProject($sessionId);
    })->onConnection('sync');
    
    return response()->json([
        'success' => true,
        'message' => 'Scan started',
        'session_id' => $sessionId
    ]);
}
/**
 * Get scan progress
 */
public function getScanProgress(Request $request)
{
    if (!Session::has('server_status_authenticated')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        $sessionId = Session::getId();
        $scanner = new \App\Helpers\SecurityScanner();
        $progress = $scanner->getProgress($sessionId);
        $results = $scanner->getResults($sessionId);
        
        // Ensure all strings are UTF-8 encoded
        $progress = $this->ensureUtf8($progress);
        if ($results) {
            $results = $this->ensureUtf8($results);
        }
        
        return response()->json([
            'progress' => $progress,
            'results' => $results,
            'completed' => $results ? true : false
        ]);
    } catch (\Exception $e) {
        Log::error('Error in getScanProgress: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to get scan progress',
            'progress' => [
                'progress' => 0,
                'current_file' => 'Error',
                'scanned_files' => 0,
                'total_files' => 0,
                'estimated_time_remaining' => 0,
                'suspicious_found' => 0,
                'modified_found' => 0,
            ],
            'completed' => false
        ]);
    }
}

/**
 * Ensure all strings in array are UTF-8 encoded
 */
protected function ensureUtf8($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = $this->ensureUtf8($value);
        }
        return $data;
    }
    
    if (is_string($data)) {
        // Check if string is valid UTF-8
        if (!mb_check_encoding($data, 'UTF-8')) {
            // Try to convert to UTF-8
            $data = mb_convert_encoding($data, 'UTF-8', 'auto');
        }
        // Remove any invalid sequences
        $data = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $data);
    }
    
    return $data;
}

/**
 * Cancel scan
 */
public function cancelScan(Request $request)
{
    if (!Session::has('server_status_authenticated')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $sessionId = Session::getId();
    Cache::forget('security_scan_progress_' . $sessionId);
    Cache::forget('security_scan_results_' . $sessionId);
    
    return response()->json(['success' => true]);
}
}