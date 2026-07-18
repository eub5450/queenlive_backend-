<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class AudioRoomBroadcastAuthController extends Controller
{
    const LEGACY_ACCESS_TOKEN = '0411f0028cfb768b3a3d96ac3aa37dw3e5';

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'check.ban']);
    }

    public function authenticate(Request $request)
    {
        if ($request->access_token !== self::LEGACY_ACCESS_TOKEN) {
            return response()->json([
                'message' => 'Unauthorized access_token',
                'code' => '401',
            ], 401);
        }

        $user = $request->user('sanctum') ?? $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'code' => '401',
            ], 401);
        }

        $request->setUserResolver(static function () use ($user) {
            return $user;
        });

        return Broadcast::auth($request);
    }
}
