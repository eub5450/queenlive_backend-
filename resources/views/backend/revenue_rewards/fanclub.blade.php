@extends('backend.layouts.main')
@section('title')
Fan Club Settings
@endsection
@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Fan Club / Guardian Tiers</h4>
                <span class="badge badge-warning">Recurring Revenue</span>
            </div>

            <div class="row mb-4">
                @foreach($stats as $label => $count)
                    <div class="col-md-3 mb-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body py-3">
                                <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $label) }}</div>
                                <h3 class="mb-0">{{ number_format($count) }}</h3>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ URL::to('admin/fanclub-tier-save') }}" method="post" class="mb-4">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Tier</label>
                        <input type="text" name="tier" class="form-control" placeholder="bronze" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Price</label>
                        <input type="number" name="price" class="form-control" min="0" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Days</label>
                        <input type="number" name="duration_days" class="form-control" value="30" min="1" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Sort</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status</label><br>
                        <label class="mt-2"><input type="checkbox" name="enabled" value="1" checked> Enabled</label>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Perks JSON</label>
                        <textarea name="perks_json" class="form-control" rows="3">{"badge":"bronze","entry_boost":2}</textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <button type="submit" class="btn btn-success">Save Tier</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Price</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Sort</th>
                            <th>Perks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiers as $tier)
                            @php
                                $tierName = data_get($tier, 'tier', 'unknown');
                                $tierPrice = (int) data_get($tier, 'price', 0);
                                $tierDays = (int) data_get($tier, 'duration_days', 30);
                                $tierEnabled = (int) data_get($tier, 'enabled', 1);
                                $tierSort = (int) data_get($tier, 'sort_order', 0);
                                $tierPerks = data_get($tier, 'perks_json', '{}');
                            @endphp
                            <tr>
                                <td>{{ $tierName }}</td>
                                <td>{{ number_format($tierPrice) }}</td>
                                <td>{{ $tierDays }}</td>
                                <td>
                                    <span class="badge badge-{{ $tierEnabled ? 'success' : 'secondary' }}">
                                        {{ $tierEnabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                                <td>{{ $tierSort }}</td>
                                <td><code style="white-space:normal">{{ $tierPerks }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No tier data available. Run migration first.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
