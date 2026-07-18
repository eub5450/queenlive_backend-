<?php

/*
|--------------------------------------------------------------------------
| QueenLive Short Video (Moments) configuration
|--------------------------------------------------------------------------
| Storage lives on the SGP1 "db-01" droplet disk (free space). Point
| `storage_path` at a directory on that mounted volume and `base_url` at the
| public URL that serves it (e.g. an nginx location). ffmpeg/ffprobe are used
| to enforce the 60s cap and burn the "QueenLive" + user-id watermark.
*/

return [
    // Absolute directory where processed videos + thumbnails are written.
    // Default keeps it inside Laravel's public storage for local dev.
    'storage_path' => env('SHORT_VIDEO_PATH', storage_path('app/public/shortvideos')),

    // Public base URL that maps to storage_path (no trailing slash).
    'base_url' => env('SHORT_VIDEO_BASE_URL', env('APP_URL', '') . '/storage/shortvideos'),

    // Hard limits.
    'max_seconds' => (int) env('SHORT_VIDEO_MAX_SECONDS', 60),
    'max_upload_mb' => (int) env('SHORT_VIDEO_MAX_MB', 80),

    // ffmpeg toolchain (absolute paths recommended in production).
    'ffmpeg' => env('FFMPEG_BIN', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_BIN', 'ffprobe'),

    // Font used by drawtext for the watermark.
    'font' => env('SHORT_VIDEO_FONT', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'),

    // Watermark brand text (the user id is appended automatically).
    'brand' => env('SHORT_VIDEO_BRAND', 'QueenLive'),
];
