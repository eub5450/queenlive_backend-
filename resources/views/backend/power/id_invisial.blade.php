@extends('backend.layouts.main')

@section('title')
Create New Agency
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        color: #ffc107;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control, .select2-container--default .select2-selection--single {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        color: #e0e0e0;
        height: 46px;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    /* Select2 Customization */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container--default .select2-selection--single {
        height: 46px;
        border: 2px solid #2f3740;
        border-radius: 50px;
        background: #252b33;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        color: #e0e0e0;
        padding-left: 20px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        right: 15px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #ffc107 transparent transparent transparent;
    }
    
    .select2-dropdown {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 15px;
        overflow: hidden;
        margin-top: 5px;
    }
    
    .select2-search--dropdown .select2-search__field {
        background: #1a1e24;
        border: 1px solid #2f3740;
        border-radius: 50px;
        padding: 8px 15px;
        color: #e0e0e0;
    }
    
    .select2-results__option {
        padding: 10px 15px;
        color: #e0e0e0;
    }
    
    .select2-results__option--highlighted[aria-selected] {
        background: #ffc107 !important;
        color: #1a1e24 !important;
    }
    
    /* Button Styles */
    .btn-success {
        background: linear-gradient(145deg, #28a745, #218838);
        border: none;
        border-radius: 50px;
        padding: 12px 35px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        min-width: 150px;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        border-color: #fff;
    }
    
    .btn-danger {
        background: linear-gradient(145deg, #dc3545, #c82333);
        border: none;
        border-radius: 20px;
        padding: 5px 15px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
    }
    
    /* Alert Styles */
    .alert-danger {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .alert-danger ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    /* Title Styles */
    .text-center.font-weight-bold.font-italic {
        color: #ffc107;
        font-size: 1.8rem;
        margin: 0.5rem 0 1.5rem 0;
        text-transform: uppercase;
        letter-spacing: 2px;
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
    
    /* Image Styles */
    .profile-img {
        width: 50px;
        height: 50px;
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
    
    /* DataTables Customization */
    .dataTables_wrapper {
        color: #e0e0e0;
        padding: 15px 0;
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
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .card-header h4 {
            font-size: 1.2rem;
        }
        
        .text-center.font-weight-bold.font-italic {
            font-size: 1.4rem;
        }
        
        .dataTables_filter input {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
        }
        
        .btn-success {
            width: 100%;
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
<!-- Error Messages -->
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!--Content Start-->
<div class="body-content">
    <!-- Form Card -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-eye-slash mr-2"></i>
            <h4 class="mb-0">New Invisible Power</h4>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h4 class="font-weight-bold font-italic" style="color: #ffc107;">Active Invisible Power</h4>
            </div>
            
            <form action="{{URL::to('invisibal_active')}}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user mr-2"></i>User ID</label>
                            <select name="user_id" class="form-control select2" required id="user_id">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->id}} — {{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-id-card mr-2"></i>ID Number</label>
                            <input type="number" name="id_number" class="form-control" placeholder="Enter ID Number For Confirm" value="" required>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle mr-2"></i> Active
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Invisible ID List Card -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-list mr-2"></i>
            <h4 class="mb-0">Invisible ID List</h4>
        </div>
        
            <div class="table-responsive">
                <table class="table table-hover" id="invisibleTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($invisible_users as $invisible_user)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td><span class="badge-id">#{{ $invisible_user->id }}</span></td>
                            <td>
                                <img src="{{URL::to($invisible_user->profile)}}" class="profile-img" alt="Profile">
                            </td>
                            <td>{{ $invisible_user->name }}</td>
                            <td>{{ $invisible_user->level }}</td>
                            <td>
                                <a href="{{URL::to('invisible_id_reject/'.$invisible_user->user_id)}}" class="btn btn-danger">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Sl</th>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
          
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Search user by ID or name...',
        allowClear: true,
        width: '100%'
    });
    
    // Initialize DataTable
    $('#invisibleTable').DataTable({
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
        scrollX: true,
        scrollCollapse: true,
        autoWidth: true
    });
});
</script>
@endpush