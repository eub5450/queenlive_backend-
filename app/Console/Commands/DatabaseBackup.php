<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Helpers\MailHelper;
use Dotenv\Dotenv;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Take database backup (keeps last 8)';

    protected $backupPath;
    protected $logFile;
    protected $maxLogLines = 20;

    public function __construct()
    {
        parent::__construct();
        $this->backupPath = storage_path('backup/database');
        $this->logFile = storage_path('logs/backup.log');
    }

    public function handle()
    {
        // Force environment
        $this->forceEnvironment();
        
        $domain = 'queenlive.site';
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$domain}_hdb_{$timestamp}.sql";
        $fullPath = $this->backupPath . '/' . $filename;

        $this->info("📀 Starting database backup: {$filename}");
        $this->logInfo("Starting database backup: {$filename}");

        try {
            // Create directory if needed
            if (!File::exists($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
                $this->info("✅ Directory created: {$this->backupPath}");
            }

            // Database credentials
            $database = 'queenlive_database';
            $username = 'queenlive_database';
            $password = 'VXRRMND(oW1I)bN';
            $host = '127.0.0.1';

            // Find mysqldump
            $mysqldump = $this->findMysqldump();
            
            // Run mysqldump
            $command = "{$mysqldump} --host={$host} --user={$username} --password=" . escapeshellarg($password) . " {$database} > {$fullPath} 2>&1";
            $this->info("🔄 Running mysqldump...");
            
            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($fullPath)) {
                throw new \Exception("mysqldump failed (code: $returnCode)");
            }

            $size = round(filesize($fullPath) / 1024 / 1024, 2);
            $this->info("✅ Backup created: {$filename} ({$size} MB)");
            $this->logInfo("Backup created: {$filename} ({$size} MB)");

            // Send success email
            MailHelper::sendBackupNotification(
                "🗄️ Database Backup Successful - {$domain} - " . date('Y-m-d H:i'),
                'database',
                $filename,
                $size . ' MB',
                'success'
            );

            $this->rotateBackups();

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            $this->logError("Database backup failed", $e->getMessage(), $e->getFile(), $e->getLine());

            // Send failure email
            MailHelper::sendBackupNotification(
                "❌ Database Backup Failed - {$domain} - " . date('Y-m-d H:i'),
                'database',
                $filename ?? 'unknown',
                '0 MB',
                'danger',
                $e->getMessage()
            );
        }

        $this->rotateLog();
        return 0;
    }

    private function forceEnvironment()
    {
        // Set mail credentials
        putenv('MAIL_USERNAME=queueit.bera@gmail.com');
        putenv('MAIL_PASSWORD=cfmx tuvi gbtv lved');
        putenv('MAIL_HOST=smtp.gmail.com');
        putenv('MAIL_PORT=587');
        putenv('MAIL_ENCRYPTION=tls');
        putenv('MAIL_FROM_ADDRESS=queueit.bera@gmail.com');
        putenv('MAIL_FROM_NAME=Backup System');
        putenv('BACKUP_EMAIL=jahirvevo@gmail.com');
        
        $_ENV['MAIL_USERNAME'] = 'queueit.bera@gmail.com';
        $_ENV['MAIL_PASSWORD'] = 'cfmx tuvi gbtv lved';
        
        // Force mail config
        config([
            'mail.mailers.smtp.username' => 'queueit.bera@gmail.com',
            'mail.mailers.smtp.password' => 'cfmx tuvi gbtv lved',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.encryption' => 'tls',
        ]);
    }

    private function findMysqldump()
    {
        $paths = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
        foreach ($paths as $path) {
            $result = shell_exec("which {$path} 2>/dev/null");
            if (!empty($result)) return trim($result);
        }
        return 'mysqldump';
    }

    private function rotateBackups()
    {
        $files = File::files($this->backupPath);
        $backups = [];
        foreach ($files as $file) {
            if ($file->getExtension() == 'sql') {
                $backups[$file->getMTime()] = $file;
            }
        }
        ksort($backups);
        $keep = 8;
        if (count($backups) > $keep) {
            $count = 0;
            while (count($backups) > $keep) {
                $oldest = array_shift($backups);
                File::delete($oldest);
                $count++;
            }
            $this->info("✅ Removed {$count} old backups");
        }
    }

    private function logInfo($message)
    {
        $this->writeLog('INFO', $message);
    }

    private function logError($subject, $message, $file, $line)
    {
        $this->writeLog('ERROR', "$subject: $message in $file:$line");
    }

    private function writeLog($level, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        File::append($this->logFile, "[$timestamp] $level: $message\n");
    }

    private function rotateLog()
    {
        if (!File::exists($this->logFile)) return;
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
        $otherLines = array_slice($otherLines, -$this->maxLogLines);
        File::put($this->logFile, implode("\n", array_merge($errorLines, $otherLines)) . "\n");
    }
}