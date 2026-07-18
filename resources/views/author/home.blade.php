@extends('author.layouts.main')
@section('content')
@php
    $fmt = function ($value) {
        return number_format((float) $value, 0);
    };
    $money = function ($value) {
        return number_format((float) $value, 2);
    };
@endphp

<div class="layout-content">
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h4 class="font-weight-bold mb-1">Country Admin Dashboard</h4>
                <div class="text-muted small">
                    {{ $dashboard['country_name'] }} only | {{ $dashboard['admin_name'] }} | {{ $dashboard['admin_email'] }}
                </div>
            </div>
            <div class="mt-3 mt-sm-0">
                <span class="badge badge-primary p-2">Country ID {{ $dashboard['country_id'] }}</span>
                <span class="badge badge-success p-2">Live Rooms {{ $fmt($dashboard['live_rooms']) }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Location Active Host</div>
                        <h3 class="mb-2">{{ $fmt($dashboard['active_hosts']) }}</h3>
                        <div class="small text-muted">Pending {{ $fmt($dashboard['pending_hosts']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Location Total Agency</div>
                        <h3 class="mb-2">{{ $fmt($dashboard['agencies']) }}</h3>
                        <div class="small text-muted">Active {{ $fmt($dashboard['active_agencies']) }} | Pending {{ $fmt($dashboard['pending_agencies']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Location Total Recharge</div>
                        <h3 class="mb-2">{{ $money($dashboard['portal_recharge']) }}</h3>
                        <div class="small text-muted">Recall {{ $money($dashboard['portal_recall']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Location Total Sending</div>
                        <h3 class="mb-2">{{ $money($dashboard['gift_sent_value']) }}</h3>
                        <div class="small text-muted">Portal transfer {{ $money($dashboard['portal_transfer']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4 border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Location Total Receiving</div>
                        <h3 class="mb-2">{{ $money($dashboard['gift_received_value']) }}</h3>
                        <div class="small">Country receiver scope only</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4 border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Country Users</div>
                        <h3 class="mb-2">{{ $fmt($dashboard['total_users']) }}</h3>
                        <div class="small">Active {{ $fmt($dashboard['active_users']) }} | Blocked {{ $fmt($dashboard['blocked_users']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4 border-0 shadow-sm bg-dark text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Balance Snapshot</div>
                        <h3 class="mb-2">{{ $money($dashboard['total_balance']) }}</h3>
                        <div class="small">Hold {{ $money($dashboard['hold_balance']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-flex flex-wrap">
                    <a class="btn btn-sm btn-outline-primary mr-2 mb-2" href="{{ route('country.author.host-search') }}">Search Profile</a>
                    <a class="btn btn-sm btn-outline-danger mr-2 mb-2" href="{{ route('country.author.host-search') }}">Add Live Data</a>
                    <a class="btn btn-sm btn-outline-dark mr-2 mb-2" href="{{ route('country.author.protal') }}">Portal History</a>
                    <a class="btn btn-sm btn-outline-info mr-2 mb-2" href="{{ route('country.author.agency-list') }}">Agency Details</a>
                    <a class="btn btn-sm btn-outline-warning mr-2 mb-2" href="{{ route('country.author.host-pending') }}">Host Active / Reject</a>
                    <a class="btn btn-sm btn-outline-secondary mr-2 mb-2" href="{{ route('country.author.host-ranking') }}">Location Ranking</a>
                    <a class="btn btn-sm btn-outline-success mr-2 mb-2" href="{{ route('country.author.protal-recall') }}">Location ID Recall</a>
                    <a class="btn btn-sm btn-outline-primary mr-2 mb-2" href="{{ route('country.author.banner') }}">Add Banner</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Country Users</h6>
                        <a href="{{ route('country.author.host-search') }}" class="btn btn-sm btn-outline-primary">Search profile</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>{{ $money($user->balance) }}</td>
                                        <td>{!! (int) $user->status === 1 ? '<span class="badge badge-success">active</span>' : '<span class="badge badge-danger">blocked</span>' !!}</td>
                                        <td>
                                            @if((int) $user->is_host_id === 1)
                                                <span class="badge badge-danger">host</span>
                                            @elseif((int) $user->is_host_id === 2)
                                                <span class="badge badge-warning">pending host</span>
                                            @elseif((int) $user->is_agency === 1)
                                                <span class="badge badge-info">agency</span>
                                            @else
                                                <span class="badge badge-secondary">user</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No users found for this country.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Location Ranking</h6>
                        <a href="{{ route('country.author.host-ranking') }}" class="btn btn-sm btn-outline-warning">Full ranking</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($topHosts as $host)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $host->name }}</strong>
                                    <div class="small text-muted">ID {{ $host->id }} | {{ $host->agency_name ?: 'No agency' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold">{{ $money($host->total_recived_gifts) }}</div>
                                    <div class="small text-muted">@if((int) $host->hosting_type === 2) video @else audio @endif</div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">No active hosts found for this country.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
