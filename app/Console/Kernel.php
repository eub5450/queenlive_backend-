<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\RunTaskEvery4Seconds::class,
        \App\Console\Commands\MoveOldGifts::class,
        \App\Console\Commands\V5DenormReconcileCommand::class,
        \App\Console\Commands\SweepFanClub::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // ===== GOOGLE DRIVE BACKUP SCHEDULE =====
        
        // ডাটাবেজ ব্যাকআপ - প্রতি ৬ ঘন্টায় (শেষ ৮টি রাখে)
       
        // Your existing tasks
        $schedule->command('task:run-every-4-seconds')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()  
            ->appendOutputTo(storage_path('logs/cornjob.log'));
            
        $schedule->command('gifts:archive')
            ->monthlyOn(1, '00:05')
            ->withoutOverlapping(60)
            ->onOneServer()
            ->runInBackground() 
            ->appendOutputTo(storage_path('logs/monthly_archive.log'));

        // ===== NEW BACKUP SYSTEM SCHEDULE =====
        // Game banner push every minute
        $schedule->command('banners:push')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()  
            ->appendOutputTo(storage_path('logs/banner.log'));

        // Generate HTML logs every 10 minutes
        $schedule->command('log:generate-html')
            ->everySixHours()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/html-generator.log'));

        // V5 denorm sanity: hourly at :15 — samples 1000 random users
        // and diff-logs gifts SUM vs users.total_gifts_received_value.
        $schedule->command('v5:denorm-reconcile')
            ->hourlyAt(15)
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/v5_denorm_reconcile.log'));

        $schedule->command('fanclub:sweep-expirations')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/fanclub_sweep.log'));






}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    

    }

}
