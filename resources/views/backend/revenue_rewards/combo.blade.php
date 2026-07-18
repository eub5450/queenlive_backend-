@extends('backend.layouts.main')
@section('title')
Combo Gift Settings
@endsection
@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Gift Combo / Streak Multiplier</h4>
                <span class="badge badge-warning">Revenue</span>
            </div>

            <form action="{{ URL::to('admin/combo-settings-save') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Combo Enabled</label><br>
                        <label class="mt-2">
                            <input type="checkbox" name="combo_enabled" value="1" {{ (int) $settings->combo_enabled === 1 ? 'checked' : '' }}>
                            Active
                        </label>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Decay Window (ms)</label>
                        <input type="number" name="combo_decay_ms" class="form-control" min="800" max="15000" value="{{ $settings->combo_decay_ms }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Max Multiplier</label>
                        <input type="number" name="combo_max" class="form-control" min="1" max="9999" value="{{ $settings->combo_max }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Milestones CSV</label>
                        <input type="text" name="combo_milestones" class="form-control" value="{{ $settings->combo_milestones }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Save Combo Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
