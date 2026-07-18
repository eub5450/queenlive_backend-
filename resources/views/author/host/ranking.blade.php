@extends('author.layouts.main')
@section('content')
@php
    $money = function ($value) {
        return number_format((float) $value, 0);
    };
@endphp
<div class="body-content container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Location Ranking</h4>
                    <div class="text-muted small">{{ $country ? ucfirst($country->name) : 'Country '.$countryId }} host ranking</div>
                </div>
                <a href="{{ route('author.dashboard') }}" class="btn btn-outline-primary btn-sm">Back to dashboard</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover basic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>Host</th>
                            <th>Agency</th>
                            <th>Type</th>
                            <th>Total Receiving</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hosts as $index => $host)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><img src="{{ URL::to($host->profile) }}" style="width:52px;height:52px;object-fit:cover;border-radius:50%;" alt="{{ $host->name }}"></td>
                                <td>{{ $host->name }}<br><span class="small text-muted">ID {{ $host->id }}</span></td>
                                <td>{{ $host->agency_name ?: 'No agency' }}</td>
                                <td>@if((int) $host->hosting_type === 2) Video @else Audio @endif</td>
                                <td>{{ $money($host->total_recived_gifts) }}</td>
                                <td>{{ $money($host->balance) }}</td>
                                <td>@if((int) $host->status === 1)<span class="badge badge-success">active</span>@else<span class="badge badge-danger">blocked</span>@endif</td>
                                <td><a href="{{ route('author.host.profile', ['id' => $host->id]) }}" class="btn btn-sm btn-outline-info">Profile</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No host ranking data found for this country.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
