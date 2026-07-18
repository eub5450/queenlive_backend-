<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityScanner
{
    protected $suspiciousPatterns = [
        'eval\s*\(',
        'base64_decode\s*\(',
        'gzinflate\s*\(',
        'str_rot13\s*\(',
        'system\s*\(',
        'exec\s*\(',
        'shell_exec\s*\(',
        'passthru\s*\(',
        'popen\s*\(',
        'proc_open\s*\(',
        'pcntl_exec\s*\(',
        'file_get_contents\s*\(\s*["\'](http|https|ftp)://',
        'curl_exec\s*\(',
        'fsockopen\s*\(',
        'pfsockopen\s*\(',
        'move_uploaded_file\s*\(',
        'copy\s*\(',
        'fopen\s*\(',
        'assert\s*\(',
        'create_function\s*\(',
        'preg_replace\s*\(\s*["\'].*[\/e].*["\']',
        '`.*`',
        '\$\{.*\}',
        '\b(?:GLOBALS|_POST|_GET|_REQUEST|_COOKIE|_SESSION)\s*\[',
        'mysql_query\s*\(',
        'mysqli_query\s*\(',
        'pg_query\s*\(',
        'unlink\s*\(',
        'rmdir\s*\(',
        'chmod\s*\(',
        'chown\s*\(',
    ];

    protected $laravelSpecificPatterns = [
        'DB::(unprepared|statement)\s*\(\s*\$',
        '\\\\DB::(unprepared|statement)\s*\(\s*\$',
        'Schema::(.*?)->(drop|delete)',
        'Artisan::call\s*\(\s*[\'"]db:wipe',
        'app[\'"]\s*\[\s*[\'"]db[\'"]\s*\]\s*->(unprepared|statement)',
    ];

    protected $maliciousFiles = [
        'shell.php', 'cmd.php', 'backdoor.php', 'webshell.php',
        'c99.php', 'r57.php', 'b374k.php', 'shells.php',
        'wso.php', 'alfan.php', 'bypass.php', 'symlink.php',
        'config.php.bak', '.env.backup', '.env.save', 'dump.sql'
    ];

    /**
     * Scan entire Laravel project for suspicious code with progress tracking
     */
    public function scanProject($sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();
        
        $results = [
            'scanned_files' => 0,
            'total_files' => 0,
            'suspicious_files' => [],
            'infected_files' => [],
            'modified_files' => [],
            'malicious_files' => [],
            'permission_issues' => [],
            'suspicious_logs' => [],
            'scanned_at' => Carbon::now()->toDateTimeString(),
            'scan_duration' => 0,
            'current_file' => '',
            'progress' => 0,
            'estimated_time_remaining' => 0,
            'risk_level' => 'CLEAN',
        ];

        $startTime = microtime(true);
        
        // Get all files first for progress tracking
        $allFiles = $this->getAllFiles();
        $results['total_files'] = count($allFiles);
        
        // Update progress in cache
        $this->updateProgress($sessionId, $results);
        
        $scannedCount = 0;
        foreach ($allFiles as $file) {
            $scannedCount++;
            
            // Update current file and progress
            $results['current_file'] = $this->getRelativePath($file);
            $results['progress'] = round(($scannedCount / $results['total_files']) * 100, 2);
            
            // Calculate estimated time remaining
            $elapsed = microtime(true) - $startTime;
            $avgTimePerFile = $scannedCount > 0 ? $elapsed / $scannedCount : 0;
            $remainingFiles = $results['total_files'] - $scannedCount;
            $results['estimated_time_remaining'] = round($avgTimePerFile * $remainingFiles);
            
            // Update progress every 10 files or every 2%
            if ($scannedCount % 10 == 0 || $results['progress'] - (int)$results['progress'] < 0.01) {
                $this->updateProgress($sessionId, $results);
            }
            
            // Skip large files and binary files
            if ($file->getSize() > 5 * 1024 * 1024 || $this->isBinaryFile($file)) {
                $results['scanned_files']++;
                continue;
            }

            try {
                // Read file content with proper encoding handling
                $content = $this->readFileContent($file->getPathname());
                if ($content === false) {
                    $results['scanned_files']++;
                    continue;
                }
                
                $suspicious = [];
                $matches = [];

                // Check for suspicious patterns
                foreach ($this->suspiciousPatterns as $pattern) {
                    if (@preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $content, $match)) {
                        $suspicious[] = $pattern;
                        $matches[$pattern] = $this->sanitizeText($match[0] ?? '');
                    }
                }

                // Check for Laravel specific patterns
                foreach ($this->laravelSpecificPatterns as $pattern) {
                    if (@preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $content, $match)) {
                        $suspicious[] = $pattern;
                        $matches[$pattern] = $this->sanitizeText($match[0] ?? '');
                    }
                }

                if (!empty($suspicious)) {
                    $results['suspicious_files'][] = [
                        'path' => $this->getRelativePath($file),
                        'patterns' => $suspicious,
                        'matches' => $matches,
                        'last_modified' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
                        'size' => $this->formatBytes($file->getSize()),
                    ];
                }

                // Check for recent modifications (last 24 hours)
                if ($file->getMTime() > (time() - 86400)) {
                    $relativePath = $this->getRelativePath($file);
                    $existingPaths = array_column($results['modified_files'], 'path');
                    if (!in_array($relativePath, $existingPaths)) {
                        $results['modified_files'][] = [
                            'path' => $relativePath,
                            'modified_at' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
                            'size' => $this->formatBytes($file->getSize()),
                        ];
                    }
                }
                
                $results['scanned_files']++;
            } catch (\Exception $e) {
                Log::error('Error scanning file: ' . $file->getPathname() . ' - ' . $e->getMessage());
                $results['scanned_files']++;
            }
        }
        
        // Scan for malicious file names
        $this->scanForMaliciousFiles($results);

        // Check file permissions
        $this->checkFilePermissions($results);

        // Check Laravel logs for suspicious activities
        $this->scanLogs($results);

        $results['scan_duration'] = round(microtime(true) - $startTime, 2);
        $results['risk_level'] = $this->calculateRiskLevel($results);
        $results['completed_at'] = Carbon::now()->toDateTimeString();
        $results['progress'] = 100;
        $results['current_file'] = 'Scan completed';
        
        // Clean any potentially invalid UTF-8 characters from results
        $results = $this->cleanResultsForJson($results);
        
        // Store final results
        Cache::put('security_scan_results_' . $sessionId, $results, now()->addHours(1));
        Cache::put('security_scan_progress_' . $sessionId, $results, now()->addHours(1));
        
        return $results;
    }

    /**
     * Read file content with proper encoding handling
     */
    protected function readFileContent($filePath)
    {
        try {
            $content = File::get($filePath);
            
            // Check if content is valid UTF-8
            if (!mb_check_encoding($content, 'UTF-8')) {
                // Try to convert to UTF-8
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            }
            
            // Remove any invalid UTF-8 sequences
            $content = $this->sanitizeText($content);
            
            return $content;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sanitize text to ensure valid UTF-8
     */
    protected function sanitizeText($text)
    {
        if (empty($text)) {
            return '';
        }
        
        // Remove non-UTF8 characters
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Replace any remaining invalid sequences
        $text = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $text);
        
        // Trim and normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Clean results array for JSON encoding
     */
    protected function cleanResultsForJson($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanResultsForJson($value);
            }
        } elseif (is_string($data)) {
            $data = $this->sanitizeText($data);
        }
        
        return $data;
    }

    /**
     * Get all files to scan
     */
    protected function getAllFiles()
    {
        $files = [];
        $pathsToScan = [
            base_path('app'),
            base_path('bootstrap'),
            base_path('config'),
            base_path('database'),
            base_path('public'),
            base_path('resources'),
            base_path('routes'),
            base_path('storage/framework/views'),
        ];

        foreach ($pathsToScan as $path) {
            if (File::exists($path)) {
                try {
                    $pathFiles = File::allFiles($path);
                    $files = array_merge($files, $pathFiles);
                } catch (\Exception $e) {
                    Log::warning('Could not scan directory: ' . $path);
                }
            }
        }
        
        // Also scan root directory for specific files
        try {
            $rootFiles = File::files(base_path());
            foreach ($rootFiles as $file) {
                if (in_array($file->getExtension(), ['php', 'env', 'txt', 'log', 'json', 'yml', 'yaml', 'xml'])) {
                    $files[] = $file;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not scan root directory');
        }
        
        return $files;
    }

    /**
     * Get relative path from base path
     */
    protected function getRelativePath($file)
    {
        return str_replace(base_path() . '/', '', $file->getPathname());
    }

    /**
     * Update progress in cache
     */
    protected function updateProgress($sessionId, $results)
    {
        $progressData = [
            'progress' => $results['progress'],
            'current_file' => $this->sanitizeText($results['current_file']),
            'scanned_files' => $results['scanned_files'],
            'total_files' => $results['total_files'],
            'estimated_time_remaining' => $results['estimated_time_remaining'] ?? 0,
            'suspicious_found' => count($results['suspicious_files']),
            'modified_found' => count($results['modified_files']),
        ];
        
        Cache::put('security_scan_progress_' . $sessionId, $progressData, now()->addMinutes(5));
    }

    /**
     * Get scan progress
     */
    public function getProgress($sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();
        return Cache::get('security_scan_progress_' . $sessionId, [
            'progress' => 0,
            'current_file' => 'Not started',
            'scanned_files' => 0,
            'total_files' => 0,
            'estimated_time_remaining' => 0,
            'suspicious_found' => 0,
            'modified_found' => 0,
        ]);
    }

    /**
     * Get scan results
     */
    public function getResults($sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();
        return Cache::get('security_scan_results_' . $sessionId);
    }

    /**
     * Scan for malicious file names
     */
    protected function scanForMaliciousFiles(&$results)
    {
        foreach ($this->maliciousFiles as $maliciousFile) {
            // Search in root directory
            if (File::exists(base_path($maliciousFile))) {
                $results['malicious_files'][] = [
                    'path' => $maliciousFile,
                    'name' => $maliciousFile,
                    'found_at' => Carbon::now()->toDateTimeString(),
                ];
            }
            
            // Search in public directory
            if (File::exists(public_path($maliciousFile))) {
                $results['malicious_files'][] = [
                    'path' => 'public/' . $maliciousFile,
                    'name' => $maliciousFile,
                    'found_at' => Carbon::now()->toDateTimeString(),
                ];
            }
        }
    }

    /**
     * Check file permissions
     */
    protected function checkFilePermissions(&$results)
    {
        $criticalFiles = [
            '.env' => '600',
            'public/index.php' => '644',
            'artisan' => '755',
            'bootstrap/app.php' => '644',
        ];

        foreach ($criticalFiles as $file => $expectedPerm) {
            $filePath = base_path($file);
            if (File::exists($filePath)) {
                $perms = substr(sprintf('%o', fileperms($filePath)), -4);
                if ($perms != $expectedPerm) {
                    $results['permission_issues'][] = [
                        'file' => $file,
                        'current' => $perms,
                        'expected' => $expectedPerm,
                    ];
                }
            }
        }
    }

    /**
     * Scan Laravel logs for suspicious activities
     */
    protected function scanLogs(&$results)
    {
        $logPath = storage_path('logs');
        if (!File::exists($logPath)) return;

        try {
            $logs = File::allFiles($logPath);
            $suspiciousLogPatterns = [
                'Failed login attempt',
                'Invalid password',
                'Unauthorized access',
                'suspicious',
                'hack',
                'attack',
                'brute force',
                'sql injection',
                'xss',
            ];

            foreach ($logs as $log) {
                if ($log->getSize() > 10 * 1024 * 1024) continue; // Skip large logs

                try {
                    $content = $this->readFileContent($log->getPathname());
                    if ($content === false) continue;
                    
                    foreach ($suspiciousLogPatterns as $pattern) {
                        if (stripos($content, $pattern) !== false) {
                            $results['suspicious_logs'][] = [
                                'file' => $this->getRelativePath($log),
                                'pattern' => $pattern,
                                'last_modified' => Carbon::createFromTimestamp($log->getMTime())->toDateTimeString(),
                            ];
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // Skip if can't read log
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not scan logs directory');
        }
    }

    /**
     * Check if file is binary
     */
    protected function isBinaryFile($file)
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'pdf', 'zip', 'tar', 'gz', 'mp3', 'mp4', 'woff', 'woff2', 'ttf', 'eot', 'svg', 'webp', 'ico', 'css', 'js', 'map'];
        return in_array(strtolower($file->getExtension()), $extensions);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Calculate risk level based on findings
     */
    protected function calculateRiskLevel($results)
    {
        $score = 0;
        
        if (!empty($results['malicious_files'])) $score += 100;
        if (!empty($results['suspicious_files'])) $score += count($results['suspicious_files']) * 10;
        if (!empty($results['suspicious_logs'])) $score += count($results['suspicious_logs']) * 5;
        if (!empty($results['permission_issues'])) $score += count($results['permission_issues']) * 3;
        if (count($results['modified_files']) > 10) $score += 20;

        if ($score >= 50) return 'CRITICAL';
        if ($score >= 30) return 'HIGH';
        if ($score >= 15) return 'MEDIUM';
        if ($score > 0) return 'LOW';
        return 'CLEAN';
    }
}