<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getMessage($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        if (!ctype_digit((string) $id)) {
            return response()->json([
                'message' => 'Invalid message id',
                'code' => 422,
            ], 422);
        }

        return response()->json([
            'message' => 'Author chat endpoint is not available',
            'code' => 404,
        ], 404);
    }

    public function sendMessage(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Author chat endpoint is not available',
            'code' => 404,
        ], 404);
    }
}
