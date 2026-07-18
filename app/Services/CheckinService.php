<?php

namespace App\Services;

use App\Models\CheckinReward;
use App\Models\User;
use App\Models\UserCheckin;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class CheckinService
{
    /**
     * Build the check-in block appended to the task dashboard.
     * Returns NULL when the feature is not provisioned (tables missing) or on any
     * error, so the dashboard NEVER breaks and the client shows its own fallback.
     */
    public function buildBlock(User $user): ?array
    {
        try {
            if (!Schema::hasTable('checkin_rewards') || !Schema::hasTable('user_checkins')) {
                return null;
            }

            $ladder = CheckinReward::query()->where('is_active', true)->orderBy('day')->get();
            $cycle = $ladder->count();
            if ($cycle === 0) {
                return null;
            }

            $uc = UserCheckin::query()->where('user_id', $user->id)->first();
            $today = CarbonImmutable::today()->toDateString();
            $yesterday = CarbonImmutable::today()->subDay()->toDateString();
            $lastDate = ($uc && $uc->last_checkin_date) ? $uc->last_checkin_date->toDateString() : null;
            $claimedToday = ($lastDate === $today);

            if ($claimedToday) {
                $streak = (int) $uc->streak;
                $todayIndex = (($streak - 1) % $cycle) + 1;
                $claimable = false;
            } else {
                $effStreak = ($lastDate === $yesterday) ? (int) $uc->streak : 0;
                $todayIndex = ($effStreak % $cycle) + 1;
                $streak = $effStreak;
                $claimable = true;
            }

            $days = [];
            foreach ($ladder as $row) {
                $d = (int) $row->day;
                if ($claimedToday) {
                    $state = $d <= $todayIndex ? 'claimed' : 'locked';
                } else {
                    $state = $d < $todayIndex ? 'claimed' : ($d === $todayIndex ? 'today' : 'locked');
                }
                $days[] = ['day' => $d, 'reward' => (int) $row->reward_amount, 'state' => $state];
            }

            return [
                'streak' => (int) $streak,
                'today_index' => (int) $todayIndex,
                'claimable_today' => (bool) $claimable,
                'claimed_today' => $claimedToday,
                'cycle_length' => (int) $cycle,
                'days' => $days,
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Claim today's check-in (idempotent per day, streak-aware). */
    public function claim(User $user): array
    {
        if (!Schema::hasTable('checkin_rewards') || !Schema::hasTable('user_checkins')) {
            throw new RuntimeException('Daily check-in is not available yet.');
        }

        return DB::transaction(function () use ($user): array {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $uc = UserCheckin::query()->where('user_id', $lockedUser->id)->lockForUpdate()->first();

            $today = CarbonImmutable::today()->toDateString();
            $yesterday = CarbonImmutable::today()->subDay()->toDateString();
            $lastDate = ($uc && $uc->last_checkin_date) ? $uc->last_checkin_date->toDateString() : null;

            if ($lastDate === $today) {
                throw new RuntimeException('Already checked in today.');
            }

            $ladder = CheckinReward::query()->where('is_active', true)->orderBy('day')->get();
            $cycle = $ladder->count();
            if ($cycle === 0) {
                throw new RuntimeException('Daily check-in is not configured.');
            }

            $effStreak = ($lastDate === $yesterday) ? (int) $uc->streak : 0;
            $newStreak = $effStreak + 1;
            $pos = (($newStreak - 1) % $cycle) + 1;
            $rewardRow = $ladder->firstWhere('day', $pos);
            $reward = (int) ($rewardRow->reward_amount ?? 0);

            $balanceBefore = (int) ($lockedUser->balance ?? 0);
            $balanceAfter = $balanceBefore + $reward;
            $lockedUser->balance = $balanceAfter;
            $lockedUser->save();

            if ($uc) {
                $uc->streak = $newStreak;
                $uc->last_checkin_date = $today;
                $uc->total_claimed = (int) $uc->total_claimed + $reward;
                $uc->save();
            } else {
                UserCheckin::query()->create([
                    'user_id' => $lockedUser->id,
                    'streak' => $newStreak,
                    'last_checkin_date' => $today,
                    'total_claimed' => $reward,
                ]);
            }

            return [
                'code' => '200',
                'message' => 'Daily check-in claimed',
                'day' => $pos,
                'streak' => $newStreak,
                'reward_amount' => $reward,
                'balance' => $balanceAfter,
            ];
        });
    }
}
