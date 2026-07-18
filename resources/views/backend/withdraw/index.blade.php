@extends('backend.layouts.main')

@section('title')
Agency List
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
<style>
    /* Dark Theme - Main Background */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
    }
    
    /* Modern Dark Card Styles */
    .body-content {
        background: transparent;
    }
    
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
        overflow: hidden;
        background: #1a1e24;
    }
    
    .card:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.8);
    }
    
    .card-header {
        background: linear-gradient(145deg, #252b33, #1a1e24);
        border-bottom: 2px solid #ffc107;
        border-radius: 20px 20px 0 0 !important;
        padding: 1.5rem 2rem;
    }
    
    .card-header h4 {
        color: #ffc107;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 0;
        font-size: 1.5rem;
    }
    
    .card-header h4 i {
        margin-right: 10px;
        color: #ffc107;
    }
    
    .card-body {
        background: #1a1e24;
        padding: 1.5rem;
    }
    
    /* Table Styles - Dark Modern */
    .table-responsive {
        border-radius: 16px;
        overflow-x: auto;
        overflow-y: visible;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        margin: 0;
        width: 100%;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 0;
        background: #1a1e24;
        table-layout: auto;
    }
    
    .table thead th {
        background: #252b33;
        color: #ffc107;
        font-weight: 600;
        padding: 15px 10px;
        font-size: 0.85rem;
        text-transform: uppercase;
        border-bottom: 2px solid #ffc107;
        white-space: nowrap;
    }
    
    .table tbody tr {
        background: #252b33;
        transition: all 0.3s ease;
        border-bottom: 1px solid #2f3740;
    }
    
    .table tbody tr:hover {
        background: #2f3740;
    }
    
    .table tbody td {
        padding: 12px 10px;
        border: none;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #e0e0e0;
        white-space: nowrap;
    }
    
    .table tfoot th {
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 15px 10px;
        border-top: 2px solid #ffc107;
        white-space: nowrap;
    }
    
    /* Profile Image Style */
    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffc107;
        transition: all 0.3s ease;
    }
    
    .profile-img:hover {
        transform: scale(1.1);
        border-color: #fff;
    }
    
    /* Badge Styles */
    .badge-id {
        background: #17a2b8;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-agency {
        background: #17a2b8;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-super {
        background: #ffc107;
        color: #1a1e24;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-na {
        background: #6c757d;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
    }
    
    /* Status Indicators */
    .status-approved {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        white-space: nowrap;
    }
    
    .status-pending {
        background: #ffc107;
        color: #1a1e24;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        white-space: nowrap;
    }
    
    /* Withdraw Type Badge */
    .withdraw-super {
        background: linear-gradient(145deg, #6f42c1, #e83e8c);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        white-space: nowrap;
    }
    
    .withdraw-agency {
        background: linear-gradient(145deg, #17a2b8, #20c997);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        white-space: nowrap;
    }
    
    /* Amount Styling */
    .amount {
        font-weight: 700;
        color: #ffc107;
    }
    
    /* DataTables Customization */
    .dataTables_wrapper {
        color: #e0e0e0;
        padding: 15px 0;
        width: 100%;
    }
    
    .dataTables_filter {
        margin-bottom: 20px;
    }
    
    .dataTables_filter input {
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 8px 15px;
        background: #252b33;
        color: #e0e0e0;
        width: 250px;
        margin-left: 10px;
    }
    
    .dataTables_filter input:focus {
        border-color: #ffc107;
        outline: none;
    }
    
    .dataTables_length select {
        background: #252b33;
        color: #e0e0e0;
        border: 1px solid #2f3740;
        border-radius: 5px;
        padding: 5px;
        margin: 0 5px;
    }
    
    .dataTables_paginate {
        margin-top: 20px;
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: #e0e0e0 !important;
        border: 1px solid #2f3740;
        border-radius: 5px;
        padding: 5px 12px;
        margin: 0 3px;
        cursor: pointer;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #ffc107;
        color: #1a1e24 !important;
    }
    
    .dataTables_info {
        margin-top: 15px;
        color: #a0a8b5;
    }
    
    /* Summary Cards */
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        background: #252b33;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #2f3740;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        border-color: #ffc107;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        background: #1a1e24;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        border: 1px solid #ffc107;
    }
    
    .stat-icon i {
        font-size: 20px;
        color: #ffc107;
    }
    
    .stat-label {
        color: #a0a8b5;
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .stat-value {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .summary-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .card-header h4 {
            font-size: 1.2rem;
        }
        
        .summary-stats {
            grid-template-columns: 1fr;
        }
        
        .dataTables_filter input {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
        }
    }
    
    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1e24;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #2f3740;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
</style>
@endpush

@section('content')
<!--Content Start-->
<div class="body-content">

    <!-- Summary Statistics Cards -->
    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Total Withdrawals</div>
            <div class="stat-value">{{ $summary['total_withdrawals'] }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ $summary['approved_count'] }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $summary['pending_count'] }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-label">Total Points</div>
            <div class="stat-value">{{ number_format($summary['total_points']) }}</div>
        </div>
    </div>
    
    <!-- Main Card -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-history mr-2"></i>
                <h4 class="mb-0 d-inline-block">Withdraw List</h4>
            </div>
            <div>
                <span style="color: #ffc107;">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ date('d M Y') }}
                </span>
            </div>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table class="table table-hover" id="withdrawTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Basic</th>
                            <th>Agency</th>
                            <th>Apps</th>
                            <th>Total</th>
                            <th>Agency</th>
                            <th>Super</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i=0; @endphp
                        @foreach($data as $row)
                        @php
                        $user = $users->get($row->host_id);
                        $agency = $agencies->get($row->agency_id);
                        $super_agency = $agencies->get($row->super_agency_id);
                        @endphp
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user && $user->profile)
                                        <img src="{{ URL::to($user->profile) }}" class="profile-img mr-2" alt="profile">
                                    @endif
                                    <div>
                                        <div style="font-weight: 600; color: #fff;">{{ $user->name ?? 'N/A' }}</div>
                                        <span class="badge-id">#{{ $user->id ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="amount">{{ number_format($row->basic_coin) }}</td>
                            <td class="amount">{{ number_format($row->agency_profit) }}</td>
                            <td class="amount">{{ number_format($row->apps_profit) }}</td>
                            <td class="amount" style="color: #ffc107; font-weight: 700;">{{ number_format($row->total) }}</td>
                            <td>
                                @if($agency)
                                    <span class="badge-agency">{{ $agency->name }}</span>
                                @else
                                    <span class="badge-na">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($super_agency)
                                    <span class="badge-super">{{ $super_agency->name }}</span>
                                @else
                                    <span class="badge-na">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($row->is_super_agency_withdraw == 1)
                                    <span class="withdraw-super">
                                        <i class="fas fa-crown mr-1"></i> Super
                                    </span>
                                @else
                                    <span class="withdraw-agency">
                                        <i class="fas fa-building mr-1"></i> Agency
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="color: #fff;">{{ date('d M Y', strtotime($row->created_at)) }}</div>
                                <small style="color: #888;">{{ date('h:i A', strtotime($row->created_at)) }}</small>
                            </td>
                            <td>
                                @if($row->status == 1)
                                    <span class="status-approved">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @else
                                    <span class="status-pending">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th>{{ number_format($summary['total_basic']) }}</th>
                            <th>{{ number_format($summary['total_agency_profit']) }}</th>
                            <th>{{ number_format($summary['total_apps_profit']) }}</th>
                            <th>{{ number_format($summary['total_points']) }}</th>
                            <th colspan="5"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with responsive option
    $('#withdrawTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search:",
            searchPlaceholder: "Type to search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        order: [[0, 'desc']],
        columnDefs: [
            { targets: [2,3,4,5], className: 'text-right' }
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: true
    });
});
</script>
@endpush

