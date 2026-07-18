<?php

namespace App\Services;

use App\Models\MyBeg;
use App\Models\ScheduledFrameRule;
use App\Models\ScheduledFrameRuleWinner;
use App\Models\User;
use App\RedisCache\CacheClearHelperFromModelAuto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ScheduledFrameRuleService
{
    public function syncAllActiveRules($now = null)
    {
        if (!$this->tablesReady()) {
            return;
        }

        $now = $this->businessNow($now);

        $this->removeInactiveOrExpiredAssignments($now);

        $rules = ScheduledFrameRule::where('status', 1)->get();

        foreach ($rules as $rule) {
            $this->syncRule($rule, $now);
        }
    }

    public function syncRule(ScheduledFrameRule $rule, $now = null)
    {
        if (!$this->tablesReady()) {
            return;
        }

        $now = $this->businessNow($now);
        $window = $this->resolveCurrentWindow($rule, $now);

        if (!$window) {
            $this->removeAssignmentsForRule($rule, $now);
            return;
        }

        $winners = $this->resolveWinners($rule, $window['start'], $window['end']);
        $winnerIds = $winners->pluck('user_id')->map(function ($item) {
            return (int) $item;
        })->all();

        $activeAssignments = ScheduledFrameRuleWinner::where('scheduled_frame_rule_id', $rule->id)
            ->whereNull('removed_at')
            ->get();

        foreach ($activeAssignments as $assignment) {
            $sameWindow = $assignment->period_key === $window['key'];
            $stillWinner = in_array((int) $assignment->user_id, $winnerIds, true);

            if (!$sameWindow || !$stillWinner) {
                $this->removeAssignment($assignment, $now);
            }
        }

        foreach ($winners as $winner) {
            $this->applyWinner($rule, $winner, $window, $now);
        }

        $rule->last_synced_at = $now;
        $rule->last_window_key = $window['key'];
        $rule->save();
    }

    public function deleteRule(ScheduledFrameRule $rule)
    {
        $now = $this->businessNow();

        $this->removeAssignmentsForRule($rule, $now);
        ScheduledFrameRuleWinner::where('scheduled_frame_rule_id', $rule->id)->delete();
        $rule->delete();
    }

    private function resolveWinners(ScheduledFrameRule $rule, Carbon $start, Carbon $end)
    {
        switch ($rule->top_type) {
            case 'top_agency':
                $rows = $this->topAgencyRows($start, $end);
                break;

            case 'top_host':
                $rows = $this->topHostRows($start, $end);
                break;

            case 'top_agency_host':
                $rows = $this->topAgencyHostRows($start, $end);
                break;

            case 'gamer':
            case 'top_gamer':
                $rows = $this->topGamerRows($start, $end);
                break;

            default:
                $rows = collect();
                break;
        }

        $rows = $rows->sortByDesc('metric_value')->values();

        if ($rule->condition_type === 'target') {
            $targetValue = (float) $rule->target_value;
            $rows = $rows->filter(function ($row) use ($targetValue) {
                return (float) $row->metric_value >= $targetValue;
            })->values();
        } else {
            $rows = $rows->take(max(1, (int) $rule->top_limit))->values();
        }

        if ($rule->condition_type === 'target' && (int) $rule->top_limit > 0) {
            $rows = $rows->take((int) $rule->top_limit)->values();
        }

        return $rows;
    }

    private function topAgencyRows(Carbon $start, Carbon $end)
    {
        return DB::table('gifts as g')
            ->join('host_data as h', 'h.user_id', '=', 'g.reciever_id')
            ->join('agencies as a', 'a.code', '=', 'h.agency_code')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->where('u.status', 1)
            ->where('u.is_admin_frame', 0)
            ->where('u.is_bd_admin', 0)
            ->where('u.is_official_frame', 0)
            ->whereBetween('g.created_at', array($start, $end))
            ->groupBy('a.code', 'u.id', 'u.name')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                'a.code as agency_code',
                DB::raw('SUM(g.value) as metric_value')
            )
            ->get();
    }

    private function topHostRows(Carbon $start, Carbon $end)
    {
        return DB::table('gifts as g')
            ->join('users as u', 'u.id', '=', 'g.reciever_id')
            ->join('host_data as h', 'h.user_id', '=', 'u.id')
            ->where('u.status', 1)
            ->where('u.is_host_id', 1)
            ->where('u.is_admin_frame', 0)
            ->where('u.is_bd_admin', 0)
            ->where('u.is_official_frame', 0)
            ->whereBetween('g.created_at', array($start, $end))
            ->groupBy('u.id', 'u.name', 'h.agency_code')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                'h.agency_code',
                DB::raw('SUM(g.value) as metric_value')
            )
            ->get();
    }

    private function topAgencyHostRows(Carbon $start, Carbon $end)
    {
        $rows = DB::table('gifts as g')
            ->join('users as u', 'u.id', '=', 'g.reciever_id')
            ->join('host_data as h', 'h.user_id', '=', 'u.id')
            ->where('u.status', 1)
            ->where('u.is_host_id', 1)
            ->where('u.is_admin_frame', 0)
            ->where('u.is_bd_admin', 0)
            ->where('u.is_official_frame', 0)
            ->whereBetween('g.created_at', array($start, $end))
            ->groupBy('u.id', 'u.name', 'h.agency_code')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                'h.agency_code',
                DB::raw('SUM(g.value) as metric_value')
            )
            ->get();

        return $rows->groupBy('agency_code')->map(function ($items) {
            return $items->sortByDesc('metric_value')->first();
        })->values();
    }

    private function topGamerRows(Carbon $start, Carbon $end)
    {
        return DB::table('furits_pots_backups as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->where('u.status', 1)
            ->whereNotIn('f.user_id', array(23825, 23826, 23827))
            ->whereBetween('f.date', array($start, $end))
            ->groupBy('u.id', 'u.name')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                DB::raw('NULL as agency_code'),
                DB::raw('SUM(f.amount) as metric_value')
            )
            ->get();
    }

    private function applyWinner(ScheduledFrameRule $rule, $winner, array $window, Carbon $now)
    {
        $user = User::find($winner->user_id);
        if (!$user) {
            return;
        }

        $assignment = ScheduledFrameRuleWinner::where('scheduled_frame_rule_id', $rule->id)
            ->where('user_id', $user->id)
            ->where('period_key', $window['key'])
            ->first();

        if ($assignment && $assignment->removed_at) {
            $assignment->removed_at = null;
            $assignment->status = 'active';
        }

        if (!$assignment) {
            $previousBeg = MyBeg::where('user_id', $user->id)
                ->where('type', 0)
                ->where('status', 1)
                ->orderByDesc('id')
                ->first();

            MyBeg::where('user_id', $user->id)
                ->where('type', 0)
                ->update(array('status' => 0));

            $myBeg = new MyBeg();
            $myBeg->store_id = $rule->entry_frame_id;
            $myBeg->user_id = $user->id;
            $myBeg->active_time = $window['start']->copy();
            $myBeg->expaire_time = $window['end']->copy();
            $myBeg->name = $rule->frame_name;
            $myBeg->image = $rule->frame_image;
            $myBeg->effect = $rule->frame_effect;
            $myBeg->status = 1;
            $myBeg->type = 0;
            $myBeg->save();

            $assignment = new ScheduledFrameRuleWinner();
            $assignment->scheduled_frame_rule_id = $rule->id;
            $assignment->user_id = $user->id;
            $assignment->my_beg_id = $myBeg->id;
            $assignment->previous_my_beg_id = optional($previousBeg)->id;
            $assignment->previous_frame_effect = $user->frame;
            $assignment->applied_at = $now;
        } else {
            $myBeg = $assignment->my_beg_id ? MyBeg::find($assignment->my_beg_id) : null;

            if (!$myBeg) {
                $myBeg = new MyBeg();
                $myBeg->store_id = $rule->entry_frame_id;
                $myBeg->user_id = $user->id;
                $myBeg->name = $rule->frame_name;
                $myBeg->image = $rule->frame_image;
                $myBeg->effect = $rule->frame_effect;
                $myBeg->type = 0;
                $assignment->my_beg_id = null;
            }

            MyBeg::where('user_id', $user->id)
                ->where('type', 0)
                ->where('id', '!=', $myBeg->id)
                ->update(array('status' => 0));

            $myBeg->active_time = $window['start']->copy();
            $myBeg->expaire_time = $window['end']->copy();
            $myBeg->status = 1;
            $myBeg->save();
            $assignment->my_beg_id = $myBeg->id;
        }

        $assignment->frame_effect = $rule->frame_effect;
        $assignment->frame_name = $rule->frame_name;
        $assignment->frame_image = $rule->frame_image;
        $assignment->metric_value = $winner->metric_value;
        $assignment->agency_code = isset($winner->agency_code) ? $winner->agency_code : null;
        $assignment->period_key = $window['key'];
        $assignment->window_starts_at = $window['start']->copy();
        $assignment->window_ends_at = $window['end']->copy();
        $assignment->status = 'active';
        $assignment->save();

        $user->frame = $rule->frame_effect;
        $user->save();

        $this->clearUserFrameCaches($user->id);
    }

    private function removeInactiveOrExpiredAssignments(Carbon $now)
    {
        $assignments = ScheduledFrameRuleWinner::with('rule')
            ->whereNull('removed_at')
            ->get();

        foreach ($assignments as $assignment) {
            $rule = $assignment->rule;
            $shouldRemove = !$rule
                || !$rule->status
                || $now->gte($assignment->window_ends_at)
                || $now->lt($assignment->window_starts_at)
                || $now->gte($rule->campaign_ends_at);

            if ($shouldRemove) {
                $this->removeAssignment($assignment, $now);
            }
        }
    }

    private function removeAssignmentsForRule(ScheduledFrameRule $rule, Carbon $now)
    {
        $assignments = ScheduledFrameRuleWinner::where('scheduled_frame_rule_id', $rule->id)
            ->whereNull('removed_at')
            ->get();

        foreach ($assignments as $assignment) {
            $this->removeAssignment($assignment, $now);
        }
    }

    private function removeAssignment(ScheduledFrameRuleWinner $assignment, Carbon $now)
    {
        if ($assignment->removed_at) {
            return;
        }

        $user = User::find($assignment->user_id);

        if ($assignment->my_beg_id) {
            $myBeg = MyBeg::find($assignment->my_beg_id);
            if ($myBeg) {
                $myBeg->delete();
            }
        }

        if ($user) {
            $fallbackWinner = ScheduledFrameRuleWinner::with('rule')
                ->where('user_id', $user->id)
                ->whereNull('removed_at')
                ->where('id', '!=', $assignment->id)
                ->orderByDesc('applied_at')
                ->get()
                ->first(function ($item) use ($now) {
                    return $item->rule
                        && $item->rule->status
                        && $item->window_starts_at
                        && $item->window_ends_at
                        && $now->between($item->window_starts_at, $item->window_ends_at);
                });

            if ($fallbackWinner) {
                $fallbackBeg = $fallbackWinner->my_beg_id ? MyBeg::find($fallbackWinner->my_beg_id) : null;
                if ($fallbackBeg) {
                    MyBeg::where('user_id', $user->id)
                        ->where('type', 0)
                        ->update(array('status' => 0));

                    $fallbackBeg->status = 1;
                    $fallbackBeg->save();
                }
                $user->frame = $fallbackWinner->frame_effect;
            } else {
                $restored = false;
                if ($assignment->previous_my_beg_id) {
                    $previousBeg = MyBeg::find($assignment->previous_my_beg_id);
                    if ($previousBeg) {
                        MyBeg::where('user_id', $user->id)
                            ->where('type', 0)
                            ->update(array('status' => 0));

                        $previousBeg->status = 1;
                        $previousBeg->save();
                        $user->frame = $previousBeg->effect;
                        $restored = true;
                    }
                }

                if (!$restored) {
                    $user->frame = $this->fallbackFrame($user);
                }
            }

            $user->save();
            $this->clearUserFrameCaches($user->id);
        }

        $assignment->removed_at = $now;
        $assignment->status = 'removed';
        $assignment->save();
    }

    private function resolveCurrentWindow(ScheduledFrameRule $rule, Carbon $now)
    {
        $start = Carbon::parse($rule->campaign_starts_at, config('app.timezone'));
        $end = Carbon::parse($rule->campaign_ends_at, config('app.timezone'));

        if ($now->lt($start) || $now->gte($end)) {
            return null;
        }

        if ($rule->schedule_type === 'custom') {
            return array(
                'key' => 'custom_' . $start->format('YmdHis') . '_' . $end->format('YmdHis'),
                'start' => $start,
                'end' => $end,
            );
        }

        $cursor = $start->copy();
        $index = 0;

        while ($cursor->lt($end)) {
            $next = $this->nextWindowBoundary($cursor, $rule->schedule_type);
            if ($next->gt($end)) {
                $next = $end->copy();
            }

            $isInsideWindow = $now->gte($cursor) && $now->lt($next);
            $isFinalBoundary = $next->equalTo($end) && $now->equalTo($next);

            if ($isInsideWindow || $isFinalBoundary) {
                return array(
                    'key' => $rule->schedule_type . '_' . $index . '_' . $cursor->format('YmdHis'),
                    'start' => $cursor->copy(),
                    'end' => $next->copy(),
                );
            }

            $cursor = $next->copy();
            $index++;
        }

        return null;
    }

    private function nextWindowBoundary(Carbon $current, $scheduleType)
    {
        if ($scheduleType === 'hourly') {
            return $current->copy()->addHour();
        }

        if ($scheduleType === 'weekly') {
            return $current->copy()->addWeek();
        }

        if ($scheduleType === 'monthly') {
            return $current->copy()->addMonthNoOverflow();
        }

        return $current->copy();
    }

    private function fallbackFrame(User $user)
    {
        if ((int) $user->is_admin_frame === 1) {
            return 'admin.svga';
        }

        if ((int) $user->is_bd_admin === 1) {
            return 'frame_11.svga';
        }

        if ((int) $user->is_official_frame === 1) {
            return 'official.svga';
        }

        if ((int) $user->is_agency === 1) {
            return 'marchant.svga';
        }

        return null;
    }

    private function clearUserFrameCaches($userId)
    {
        CacheClearHelperFromModelAuto::clearUserCaches($userId, 'scheduled_frame_rule');
        Cache::forget("vip_lists_{$userId}");
        Cache::forget("my_effects_{$userId}");
        Cache::forget("notifications_{$userId}");
        Cache::forget("notifications_v2_{$userId}");
    }

    private function tablesReady()
    {
        try {
            return Schema::hasTable('scheduled_frame_rules')
                && Schema::hasTable('scheduled_frame_rule_winners');
        } catch (\Throwable $throwable) {
            Log::warning('Scheduled frame tables check failed', array(
                'message' => $throwable->getMessage(),
            ));

            return false;
        }
    }

    private function businessNow($now = null)
    {
        if ($now instanceof Carbon) {
            return $now->copy()->timezone(config('app.timezone'));
        }

        return Carbon::now(config('app.timezone'));
    }
}
