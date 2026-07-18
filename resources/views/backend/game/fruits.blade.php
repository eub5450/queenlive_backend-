@extends('backend.layouts.main')

@section('title')
Fruits Game Dashboard
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Professional Office Dark Theme - No Animations */
    :root {
        --primary-dark: #1e1e1e;
        --secondary-dark: #2d2d2d;
        --tertiary-dark: #363636;
        --border-light: #404040;
        --text-primary: #e0e0e0;
        --text-secondary: #a0a0a0;
        --accent-blue: #2b5797;
        --accent-blue-hover: #1e3f6b;
        --success-green: #2e7d32;
        --success-green-hover: #1e5a24;
        --warning-orange: #b85c1a;
        --warning-orange-hover: #9e4a15;
        --danger-red: #a93226;
        --danger-red-hover: #8b281e;
        --info-teal: #117a7a;
        --info-teal-hover: #0e5e5e;
    }

    body {
        background: var(--primary-dark);
        color: var(--text-primary);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Professional Card Style */
    .card {
        background: var(--secondary-dark);
        border: 1px solid var(--border-light);
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .card-header {
        background: var(--tertiary-dark);
        border-bottom: 1px solid var(--border-light);
        padding: 16px 20px;
        border-radius: 8px 8px 0 0;
    }

    .card-header h4, .card-header h5 {
        margin: 0;
        font-weight: 500;
        color: var(--text-primary);
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 20px;
    }

    /* Balance Display */
    .balance-display {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-top: 16px;
    }

    .balance-item {
        background: var(--tertiary-dark);
        padding: 16px;
        border-radius: 6px;
        border: 1px solid var(--border-light);
    }

    .balance-label {
        display: block;
        color: var(--text-secondary);
        font-size: 13px;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
    }

    .balance-value {
        display: block;
        color: var(--text-primary);
        font-size: 24px;
        font-weight: 500;
        font-family: 'Consolas', monospace;
    }

    /* Control Grid */
    .controls-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    /* Button Base Styles */
    .btn-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-control i {
        margin-right: 8px;
        font-size: 14px;
    }

    /* Button States - Active (On) States */
    .btn-control.btn-active {
        background: var(--success-green);
        color: #fff;
    }

    .btn-control.btn-active:hover {
        background: var(--success-green-hover);
        text-decoration: none;
        color: #fff;
    }

    .btn-control.btn-active i {
        color: #fff;
    }

    /* Button States - Inactive (Off) States */
    .btn-control.btn-inactive {
        background: var(--danger-red);
        color: #fff;
    }

    .btn-control.btn-inactive:hover {
        background: var(--danger-red-hover);
        text-decoration: none;
        color: #fff;
    }

    .btn-control.btn-inactive i {
        color: #fff;
    }

    /* Button States - Neutral Actions */
    .btn-control.btn-neutral {
        background: var(--accent-blue);
        color: #fff;
    }

    .btn-control.btn-neutral:hover {
        background: var(--accent-blue-hover);
        text-decoration: none;
        color: #fff;
    }

    .btn-control.btn-neutral i {
        color: #fff;
    }

    /* Button States - Warning Actions */
    .btn-control.btn-warning {
        background: var(--warning-orange);
        color: #fff;
    }

    .btn-control.btn-warning:hover {
        background: var(--warning-orange-hover);
        text-decoration: none;
        color: #fff;
    }

    .btn-control.btn-warning i {
        color: #fff;
    }

    /* Button States - Info Actions */
    .btn-control.btn-info {
        background: var(--info-teal);
        color: #fff;
    }

    .btn-control.btn-info:hover {
        background: var(--info-teal-hover);
        text-decoration: none;
        color: #fff;
    }

    .btn-control.btn-info i {
        color: #fff;
    }

    /* Modal Trigger Buttons */
    .btn-control.btn-modal {
        background: var(--tertiary-dark);
        border: 1px solid var(--border-light);
        color: var(--text-primary);
    }

    .btn-control.btn-modal:hover {
        background: var(--border-light);
        text-decoration: none;
    }

    .btn-control.btn-modal i {
        color: var(--text-secondary);
    }

    .btn-control.btn-modal:hover i {
        color: var(--text-primary);
    }

    /* Status Badges */
    .game-lock-card, .game-winner-card {
        background: var(--tertiary-dark);
        border: 1px solid var(--border-light);
        border-radius: 6px;
        padding: 16px;
        position: relative;
    }

    .lock-badge, .winner-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .text-warning { color: #f39c12 !important; }
    .text-success { color: #27ae60 !important; }
    .text-info { color: #3498db !important; }
    .text-white-50 { color: rgba(255, 255, 255, 0.5) !important; }

    /* Table Styles */
    .table {
        color: var(--text-primary);
        margin-bottom: 0;
        font-size: 14px;
    }

    .table thead th {
        background: var(--tertiary-dark);
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid var(--border-light);
        padding: 12px;
    }

    .table tbody td {
        padding: 12px;
        border-top: 1px solid var(--border-light);
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: var(--tertiary-dark);
    }

    /* Badges */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 12px;
        letter-spacing: 0.3px;
    }

    .badge-warning {
        background: #b85c1a;
        color: #fff;
    }

    .badge-success {
        background: #2e7d32;
        color: #fff;
    }

    .badge-danger {
        background: #a93226;
        color: #fff;
    }

    .badge-info {
        background: #2b5797;
        color: #fff;
    }

    .badge-secondary {
        background: #404040;
        color: #fff;
    }

    /* Modal Styles */
    .modal-content {
        background: var(--secondary-dark);
        border: 1px solid var(--border-light);
        border-radius: 8px;
    }

    .modal-header {
        background: var(--tertiary-dark);
        border-bottom: 1px solid var(--border-light);
        padding: 16px 20px;
        border-radius: 8px 8px 0 0;
    }

    .modal-header .close {
        color: var(--text-secondary);
        opacity: 1;
        text-shadow: none;
    }

    .modal-header .close:hover {
        color: var(--text-primary);
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid var(--border-light);
        padding: 16px 20px;
    }

    /* Form Controls */
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        color: var(--text-secondary);
        font-size: 13px;
        margin-bottom: 6px;
        font-weight: 400;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        background: var(--primary-dark);
        border: 1px solid var(--border-light);
        border-radius: 4px;
        color: var(--text-primary);
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent-blue);
    }

    /* Button Styles */
    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .btn-primary {
        background: var(--accent-blue);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--accent-blue-hover);
    }

    .btn-secondary {
        background: var(--tertiary-dark);
        border-color: var(--border-light);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background: var(--border-light);
    }

    /* Avatar */
    .avatar-circle {
        width: 32px;
        height: 32px;
        background: var(--tertiary-dark);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        color: var(--text-secondary);
        border: 1px solid var(--border-light);
    }

    /* Fruit Icons */
    .fruit-icon {
        width: 24px;
        height: 24px;
        opacity: 0.7;
    }

    .winner-icon {
        width: 32px;
        height: 32px;
        opacity: 0.8;
    }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_filter input {
        background: var(--primary-dark);
        border: 1px solid var(--border-light);
        border-radius: 4px;
        padding: 6px 10px;
        color: var(--text-primary);
        margin-left: 8px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--accent-blue);
    }

    .dataTables_wrapper .dataTables_length select {
        background: var(--primary-dark);
        border: 1px solid var(--border-light);
        border-radius: 4px;
        padding: 6px 24px 6px 10px;
        color: var(--text-primary);
        margin: 0 4px;
    }

    .dataTables_wrapper .dataTables_info {
        color: var(--text-secondary);
        font-size: 13px;
        padding-top: 16px;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 16px;
    }

    .dataTables_wrapper .paginate_button {
        padding: 6px 12px !important;
        margin: 0 2px;
        border: 1px solid var(--border-light) !important;
        border-radius: 4px !important;
        background: var(--tertiary-dark) !important;
        color: var(--text-secondary) !important;
        cursor: pointer;
    }

    .dataTables_wrapper .paginate_button.current {
        background: var(--accent-blue) !important;
        border-color: var(--accent-blue) !important;
        color: #fff !important;
    }

    .dataTables_wrapper .paginate_button:hover {
        background: var(--border-light) !important;
        border-color: var(--border-light) !important;
        color: var(--text-primary) !important;
    }

    /* Simple Loading - No Animation */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-dark);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-message {
        color: var(--text-secondary);
        font-size: 14px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .balance-display {
            grid-template-columns: 1fr;
        }
        
        .controls-grid {
            grid-template-columns: 1fr;
        }
        
        .balance-value {
            font-size: 20px;
        }
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--primary-dark);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--border-light);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }
</style>
@endpush

