<?php

namespace App\Http\Controllers;

use App\Services\LiveKitService;
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
use Auth;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\PerformanceLogger;
use Illuminate\Support\Facades\Redis;
class StreamController extends Controller
{
    protected $livekitService;

    public function __construct(LiveKitService $livekitService)
    {
        $this->livekitService = $livekitService;
    }

    public function startStream(Request $request)
    {
        $roomName = $request->input('room_name');
        $identity = $request->input('identity');

        // Create room
        $this->livekitService->createRoom($roomName);

        // Generate access token
        $token = $this->livekitService->createAccessToken($roomName, $identity);

        return response()->json([
            'token' => $token,
            'room' => $roomName
        ]);
    }

    public function joinStream(Request $request)
    {
        $roomName = $request->input('room_name');
        $identity = $request->input('identity');

        // Generate access token for joining
        $token = $this->livekitService->createAccessToken($roomName, $identity);

        return response()->json([
            'token' => $token,
            'room' => $roomName
        ]);
    }
  
}
