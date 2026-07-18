@extends('backend.layouts.main')

@section('title')
Thomas Game Details
@endsection

@section('content')
<style>
    .thomas-detail-page{padding:20px;background:#242424;min-height:calc(100vh - 70px);color:#e8edf5}
    .detail-topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .detail-title{margin:0;font-size:18px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;color:#f8fafc}
    .detail-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .detail-btn{border:0;border-radius:6px;padding:9px 13px;font-weight:800;color:#fff;text-decoration:none;background:#2563eb;display:inline-flex;align-items:center;gap:6px}
    .detail-btn:hover{color:#fff;text-decoration:none;background:#1d4ed8}
    .detail-select{height:38px;background:#161616;color:#f8fafc;border:1px solid #3d4652;border-radius:6px;padding:0 10px;font-weight:700}
    .detail-card{background:#2b2b2b;border:1px solid #3c4654;border-radius:8px;margin-bottom:18px;box-shadow:0 12px 30px rgba(0,0,0,.22);overflow:hidden}
    .detail-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#333;padding:13px 16px;border-bottom:1px solid #3c4654}
    .detail-card-head h3{margin:0;color:#f8fafc;font-size:15px;font-weight:900;text-transform:uppercase}
    .detail-sub{color:#c6ced8;font-size:12px;font-weight:700}
    .detail-table-wrap{width:100%;overflow:auto}
    .detail-table{width:100%;margin:0;border-collapse:collapse;color:#f1f5f9}
    .detail-table th{background:#343434;color:#b8c2cf;font-size:12px;text-transform:uppercase;white-space:nowrap;border:1px solid #424a55;padding:12px}
    .detail-table td{border:1px solid #3e454f;padding:12px;white-space:nowrap;vertical-align:middle;background:#2b2b2b}
    .detail-table tbody tr:hover td{background:#303743}
    .round-id{display:inline-flex;background:#2b5797;color:#fff;border-radius:5px;padding:4px 8px;font-weight:900;font-size:12px}
    .winner-chip{display:inline-flex;align-items:center;gap:6px;font-weight:900}
    .status-pill{display:inline-flex;border-radius:5px;padding:4px 8px;font-size:11px;font-weight:900;color:#fff;text-transform:capitalize}
    .status-win,.status-won,.status-settled,.status-completed{background:#2f8f3b}
    .status-loss,.status-lost,.status-failed{background:#c0392b}
    .status-hold,.status-pending,.status-accepted,.status-processing{background:#b7791f}
    .status-open,.status-betting,.status-revealed{background:#2563eb}
    .money-positive{color:#7ee787;font-weight:900}
    .money-negative{color:#ff8b8b;font-weight:900}
    .user-cell{display:flex;align-items:center;gap:10px;min-width:230px}
    .avatar{width:32px;height:32px;border-radius:50%;background:#3a3a3a;display:grid;place-items:center;color:#f8fafc;font-size:12px;font-weight:900;overflow:hidden;border:1px solid #464f5c}
    .avatar img{width:100%;height:100%;object-fit:cover}
    .empty-state{padding:22px;color:#c6ced8;font-weight:800;text-align:center}
</style>

<div class="body-content thomas-detail-page">
    <div class="detail-topbar">
        <div>
            <h1 class="detail-title">Thomas Game Bet Details</h1>
            <div class="detail-sub">
                {{ $selectedGame ? $selectedGame->name . ' | ' . $selectedGame->game_code : 'No game selected' }}
            </div>
        </div>
        <div class="detail-actions">
            <form method="get" action="{{ URL::to('admin/thomas-game-lobby/details') }}">
                <select class="detail-select" name="game_code" onchange="this.form.submit()">
                    @foreach($games as $game)
                        <option value="{{ $game->game_code }}" {{ $selectedGame && $selectedGame->game_code === $game->game_code ? 'selected' : '' }}>
                            {{ $game->name }} ({{ $game->game_code }})
                        </option>
                    @endforeach
                </select>
            </form>
            <a class="detail-btn" href="{{ URL::to('admin/thomas-game-lobby') }}">Back Lobby</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head">
            <h3>Bet Details</h3>
            <div class="detail-sub">Last 40 rounds with board totals, payout and payout selection algorithm</div>
        </div>
        <div class="detail-table-wrap">
            @if($roundRows->isEmpty())
                <div class="empty-state">No rounds found for this game.</div>
            @else
                <table class="detail-table table display table-bordered table-striped table-hover basic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Round ID</th>
                            <th>Winner</th>
                            @foreach($boardKeys as $boardKey)
                                <th>{{ $boards[$boardKey]['icon'] ?? '•' }} {{ $boards[$boardKey]['label'] ?? $boardKey }} Serve</th>
                            @endforeach
                            <th>Total Bet</th>
                            <th>Total Payout</th>
                            <th>House Result</th>
                            <th>Start Time</th>
                            <th>Status</th>
                            <th>Algorithm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roundRows as $index => $round)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="round-id">{{ $round['round_no'] }}</span></td>
                                <td>
                                    @if(!empty($round['winner_board_key']))
                                        <span class="winner-chip">{{ $boards[$round['winner_board_key']]['icon'] ?? '🎯' }} {{ $boards[$round['winner_board_key']]['label'] ?? $round['winner_board_key'] }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                @foreach($boardKeys as $boardKey)
                                    <td>{{ number_format((float) ($round['board_totals'][$boardKey] ?? 0), 0) }}</td>
                                @endforeach
                                <td>{{ number_format((float) $round['total_bet_amount'], 0) }}</td>
                                <td>{{ number_format((float) $round['total_payout_amount'], 0) }}</td>
                                <td class="{{ (float) $round['net_house_result'] >= 0 ? 'money-positive' : 'money-negative' }}">
                                    {{ number_format((float) $round['net_house_result'], 0) }}
                                </td>
                                <td>{{ $round['start_at'] }}</td>
                                <td><span class="status-pill status-{{ strtolower($round['settlement_status'] ?: $round['status']) }}">{{ $round['settlement_status'] ?: $round['status'] }}</span></td>
                                <td>{{ $round['algorithm_name'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head">
            <h3>User Bets Details</h3>
            <div class="detail-sub">All user bet rows from the selected last 40 rounds with payout details</div>
        </div>
        <div class="detail-table-wrap">
            @if($betRows->isEmpty())
                <div class="empty-state">No user bets found for these rounds.</div>
            @else
                <table class="detail-table table display table-bordered table-striped table-hover basic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>User ID</th>
                            <th>Round ID</th>
                            @foreach($boardKeys as $boardKey)
                                <th>{{ $boards[$boardKey]['icon'] ?? '•' }} Amount</th>
                            @endforeach
                            <th>Multiplier</th>
                            <th>Potential Payout</th>
                            <th>Paid Payout</th>
                            <th>Status</th>
                            <th>Wallet After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($betRows as $index => $bet)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar">
                                            @if(!empty($bet['user_profile']))
                                                <img src="{{ $bet['user_profile'] }}" alt="">
                                            @else
                                                {{ strtoupper(substr($bet['user_name'], 0, 1)) }}
                                            @endif
                                        </div>
                                        <span>{{ $bet['user_name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $bet['user_id'] }}</td>
                                <td><span class="round-id">{{ $bet['round_no'] }}</span></td>
                                @foreach($boardKeys as $boardKey)
                                    <td>{{ number_format((float) ($bet['board_amounts'][$boardKey] ?? 0), 0) }}</td>
                                @endforeach
                                <td>{{ number_format((float) $bet['payout_multiplier'], 2) }}x</td>
                                <td>{{ number_format((float) $bet['potential_win'], 0) }}</td>
                                <td>{{ number_format((float) $bet['win_amount'], 0) }}</td>
                                <td><span class="status-pill status-{{ strtolower($bet['status']) }}">{{ $bet['status'] }}</span></td>
                                <td>{{ number_format((float) $bet['wallet_after'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
