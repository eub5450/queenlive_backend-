@extends('subadmin.layouts.main')
@section('title', 'QueenLive Subadmin Dashboard')
@section('content')
@php
    $countFmt = function ($value) {
        return number_format((int) $value);
    };
@endphp
<div class="body-content">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#132238 0%,#1f4f8a 55%,#2ba6cb 100%);">
                <div class="card-body text-white py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50 text-uppercase mb-2">QueenLive Control</div>
                            <h3 class="mb-2 font-weight-bold">Subadmin Dashboard</h3>
                            <div class="small text-white-50">Host approvals, profile reviews, live monitoring, agency control, and ban actions.</div>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="{{ URL::to('subadmin/sub_admin/profile_pending') }}" class="btn btn-light btn-sm mb-2 mb-md-0 mr-2">Pending Profiles</a>
                            <a href="{{ URL::to('subadmin/sub_admin/pending_host') }}" class="btn btn-outline-light btn-sm">Pending Hosts</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Pending Profiles</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">{{ $countFmt($pending_profile) }}</h3>
                        <span class="badge badge-warning p-2"><i class="typcn typcn-image-outline"></i></span>
                    </div>
                    <div class="mt-3 small text-muted">User image and profile update approval queue.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Pending Hosts</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">{{ $countFmt($pending_host) }}</h3>
                        <span class="badge badge-danger p-2"><i class="typcn typcn-user-add-outline"></i></span>
                    </div>
                    <div class="mt-3 small text-muted">Hosts waiting for activation or rejection.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Active Hosts</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">{{ $countFmt($active_host) }}</h3>
                        <span class="badge badge-success p-2"><i class="typcn typcn-microphone-outline"></i></span>
                    </div>
                    <div class="mt-3 small text-muted">Currently approved hosts in the system.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Live Rooms</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">{{ $countFmt($active_live) }}</h3>
                        <span class="badge badge-primary p-2"><i class="typcn typcn-media-play-outline"></i></span>
                    </div>
                    <div class="mt-3 small text-muted">Current audio and video lives tracked by subadmin.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Total Users</div>
                    <h3 class="mb-2">{{ $countFmt($total_users) }}</h3>
                    <div class="small text-muted">All QueenLive users visible to subadmin tools.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-2">Total Agency</div>
                    <h3 class="mb-2">{{ $countFmt($total_agency) }}</h3>
                    <div class="small text-muted">Active {{ $countFmt($active_agency) }} | Pending {{ $countFmt($pending_agency) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-4 col-md-12">
            <div class="card mb-4 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase mb-3">Quick Actions</div>
                    <div class="d-flex flex-wrap">
                        <a href="{{ URL::to('subadmin/sub_admin/add-host') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2">Add Host</a>
                        <a href="{{ URL::to('subadmin/sub_admin/agency_create') }}" class="btn btn-outline-info btn-sm mr-2 mb-2">Create Agency</a>
                        <a href="{{ URL::to('subadmin/sub_admin/ranking') }}" class="btn btn-outline-success btn-sm mr-2 mb-2">Ranking</a>
                        <a href="{{ URL::to('subadmin/sub_admin/live-list') }}" class="btn btn-outline-dark btn-sm mr-2 mb-2">Live List</a>
                        <a href="{{ URL::to('subadmin/sub_admin/ban_id') }}" class="btn btn-outline-danger btn-sm mr-2 mb-2">Ban ID</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Pending Profile Queue</h4>
                <div class="small text-muted">Review profile image and name updates before approval.</div>
            </div>
            <a href="{{ URL::to('subadmin/sub_admin/profile_pending') }}" class="btn btn-outline-primary btn-sm">Open Full Queue</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Preview</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        @php $user = App\Models\User::find($item->user_id); @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user ? $user->id : '-' }}</td>
                            <td>{{ $item->name ?: ($user ? $user->name : 'Unknown user') }}</td>
                            <td>
                                <img class="img-fluid rounded shadow-sm" style="width:72px;height:72px;object-fit:cover;" src="{{ URL::to($item->image) }}" alt="{{ $item->name }}">
                            </td>
                            <td class="text-right">
                                <a href="{{ URL::to('subadmin/sub_admin/profile_approved', $item->id) }}" class="btn btn-sm btn-success">Approve</a>
                                <a href="{{ URL::to('subadmin/sub_admin/profile_reject', $item->id) }}" class="btn btn-sm btn-danger">Reject</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No pending profile requests right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
