<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLive;
use Carbon;
use DB;
use App\Models\LiveCall;
use App\Models\Comment;
use App\Models\User;
use App\Models\Gift;
use App\Models\AudienceJoin;
use App\Models\DayTime;
use App\Models\BanDevice;
use App\Models\DeviceLockInvite;
use App\Models\Slider;
use App\Models\Setting;
use App\Models\BedWord;

class HomeController extends Controller
{
    public function HomeIndex(Request $request)
    {
        $response = [];
        $token = $request->access_token;
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, ['options' => JSON_UNESCAPED_UNICODE]);
        }

        $host_id = $request->host_id;
        $limit = max(1, min((int) $request->get('limit', 50), 100));

        $lives = $this->orderedLiveQuery()
            ->select($this->liveColumns())
            ->orderByRaw("CASE WHEN users.id = ? THEN 0 ELSE 1 END", [$host_id])
            ->limit($limit)
            ->get();

        array_push($response, array('message' => 'Global Data  Successfully ', 'lives' => $lives, 'code' => '200'));
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function LivesNowIndex(Request $request)
    {
        $token = $request->access_token;
        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                ['message' => 'Unauthorized', 'code' => '401']
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $limit = max(1, min((int) $request->get('limit', 50), 100));
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $limit;

        $topRows = $this->orderedLiveQuery()
            ->select(array_merge(['user_lives.id as live_row_id'], $this->liveColumns()))
            ->limit(2)
            ->get();

        $topLiveIds = $topRows->pluck('live_row_id')->all();
        $topLive = $topRows->map(function ($row) {
            unset($row->live_row_id);
            return $row;
        })->values();

        $liveQuery = $this->orderedLiveQuery()->select($this->liveColumns());
        if (!empty($topLiveIds)) {
            $liveQuery->whereNotIn('user_lives.id', $topLiveIds);
        }

        $lives = $liveQuery
            ->skip($offset)
            ->take($limit)
            ->get();

        $response = [
            'message' => 'Home Page Data Show',
            'top_live' => $topLive,
            'lives' => $lives,
            'code' => '200',
        ];

        return response()->json([$response], 200, ['options' => JSON_UNESCAPED_UNICODE]);
    }

    private function orderedLiveQuery()
    {
        return DB::table('user_lives')
            ->join('users', 'users.id', '=', 'user_lives.user_id')
            ->where('user_lives.pin', 0)
            ->orderByDesc('user_lives.type')
            ->orderByDesc('user_lives.top_value')
            ->orderByDesc('user_lives.id');
    }

    private function liveColumns()
    {
        return [
            'users.name',
            'users.id',
            'users.level',
            'users.balance',
            'users.profile',
            'user_lives.token',
            'user_lives.channelName',
            'user_lives.type',
            'user_lives.backgorund',
            'user_lives.notice',
            'user_lives.bullet_notice',
            'user_lives.pin',
            'user_lives.audio_brd_design',
            'users.host_badge',
            'user_lives.avatar',
            'user_lives.sdk',
        ];
    }
}
