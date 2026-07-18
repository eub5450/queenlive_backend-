<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'OAuth web integration is not configured',
            'code' => 503,
        ], 503);
    }

    public function callback(Request $request)
    {
        return response()->json([
            'message' => 'OAuth callback is not configured',
            'code' => 503,
        ], 503);
    }

    public function status(Request $request)
    {
        return response()->json([
            'configured' => false,
            'message' => 'OAuth web integration is not configured',
            'code' => 503,
        ], 503);
    }

    public function logout(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'OAuth web session cleared',
            'code' => 200,
        ]);
    }
}
