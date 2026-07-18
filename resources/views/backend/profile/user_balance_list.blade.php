@extends('backend.layouts.main')

@section('title')
User Balance List
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
    .balance-card {
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
    
    /* Search Section */
    .search-section {
        background: #252b33;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #2f3740;
    }
    
    .search-title {
        color: #ffc107;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .search-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .search-input-group {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ffc107;
        z-index: 1;
    }
    
    .search-input {
        width: 100%;
        background: #1a1e24;
        border: 2px solid #2f3740;
        border-radius: 12px;
        padding: 14px 20px 14px 45px;
        color: #fff;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }
    
    .search-input::placeholder {
        color: #6c757d;
    }
    
    .search-button {
        background: linear-gradient(145deg, #ffc107, #ffaa00);
        color: #1a1e24;
        border: none;
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .search-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.5);
    }
    
    .reset-button {
        background: #2f3740;
        color: #fff;
        border: none;
        padding: 14px 25px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .reset-button:hover {
        background: #3a4450;
        transform: translateY(-2px);
    }
    
    /* Summary Stats */
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: #252b33;
        border-radius: 20px;
        padding: 25px;
        border: 1px solid #2f3740;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: #ffc107;
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.1);
    }
    
    .stat-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: #1a1e24;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
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
        letter-spacing: 0.5px;
    }
    
    .stat-value {
        color: #ffc107;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }
    
    .stat-value small {
        font-size: 0.9rem;
        color: #6c757d;
        margin-left: 5px;
    }
    
    /* Table Styles */
    .table-responsive {
        border-radius: 20px;
        overflow: hidden;
        background: #1a1e24;
    }
    
    .balance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin: 0;
    }
    
    .balance-table thead tr {
        background: #252b33;
    }
    
    .balance-table thead th {
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        border-bottom: 2px solid #ffc107;
    }
    
    .balance-table tbody tr {
        background: #252b33;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .balance-table tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        background: #2f3740;
        box-shadow: 0 15px 30px rgba(255, 193, 7, 0.15);
    }
    
    .balance-table tbody td {
        padding: 20px;
        border: none;
        vertical-align: middle;
        color: #e0e0e0;
    }
    
    .balance-table tbody td:first-child {
        font-weight: 700;
        color: #ffc107;
    }
    
    /* Profile Image */
    .profile-wrapper {
        position: relative;
        width: 60px;
        height: 60px;
    }
    
    .profile-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ffc107;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
        transition: all 0.3s ease;
    }
    
    .profile-img:hover {
        transform: scale(1.1) rotate(5deg);
        border-color: #fff;
    }
    
    /* User Info */
    .user-name {
        font-weight: 600;
        color: #fff;
        margin-bottom: 3px;
    }
    
    .user-email {
        font-size: 0.85rem;
        color: #a0a8b5;
    }
    
    .user-id-badge {
        display: inline-block;
        background: #ffc107;
        color: #1a1e24;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: 3px;
    }
    
    /* Level Badge */
    .level-badge {
        background: linear-gradient(145deg, #17a2b8, #138496);
        color: white;
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-block;
        box-shadow: 0 3px 10px rgba(23, 162, 184, 0.3);
    }
    
    /* Balance Amount */
    .balance-amount {
        font-size: 1.2rem;
        font-weight: 800;
        color: #ffc107;
        background: #1a1e24;
        padding: 10px 20px;
        border-radius: 50px;
        display: inline-block;
        border: 2px solid #ffc107;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
    }
    
    /* Table Footer */
    .balance-table tfoot th {
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 18px 20px;
        border-top: 2px solid #ffc107;
        font-size: 1.1rem;
    }
    
    .total-badge {
        background: #ffc107;
        color: #1a1e24;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 1.2rem;
        font-weight: 800;
        display: inline-block;
    }
    
    /* No Results */
    .no-results {
        text-align: center;
        padding: 60px 20px;
        background: #252b33;
        border-radius: 20px;
    }
    
    .no-results i {
        font-size: 50px;
        color: #ffc107;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .no-results h5 {
        color: #fff;
        margin-bottom: 10px;
    }
    
    .no-results p {
        color: #a0a8b5;
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
        transition: all 0.3s;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107 !important;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #ffc107;
        color: #1a1e24 !important;
        border-color: #ffc107;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
        }
        
        .search-form {
            flex-direction: column;
        }
        
        .summary-stats {
            grid-template-columns: 1fr;
        }
        
        .balance-amount {
            font-size: 1rem;
            padding: 8px 15px;
        }
    }
    
    /* Loading State */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        border: 2px solid #ffc107;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1e24;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #2f3740;
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
</style>
@endpush

