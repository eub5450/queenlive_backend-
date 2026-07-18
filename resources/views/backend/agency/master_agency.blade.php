@extends('backend.layouts.main')

@section('title')
Master Agency List
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Dark Theme */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    /* Main Card */
    .master-card {
        border: none;
        border-radius: 24px;
        background: #1a1e24;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        margin: 20px 0;
        overflow: hidden;
        border: 1px solid rgba(255, 215, 0, 0.1);
    }
    
    .card-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .card-header h4 {
        color: #ffc107;
        font-weight: 700;
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-header h4 i {
        color: #ffc107;
    }
    
    /* Add Button */
    .btn-add {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        border: none;
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .btn-add:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.5);
        background: linear-gradient(145deg, #ffaa00, #ffc107);
    }
    
    .card-body {
        padding: 2rem;
        background: #1a1e24;
    }
    
    /* Table Styles */
    .table-responsive {
        border-radius: 20px;
        overflow: hidden;
        background: #1a1e24;
    }
    
    .master-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin: 0;
    }
    
    .master-table thead tr {
        background: #252b33;
    }
    
    .master-table thead th {
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        border-bottom: 2px solid #ffc107;
    }
    
    .master-table tbody tr {
        background: #252b33;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .master-table tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        background: #2f3740;
        box-shadow: 0 15px 30px rgba(255, 193, 7, 0.15);
    }
    
    .master-table tbody td {
        padding: 20px;
        border: none;
        vertical-align: middle;
        color: #e0e0e0;
    }
    
    .master-table tbody td:first-child {
        font-weight: 700;
        color: #ffc107;
    }
    
    /* Agency Badge */
    .agency-badge {
        background: #ffc107;
        color: #1a1e24;
        padding: 8px 15px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .count-badge {
        background: #17a2b8;
        color: white;
        padding: 8px 15px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-block;
        min-width: 60px;
        text-align: center;
    }
    
    /* Action Button */
    .btn-view {
        background: linear-gradient(145deg, #17a2b8, #138496);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-view:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3);
        color: white;
        text-decoration: none;
    }
    
    /* Modal Styles */
    .modal-dark .modal-content {
        background: #1a1e24;
        border: 2px solid #ffc107;
        border-radius: 24px;
        box-shadow: 0 30px 60px rgba(255, 193, 7, 0.2);
    }
    
    .modal-dark .modal-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.8rem;
        border-radius: 22px 22px 0 0;
    }
    
    .modal-dark .modal-title {
        color: #ffc107;
        font-weight: 700;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-dark .close {
        color: #ffc107;
        opacity: 1;
        font-size: 2rem;
        text-shadow: none;
    }
    
    .modal-dark .modal-body {
        padding: 2rem;
    }
    
    .modal-dark .modal-footer {
        background: #252b33;
        border-top: 2px solid #ffc107;
        padding: 1.8rem;
        border-radius: 0 0 22px 22px;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.8rem;
    }
    
    .form-label {
        color: #ffc107;
        font-weight: 600;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-select {
        background: #252b33;
        border: 2px solid #2f3740;
        border-radius: 16px;
        padding: 14px 18px;
        color: #fff;
        width: 100%;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .form-select:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }
    
    .form-select option {
        background: #1a1e24;
        color: #e0e0e0;
        padding: 10px;
    }
    
    /* Modal Buttons */
    .btn-modal {
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-modal-close {
        background: #2f3740;
        color: #fff;
    }
    
    .btn-modal-close:hover {
        background: #3a4450;
        transform: translateY(-2px);
    }
    
    .btn-modal-save {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        font-weight: 700;
    }
    
    .btn-modal-save:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 20px rgba(255, 193, 7, 0.4);
    }
    
    /* DataTables */
    .dataTables_wrapper {
        color: #e0e0e0;
        padding: 20px 0;
    }
    
    .dataTables_filter input {
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        background: #252b33;
        color: #fff;
        margin-bottom: 20px;
    }
    
    .dataTables_filter input:focus {
        border-color: #ffc107;
        outline: none;
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: #e0e0e0 !important;
        border: 2px solid #2f3740;
        border-radius: 10px;
        padding: 8px 15px;
        margin: 0 3px;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107 !important;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
        }
        
        .btn-add {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="body-content">
    <div class="master-card">
        <div class="card-header">
            <h4>
                <i class="fas fa-sitemap"></i>
                Master Agency List
            </h4>
            <button type="button" class="btn-add" data-toggle="modal" data-target="#addAgencyModal">
                <i class="fas fa-plus-circle"></i>
                Add New Agency
            </button>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="master-table" id="masterAgencyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Agency Code</th>
                            <th>Agency Name</th>
                            <th>Child Agencies</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($lists as $item)
                        <tr>
                            <td>#{{ ++$i }}</td>
                            <td>
                                <span class="agency-badge">
                                    <i class="fas fa-qrcode mr-1"></i>
                                    {{ $item['master_agency_code'] }}
                                </span>
                            </td>
                            <td style="font-weight: 600; color: #fff;">{{ $item['master_agency'] }}</td>
                            <td>
                                <span class="count-badge">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $item['count'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ URL::to('admin-master-agency-view', $item['id']) }}" class="btn-view">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Agency Modal -->
<div class="modal fade modal-dark" id="addAgencyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i>
                    Add New Child Agency
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ URL::to('admin/child_agency_store') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-building"></i>
                            Master Agency
                        </label>
                        <select name="master_agency_id" class="form-select" required>
                            <option value="">Select Master Agency</option>
                            @foreach($agencys as $agency)
                            <option value="{{ $agency->id }}">
                                {{ $agency->code }} — {{ $agency->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-child"></i>
                            Child Agency
                        </label>
                        <select name="child_agency_id" class="form-select" required>
                            <option value="">Select Child Agency</option>
                            @foreach($agencys as $agency)
                            <option value="{{ $agency->id }}">
                                {{ $agency->code }} — {{ $agency->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-modal btn-modal-save">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
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
    $('#masterAgencyTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "🔍 Search:",
            searchPlaceholder: "Search agencies...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ agencies",
            paginate: {
                first: "⟪",
                last: "⟫",
                next: "→",
                previous: "←"
            }
        }
    });
});
</script>
@endpush