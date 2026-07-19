@extends('backend.layouts.main')

@section('title')
Admin Dashboard | 
@endsection

@section('content')
<style>
  /* Modern Dashboard Styling */
  .dashboard-container {
    padding: 24px;
    max-width: 100%;
    background:
      radial-gradient(circle at 8% 8%, rgba(66, 153, 225, .16), transparent 28%),
      radial-gradient(circle at 90% 4%, rgba(237, 100, 166, .15), transparent 26%),
      linear-gradient(135deg, #f7fbff 0%, #eef5ff 48%, #fff8fb 100%);
    min-height: calc(100vh - 70px);
    position: relative;
    z-index: 1;
    overflow-x: hidden;
  }

  .navbar-custom-menu {
    position: relative;
    z-index: 1200;
  }

  .navbar-custom-menu .user-menu,
  .navbar-custom-menu .dropdown-menu {
    position: relative;
    z-index: 1210;
  }

  .navbar-custom-menu .dropdown-menu {
    left: auto;
    right: 0;
  }
  
  .dashboard-header {
    margin-bottom: 30px;
  }
  
  .dashboard-title {
    font-size: 2rem;
    font-weight: 900;
    color: #13213b;
    margin-bottom: 6px;
  }

  .dashboard-hero {
    border-radius: 26px;
    padding: 24px;
    margin-bottom: 22px;
    color: #fff;
    background: linear-gradient(135deg, #071321 0%, #114b93 48%, #8d1b7b 100%);
    box-shadow: 0 22px 55px rgba(16, 37, 80, .20);
    position: relative;
    overflow: hidden;
  }

  .dashboard-hero:before {
    content: "";
    position: absolute;
    right: -80px;
    top: -120px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0, 229, 255, .34), rgba(0, 229, 255, 0) 70%);
  }

  .dashboard-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
  }

  .dashboard-hero p {
    margin: 0;
    color: #dcecff;
    font-weight: 600;
  }

  .dashboard-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 22px;
  }

  .dashboard-summary-card {
    border-radius: 20px;
    padding: 18px;
    color: #fff;
    box-shadow: 0 15px 34px rgba(20, 36, 72, .16);
    min-height: 112px;
  }

  .dashboard-summary-card span {
    display: block;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: .84;
  }

  .dashboard-summary-card strong {
    display: block;
    font-size: 26px;
    line-height: 1.1;
    margin-top: 10px;
    color: #fff;
  }

  .summary-blue { background: linear-gradient(135deg, #2563eb, #06b6d4); }
  .summary-green { background: linear-gradient(135deg, #059669, #22c55e); }
  .summary-gold { background: linear-gradient(135deg, #f59e0b, #f97316); }
  .summary-rose { background: linear-gradient(135deg, #e11d48, #8b5cf6); }
  .summary-purple { background: linear-gradient(135deg, #7c3aed, #db2777); }
  .summary-teal { background: linear-gradient(135deg, #0f766e, #14b8a6); }
  .summary-slate { background: linear-gradient(135deg, #334155, #0f172a); }
  .summary-red { background: linear-gradient(135deg, #dc2626, #f97316); }

  .metric-summary-grid {
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  }

  .dashboard-container .minimizable-card {
    display: none;
  }

  .private-channel-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    background: #111827;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    max-width: 100%;
    word-break: break-all;
  }

  .profit-breakdown-grid {
    display: grid;
    grid-template-columns: 1.2fr repeat(3, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 22px;
  }

  .profit-panel {
    border: 1px solid rgba(208, 220, 238, .85);
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 14px 32px rgba(20,36,72,.10);
    padding: 18px;
  }

  .profit-panel-title {
    color: #536079;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: .8px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .profit-panel-value {
    color: #13213b;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
  }

  .profit-panel-note {
    color: #718096;
    font-size: 12px;
    font-weight: 700;
    margin-top: 8px;
  }

  .live-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .live-badge {
    display: none;
    min-width: 22px;
    border-radius: 999px;
    padding: 3px 8px;
    color: #fff;
    background: #e53e3e;
    font-size: 11px;
    font-weight: 900;
    text-align: center;
  }
  
  /* Status Cards */
  .status-card {
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(20,36,72,.10);
    margin-bottom: 25px;
    border: 1px solid rgba(208, 220, 238, .75);
    overflow: hidden;
    transition: all 0.3s;
    background: #fff;
  }
  
  .status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  }
  
  .status-card-header {
    padding: 16px 20px;
    color: white;
    font-weight: 900;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.3s;
  }
  
  .status-card-header:hover {
    opacity: 0.9;
  }
  
  .status-card-body {
    padding: 20px;
    background: white;
    transition: all 0.3s ease;
  }
  
  .status-card-body.minimized {
    padding: 0;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
  }
  
  .stat-value {
    font-size: 1.55rem;
    font-weight: 900;
    color:#13213b;
    margin-bottom: 10px;
  }
  
  .stat-label {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 5px;
  }
  .stat-value:hover {
    color: #667eea;
}
  .stat-detail {
    font-size: 0.85rem;
    color: black;
  }
  
  /* Color Variants */
  .card-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  
  .card-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  }
  
  .card-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
  }
  
  .card-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
  }
  
  .card-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
  }
  
  .card-purple {
    background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
  }
  
  .card-teal {
    background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
  }
  
  /* Action Buttons */
  .action-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    text-align: center;
    white-space: normal;
    line-height: 1.35;
  }
  
  .action-btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
  }
  
  .action-btn i {
    margin-right: 5px;
  }
  
  .btn-sm {
    padding: 5px 10px;
    font-size: 0.8rem;
  }
  
  .btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  }
  
  .btn-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
  }
  
  .btn-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
  }
  
  .btn-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
  }
  
  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .dashboard-container .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .dashboard-container table {
    min-width: 640px;
  }

  .dashboard-container .modal-dialog {
    max-width: min(700px, calc(100vw - 24px));
  }
  
  /* Profit/Loss Indicator */
  .profit-indicator {
    font-weight: 700;
    font-size: 1.2rem;
  }
  
  .profit {
    color: #38a169;
  }
  
  .loss {
    color: #e53e3e;
  }
  
  /* Chat/Comment Sections */
  .interaction-section {
    background: white;
    border-radius: 18px;
    border: 1px solid rgba(208, 220, 238, .85);
    box-shadow: 0 14px 32px rgba(20,36,72,.10);
    padding: 20px;
    margin-bottom: 25px;
  }
  
  .section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .section-title:hover {
    color: #667eea;
  }
  
  .section-content {
    transition: all 0.3s ease;
  }
  
  .section-content.minimized {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    padding: 0;
    margin: 0;
  }
  
  .interaction-list {
    max-height: 420px;
    overflow-y: auto;
    padding-right: 10px;
  }
  
  /* Toggle Icons */
  .toggle-icon {
    font-size: 1rem;
    transition: transform 0.3s;
  }
  
  .toggle-icon.minimized {
    transform: rotate(-90deg);
  }
  
  /* Modal Styling */
  .modal-content {
    border-radius: 12px;
    border: none;
  }
  
  .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
  }
  
  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .dashboard-title {
      font-size: 1.5rem;
    }
    
    .stat-value {
      font-size: 1.3rem;
    }
  }
  
  /* Animation for new items */
  .chat-item, .comment-item {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    background: white;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
  }
  
  .chat-item:hover, .comment-item:hover {
    background-color: #f8f9fa;
    border-left-color: #667eea;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .chat-item.new-item, .comment-item.new-item {
    animation: slideIn 0.5s ease;
    background-color: #fff3cd;
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .status-badge {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    position: absolute;
    bottom: 2px;
    right: 2px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  .online {
    background-color: #28a745;
    animation: pulse 2s infinite;
  }
  
  .offline {
    background-color: #6c757d;
  }
  
  .message-content, .comment-message {
    word-break: break-word;
    max-width: 100%;
    overflow: hidden;
    font-size: 0.9rem;
  }
  
  .interaction-list {
    scroll-behavior: smooth;
    padding-right: 5px;
  }
  
  .interaction-list::-webkit-scrollbar {
    width: 5px;
  }
  
  .interaction-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  
  .interaction-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
  }
  
  .interaction-list::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
  
  #chat-new-badge, #comment-new-badge {
    animation: pulse 1s infinite;
  }
  
  @keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
  
  /* User info styling */
  .user-info {
    display: flex;
    align-items: center;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
  }
  
  .user-details {
    flex: 1;
  }
  
  .user-name {
    font-weight: 600;
    margin-bottom: 2px;
  }
  
  .user-email {
    font-size: 0.8rem;
    color: #718096;
  }
  
  .message-time {
    font-size: 0.7rem;
    color: #a0aec0;
  }

  @media (max-width: 991.98px) {
    .dashboard-container {
      padding: 18px;
    }

    .dashboard-title {
      font-size: 1.7rem;
    }

    .dashboard-hero {
      padding: 20px;
      border-radius: 22px;
    }

    .dashboard-summary-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .profit-breakdown-grid,
    .live-dashboard-grid {
      grid-template-columns: 1fr;
    }

    .dashboard-container .row.mb-4 > [class*="col-"] {
      margin-bottom: 12px;
    }

    .action-btn {
      width: 100%;
      min-height: 46px;
    }
  }

  @media (max-width: 767.98px) {
    .dashboard-container {
      padding: 14px;
    }

    .dashboard-header {
      margin-bottom: 22px;
    }

    .dashboard-hero {
      padding: 18px 16px;
      margin-bottom: 16px;
      border-radius: 20px;
    }

    .dashboard-hero-content {
      flex-direction: column;
      align-items: flex-start;
    }

    .dashboard-hero-content .text-right {
      width: 100%;
      text-align: left !important;
    }

    .dashboard-title {
      font-size: 1.45rem;
      line-height: 1.2;
    }

    .dashboard-summary-grid {
      grid-template-columns: 1fr;
      gap: 10px;
      margin-bottom: 18px;
    }

    .profit-breakdown-grid {
      grid-template-columns: 1fr;
      margin-bottom: 18px;
    }

    .dashboard-summary-card {
      min-height: 0;
      padding: 16px;
      border-radius: 16px;
    }

    .status-card {
      margin-bottom: 16px;
      border-radius: 16px;
    }

    .status-card-header {
      padding: 14px 16px;
      gap: 12px;
      font-size: 0.95rem;
    }

    .status-card-body {
      padding: 16px;
    }

    .stat-value {
      font-size: 1.3rem;
    }

    .dashboard-container .row {
      margin-left: -6px;
      margin-right: -6px;
    }

    .dashboard-container .row > [class*="col-"] {
      padding-left: 6px;
      padding-right: 6px;
      margin-bottom: 12px;
    }

    .dashboard-container .d-flex.flex-wrap.gap-2.mt-3 {
      gap: 0.65rem !important;
    }

    .dashboard-container .modal-dialog {
      margin: 0.75rem auto;
      max-width: calc(100vw - 12px);
    }
  }

  @media (max-width: 575.98px) {
    .dashboard-container {
      padding: 12px;
    }

    .dashboard-hero p {
      font-size: 0.92rem;
      line-height: 1.55;
    }

    .status-card-header span,
    .stat-label,
    .stat-detail {
      word-break: break-word;
    }

    .dashboard-container table {
      min-width: 560px;
    }
  }

</style>

@php
  $currentAdminEmail = strtolower((string) optional(auth()->user())->email);
  $hideGameProCalculationPanel = !\App\Models\AdminParmisiton::allowed(\Auth::id(), 'dashboard_game_pro_balance_manage');
  $adminCan = function ($key, $default = false) {
      return \App\Models\AdminParmisiton::allowed(Auth::id(), $key, $default);
  };
@endphp

@if($adminCan('dashboard_access'))
@if((int)(Auth::user()->is_admin ?? 0) === 2)
<div class="alert" style="background:linear-gradient(135deg,#fff8e1,#fff3cd);border:1.5px solid #ffc107;border-radius:8px;padding:10px 14px;margin-bottom:12px;"><b style="color:#856404;">Country Admin Mode</b> — <span style="color:#856404;font-size:13px;">Showing data for your assigned country only.</span></div>
@endif
<div class="dashboard-container">
  <div class="dashboard-header">
    <div class="dashboard-hero">
      <div class="dashboard-hero-content">
        <div>
          <h1 class="dashboard-title">
            <i class="fas fa-tachometer-alt mr-2"></i> Admin Dashboard
          </h1>
          <p>Live business metrics, wallet totals, game balances, and operational controls.</p>
        </div>
        <div class="text-right">
          <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#aee8ff;font-weight:900;">Today Users</div>
          <div style="font-size:32px;font-weight:900;color:#fff;">{{ $today_user }}</div>
        </div>
      </div>
    </div>

    <div class="dashboard-summary-grid">
      @if($adminCan('dashboard_total_users'))
      <div class="dashboard-summary-card summary-blue">
        <span>Total Users</span>
        <strong>{{ $total_users }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_user_wallets'))
      <div class="dashboard-summary-card summary-green">
        <span>User Wallets</span>
        <strong>{{ $users_balance }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_game_profit'))
      <div class="dashboard-summary-card summary-gold">
        <span>Game Profit</span>
        <strong>{{ $game_profit_total ?? 0 }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_profit_loss'))
      <div class="dashboard-summary-card summary-rose">
        <span>Profit / Loss</span>
        <strong>{{ round($loss_profit) }}</strong>
      </div>
      @endif
    </div>

    <div class="dashboard-summary-grid">
      @if($adminCan('dashboard_today_recharge'))
      <div class="dashboard-summary-card summary-blue">
        <span>Today Recharge</span>
        <strong>{{ $today_portal_transfer }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_today_sending'))
      <div class="dashboard-summary-card summary-green">
        <span>Today Sending</span>
        <strong>{{ $today_sanding }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_today_receiving'))
      <div class="dashboard-summary-card summary-gold">
        <span>Today Receiving</span>
        <strong>{{ $today_reciving }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_today_gift'))
      <div class="dashboard-summary-card summary-rose">
        <span>Today Gift Sum</span>
        <strong>{{ $today_gift_sum }}</strong>
      </div>
      @endif
    </div>

    <div class="profit-breakdown-grid" id="dashboard-game-profit">
      @unless($hideGameProCalculationPanel)
        <div class="profit-panel">
          <div class="profit-panel-title">GamePro Calculation</div>
          <div class="profit-panel-value {{ ($game_profit_total ?? 0) >= 0 ? 'profit' : 'loss' }}">
            {{ $game_profit_total ?? 0 }}
          </div>
          <div class="profit-panel-note">
            Local game adjust net plus Thomas game settlement house result.
          </div>
        </div>
      @endunless
      @if($adminCan('dashboard_today_game_profit'))
      <div class="profit-panel">
        <div class="profit-panel-title">Today Game Profit</div>
        <div class="profit-panel-value {{ ($game_profit_today ?? 0) >= 0 ? 'profit' : 'loss' }}">
          {{ $game_profit_today ?? 0 }}
        </div>
        <div class="profit-panel-note">
          Local today: {{ $game_profit_breakdown['local_today'] ?? 0 }} | Thomas today: {{ $game_profit_breakdown['thomas_today'] ?? 0 }}
        </div>
      </div>
      @endif
      @if($adminCan('dashboard_game_pro_balance'))
      <div class="profit-panel">
        <div class="profit-panel-title">Game Pro Balance</div>
        <div class="profit-panel-value">{{ $game_pro_balance ?? 0 }}</div>
        <div class="profit-panel-note">
          Active Fruits, Greedy, Teen Patti, Five Star and Lucky Gift pools.
        </div>
      </div>
      @endif
      @if($adminCan('dashboard_thomas_settlements'))
      <div class="profit-panel">
        <div class="profit-panel-title">Thomas Settlements</div>
        <div class="profit-panel-value {{ (($game_profit_breakdown['thomas_total'] ?? 0) >= 0) ? 'profit' : 'loss' }}">
          {{ $game_profit_breakdown['thomas_total'] ?? 0 }}
        </div>
        <div class="profit-panel-note">
          Bet: {{ $game_profit_breakdown['thomas_total_bet'] ?? 0 }} | Payout: {{ $game_profit_breakdown['thomas_total_payout'] ?? 0 }}
        </div>
      </div>
      @endif
    </div>

    <div class="dashboard-summary-grid metric-summary-grid" id="dashboard-all-summary-cards">
      @if($adminCan('dashboard_withdraw_commission'))
      <div class="dashboard-summary-card summary-blue">
        <span>Withdraw Commission</span>
        <strong>{{ $approved_balance }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_users_agents'))
      <div class="dashboard-summary-card summary-green">
        <span>Users & Agents</span>
        <strong>{{ $total_users }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_coin_metrics'))
      <div class="dashboard-summary-card summary-gold">
        <span>Coin Metrics</span>
        <strong>{{ $total_coin_beg }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_game_pro_balance'))
      <div class="dashboard-summary-card summary-purple">
        <span>Game Pro Balance</span>
        <strong>{{ $game_pro_balance ?? 0 }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_today_transactions'))
      <div class="dashboard-summary-card summary-rose">
        <span>Today Transactions</span>
        <strong>{{ $today_portal_transfer }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_user_wallets'))
      <div class="dashboard-summary-card summary-teal">
        <span>User Wallets</span>
        <strong>{{ $users_balance }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_withdraw_profit'))
      <div class="dashboard-summary-card summary-slate">
        <span>Withdraw Profit</span>
        <strong>{{ $withdraw_app_profit_today }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_portal_balance'))
      <div class="dashboard-summary-card summary-blue">
        <span>Portal Balance</span>
        <strong>{{ $total_portal_recharge-($total_portal_transfer+$total_portal_recall) }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_portal_send'))
      <div class="dashboard-summary-card summary-green">
        <span>Portal Send</span>
        <strong>{{ $protal_sand }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_total_serve_coin'))
      <div class="dashboard-summary-card summary-gold">
        <span>Total Serve Coin</span>
        <strong>{{ $total_serve }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_total_receiving'))
      <div class="dashboard-summary-card summary-purple">
        <span>Total Receiving</span>
        <strong>{{ $total_gift }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_coin_generate_game'))
      <div class="dashboard-summary-card summary-teal">
        <span>Coin Generation</span>
        <strong>{{ ($total_portal_recharge-($total_portal_transfer+$total_portal_recall))+$protal_sand }}</strong>
      </div>
      @endif
      @if($adminCan('dashboard_profit_loss'))
      <div class="dashboard-summary-card {{ $loss_profit > 0 ? 'summary-green' : 'summary-red' }}">
        <span>Profit / Loss</span>
        <strong>{{ round($loss_profit) }}</strong>
      </div>
      @endif
    </div>
  </div>

  <!-- Financial Summary Cards - All will be minimizable -->
  <div class="row">
    @if($adminCan('dashboard_withdraw_commission'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-primary">
          <span>Withdraw Commission</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-wallet mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$approved_balance}}</div>
          <div class="stat-label">Total Withdraw Commission</div>
          <div class="stat-detail">
            Converted: {{$agency_convart_balance}} | Hold: {{$approved_balance-$agency_convart_balance}}
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_users_agents'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-info">
          <span>Users & Agents</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-users mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$total_users}}</div>
          <div class="stat-label">Total Users (Today: {{$today_user}})</div>
          <div class="stat-detail">
            Active Hosts: {{$active_host}} | Total Agency: {{$total_agency}}
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_coin_metrics'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-success">
          <span>Coin Metrics</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-coins mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$total_coin_beg}}</div>
          <div class="stat-label">Total Coin Beg (Today: {{$today_coin_beg}})</div>
          <div class="stat-detail">
            Entry Frame Profit: {{$EntryFrameProfit}}
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_game_pro_balance'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 48%, #6a11cb 100%);">
          <span>Game Pro Balance</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-gamepad mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{ $game_pro_balance ?? 0 }}</div>
          <div class="stat-label">Total Active Game Balance</div>
          <div class="stat-detail">
            Profit: {{ $game_profit_total ?? 0 }} | Today: {{ $game_profit_today ?? 0 }}
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($adminCan('dashboard_today_transactions'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-warning">
          <span>Today's Transactions</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-exchange-alt mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$today_portal_transfer}}</div>
          <div class="stat-label">Today Recharge</div>
          <div class="stat-detail">
            Send: {{$today_sanding}} | Receive: {{$today_reciving}} | Gift Sum: {{$today_gift_sum}} | Withdraw: {{$today_withdraw}}
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- Second Row of Status Cards -->
  <div class="row">
    @if($adminCan('dashboard_user_wallets'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-purple">
          <span>User Wallets</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-piggy-bank mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$users_balance}}</div>
          <div class="stat-label">Total Wallet Balance</div>
          <div class="stat-detail">
            <a href="{{URL::to('weekly_new_user')}}" class="action-btn btn-sm btn-info">
              <i class="fas fa-calendar-week mr-1"></i> Weekly New Users
            </a>
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_withdraw_profit'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-teal">
          <span>Withdraw Profit</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-chart-line mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$withdraw_app_profit_today}}</div>
          <div class="stat-label">Today's Profit</div>
          <div class="stat-detail">
            This Month: {{$withdraw_app_profit_total}}
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_portal_balance'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-info">
          <span>Portal Balance</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-server mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$total_portal_recharge-($total_portal_transfer+$total_portal_recall)}}</div>
          <div class="stat-label">Available Balance</div>
          <div class="stat-detail">
            Total Recharge: {{$total_portal_recharge}}
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_portal_send'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-primary">
          <span>Portal Sand</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-paper-plane mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$protal_sand}}</div>
          <div class="stat-label">Total Sand Transactions</div>
          <div class="stat-detail">
            Recall: {{$total_recall}}
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- Third Row of Status Cards -->
  <div class="row">
    @if($adminCan('dashboard_total_serve_coin'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-success">
          <span>Total Serve Coin</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-coins mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$total_serve}}</div>
          <div class="stat-label">System-wide Coin Balance</div>
          <div class="stat-detail">
            Includes all games and wallets
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($adminCan('dashboard_total_receiving'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-warning">
          <span>Total Receiving</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-download mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{$total_gift}}</div>
          <div class="stat-label">Gifts Received</div>
          <div class="stat-detail">
            Total transactions processed
          </div>
        </div>
      </div>
    </div>
    @endif
    
    @if($adminCan('dashboard_coin_generate_game'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-purple">
          <span>Coin Generation</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-bolt mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value">{{($total_portal_recharge-($total_portal_transfer+$total_portal_recall))+$protal_sand}}</div>
          <div class="stat-label">System Generated</div>
          <div class="stat-detail">
            Includes all sources
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($adminCan('dashboard_profit_loss'))
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card minimizable-card" data-minimized="true">
        <div class="status-card-header card-danger">
          <span>Profit/Loss</span>
          <div class="d-flex align-items-center">
            <i class="fas fa-chart-pie mr-2"></i>
            <i class="fas fa-chevron-down toggle-icon minimized"></i>
          </div>
        </div>
        <div class="status-card-body minimized">
          <div class="stat-value profit-indicator @if($loss_profit>0) profit @else loss @endif">
            {{round($loss_profit)}}
          </div>
          <div class="stat-label">Current Status</div>
          <div class="stat-detail">
            @if($loss_profit>0) Profitable @else Operating at Loss @endif
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  @if($adminCan('dashboard_realtime_feeds'))
  <div class="live-dashboard-grid">
    <div class="interaction-section minimizable-section" data-minimized="false" id="dashboard-live-chat">
      <div class="section-title">
        <span><i class="fas fa-comments mr-2"></i>Real Time Chat</span>
        <span class="d-flex align-items-center">
          <span id="chat-new-badge" class="live-badge mr-2">0</span>
          <i class="fas fa-chevron-down toggle-icon"></i>
        </span>
      </div>
      <div class="section-content">
        <div class="profit-panel-note mb-3">Latest private chat messages refresh automatically and show the private chat channel.</div>
        <div class="interaction-list" id="chat-list">
          @include('backend.partials.chat-items', ['chats' => $initialChats ?? collect()])
        </div>
      </div>
    </div>

    <div class="interaction-section minimizable-section" data-minimized="false" id="dashboard-live-comments">
      <div class="section-title">
        <span><i class="fas fa-broadcast-tower mr-2"></i>Real Time Room Comments</span>
        <span class="d-flex align-items-center">
          <span id="comment-new-badge" class="live-badge mr-2">0</span>
          <i class="fas fa-chevron-down toggle-icon"></i>
        </span>
      </div>
      <div class="section-content">
        <div class="profit-panel-note mb-3">Latest room comments refresh automatically and show the private-*-room.channel target.</div>
        <div class="interaction-list" id="comment-list">
          @include('backend.partials.comment-items', ['comments' => $initialComments ?? collect()])
        </div>
      </div>
    </div>
  </div>
  @endif

  @if($adminCan('dashboard_game_data'))
  <!-- Game Balance Management Section (NOT minimizable) -->
  <div class="row mt-4">
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card">
        <div class="status-card-header" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);">
          <span>Fruits Game Balance</span>
          <i class="fas fa-apple-alt"></i>
        </div>
        <div class="status-card-body">
          <div class="stat-value">{{round($fruts_game_balance->game_balance+$fruts_game_balance->second_balance)}}</div>
          <div class="stat-label">Total Balance</div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="action-btn btn-danger" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Fruits"
                    data-balance-type="main"
                    data-balance-display="Main Balance"
                    data-action="{{URL::to('game_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> Adjust Game
            </button>
            <button type="button" class="action-btn btn-warning" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Fruits"
                    data-balance-type="second"
                    data-balance-display="2nd Balance"
                    data-action="{{URL::to('fruits_game_sec_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 2nd Balance
            </button>
            <button type="button" class="action-btn btn-info" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Fruits"
                    data-balance-type="third"
                    data-balance-display="3rd Balance"
                    data-action="{{URL::to('fruits_game_third_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 3rd Balance
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card">
        <div class="status-card-header" style="background: linear-gradient(135deg, #f687b3 0%, #ed64a6 100%);">
          <span>Greedy Game Balance</span>
          <i class="fas fa-gem"></i>
        </div>
        <div class="status-card-body">
          <div class="stat-value">{{round($greedy_game_balance->game_balance+$greedy_game_balance->second_balance+$greedy_game_balance->third_balance)}}</div>
          <div class="stat-label">Total Balance</div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="action-btn btn-success" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Greedy"
                    data-balance-type="main"
                    data-balance-display="Main Balance"
                    data-action="{{URL::to('greedy_game_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> Adjust Game
            </button>
            <button type="button" class="action-btn btn-primary" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Greedy"
                    data-balance-type="second"
                    data-balance-display="2nd Balance"
                    data-action="{{URL::to('greedy_game_sec_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 2nd Balance
            </button>
            <button type="button" class="action-btn btn-primary" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Greedy"
                    data-balance-type="third"
                    data-balance-display="3rd Balance"
                    data-action="{{URL::to('greedy_game_third_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 3rd Balance
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card">
        <div class="status-card-header" style="background: linear-gradient(135deg, #68d391 0%, #48bb78 100%);">
          <span>Teen Patti Balance</span>
          <i class="fas fa-dice"></i>
        </div>
        <div class="status-card-body">
          <div class="stat-value">{{$teenpatti_game_balance->game_balance}}</div>
          <div class="stat-label">Total Balance</div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="action-btn btn-success" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Teen Patti"
                    data-balance-type="main"
                    data-balance-display="Main Balance"
                    data-action="{{URL::to('teenpatti_game_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> Adjust Game
            </button>
            <button type="button" class="action-btn btn-info" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Teen Patti"
                    data-balance-type="second"
                    data-balance-display="2nd Balance"
                    data-action="{{URL::to('teenpatti_game_sec_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 2nd Balance
            </button>
            <button type="button" class="action-btn btn-primary" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Teen Patti"
                    data-balance-type="third"
                    data-balance-display="3rd Balance"
                    data-action="{{URL::to('teen_patti_game_third_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> 3rd Balance
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Lucky Gift and Five Star Games (if you have them) -->
  @if(isset($lucky_game_balance) || isset($five_game_balance))
  <div class="row mt-4">
    @if(isset($lucky_game_balance))
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
      <div class="status-card">
        <div class="status-card-header" style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);">
          <span>Lucky Gift Balance</span>
          <i class="fas fa-gift"></i>
        </div>
        <div class="status-card-body">
          <div class="stat-value">{{$lucky_game_balance->game_balance ?? 0}}</div>
          <div class="stat-label">Total Balance</div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="action-btn btn-primary" 
                    data-toggle="modal" 
                    data-target="#gameBalanceModal"
                    data-game="Lucky"
                    data-balance-type="main"
                    data-balance-display="Main Balance"
                    data-action="{{URL::to('lucky_game_balance_block')}}">
              <i class="fas fa-cog mr-1"></i> Adjust Game
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif
    
    <!--@if(isset($five_game_balance))-->
    <!--<div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">-->
    <!--  <div class="status-card">-->
    <!--    <div class="status-card-header" style="background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);">-->
    <!--      <span>Five Star Balance</span>-->
    <!--      <i class="fas fa-star"></i>-->
    <!--    </div>-->
    <!--    <div class="status-card-body">-->
    <!--      <div class="stat-value">{{$five_game_balance->game_balance ?? 0}}</div>-->
    <!--      <div class="stat-label">Total Balance</div>-->
    <!--      <div class="d-flex flex-wrap gap-2 mt-3">-->
    <!--        <button type="button" class="action-btn btn-warning" -->
    <!--                data-toggle="modal" -->
    <!--                data-target="#gameBalanceModal"-->
    <!--                data-game="Five"-->
    <!--                data-balance-type="main"-->
    <!--                data-balance-display="Main Balance"-->
    <!--                data-action="{{URL::to('five_game_balance_block')}}">-->
    <!--          <i class="fas fa-cog mr-1"></i> Adjust Game-->
    <!--        </button>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--  </div>-->
    <!--</div>-->
    <!--@endif-->
  </div>
  @endif
  @endif

</div>

@if($adminCan('dashboard_game_data'))
<!-- Single Reusable Game Balance Modal -->
<div class="modal fade" id="gameBalanceModal" tabindex="-1" role="dialog" aria-labelledby="gameBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="gameBalanceModalLabel">Adjust Game Balance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="gameBalanceForm" method="post">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="game_name" id="modal_game_name">
          <input type="hidden" name="balance_type" id="modal_balance_type">
          
          <div class="form-group">
            <label for="modal_game_display" class="col-form-label">Game Name:</label>
            <input type="text" class="form-control" id="modal_game_display" readonly disabled style="background-color: #f8f9fa;">
          </div>
          
          <div class="form-group">
            <label for="modal_balance_display" class="col-form-label">Balance Type:</label>
            <input type="text" class="form-control" id="modal_balance_display" readonly disabled style="background-color: #f8f9fa;">
          </div>
          
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Transaction Type:</label>
            <select name="type" class="form-control" id="modal_transaction_type" required>
              <option value="deposit">Deposit (Add Money)</option>
              <option value="withdraw">Withdraw (Remove Money)</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="modal_amount" class="col-form-label">Amount:</label>
            <input type="number" name="amount" class="form-control" id="modal_amount" required min="0.01" step="0.01" placeholder="Enter amount">
          </div>
          
          <div class="alert alert-info mt-2" style="font-size: 0.9rem;">
            <i class="fas fa-info-circle mr-1"></i> 
            <span id="modal_action_description">You are about to adjust the main balance</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="modal_submit_btn">
            <i class="fas fa-save mr-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@else
<div class="dashboard-container">
  <div class="alert alert-warning mb-0">Dashboard access is not allowed for this account.</div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Minimize functionality
$(document).ready(function() {
    // Auto-minimize all status cards and sections on load
    setTimeout(function() {
        // Status cards minimize
        $('.minimizable-card').each(function() {
            const card = $(this);
            const body = card.find('.status-card-body');
            const icon = card.find('.toggle-icon');
            
            if (card.data('minimized') === true) {
                body.addClass('minimized');
                icon.addClass('minimized');
            }
        });
        
        // Sections minimize
        $('.minimizable-section').each(function() {
            const section = $(this);
            const content = section.find('.section-content');
            const icon = section.find('.toggle-icon');
            
            if (section.data('minimized') === true) {
                content.addClass('minimized');
                icon.addClass('minimized');
            }
        });
    }, 100);
    
    // Toggle status cards on header click
    $(document).on('click', '.minimizable-card .status-card-header', function(e) {
        e.preventDefault();
        const card = $(this).closest('.minimizable-card');
        const body = card.find('.status-card-body');
        const icon = card.find('.toggle-icon');
        
        body.toggleClass('minimized');
        icon.toggleClass('minimized');
        
        // Update data attribute
        card.data('minimized', body.hasClass('minimized'));
    });
    
    // Toggle sections on title click
    $(document).on('click', '.minimizable-section .section-title', function(e) {
        e.preventDefault();
        const section = $(this).closest('.minimizable-section');
        const content = section.find('.section-content');
        const icon = section.find('.toggle-icon');
        
        content.toggleClass('minimized');
        icon.toggleClass('minimized');
        
        // Update data attribute
        section.data('minimized', content.hasClass('minimized'));
    });
});

// Game Balance Modal Handler
$(document).ready(function() {
    // When modal is about to show, populate it with button data
    $('#gameBalanceModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const gameName = button.data('game');
        const balanceType = button.data('balance-type');
        const balanceDisplay = button.data('balance-display');
        const actionUrl = button.data('action');
        
        const modal = $(this);
        const modalForm = modal.find('#gameBalanceForm');
        
        // Set form values
        modalForm.attr('action', actionUrl);
        modal.find('#modal_game_name').val(gameName);
        modal.find('#modal_balance_type').val(balanceType);
        modal.find('#modal_game_display').val(gameName);
        modal.find('#modal_balance_display').val(balanceDisplay);
        
        // Update action description
        let description = `You are about to adjust the ${balanceDisplay.toLowerCase()} of ${gameName} game.`;
        modal.find('#modal_action_description').text(description);
        
        // Clear previous values
        modal.find('#modal_amount').val('');
        modal.find('#modal_transaction_type').val('deposit');
    });
    
    // Form validation
    $('#gameBalanceForm').on('submit', function(e) {
        const amount = $('#modal_amount').val();
        
        if (!amount || parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('Please enter a valid amount greater than 0');
            return false;
        }
    });
    
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

// Dashboard live chat and room comment polling
$(document).ready(function() {
    let lastChatId = parseInt($('#chat-list .chat-item:first').data('id') || 0, 10);
    let lastCommentId = parseInt($('#comment-list .comment-item:first').data('id') || 0, 10);

    function prependLiveItems(listSelector, badgeSelector, html, count) {
        const list = $(listSelector);
        const badge = $(badgeSelector);

        list.prepend(html);
        list.find('.chat-item, .comment-item').slice(30).remove();
        list.find('.chat-item, .comment-item').slice(0, count).addClass('new-item');

        badge.text(count).fadeIn(150);
        setTimeout(function() {
            badge.fadeOut(250);
            list.find('.new-item').removeClass('new-item');
        }, 5000);
    }

    function pollNewChats() {
        $.get('{{ route('chat.new') }}', { last_id: lastChatId })
            .done(function(response) {
                if (!response || !response.html) {
                    return;
                }

                lastChatId = parseInt(response.last_id || lastChatId, 10);
                prependLiveItems('#chat-list', '#chat-new-badge', response.html, response.count || 1);
            });
    }

    function pollNewComments() {
        $.get('{{ route('comment.new') }}', { last_id: lastCommentId })
            .done(function(response) {
                if (!response || !response.html) {
                    return;
                }

                lastCommentId = parseInt(response.last_id || lastCommentId, 10);
                prependLiveItems('#comment-list', '#comment-new-badge', response.html, response.count || 1);
            });
    }

    setInterval(pollNewChats, 10000);
    setInterval(pollNewComments, 10000);
});

</script>
@endpush
