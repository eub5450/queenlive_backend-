<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TaskApiHelper
{
    public function validateDashboard(Request $request): array
    {
        return Validator::make($request->all(), [
            'access_token' => ['nullable', 'string'],
            'user_id' => ['required', 'integer', 'min:1'],
        ])->validate();
    }

    public function validateClaim(Request $request): array
    {
        return Validator::make($request->all(), [
            'access_token' => ['nullable', 'string'],
            'user_id' => ['required', 'integer', 'min:1'],
            'task_key' => ['required', 'string', 'max:120'],
        ])->validate();
    }

    public function resolveAuthenticatedUser(Request $request, array $payload): User
    {
        $userId = (int) ($payload['user_id'] ?? 0);
        $bearerToken = trim((string) $request->bearerToken());

        if ($userId <= 0) {
            throw ValidationException::withMessages([
                'user_id' => ['Invalid user id.'],
            ]);
        }

        if ($bearerToken === '') {
            throw new RuntimeException('Missing bearer token.');
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('id', $userId)
            ->where('api_token', $bearerToken)
            ->first();

        if ($user === null) {
            throw new RuntimeException('User session is not valid.');
        }

        return $user;
    }

    public function buildAuditMeta(Request $request): array
    {
        return [
            'claim_ip' => $request->ip(),
            'claim_user_agent' => substr((string) $request->userAgent(), 0, 500),
        ];
    }
}
