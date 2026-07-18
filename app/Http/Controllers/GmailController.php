<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\AgoraKeys;
use Illuminate\Support\Str;
use App\Models\GeneratedEmail;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class GmailController extends Controller
{
    /**
     * Step 1: Redirect user to Google OAuth consent screen
     */
    
      public function ProxyEmailgenerate()
{
    $mail = 'sdafasdfafdg@gmail.com';
    
    $email = strtolower(trim($mail));
    
    // Only Gmail allowed
    if (!Str::endsWith($email, '@gmail.com')) {
        return response()->json([
            'status' => false,
            'message' => 'Only @gmail.com addresses are allowed.'
        ], 422);
    }

    [$username, $domain] = explode('@', $email);
    
    $variations = [];
    
    // Generate DOT variations
    $length = strlen($username);
    $max = pow(2, $length - 1);
    
    for ($i = 0; $i < $max; $i++) {
        $newUsername = '';
        for ($j = 0; $j < $length; $j++) {
            $newUsername .= $username[$j];
            if ($j < $length - 1 && ($i & (1 << $j))) {
                $newUsername .= '.';
            }
        }
        // Skip original (no dots)
        if ($newUsername !== $username) {
            $variations[] = $newUsername . '@' . $domain;
        }
    }
    
    // Generate PLUS variations
    $plusTags = ['test', 'signup', 'shop', 'login', 'promo', 'offer', 'verify', 'account'];
    foreach ($plusTags as $tag) {
        $variations[] = $username . '+' . $tag . '@' . $domain;
    }
    
    $variations = array_unique($variations);
    $variations = array_slice($variations, 0, 2100);
    
    // Get existing emails in one query
    $existingEmails = GeneratedEmail::whereIn('generated_email', $variations)
        ->pluck('generated_email')
        ->toArray();
    
    $newVariations = array_diff($variations, $existingEmails);
    
    // Batch insert new variations
    $insertData = [];
    foreach ($newVariations as $genEmail) {
        $insertData[] = [
            'original_email' => $email,
            'generated_email' => $genEmail,
            'is_used' => 0,
            'login_password' => 'Ago5248@#',
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
    
    if (!empty($insertData)) {
        GeneratedEmail::insert($insertData);
    }
    
    // Prepare response
    $result = [];
    foreach ($variations as $genEmail) {
        $result[] = [
            'email' => $genEmail,
            'is_used' => 0,
            'login_password' => 'Md1234567'
        ];
    }
    
    return response()->json([
        'status' => true,
        'original' => $email,
        'count' => count($result),
        'new_inserts' => count($insertData),
        'emails' => $result,
        'login_password' => 'Md1234567'
    ]);
}
        /**
 * Clean up old notification emails
 */
private function cleanupOldNotificationEmails($accessToken, $now)
{
    $subjectsToDelete = [
        'You are close to exceeding RTC Free Package quota, please upgrade package soon',
        'Verification Code'
    ];

    foreach ($subjectsToDelete as $subject) {
        try {
            // Search for emails with specific subject
            $searchResponse = Http::withToken($accessToken)
                ->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                    'q' => "subject:\"{$subject}\"",
                    'maxResults' => 10
                ]);

            $messages = $searchResponse->json()['messages'] ?? [];

            foreach ($messages as $msg) {
                // Get full message details to check timestamp
                $msgDetail = Http::withToken($accessToken)
                    ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$msg['id']}");

                $data = $msgDetail->json();
                
                if (isset($data['internalDate'])) {
                    $receivedTime = Carbon::createFromTimestampMs($data['internalDate']);
                    
                    // Delete if older than 15 minutes
                    if ($receivedTime->diffInMinutes($now) > 15) {
                        Http::withToken($accessToken)
                            ->delete("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$msg['id']}");
                        
                        \Log::info("Deleted old email: {$msg['id']} with subject: {$subject}");
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error cleaning up {$subject} emails: " . $e->getMessage());
        }
    }
}
public function checkVerification()
{
    $verificationCode = null;
    $mailTime = null;
    $now = Carbon::now(config('app.timezone', 'Europe/London'));

    $next_email = GeneratedEmail::where('is_used', 0)->first();

    if ($next_email) {
        $next_email_address = $next_email->generated_email;
        
        // Get IMAP credentials
        $email = env('IMAP_USERNAME', 'sdafasdfafdg@gmail.com');
        $password = env('IMAP_PASSWORD', 'cgjr qhag aris ponj');
        
        $mailbox = function_exists('imap_open') ? imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $email, $password) : false;
        
        if ($mailbox) {
            // Search for emails to the specific generated address
            $searchCriteria = 'TO "' . $next_email_address . '" FROM "no-reply@account.agora.io" SUBJECT "Verification Code"';
            $emails = imap_search($mailbox, $searchCriteria);
            
            if ($emails) {
                rsort($emails);
                $latestEmailId = $emails[0];
                
                $header = imap_headerinfo($mailbox, $latestEmailId);
                $body = $this->getEmailBodyImap($mailbox, $latestEmailId);
                
                if ($body && preg_match('/\b\d{6}\b/', $body, $matches)) {
                    $verificationCode = $matches[0];
                }
                
                if (isset($header->udate)) {
                    $receivedTime = Carbon::createFromTimestamp($header->udate);
                    $mailTime = $receivedTime->diffForHumans();
                    
                    if ($receivedTime->diffInMinutes($now) > 15) {
                        imap_delete($mailbox, $latestEmailId);
                        imap_expunge($mailbox);
                        \Log::info("Deleted old verification email for: {$next_email_address}");
                        $verificationCode = null;
                        $mailTime = null;
                    }
                }
                
                if ($verificationCode) {
                    imap_setflag_full($mailbox, $latestEmailId, '\\Seen');
                }
            }
            
            imap_close($mailbox);
        }
    }

    return response()->json([
        'verificationCode' => $verificationCode,
        'mailTime' => $mailTime
    ]);
}

public function AgoraSystemIndex()
{
    $data = AgoraKeys::where('status', '!=', 2)
            ->where('type', 1)
            ->get();

    $next_email = GeneratedEmail::where('is_used', 0)->first();
    $verificationCode = null;
    $mailTime = null;
    $firstName = null;
    $lastName = null;
    $companyWebsite = null;
    $accountEmail = null;
    
    // Arrays for random data
    $firstNames = [
        'John', 'Emma', 'Michael', 'Sophia', 'William', 'Olivia', 'James', 'Ava', 
        'Robert', 'Isabella', 'David', 'Mia', 'Richard', 'Charlotte', 'Joseph', 
        'Amelia', 'Thomas', 'Harper', 'Charles', 'Evelyn', 'Christopher', 'Abigail',
        'Daniel', 'Emily', 'Matthew', 'Elizabeth', 'Anthony', 'Sofia', 'Donald',
        'Avery', 'Mark', 'Ella', 'Paul', 'Madison', 'Steven', 'Scarlett',
        'Andrew', 'Victoria', 'Kenneth', 'Aria', 'Joshua', 'Grace', 'Kevin',
        'Chloe', 'Brian', 'Camila', 'George', 'Penelope', 'Edward', 'Riley'
    ];
    
    $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
        'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen',
        'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera',
        'Campbell', 'Mitchell', 'Carter', 'Roberts'
    ];
    
    $companies = [
        'https://techcorp.com', 'https://innovate.io', 'https://global-solutions.com',
        'https://creative-studio.co', 'https://nextgen.dev', 'https://prime-ventures.com',
        'https://apex-systems.net', 'https://fusion-labs.io', 'https://horizon-tech.com',
        'https://velocity.cloud', 'https://matrix-solutions.com', 'https://quantum-dev.io',
        'https://stellar-tech.co', 'https://nova-systems.com', 'https://eclipse-labs.net',
        'https://cyberdyne.io', 'https://umbrella-corp.com', 'https://wayne-enterprises.com',
        'https://stark-industries.com', 'https://oscorp.net', 'https://massive-dynamic.com',
        'https://acme-corp.com', 'https://initech.com', 'https://hooli.xyz',
        'https://piedpiper.net', 'https://aviato.com', 'https://bytechain.io'
    ];

  
    $verificationCode=0;
    // Generate random data from arrays
    $randomFirstName = $firstNames[array_rand($firstNames)];
    $randomLastName = $lastNames[array_rand($lastNames)];
    $randomCompany = $companies[array_rand($companies)];
    
    // Use random data (you can also conditionally use extracted data if available)
    $firstName = $randomFirstName;
    $lastName = $randomLastName;
    $companyWebsite = $randomCompany;
    
    // Optional: If you want to use extracted data when available, and fallback to random
    // $firstName = $extractedFirstName ?? $randomFirstName;
    // $lastName = $extractedLastName ?? $randomLastName;
    // $companyWebsite = $extractedCompany ?? $randomCompany;
    $today_data = AgoraKeys::whereDate('created_at', today())->count();

    return view('from.agora_datain', compact(
        'data', 
        'verificationCode', 
        'mailTime', 
        'next_email',
        'firstName',
        'lastName',
        'companyWebsite',
        'accountEmail',
        'today_data'
    ));
}


   public function DataFontAgoraStore(Request $request)
    {
        $request->validate([
            'appId'              => 'required|string|max:255',
            'appCertificate'     => 'required|string|max:255',
            'AgoraEmail'         =>    'required|email|max:255',
        ]);
    
        // Save account
        AgoraKeys::create([
            'appId'              => $request->appId,
            'appCertificate'     => $request->appCertificate,
            'AgoraEmail'         => $request->AgoraEmail,
            'type'               => 1,
            'main_email'         => str_replace('.', '', strstr($request->AgoraEmail, '@', true)),
            'AgoraEmailPassword' =>'Ago5248@#',
        ]);
        GeneratedEmail::where('generated_email', $request->AgoraEmail)->update(['is_used' => 1]);
        self::deleteAgoraVerificationEmails();
        return redirect()->back()->with('success', 'Agora Account saved successfully!');
    }
    
  public function deleteAgoraVerificationEmails()
{
    try {
        // Get IMAP credentials from .env
        $email = env('IMAP_USERNAME', 'sdafasdfafdg@gmail.com');
        $password = env('IMAP_PASSWORD', 'cgjr qhag aris ponj');
        
        // Connect to Gmail INBOX
        $mailbox = function_exists('imap_open') ? imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $email, $password) : false;
        
        if (!$mailbox) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect: ' . imap_last_error()
            ]);
        }
        
        // Search for emails from Agora with Verification Code subject
        $emails = imap_search($mailbox, 'FROM "no-reply@account.agora.io" SUBJECT "Verification Code"');
        
        $deletedCount = 0;
        
        if ($emails) {
            foreach ($emails as $emailId) {
                // Mark for deletion
                imap_delete($mailbox, $emailId);
                $deletedCount++;
            }
            
            // Permanently delete marked emails
            imap_expunge($mailbox);
            
            $message = "Deleted {$deletedCount} verification email(s) from no-reply@account.agora.io";
            \Log::info($message);
            
        } else {
            $message = "No verification emails found from no-reply@account.agora.io";
        }
        
        imap_close($mailbox);
        
        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => $message ?? 'No emails found'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
    /**
     * Get form data for Agora registration
     *
     * @return JsonResponse
     */
    public function getFormData(): JsonResponse
    {
        try {
            // Get next unused email from database
            $nextEmail = GeneratedEmail::where('is_used', 0)->first();
            
            if (!$nextEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No unused emails available in database'
                ], 404);
            }

            // Random data arrays
            $firstNames = [
                'John', 'Emma', 'Michael', 'Sophia', 'William', 'Olivia', 'James', 'Ava',
                'Robert', 'Isabella', 'David', 'Mia', 'Richard', 'Charlotte', 'Joseph',
                'Amelia', 'Thomas', 'Harper', 'Charles', 'Evelyn', 'Christopher', 'Abigail',
                'Daniel', 'Emily', 'Matthew', 'Elizabeth', 'Anthony', 'Sofia', 'Donald',
                'Avery', 'Mark', 'Ella', 'Paul', 'Madison', 'Steven', 'Scarlett',
                'Andrew', 'Victoria', 'Kenneth', 'Aria', 'Joshua', 'Grace', 'Kevin',
                'Chloe', 'Brian', 'Camila', 'George', 'Penelope', 'Edward', 'Riley',
                'Jason', 'Sarah', 'Justin', 'Laura', 'Brandon', 'Amy', 'Jeffrey', 'Angela',
                'Ryan', 'Melissa', 'Jacob', 'Rebecca', 'Gary', 'Michelle', 'Nicholas', 'Nicole',
                'Eric', 'Kimberly', 'Jonathan', 'Lisa', 'Stephen', 'Nancy', 'Larry', 'Sandra'
            ];

            $lastNames = [
                'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
                'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
                'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
                'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
                'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen',
                'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera',
                'Campbell', 'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans',
                'Turner', 'Diaz', 'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart',
                'Morris', 'Murphy', 'Cook', 'Rogers', 'Morgan', 'Peterson', 'Cooper', 'Reed',
                'Bailey', 'Bell', 'Gomez', 'Kelly', 'Howard', 'Ward', 'Cox', 'Diaz', 'Richardson'
            ];

            $companies = [
                'TechCorp Solutions', 'InnovateTech', 'Global Dynamics', 'Creative Systems',
                'NextGen Innovations', 'Prime Ventures', 'Apex Technologies', 'Fusion Labs',
                'Horizon Enterprises', 'Velocity Systems', 'Matrix Solutions', 'Quantum Devices',
                'Stellar Technologies', 'Nova Industries', 'Eclipse Innovations', 'Cyberdyne Systems',
                'Umbrella Corporation', 'Wayne Enterprises', 'Stark Industries', 'Oscorp Industries',
                'Massive Dynamic', 'ACME Corporation', 'Initech', 'Hooli', 'Pied Piper',
                'Aviato', 'ByteChain', 'CloudNine', 'DataStream', 'CyberCore', 'DigitalFrontier',
                'EcoTech', 'FutureGen', 'GlobalTech', 'Hyperion', 'IntelliSys', 'Jupiter Systems',
                'Krypton Technologies', 'Luna Innovations', 'Meridian Solutions', 'NexGen Dynamics',
                'OmniCorp', 'Pioneer Tech', 'Quantum Leap', 'RocketFuel', 'Solaris', 'Titan Industries',
                'Unified Technologies', 'Vertex Solutions', 'WebWorks', 'Xenon Systems', 'York Enterprises',
                'Zenith Technologies', 'AlphaTech', 'Beta Solutions', 'Gamma Innovations', 'Delta Systems'
            ];

            $countries = [
                'US - United States', 'GB - United Kingdom', 'CA - Canada', 'AU - Australia',
                'BD - Bangladesh', 'IN - India', 'DE - Germany', 'FR - France', 'JP - Japan',
                'SG - Singapore', 'AE - United Arab Emirates', 'SA - Saudi Arabia', 'CN - China',
                'BR - Brazil', 'MX - Mexico', 'IT - Italy', 'ES - Spain', 'NL - Netherlands',
                'SE - Sweden', 'NO - Norway', 'DK - Denmark', 'FI - Finland', 'CH - Switzerland',
                'BE - Belgium', 'AT - Austria', 'IE - Ireland', 'NZ - New Zealand', 'ZA - South Africa',
                'KR - South Korea', 'MY - Malaysia', 'TH - Thailand', 'VN - Vietnam', 'PH - Philippines',
                'PK - Pakistan', 'LK - Sri Lanka', 'NP - Nepal', 'ID - Indonesia', 'TR - Turkey',
                'IL - Israel', 'EG - Egypt', 'KE - Kenya', 'NG - Nigeria', 'AR - Argentina',
                'CL - Chile', 'CO - Colombia', 'PE - Peru', 'PT - Portugal', 'GR - Greece',
                'PL - Poland', 'CZ - Czech Republic', 'HU - Hungary', 'RO - Romania'
            ];

            // Generate random data
            $randomFirstName = $firstNames[array_rand($firstNames)];
            $randomLastName = $lastNames[array_rand($lastNames)];
            
            // Generate company name (either from list or create one)
            $randomCompany = $companies[array_rand($companies)];
            
            // Generate company website from company name
            $companyWebsite = 'https://' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $randomCompany)) . '.com';
            
            // Random country
            $randomCountry = $countries[array_rand($countries)];

            // Fixed password as requested
            $fixedPassword = 'Ago5248@#';

            // Prepare response data
            $responseData = [
                'success' => true,
                'data' => [
                    'firstName' => $randomFirstName,
                    'lastName' => $randomLastName,
                    'companyName' => $randomCompany,
                    'companyWebsite' => $companyWebsite,
                    'country' => $randomCountry,
                    'accountEmail' => $nextEmail->generated_email,
                    'password' => $fixedPassword,
                    'generatedEmail' => [
                        'email' => $nextEmail->generated_email,
                        'password' => $fixedPassword ?? null,
                        'recovery_email' => $nextEmail->recovery_email ?? null,
                        'id' => $nextEmail->id
                    ]
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'email_id' => $nextEmail->id
                ]
            ];

            // OPTIONAL: Mark email as used immediately
            // Uncomment the next line if you want to mark email as used when fetched
            // $nextEmail->update(['is_used' => 1]);

            return response()->json($responseData, 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optional: Additional method to mark email as used
     */
    public function markEmailAsUsed($emailId)
    {
        try {
            $email = GeneratedEmail::find($emailId);
            
            if ($email) {
                $email->update(['is_used' => 1]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Email marked as used successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking email as used',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
 * Helper function to extract email body via IMAP
 */
private function getEmailBodyImap($mailbox, $emailId)
{
    $body = '';
    
    // Try to get HTML body
    $htmlBody = imap_fetchbody($mailbox, $emailId, 1.2);
    if ($htmlBody == '') {
        $htmlBody = imap_fetchbody($mailbox, $emailId, 1);
    }
    
    // Try to get plain text body
    $textBody = imap_fetchbody($mailbox, $emailId, 1.1);
    
    // Determine encoding and decode
    $structure = imap_fetchstructure($mailbox, $emailId);
    $encoding = $structure->encoding ?? 0;
    
    // Use text body if available, otherwise use HTML and strip tags
    if (!empty($textBody)) {
        $body = $this->decodeBodyImap($textBody, $encoding);
    } elseif (!empty($htmlBody)) {
        $body = strip_tags($this->decodeBodyImap($htmlBody, $encoding));
    }
    
    return $body;
}

/**
 * Helper function to decode IMAP body
 */
private function decodeBodyImap($body, $encoding)
{
    if (empty($body)) return '';
    
    switch ($encoding) {
        case 3: // ENCBASE64
            return base64_decode($body);
        case 4: // ENCQUOTEDPRINTABLE
            return quoted_printable_decode($body);
        default:
            return imap_qprint($body);
    }
}   
}
