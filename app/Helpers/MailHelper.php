<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class MailHelper
{
    /**
     * Send notification using PHP's built-in mail() function
     */
    public static function sendNotification($type, $status, $message, $details = [])
    {
        $domain = 'queenlive.site';
        
        $colors = [
            'success' => ['bg' => '#10b981', 'light' => '#d1fae5', 'text' => '#065f46', 'icon' => '✅', 'name' => 'সফল'],
            'danger' => ['bg' => '#dc2626', 'light' => '#fee2e2', 'text' => '#991b1b', 'icon' => '❌', 'name' => 'সমস্যা'],
        ];
        
        $color = $colors[$status] ?? $colors['success'];
        $subject = self::generateSubject($type, $status, $domain);
        
        $downloadLink = $details['download_link'] ?? '#';
        $filename = $details['📁 ফাইলের নাম'] ?? 'backup.sql';
        
        $htmlContent = self::generateHtmlContent($color, $type, $domain, $message, $details, $downloadLink, $filename);
        
        $to = 'jahirvevo@gmail.com';
        $from = 'queueit.bera@gmail.com';
        $fromName = 'Backup System';
        
        Log::info('MailHelper attempting to send via PHP mail()', [
            'to' => $to,
            'from' => $from,
            'subject' => $subject
        ]);
        
        // Headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Send email
        if (mail($to, $subject, $htmlContent, $headers)) {
            Log::info("✅ Mail sent via PHP mail()");
            return true;
        } else {
            Log::error("❌ PHP mail() failed");
            return false;
        }
    }
    
    /**
     * Send backup notification with download link
     */
    public static function sendBackupNotification($subject, $type, $filename, $size, $status = 'success', $errorMessage = null)
    {
        $domain = 'queenlive.site';
        $icon = $type === 'database' ? '🗄️' : '📦';
        
        // Generate download link
        $downloadLink = "https://{$domain}/storage/backup/database/{$filename}";
        
        $message = $status === 'success' 
            ? "✅ {$icon} {$type} ব্যাকআপ সফল হয়েছে"
            : "❌ {$icon} {$type} ব্যাকআপ ব্যর্থ হয়েছে";
        
        $details = [
            '📁 ফাইলের নাম' => $filename,
            '📊 ফাইল সাইজ' => $size,
            '⏰ সময়' => date('Y-m-d H:i:s'),
            'download_link' => $downloadLink
        ];
        
        if ($status === 'success') {
            $details['🔄 পরবর্তী ব্যাকআপ'] = $type === 'database' 
                ? date('Y-m-d H:i:s', strtotime('+6 hours'))
                : date('Y-m-d H:i:s', strtotime('tomorrow 01:00'));
        }
        
        if ($errorMessage) {
            $details['❌ এরর বিবরণ'] = $errorMessage;
        }
        
        return self::sendNotification($type, $status === 'success' ? 'success' : 'danger', $message, $details);
    }
    
    /**
     * Generate HTML content with download button
     */
    private static function generateHtmlContent($color, $type, $domain, $message, $details, $downloadLink, $filename)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: ' . $color['bg'] . '; color: white; padding: 30px; text-align: center; }
                .content { background: white; padding: 30px; }
                .message-box { background: ' . $color['light'] . '; color: ' . $color['text'] . '; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .details { background: #f9fafb; padding: 20px; border-radius: 8px; }
                .footer { background: #f3f4f6; padding: 20px; text-align: center; color: #6b7280; font-size: 12px; }
                .download-btn {
                    display: inline-block;
                    background: ' . $color['bg'] . ';
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: bold;
                    margin: 20px 0;
                    border: none;
                    cursor: pointer;
                }
                .download-btn:hover {
                    opacity: 0.9;
                }
                .file-info {
                    background: #e8f5e9;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border-left: 4px solid #4caf50;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . $color['icon'] . ' ' . ucfirst($type) . ' ' . $color['name'] . '</h1>
                    <p>' . $domain . '</p>
                </div>
                <div class="content">
                    <div class="message-box">' . $message . '</div>';
        
        if ($downloadLink !== '#') {
            $html .= '<div class="file-info">
                        <h3 style="margin-top:0; color:#2e7d32;">📥 ডাউনলোড লিঙ্ক</h3>
                        <p style="margin-bottom:15px;"><strong>ফাইল:</strong> ' . $filename . '</p>
                        <p><strong>সাইজ:</strong> ' . $details['📊 ফাইল সাইজ'] . '</p>
                        <p><strong>সময়:</strong> ' . $details['⏰ সময়'] . '</p>
                        <a href="' . $downloadLink . '" class="download-btn" target="_blank">📥 ডাউনলোড ব্যাকআপ</a>
                        <p style="font-size:12px; color:#666; margin-top:10px;">লিঙ্কটি 24 ঘন্টা সক্রিয় থাকবে</p>
                      </div>';
        }
        
        if (!empty($details)) {
            $html .= '<div class="details"><h3 style="margin-top:0; color:#374151;">বিস্তারিত তথ্য:</h3>';
            foreach ($details as $key => $value) {
                if ($key !== 'download_link') {
                    $html .= '<div style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                                <strong>' . $key . ':</strong> ' . $value . '
                              </div>';
                }
            }
            $html .= '</div>';
        }
        
        $html .= '      <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: center;">
                        <p style="color: #0369a1; margin:0;">
                            ⏰ ' . date('Y-m-d H:i:s') . '<br>
                            <small>এই লিঙ্কটি শুধুমাত্র আপনার জন্য তৈরি করা হয়েছে</small>
                        </p>
                    </div>
                </div>
                <div class="footer">
                    <p>এটি আপনার সার্ভার থেকে পাঠানো একটি স্বয়ংক্রিয় নোটিফিকেশন।</p>
                    <p>&copy; ' . date('Y') . ' ' . $domain . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Generate subject
     */
    private static function generateSubject($type, $status, $domain)
    {
        $typeBangla = [
            'database' => 'ডাটাবেজ',
            'file' => 'ফাইল',
            'backup' => 'ব্যাকআপ',
            'test' => 'টেস্ট'
        ];
        
        $typeText = $typeBangla[$type] ?? $type;
        $time = date('H:i');
        $dayName = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'][date('w')];
        
        if ($status === 'danger') {
            return "❌ জরুরি! {$typeText} সমস্যা হয়েছে - {$domain} - {$time}";
        } elseif ($status === 'success') {
            return "✅ {$typeText} ব্যাকআপ - {$domain} - {$dayName} {$time}";
        } else {
            return "ℹ️ {$typeText} তথ্য - {$domain} - {$time}";
        }
    }
}