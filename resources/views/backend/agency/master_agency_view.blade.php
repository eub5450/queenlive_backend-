@extends('backend.layouts.main')

@section('title')
Master Agency Details
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
    
    /* Profile Card */
    .profile-card {
        border: none;
        border-radius: 30px;
        background: #1a1e24;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        border: 1px solid rgba(255, 215, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .profile-header {
        background: linear-gradient(145deg, #252b33, #1a1e24);
        padding: 40px 30px;
        text-align: center;
        border-bottom: 2px solid #ffc107;
        position: relative;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 0%, rgba(255, 193, 7, 0.1), transparent 70%);
    }
    
    .profile-image-wrapper {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        position: relative;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffc107;
        box-shadow: 0 10px 30px rgba(255, 193, 7, 0.3);
        position: relative;
        z-index: 2;
        background: #1a1e24;
    }
    
    .profile-glow {
        position: absolute;
        top: -10px;
        left: -10px;
        right: -10px;
        bottom: -10px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 193, 7, 0.3), transparent 70%);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    
    .profile-name {
        color: #ffc107;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 5px;
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
    }
    
    .profile-code {
        display: inline-block;
        background: #ffc107;
        color: #1a1e24;
        padding: 8px 25px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        margin-top: 10px;
    }
    
    .profile-body {
        padding: 30px;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-item {
        background: #252b33;
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        border: 1px solid #2f3740;
        transition: all 0.3s ease;
    }
    
    .stat-item:hover {
        transform: translateY(-5px);
        border-color: #ffc107;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.1);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        background: #1a1e24;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border: 2px solid #ffc107;
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
        color: #ffc107;
        font-size: 2rem;
        font-weight: 800;
    }
    
    /* Table Card */
    .table-card {
        border: none;
        border-radius: 24px;
        background: #1a1e24;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        border: 1px solid rgba(255, 215, 0, 0.1);
    }
    
    .table-header {
        background: #252b33;
        border-bottom: 2px solid #ffc107;
        padding: 1.5rem 2rem;
    }
    
    .table-header h6 {
        color: #ffc107;
        font-weight: 700;
        margin: 0;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* Table Styles */
    .gift-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin: 0;
    }
    
    .gift-table thead tr {
        background: #252b33;
    }
    
    .gift-table thead th {
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        font-size: 0.9rem;
        text-transform: uppercase;
        border: none;
        border-bottom: 2px solid #ffc107;
    }
    
    .gift-table tbody tr {
        background: #252b33;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    
    .gift-table tbody tr:hover {
        background: #2f3740;
        transform: translateY(-2px);
    }
    
    .gift-table tbody td {
        padding: 20px;
        border: none;
        color: #e0e0e0;
    }
    
    .gift-table tbody td:first-child {
        font-weight: 700;
        color: #ffc107;
    }
    
    .gift-table tfoot th {
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        border-top: 2px solid #ffc107;
    }
    
    /* Remove Button */
    .btn-remove {
        background: linear-gradient(145deg, #dc3545, #c82333);
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
    
    .btn-remove:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
        color: white;
        text-decoration: none;
    }
    
    /* Total Badge */
    .total-badge {
        background: #ffc107;
        color: #1a1e24;
        padding: 5px 15px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    /* Back Button */
    .back-button {
        position: absolute;
        top: 30px;
        left: 30px;
        background: #252b33;
        color: #ffc107;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        border: 2px solid #ffc107;
    }
    
    .back-button:hover {
        background: #ffc107;
        color: #1a1e24;
        transform: translateX(-5px);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-name {
            font-size: 1.5rem;
        }
        
        .back-button {
            top: 15px;
            left: 15px;
            width: 35px;
            height: 35px;
        }
    }
</style>
@endpush

@section('content')
@php
    // Calculate total if not already calculated in controller
    $reciving_history_total = 0;
    foreach($lists as $item) {
        $reciving_history_total += $item['total_target'];
    }
@endphp

<div class="body-content">
    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <a href="{{ URL::previous() }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="profile-image-wrapper">
                <div class="profile-glow"></div>
                <img src="{{ URL::to($master_agency->logo) }}" class="profile-image" alt="Agency Logo">
            </div>
            <h1 class="profile-name">{{ $master_agency->name }}</h1>
            <span class="profile-code">
                <i class="fas fa-qrcode mr-2"></i>
                {{ $master_agency->code }}
            </span>
        </div>
        
        <div class="profile-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-label">Member Since</div>
                    <div class="stat-value">{{ date('Y', strtotime($master_agency->created_at)) }}</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-label">Child Agencies</div>
                    <div class="stat-value">{{ count($lists) }}</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="stat-label">Total Gifts</div>
                    <div class="stat-value">{{ number_format($reciving_history_total) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gift History Table -->
    <div class="table-card">
        <div class="table-header">
            <h6>
                <i class="fas fa-history"></i>
                Gift Received History
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="gift-table" id="giftTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Agency Name</th>
                            <th>Agency Code</th>
                            <th>Gift Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($lists as $item)
                        <tr>
                            <td>#{{ ++$i }}</td>
                            <td style="font-weight: 600;">{{ $item['agency'] }}</td>
                            <td>
                                <span style="background: #2f3740; padding: 5px 10px; border-radius: 8px;">
                                    {{ $item['agency_code'] }}
                                </span>
                            </td>
                            <td style="color: #ffc107; font-weight: 700;">
                                {{ number_format($item['total_target']) }}
                            </td>
                            <td>
                                <a href="{{ URL::to('/remove_as_child_agency', $item['id']) }}" 
                                   class="btn-remove"
                                   onclick="return confirm('Are you sure you want to remove this child agency?')">
                                    <i class="fas fa-trash-alt"></i>
                                    Remove
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align: right;">Total:</th>
                            <th>
                                <span class="total-badge">
                                    {{ number_format($reciving_history_total) }}
                                </span>
                            </th>
                            <th></th>
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#giftTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "🔍 Search:",
            searchPlaceholder: "Search gifts...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
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