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
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="{{ URL::to($user->profile) }}" class="rounded-circle" style="width:110px;height:110px;object-fit:cover;" alt="{{ $user->name }}">
                </div>
                <div class="col-md-6">
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <div class="text-muted">User ID {{ $user->id }} | {{ $user->email }}</div>
                    <div class="text-muted">{{ $country ? ucfirst($country->name) : 'Unknown country' }} | Balance {{ $money($user->balance) }}</div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <a href="{{ route('country.author.host-search') }}" class="btn btn-outline-primary btn-sm">Search Profile</a>
                    @if($user->is_host_id != 1)
                        <a href="{{ route('author.host.active', ['id' => $user->id]) }}" class="btn btn-success btn-sm">Host Active</a>
                    @else
                        <a href="{{ route('author.host.inactive', ['id' => $user->id]) }}" class="btn btn-danger btn-sm">Host Reject</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Agency Details</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Level</th><td>{{ $user->level }}</td></tr>
                        <tr><th>Phone</th><td>{{ $info ? $info->phone : $user->phone }}</td></tr>
                        <tr><th>NID</th><td>{{ $info ? $info->nid : '-' }}</td></tr>
                        <tr><th>VIP</th><td>{{ $user->is_vip }}</td></tr>
                        <tr><th>Agency</th><td>{{ $agencyInfo ? $agencyInfo->name : 'No agency' }}</td></tr>
                        <tr><th>Agency Code</th><td>{{ $agencyInfo ? $agencyInfo->code : '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Portal History</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Recharge</th><td>{{ $money($protalRecharge) }}</td></tr>
                        <tr><th>Transfer</th><td>{{ $money($protalTransfer) }}</td></tr>
                        <tr><th>Recall</th><td>{{ $money($recallProtalRecharge) }}</td></tr>
                        <tr><th>Balance</th><td>{{ $money($protalRecharge - ($protalTransfer - $recallProtalRecharge)) }}</td></tr>
                    </table>
                </div>
            </div>

            @if($info)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white"><strong>ID Images</strong></div>
                    <div class="card-body text-center">
                        <img src="{{ URL::to($info->image) }}" class="img-fluid mb-3" style="max-height:160px;" alt="Host photo">
                        <img src="{{ URL::to($info->photo_id) }}" class="img-fluid mb-3" style="max-height:160px;" alt="Photo ID">
                        <img src="{{ URL::to($info->selfie) }}" class="img-fluid" style="max-height:160px;" alt="Selfie">
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            @if($liveSummary)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>Add Live Data</strong>
                        <a href="{{ route('author.host.toggle-type', ['id' => $user->id]) }}" class="btn btn-sm btn-outline-warning">
                            @if((int) $liveSummary['hosting_type'] === 2) Make Audio @else Make Video @endif
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3"><div class="text-muted small">Hosting Type</div><div class="font-weight-bold">@if((int) $liveSummary['hosting_type'] === 2) Video @else Audio @endif</div></div>
                            <div class="col-md-3"><div class="text-muted small">Active Days</div><div class="font-weight-bold">{{ $liveSummary['active_days'] }}</div></div>
                            <div class="col-md-3"><div class="text-muted small">Duration</div><div class="font-weight-bold">{{ $liveSummary['duration'] }}</div></div>
                            <div class="col-md-3"><div class="text-muted small">Points</div><div class="font-weight-bold">{{ $money($liveSummary['points']) }}</div></div>
                        </div>
                        <div class="text-muted small mt-3">Date range {{ $liveSummary['date_range'] }}</div>
                    </div>
                </div>
            @endif

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Recharge History</strong></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr><th>ID</th><th>Date</th><th>Approved By</th><th>Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($protalRechargeDetails as $item)
                                @php $approvedBy = App\Models\User::find($item->recharge_by); @endphp
                                <tr>
                                    <td>{{ $item->trxid }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $approvedBy ? $approvedBy->name : '-' }}</td>
                                    <td>{{ $money($item->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No recharge history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Transfer History</strong></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr><th>ID</th><th>Date</th><th>Receiver</th><th>Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($protalTransferDetails as $item)
                                <tr>
                                    <td>{{ $item->trxid }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $item->user_id }}</td>
                                    <td>{{ $money($item->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No transfer history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Gift History</strong></div>
                <div class="row no-gutters">
                    <div class="col-md-6 border-right">
                        <div class="p-3">
                            <h6 class="mb-3">Total Sending</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead><tr><th>To</th><th>Value</th><th>Date</th></tr></thead>
                                    <tbody>
                                        @forelse($sandingHistorys as $item)
                                            <tr><td>{{ $item->reciever_id }}</td><td>{{ $money($item->value) }}</td><td>{{ $item->date }}</td></tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">No sending history.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3">
                            <h6 class="mb-3">Total Receiving</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead><tr><th>From</th><th>Value</th><th>Date</th></tr></thead>
                                    <tbody>
                                        @forelse($recivingHistorys as $item)
                                            <tr><td>{{ $item->sander_id }}</td><td>{{ $money($item->value) }}</td><td>{{ $item->date }}</td></tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">No receiving history.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
