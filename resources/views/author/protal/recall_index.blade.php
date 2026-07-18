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
                <h4 class="mb-0">Recall History</h4>
                <a href="{{ route('country.author.protal-recall') }}" class="btn btn-outline-primary btn-sm">New Recall</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover basic">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Portal</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            @php $user = App\Models\User::find($item->recharge_by); @endphp
                            <tr>
                                <td>{{ $item->trxid }}</td>
                                <td>{{ $item->portal_name }}</td>
                                <td>{{ $money($item->amount) }}</td>
                                <td>{{ $item->date }}</td>
                                <td>{{ $user ? $user->name : $item->recharge_by }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No recall history found for this country.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
