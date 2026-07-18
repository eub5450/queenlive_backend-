<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShortVideo;
use App\Models\ShortVideoLike;
use App\Models\ShortVideoComment;
use App\Models\ShortVideoGift;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * QueenLive "Moments" — TikTok-style short videos.
 * Upload (<=60s, watermarked QueenLive + user id), vertical feed, like, comment,
 * view counter and in-feed gifting. Mirrors the project's existing API style:
 * shared access token + Auth::id() match, json_encode responses with a `code`.
 */
class ShortVideoController extends Controller
{
    private const ACCESS_TOKEN = "0411f0028cfb768b3a3d96ac3aa37dw3e5";

    /** Uniform response helper matching the rest of the API. */
    private function reply(array $payload)
    {
        return json_encode([$payload], JSON_UNESCAPED_UNICODE);
    }

    private function unauthorized()
    {
        return $this->reply(['message' => 'Unauthorized', 'code' => '401']);
    }

    /** Token + logged-in-user check used by every endpoint. */
    private function guard(Request $request): bool
    {
        return $request->access_token === self::ACCESS_TOKEN
            && (string) $request->user_id === (string) Auth::id();
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('shortvideo.base_url'), '/');
    }

    /** Shape a video row for the app (with author + viewer-specific is_liked). */
    private function shapeVideo(ShortVideo $v, $viewerId, array $likedIds = null)
    {
        $author = $v->relationLoaded('user') ? $v->user : User::find($v->user_id);
        $isLiked = $likedIds !== null
            ? in_array($v->id, $likedIds)
            : ShortVideoLike::where('video_id', $v->id)->where('user_id', $viewerId)->exists();

        return [
            'id' => $v->id,
            'user_id' => $v->user_id,
            'user_name' => $author->name ?? 'QueenLive User',
            'user_profile' => $author->profile ?? '',
            'user_level' => (string) ($author->level ?? '0'),
            'user_frame' => $author->frame ?? '',
            'is_official' => (string) ($author->is_official_id ?? '0'),
            'video_url' => $v->video_url,
            'thumb_url' => $v->thumb_url ?? '',
            'caption' => $v->caption ?? '',
            'duration' => (int) $v->duration,
            'width' => (int) $v->width,
            'height' => (int) $v->height,
            'views_count' => (int) $v->views_count,
            'likes_count' => (int) $v->likes_count,
            'comments_count' => (int) $v->comments_count,
            'gifts_count' => (int) $v->gifts_count,
            'gift_value' => (int) $v->gift_value,
            'is_liked' => $isLiked ? 1 : 0,
            'created_at' => optional($v->created_at)->toIso8601String(),
        ];
    }

    /**
     * Build a stable page without duplicate videos and without back-to-back
     * entries from the same author when alternatives exist.
     */
    private function buildFeedPage(int $page, int $perPage)
    {
        $needed = $page * $perPage;
        $poolLimit = max($needed * 6, 60);

        $pool = ShortVideo::with('user')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->limit($poolLimit)
            ->get();

        $selected = collect();
        $deferred = collect();
        $usedIds = [];
        $usedUrls = [];

        foreach ($pool as $video) {
            $videoId = (int) $video->id;
            $videoUrl = strtolower(trim((string) $video->video_url));

            if (isset($usedIds[$videoId])) {
                continue;
            }

            if ($videoUrl !== '' && isset($usedUrls[$videoUrl])) {
                continue;
            }

            $last = $selected->last();
            if ($last && (int) $last->user_id === (int) $video->user_id) {
                $deferred->push($video);
                continue;
            }

            $selected->push($video);
            $usedIds[$videoId] = true;
            if ($videoUrl !== '') {
                $usedUrls[$videoUrl] = true;
            }

            if ($selected->count() >= $needed) {
                break;
            }
        }

        if ($selected->count() < $needed) {
            foreach ($deferred as $video) {
                $videoId = (int) $video->id;
                $videoUrl = strtolower(trim((string) $video->video_url));

                if (isset($usedIds[$videoId])) {
                    continue;
                }

                if ($videoUrl !== '' && isset($usedUrls[$videoUrl])) {
                    continue;
                }

                $selected->push($video);
                $usedIds[$videoId] = true;
                if ($videoUrl !== '') {
                    $usedUrls[$videoUrl] = true;
                }

                if ($selected->count() >= $needed) {
                    break;
                }
            }
        }

        return $selected->slice(($page - 1) * $perPage, $perPage)->values();
    }

    // ---------------------------------------------------------------------
    // UPLOAD  (multipart: video file + optional caption)
    // ---------------------------------------------------------------------
    public function Upload(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }

        if (!$request->hasFile('video')) {
            return $this->reply(['message' => 'No video file', 'code' => '422']);
        }

        $file = $request->file('video');
        $maxBytes = ((int) config('shortvideo.max_upload_mb')) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return $this->reply(['message' => 'Video too large', 'code' => '413']);
        }

        $userId = (int) Auth::id();
        $dir = rtrim((string) config('shortvideo.storage_path'), '/\\');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $stamp = $userId . '_' . time() . '_' . substr(md5(uniqid('', true)), 0, 8);
        $rawPath = $dir . DIRECTORY_SEPARATOR . $stamp . '_raw.' . strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $outPath = $dir . DIRECTORY_SEPARATOR . $stamp . '.mp4';
        $thumbPath = $dir . DIRECTORY_SEPARATOR . $stamp . '.jpg';

        try {
            $file->move($dir, basename($rawPath));
        } catch (\Throwable $e) {
            Log::error('[shortvideo] move failed: ' . $e->getMessage());
            return $this->reply(['message' => 'Upload failed', 'code' => '500']);
        }

        // Probe duration + dimensions; enforce the 60s cap.
        [$duration, $width, $height] = $this->probe($rawPath);
        $maxSeconds = (int) config('shortvideo.max_seconds');
        if ($duration > 0 && $duration > $maxSeconds + 1) {
            @unlink($rawPath);
            return $this->reply([
                'message' => 'Video must be ' . $maxSeconds . 's or shorter',
                'code' => '422',
            ]);
        }

        // Watermark + thumbnail. Falls back to the raw file if ffmpeg is absent.
        $processed = $this->watermark($rawPath, $outPath, $userId);
        if ($processed) {
            @unlink($rawPath);
            $this->makeThumb($outPath, $thumbPath);
            $finalName = basename($outPath);
        } else {
            // No ffmpeg on host: keep the raw file, no burned watermark.
            $finalName = basename($rawPath);
            $outPath = $rawPath;
            $this->makeThumb($outPath, $thumbPath);
        }

        $thumbUrl = is_file($thumbPath) ? $this->baseUrl() . '/' . basename($thumbPath) : '';

        $video = ShortVideo::create([
            'user_id' => $userId,
            'video_url' => $this->baseUrl() . '/' . $finalName,
            'thumb_url' => $thumbUrl,
            'caption' => mb_substr((string) $request->input('caption', ''), 0, 1000),
            'duration' => $duration > 0 ? min($duration, $maxSeconds) : 0,
            'width' => $width,
            'height' => $height,
            'status' => 'active',
        ]);

        return $this->reply([
            'message' => 'Uploaded',
            'code' => '200',
            'video' => $this->shapeVideo($video, $userId, []),
        ]);
    }

    /** ffprobe -> [durationSeconds, width, height]; zeros if unavailable. */
    private function probe(string $path): array
    {
        $ffprobe = (string) config('shortvideo.ffprobe');
        if (!$this->binaryUsable($ffprobe) || !is_file($path)) {
            return [0, 0, 0];
        }
        $cmd = escapeshellarg($ffprobe)
            . ' -v error -select_streams v:0'
            . ' -show_entries format=duration:stream=width,height'
            . ' -of default=noprint_wrappers=1:nokey=1 '
            . escapeshellarg($path) . ' 2>&1';
        $out = shell_exec($cmd);
        if (!$out) {
            return [0, 0, 0];
        }
        $lines = array_values(array_filter(array_map('trim', explode("\n", $out)), 'strlen'));
        // order: width, height, duration (stream entries then format)
        $width = isset($lines[0]) ? (int) $lines[0] : 0;
        $height = isset($lines[1]) ? (int) $lines[1] : 0;
        $duration = 0;
        foreach ($lines as $l) {
            if (is_numeric($l) && (float) $l > 0 && strpos($l, '.') !== false) {
                $duration = (int) ceil((float) $l);
            }
        }
        return [$duration, $width, $height];
    }

    /** Burn "QueenLive" + "ID: {user}" onto the video. Returns true on success. */
    private function watermark(string $in, string $out, int $userId): bool
    {
        $ffmpeg = (string) config('shortvideo.ffmpeg');
        if (!$this->binaryUsable($ffmpeg) || !is_file($in)) {
            return false;
        }
        $font = (string) config('shortvideo.font');
        $brand = (string) config('shortvideo.brand');
        $fontOpt = ($font && is_file($font)) ? "fontfile='" . str_replace("'", "", $font) . "':" : '';

        $brandTxt = $this->ffEscape($brand);
        $idTxt = $this->ffEscape('ID: ' . $userId);

        // Brand top-right, user id bottom-left, both semi-transparent.
        $draw = "drawtext={$fontOpt}text='{$brandTxt}':fontcolor=white@0.85:fontsize=h/22:x=w-tw-20:y=24:shadowcolor=black@0.5:shadowx=2:shadowy=2"
            . ",drawtext={$fontOpt}text='{$idTxt}':fontcolor=white@0.8:fontsize=h/28:x=20:y=h-th-24:shadowcolor=black@0.5:shadowx=2:shadowy=2";

        $cmd = escapeshellarg($ffmpeg)
            . ' -y -i ' . escapeshellarg($in)
            . ' -vf ' . escapeshellarg($draw)
            . ' -c:v libx264 -preset veryfast -crf 24 -c:a aac -movflags +faststart '
            . escapeshellarg($out) . ' 2>&1';

        @shell_exec($cmd);
        return is_file($out) && filesize($out) > 0;
    }

    private function makeThumb(string $video, string $thumb): void
    {
        $ffmpeg = (string) config('shortvideo.ffmpeg');
        if (!$this->binaryUsable($ffmpeg) || !is_file($video)) {
            return;
        }
        $cmd = escapeshellarg($ffmpeg)
            . ' -y -i ' . escapeshellarg($video)
            . ' -ss 00:00:01 -vframes 1 -q:v 3 '
            . escapeshellarg($thumb) . ' 2>&1';
        @shell_exec($cmd);
    }

    private function ffEscape(string $text): string
    {
        // drawtext needs colon/backslash/quote escaped.
        return str_replace([':', "'", '%'], ['\:', '', ''], $text);
    }

    private function binaryUsable(string $bin): bool
    {
        if ($bin === '') {
            return false;
        }
        if (!function_exists('shell_exec') || !function_exists('escapeshellarg')) {
            return false;
        }
        // Absolute path -> must exist; bare name -> trust PATH.
        if (strpbrk($bin, "/\\") !== false) {
            return is_file($bin);
        }
        return true;
    }

    // ---------------------------------------------------------------------
    // FEED  (paginated, newest first)
    // ---------------------------------------------------------------------
    public function Feed(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }

        $viewerId = (int) Auth::id();
        $perPage = min(max((int) $request->input('per_page', 10), 1), 30);
        $page = max((int) $request->input('page', 1), 1);

        $videos = $this->buildFeedPage($page, $perPage);

        $likedIds = ShortVideoLike::where('user_id', $viewerId)
            ->whereIn('video_id', $videos->pluck('id'))
            ->pluck('video_id')->all();

        $data = $videos->map(fn ($v) => $this->shapeVideo($v, $viewerId, $likedIds))->values();

        return $this->reply([
            'message' => 'Data Found! ',
            'code' => '200',
            'page' => $page,
            'has_more' => $videos->count() === $perPage ? 1 : 0,
            'videos' => $data,
        ]);
    }

    // ---------------------------------------------------------------------
    // MY VIDEOS (for the profile "Moments" grid)
    // ---------------------------------------------------------------------
    public function MyVideos(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $targetId = (int) $request->input('target_id', Auth::id());
        $videos = ShortVideo::with('user')
            ->where('user_id', $targetId)
            ->where('status', 'active')
            ->orderByDesc('id')->limit(60)->get();
        $data = $videos->map(fn ($v) => $this->shapeVideo($v, (int) Auth::id()))->values();
        return $this->reply(['message' => 'Data Found! ', 'code' => '200', 'videos' => $data]);
    }

    // ---------------------------------------------------------------------
    // LIKE (toggle)
    // ---------------------------------------------------------------------
    public function Like(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $videoId = (int) $request->input('video_id');
        $userId = (int) Auth::id();
        $video = ShortVideo::find($videoId);
        if (!$video) {
            return $this->reply(['message' => 'Video not found', 'code' => '404']);
        }

        $liked = false;
        DB::transaction(function () use ($videoId, $userId, $video, &$liked) {
            $existing = ShortVideoLike::where('video_id', $videoId)->where('user_id', $userId)->first();
            if ($existing) {
                $existing->delete();
                $video->decrement('likes_count');
                $liked = false;
            } else {
                ShortVideoLike::create(['video_id' => $videoId, 'user_id' => $userId]);
                $video->increment('likes_count');
                $liked = true;
            }
        });

        return $this->reply([
            'message' => 'OK',
            'code' => '200',
            'is_liked' => $liked ? 1 : 0,
            'likes_count' => (int) $video->fresh()->likes_count,
        ]);
    }

    // ---------------------------------------------------------------------
    // VIEW (counted once per user)
    // ---------------------------------------------------------------------
    public function View(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $videoId = (int) $request->input('video_id');
        $userId = (int) Auth::id();
        $video = ShortVideo::find($videoId);
        if (!$video) {
            return $this->reply(['message' => 'Video not found', 'code' => '404']);
        }
        $inserted = DB::table('short_video_views')->insertOrIgnore([
            'video_id' => $videoId,
            'user_id' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        if ($inserted) {
            $video->increment('views_count');
        }
        return $this->reply([
            'message' => 'OK',
            'code' => '200',
            'views_count' => (int) $video->fresh()->views_count,
        ]);
    }

    // ---------------------------------------------------------------------
    // COMMENTS list + add
    // ---------------------------------------------------------------------
    public function Comments(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $videoId = (int) $request->input('video_id');
        $page = max((int) $request->input('page', 1), 1);
        $rows = ShortVideoComment::with('user')
            ->where('video_id', $videoId)
            ->orderByDesc('id')
            ->forPage($page, 30)->get();

        $data = $rows->map(function ($c) {
            return [
                'id' => $c->id,
                'user_id' => $c->user_id,
                'user_name' => $c->user->name ?? 'QueenLive User',
                'user_profile' => $c->user->profile ?? '',
                'user_level' => (string) ($c->user->level ?? '0'),
                'comment' => $c->comment,
                'created_at' => optional($c->created_at)->toIso8601String(),
            ];
        })->values();

        return $this->reply(['message' => 'Data Found! ', 'code' => '200', 'comments' => $data]);
    }

    public function CommentAdd(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $videoId = (int) $request->input('video_id');
        $text = trim((string) $request->input('comment', ''));
        if ($text === '') {
            return $this->reply(['message' => 'Empty comment', 'code' => '422']);
        }
        $video = ShortVideo::find($videoId);
        if (!$video) {
            return $this->reply(['message' => 'Video not found', 'code' => '404']);
        }
        $userId = (int) Auth::id();
        $comment = ShortVideoComment::create([
            'video_id' => $videoId,
            'user_id' => $userId,
            'comment' => mb_substr($text, 0, 1000),
        ]);
        $video->increment('comments_count');
        $me = User::find($userId);

        return $this->reply([
            'message' => 'OK',
            'code' => '200',
            'comments_count' => (int) $video->fresh()->comments_count,
            'comment' => [
                'id' => $comment->id,
                'user_id' => $userId,
                'user_name' => $me->name ?? 'QueenLive User',
                'user_profile' => $me->profile ?? '',
                'user_level' => (string) ($me->level ?? '0'),
                'comment' => $comment->comment,
                'created_at' => optional($comment->created_at)->toIso8601String(),
            ],
        ]);
    }

    // ---------------------------------------------------------------------
    // GIFT (send coins on a video; deduct sender, credit author)
    // ---------------------------------------------------------------------
    public function Gift(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $videoId = (int) $request->input('video_id');
        $giftId = (int) $request->input('gift_id', 0);
        $coin = (int) $request->input('coin', 0);
        $qty = max((int) $request->input('quantity', 1), 1);
        $total = $coin * $qty;

        if ($total <= 0) {
            return $this->reply(['message' => 'Invalid gift', 'code' => '422']);
        }

        $video = ShortVideo::find($videoId);
        if (!$video) {
            return $this->reply(['message' => 'Video not found', 'code' => '404']);
        }
        $senderId = (int) Auth::id();
        $receiverId = (int) $video->user_id;
        if ($senderId === $receiverId) {
            return $this->reply(['message' => 'Cannot gift your own video', 'code' => '422']);
        }

        $giftName = trim((string) $request->input('gift_name', ''));
        if ($giftName === '') {
            $giftName = 'Moments Gift';
        }

        $result = ['ok' => false, 'balance' => 0, 'gift_value' => 0];
        try {
            DB::transaction(function () use ($senderId, $receiverId, $videoId, $giftId, $coin, $qty, $total, $giftName, $video, &$result) {
                // Lock ONLY the sender row — receiver earnings are derived
                // from SUM(gifts) so no row to lock for credit.
                $sender = User::lockForUpdate()->find($senderId);
                if (!$sender) {
                    return;
                }
                if ((int) $sender->balance < $total) {
                    $result['balance'] = (int) $sender->balance;
                    $result['insufficient'] = true;
                    return;
                }

                // 1) Debit sender (spendable wallet).
                $sender->balance = (int) $sender->balance - $total;
                $sender->total_sanding = (int) $sender->total_sanding + $total;
                $sender->save();

                // 2) Credit receiver via the unified gifts pool — this is
                //    what the rest of the app SUMs to compute host earnings
                //    on the Exchange / Withdraw / wallet screens.
                $sharedGift = new \App\Models\Gift();
                $sharedGift->sander_id = $senderId;
                $sharedGift->reciever_id = $receiverId;
                $sharedGift->name = $giftName;
                $sharedGift->value = $total;
                $sharedGift->channelName = 'moments_' . $videoId;
                $sharedGift->date = \Carbon\Carbon::now();
                $sharedGift->save();

                // 3) Per-video log (for Moments feed counts).
                ShortVideoGift::create([
                    'video_id' => $videoId,
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'gift_id' => $giftId,
                    'coin' => $coin,
                    'quantity' => $qty,
                ]);

                $video->increment('gifts_count', $qty);
                $video->increment('gift_value', $total);

                $result['ok'] = true;
                $result['balance'] = (int) $sender->balance;
                $result['gift_value'] = (int) $video->fresh()->gift_value;
            });
        } catch (\Throwable $e) {
            Log::error('[shortvideo] gift failed: ' . $e->getMessage());
            return $this->reply(['message' => 'Gift failed', 'code' => '500']);
        }

        if (!empty($result['insufficient'])) {
            return $this->reply(['message' => 'Insufficient balance', 'code' => '402', 'balance' => $result['balance']]);
        }
        if (!$result['ok']) {
            return $this->reply(['message' => 'Gift failed', 'code' => '500']);
        }

        return $this->reply([
            'message' => 'OK',
            'code' => '200',
            'balance' => $result['balance'],
            'gift_value' => $result['gift_value'],
        ]);
    }

    // ---------------------------------------------------------------------
    // DELETE (soft)
    // ---------------------------------------------------------------------
    public function Delete(Request $request)
    {
        if (!$this->guard($request)) {
            return $this->unauthorized();
        }
        $video = ShortVideo::find((int) $request->input('video_id'));
        if (!$video || (int) $video->user_id !== (int) Auth::id()) {
            return $this->reply(['message' => 'Not allowed', 'code' => '403']);
        }
        $video->status = 'deleted';
        $video->save();
        return $this->reply(['message' => 'Deleted', 'code' => '200']);
    }
}
