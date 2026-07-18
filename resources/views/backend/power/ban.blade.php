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
        padding: 20px;
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
    
    .form-control {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        color: #e0e0e0;
        height: 46px;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .form-control:focus {
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
        z-index: 9999;
    }
    
    .select2-search--dropdown .select2-search__field {
        background: #1a1e24;
        border: 1px solid #2f3740;
        border-radius: 50px;
        padding: 8px 15px;
        color: #e0e0e0;
        width: 100%;
    }
    
    .select2-results__option {
        padding: 10px 15px;
        color: #e0e0e0;
    }
    
    .select2-results__option--highlighted[aria-selected] {
        background: #ffc107 !important;
        color: #1a1e24 !important;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #2f3740;
    }
    
    /* User search result formatting */
    .user-search-result {
        padding: 5px 0;
    }
    
    .user-search-result small {
        color: #aaa;
        font-size: 0.85em;
    }
    
    /* Custom File Input */
    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        height: 46px;
    }
    
    .custom-file-input {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 46px;
        margin: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .custom-file-label {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1;
        height: 46px;
        padding: 10px 20px;
        font-weight: 400;
        line-height: 26px;
        color: #e0e0e0;
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 50px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .custom-file-label::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 3;
        display: block;
        height: 42px;
        padding: 10px 20px;
        line-height: 26px;
        color: #1a1e24;
        content: "Browse";
        background: #ffc107;
        border-left: inherit;
        border-radius: 0 50px 50px 0;
        font-weight: 600;
    }
    
    .custom-file-input:focus ~ .custom-file-label {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    /* Button Styles */
    .btn-primary {
        background: linear-gradient(145deg, #ffc107, #ffb300);
        border: none;
        border-radius: 50px;
        padding: 12px 35px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1a1e24;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 193, 7, 0.4);
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
    
    .alert-info {
        background: #17a2b8;
        color: white;
        border: none;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
    }
    
    .alert-warning {
        background: #ffc107;
        color: #1a1e24;
        border: none;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
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
    
    /* Badge Styles */
    .badge-id {
        background: #2f3740;
        color: #ffc107;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    /* Image Styles */
    .profile-img, .proof-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffc107;
        transition: all 0.3s ease;
    }
    
    .profile-img:hover, .proof-img:hover {
        transform: scale(1.1);
        border-color: #fff;
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
        color: #e0e0e0;
        font-weight: 500;
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
    
    .dataTables_info {
        color: #aaa !important;
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
        display: inline-block;
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
    
    .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
        
        .dataTables_filter input {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
        }
        
        .dataTables_filter label {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="body-content">
    
    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Success!</strong> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="fas fa-ban mr-2"></i>
                    <h4 class="mb-0">New ID Ban</h4>
                </div>
                <div class="card-body">
                    <form action="{{ URL::to('banned_store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group">
                            <label><i class="fas fa-user mr-2"></i>Select User</label>
                            <select name="user_id" class="form-control select2-ban_user" id="ban_user_id" required>
                                <option value="">Search for a user...</option>
                            </select>
                            <small class="form-text text-muted">Type at least 2 characters to search</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-id-card mr-2"></i>Confirm ID Number</label>
                            <input type="number" name="id_number" class="form-control" placeholder="Enter ID number for confirmation" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag mr-2"></i>Ban Type</label>
                            <select name="ban_type" class="form-control" required>
                                <option value="">Select ban type...</option>
                                <option value="A">Ban Type (A) - Permanent ban</option>
                                <option value="B">Ban Type (B) - Behavioral issues</option>
                                <option value="C">Ban Type (C) - Content violation</option>
                                <option value="D">Ban Type (D) - Temporary restriction</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-file mr-2"></i>Proof Document</label>
                            <div class="custom-file">
                                <input type="file" name="proved" class="custom-file-input" id="proved" required accept="image/*,.pdf">
                                <label class="custom-file-label" for="proved">Choose file...</label>
                            </div>
                            <small class="form-text text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Without proof you can't ban</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-ban mr-2"></i> Ban User
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle mr-2"></i>
                    <h4 class="mb-0">Ban Guidelines</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-tags mr-2"></i>Ban Types Explained</h5>
                        <ul class="mb-0">
                            <li><strong>Type A</strong>: Permanent ban</li>
                            <li><strong>Type B</strong>: Behavioral issues</li>
                            <li><strong>Type C</strong>: Content violation</li>
                            <li><strong>Type D</strong>: Temporary restriction</li>
                        </ul>
                    </div>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Requirements</h5>
                        <p class="mb-0">You must provide valid proof document for any ban action. Unjustified bans may be reviewed by administrators.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Banned Users Table -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-eye-slash mr-2"></i>
            <h4 class="mb-0">Banned ID List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="banTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Type</th>
                            <th>Proof</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @forelse($ban_ids as $ban_id)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td><span class="badge-id">#{{ $ban_id->id }}</span></td>
                            <td>
                                @if($ban_id->profile)
                                    <img src="{{ URL::to($ban_id->profile) }}" class="profile-img" alt="Profile">
                                @else
                                    <div class="profile-img" style="background: #2f3740; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="color: #ffc107;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $ban_id->name ?? 'N/A' }}</td>
                            <td>{{ $ban_id->level ?? 'N/A' }}</td>
                            <td>
                                @if($ban_id->ban_type == 'A')
                                    <span class="badge" style="background: #dc3545; color: white; padding: 5px 12px; border-radius: 20px;">Type A</span>
                                @elseif($ban_id->ban_type == 'B')
                                    <span class="badge" style="background: #ffc107; color: #1a1e24; padding: 5px 12px; border-radius: 20px;">Type B</span>
                                @elseif($ban_id->ban_type == 'C')
                                    <span class="badge" style="background: #17a2b8; color: white; padding: 5px 12px; border-radius: 20px;">Type C</span>
                                @elseif($ban_id->ban_type == 'D')
                                    <span class="badge" style="background: #6c757d; color: white; padding: 5px 12px; border-radius: 20px;">Type D</span>
                                @else
                                    <span class="badge" style="background: #6c757d; color: white; padding: 5px 12px; border-radius: 20px;">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($ban_id->ban_proved)
                                    <img src="{{ URL::to($ban_id->ban_proved) }}" class="proof-img" alt="Proof">
                                @else
                                    <span class="text-muted">No proof</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ URL::to('ban_id_reject/'.$ban_id->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this ban?')">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No banned users found</td>
                        </tr>
                        @endforelse
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
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 with AJAX search
    $('.select2-ban_user').select2({
        placeholder: 'Search for a user by ID, name, email or phone...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("users.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(user) {
                        return {
                            id: user.id,
                            text: user.id + ' - ' + user.name + ' (' + user.email + ')',
                            name: user.name,
                            email: user.email,
                            id_number: user.id_number
                        };
                    })
                };
            },
            cache: true
        },
        templateResult: formatUserResult,
        templateSelection: formatUserSelection,
        dropdownParent: $('body'),
        language: {
            inputTooShort: function() {
                return 'Please enter 2 or more characters';
            },
            searching: function() {
                return 'Searching...';
            },
            noResults: function() {
                return 'No users found';
            }
        }
    });
    
    // Custom formatting for dropdown results
    function formatUserResult(user) {
        if (user.loading) {
            return $('<span><i class="fas fa-spinner fa-spin"></i> Loading...</span>');
        }
        
        var $container = $(
            '<div class="user-search-result">' +
            '<div><strong>' + (user.name || 'N/A') + '</strong></div>' +
            '<small>ID: ' + user.id + ' | Email: ' + (user.email || 'N/A') + '</small>' +
            '</div>'
        );
        
        return $container;
    }
    
    function formatUserSelection(user) {
        if (user.id) {
            return user.text || 'User #' + user.id;
        }
        return 'Search for a user...';
    }
    
    // Update file input label
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Initialize DataTable
    if ($('#banTable tbody tr').length > 1 || !$('#banTable tbody tr td.text-center').length) {
        var table = $('#banTable').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "Search table:",
                searchPlaceholder: "Type to filter banned users...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: "No banned users found"
            },
            order: [[0, 'desc']],
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,
            columnDefs: [
                { orderable: false, targets: [2, 6, 7] },
                { searchable: false, targets: [2, 6, 7] }
            ]
        });
    }
    
    // Auto-fill ID number when user is selected (optional)
    $('#ban_user_id').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id_number) {
            $('input[name="id_number"]').val(data.id_number);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var userId = $('#ban_user_id').val();
        var idNumber = $('input[name="id_number"]').val();
        var banType = $('select[name="ban_type"]').val();
        var proof = $('#proved').val();
        
        if (!userId) {
            e.preventDefault();
            alert('Please select a user');
            return false;
        }
        
        if (!idNumber) {
            e.preventDefault();
            alert('Please enter ID number');
            return false;
        }
        
        if (!banType) {
            e.preventDefault();
            alert('Please select ban type');
            return false;
        }
        
        if (!proof) {
            e.preventDefault();
            alert('Please select proof document');
            return false;
        }
    });
});
</script>
@endpush