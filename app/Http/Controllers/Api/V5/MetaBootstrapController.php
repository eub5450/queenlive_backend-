<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Services\V5\MetaBootstrapService;
use Illuminate\Http\Request;

/**
 * v5 meta bootstrap.
 *
 * Returns one envelope containing every rarely-changing meta block the app
 * pulls at launch (settings/sliders/host-types/power-rules/level-rules/gift-list/
 * store-items/badwords/agora/shortlinks) plus a `meta_version` etag.
 *
 * Client sends `If-Meta-Version: <hash>` on subsequent launches; if it matches
 * the current version we respond `{ ok: true, unchanged: true, meta_version }`
 * with HTTP 200 (304 bodies are unreliable on some Dio configs).
 *
 * Additive only - does NOT touch existing setting_info / slider / gift_file_data
 * / store_items / comment_skip_word_list / lavel_list endpoints. They keep
 * working for legacy clients.
 *
 * Boss 2026-06-28 (Agent Q design, Agent Z build).
 */
class MetaBootstrapController extends Controller
{
    public function bootstrap(Request $request, MetaBootstrapService $service)
    {
        $clientVersion = trim((string) $request->header('If-Meta-Version', ''));
        $payload = $service->bootstrap($clientVersion !== '' ? $clientVersion : null);

        $status = 200;
        $response = response()->json($payload, $status, [], JSON_UNESCAPED_UNICODE);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        $response->header('Cache-Control', 'private, max-age=30');
        $response->header('X-Meta-Version', $payload['meta_version'] ?? '');
        return $response;
    }
}
