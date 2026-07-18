<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriveController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->view('drive-backups', [
            'databaseBackups' => [],
            'fullBackups' => [],
        ]);
    }

    public function status(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Drive backup integration is not configured',
            'databaseBackups' => 0,
            'fullBackups' => 0,
            'code' => 503,
        ], 503);
    }

    public function upload(Request $request, $type)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Drive upload is not configured',
            'type' => (string) $type,
            'code' => 503,
        ], 503);
    }

    public function test(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Drive connection is not configured',
            'code' => 503,
        ], 503);
    }

    public function createFolders(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Authentication required',
                'code' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Drive folder creation is not configured',
            'code' => 503,
        ], 503);
    }
}
