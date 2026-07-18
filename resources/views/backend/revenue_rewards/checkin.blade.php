@extends('backend.layouts.main')
@section('title')
Daily Check-in Settings
@endsection
@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Daily Check-in Reward Ladder</h4>
                <span class="badge badge-info">Habit / DAU</span>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-muted text-uppercase small">Today Claims</div>
                            <h3 class="mb-0">{{ number_format($stats['today_claims']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-muted text-uppercase small">Total Claims</div>
                            <h3 class="mb-0">{{ number_format($stats['total_claims']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ URL::to('admin/checkin-reward-save') }}" method="post" class="mb-4">
                @csrf
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Day</label>
                        <input type="number" name="day" class="form-control" min="1" max="30" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Reward Amount</label>
                        <input type="number" name="reward_amount" class="form-control" min="0" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Status</label><br>
                        <label class="mt-2"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn btn-success">Save Reward</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Reward</th>
                            <th>Status</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>Day {{ $row->day }}</td>
                                <td>{{ number_format($row->reward_amount) }}</td>
                                <td>
                                    <span class="badge badge-{{ $row->is_active ? 'success' : 'secondary' }}">
                                        {{ $row->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $row->updated_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No check-in rewards available. Run migration first.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
