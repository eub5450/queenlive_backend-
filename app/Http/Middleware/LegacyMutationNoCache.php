<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Route alias: `legacy.mutation.no_cache`.
 *
 * Referenced by 32 v4 mutation routes (user_live_remove, audio_co_host_request,
 * audio_call_accept, audio_gift_push, video_gift_push, ...) but the class +
 * alias were never deployed in the 2026-06-15 release, so every one of those
 * routes threw BindingResolutionException -> HTTP 500 (2000+ errors/log window).
 *
 * Intent (from the name): mark these mutation responses as non-cacheable so the
 * edge/CDN/proxies never serve a stale mutation result. Pure pass-through that
 * only adds no-store headers — no behavioural change to the controllers.
 */
class LegacyMutationNoCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (is_object($response) && method_exists($response, 'header')) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}
