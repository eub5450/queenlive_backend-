<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BackupController extends Controller
{
    private $secretToken = 'e4f1c2d3b4a59687a1b2c3d4e5f67890'; // CHANGE THIS!
    private $backupPath;
    private $maxDatabaseBackups = 4;
    private $maxFileBackups = 4;

    public function __construct()
    {
        $this->backupPath = storage_path('backup');
    }

    /**
     * Run database backup (every 12 hours)
     */
    public function databaseBackup(Request $request)
    {
        // SECURITY: Check token
        if (!$this->validateRequest($request)) {
            Log::warning('Unauthorized database backup attempt from IP: ' . $request->ip());
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // SECURITY: Check IP
        // if (!$this->isAllowedIp($request->ip())) {
        //     Log::warning('Blocked IP attempted backup: ' . $request->ip());
        //     return response()->json(['error' => 'IP not allowed'], 403);
        // }

        // Rate limiting
        $key = 'backup_database_' . date('Y-m-d-H');
        if (Cache::has($key)) {
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }
        Cache::put($key, true, 3600);

        Log::info('Database backup started via web');

        try {
            $result = $this->executeDatabaseBackup();
            
            if ($result['success']) {
                $this->rotateBackups('database', $this->maxDatabaseBackups);
                $this->sendBackupEmail('database', $result['filename'], $result['size']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Database backup completed',
                    'filename' => $result['filename'],
                    'size' => $result['size'],
                    'kept' => $this->maxDatabaseBackups,
                    'next_backup' => now()->addHours(12)->format('Y-m-d H:i:s')
                ]);
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            $this->sendFailureEmail('database', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run custom file backup (daily at 1:00 AM)
     * Backs up: config, app, routes, resources, .env, .htaccess
     */
    public function fileBackup(Request $request)
    {
        // SECURITY: Check token
        if (!$this->validateRequest($request)) {
            Log::warning('Unauthorized file backup attempt from IP: ' . $request->ip());
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // SECURITY: Check IP
        // if (!$this->isAllowedIp($request->ip())) {
        //     Log::warning('Blocked IP attempted file backup: ' . $request->ip());
        //     return response()->json(['error' => 'IP not allowed'], 403);
        // }

        // Rate limiting (once per day)
        $key = 'backup_file_' . date('Y-m-d');
        if (Cache::has($key)) {
            return response()->json(['error' => 'Daily backup already done'], 429);
        }
        Cache::put($key, true, 86400);

        Log::info('Custom file backup started via web');

        try {
            $result = $this->executeCustomFileBackup();
            
            if ($result['success']) {
                $this->rotateBackups('files', $this->maxFileBackups);
                $this->sendBackupEmail('file', $result['filename'], $result['size']);
                
                Log::info('Custom file backup completed successfully');
                return response()->json([
                    'success' => true,
                    'message' => 'Custom file backup completed',
                    'filename' => $result['filename'],
                    'size' => $result['size'],
                    'kept' => $this->maxFileBackups,
                    'next_backup' => now()->addDay()->format('Y-m-d 01:00:00'),
                    'included' => [
                        'config/ directory',
                        'app/ directory',
                        'routes/ directory',
                        'resources/ directory',
                        '.env file',
                        '.htaccess file'
                    ]
                ]);
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            Log::error('File backup failed: ' . $e->getMessage());
            $this->sendFailureEmail('file', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute custom file backup (config, app, routes, resources, .env, .htaccess)
     */
    private function executeCustomFileBackup()
    {
        $filePath = $this->backupPath . '/files';
        if (!File::exists($filePath)) {
            File::makeDirectory($filePath, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "queenlive_custom_config_{$timestamp}.tar.gz";
        $fullPath = $filePath . '/' . $filename;
        
        $sourceDir = '/home/bdlive/public_html';

        // Create a temporary file with list of files to backup
        $tempListFile = $filePath . '/backup_list_' . $timestamp . '.txt';
        $fileList = [];

        // Add config directory
        if (File::exists($sourceDir . '/config')) {
            $fileList[] = 'config';
        }

        // Add app directory
        if (File::exists($sourceDir . '/app')) {
            $fileList[] = 'app';
        }

        // Add routes directory
        if (File::exists($sourceDir . '/routes')) {
            $fileList[] = 'routes';
        }

        // Add resources directory
        if (File::exists($sourceDir . '/resources')) {
            $fileList[] = 'resources';
        }

        // Add .env file if exists
        if (File::exists($sourceDir . '/.env')) {
            $fileList[] = '.env';
        }

        // Add .htaccess file if exists
        if (File::exists($sourceDir . '/.htaccess')) {
            $fileList[] = '.htaccess';
        }

        // Write file list to temp file
        File::put($tempListFile, implode("\n", $fileList));

        // Create tar.gz archive with the specified files
        $command = "tar -czf {$fullPath} -C {$sourceDir} -T {$tempListFile} 2>&1";
        
        exec($command, $output, $returnCode);

        // Remove temp file
        if (File::exists($tempListFile)) {
            File::delete($tempListFile);
        }

        if ($returnCode !== 0 || !file_exists($fullPath)) {
            return [
                'success' => false,
                'error' => "Custom file backup failed (code: $returnCode)"
            ];
        }

        if (filesize($fullPath) == 0) {
            File::delete($fullPath);
            return [
                'success' => false,
                'error' => "Backup file is empty - no files found to backup"
            ];
        }

        $size = round(filesize($fullPath) / 1024 / 1024, 2);

        return [
            'success' => true,
            'filename' => $filename,
            'size' => $size . ' MB'
        ];
    }

    /**
     * Execute database backup
     */
    private function executeDatabaseBackup()
    {
        $dbPath = $this->backupPath . '/database';
        if (!File::exists($dbPath)) {
            File::makeDirectory($dbPath, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "queenlive_db_{$timestamp}.sql.gz";
        $fullPath = $dbPath . '/' . $filename;

        // Database credentials
        $database = 'queenlive_database';
        $username = 'queenlive_database';
        $password = 'VXRRMND(oW1I)bN';
        $host = '127.0.0.1';

        $mysqldump = $this->findMysqldump();
        
        $command = "{$mysqldump} --host={$host} --user={$username} --password=" . escapeshellarg($password) . " --opt --routines --events --triggers {$database} | gzip > {$fullPath} 2>&1";
        
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($fullPath)) {
            return [
                'success' => false,
                'error' => "mysqldump failed (code: $returnCode)"
            ];
        }

        $size = round(filesize($fullPath) / 1024 / 1024, 2);

        return [
            'success' => true,
            'filename' => $filename,
            'size' => $size . ' MB'
        ];
    }

    /**
     * Rotate backups - keep only specified number
     */
    private function rotateBackups($type, $keep)
    {
        $path = $this->backupPath . '/' . $type;
        if (!File::exists($path)) return;

        $files = File::files($path);
        $backups = [];

        foreach ($files as $file) {
            $backups[$file->getMTime()] = $file;
        }

        ksort($backups); // Oldest first

        while (count($backups) > $keep) {
            $oldest = array_shift($backups);
            File::delete($oldest);
            Log::info("Removed old {$type} backup: " . $oldest->getFilename());
        }
    }

    /**
     * Send beautiful backup email
     */
    private function sendBackupEmail($type, $filename, $size)
    {
        $domain = 'queenlive.site';
        $downloadLink = "https://{$domain}/storage/backup/{$type}/{$filename}";
        
        $subject = $type === 'database' 
            ? "✅ ডাটাবেজ ব্যাকআপ সফল - {$domain} - " . date('Y-m-d H:i')
            : "✅ কনফিগ ফাইল ব্যাকআপ সফল - {$domain} - " . date('Y-m-d H:i');

        $nextBackup = $type === 'database'
            ? date('Y-m-d H:i:s', strtotime('+12 hours'))
            : date('Y-m-d H:i:s', strtotime('tomorrow 01:00'));

        $includedFiles = $type === 'database' 
            ? 'সম্পূর্ণ ডাটাবেজ'
            : 'config/, app/, routes/, resources/, .env, .htaccess';

        $details = [
            '📁 ফাইলের নাম' => $filename,
            '📊 ফাইল সাইজ' => $size,
            '⏰ ব্যাকআপ সময়' => date('Y-m-d H:i:s'),
            '📦 ব্যাকআপ করা ফাইল' => $includedFiles,
            '🔄 সংরক্ষিত ব্যাকআপ' => 'সর্বশেষ ৪টি',
            '⏱️ পরবর্তী ব্যাকআপ' => $nextBackup,
            '📥 ডাউনলোড লিঙ্ক' => $downloadLink
        ];

        MailHelper::sendBackupNotification($subject, $type, $filename, $size, 'success', null, $details);
    }

    /**
     * Send failure email
     */
    private function sendFailureEmail($type, $error)
    {
        $domain = 'queenlive.site';
        
        $subject = $type === 'database'
            ? "❌ ডাটাবেজ ব্যাকআপ ব্যর্থ - {$domain} - " . date('Y-m-d H:i')
            : "❌ কনফিগ ফাইল ব্যাকআপ ব্যর্থ - {$domain} - " . date('Y-m-d H:i');

        $details = [
            '⏰ সময়' => date('Y-m-d H:i:s'),
            '❌ ত্রুটির বিবরণ' => $error,
            '⚠️ পরবর্তী চেষ্টা' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ];

        MailHelper::sendBackupNotification($subject, $type, 'N/A', '0 MB', 'danger', $error, $details);
    }

    /**
     * Find mysqldump path
     */
    private function findMysqldump()
    {
        $paths = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
        foreach ($paths as $path) {
            $result = shell_exec("which {$path} 2>/dev/null");
            if (!empty($result)) return trim($result);
        }
        return 'mysqldump';
    }

    /**
     * Validate request token
     */
    private function validateRequest(Request $request)
    {
        $token = $request->get('token');
        return $token === $this->secretToken;
    }

    /**
     * Check if IP is allowed
     */
    private function isAllowedIp($ip)
    {
        $allowedIps = [
            '127.0.0.1',
            '::1',
            '103.87.214.16',
            '139.162.49.107', // Your server IP
        ];
        
        return in_array($ip, $allowedIps);
    }

    /**
     * Get backup status
     */
    public function status(Request $request)
    {
        if (!$this->validateRequest($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dbBackups = File::exists($this->backupPath . '/database') 
            ? count(File::files($this->backupPath . '/database')) 
            : 0;
            
        $fileBackups = File::exists($this->backupPath . '/files') 
            ? count(File::files($this->backupPath . '/files')) 
            : 0;

        return response()->json([
            'database_backups' => $dbBackups,
            'file_backups' => $fileBackups,
            'max_database' => $this->maxDatabaseBackups,
            'max_files' => $this->maxFileBackups,
            'next_database' => now()->addHours(12)->format('Y-m-d H:i:s'),
            'next_file' => now()->addDay()->format('Y-m-d 01:00:00'),
            'disk_free' => round(disk_free_space('/home') / 1024 / 1024 / 1024, 2) . ' GB',
            'disk_total' => round(disk_total_space('/home') / 1024 / 1024 / 1024, 2) . ' GB'
        ]);
    }
}