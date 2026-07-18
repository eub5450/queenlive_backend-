<?php

namespace App\Http\Controllers\Api\V5\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait AdminActorAuthorization
{
    protected function authorizedActor(Request $request)
    {
        if ((string) $request->access_token !== $this->adminAccessToken()) {
            return null;
        }

        $actorId = $request->actor_id ?? $request->admin_id ?? $request->user_id;
        if (empty($actorId)) {
            return null;
        }

        $actor = User::find($actorId);
        if (!$actor) {
            return null;
        }

        $isAdmin = (int) ($actor->is_admin ?? 0) >= 1;
        $isBdAdmin = (int) ($actor->is_bd_admin ?? 0) === 1;
        $isOfficial = (int) ($actor->is_official_id ?? 0) !== 0;

        return ($isAdmin || $isBdAdmin || $isOfficial) ? $actor : null;
    }

    protected function unauthorized()
    {
        return response()->json([['message' => 'Unauthorized', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected function adminAccessToken()
    {
        return '0411f0028cfb768b3a3d96ac3aa37dw3e5';
    }

    protected function success($message, array $data = array(), $code = '200')
    {
        return response()->json([array_merge([
            'message' => $message,
            'code' => (string) $code,
        ], $data)], 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected function error($message, $code = '400', array $data = array())
    {
        return response()->json([array_merge([
            'message' => $message,
            'code' => (string) $code,
        ], $data)], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
