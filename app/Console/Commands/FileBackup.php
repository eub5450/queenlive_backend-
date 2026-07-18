<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Helpers\MailHelper;
use ZipArchive;

class FileBackup extends Command
{
    protected $signature = 'backup:full';
    protected $description = 'Take full system backup (keeps last 8)';

    protected $backupPath;
    protected $logFile;
    protected $maxLogLines = 20;

    public function __construct()
    {
        parent::__construct();
        $this->backupPath = storage_path('backup/file');
        $this->logFile = storage_path('logs/backup.log');
    }

    public function handle()
    {
        $domain = request()->getHost();
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$domain}_full_{$timestamp}.zip";
        $fullPath = $this->backupPath . '/' . $filename;
        $tempDir = storage_path('backup/temp/' . $timestamp);

        $this->info("📦 Starting full backup: {$filename}");
        $this->logInfo("Starting full backup: {$filename}");

        try {
            // Check directories
            if (!File::exists($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
                $this->info("✅ Backup directory created: {$this->backupPath}");
            }
            
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
                $this->info("✅ Temp directory created: {$tempDir}");
            }

            // Backup database
            $this->backupDatabase($tempDir);
            
            // Backup important files
            $this->backupFiles($tempDir);
            
            // Create ZIP
            $this->createZip($tempDir, $fullPath);
            
            // Clean up temp
            File::deleteDirectory($tempDir);

            $size = round(filesize($fullPath) / 1024 / 1024, 2);
            $this->info("✅ Backup created: {$filename} ({$size} MB)");
            $this->logInfo("Backup created: {$filename} ({$size} MB)");

            // Success email
            MailHelper::sendBackupNotification(
                "📦 Full Backup Successful - {$domain} - " . date('Y-m-d H:i'),
                'full',
                $filename,
                $size . ' MB',
                'success'
            );

            // Rotate old backups
            $this->rotateBackups();

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            $this->logError("Full backup failed", $e->getMessage(), $e->getFile(), $e->getLine());

            // Failure email
            MailHelper::sendBackupNotification(
                "❌ Full Backup Failed - {$domain} - " . date('Y-m-d H:i'),
                'full',
                $filename ?? 'unknown',
                '0 MB',
                'danger',
                $e->getMessage()
            );

            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        // Rotate log file
        $this->rotateLog();
        
        return 0;
    }

    protected function backupDatabase($tempDir)
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        
        $sqlFile = $tempDir . '/database.sql';
        $this->info("📀 Backing up database...");
        
        $command = "mysqldump --host={$host} --user={$username} --password=" . escapeshellarg($password) . " {$database} > {$sqlFile}";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($sqlFile)) {
            throw new \Exception("Database backup failed");
        }
        
        $size = round(filesize($sqlFile) / 1024 / 1024, 2);
        $this->info("   ✅ Database backed up: {$size} MB");
    }

    protected function backupFiles($tempDir)
    {
        $this->info("📁 Backing up important files...");
        
        $items = [
            'app' => base_path('app'),
            'config' => base_path('config'),
            'routes' => base_path('routes'),
            'resources' => base_path('resources'),
            '.env' => base_path('.env'),
            'composer.json' => base_path('composer.json'),
            'artisan' => base_path('artisan'),
        ];
        
        $count = 0;
        foreach ($items as $name => $path) {
            if (File::exists($path)) {
                if (is_file($path)) {
                    File::copy($path, $tempDir . '/' . $name);
                    $count++;
                } else {
                    File::copyDirectory($path, $tempDir . '/' . $name);
                    $count += count(File::allFiles($path));
                }
            }
        }
        
        $this->info("   ✅ {$count} files/directories backed up");
    }

    protected function createZip($source, $destination)
    {
        $this->info("🗜 Creating ZIP archive...");
        
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) === true) {
            $files = File::allFiles($source);
            $fileCount = 0;
            
            foreach ($files as $file) {
                $relative = substr($file->getPathname(), strlen($source) + 1);
                $zip->addFile($file->getPathname(), $relative);
                $fileCount++;
            }
            
            $zip->close();
            $this->info("   ✅ ZIP created with {$fileCount} files");
        } else {
            throw new \Exception("Failed to create ZIP file");
        }
    }

    protected function rotateBackups()
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() == 'zip') {
                $backups[$file->getMTime()] = $file;
            }
        }

        ksort($backups); // Oldest first
        $keep = 8;
        $total = count($backups);

        if ($total > $keep) {
            $toDelete = $total - $keep;
            $this->info("🔄 Keeping {$keep} out of {$total} backups, removing {$toDelete}...");
            
            $count = 0;
            while (count($backups) > $keep) {
                $oldest = array_shift($backups);
                File::delete($oldest);
                $count++;
                $this->info("   🗑️ Removed: " . $oldest->getFilename());
            }
            $this->info("✅ Removed {$count} old backups");
        } else {
            $this->info("✅ {$total} backups exist (max {$keep})");
        }
    }

    protected function logInfo($message)
    {
        $this->writeLog('INFO', $message);
    }

    protected function logError($subject, $message, $file, $line)
    {
        $this->writeLog('ERROR', "$subject: $message in $file:$line");
    }

    protected function writeLog($level, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] $level: $message\n";
        File::append($this->logFile, $logLine);
    }

    protected function rotateLog()
    {
        if (!File::exists($this->logFile)) {
            return;
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $errorLines = [];
        $otherLines = [];

        foreach ($lines as $line) {
            if (strpos($line, 'ERROR:') !== false) {
                $errorLines[] = $line;
            } else {
                $otherLines[] = $line;
            }
        }

        // Keep all errors, limit others to maxLogLines
        $otherLines = array_slice($otherLines, -$this->maxLogLines);
        $allLines = array_merge($errorLines, $otherLines);
        File::put($this->logFile, implode("\n", $allLines) . "\n");
    }
}