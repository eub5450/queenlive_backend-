<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Services\FeedDiscoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * v5/feed/sections — Smarter Feed / Discovery Rows (feature #9, Boss 2026-07-07).
 *
 * Sanctum + check.ban middleware (see routes/api.php) plus the static
 * access_token body check the other v5 controllers use. Returns the sectioned
 * discovery rows the home page renders above the flat grid. The client falls
 * back to its existing flat feed on any non-200, so failures here are silent to
 * the user.
 */
class FeedDiscoveryController extends Controller
{
    private FeedDiscoveryService $service;

    public function __construct()
    {
        $this->service = new FeedDiscoveryService();
    }

    public function sections(Request $request): JsonResponse
    {
        $body = $this->body($request);
        if (!$this->authGate($body)) {
            return $this->fail('401', 'Unauthorized access_token');
        }
        try {
            $viewerId = (string) ($body['user_id'] ?? $body['userId'] ?? '');
            $lat = $this->floatOrNull($body['lat'] ?? $body['latitude'] ?? null);
            $lng = $this->floatOrNull($body['lng'] ?? $body['lon'] ?? $body['longitude'] ?? null);

            $sections = $this->service->sections($viewerId, $lat, $lng);
            return $this->ok(['sections' => $sections]);
        } catch (Throwable $e) {
            Log::warning('feed.sections.failed', ['msg' => $e->getMessage()]);
            // Return an empty section list (still code 200) rather than a 500 so
            // a transient failure degrades to "no rows" instead of an error the
            // client would have to special-case.
            return $this->ok(['sections' => []]);
        }
    }

    // ---- helpers ----

    private function body(Request $request): array
    {
        $body = $request->all();
        if (empty($body)) {
            $raw = $request->getContent();
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $body = $decoded;
                }
            }
        }
        return $body;
    }

    private function authGate(array $body): bool
    {
        $token = (string) ($body['access_token'] ?? '');
        return $token === '0411f0028cfb768b3a3d96ac3aa37dw3e5';
    }

    private function floatOrNull($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function ok(array $payload): JsonResponse
    {
        return response()->json([array_merge(['code' => '200'], $payload)]);
    }

    private function fail(string $code, string $message): JsonResponse
    {
        return response()->json([['code' => $code, 'message' => $message]]);
    }
}
