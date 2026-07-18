<?php

namespace App\Http\Controllers;

use App\Events\TestWebSocket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocketController extends Controller
{
    public function index(Request $request)
    {
        $socketConfig = [
            'app_key'       => (string) env('PUSHER_APP_KEY', ''),
            'host'          => 'queenlive.site',
            'ws_port'       => 80,
            'wss_port'      => 443,
            'scheme'        => 'https',
            'channel'       => 'bd_chat',
            'event'         => 'BDEvent',
            'send_route'    => route('socket.send'),
            'health_route'  => route('socket.health'),
            'page_protocol' => $request->isSecure() ? 'https:' : 'http:',
        ];

        return view('test', compact('socketConfig'));
    }

    public function send(Request $request): JsonResponse
    {
        $message = (string) $request->input('message', 'Hello from SocketController @ ' . now()->toDateTimeString());

        try {
            event(new TestWebSocket($message));

            return response()->json([
                'success' => true,
                'message' => 'TestWebSocket event dispatched successfully.',
                'data'    => [
                    'sent_message'      => $message,
                    'channel'           => 'bd_chat',
                    'event'             => 'BDEvent',
                    'time'              => now()->toDateTimeString(),
                    'broadcast_driver'  => config('broadcasting.default'),
                    'internal_host'     => env('PUSHER_HOST', '127.0.0.1'),
                    'internal_port'     => env('PUSHER_PORT', 6001),
                    'internal_scheme'   => env('PUSHER_SCHEME', 'http'),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Socket test send failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Event send failed.',
                'error'   => [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function health(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'server'  => [
                'app_url'          => config('app.url'),
                'request_host'     => $request->getHost(),
                'request_secure'   => $request->isSecure(),
                'broadcast_driver' => config('broadcasting.default'),
                'queue_connection' => config('queue.default'),
                'now'              => now()->toDateTimeString(),
            ],
            'laravel_internal_pusher' => [
                'app_id'   => (string) env('PUSHER_APP_ID', ''),
                'app_key'  => (string) env('PUSHER_APP_KEY', ''),
                'host'     => (string) env('PUSHER_HOST', '127.0.0.1'),
                'port'     => (int) env('PUSHER_PORT', 6001),
                'scheme'   => (string) env('PUSHER_SCHEME', 'http'),
                'cluster'  => (string) env('PUSHER_APP_CLUSTER', 'mt1'),
            ],
            'browser_public_socket' => [
                'host'     => 'queenlive.site',
                'ws_port'  => 80,
                'wss_port' => 443,
                'scheme'   => 'https',
            ],
            'test' => [
                'channel' => 'bd_chat',
                'event'   => 'BDEvent',
            ],
            'hints' => [
                'Laravel internal uses 127.0.0.1:6001 via http',
                'Browser uses queenlive.site via ws:80 / wss:443',
                'LiteSpeed reverse proxy must map /app/ to ws://127.0.0.1:6001/app/',
                'If send works but receive fails, proxy or browser-side host/port is wrong',
            ],
        ]);
    }
}