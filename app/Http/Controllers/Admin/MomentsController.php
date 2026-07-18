<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortVideo;
use App\Models\ShortVideoComment;
use App\Models\ShortVideoGift;
use App\Models\ShortVideoLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MomentsController extends Controller
{
    public function Index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $videos = ShortVideo::with('user')
            ->where('status', '!=', 'deleted')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('id', $search)
                        ->orWhere('user_id', $search)
                        ->orWhere('caption', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        return view('backend.moments.index', compact('videos', 'search'));
    }

    public function Show($id)
    {
        $video = ShortVideo::with('user')->findOrFail($id);
        $comments = ShortVideoComment::with('user')
            ->where('video_id', $video->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $gifts = ShortVideoGift::where('video_id', $video->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('backend.moments.show', compact('video', 'comments', 'gifts'));
    }

    public function Destroy($id)
    {
        $video = ShortVideo::findOrFail($id);

        DB::transaction(function () use ($video) {
            ShortVideoLike::where('video_id', $video->id)->delete();
            ShortVideoComment::where('video_id', $video->id)->delete();
            DB::table('short_video_views')->where('video_id', $video->id)->delete();
            ShortVideoGift::where('video_id', $video->id)->delete();

            $video->status = 'deleted';
            $video->save();
        });

        $this->deleteMediaFile((string) $video->video_url);
        $this->deleteMediaFile((string) $video->thumb_url);

        $notification = [
            'messege' => 'Moment removed successfully!',
            'alert-type' => 'success',
        ];

        return redirect('admin/moments-list')->with($notification);
    }

    private function deleteMediaFile(string $url): void
    {
        $url = trim($url);
        if ($url === '') {
            return;
        }

        $storagePath = rtrim((string) config('shortvideo.storage_path'), '/\\');
        if ($storagePath === '') {
            return;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $filename = basename($path);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        $fullPath = $storagePath . DIRECTORY_SEPARATOR . $filename;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
