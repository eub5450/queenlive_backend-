@extends('backend.layouts.main')

@section('title')
Employee | Fruits Lock Management
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* ===== CLEAN, PROFESSIONAL DARK STYLES - NO SHAKING ===== */
    
    /* Body Content */
    .body-content {
        padding: 1.5rem;
        background-color: #1a1e24;
        min-height: 100vh;
    }
    
    /* Cards */
    .card {
        border: 1px solid #404854;
        border-radius: 12px;
        background: #2d333b;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background: #363c47;
        border-bottom: 1px solid #404854;
        padding: 1rem 1.5rem;
    }
    
    .card-header h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Tabs */
    .nav-tabs {
        border-bottom: 2px solid #404854;
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .nav-tabs .nav-link {
        color: #e0e0e0;
        background: #2d333b;
        border: 1px solid #404854;
        border-bottom: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 8px 8px 0 0;
        margin-right: 0.25rem;
        transition: none;
        cursor: pointer;
    }
    
    .nav-tabs .nav-link:hover {
        background: #363c47;
        border-color: #404854;
        color: #fff;
    }
    
    .nav-tabs .nav-link.active {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }
    
    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
    }
    
    /* Tab Content */
    .tab-content {
        background: transparent;
        padding: 0;
    }
    
    .tab-pane {
        display: none;
    }
    
    .tab-pane.active {
        display: block;
    }
    
    /* Tables */
    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #404854;
    }
    
    .table {
        width: 100%;
        color: #e0e0e0;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    
    .table thead th {
        background: #363c47;
        color: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #28a745;
        padding: 1rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #404854;
    }
    
    .table tbody tr:hover {
        background: rgba(40, 167, 69, 0.1);
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    /* Clickable ID Styles */
    .clickable-id {
        color: #ffc107;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .clickable-id:hover {
        color: #ffca2c;
        text-decoration: underline;
    }
    
    .clickable-id.success {
        color: #28a745;
    }
    
    .clickable-id.success:hover {
        color: #34ce57;
    }
    
    .clickable-id.danger {
        color: #dc3545;
    }
    
    .clickable-id.danger:hover {
        color: #e04b59;
    }
    
    /* Lock Row Colors */
    .lock-row {
        background-color: rgba(220, 53, 69, 0.15);
    }
    
    .win-row {
        background-color: rgb(12 210 57 / 39%);
    }
    
    /* Badges */
    .badge {
        padding: 0.4rem 0.75rem;
        border-radius: 30px;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-block;
    }
    
    .badge.bg-warning {
        background: #ffc107;
        color: #000;
    }
    
    .badge.bg-success {
        background: #28a745;
        color: #fff;
    }
    
    .badge.bg-danger {
        background: #dc3545;
        color: #fff;
    }
    
    .badge.bg-info {
        background: #17a2b8;
        color: #fff;
    }
    
    .badge.bg-secondary {
        background: #6c757d;
        color: #fff;
    }
    
    /* Buttons */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        transition: none;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #000;
    }
    
    .btn-warning:hover {
        background: #e0a800;
    }
    
    .btn-info {
        background: #17a2b8;
        color: #fff;
    }
    
    .btn-info:hover {
        background: #138496;
    }
    
    .btn-success {
        background: #28a745;
        color: #fff;
    }
    
    .btn-success:hover {
        background: #218838;
    }
    
    .btn-outline-danger {
        background: transparent;
        border: 1px solid #dc3545;
        color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background: #dc3545;
        color: #fff;
    }
    
    .btn-outline-success {
        background: transparent;
        border: 1px solid #28a745;
        color: #28a745;
    }
    
    .btn-outline-success:hover {
        background: #28a745;
        color: #fff;
    }
    
    .btn-outline-info {
        background: transparent;
        border: 1px solid #17a2b8;
        color: #17a2b8;
    }
    
    .btn-outline-info:hover {
        background: #17a2b8;
        color: #fff;
    }
    
    .btn-outline-secondary {
        background: transparent;
        border: 1px solid #404854;
        color: #e0e0e0;
    }
    
    .btn-outline-secondary:hover {
        background: #363c47;
        color: #fff;
    }
    
    /* Modal - Fixed */
    .modal {
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }
    
    .modal-content {
        border-radius: 12px;
        border: 1px solid #404854;
        background: #2d333b;
    }
    
    .modal-header {
        background: #363c47;
        border-bottom: 1px solid #404854;
        padding: 1.2rem 1.5rem;
        border-radius: 12px 12px 0 0;
    }
    
    .modal-header h5 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .modal-header .close {
        color: #fff;
        opacity: 0.8;
        font-size: 1.5rem;
        background: transparent;
        border: none;
        cursor: pointer;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        border-top: 1px solid #404854;
        padding: 1.2rem 1.5rem;
    }
    
    /* Form Controls */
    .form-group {
        margin-bottom: 1.2rem;
    }
    
    .form-group label {
        color: #e0e0e0;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .form-group label i {
        margin-right: 8px;
        color: #ffc107;
        width: 18px;
    }
    
    .form-control {
        background: #242931;
        border: 1px solid #404854;
        border-radius: 6px;
        color: #e0e0e0;
        padding: 0.6rem 1rem;
        width: 100%;
        font-size: 0.95rem;
    }
    
    .form-control:focus {
        border-color: #28a745;
        outline: none;
    }
    
    select.form-control {
        cursor: pointer;
    }
    
    /* Header Count */
    .header-count {
        background: #1a1e24;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-size: 0.85rem;
        margin-left: 0.5rem;
        color: #e0e0e0;
    }
    
    /* Serial Number */
    .serial-number {
        width: 60px;
        text-align: center;
        font-weight: 600;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    /* Text Colors */
    .text-warning { color: #ffc107; }
    .text-success { color: #28a745; }
    .text-danger { color: #dc3545; }
    .text-info { color: #17a2b8; }
    .text-white { color: #fff; }
    .text-white-50 { color: rgba(255, 255, 255, 0.7); }
    
    /* Utilities */
    .d-flex { display: flex; }
    .align-items-center { align-items: center; }
    .justify-content-between { justify-content: space-between; }
    .justify-content-end { justify-content: flex-end; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 1rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .mt-3 { margin-top: 1rem; }
    .me-2 { margin-right: 0.5rem; }
    .ms-1 { margin-left: 0.25rem; }
    .ms-2 { margin-left: 0.5rem; }
    .ms-auto { margin-left: auto; }
    .p-0 { padding: 0; }
    .pl-0 { padding-left: 0; }
    .pr-0 { padding-right: 0; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: 700; }
    .fw-semibold { font-weight: 600; }
    
    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #1a1e24;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .loader {
        width: 50px;
        height: 50px;
        border: 3px solid #404854;
        border-top-color: #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
@if(Auth::id() == 22222 || Auth::id() == 1)
<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loader"></div>
</div>

<!-- Content Start -->
<div class="body-content">
    <!-- Header Card with Two Buttons -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-apple-alt text-warning me-2"></i>
                    Fruits Lock Management
                </h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#addLockModal">
                        <i class="fas fa-plus-circle"></i> Add Lock
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#lockOffModal">
                        <i class="fas fa-lock-open"></i> Lock Off
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Lock Modal -->
    <div class="modal fade" id="addLockModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>
                        <i class="fas fa-lock me-2"></i>
                        Add New Lock
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('admin/fruts-game-lock_id_list-store')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-id-card"></i> User ID
                            </label>
                            <input type="number" name="block_id" class="form-control" required placeholder="Enter user ID">
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-percent"></i> Percentage
                            </label>
                            <input type="number" step="0.01" name="parcentage" class="form-control" required placeholder="Enter percentage">
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-tag"></i> Type
                            </label>
                            <select name="type" class="form-control" required>
                                <option value="0">🔒 Lock</option>
                                <option value="1">🏆 Win</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Lock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lock Off Modal -->
    <div class="modal fade" id="lockOffModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>
                        <i class="fas fa-lock-open me-2"></i>
                        Lock Off User
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ URL::to('admin/fruts-game-lock_off-store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-id-card"></i> User ID
                            </label>
                            <input type="number" name="block_id" class="form-control" placeholder="Enter User ID to Unlock" required>
                        </div>
                        
                        <div class="alert alert-info" style="background: rgba(23, 162, 184, 0.1); border: 1px solid #17a2b8; color: #fff; border-radius: 6px; padding: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-info-circle me-2"></i>
                            This will remove all lock restrictions from the user.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-lock-open"></i> Unlock User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="nav-tabs">
         <button class="nav-link active" data-tab="win-tab">
            <i class="fas fa-trophy"></i> Win List 
            <span class="header-count">{{ count($fruits_win_lists) }}</span>
        </button>
        <button class="nav-link " data-tab="lock-tab">
            <i class="fas fa-lock"></i> Lock List 
            <span class="header-count">{{ count($fruits_lock_lists) }}</span>
        </button>
       
        <button class="nav-link" data-tab="lockoff-tab">
            <i class="fas fa-ban"></i> Lock Off List 
            <span class="header-count">{{ isset($lock_off_ids) ? count($lock_off_ids) : 0 }}</span>
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content">
        <!-- Lock List Tab -->
        <div class="tab-pane" id="lock-tab">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sl.</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Other IDs</th>
                                    <th>Auto Lock?</th>
                                    <th>Percentage</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=0; @endphp
                                @forelse($fruits_lock_lists as $row)
                                    @php
                                        $user = \App\RedisCache\RedisCache::UserfindById($row->user_id);
                                        $old_ids = $user ? App\Models\User::where('imei_number', $user->imei_number ?? '')
                                            ->where('id', '!=', $row->user_id)
                                            ->get() : collect();
                                    @endphp
                                    <tr class="{{ $row->auto_lock_active ? 'lock-row' : '' }}">
                                        <td class="serial-number">{{ ++$i }}</td>
                                        <td>
                                            <a href="{{ URL::to('/') }}/id_search?id={{ $row->user_id }}" class="clickable-id">
                                                {{ $row->user_id }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($user)
                                                <span class="text-white">{{ $user->name }}</span>
                                            @else
                                                <span class="text-white-50">User not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($old_ids->count() > 0)
                                                @foreach($old_ids as $old_id)
                                                    <a href="{{ URL::to('/') }}/id_search?id={{ $old_id->id }}" class="badge bg-secondary mb-1 text-decoration-none">
                                                        {{ $old_id->name }} - {{ $old_id->id }}
                                                    </a>
                                                @endforeach
                                            @else
                                                <span class="text-white-50">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->auto_lock_active)
                                                <span class="badge bg-warning">🔒 Auto Lock</span>
                                            @else
                                                <span class="badge bg-secondary">Manual</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-warning">{{ $row->parcentage }}%</span></td>
                                        <td><span class="text-info fw-semibold">{{ $user->balance ?? 'N/A' }}</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ URL::to('admin/fruts-game-lock_id-list-delete/'.$row->id) }}" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-white-50 py-4">No lock records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Win List Tab -->
        <div class="tab-pane active" id="win-tab">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sl.</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Other IDs</th>
                                    <th>Auto Win?</th>
                                    <th>Percentage</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=0; @endphp
                                @forelse($fruits_win_lists as $row)
                                    @php
                                        $user = \App\RedisCache\RedisCache::UserfindById($row->user_id);
                                        $old_ids = $user ? App\Models\User::where('imei_number', $user->imei_number ?? '')
                                            ->where('id', '!=', $row->user_id)
                                            ->get() : collect();
                                    @endphp
                                    <tr class="{{ $row->auto_lock_active ? 'win-row' : '' }}">
                                        <td class="serial-number">{{ ++$i }}</td>
                                        <td>
                                            <a href="{{ URL::to('/') }}/id_search?id={{ $row->user_id }}" class="clickable-id success">
                                                {{ $row->user_id }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($user)
                                                <span class="text-white">{{ $user->name }}</span>
                                            @else
                                                <span class="text-white-50">User not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($old_ids->count() > 0)
                                                @foreach($old_ids as $old_id)
                                                    <a href="{{ URL::to('/') }}/id_search?id={{ $old_id->id }}" class="badge bg-secondary mb-1 text-decoration-none">
                                                        {{ $old_id->name }} - {{ $old_id->id }}
                                                    </a>
                                                @endforeach
                                            @else
                                                <span class="text-white-50">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->auto_lock_active)
                                                <span class="badge bg-success">🎯 Auto Win</span>
                                            @else
                                                <span class="badge bg-secondary">Manual</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-success">{{ $row->parcentage }}%</span></td>
                                        <td><span class="text-info fw-semibold">{{ $user->balance ?? 'N/A' }}</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ URL::to('admin/fruts-game-lock_id-list-delete/'.$row->id) }}" 
                                                   class="btn btn-sm btn-outline-success"
                                                   onclick="return confirm('Are you sure you want to delete this?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-white-50 py-4">No win records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lock Off List Tab -->
        <div class="tab-pane" id="lockoff-tab">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sl.</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=0; @endphp
                                @forelse($lock_off_ids ?? [] as $lockoff)
                                    @php
                                        $lockoffUser = \App\RedisCache\RedisCache::UserfindById($lockoff->user_id ?? $lockoff->id);
                                    @endphp
                                    <tr>
                                        <td class="serial-number">{{ ++$i }}</td>
                                        <td>
                                            <a href="{{ URL::to('/') }}/id_search?id={{ $lockoff->user_id ?? $lockoff->id }}" class="clickable-id danger">
                                                {{ $lockoff->user_id ?? $lockoff->id }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($lockoffUser)
                                                <span class="text-white">{{ $lockoffUser->name }}</span>
                                            @else
                                                <span class="text-white-50">{{ $lockoff->name ?? 'User not found' }}</span>
                                            @endif
                                        </td>
                                        <td><span class="text-info fw-semibold">{{ $lockoffUser->balance ?? $lockoff->balance ?? 'N/A' }}</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ URL::to('admin/fruts-game-lock_id-off-delete/'.($lockoff->id ?? $lockoff->user_id)) }}" 
                                                   class="btn btn-sm btn-outline-info"
                                                   onclick="return confirm('Are you sure you want to remove this lock off?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-white-50 py-4">No lock off records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple Tab Switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-link');
    const panes = document.querySelectorAll('.tab-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and panes
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding pane
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});

// Hide loading overlay
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }, 500);
});
</script>
@endif
@endsection