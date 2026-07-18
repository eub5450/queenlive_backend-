<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

trait ApiResponseTrait
{
    protected function success(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status);
    }

    protected function error(Throwable $throwable): JsonResponse
    {
        $status = 422;
        $message = $throwable->getMessage();

        if ($throwable instanceof ValidationException) {
            $message = $throwable->validator->errors()->first() ?: 'Validation failed.';
        } elseif ($throwable instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Requested resource was not found.';
        } elseif ($message === '') {
            $message = 'Request failed.';
        }

        return response()->json([
            'code' => (string) $status,
            'message' => $message,
        ], $status);
    }
}
