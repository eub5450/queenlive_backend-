@extends('backend.layouts.main')

@section('title')
System Setting
@endsection

@php
    $runningCount = $scheduledFrameData['running']->count();
    $upcomingCount = $scheduledFrameData['upcoming']->count();
    $previousCount = $scheduledFrameData['previous']->count();
@endphp

@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div>
                    <h4 class="mb-1">System Setting</h4>
                    <small class="text-muted">Reward, portal, recall, withdraw, and next round frame control.</small>
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <span class="badge badge-info mr-2">Live Control</span>
                    <form action="{{ route('admin.system_setting.frame_rule.sync') }}" method="POST" class="mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Run Sync Now</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Running</div>
                            <div class="h3 mb-0">{{ $runningCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Upcoming</div>
                            <div class="h3 mb-0">{{ $upcomingCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Previous / Paused</div>
                            <div class="h3 mb-0">{{ $previousCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <strong>Reward setup</strong>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                <div>
                                    <h5 class="mb-1">Reward setup</h5>
                                    <small class="text-muted">V4 and V5 both read these values for video daytime reward.</small>
                                </div>
                                <span class="badge badge-light mt-2 mt-md-0">Timezone: Europe/London</span>
                            </div>

                            <form action="{{ route('admin.system_setting.reward_setup.update') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Blocked Start Time
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Reward counting is blocked from this time every day. Use HH:MM:SS.">?</span>
                                        </label>
                                        <input type="time" step="1" name="blocked_start_time" class="form-control" value="{{ old('blocked_start_time', $rewardSetup['blocked_start_time']) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Blocked End Time
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Reward counting resumes after this blocked window ends. 12:00:00 is outside when end is 11:59:59.">?</span>
                                        </label>
                                        <input type="time" step="1" name="blocked_end_time" class="form-control" value="{{ old('blocked_end_time', $rewardSetup['blocked_end_time']) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Minimum Count Minutes
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Only day_time rows at or above this minute count enter the daily reward sum. Example: 30 + 30 can make 60.">?</span>
                                        </label>
                                        <input type="number" min="1" max="1440" name="minimum_count_minutes" class="form-control" value="{{ old('minimum_count_minutes', $rewardSetup['minimum_count_minutes']) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Third Reward Receive Target
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Required receive points for the 3-hour reward unlock. Example: 100000 means 1 lac received.">?</span>
                                        </label>
                                        <input type="number" min="0" name="reward_three_receive_points" class="form-control" value="{{ old('reward_three_receive_points', $rewardSetup['reward_three_receive_points']) }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="font-weight-bold mb-3">1 Hour Reward</div>
                                            <div class="form-group mb-3">
                                                <label>
                                                    Target Minutes
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="First reward unlock threshold. Must be 60 or more to require a full hour.">?</span>
                                                </label>
                                                <input type="number" min="1" max="1440" name="reward_one_minutes" class="form-control" value="{{ old('reward_one_minutes', $rewardSetup['reward_one_minutes']) }}" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>
                                                    Reward Points
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="Points sent when the first target is reached.">?</span>
                                                </label>
                                                <input type="number" min="0" name="reward_one_points" class="form-control" value="{{ old('reward_one_points', $rewardSetup['reward_one_points']) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="font-weight-bold mb-3">2 Hour Reward</div>
                                            <div class="form-group mb-3">
                                                <label>
                                                    Target Minutes
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="Second reward unlock threshold. It must be greater than the first target.">?</span>
                                                </label>
                                                <input type="number" min="1" max="1440" name="reward_two_minutes" class="form-control" value="{{ old('reward_two_minutes', $rewardSetup['reward_two_minutes']) }}" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>
                                                    Reward Points
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="Extra points sent at the second threshold. Total shown to users becomes first plus second reward.">?</span>
                                                </label>
                                                <input type="number" min="0" name="reward_two_points" class="form-control" value="{{ old('reward_two_points', $rewardSetup['reward_two_points']) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="font-weight-bold mb-3">3 Hour Reward</div>
                                            <div class="form-group mb-3">
                                                <label>
                                                    Target Minutes
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="Third reward unlock threshold. It must be greater than the second target.">?</span>
                                                </label>
                                                <input type="number" min="1" max="1440" name="reward_three_minutes" class="form-control" value="{{ old('reward_three_minutes', $rewardSetup['reward_three_minutes']) }}" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>
                                                    Reward Points
                                                    <span class="text-muted ml-1" data-toggle="tooltip" title="Extra points sent at the third threshold after receive target is also satisfied.">?</span>
                                                </label>
                                                <input type="number" min="0" name="reward_three_points" class="form-control" value="{{ old('reward_three_points', $rewardSetup['reward_three_points']) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <small class="text-muted mb-2 mb-md-0">Current default flow: 60m = 2k, 120m = +3k, 180m with receive target = +5k.</small>
                                    <button type="submit" class="btn btn-success">Save Reward Setup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <strong>Portal And Offer Setup</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.system_setting.portal_setup.update') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>
                                        Minimum Recharge Amount
                                        <span class="text-muted ml-1" data-toggle="tooltip" title="Minimum portal recharge amount. Example: 100000 means 1 lac minimum.">?</span>
                                    </label>
                                    <input type="number" min="1" name="portal_min_recharge_amount" class="form-control" value="{{ old('portal_min_recharge_amount', $portalSetup['portal_min_recharge_amount']) }}" required>
                                </div>

                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="font-weight-bold">
                                            VIP Recharge Offer
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="When active, VIP recharge requirement is reduced by this percentage. Example: VIP 1 normal 1000000 becomes 500000 at 50%.">?</span>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="vip_discount" name="vip_discount" value="1" {{ old('vip_discount', $portalSetup['vip_discount']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="vip_discount">Active</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>VIP Offer Percentage</label>
                                        <input type="number" min="0" max="100" step="0.01" name="vip_discount_percentage" class="form-control" value="{{ old('vip_discount_percentage', $portalSetup['vip_discount_percentage']) }}" required>
                                    </div>
                                </div>

                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="font-weight-bold">
                                            Recharge Bonus Offer
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="When active, this percentage is added as extra recharge bonus. Existing recharge offer logic reads this value.">?</span>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="recharge_offer_reward" name="recharge_offer_reward" value="1" {{ old('recharge_offer_reward', $portalSetup['recharge_offer_reward']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="recharge_offer_reward">Active</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Recharge Bonus Percentage</label>
                                        <input type="number" min="0" max="100" step="0.01" name="recharge_offer_reward_percentage" class="form-control" value="{{ old('recharge_offer_reward_percentage', $portalSetup['recharge_offer_reward_percentage']) }}" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <small class="text-muted mb-2 mb-md-0">This replaces the old dashboard offer buttons.</small>
                                    <button type="submit" class="btn btn-primary">Save Portal Setup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-dark text-white">
                            <strong>Recall Setting</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.system_setting.recall_setup.update') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Portal %
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="This percentage goes to the selected portal during balance recall.">?</span>
                                        </label>
                                        <input type="number" min="0" max="100" step="0.01" name="recall_portal_percentage" class="form-control" value="{{ old('recall_portal_percentage', $recallSetup['recall_portal_percentage']) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Company %
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="This percentage goes to the company user id balance during recall.">?</span>
                                        </label>
                                        <input type="number" min="0" max="100" step="0.01" name="recall_company_percentage" class="form-control" value="{{ old('recall_company_percentage', $recallSetup['recall_company_percentage']) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Company User ID
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="The company share is credited to this user id balance.">?</span>
                                        </label>
                                        <input type="number" min="1" name="recall_company_user_id" class="form-control" value="{{ old('recall_company_user_id', $recallSetup['recall_company_user_id']) }}" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <small class="text-muted mb-2 mb-md-0">Portal % and Company % must total exactly 100.</small>
                                    <button type="submit" class="btn btn-dark">Save Recall Setting</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning">
                            <strong>Withdraw Setting</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.system_setting.withdraw_setup.update') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Minimum Complete Day Count
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Required complete day count before withdraw. Set 0 to disable.">?</span>
                                        </label>
                                        <input type="number" min="0" max="365" name="withdraw_day_requirement" class="form-control" value="{{ old('withdraw_day_requirement', $withdrawSetup['withdraw_day_requirement']) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Scope
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Choose who can withdraw under the current rule. Allowed user ids can override and blocked user ids can stop specific ids.">?</span>
                                        </label>
                                        <select name="withdraw_scope_type" id="withdraw_scope_type" class="form-control" required>
                                            <option value="all_hosts" {{ old('withdraw_scope_type', $withdrawSetup['withdraw_scope_type']) === 'all_hosts' ? 'selected' : '' }}>All Host</option>
                                            <option value="agency_hosts" {{ old('withdraw_scope_type', $withdrawSetup['withdraw_scope_type']) === 'agency_hosts' ? 'selected' : '' }}>Agency Wise Host</option>
                                            <option value="all_agency_owners" {{ old('withdraw_scope_type', $withdrawSetup['withdraw_scope_type']) === 'all_agency_owners' ? 'selected' : '' }}>All Agency Owner Without Host</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="withdraw_scope_agency_wrapper">
                                        <label>
                                            Selected Agency
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Used only when scope is Agency Wise Host. Only hosts under this agency can withdraw.">?</span>
                                        </label>
                                        <select name="withdraw_scope_agency_id" class="form-control">
                                            <option value="">Select Agency</option>
                                            @foreach($agencyOptions as $agency)
                                                <option value="{{ $agency->id }}" {{ (string) old('withdraw_scope_agency_id', $withdrawSetup['withdraw_scope_agency_id']) === (string) $agency->id ? 'selected' : '' }}>
                                                    {{ $agency->name }} ({{ $agency->code }}) - owner {{ $agency->user_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Allowed Amount List
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Comma separated amount list. Example: 300000,500000,700000,1000000">?</span>
                                        </label>
                                        <textarea name="withdraw_allowed_amounts" class="form-control" rows="3" required>{{ old('withdraw_allowed_amounts', $withdrawSetup['withdraw_allowed_amounts']) }}</textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Blocked Day Numbers
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Comma separated day numbers. Example: 27,28,29 blocks those month dates.">?</span>
                                        </label>
                                        <textarea name="withdraw_blocked_days" class="form-control" rows="3">{{ old('withdraw_blocked_days', $withdrawSetup['withdraw_blocked_days']) }}</textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>
                                            Allowed User IDs
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Comma separated user ids that can withdraw even if the main scope or blocked day would stop them.">?</span>
                                        </label>
                                        <textarea name="withdraw_allowed_user_ids" class="form-control" rows="3">{{ old('withdraw_allowed_user_ids', $withdrawSetup['withdraw_allowed_user_ids']) }}</textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>
                                            Blocked User IDs
                                            <span class="text-muted ml-1" data-toggle="tooltip" title="Comma separated user ids that must not withdraw. This block wins before manual allow.">?</span>
                                        </label>
                                        <textarea name="withdraw_blocked_user_ids" class="form-control" rows="3">{{ old('withdraw_blocked_user_ids', $withdrawSetup['withdraw_blocked_user_ids']) }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <div class="alert alert-light border mb-0 w-100">
                                            <strong>Current rule use:</strong>
                                            V4 and V5 host withdraw request now read this day count, amount list, blocked dates, scope, allowed ids, and blocked ids.
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-warning">Save Withdraw Setting</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-warning">
                            <strong>Next Round Frame</strong>
                        </div>
                        <div class="card-body">
                            @if($scheduledFrameData['tables']['scheduled_frame_rules'] !== 'FOUND' || $scheduledFrameData['tables']['scheduled_frame_rule_winners'] !== 'FOUND')
                                <div class="alert alert-warning">
                                    Scheduled frame tables are not ready yet. Run the migration, then use this form.
                                </div>
                            @endif

                            @if($scheduledFrameData['error'])
                                <div class="alert alert-danger">
                                    Scheduled frame load failed: {{ $scheduledFrameData['error'] }}
                                </div>
                            @endif

                            <form action="{{ route('admin.system_setting.frame_rule.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Campaign Name</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Next Round Frame Campaign">
                                </div>

                                <div class="form-group">
                                    <label>Select One Frame From Store</label>
                                    <select name="entry_frame_id" class="form-control" required>
                                        <option value="">Select Frame</option>
                                        @foreach($frameStore as $frame)
                                            <option value="{{ $frame->id }}" {{ old('entry_frame_id') == $frame->id ? 'selected' : '' }}>
                                                {{ $frame->name }} - {{ $frame->effect }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Condition Group</label>
                                    <select name="top_type" id="top_type" class="form-control" required>
                                        <option value="top_agency" {{ old('top_type') == 'top_agency' ? 'selected' : '' }}>Top Agency</option>
                                        <option value="top_host" {{ old('top_type') == 'top_host' ? 'selected' : '' }}>Top Host</option>
                                        <option value="gamer" {{ old('top_type') == 'gamer' ? 'selected' : '' }}>Gamer</option>
                                        <option value="top_gamer" {{ old('top_type') == 'top_gamer' ? 'selected' : '' }}>Top Gamer</option>
                                        <option value="top_agency_host" {{ old('top_type') == 'top_agency_host' ? 'selected' : '' }}>Top Agency Host</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Condition</label>
                                    <select name="condition_type" id="condition_type" class="form-control" required>
                                        <option value="top_rank" {{ old('condition_type') == 'top_rank' ? 'selected' : '' }}>Top Rank</option>
                                        <option value="target" {{ old('condition_type') == 'target' ? 'selected' : '' }}>Target</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Metric</label>
                                    <select name="metric_type" id="metric_type" class="form-control" required>
                                        <option value="gift_receive" {{ old('metric_type') == 'gift_receive' ? 'selected' : '' }}>Total Gift Receive</option>
                                        <option value="total_game_bet" {{ old('metric_type') == 'total_game_bet' ? 'selected' : '' }}>Total Game Bet</option>
                                    </select>
                                    <small class="text-muted">Host and agency use gift receive. Gamer uses total game bet.</small>
                                </div>

                                <div class="form-group" id="target_value_wrapper">
                                    <label>Target Value</label>
                                    <input type="number" min="0" step="0.01" name="target_value" class="form-control" value="{{ old('target_value') }}" placeholder="1000000">
                                </div>

                                <div class="form-group">
                                    <label>Winner Limit</label>
                                    <input type="number" min="1" max="100" name="top_limit" class="form-control" value="{{ old('top_limit', 1) }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Cycle</label>
                                    <select name="schedule_type" class="form-control" required>
                                        <option value="custom" {{ old('schedule_type') == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                                        <option value="hourly" {{ old('schedule_type') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="weekly" {{ old('schedule_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('schedule_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>From Date Time</label>
                                    <input type="datetime-local" name="campaign_starts_at" class="form-control" value="{{ old('campaign_starts_at') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>To Date Time</label>
                                    <input type="datetime-local" name="campaign_ends_at" class="form-control" value="{{ old('campaign_ends_at') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes">{{ old('notes') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Add Frame Rule</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <strong>Rule Queue</strong>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-md-4 mb-2">
                                    <div class="border rounded p-2">
                                        <div class="small text-muted">Running</div>
                                        <div class="font-weight-bold">{{ $runningCount }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="border rounded p-2">
                                        <div class="small text-muted">Next</div>
                                        <div class="font-weight-bold">{{ $upcomingCount }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="border rounded p-2">
                                        <div class="small text-muted">Previous</div>
                                        <div class="font-weight-bold">{{ $previousCount }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Campaign</th>
                                            <th>Frame</th>
                                            <th>Type</th>
                                            <th>Condition</th>
                                            <th>Cycle</th>
                                            <th>Window</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($scheduledFrameData['rules'] as $rule)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="font-weight-bold">{{ $rule->title }}</div>
                                                <small class="text-muted">Winners: {{ $rule->top_limit }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $rule->frame_name }}</div>
                                                <small class="text-muted">{{ $rule->frame_effect }}</small>
                                            </td>
                                            <td>{{ $rule->top_type_label }}</td>
                                            <td>
                                                {{ $rule->condition_type_label }}
                                                @if((float) $rule->target_value > 0)
                                                    <br><small class="text-muted">Target: {{ rtrim(rtrim(number_format($rule->target_value, 2, '.', ''), '0'), '.') }}</small>
                                                @endif
                                                <br><small class="text-muted">{{ $rule->metric_type_label }}</small>
                                            </td>
                                            <td>{{ $rule->window_label }}</td>
                                            <td>
                                                <small>{{ optional($rule->campaign_starts_at)->format('d M Y H:i') }}</small>
                                                <br>
                                                <small>{{ optional($rule->campaign_ends_at)->format('d M Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $rule->status_badge }}">{{ $rule->status_label }}</span>
                                            </td>
                                            <td style="min-width: 160px;">
                                                <form action="{{ route('admin.system_setting.frame_rule.toggle', $rule->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-{{ $rule->status ? 'warning' : 'success' }}">
                                                        {{ $rule->status ? 'Pause' : 'Resume' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.system_setting.frame_rule.delete', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this frame rule?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No frame rules added yet.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <strong>Recent Applied Winners</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Rule</th>
                                    <th>User ID</th>
                                    <th>Frame</th>
                                    <th>Metric</th>
                                    <th>Period</th>
                                    <th>Applied</th>
                                    <th>Removed</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($scheduledFrameData['recentWinners'] as $winner)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($winner->rule)->title ?: 'Deleted Rule' }}</td>
                                    <td>{{ $winner->user_id }}</td>
                                    <td>{{ $winner->frame_effect }}</td>
                                    <td>{{ rtrim(rtrim(number_format($winner->metric_value, 2, '.', ''), '0'), '.') }}</td>
                                    <td>
                                        <small>{{ optional($winner->window_starts_at)->format('d M Y H:i') }}</small>
                                        <br>
                                        <small>{{ optional($winner->window_ends_at)->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>{{ optional($winner->applied_at)->format('d M Y H:i') }}</td>
                                    <td>{{ optional($winner->removed_at)->format('d M Y H:i') ?: 'Running' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No winner history yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.tooltip) {
            window.jQuery('[data-toggle="tooltip"]').tooltip();
        }

        var topType = document.getElementById('top_type');
        var metricType = document.getElementById('metric_type');
        var conditionType = document.getElementById('condition_type');
        var targetWrapper = document.getElementById('target_value_wrapper');
        var withdrawScopeType = document.getElementById('withdraw_scope_type');
        var withdrawScopeAgencyWrapper = document.getElementById('withdraw_scope_agency_wrapper');

        function syncMetricOptions() {
            if (!topType || !metricType) {
                return;
            }

            var value = topType.value;
            if (value === 'gamer' || value === 'top_gamer') {
                metricType.value = 'total_game_bet';
            } else {
                metricType.value = 'gift_receive';
            }
        }

        function syncTargetVisibility() {
            if (!conditionType || !targetWrapper) {
                return;
            }

            targetWrapper.style.display = conditionType.value === 'target' ? 'block' : 'none';
        }

        function syncWithdrawScopeVisibility() {
            if (!withdrawScopeType || !withdrawScopeAgencyWrapper) {
                return;
            }

            withdrawScopeAgencyWrapper.style.display = withdrawScopeType.value === 'agency_hosts' ? 'block' : 'none';
        }

        if (topType) {
            topType.addEventListener('change', syncMetricOptions);
        }

        if (conditionType) {
            conditionType.addEventListener('change', syncTargetVisibility);
        }

        if (withdrawScopeType) {
            withdrawScopeType.addEventListener('change', syncWithdrawScopeVisibility);
        }

        syncMetricOptions();
        syncTargetVisibility();
        syncWithdrawScopeVisibility();
    })();
</script>
@endsection
