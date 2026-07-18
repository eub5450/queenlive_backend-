<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V5\Concerns\AdminActorAuthorization;
use App\Models\Help;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSupportController extends Controller
{
    use AdminActorAuthorization;

    public function index(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

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

            return [
                'user_id' => $userId,
                'user' => $users[$userId] ?? null,
                'messages' => $sorted,
                'latest' => $latest,
                'pending' => $pending,
                'last_at' => optional($latest)->updated_at ?: optional($latest)->created_at,
            ];
        })->sortByDesc(function ($thread) {
            return optional($thread['last_at'])->timestamp ?? 0;
        })->values();

        return $this->success('Support data loaded successfully', [
            'data' => [
                'tickets' => $data,
                'pending_count' => $pendingCount,
                'threads' => $threads,
            ],
        ]);
    }

    public function reply(Request $request, $id = null)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $helpId = $id ?: $request->id ?: $request->help_id;
        $replyBody = trim((string) ($request->replay ?? $request->reply ?? ''));

        if ($replyBody === '') {
            return $this->error('Reply message is required', '422');
        }

        $replay = Help::find($helpId);
        if (!$replay) {
            return $this->error('Support ticket not found', '404');
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
        $this->sendFcmReply($replay->user_id, $comment);

        return $this->success('Replay Successfully Submit', [
            'data' => $replay->fresh(),
        ]);
    }

    private function forgetNotificationCache($userId)
    {
        Cache::forget("notifications_{$userId}");
        Cache::forget("notifications_v2_{$userId}");
    }

    private function sendFcmReply($userId, $comment)
    {
        $user = User::find($userId);
        if (!$user || empty($user->device_id)) {
            return;
        }

        $data = array(
            'to' => $user->device_id,
            'data' => array(
                'link' => 'https://lindaapp.in/new_live/share/v2/22441/760b23bf21f945daadedcf29b790e52e/1',
            ),
            'notification' => array(
                'body' => $comment,
                'title' => 'BPLive Support Response',
                'click_action' => 'deviceNoti',
            ),
        );

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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: key=AAAAd7PU44s:APA91bEjM26iXg_0tksuEmwQ5N6UBAWCdsm01Ym66dI3IYeHo92JBMOjB4VWyGsnZbWRqIvFkJqxxCOYI7FOhnWWzYBT9hSd3eic2S3RNJ5C8jqphRDpjp2EYEUKNLiDQtnfKhKD_edK',
            ),
        ));
        curl_exec($curl);
        curl_close($curl);
    }
}
