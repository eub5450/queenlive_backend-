<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class AudioRoomBroadcastAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'check.ban']);
    }

    public function authenticate(Request $request)
    {
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
