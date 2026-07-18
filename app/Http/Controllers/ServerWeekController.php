<?php
// app/Http/Controllers/ServerWeekController.php

namespace App\Http\Controllers;

use App\Services\RedisPerformanceStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ServerWeekController extends Controller
{
    protected $stats;
    
    public function __construct(RedisPerformanceStats $stats)
    {
        $this->stats = $stats;
    }

    /**
     * Get weekly analysis data for AJAX calls
     * Used by: server-status/week/data route
     */
    public function index(Request $request)
    {
        // Check if authenticated
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get week and year from request, default to current week
        $selectedWeek = $request->get('week', Carbon::now()->weekOfYear);
        $selectedYear = $request->get('year', Carbon::now()->year);
        
        // Get week analysis from Redis
        $weekAnalysis = $this->stats->getWeekAnalysis($selectedWeek, $selectedYear);
        $availableWeeks = $this->stats->getAvailableWeeks();
        $slowQueries = $this->stats->getTodaySlowQueries(20);
        
        // Calculate week comparison (current vs previous week)
        $weekComparison = $this->getWeekComparison($weekAnalysis);
        
        // Get performance trend for last 8 weeks
        $performanceTrend = $this->getPerformanceTrend();
        
        // Format daily breakdown for charts
        $dailyBreakdown = $this->formatDailyBreakdown($weekAnalysis);
        
        // Calculate performance by day of week
        $performanceByDay = $this->getPerformanceByDay($weekAnalysis);
        
        // Calculate cache impact
        $totalRequests = $weekAnalysis['total_requests'] ?? 0;
        $cacheHits = $weekAnalysis['total_cache_hits'] ?? 0;
        $cacheEfficiency = $weekAnalysis['cache_efficiency'] ?? 0;
        
        // Estimate saved time (50ms per cache hit)
        $savedTime = $cacheHits * 50; // milliseconds
        $savedTimeSeconds = round($savedTime / 1000, 2);
        
        // Estimate saved cost (assuming $0.05 per hour of compute time)
        $savedHours = $savedTime / (1000 * 3600);
        $savedCostUSD = round($savedHours * 0.05, 4);
        $savedCostBDT = round($savedCostUSD * 120, 2);
        
        // Get top slow queries for the week
        $topSlowQueries = $this->getTopSlowQueries($weekAnalysis);
        
        return response()->json([
            'success' => true,
            'week_analysis' => $weekAnalysis,
            'week_comparison' => $weekComparison,
            'performance_trend' => $performanceTrend,
            'daily_breakdown' => $dailyBreakdown,
            'performance_by_day' => $performanceByDay,
            'available_weeks' => $availableWeeks,
            'selected_week' => $selectedWeek,
            'selected_year' => $selectedYear,
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'cache_efficiency' => $cacheEfficiency,
            'saved_time_ms' => $savedTime,
            'saved_time_seconds' => $savedTimeSeconds,
            'saved_cost_usd' => $savedCostUSD,
            'saved_cost_bdt' => $savedCostBDT,
            'top_slow_queries' => $topSlowQueries,
            'slow_queries' => $slowQueries,
            'performance_rating' => $weekAnalysis['performance_rating'] ?? [
                'overall' => 'Unknown',
                'score' => 0
            ],
            'peak_day' => $this->getPeakDay($weekAnalysis),
            'average_response_time' => $weekAnalysis['avg_response_time'] ?? 0,
            'total_queries' => $weekAnalysis['total_queries'] ?? 0,
            'total_slow_queries' => $weekAnalysis['total_slow_queries'] ?? 0
        ]);
    }

    /**
     * Export week analysis
     * Used by: server-status/week/export route
     */
    public function export(Request $request)
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $week = $request->get('week', Carbon::now()->weekOfYear);
        $year = $request->get('year', Carbon::now()->year);
        $format = $request->get('format', 'json');
        
        $weekAnalysis = $this->stats->getWeekAnalysis($week, $year);
        
        if ($format === 'csv') {
            return $this->exportAsCsv($weekAnalysis, $week, $year);
        }
        
        // Default JSON export
        return response()->json([
            'exported_at' => now()->toIso8601String(),
            'week' => $week,
            'year' => $year,
            'analysis' => $weekAnalysis,
            'generated_by' => 'ServerWeekController'
        ], 200, [
            'Content-Disposition' => 'attachment; filename="week-' . $week . '-' . $year . '-analysis.json"',
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Export as CSV
     */
    protected function exportAsCsv($weekAnalysis, $week, $year)
    {
        $filename = "week-{$week}-{$year}-analysis.csv";
        
        $handle = fopen('php://temp', 'w+');
        
        // Add metadata
        fputcsv($handle, ['Week', $week]);
        fputcsv($handle, ['Year', $year]);
        fputcsv($handle, ['Period', $weekAnalysis['start_date'] ?? '', 'to', $weekAnalysis['end_date'] ?? '']);
        fputcsv($handle, []);
        
        // Summary
        fputcsv($handle, ['SUMMARY']);
        fputcsv($handle, ['Total Requests', $weekAnalysis['total_requests'] ?? 0]);
        fputcsv($handle, ['Total Cache Hits', $weekAnalysis['total_cache_hits'] ?? 0]);
        fputcsv($handle, ['Total Cache Misses', $weekAnalysis['total_cache_misses'] ?? 0]);
        fputcsv($handle, ['Cache Efficiency', ($weekAnalysis['cache_efficiency'] ?? 0) . '%']);
        fputcsv($handle, ['Avg Response Time', ($weekAnalysis['avg_response_time'] ?? 0) . 'ms']);
        fputcsv($handle, ['Total Queries', $weekAnalysis['total_queries'] ?? 0]);
        fputcsv($handle, ['Slow Queries', $weekAnalysis['total_slow_queries'] ?? 0]);
        fputcsv($handle, []);
        
        // Daily breakdown
        fputcsv($handle, ['DAILY BREAKDOWN']);
        fputcsv($handle, ['Date', 'Day', 'Requests', 'Cache Hits', 'Cache Misses', 'Cache Efficiency', 'Avg Response', 'Slow Queries']);
        
        foreach ($weekAnalysis['daily'] ?? [] as $date => $day) {
            fputcsv($handle, [
                $date,
                $day['day_name'] ?? '',
                $day['requests'] ?? 0,
                $day['cache_hits'] ?? 0,
                $day['cache_misses'] ?? 0,
                ($day['cache_efficiency'] ?? 0) . '%',
                ($day['avg_response_time'] ?? 0) . 'ms',
                $day['slow_queries'] ?? 0
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Compare two weeks
     * Used by: server-status/week/compare route
     */
    public function compare(Request $request)
    {
        if (!Session::has('server_status_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $week1 = $request->get('week1', Carbon::now()->weekOfYear);
        $year1 = $request->get('year1', Carbon::now()->year);
        $week2 = $request->get('week2', Carbon::now()->subWeek()->weekOfYear);
        $year2 = $request->get('year2', Carbon::now()->subWeek()->year);
        
        $analysis1 = $this->stats->getWeekAnalysis($week1, $year1);
        $analysis2 = $this->stats->getWeekAnalysis($week2, $year2);
        
        $comparison = [
            'requests' => [
                'week1' => $analysis1['total_requests'] ?? 0,
                'week2' => $analysis2['total_requests'] ?? 0,
                'change' => $this->calculateChange(
                    $analysis1['total_requests'] ?? 0,
                    $analysis2['total_requests'] ?? 0
                )
            ],
            'cache_efficiency' => [
                'week1' => round($analysis1['cache_efficiency'] ?? 0, 2),
                'week2' => round($analysis2['cache_efficiency'] ?? 0, 2),
                'change' => round(
                    ($analysis1['cache_efficiency'] ?? 0) - ($analysis2['cache_efficiency'] ?? 0),
                    2
                )
            ],
            'avg_response_time' => [
                'week1' => round($analysis1['avg_response_time'] ?? 0, 2),
                'week2' => round($analysis2['avg_response_time'] ?? 0, 2),
                'change' => round(
                    ($analysis1['avg_response_time'] ?? 0) - ($analysis2['avg_response_time'] ?? 0),
                    2
                )
            ],
            'slow_queries' => [
                'week1' => $analysis1['total_slow_queries'] ?? 0,
                'week2' => $analysis2['total_slow_queries'] ?? 0,
                'change' => $this->calculateChange(
                    $analysis1['total_slow_queries'] ?? 0,
                    $analysis2['total_slow_queries'] ?? 0
                )
            ]
        ];
        
        return response()->json([
            'success' => true,
            'week1' => [
                'week' => $week1,
                'year' => $year1,
                'data' => $analysis1
            ],
            'week2' => [
                'week' => $week2,
                'year' => $year2,
                'data' => $analysis2
            ],
            'comparison' => $comparison
        ]);
    }

    /**
     * Get week comparison (current vs previous week)
     */
    protected function getWeekComparison($currentWeek)
    {
        if (empty($currentWeek) || !isset($currentWeek['week_number'])) {
            return [
                'has_data' => false,
                'changes' => []
            ];
        }

        // Calculate previous week
        $prevWeekNumber = $currentWeek['week_number'] - 1;
        $prevYear = $currentWeek['year'];
        
        if ($prevWeekNumber < 1) {
            $prevWeekNumber = 52; // Assuming 52 weeks in year
            $prevYear--;
        }
        
        // Get previous week data
        $previousWeek = $this->stats->getWeekAnalysis($prevWeekNumber, $prevYear);
        
        if (empty($previousWeek) || ($previousWeek['total_requests'] ?? 0) == 0) {
            return [
                'has_data' => false,
                'changes' => []
            ];
        }
        
        // Calculate changes
        $changes = [
            'requests' => [
                'current' => $currentWeek['total_requests'] ?? 0,
                'previous' => $previousWeek['total_requests'] ?? 0,
                'change' => $this->calculateChange(
                    $currentWeek['total_requests'] ?? 0,
                    $previousWeek['total_requests'] ?? 0
                ),
                'trend' => $this->getTrend(
                    $currentWeek['total_requests'] ?? 0,
                    $previousWeek['total_requests'] ?? 0,
                    true // Higher is better
                )
            ],
            'cache_efficiency' => [
                'current' => round($currentWeek['cache_efficiency'] ?? 0, 2),
                'previous' => round($previousWeek['cache_efficiency'] ?? 0, 2),
                'change' => round(
                    ($currentWeek['cache_efficiency'] ?? 0) - ($previousWeek['cache_efficiency'] ?? 0),
                    2
                ),
                'trend' => $this->getTrend(
                    $currentWeek['cache_efficiency'] ?? 0,
                    $previousWeek['cache_efficiency'] ?? 0,
                    true // Higher is better
                )
            ],
            'avg_response_time' => [
                'current' => round($currentWeek['avg_response_time'] ?? 0, 2),
                'previous' => round($previousWeek['avg_response_time'] ?? 0, 2),
                'change' => round(
                    ($currentWeek['avg_response_time'] ?? 0) - ($previousWeek['avg_response_time'] ?? 0),
                    2
                ),
                'trend' => $this->getTrend(
                    $currentWeek['avg_response_time'] ?? 0,
                    $previousWeek['avg_response_time'] ?? 0,
                    false // Lower is better
                )
            ],
            'slow_queries' => [
                'current' => $currentWeek['total_slow_queries'] ?? 0,
                'previous' => $previousWeek['total_slow_queries'] ?? 0,
                'change' => $this->calculateChange(
                    $currentWeek['total_slow_queries'] ?? 0,
                    $previousWeek['total_slow_queries'] ?? 0
                ),
                'trend' => $this->getTrend(
                    $currentWeek['total_slow_queries'] ?? 0,
                    $previousWeek['total_slow_queries'] ?? 0,
                    false // Lower is better
                )
            ],
            'cache_hits' => [
                'current' => $currentWeek['total_cache_hits'] ?? 0,
                'previous' => $previousWeek['total_cache_hits'] ?? 0,
                'change' => $this->calculateChange(
                    $currentWeek['total_cache_hits'] ?? 0,
                    $previousWeek['total_cache_hits'] ?? 0
                ),
                'trend' => $this->getTrend(
                    $currentWeek['total_cache_hits'] ?? 0,
                    $previousWeek['total_cache_hits'] ?? 0,
                    true // Higher is better
                )
            ]
        ];
        
        return [
            'has_data' => true,
            'current_week' => [
                'number' => $currentWeek['week_number'],
                'year' => $currentWeek['year'],
                'start_date' => $currentWeek['start_date'] ?? null,
                'end_date' => $currentWeek['end_date'] ?? null
            ],
            'previous_week' => [
                'number' => $previousWeek['week_number'],
                'year' => $previousWeek['year'],
                'start_date' => $previousWeek['start_date'] ?? null,
                'end_date' => $previousWeek['end_date'] ?? null
            ],
            'changes' => $changes
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get trend direction
     */
    protected function getTrend($current, $previous, $higherIsBetter = true)
    {
        if ($current > $previous) {
            return $higherIsBetter ? 'up' : 'down';
        } elseif ($current < $previous) {
            return $higherIsBetter ? 'down' : 'up';
        }
        return 'stable';
    }

    /**
     * Get performance trend for last 8 weeks
     */
    protected function getPerformanceTrend()
    {
        $trend = [];
        $now = Carbon::now();
        
        for ($i = 7; $i >= 0; $i--) {
            $weekNumber = $now->copy()->subWeeks($i)->weekOfYear;
            $year = $now->copy()->subWeeks($i)->year;
            $weekData = $this->stats->getWeekAnalysis($weekNumber, $year);
            
            if (!empty($weekData) && ($weekData['total_requests'] ?? 0) > 0) {
                $trend[] = [
                    'week' => "Week {$weekNumber}",
                    'week_number' => $weekNumber,
                    'year' => $year,
                    'label' => $this->getWeekLabel($weekNumber, $year),
                    'requests' => $weekData['total_requests'] ?? 0,
                    'cache_efficiency' => round($weekData['cache_efficiency'] ?? 0, 2),
                    'avg_response_time' => round($weekData['avg_response_time'] ?? 0, 2),
                    'performance_score' => $weekData['performance_rating']['score'] ?? 0,
                    'total_queries' => $weekData['total_queries'] ?? 0,
                    'slow_queries' => $weekData['total_slow_queries'] ?? 0
                ];
            }
        }
        
        return $trend;
    }

    /**
     * Get week label
     */
    protected function getWeekLabel($weekNumber, $year)
    {
        try {
            $startDate = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek();
            $endDate = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek();
            return $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
        } catch (\Exception $e) {
            return "Week {$weekNumber}, {$year}";
        }
    }

    /**
     * Format daily breakdown for charts
     */
    protected function formatDailyBreakdown($weekAnalysis)
    {
        $daily = [];
        
        if (empty($weekAnalysis['daily'])) {
            return $daily;
        }
        
        foreach ($weekAnalysis['daily'] as $date => $dayData) {
            $carbonDate = Carbon::parse($date);
            
            $daily[] = [
                'date' => $date,
                'day' => $carbonDate->format('D'),
                'day_full' => $carbonDate->format('l'),
                'day_name' => $carbonDate->format('l'),
                'day_number' => $carbonDate->format('d'),
                'month' => $carbonDate->format('M'),
                'requests' => $dayData['requests'] ?? 0,
                'cache_efficiency' => $dayData['cache_efficiency'] ?? 0,
                'avg_response_time' => $dayData['avg_response_time'] ?? 0,
                'slow_queries' => $dayData['slow_queries'] ?? 0,
                'cache_hits' => $dayData['cache_hits'] ?? 0,
                'cache_misses' => $dayData['cache_misses'] ?? 0,
                'total_queries' => $dayData['total_queries'] ?? 0,
                'performance_score' => $this->calculateDailyScore($dayData)
            ];
        }
        
        // Sort by date
        usort($daily, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });
        
        return $daily;
    }

    /**
     * Calculate daily performance score
     */
    protected function calculateDailyScore($dayData)
    {
        $cacheScore = min(100, ($dayData['cache_efficiency'] ?? 0) * 1);
        $responseScore = max(0, 100 - (($dayData['avg_response_time'] ?? 0) / 10));
        $queryScore = max(0, 100 - (($dayData['slow_queries'] ?? 0) * 2));
        
        return round(($cacheScore * 0.4) + ($responseScore * 0.3) + ($queryScore * 0.3));
    }

    /**
     * Get performance by day of week
     */
    protected function getPerformanceByDay($weekAnalysis)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $performance = [];
        
        // Initialize
        foreach ($days as $day) {
            $performance[$day] = [
                'avg_response_time' => 0,
                'avg_cache_efficiency' => 0,
                'total_requests' => 0,
                'total_slow_queries' => 0,
                'count' => 0,
                'best_time' => PHP_FLOAT_MAX,
                'worst_time' => 0
            ];
        }
        
        // Aggregate
        foreach ($weekAnalysis['daily'] ?? [] as $date => $dayData) {
            $carbonDate = Carbon::parse($date);
            $dayName = $carbonDate->englishDayOfWeek;
            
            if (isset($performance[$dayName])) {
                $performance[$dayName]['avg_response_time'] += $dayData['avg_response_time'] ?? 0;
                $performance[$dayName]['avg_cache_efficiency'] += $dayData['cache_efficiency'] ?? 0;
                $performance[$dayName]['total_requests'] += $dayData['requests'] ?? 0;
                $performance[$dayName]['total_slow_queries'] += $dayData['slow_queries'] ?? 0;
                $performance[$dayName]['count']++;
                
                // Track best/worst
                $responseTime = $dayData['avg_response_time'] ?? 0;
                if ($responseTime > 0) {
                    $performance[$dayName]['best_time'] = min($performance[$dayName]['best_time'], $responseTime);
                    $performance[$dayName]['worst_time'] = max($performance[$dayName]['worst_time'], $responseTime);
                }
            }
        }
        
        // Calculate averages
        foreach ($performance as $day => &$data) {
            if ($data['count'] > 0) {
                $data['avg_response_time'] = round($data['avg_response_time'] / $data['count'], 2);
                $data['avg_cache_efficiency'] = round($data['avg_cache_efficiency'] / $data['count'], 2);
                $data['avg_requests'] = round($data['total_requests'] / $data['count'], 2);
                $data['avg_slow_queries'] = round($data['total_slow_queries'] / $data['count'], 2);
            } else {
                $data['avg_response_time'] = 0;
                $data['avg_cache_efficiency'] = 0;
                $data['avg_requests'] = 0;
                $data['avg_slow_queries'] = 0;
                $data['best_time'] = 0;
                $data['worst_time'] = 0;
            }
            
            // Remove count from final output
            unset($data['count']);
        }
        
        return $performance;
    }

    /**
     * Get top slow queries for the week
     */
    protected function getTopSlowQueries($weekAnalysis)
    {
        $slowQueries = [];
        
        // Get slow queries from Redis for each day of the week
        if (!empty($weekAnalysis['daily'])) {
            foreach (array_keys($weekAnalysis['daily']) as $date) {
                $daySlowQueries = $this->stats->getTodaySlowQueries(5); // Get top 5 per day
                $slowQueries = array_merge($slowQueries, $daySlowQueries);
            }
        }
        
        // Sort by time (descending) and take top 10
        usort($slowQueries, function($a, $b) {
            return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
        });
        
        return array_slice($slowQueries, 0, 10);
    }

    /**
     * Get peak day of the week
     */
    protected function getPeakDay($weekAnalysis)
    {
        if (empty($weekAnalysis['daily'])) {
            return null;
        }
        
        $peakDay = null;
        $maxRequests = 0;
        
        foreach ($weekAnalysis['daily'] as $date => $dayData) {
            $requests = $dayData['requests'] ?? 0;
            if ($requests > $maxRequests) {
                $maxRequests = $requests;
                $peakDay = [
                    'date' => $date,
                    'day' => Carbon::parse($date)->format('l'),
                    'requests' => $requests,
                    'cache_efficiency' => $dayData['cache_efficiency'] ?? 0,
                    'avg_response_time' => $dayData['avg_response_time'] ?? 0
                ];
            }
        }
        
        return $peakDay;
    }
}