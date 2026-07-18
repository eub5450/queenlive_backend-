<?php

namespace App\Services;

use App\Models\TaskClaim;
use App\Models\TaskDefinition;
use App\Models\TaskProgress;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class TaskService
{
    public function buildDashboard(User $user): array
    {
        $definitions = TaskDefinition::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $tasks = [];
        $claimableCount = 0;
        $claimableRewardTotal = 0;
        $claimedToday = 0;

        foreach ($definitions as $definition) {
            $task = $this->resolveTaskState($user, $definition);
            $tasks[] = $task;

            if ($task['claimable'] === true) {
                $claimableCount++;
                $claimableRewardTotal += (int) $task['reward_amount'];
            }

            if (
                $task['claimed'] === true &&
                $definition->recurrence === TaskDefinition::RECURRENCE_DAILY
            ) {
                $claimedToday++;
            }
        }

        return [
            'code' => '200',
            'message' => 'Task dashboard loaded',
            'summary' => [
                'claimable_count' => $claimableCount,
                'claimed_today' => $claimedToday,
                'total_task_reward_earned' => $this->claimedRewardTotal((int) $user->id),
                'available_task_reward_balance' => $claimableRewardTotal,
                'wallet_balance' => (int) ($user->balance ?? 0),
            ],
            'security' => [
                'synced_by' => 'user_id',
                'device_scoped' => false,
            ],
            'tasks' => $tasks,
            'checkin' => app(\App\Services\CheckinService::class)->buildBlock($user),
        ];
    }

    public function claim(User $user, string $taskKey, array $auditMeta = []): array
    {
        return DB::transaction(function () use ($user, $taskKey, $auditMeta): array {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            /** @var TaskDefinition $definition */
            $definition = TaskDefinition::query()
                ->where('task_key', $taskKey)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $task = $this->resolveTaskState($lockedUser, $definition);
            if ($task['claimable'] !== true) {
                throw new RuntimeException('Task is not claimable right now.');
            }

            $cycleKey = $this->cycleKey((string) $definition->recurrence);

            $alreadyClaimed = TaskClaim::query()
                ->where('user_id', $lockedUser->id)
                ->where('task_definition_id', $definition->id)
                ->where('cycle_key', $cycleKey)
                ->exists();

            if ($alreadyClaimed) {
                throw new RuntimeException('Task already claimed.');
            }

            $rewardAmount = (int) $definition->reward_amount;
            $balanceBefore = (int) ($lockedUser->balance ?? 0);
            $balanceAfter = $balanceBefore + $rewardAmount;

            $lockedUser->balance = $balanceAfter;
            $lockedUser->save();

            TaskClaim::query()->create([
                'user_id' => $lockedUser->id,
                'task_definition_id' => $definition->id,
                'task_key' => $definition->task_key,
                'cycle_key' => $cycleKey,
                'reward_amount' => $rewardAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'claim_ip' => $auditMeta['claim_ip'] ?? null,
                'claim_user_agent' => $auditMeta['claim_user_agent'] ?? null,
                'claimed_at' => now(),
            ]);

            return [
                'code' => '200',
                'message' => 'Task claimed successfully',
                'task_key' => $definition->task_key,
                'reward_amount' => $rewardAmount,
                'balance' => $balanceAfter,
            ];
        });
    }

    private function resolveTaskState(User $user, TaskDefinition $definition): array
    {
        $goal = max((int) $definition->goal, 1);
        $cycleKey = $this->cycleKey((string) $definition->recurrence);
        [$progress, $source] = $this->resolveProgress($user, $definition, $cycleKey);

        $progress = min(max($progress, 0), $goal);
        $done = $progress >= $goal;
        $claimed = TaskClaim::query()
            ->where('user_id', $user->id)
            ->where('task_definition_id', $definition->id)
            ->where('cycle_key', $cycleKey)
            ->exists();

        return [
            'task_key' => $definition->task_key,
            'title' => $definition->title,
            'description' => $definition->description,
            'reward_amount' => (int) $definition->reward_amount,
            'progress' => $progress,
            'goal' => $goal,
            'unit' => $definition->unit,
            'category' => $definition->category,
            'status' => $claimed ? 'claimed' : ($done ? 'done' : 'progress'),
            'claimable' => $done && ! $claimed,
            'claimed' => $claimed,
            'source' => $source,
        ];
    }

    private function resolveProgress(
        User $user,
        TaskDefinition $definition,
        string $cycleKey
    ): array {
        $resolver = (string) $definition->progress_resolver;

        switch ($resolver) {
            case 'daily_login':
                return [$this->dailyLoginProgress($user), 'users'];

            case 'profile_complete':
                return [$this->profileCompleteProgress($user), 'users'];

            case 'join_room_count':
                return [
                    $this->joinRoomProgress($user, $definition, $cycleKey),
                    $this->joinRoomSource(),
                ];

            case 'follow_count':
                return [
                    $this->followProgress($user, $definition, $cycleKey),
                    $this->followSource(),
                ];

            default:
                return [
                    $this->progressFromLedger((int) $user->id, $definition->task_key, $cycleKey),
                    'task_progress',
                ];
        }
    }

    private function profileCompleteProgress(User $user): int
    {
        $steps = 0;

        if (! empty($user->profile)) {
            $steps++;
        }
        if (! empty($user->bio)) {
            $steps++;
        }
        if (! empty($user->phone)) {
            $steps++;
        }
        if (! empty($user->date_of_birth)) {
            $steps++;
        }

        return $steps;
    }

    private function dailyLoginProgress(User $user): int
    {
        $reference = $user->last_login_at ?? $user->updated_at;
        if ($reference === null) {
            return 0;
        }

        return CarbonImmutable::parse($reference)->isToday() ? 1 : 0;
    }

    private function joinRoomProgress(User $user, TaskDefinition $definition, string $cycleKey): int
    {
        if (Schema::hasTable('join_users')) {
            return (int) DB::table('join_users')
                ->where('user_id', $user->id)
                ->whereBetween('created_at', $this->timeWindowFor($definition->recurrence))
                ->distinct()
                ->count('channel_name');
        }

        return $this->progressFromLedger((int) $user->id, $definition->task_key, $cycleKey);
    }

    private function followProgress(User $user, TaskDefinition $definition, string $cycleKey): int
    {
        if (Schema::hasTable('followers')) {
            return (int) DB::table('followers')
                ->where('follower_id', $user->id)
                ->count();
        }

        return $this->progressFromLedger((int) $user->id, $definition->task_key, $cycleKey);
    }

    private function progressFromLedger(int $userId, string $taskKey, string $cycleKey): int
    {
        return (int) (TaskProgress::query()
            ->where('user_id', $userId)
            ->where('task_key', $taskKey)
            ->where('cycle_key', $cycleKey)
            ->value('progress') ?? 0);
    }

    private function claimedRewardTotal(int $userId): int
    {
        return (int) TaskClaim::query()
            ->where('user_id', $userId)
            ->sum('reward_amount');
    }

    private function cycleKey(string $recurrence): string
    {
        $now = CarbonImmutable::now();

        switch ($recurrence) {
            case TaskDefinition::RECURRENCE_DAILY:
                return $now->format('Y-m-d');

            case TaskDefinition::RECURRENCE_WEEKLY:
                return sprintf('%s-W%s', $now->format('o'), $now->format('W'));

            case TaskDefinition::RECURRENCE_MONTHLY:
                return $now->format('Y-m');

            default:
                return 'once';
        }
    }

    private function timeWindowFor(string $recurrence): array
    {
        $now = CarbonImmutable::now();

        switch ($recurrence) {
            case TaskDefinition::RECURRENCE_DAILY:
                return [$now->startOfDay(), $now->endOfDay()];

            case TaskDefinition::RECURRENCE_WEEKLY:
                return [$now->startOfWeek(), $now->endOfWeek()];

            case TaskDefinition::RECURRENCE_MONTHLY:
                return [$now->startOfMonth(), $now->endOfMonth()];

            default:
                return [CarbonImmutable::create(1970, 1, 1, 0, 0, 0), $now->endOfDay()];
        }
    }

    private function joinRoomSource(): string
    {
        return Schema::hasTable('join_users') ? 'join_users' : 'task_progress';
    }

    private function followSource(): string
    {
        return Schema::hasTable('followers') ? 'followers' : 'task_progress';
    }
}
