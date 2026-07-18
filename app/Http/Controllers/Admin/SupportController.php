<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Help;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use DB;

class SupportController extends Controller
{
    public function Index()
    {
        $data = Help::orderByRaw('status = 0 DESC')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $users = User::whereIn('id', $data->pluck('user_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $pendingCount = $data->where('status', 0)->count();
        $threads = $data->groupBy('user_id')->map(function ($items, $userId) use ($users) {
            $sorted = $items->sortBy('created_at')->values();
            $latest = $sorted->last();
            $pending = $items->where('status', 0)->count();

            return (object) [
                'user_id' => $userId,
                'user' => $users[$userId] ?? null,
                'messages' => $sorted,
                'latest' => $latest,
                'pending' => $pending,
                'last_at' => optional($latest)->updated_at ?: optional($latest)->created_at,
            ];
        })->sortByDesc(function ($thread) {
            return optional($thread->last_at)->timestamp ?? 0;
        })->values();

        return view('backend.support', compact('data', 'users', 'pendingCount', 'threads'));
    }

    public function Replay($id,Request $request)
    {
        $replyBody = trim((string) $request->replay);
        if ($replyBody === '') {
            return Redirect()->back()->with([
                'messege' => 'Reply message is required',
                'alert-type' => 'error',
            ]);
        }

        $replay = Help::find($id);
        if (!$replay) {
            return Redirect()->back()->with([
                'messege' => 'Support ticket not found',
                'alert-type' => 'error',
            ]);
        }

        $comment = 'Help Desk Reply: ' . $replyBody . ' -- Best Regards - Support Team - QueenLive';

        DB::transaction(function () use ($replay, $comment) {
            $notification = Notification::create([
                'user_id' => $replay->user_id,
                'title' => 'Help Desk Reply',
                'date' => date('Y-m-d'),
                'message' => $comment,
                'notification_type' => 'help_desk',
                'accent_color' => '#FF5AA5',
                'is_read' => 0,
                'read_at' => null,
                'help_id' => $replay->id,
            ]);

            $replay->replay = $comment;
            $replay->status = 1;
            $replay->reply_notification_id = $notification->id;
            $replay->replied_at = Carbon::now();
            $replay->save();
        });

        $this->forgetNotificationCache($replay->user_id);

        $user = User::find($replay->user_id);
        if ($user && !empty($user->device_id)) {
            $data = array(
                "to" => $user->device_id,
                "data" => array(
                    "link" => "https://lindaapp.in/new_live/share/v2/22441/760b23bf21f945daadedcf29b790e52e/1"
                ),
                "notification" => array(
                    "body" => $comment,
                    "title" => "BPLive Support Response",
                    "click_action" => "deviceNoti"
                )
            );
            $payload = json_encode($data);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: key=AAAAd7PU44s:APA91bEjM26iXg_0tksuEmwQ5N6UBAWCdsm01Ym66dI3IYeHo92JBMOjB4VWyGsnZbWRqIvFkJqxxCOYI7FOhnWWzYBT9hSd3eic2S3RNJ5C8jqphRDpjp2EYEUKNLiDQtnfKhKD_edK'
                ),
            ));

            curl_exec($curl);
            curl_close($curl);
        }

        $notification = array(
            'messege' => 'Replay Successfully Submit',
            'alert-type' => 'success'
        );

        return Redirect()->back()->with($notification);
    }

    private function forgetNotificationCache($userId)
    {
        Cache::forget("notifications_{$userId}");
        Cache::forget("notifications_v2_{$userId}");
    }
}