@section('content')
<!--Content Start-->
<div class="body-content">
    @php
    // Calculate totals
    $total_users = count($users);
    $total_balance = 0;
    $average_balance = 0;
    $highest_balance = 0;
    $highest_user = '';
    
    foreach($users as $user) {
        $total_balance += $user->balance;
        if($user->balance > $highest_balance) {
            $highest_balance = $user->balance;
            $highest_user = $user->name;
        }
    }
    
    $average_balance = $total_users > 0 ? round($total_balance / $total_users) : 0;
    @endphp
    
    <div class="balance-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4>
                <i class="fas fa-wallet"></i>
                User Balance List
            </h4>
            <span style="color: #ffc107;">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ date('d M Y') }}
            </span>
        </div>
        
        <div class="card-body">
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-title">
                    <i class="fas fa-search"></i>
                    Search User by ID
                </div>
                <form action="{{ url('id_search') }}" method="GET" class="search-form" id="searchForm">
                    <input type="hidden" name="_token" value="YVf4oJmhURAkdqOfoLyHR8Vo93waxfeATevjkkrH">
                    <div class="search-input-group">
                        <i class="fas fa-id-card search-icon"></i>
                        <input type="text" 
                               name="id" 
                               class="search-input" 
                               placeholder="Enter User ID (e.g., 81108)" 
                               value="{{ request()->get('id') }}"
                               required>
                    </div>
                    <button type="submit" class="search-button" id="searchBtn">
                        <i class="fas fa-search"></i>
                        Search User
                    </button>
                    <a href="{{ url()->current() }}" class="reset-button">
                        <i class="fas fa-undo-alt"></i>
                        Reset
                    </a>
                </form>
                @if(request()->has('id'))
                    <div style="margin-top: 15px; padding: 10px; background: #1a1e24; border-radius: 10px; border-left: 4px solid #ffc107;">
                        <i class="fas fa-info-circle" style="color: #ffc107;"></i>
                        <span style="color: #fff; margin-left: 8px;">Showing results for User ID: <strong style="color: #ffc107;">{{ request()->get('id') }}</strong></span>
                    </div>
                @endif
            </div>
            
            <!-- Summary Stats -->
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value">{{ number_format($total_users) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Balance</div>
                            <div class="stat-value">{{ number_format($total_balance) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="stat-label">Average Balance</div>
                            <div class="stat-value">{{ number_format($average_balance) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <div class="stat-label">Highest Balance</div>
                            <div class="stat-value">{{ number_format($highest_balance) }}</div>
                            <small style="color: #a0a8b5;">{{ $highest_user }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            @if(count($users) > 0)
            <div class="table-responsive">
                <table class="balance-table" id="userTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>User Details</th>
                            <th>Level</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($users as $user)
                        <tr>
                            <td>#{{ ++$i }}</td>
                            <td>
                                <div class="profile-wrapper">
                                    @if($user && $user->profile)
                                        <img src="{{ URL::to($user->profile) }}" class="profile-img" alt="{{ $user->name }}">
                                    @else
                                        <div class="profile-img" style="background: #2f3740; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user" style="color: #ffc107; font-size: 24px;"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="user-name">{{ $user->name ?? 'N/A' }}</div>
                                <div class="user-email">
                                    <i class="fas fa-envelope" style="color: #ffc107; font-size: 12px; margin-right: 5px;"></i>
                                    {{ $user->email ?? 'N/A' }}
                                </div>
                                <span class="user-id-badge">
                                    <i class="fas fa-id-card"></i>
                                    ID: {{ $user->id ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($user && $user->level)
                                    <span class="level-badge">
                                        <i class="fas fa-star mr-1"></i>
                                        Level {{ $user->level }}
                                    </span>
                                @else
                                    <span class="level-badge" style="background: #6c757d;">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="balance-amount">
                                    <i class="fas fa-coins mr-1"></i>
                                    {{ number_format($user->balance) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align: right;">Total Balance:</th>
                            <th>
                                <span class="total-badge">
                                    <i class="fas fa-coins mr-1"></i>
                                    {{ number_format($total_balance) }}
                                </span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="no-results">
                <i class="fas fa-users"></i>
                <h5>No Users Found</h5>
                <p>There are no users to display at the moment.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchBtn = document.getElementById('searchBtn');
    
    // Add loading state on form submit
    searchForm.addEventListener('submit', function(e) {
        searchBtn.classList.add('loading');
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    });
    
    // Auto-submit when ID is pasted (optional)
    const searchInput = document.querySelector('.search-input');
    if(searchInput) {
        searchInput.addEventListener('paste', function() {
            setTimeout(() => {
                if(this.value.length >= 3) {
                    searchForm.submit();
                }
            }, 100);
        });
    }
});

// Initialize DataTable
$(document).ready(function() {
    $('#userTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "🔍 Search:",
            searchPlaceholder: "Search users...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "Showing 0 to 0 of 0 users",
            paginate: {
                first: "⟪",
                last: "⟫",
                next: "→",
                previous: "←"
            }
        },
        columnDefs: [
            { targets: [4], className: 'text-right' }
        ]
    });
});
</script>
@endsection