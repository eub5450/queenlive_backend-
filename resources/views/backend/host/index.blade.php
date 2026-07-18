@extends('backend.layouts.main')

@section('title')
Active Host List
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
        padding: 2rem;
    }
    
    /* Table Styles */
    .table-responsive {
        border-radius: 16px;
        overflow-x: auto;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        margin: 0;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 0;
        background: #1a1e24;
    }
    
    .table thead th {
        background: #252b33;
        color: #ffc107;
        font-weight: 600;
        padding: 15px 12px;
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
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    .table tbody td {
        padding: 15px 12px;
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
        padding: 15px 12px;
        border-top: 2px solid #ffc107;
        white-space: nowrap;
    }
    
    /* Profile Image Styles */
    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffc107;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(255, 193, 7, 0.2);
    }
    
    .profile-img:hover {
        transform: scale(1.1);
        border-color: #fff;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
    }
    
    /* Flag Image Styles */
    .flag-img {
        width: 40px;
        height: 30px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #ffc107;
        transition: all 0.3s ease;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
    }
    
    .flag-img:hover {
        transform: scale(1.1);
        border-color: #fff;
    }
    
    /* Badge Styles */
    .badge-id {
        background: #17a2b8;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-level {
        background: linear-gradient(145deg, #6f42c1, #e83e8c);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-agency {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .badge-na {
        background: #6c757d;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    
    /* Banned Button */
    .btn-banned {
        background: linear-gradient(145deg, #dc3545, #c82333);
        border: none;
        border-radius: 20px;
        padding: 6px 15px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid transparent;
    }
    
    .btn-banned:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        border-color: #fff;
    }
    
    .btn-banned i {
        margin-right: 5px;
    }
    
    /* DataTables Customization */
    .dataTables_wrapper {
        color: #e0e0e0;
        padding: 15px 0;
    }
    
    .dataTables_filter {
        margin-bottom: 20px;
    }
    
    .dataTables_filter label {
        color: #ffc107;
        font-weight: 600;
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
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .dataTables_length {
        margin-bottom: 20px;
    }
    
    .dataTables_length label {
        color: #ffc107;
        font-weight: 600;
    }
    
    .dataTables_length select {
        background: #252b33;
        color: #e0e0e0;
        border: 2px solid #2f3740;
        border-radius: 8px;
        padding: 5px 10px;
        margin: 0 5px;
        cursor: pointer;
    }
    
    .dataTables_length select:focus {
        border-color: #ffc107;
        outline: none;
    }
    
    .dataTables_paginate {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 5px;
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: #e0e0e0 !important;
        border: 1px solid #2f3740;
        border-radius: 8px;
        padding: 8px 15px;
        margin: 0 2px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107;
        color: #1a1e24 !important;
        border-color: #ffc107;
        font-weight: 700;
    }
    
    .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #2f3740;
        border-color: #ffc107;
        transform: translateY(-2px);
    }
    
    .dataTables_info {
        margin-top: 15px;
        color: #a0a8b5;
        font-size: 0.9rem;
    }
    
    /* Summary Stats Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        background: #252b33;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #2f3740;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #ffc107, #ffb300);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: #ffc107;
        box-shadow: 0 10px 30px rgba(255, 193, 7, 0.15);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: #1a1e24;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        border: 1px solid #ffc107;
    }
    
    .stat-icon i {
        font-size: 24px;
        color: #ffc107;
    }
    
    .stat-label {
        color: #a0a8b5;
        font-size: 0.9rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .stat-value {
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
    }
    
    /* Email Style */
    .email-text {
        color: #17a2b8;
        font-size: 0.85rem;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .stats-container {
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
        
        .stats-container {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .dataTables_filter input {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
        }
        
        .dataTables_paginate {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            font-size: 0.85rem;
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
    
    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Tooltip */
    .tooltip-icon {
        cursor: help;
        margin-left: 5px;
        color: #ffc107;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<!--Content Start-->
<div class="body-content">
    
    @php
    // Calculate statistics
    $total_hosts = $users->count();
    $total_levels = $users->sum('level');
    $avg_level = $total_hosts > 0 ? round($total_levels / $total_hosts, 1) : 0;
    $total_agencies = $users->filter(function($user) {
        $agency = DB::table('host_data')->where('user_id', $user->id)->first();
        return $agency && $agency->agency_code;
    })->count();
    @endphp
    
    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Total Active Hosts</div>
            <div class="stat-value">{{ $total_hosts }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-label">Average Level</div>
            <div class="stat-value">{{ $avg_level }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-label">With Agency</div>
            <div class="stat-value">{{ $total_agencies }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-label">Countries</div>
            <div class="stat-value">{{ $users->unique('country_id')->count() }}</div>
        </div>
    </div>
    
    <!-- Main Card -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-server mr-2"></i>
                <h4 class="mb-0 d-inline-block">Active Host List</h4>
            </div>
            <div>
                <span style="color: #ffc107;">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ date('d M Y, h:i A') }}
                </span>
            </div>
        </div>
       
            <div class="table-responsive">
                <table class="table table-hover" id="hostTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>ID</th>
                            <th>Level</th>
                            <th>Email</th>
                            <th>Agency</th>
                            <th>Country</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($users as $item)
                        @php
                        $agency = DB::table('host_data')
                                    ->join('agencies', 'agencies.code', 'host_data.agency_code')
                                    ->where('host_data.user_id', $item->id)
                                    ->first();
                        $country = App\Models\Country::find($item->country_id);
                        @endphp
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>
                                <img src="{{ \App\Support\MediaPathHelper::publicUrl($item->profile) }}" class="profile-img" alt="Profile" 
                                     title="{{$item->name}}'s profile">
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #fff;">{{$item->name}}</span>
                                @if($item->id == 1)
                                    <i class="fas fa-crown tooltip-icon" title="Admin" style="color: #ffc107;"></i>
                                @endif
                            </td>
                            <td><span class="badge-id">#{{$item->id}}</span></td>
                            <td><span class="badge-level">Level {{$item->level}}</span></td>
                            <td>
                                <span class="email-text">
                                    <i class="fas fa-envelope mr-1" style="color: #ffc107;"></i>
                                    {{$item->email}}
                                </span>
                            </td>
                            <td>
                                @if($agency)
                                    <span class="badge-agency" title="{{$agency->name}}">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ Str::limit($agency->name, 20) }}
                                    </span>
                                @else
                                    <span class="badge-na">
                                        <i class="fas fa-times mr-1"></i> No Agency
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($country)
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <img src="{{ \App\Support\MediaPathHelper::publicUrl($country->flag) }}" class="flag-img" alt="{{$country->name}}" title="{{$country->name}}">
                                        <span style="color: #e0e0e0;">{{$country->name}}</span>
                                    </div>
                                @else
                                    <span class="badge-na">
                                        <i class="fas fa-globe mr-1"></i> N/A
                                    </span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-banned" onclick="confirmBan({{$item->id}})">
                                    <i class="fas fa-ban"></i> Ban
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>ID</th>
                            <th>Level</th>
                            <th>Email</th>
                            <th>Agency</th>
                            <th>Country</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
           
        </div>
    </div>
</div>

<!-- Ban Confirmation Modal -->
<div class="modal fade" id="banModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: #1a1e24; border: 2px solid #ffc107; border-radius: 20px;">
            <div class="modal-header" style="border-bottom: 1px solid #2f3740;">
                <h5 class="modal-title" style="color: #ffc107;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Ban
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color: #ffc107;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="color: #e0e0e0;">
                Are you sure you want to ban this host?
            </div>
            <div class="modal-footer" style="border-top: 1px solid #2f3740;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background: #2f3740; border: none; border-radius: 50px; padding: 8px 20px;">Cancel</button>
                <a href="#" id="confirmBanBtn" class="btn btn-danger" style="border-radius: 50px; padding: 8px 20px;">
                    <i class="fas fa-ban mr-1"></i>Ban Host
                </a>
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
    $('#hostTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search Hosts:",
            searchPlaceholder: "Type to search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ hosts",
            infoEmpty: "Showing 0 to 0 of 0 hosts",
            infoFiltered: "(filtered from _MAX_ total hosts)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        order: [[0, 'asc']],
        columnDefs: [
            { targets: [1,8], orderable: false },
            { targets: [4,5,6,7], className: 'text-center' }
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: true,
        initComplete: function() {
            // Add animation to rows
            $('#hostTable tbody tr').each(function(index) {
                $(this).css('animation', `fadeIn 0.3s ease-out ${index * 0.05}s forwards`);
                $(this).css('opacity', '0');
            });
        }
    });
    
    // Add tooltips
    $('[title]').tooltip();
});

// Ban confirmation function
function confirmBan(userId) {
    $('#confirmBanBtn').attr('href', '/ban-host/' + userId);
    $('#banModal').modal('show');
}
</script>
@endpush
