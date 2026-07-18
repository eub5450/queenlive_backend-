<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\BanDevice;
use Auth;
class CheckUserBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
    
        if ($user) {
            if ($user->isBanned()) {
                return response()->json([
                    'message' => 'Device Banned',
                    'code' => '401'
                ], 401);
            }
    
            if ($user->isBanned()) {
              //  Auth::logout();
                return response()->json([
                    'message' => $user->getBanMessage(),
                    'code' => '404'
                ], 404);
            }
        }
    
        return $next($request);
    }
}
