<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Server-authoritative nude / adult image check for QueenLive uploads
 * (profile photo, room background, go-live avatar).
 *
 * The Flutter client POSTs the picked image here BEFORE it uploads it. This
 * proxy forwards the image to the shared Lovebo NudeNet moderation service
 * (kept server-side so the moderation key never ships in the app) and returns
 * a simple verdict. It is intentionally FAIL-OPEN: any unconfigured /
 * unreachable / non-adult outcome returns allow=true, because the client keeps
 * its own on-device gate as the backstop and a moderation outage must never
 * block every user's uploads. Only a definite "adult" verdict blocks.
 */
class ImageModerationController extends Controller
{
    private const APP_ACCESS_TOKEN = '0411f0028cfb768b3a3d96ac3aa37dw3e5';

    public function moderate(Request $request)
    {
        // Reject casual abuse of the compute endpoint; the real gate is the
        // upstream key. Mirrors the access_token check used across the API.
        if ((string) $request->input('access_token') !== self::APP_ACCESS_TOKEN) {
            return $this->allow('bad_access_token');
        }

        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return $this->allow('no_file');
        }

        $endpoint = rtrim((string) env('LOVEBO_MODERATION_URL', 'https://fariaislamwasifa.xyz/api/bdlive/moderate_image'), '/');
        $key = (string) env('LOVEBO_MODERATION_KEY', '');
        if ($key === '') {
            // Not wired up on this node yet -> device gate still applies.
            return $this->allow('unconfigured');
        }

        $file = $request->file('file');

        try {
            $response = Http::withHeaders(['X-BDLive-Key' => $key])
                ->timeout(15)
                ->connectTimeout(6)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName() ?: 'upload.jpg'
                )
                ->post($endpoint);
        } catch (\Throwable $e) {
            return $this->allow('unreachable');
        }

        if (!$response->ok()) {
            return $this->allow('bad_status');
        }

        $body = $response->json();
        $verdict = is_array($body) ? (string) ($body['verdict'] ?? 'safe') : 'safe';

        // Block ONLY on a definite adult verdict. "error"/anything else -> allow.
        $allow = $verdict !== 'adult';

        return response()->json([
            'verdict' => $verdict,
            'allow'   => $allow,
            'score'   => is_array($body) ? ($body['score'] ?? 0) : 0,
            'hit'     => is_array($body) ? ($body['hit'] ?? null) : null,
        ]);
    }

    private function allow(string $reason)
    {
        return response()->json([
            'verdict' => 'safe',
            'allow'   => true,
            'score'   => 0,
            'hit'     => null,
            'reason'  => $reason,
        ]);
    }
}
