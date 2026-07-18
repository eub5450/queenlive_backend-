<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Support\TaskApiHelper;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TaskController extends Controller
{
    use ApiResponseTrait;

    /** @var TaskService */
    protected $taskService;

    /** @var TaskApiHelper */
    protected $taskApiHelper;

    public function __construct(TaskService $taskService, TaskApiHelper $taskApiHelper)
    {
        $this->taskService = $taskService;
        $this->taskApiHelper = $taskApiHelper;
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $payload = $this->taskApiHelper->validateDashboard($request);
            $user = $this->taskApiHelper->resolveAuthenticatedUser($request, $payload);

            return $this->success(
                $this->taskService->buildDashboard($user)
            );
        } catch (Throwable $throwable) {
            return $this->error($throwable);
        }
    }

    public function claim(Request $request): JsonResponse
    {
        try {
            $payload = $this->taskApiHelper->validateClaim($request);
            $user = $this->taskApiHelper->resolveAuthenticatedUser($request, $payload);

            return $this->success(
                $this->taskService->claim(
                    $user,
                    (string) $payload['task_key'],
                    $this->taskApiHelper->buildAuditMeta($request)
                )
            );
        } catch (Throwable $throwable) {
            return $this->error($throwable);
        }
    }

    public function checkinClaim(Request $request): JsonResponse
    {
        try {
            $payload = $this->taskApiHelper->validateDashboard($request);
            $user = $this->taskApiHelper->resolveAuthenticatedUser($request, $payload);

            return $this->success(
                app(\App\Services\CheckinService::class)->claim($user)
            );
        } catch (Throwable $throwable) {
            return $this->error($throwable);
        }
    }
}
