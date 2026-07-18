<?php
// app/Console/Commands/TestEmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test-email';
    protected $description = 'Test email configuration';

    public function handle()
    {
        $this->info('📧 Testing email configuration...');
        
        try {
            Mail::send([], [], function ($message) {
                $message->to('jahirvevo@gmail.com')
                        ->from(env('MAIL_FROM_ADDRESS', 'queueit.bera@gmail.com'), env('MAIL_FROM_NAME', 'Backup System'))
                        ->subject('✅ Backup System Test Email')
                        ->html("
                            <h2>✅ Email Configuration Successful!</h2>
                            <p>Your backup system email is working properly.</p>
                            <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                            <p><strong>Status:</strong> ✅ Success</p>
                        ", 'text/html');
            });
            
            $this->info('✅ Test email sent successfully to jahirvevo@gmail.com');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
        }
        
        return 0;
    }
}