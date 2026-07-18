@extends('backend.layouts.main')

@section('title')
Agency List
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Dark Theme */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
    }
    
    /* Main Card */
    .main-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        background: #1a1e24;
        margin: 20px 0;
        overflow: hidden;
    }
    
    .card-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.5rem 2rem;
    }
    
    .card-header h4 {
        color: #ffc107;
        font-weight: 700;
        margin: 0;
        font-size: 1.5rem;
    }
    
    .card-header h4 i {
        margin-right: 10px;
        color: #ffc107;
    }
    
    .card-body {
        padding: 2rem;
        background: #1a1e24;
    }
    
    /* Table Styles */
    .table-responsive {
        border-radius: 16px;
        overflow-x: auto;
        background: #1a1e24;
    }
    
    .table {
        width: 100%;
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
    }
    
    .table tbody td {
        padding: 15px 12px;
        border: none;
        vertical-align: middle;
        color: #e0e0e0;
        white-space: nowrap;
    }
    
    .table tfoot th {
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 15px 12px;
        border-top: 2px solid #ffc107;
    }
    
    /* Profile Image */
    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ffc107;
        transition: all 0.3s ease;
    }
    
    .profile-img:hover {
        transform: scale(1.1);
        border-color: #fff;
    }
    
    /* Flag Image */
    .flag-img {
        width: 40px;
        height: 25px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #ffc107;
    }
    
    /* Badge Styles */
    .badge-status {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
    }
    
    .badge-active {
        background: #28a745;
        color: white;
    }
    
    .badge-inactive {
        background: #dc3545;
        color: white;
    }
    
    .badge-pending {
        background: #ffc107;
        color: #1a1e24;
    }
    
    /* Button Styles */
    .btn-action {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 2px;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #1a1e24;
    }
    
    .btn-edit:hover {
        background: #e0a800;
        color: #1a1e24;
        transform: translateY(-2px);
    }
    
    .btn-active {
        background: #28a745;
        color: white;
    }
    
    .btn-active:hover {
        background: #218838;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-inactive {
        background: #dc3545;
        color: white;
    }
    
    .btn-inactive:hover {
        background: #c82333;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-reject {
        background: #ffc107;
        color: #1a1e24;
    }
    
    .btn-reject:hover {
        background: #e0a800;
        color: #1a1e24;
        transform: translateY(-2px);
    }
    
    /* Modal Styles */
    .modal-content {
        background: #1a1e24;
        border: 1px solid #ffc107;
        border-radius: 16px;
    }
    
    .modal-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        border-radius: 16px 16px 0 0;
        padding: 1.5rem;
    }
    
    .modal-title {
        color: #ffc107;
        font-weight: 700;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .modal-footer {
        background: #252b33;
        border-top: 1px solid #2f3740;
        border-radius: 0 0 16px 16px;
        padding: 1.5rem;
    }
    
    .close {
        color: #ffc107;
        opacity: 1;
    }
    
    .close:hover {
        color: #fff;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        color: #ffc107;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control {
        background: #252b33;
        border: 1px solid #2f3740;
        border-radius: 8px;
        padding: 10px 15px;
        color: #e0e0e0;
        width: 100%;
    }
    
    .form-control:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
    }
    
    /* DataTables */
    .dataTables_wrapper {
        color: #e0e0e0;
        padding: 15px 0;
    }
    
    .dataTables_filter input {
        border: 1px solid #2f3740;
        border-radius: 20px;
        padding: 8px 15px;
        background: #252b33;
        color: #e0e0e0;
        margin-bottom: 20px;
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
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: #e0e0e0 !important;
        border: 1px solid #2f3740;
        border-radius: 5px;
        padding: 5px 12px;
        margin: 0 3px;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    /* User Info */
    .user-name {
        color: #fff;
        font-weight: 600;
    }
    
    .user-email {
        color: #a0a8b5;
        font-size: 0.85rem;
    }
    
    .user-id {
        color: #ffc107;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .agency-name {
        color: #fff;
        font-weight: 600;
    }
    
    .agency-code {
        color: #ffc107;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    /* Level Badge */
    .level-badge {
        background: #17a2b8;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-header h4 {
            font-size: 1.2rem;
        }
        
        .btn-action {
            padding: 4px 8px;
            font-size: 0.7rem;
        }
    }
</style>
@endpush

@section('content')
<!--Content Start-->
<div class="body-content">
    <div class="main-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0">
                <i class="fas fa-building"></i>
                Agency List
            </h4>
            <span style="color: #ffc107;">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ date('d M Y') }}
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="agencyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>User Info</th>
                            <th>Level</th>
                            <th>Agency</th>
                            <th>Code</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i=0; @endphp
                        @foreach($agencys as $item)
                        @php
                            $user = App\Models\User::find($item->user_id);
                            $country = App\Models\Country::find($item->country_id);
                            
                            // Update user properties
                            if($user) {
                                $user->is_agency = 1;
                                $user->comment_badge = 'Merchant';
                                $user->is_coin_protal_active = 1;
                                $user->save();
                            }
                        @endphp
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>
                                @if($user && $item->logo)
                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($item->logo) }}" class="profile-img" alt="profile">
                                @else
                                    <div class="profile-img" style="background: #2f3740; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="color: #ffc107;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($user)
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-email">{{ $user->email }}</div>
                                    <span class="user-id">#{{ $user->id }}</span>
                                @else
                                    <span class="badge-status badge-inactive">No User</span>
                                @endif
                            </td>
                            <td>
                                @if($user && $user->level)
                                    <span class="level-badge">Level {{ $user->level }}</span>
                                @else
                                    <span class="badge-status badge-pending">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="agency-name">{{ $item->name }}</div>
                            </td>
                            <td>
                                <span class="agency-code">{{ $item->code }}</span>
                            </td>
                            <td>
                                @if($country)
                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($country->flag) }}" class="flag-img" alt="flag">
                                @else
                                    <span class="badge-status badge-pending">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 0)
                                    <span class="badge-status badge-pending">Pending</span>
                                @else
                                    <span class="badge-status badge-active">Active</span>
                                @endif
                            </td>
                            <td>
                                <!-- Edit Button -->
                                <button type="button" class="btn-action btn-edit" data-toggle="modal" data-target="#editModal{{ $item->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Status Toggle -->
                                @if($user)
                                    @if($user->is_agency == 1)
                                        <a href="{{ URL::to('admin-agency-off/'.$user->id) }}" class="btn-action btn-inactive" onclick="return confirm('Deactivate this agency?')">
                                            <i class="fas fa-ban"></i> Inactive
                                        </a>
                                    @else
                                        <a href="{{ URL::to('admin-agency-on/'.$user->id) }}" class="btn-action btn-active" onclick="return confirm('Activate this agency?')">
                                            <i class="fas fa-check"></i> Active
                                        </a>
                                    @endif
                                @endif
                                
                                <!-- Agency Status -->
                                @if($item->status == 0)
                                    <a href="{{ URL::to('admin-agency-active',$item->id) }}" class="btn-action btn-active" onclick="return confirm('Approve this agency?')">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                @else
                                    <a href="{{ URL::to('admin-agency-reject',$item->id) }}" class="btn-action btn-reject" onclick="return confirm('Reject this agency?')">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit Agency
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ URL::to('agency_update',$item->id) }}" enctype="multipart/form-data" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="form-label">Agency Name:</label>
                                                <input type="text" name="name" value="{{ $item->name }}" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Logo:</label>
                                                <input type="file" name="logo" class="form-control">
                                                <input type="hidden" name="old_logo" value="{{ \App\Support\MediaPathHelper::localRelativePath($item->logo, $item->logo) }}">
                                                @if($item->logo)
                                                    <div class="mt-2">
                                                        <img src="{{ \App\Support\MediaPathHelper::publicUrl($item->logo) }}" style="width: 60px; height: 60px; border-radius: 10px; border: 2px solid #ffc107;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn-action btn-reject" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                            <button type="submit" class="btn-action btn-active">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
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

<script>
$(document).ready(function() {
    $('#agencyTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "Search:",
            searchPlaceholder: "Type to search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ agencies",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});
</script>
@endpush
