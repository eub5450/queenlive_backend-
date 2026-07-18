<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V5\Concerns\AdminActorAuthorization;
use App\Models\Agency;
use App\Models\EntryFrame;
use App\Models\ScheduledFrameRule;
use App\Models\ScheduledFrameRuleWinner;
use App\Models\Setting;
use App\Services\ScheduledFrameRuleService;
use App\Support\SystemSettingRuntimeStore;
use App\Support\SystemSettingValueHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AdminSystemSettingController extends Controller
{
    use AdminActorAuthorization;

    public function index(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        return $this->success('System setting data loaded', [
            'data' => [
                'reward_setup' => $this->loadRewardSetup(),
                'portal_setup' => $this->loadPortalSetup(),
                'recall_setup' => $this->loadRecallSetup(),
                'withdraw_setup' => $this->loadWithdrawSetup(),
                'scheduled_frame_data' => $this->loadScheduledFrameData(),
                'frame_store' => EntryFrame::where('type', 0)->where('is_show', 1)->orderByDesc('id')->get(),
                'agency_options' => Agency::query()->orderBy('name')->get(['id', 'name', 'code', 'user_id']),
            ],
        ]);
    }

    public function updateRewardSetup(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!Schema::hasTable('settings')) {
            return $this->error('Settings table is missing.', '500');
        }

        $validated = $request->validate([
            'blocked_start_time' => 'required|date_format:H:i:s',
            'blocked_end_time' => 'required|date_format:H:i:s',
            'minimum_count_minutes' => 'required|integer|min:1|max:1440',
            'reward_one_minutes' => 'required|integer|min:1|max:1440',
            'reward_one_points' => 'required|integer|min:0',
            'reward_two_minutes' => 'required|integer|min:1|max:1440',
            'reward_two_points' => 'required|integer|min:0',
            'reward_three_minutes' => 'required|integer|min:1|max:1440',
            'reward_three_points' => 'required|integer|min:0',
            'reward_three_receive_points' => 'required|integer|min:0',
        ]);

        if ((int) $validated['reward_two_minutes'] <= (int) $validated['reward_one_minutes']) {
            return $this->error('2 hour target must be greater than 1 hour target.');
        }
        if ((int) $validated['reward_three_minutes'] <= (int) $validated['reward_two_minutes']) {
            return $this->error('3 hour target must be greater than 2 hour target.');
        }

        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $this->error('Settings row is missing. Create the main settings row first.', '500');
        }

        $setting->video_reward_block_start_time = $validated['blocked_start_time'];
        $setting->video_reward_block_end_time = $validated['blocked_end_time'];
        $setting->video_reward_min_count_seconds = (int) $validated['minimum_count_minutes'] * 60;
        $setting->video_reward_one_hour_target_seconds = (int) $validated['reward_one_minutes'] * 60;
        $setting->video_reward_one_hour_points = (int) $validated['reward_one_points'];
        $setting->video_reward_two_hour_target_seconds = (int) $validated['reward_two_minutes'] * 60;
        $setting->video_reward_two_hour_points = (int) $validated['reward_two_points'];
        $setting->video_reward_three_hour_target_seconds = (int) $validated['reward_three_minutes'] * 60;
        $setting->video_reward_three_hour_points = (int) $validated['reward_three_points'];
        $setting->video_reward_three_hour_receive_points = (int) $validated['reward_three_receive_points'];
        $setting->save();
        Cache::forget('app_settings');

        return $this->success('Reward setup updated successfully for V4 and V5.', [
            'data' => $this->loadRewardSetup(),
        ]);
    }

    public function updatePortalSetup(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!Schema::hasTable('settings')) {
            return $this->error('Settings table is missing.', '500');
        }

        $validated = $request->validate([
            'portal_min_recharge_amount' => 'required|integer|min:1',
            'vip_discount' => 'nullable|in:0,1',
            'vip_discount_percentage' => 'required|numeric|min:0|max:100',
            'recharge_offer_reward' => 'nullable|in:0,1',
            'recharge_offer_reward_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $this->error('Settings row is missing. Create the main settings row first.', '500');
        }

        $setting->vip_discount = (int) ($validated['vip_discount'] ?? 0);
        $setting->recharge_offer_reward = (int) ($validated['recharge_offer_reward'] ?? 0);
        $setting->save();

        SystemSettingRuntimeStore::putMany([
            'portal_min_recharge_amount' => (int) $validated['portal_min_recharge_amount'],
            'vip_discount_percentage' => round((float) $validated['vip_discount_percentage'], 2),
            'recharge_offer_reward_percentage' => round((float) $validated['recharge_offer_reward_percentage'], 2),
        ]);
        Cache::forget('app_settings');

        return $this->success('Portal and offer setup updated successfully.', [
            'data' => $this->loadPortalSetup(),
        ]);
    }

    public function updateRecallSetup(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'recall_portal_percentage' => 'required|numeric|min:0|max:100',
            'recall_company_percentage' => 'required|numeric|min:0|max:100',
            'recall_company_user_id' => 'required|integer|min:1',
        ]);

        $total = round((float) $validated['recall_portal_percentage'] + (float) $validated['recall_company_percentage'], 2);
        if (abs($total - 100.0) > 0.01) {
            return $this->error('Recall percentages must total exactly 100.');
        }

        SystemSettingRuntimeStore::putMany([
            'recall_portal_percentage' => round((float) $validated['recall_portal_percentage'], 2),
            'recall_company_percentage' => round((float) $validated['recall_company_percentage'], 2),
            'recall_company_user_id' => (int) $validated['recall_company_user_id'],
        ]);
        Cache::forget('app_settings');

        return $this->success('Recall setting updated successfully.', [
            'data' => $this->loadRecallSetup(),
        ]);
    }

    public function updateWithdrawSetup(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'withdraw_day_requirement' => 'required|integer|min:0|max:365',
            'withdraw_allowed_amounts' => 'required|string',
            'withdraw_blocked_days' => 'nullable|string',
            'withdraw_scope_type' => 'required|in:all_hosts,agency_hosts,all_agency_owners',
            'withdraw_scope_agency_id' => 'nullable|integer|min:1',
            'withdraw_allowed_user_ids' => 'nullable|string',
            'withdraw_blocked_user_ids' => 'nullable|string',
        ]);

        $amounts = SystemSettingValueHelper::parseIntegerCsv($validated['withdraw_allowed_amounts'], 1, PHP_INT_MAX);
        if (empty($amounts)) {
            return $this->error('Withdraw amount list must contain at least one valid amount.');
        }

        $days = SystemSettingValueHelper::parseIntegerCsv($validated['withdraw_blocked_days'] ?? '', 1, 31);
        $allowedIds = SystemSettingValueHelper::parseIntegerCsv($validated['withdraw_allowed_user_ids'] ?? '', 1, PHP_INT_MAX);
        $blockedIds = SystemSettingValueHelper::parseIntegerCsv($validated['withdraw_blocked_user_ids'] ?? '', 1, PHP_INT_MAX);

        if ($validated['withdraw_scope_type'] === 'agency_hosts' && empty($validated['withdraw_scope_agency_id'])) {
            return $this->error('Select one agency for agency wise host scope.');
        }

        SystemSettingRuntimeStore::putMany([
            'withdraw_day_requirement' => (int) $validated['withdraw_day_requirement'],
            'withdraw_allowed_amounts' => implode(',', $amounts),
            'withdraw_blocked_days' => implode(',', $days),
            'withdraw_scope_type' => $validated['withdraw_scope_type'],
            'withdraw_scope_agency_id' => $validated['withdraw_scope_type'] === 'agency_hosts' ? (int) $validated['withdraw_scope_agency_id'] : null,
            'withdraw_allowed_user_ids' => implode(',', $allowedIds),
            'withdraw_blocked_user_ids' => implode(',', $blockedIds),
        ]);
        Cache::forget('app_settings');

        return $this->success('Withdraw setting updated successfully.', [
            'data' => $this->loadWithdrawSetup(),
        ]);
    }

    public function storeFrameRule(Request $request, ScheduledFrameRuleService $service)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!$this->scheduledFrameTablesReady()) {
            return $this->error('Scheduled frame tables are missing. Run migration first.', '500');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'entry_frame_id' => 'required|exists:entry_frames,id',
            'top_type' => 'required|in:top_agency,top_host,gamer,top_gamer,top_agency_host',
            'condition_type' => 'required|in:top_rank,target',
            'metric_type' => 'required|in:gift_receive,total_game_bet',
            'target_value' => 'nullable|numeric|min:0',
            'top_limit' => 'nullable|integer|min:1|max:100',
            'schedule_type' => 'required|in:custom,hourly,weekly,monthly',
            'campaign_starts_at' => 'required|date',
            'campaign_ends_at' => 'required|date|after:campaign_starts_at',
            'notes' => 'nullable|string',
        ]);

        $metricError = $this->validateMetricAgainstTopType($validated['top_type'], $validated['metric_type']);
        if ($metricError) {
            return $this->error($metricError);
        }
        if ($validated['condition_type'] === 'target' && (float) ($validated['target_value'] ?? 0) <= 0) {
            return $this->error('Target value is required for target condition.');
        }

        $frame = EntryFrame::findOrFail($validated['entry_frame_id']);
        $actor = $this->authorizedActor($request);

        ScheduledFrameRule::create([
            'title' => trim((string) ($validated['title'] ?? '')) ?: $frame->name . ' - ' . $this->labelFromKey($validated['top_type']),
            'entry_frame_id' => $frame->id,
            'frame_name' => $frame->name,
            'frame_image' => $frame->image,
            'frame_effect' => $frame->effect,
            'top_type' => $validated['top_type'],
            'metric_type' => $validated['metric_type'],
            'condition_type' => $validated['condition_type'],
            'target_value' => $validated['target_value'] ?? 0,
            'top_limit' => $validated['top_limit'] ?? 1,
            'schedule_type' => $validated['schedule_type'],
            'campaign_starts_at' => $validated['campaign_starts_at'],
            'campaign_ends_at' => $validated['campaign_ends_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => 1,
            'created_by' => optional($actor)->id,
        ]);

        $service->syncAllActiveRules();

        return $this->success('Next round frame rule added successfully.', [
            'data' => $this->loadScheduledFrameData(),
        ]);
    }

    public function toggleFrameRule(Request $request, ScheduledFrameRuleService $service, $id = null)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!$this->scheduledFrameTablesReady()) {
            return $this->error('Scheduled frame tables are missing. Run migration first.', '500');
        }

        $ruleId = $id ?: $request->id ?: $request->rule_id;
        $rule = ScheduledFrameRule::findOrFail($ruleId);
        $rule->status = $rule->status ? 0 : 1;
        $rule->save();
        $service->syncAllActiveRules();

        return $this->success($rule->status ? 'Rule activated successfully.' : 'Rule paused successfully.', [
            'data' => [
                'rule' => $rule,
                'scheduled_frame_data' => $this->loadScheduledFrameData(),
            ],
        ]);
    }

    public function deleteFrameRule(Request $request, ScheduledFrameRuleService $service, $id = null)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!$this->scheduledFrameTablesReady()) {
            return $this->error('Scheduled frame tables are missing. Run migration first.', '500');
        }

        $ruleId = $id ?: $request->id ?: $request->rule_id;
        $rule = ScheduledFrameRule::findOrFail($ruleId);
        $service->deleteRule($rule);

        return $this->success('Rule removed successfully.', [
            'data' => $this->loadScheduledFrameData(),
        ]);
    }

    public function syncFrameRules(Request $request, ScheduledFrameRuleService $service)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }
        if (!$this->scheduledFrameTablesReady()) {
            return $this->error('Scheduled frame tables are missing. Run migration first.', '500');
        }

        $service->syncAllActiveRules();

        return $this->success('Frame rule sync completed.', [
            'data' => $this->loadScheduledFrameData(),
        ]);
    }

    private function loadScheduledFrameData()
    {
        $data = [
            'rules' => collect(),
            'running' => collect(),
            'upcoming' => collect(),
            'previous' => collect(),
            'recent_winners' => collect(),
            'error' => null,
            'tables' => [
                'scheduled_frame_rules' => 'MISSING',
                'scheduled_frame_rule_winners' => 'MISSING',
            ],
        ];

        try {
            $data['tables']['scheduled_frame_rules'] = Schema::hasTable('scheduled_frame_rules') ? 'FOUND' : 'MISSING';
            $data['tables']['scheduled_frame_rule_winners'] = Schema::hasTable('scheduled_frame_rule_winners') ? 'FOUND' : 'MISSING';
            if ($data['tables']['scheduled_frame_rules'] !== 'FOUND') {
                return $data;
            }

            $rules = ScheduledFrameRule::orderByDesc('id')->get();
            $decorated = $rules->map(function ($rule) {
                $status = $this->ruleStatusSummary($rule);
                $rule->status_badge = $status['badge'];
                $rule->status_label = $status['label'];
                $rule->window_label = $this->labelFromKey($rule->schedule_type);
                $rule->top_type_label = $this->labelFromKey($rule->top_type);
                $rule->metric_type_label = $this->labelFromKey($rule->metric_type);
                $rule->condition_type_label = $this->labelFromKey($rule->condition_type);
                return $rule;
            });

            $data['rules'] = $decorated->values();
            $data['running'] = $decorated->filter(function ($rule) { return $rule->status_label === 'Running'; })->values();
            $data['upcoming'] = $decorated->filter(function ($rule) { return $rule->status_label === 'Upcoming'; })->values();
            $data['previous'] = $decorated->filter(function ($rule) {
                return in_array($rule->status_label, ['Completed', 'Paused'], true);
            })->values();

            if ($data['tables']['scheduled_frame_rule_winners'] === 'FOUND') {
                $data['recent_winners'] = ScheduledFrameRuleWinner::with('rule')->orderByDesc('id')->limit(15)->get();
            }
        } catch (\Throwable $throwable) {
            $data['error'] = $throwable->getMessage();
        }

        return $data;
    }

    private function ruleStatusSummary($rule)
    {
        $now = now(config('app.timezone'));
        if (!$rule->status) {
            return ['label' => 'Paused', 'badge' => 'secondary'];
        }
        if ($now->lt($rule->campaign_starts_at)) {
            return ['label' => 'Upcoming', 'badge' => 'warning'];
        }
        if ($now->gte($rule->campaign_ends_at)) {
            return ['label' => 'Completed', 'badge' => 'dark'];
        }
        return ['label' => 'Running', 'badge' => 'success'];
    }

    private function scheduledFrameTablesReady()
    {
        return Schema::hasTable('scheduled_frame_rules')
            && Schema::hasTable('scheduled_frame_rule_winners');
    }

    private function validateMetricAgainstTopType($topType, $metricType)
    {
        $giftTypes = ['top_agency', 'top_host', 'top_agency_host'];
        $gameTypes = ['gamer', 'top_gamer'];
        if (in_array($topType, $giftTypes, true) && $metricType !== 'gift_receive') {
            return 'Selected top type only supports gift receive condition.';
        }
        if (in_array($topType, $gameTypes, true) && $metricType !== 'total_game_bet') {
            return 'Selected top type only supports total game bet condition.';
        }
        return null;
    }

    private function labelFromKey($value)
    {
        return ucwords(str_replace('_', ' ', (string) $value));
    }

    private function loadRewardSetup()
    {
        $defaults = [
            'blocked_start_time' => '06:00:00',
            'blocked_end_time' => '11:59:59',
            'minimum_count_minutes' => 30,
            'reward_one_minutes' => 60,
            'reward_one_points' => 2000,
            'reward_two_minutes' => 120,
            'reward_two_points' => 3000,
            'reward_three_minutes' => 180,
            'reward_three_points' => 5000,
            'reward_three_receive_points' => 100000,
        ];

        if (!Schema::hasTable('settings')) {
            return $defaults;
        }
        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $defaults;
        }

        return [
            'blocked_start_time' => $this->normalizeStoredTime($setting->video_reward_block_start_time ?? null, $defaults['blocked_start_time']),
            'blocked_end_time' => $this->normalizeStoredTime($setting->video_reward_block_end_time ?? null, $defaults['blocked_end_time']),
            'minimum_count_minutes' => $this->secondsToMinutes($setting->video_reward_min_count_seconds ?? null, $defaults['minimum_count_minutes']),
            'reward_one_minutes' => $this->secondsToMinutes($setting->video_reward_one_hour_target_seconds ?? null, $defaults['reward_one_minutes']),
            'reward_one_points' => (int) ($setting->video_reward_one_hour_points ?? $defaults['reward_one_points']),
            'reward_two_minutes' => $this->secondsToMinutes($setting->video_reward_two_hour_target_seconds ?? null, $defaults['reward_two_minutes']),
            'reward_two_points' => (int) ($setting->video_reward_two_hour_points ?? $defaults['reward_two_points']),
            'reward_three_minutes' => $this->secondsToMinutes($setting->video_reward_three_hour_target_seconds ?? null, $defaults['reward_three_minutes']),
            'reward_three_points' => (int) ($setting->video_reward_three_hour_points ?? $defaults['reward_three_points']),
            'reward_three_receive_points' => (int) ($setting->video_reward_three_hour_receive_points ?? $defaults['reward_three_receive_points']),
        ];
    }

    private function loadPortalSetup()
    {
        $defaults = [
            'portal_min_recharge_amount' => 100000,
            'vip_discount' => 0,
            'vip_discount_percentage' => 50,
            'recharge_offer_reward' => 0,
            'recharge_offer_reward_percentage' => 5,
        ];

        if (!Schema::hasTable('settings')) {
            return $defaults;
        }
        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $defaults;
        }

        return [
            'portal_min_recharge_amount' => SystemSettingValueHelper::portalMinRechargeAmount($setting),
            'vip_discount' => (int) ($setting->vip_discount ?? $defaults['vip_discount']),
            'vip_discount_percentage' => SystemSettingValueHelper::vipDiscountPercentage($setting),
            'recharge_offer_reward' => (int) ($setting->recharge_offer_reward ?? $defaults['recharge_offer_reward']),
            'recharge_offer_reward_percentage' => SystemSettingValueHelper::rechargeOfferRewardPercentage($setting),
        ];
    }

    private function loadRecallSetup()
    {
        $defaults = [
            'recall_portal_percentage' => 70,
            'recall_company_percentage' => 30,
            'recall_company_user_id' => 1,
        ];

        if (!Schema::hasTable('settings')) {
            return $defaults;
        }
        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $defaults;
        }

        return [
            'recall_portal_percentage' => SystemSettingValueHelper::recallPortalPercentage($setting),
            'recall_company_percentage' => SystemSettingValueHelper::recallCompanyPercentage($setting),
            'recall_company_user_id' => SystemSettingValueHelper::recallCompanyUserId($setting) ?? $defaults['recall_company_user_id'],
        ];
    }

    private function loadWithdrawSetup()
    {
        $defaults = [
            'withdraw_day_requirement' => 0,
            'withdraw_allowed_amounts' => implode(',', SystemSettingValueHelper::defaultWithdrawAmounts()),
            'withdraw_blocked_days' => '22,23,24,25,26,27,28,29,30',
            'withdraw_scope_type' => 'all_hosts',
            'withdraw_scope_agency_id' => null,
            'withdraw_allowed_user_ids' => '',
            'withdraw_blocked_user_ids' => '',
        ];

        if (!Schema::hasTable('settings')) {
            return $defaults;
        }
        $setting = Setting::find(1) ?: Setting::query()->first();
        if (!$setting) {
            return $defaults;
        }

        return [
            'withdraw_day_requirement' => SystemSettingValueHelper::withdrawDayRequirement($setting),
            'withdraw_allowed_amounts' => implode(',', SystemSettingValueHelper::withdrawAllowedAmounts($setting, SystemSettingValueHelper::defaultWithdrawAmounts())),
            'withdraw_blocked_days' => implode(',', SystemSettingValueHelper::withdrawBlockedDays($setting)),
            'withdraw_scope_type' => SystemSettingValueHelper::withdrawScopeType($setting),
            'withdraw_scope_agency_id' => SystemSettingValueHelper::withdrawScopeAgencyId($setting),
            'withdraw_allowed_user_ids' => implode(',', SystemSettingValueHelper::withdrawAllowedUserIds($setting)),
            'withdraw_blocked_user_ids' => implode(',', SystemSettingValueHelper::withdrawBlockedUserIds($setting)),
        ];
    }

    private function secondsToMinutes($seconds, $fallback)
    {
        $seconds = (int) $seconds;
        return $seconds <= 0 ? (int) $fallback : (int) floor($seconds / 60);
    }

    private function normalizeStoredTime($time, $fallback)
    {
        $time = trim((string) $time);
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        return $fallback;
    }
}