@section('content')
@php
$user = \App\RedisCache\RedisCache::UserfindById(Auth::id());
@endphp

@if(Auth::id() == 22222 || Auth::id() == 1)
<!-- Simple Loading Overlay - No Animation -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-message">Loading dashboard...</div>
</div>

<div class="container-fluid">
    <!-- Game Status Cards -->
    <div class="row mb-4">
        <!-- Game Balance Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line me-2"></i>Game Balance Overview</h4>
                </div>
                <div class="card-body">
                    <div class="balance-display">
                        <div class="balance-item">
                            <span class="balance-label">Primary Balance</span>
                            <span class="balance-value" id="gready_balance">{{round($balance->game_balance)}}</span>
                        </div>
                        <div class="balance-item">
                            <span class="balance-label">Secondary Balance</span>
                            <span class="balance-value" id="sec_gready_balance">{{round($balance->second_balance)}}</span>
                        </div>
                        <div class="balance-item">
                            <span class="balance-label">Tertiary Balance</span>
                            <span class="balance-value" id="third_balance">{{round($balance->third_balance)}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
        <!-- Game Controls Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-sliders-h me-2"></i>Game Controls</h4>
                </div>
                <div class="card-body">
                    <div class="controls-grid">
                        <!-- Game Toggle -->
                        <div>
                            @if($balance->game_status == 1)
                            <a href="{{URL::to('admin/fruits_game_off')}}" class="btn-control btn-active" title="Game is ON - Click to turn OFF">
                                <i class="fas fa-power-off"></i> Game On
                            </a>
                            @else 
                            <a href="{{URL::to('admin/fruits_game_on')}}" class="btn-control btn-inactive" title="Game is OFF - Click to turn ON">
                                <i class="fas fa-power-off"></i> Game Off
                            </a>
                            @endif
                        </div>
                        
                        <!-- Robot Toggle -->
                        <div>
                            @if($balance->robot_on == 1)
                            <a href="{{URL::to('admin/fruits_game_robot_off')}}" class="btn-control btn-active" title="Robot is ON - Click to turn OFF">
                                <i class="fas fa-robot"></i> Robot On
                            </a>
                            @else 
                            <a href="{{URL::to('admin/fruits_game_robot_on')}}" class="btn-control btn-inactive" title="Robot is OFF - Click to turn ON">
                                <i class="fas fa-robot"></i> Robot Off
                            </a>
                            @endif
                        </div>
                        
                        <!-- Clear Game -->
                        <div>
                            <a href="{{URL::to('admin/fruits_game_clear')}}" class="btn-control btn-warning" title="Clear current game data">
                                <i class="fas fa-sync-alt"></i> Clear Game
                            </a>
                        </div>
                        
                        <!-- Auto Lock Toggle -->
                        <div>
                            @if($balance->auto_lock == 1)
                            <a href="{{URL::to('admin/fruits_game_auto_lock_off')}}" class="btn-control btn-active" title="Auto Lock is ON - Click to turn OFF">
                                <i class="fas fa-lock"></i> Auto Lock On
                            </a>
                            @else 
                            <a href="{{URL::to('admin/fruits_game_auto_lock_on')}}" class="btn-control btn-inactive" title="Auto Lock is OFF - Click to turn ON">
                                <i class="fas fa-lock-open"></i> Auto Lock Off
                            </a>
                            @endif
                        </div>
                        
                        <!-- Pattern Reverse -->
                        <div>
                            <a href="{{URL::to('admin/game_pattern_reverse')}}" class="btn-control btn-info" title="Reverse game pattern">
                                <i class="fas fa-random"></i> Reverse Pattern
                            </a>
                        </div>
                        
                        <!-- Minus Status -->
                        <div>
                            @if($balance->game_minus_status == 1)
                            <a href="{{URL::to('admin/game_minus_status')}}" class="btn-control btn-active" title="Minus is ACTIVE - Click to deactivate">
                                <i class="fas fa-check-circle"></i> Minus Active
                            </a>
                            @else
                            <a href="{{URL::to('admin/game_minus_status')}}" class="btn-control btn-inactive" title="Minus is INACTIVE - Click to activate">
                                <i class="fas fa-times-circle"></i> Minus Inactive
                            </a>
                            @endif
                        </div>
                        
                        <!-- ID Lock Modal Trigger -->
                        <div>
                            <button type="button" class="btn-control btn-modal" data-toggle="modal" data-target="#idlocked" title="Configure ID lock settings">
                                <i class="fas fa-user-lock"></i> ID Lock
                            </button>
                        </div>
                        
                        <!-- Third Balance Modal Trigger -->
                        <div>
                            <button type="button" class="btn-control btn-modal" data-toggle="modal" data-target="#third_balance_setting" title="Configure third balance settings">
                                <i class="fas fa-cog"></i> 3rd Balance
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="game-lock-card">
                                <span class="lock-badge"><i class="fas fa-lock"></i></span>
                                <div class="text-white-50 mb-2" style="font-size: 13px;">Game Lock</div>
                                <div class="h4 mb-2" style="color: #f39c12;">{{$balance->block_id}}</div>
                                <span class="badge badge-warning">{{$balance->lock_parcent}}% Locked</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="game-winner-card">
                                <span class="winner-badge"><i class="fas fa-trophy"></i></span>
                                <div class="text-white-50 mb-2" style="font-size: 13px;">Winner ID</div>
                                <div class="h4 mb-2" style="color: #27ae60;">{{$balance->winner_id}}</div>
                                <span class="badge badge-success">Current Winner</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    <!-- Third Balance Settings Modal -->
    <div class="modal fade" id="third_balance_setting" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Third Balance Settings</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('fruits_third_setting')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Third Take Margin</label>
                                    <input type="number" name="third_take_margin" value="{{$balance->third_take_margin}}" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label>Third 2nd Give Margin</label>
                                    <input type="number" name="third_helf_give_margin" value="{{$balance->third_helf_give_margin}}" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label>Third 3rd Give Margin</label>
                                    <input type="number" name="third_full_give_margin" value="{{$balance->third_full_give_margin}}" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Third Take Percentage</label>
                                    <input type="number" name="third_take_parcentage" value="{{$balance->third_take_parcentage}}" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label>Third 2nd Given Percentage</label>
                                    <input type="number" name="third_helf_given_parcentage" value="{{$balance->third_helf_given_parcentage}}" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label>Third 3rd Given Percentage</label>
                                    <input type="number" name="third_full_given_parcentage" value="{{$balance->third_full_given_parcentage}}" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Game Withdraw Percentage</label>
                                    <input type="text" name="fruits_game_withdraw_parcentage" value="{{$balance->fruits_game_withdraw_parcentage}}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ID Lock Modal -->
    <div class="modal fade" id="idlocked" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ID Lock Settings</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('fruits_id_lock')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Lock ID Number</label>
                            <input type="number" value="{{$balance->block_id}}" name="block_id" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Lock Percentage</label>
                            <input type="number" name="lock_parcent" value="{{$balance->lock_parcent}}" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Winner ID Number</label>
                            <input type="number" value="{{$balance->winner_id}}" name="winner_id" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bet Details Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Bet Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table display table-bordered table-striped table-hover basic" id="betDetailsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tray ID</th>
                                    <th>Winner</th>
                                    <th><img src="{{asset('public/game/new/image/apple.png')}}" class="fruit-icon" alt="Apple"> Serve</th>
                                    <th><img src="{{asset('public/game/new/image/lemon.png')}}" class="fruit-icon" alt="Lemon"> Serve</th>
                                    <th><img src="{{asset('public/game/new/image/watermelon.png')}}" class="fruit-icon" alt="Watermelon"> Serve</th>
                                    <th>Start Time</th>
                                    <th>Pattern</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($game_serve_details as $i => $game_serve_detail)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><span class="badge badge-info">{{ $game_serve_detail->tray_id }}</span></td>
                                    <td>
                                        @if($game_serve_detail->winner == 'saven_win')
                                        <img src="{{asset('public/game/new/image/lemon.png')}}" class="winner-icon" alt="Lemon">
                                        @elseif($game_serve_detail->winner == 'watermelon')
                                        <img src="{{asset('public/game/new/image/watermelon.png')}}" class="winner-icon" alt="Watermelon">
                                        @else
                                        <img src="{{asset('public/game/new/image/apple.png')}}" class="winner-icon" alt="Apple">
                                        @endif
                                    </td>
                                    <td>{{ $game_serve_detail->apple_serve }}</td>
                                    <td>{{ $game_serve_detail->lemon_serve }}</td>
                                    <td>{{ $game_serve_detail->watermalon_serve }}</td>
                                    <td>{{ $game_serve_detail->created_at }}</td>
                                    <td><span class="badge badge-info">{{ $game_serve_detail->randomPercentage }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Bets Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>User Bets Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="userBetsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>User ID</th>
                                    <th>Tray ID</th>
                                    <th>
                                        <img src="{{asset('public/game/new/image/apple.png')}}" class="fruit-icon" alt="Apple"> Amount
                                    </th>
                                    <th>
                                        <img src="{{asset('public/game/new/image/lemon.png')}}" class="fruit-icon" alt="Lemon"> Amount
                                    </th>
                                    <th>
                                        <img src="{{asset('public/game/new/image/watermelon.png')}}" class="fruit-icon" alt="Watermelon"> Amount
                                    </th>
                                    <th>Status</th>
                                    <th>Add Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($game_serve_users_details as $i => $game_serve_users_detail)
                                @php
                                $user = \App\RedisCache\RedisCache::UserfindById($game_serve_users_detail->user_id);
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div class="avatar-circle">{{ strtoupper(substr($user ? $user->name : 'U', 0, 1)) }}</div>
                                            <span>{{ $user ? $user->name : 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $game_serve_users_detail->user_id }}</td>
                                    <td>{{ $game_serve_users_detail->tray_id }}</td>
                                    <td>{{ $game_serve_users_detail->pot_no == 'apple' ? $game_serve_users_detail->amount : 0 }}</td>
                                    <td>{{ $game_serve_users_detail->pot_no == 'saven_win' ? $game_serve_users_detail->amount : 0 }}</td>
                                    <td>{{ $game_serve_users_detail->pot_no == 'watermelon' ? $game_serve_users_detail->amount : 0 }}</td>
                                    <td>
                                        @if($game_serve_users_detail->status == 0)
                                        <span class="badge badge-warning">Hold</span>
                                        @elseif($game_serve_users_detail->status == 1)
                                        <span class="badge badge-success">Win</span>
                                        @else
                                        <span class="badge badge-danger">Loss</span>
                                        @endif
                                    </td>
                                    <td>{{ $game_serve_users_detail->serve_balance }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable for Bet Details table
    $('#betDetailsTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "order": [[6, "desc"]], // Sort by Start Time descending
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No data available in table",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            {
                "targets": [2], // Winner column
                "orderable": false // Disable sorting on winner column with images
            }
        ]
    });

    // Initialize DataTable for User Bets table
    $('#userBetsTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "order": [[0, "asc"]], // Sort by # ascending
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No data available in table",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            {
                "targets": [1], // User column with avatar
                "orderable": false // Disable sorting on user column with avatars
            }
        ]
    });

    // Auto refresh balances
    function fetchData() {
        $.ajax({
            url: '{{ URL::route('friuts_fetch.data') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#gready_balance').text(Math.round(data.game_balance));
                $('#sec_gready_balance').text(Math.round(data.second_balance));
                $('#third_balance').text(Math.round(data.third_balance));
                setTimeout(fetchData, 30000);
            },
            error: function() {
                setTimeout(fetchData, 30000);
            }
        });
    }

    fetchData();

    // Hide loading overlay
    setTimeout(function() {
        $('#loadingOverlay').fadeOut(500);
    }, 500);
});
</script>
@endpush