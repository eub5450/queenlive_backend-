<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MediaPathHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Boss 2026-07-04: automatic ADULT-IMAGE upload enforcement.
 *
 * The app's on-device nudity detector (ImageUploadGuardService) blocks the
 * upload client-side; it then reports the violation here so the account is
 * BANNED FOR 1 HOUR server-side (ban_type D with a custom 1-hour open_time —
 * the existing unlockBannedUsers cron auto-unbans when it expires) and the
 * user's CURRENT profile image is purged (file deleted + reset to default)
 * in case an earlier nude slipped through.
 */
class AdultUploadReportController extends Controller
{
    public function Report(Request $request)
    {
        $access_token = $request->access_token;
        if ($access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return json_encode([
                ['message' => 'Unauthorized access_token', 'code' => '401'],
            ], JSON_UNESCAPED_UNICODE);
        }

        $userId = trim((string) ($request->user_id ?? ''));
        if ($userId === '' || !ctype_digit($userId)) {
            return json_encode([
                ['message' => 'user_id required', 'code' => '400'],
            ], JSON_UNESCAPED_UNICODE);
        }

        $user = User::find($userId);
        if (!$user) {
            return json_encode([
                ['message' => 'User not found', 'code' => '404'],
            ], JSON_UNESCAPED_UNICODE);
        }

        // Never auto-ban platform staff.
        if ((int) ($user->is_admin ?? 0) >= 1 || (int) ($user->is_official_id ?? 0) !== 0) {
            return json_encode([
                ['message' => 'Skipped for staff account', 'code' => '200'],
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            // 1-HOUR ban: type D + custom open_time; unlockBannedUsers clears
            // it automatically once open_time passes.
            $user->status = 0;
            $user->ban_type = 'D';
            $user->ban_proved = 'adult_image_upload_auto';
            $user->open_time = Carbon::now()->addHour()->format('Y-m-d H:i:s');

            // Purge the CURRENT profile image (an earlier nude may already be
            // live) — delete the stored file and reset to the default avatar.
            $currentProfile = (string) ($user->profile ?? '');
            if ($currentProfile !== '' &&
                $currentProfile !== 'store/profile/default.png' &&
                strpos($currentProfile, 'default.png') === false &&
                strpos($currentProfile, 'default.webp') === false) {
                try {
                    MediaPathHelper::deleteLocalFile($currentProfile, ['store/user']);
                } catch (\Throwable $e) { /* best effort */ }
                $user->profile = 'store/profile/default.png';
            }

            $user->save();
        } catch (\Throwable $e) {
            return json_encode([
                ['message' => 'Ban store failed', 'code' => '500'],
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            [
                'message' => 'User banned for 1 hour (adult image upload)',
                'code' => '200',
                'open_time' => (string) $user->open_time,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
}
