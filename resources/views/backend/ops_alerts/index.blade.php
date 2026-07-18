@extends('backend.layouts.main')

@section('title')
OPS Alert Inbox
@endsection

@section('content')
<style>
    .ops-alert-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 24px;
    }

    .ops-alert-card .card-header {
        border-radius: 12px 12px 0 0;
        color: #fff;
        font-weight: 600;
    }

    .ops-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
    }

    .ops-badge-critical {
        background: #e53e3e;
    }

    .ops-badge-warning {
        background: #dd6b20;
    }

    .ops-badge-info {
        background: #3182ce;
    }

    .ops-badge-firing {
        background: #c53030;
    }

    .ops-badge-resolved {
        background: #2f855a;
    }
</style>

<div class="body-content">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card ops-alert-card">
                <div class="card-header" style="background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);">Firing Alerts</div>
                <div class="card-body">
                    <h3>{{ $summary['firing_count'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted">Currently active monitoring alerts</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card ops-alert-card">
                <div class="card-header" style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);">Critical Alerts</div>
                <div class="card-body">
                    <h3>{{ $summary['critical_count'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted">Critical alerts needing immediate review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card ops-alert-card">
                <div class="card-header" style="background: linear-gradient(135deg, #d69e2e 0%, #b7791f 100%);">Warning Alerts</div>
                <div class="card-body">
                    <h3>{{ $summary['warning_count'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted">Warnings that need follow-up checks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card ops-alert-card">
                <div class="card-header" style="background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);">Resolved Alerts</div>
                <div class="card-body">
                    <h3>{{ $summary['resolved_count'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted">Resolved alerts kept for recent history</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card ops-alert-card">
        <div class="card-header" style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);">
            Active OPS Alerts
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Severity</th>
                    <th>Service</th>
                    <th>Domain</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Starts At</th>
                    <th>Last Received</th>
                    <th>Safe Next Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activeAlerts as $alert)
                    <tr>
                        <td>
                            <span class="ops-badge ops-badge-{{ $alert['severity'] ?? 'info' }}">{{ strtoupper($alert['severity'] ?? 'info') }}</span>
                        </td>
                        <td>{{ $alert['service'] ?? 'monitoring' }}</td>
                        <td>{{ $alert['domain'] ?? '-' }}</td>
                        <td><span class="ops-badge ops-badge-firing">FIRING</span></td>
                        <td>{{ $alert['summary'] ?? 'Monitoring alert' }}</td>
                        <td>{{ $alert['starts_at'] ?? '-' }}</td>
                        <td>{{ $alert['last_received_at'] ?? '-' }}</td>
                        <td>{{ $alert['safe_next_action'] ?? 'Check monitoring evidence safely.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No active OPS alerts stored yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card ops-alert-card">
        <div class="card-header" style="background: linear-gradient(135deg, #2f855a 0%, #276749 100%);">
            Resolved OPS Alerts
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Severity</th>
                    <th>Service</th>
                    <th>Domain</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Starts At</th>
                    <th>Ends At</th>
                    <th>Last Received</th>
                </tr>
                </thead>
                <tbody>
                @forelse($resolvedAlerts as $alert)
                    <tr>
                        <td>
                            <span class="ops-badge ops-badge-{{ $alert['severity'] ?? 'info' }}">{{ strtoupper($alert['severity'] ?? 'info') }}</span>
                        </td>
                        <td>{{ $alert['service'] ?? 'monitoring' }}</td>
                        <td>{{ $alert['domain'] ?? '-' }}</td>
                        <td><span class="ops-badge ops-badge-resolved">RESOLVED</span></td>
                        <td>{{ $alert['summary'] ?? 'Monitoring alert' }}</td>
                        <td>{{ $alert['starts_at'] ?? '-' }}</td>
                        <td>{{ $alert['ends_at'] ?? '-' }}</td>
                        <td>{{ $alert['last_received_at'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No resolved OPS alerts stored yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
