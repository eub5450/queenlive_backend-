@extends('backend.layouts.main')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Dark Theme - Main Background */
    body {
        background: #0a0c0f;
        color: #e0e0e0;
    }
    
    /* Modern Dark Card Styles */
    .ranking-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
        overflow: hidden;
        background: #1a1e24;
    }
    
    .ranking-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.8);
        transform: translateY(-3px);
    }
    
    .card-header {
        background: #252b33;
        border-bottom: 1px solid #2f3740;
        border-radius: 16px 16px 0 0 !important;
        padding: 1.5rem 2rem;
    }
    
    .card-header h5 {
        color: #ffc107;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        text-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    
    .card-body {
        background: #1a1e24;
        padding: 2rem;
    }
    
    /* Modern Navigation Pills - Dark */
    .nav-pills {
        background: #252b33;
        border-radius: 50px;
        padding: 0.5rem;
        border: 1px solid #2f3740;
    }
    
    .nav-pills .nav-link {
        border-radius: 50px;
        color: #a0a8b5;
        font-weight: 600;
        padding: 10px 25px;
        margin: 0 3px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .nav-pills .nav-link:hover {
        background: #2f3740;
        color: #ffc107;
        transform: translateY(-2px);
    }
    
    .nav-pills .nav-link.active {
        background: #ffc107;
        color: #1a1e24;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .nav-pills .nav-link i {
        margin-right: 8px;
        font-size: 1rem;
    }
    
    /* Modern Table Styles - Dark */
    .table {
        border-collapse: separate;
        border-spacing: 0 8px;
        margin-top: 10px;
    }
    
    .table thead th {
        border: none;
        background: #252b33;
        color: #ffc107;
        font-weight: 600;
        padding: 15px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #2f3740;
    }
    
    .table thead th:first-child {
        border-radius: 12px 0 0 12px;
    }
    
    .table thead th:last-child {
        border-radius: 0 12px 12px 0;
    }
    
    .table tbody tr {
        background: #252b33;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.2);
        background: #2f3740;
    }
    
    .table tbody td {
        padding: 15px;
        border: none;
        vertical-align: middle;
        font-size: 0.95rem;
        color: #e0e0e0;
        font-weight: 500;
        border-bottom: 1px solid #2f3740;
    }
    
    .table tbody td:first-child {
        border-radius: 12px 0 0 12px;
        font-weight: 700;
        color: #ffc107;
    }
    
    .table tbody td:last-child {
        border-radius: 0 12px 12px 0;
    }
    
    .table tfoot th {
        border: none;
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 15px;
        font-size: 1rem;
        border-top: 2px solid #2f3740;
    }
    
    .table tfoot th:first-child {
        border-radius: 12px 0 0 12px;
    }
    
    .table tfoot th:last-child {
        border-radius: 0 12px 12px 0;
    }
    
    /* Badge Styles - Dark */
    .badge {
        font-weight: 600;
        padding: 8px 15px;
        font-size: 0.85rem;
        border-radius: 50px;
        letter-spacing: 0.3px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
    }
    
    .badge-primary {
        background: #ffc107;
        color: #1a1e24;
    }
    
    .badge-success {
        background: #28a745;
        color: #fff;
    }
    
    .badge-warning {
        background: #ffc107;
        color: #1a1e24;
    }
    
    .badge-danger {
        background: #dc3545;
        color: #fff;
    }
    
    .badge-info {
        background: #17a2b8;
        color: #fff;
    }
    
    /* Highlight negative balance - Dark */
    .negative-balance {
        background: #dc3545 !important;
        color: white !important;
        font-weight: 700;
        border-radius: 8px;
        padding: 8px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }
    
    /* DataTables Customization - Dark */
    .dataTables_wrapper {
        color: #e0e0e0;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin: 20px 0;
        color: #e0e0e0;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #2f3740;
        border-radius: 50px;
        padding: 10px 20px;
        background: #252b33;
        color: #e0e0e0;
        transition: all 0.3s ease;
        width: 300px;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #ffc107;
        outline: none;
        box-shadow: 0 5px 20px rgba(255, 193, 7, 0.2);
        background: #2f3740;
    }
    
    .dataTables_wrapper .dataTables_filter input::placeholder {
        color: #6c757d;
        font-style: italic;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #2f3740;
        border-radius: 8px;
        padding: 5px 15px;
        background: #252b33;
        color: #e0e0e0;
        cursor: pointer;
    }
    
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #ffc107;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none;
        border-radius: 8px;
        padding: 8px 15px;
        margin: 0 3px;
        background: #252b33;
        color: #e0e0e0 !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #ffc107;
        color: #1a1e24 !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #ffc107;
        color: #1a1e24 !important;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .dataTables_wrapper .dataTables_info {
        color: #a0a8b5;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .nav-pills .nav-link {
            padding: 8px 15px;
            font-size: 12px;
        }
        
        .table thead th {
            font-size: 0.8rem;
            padding: 10px;
        }
        
        .table tbody td {
            font-size: 0.85rem;
            padding: 10px;
        }
    }
    
    /* Animation for new rows */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .table tbody tr {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    /* Add delay to each row */
    .table tbody tr:nth-child(1) { animation-delay: 0.1s; }
    .table tbody tr:nth-child(2) { animation-delay: 0.2s; }
    .table tbody tr:nth-child(3) { animation-delay: 0.3s; }
    .table tbody tr:nth-child(4) { animation-delay: 0.4s; }
    .table tbody tr:nth-child(5) { animation-delay: 0.5s; }
    .table tbody tr:nth-child(6) { animation-delay: 0.6s; }
    .table tbody tr:nth-child(7) { animation-delay: 0.7s; }
    .table tbody tr:nth-child(8) { animation-delay: 0.8s; }
    .table tbody tr:nth-child(9) { animation-delay: 0.9s; }
    .table tbody tr:nth-child(10) { animation-delay: 1s; }
    
    /* Amount styling */
    .text-right {
        font-weight: 600;
        color: #ffc107;
    }
    
    /* Scrollbar styling - Dark */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1e24;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #2f3740;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
    
    /* Table container */
    .table-responsive {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
    }
    
    /* Text colors */
    h5, h6, p {
        color: #e0e0e0;
    }
    
    .text-muted {
        color: #a0a8b5 !important;
    }
    
    /* Border colors */
    .border-bottom {
        border-bottom-color: #2f3740 !important;
    }
    
    /* Container background */
    .container-fluid {
        background: #0a0c0f;
    }
    
    /* Additional dark elements */
    .bg-light {
        background-color: #1a1e24 !important;
    }
    
    .btn-outline-secondary {
        border-color: #2f3740;
        color: #e0e0e0;
    }
    
    .btn-outline-secondary:hover {
        background: #2f3740;
        border-color: #ffc107;
        color: #ffc107;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card ranking-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line mr-2" style="color: #ffc107;"></i>
                                Ranking History
                            </h5>
                        </div>
                        <div>
                            <span class="badge badge-primary mr-2">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ date('F Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-4 justify-content-center" id="ranking-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="running-sanding-tab" data-toggle="pill" href="#running-sanding" role="tab">
                                <i class="fas fa-paper-plane mr-1"></i> Running Sanding
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="running-receiver-tab" data-toggle="pill" href="#running-receiver" role="tab">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Running Receiver
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="running-family-tab" data-toggle="pill" href="#running-family" role="tab">
                                <i class="fas fa-users mr-1"></i> Running Family
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="previous-sanding-tab" data-toggle="pill" href="#previous-sanding" role="tab">
                                <i class="fas fa-paper-plane mr-1"></i> Previous Sanding
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="previous-receiver-tab" data-toggle="pill" href="#previous-receiver" role="tab">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Previous Receiver
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="previous-family-tab" data-toggle="pill" href="#previous-family" role="tab">
                                <i class="fas fa-users mr-1"></i> Previous Family
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="ranking-tabs-content">
                        <!-- Running Sanding Tab -->
                        <div class="tab-pane fade show active" id="running-sanding" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="runningSandingTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; $totalSand_total = 0; @endphp
                                        @foreach($totalSands as $totalSand)
                                        @php
                                            $i++;
                                            $totalSand_total += $totalSand->total_sand;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalSand->id }}</td>
                                            <td>{{ $totalSand->name }}</td>
                                            <td class="text-right">{{ number_format($totalSand->total_sand) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($totalSand_total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Running Receiver Tab -->
                        <div class="tab-pane fade" id="running-receiver" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="runningReceiverTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Previous Points</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Withdraw</th>
                                            <th class="text-right">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 0;
                                            $totalRecived_total = 0;
                                            $total_withdraw = 0;
                                            $total_withdraw_due = 0;
                                            $start_date = date('Y-m') . '-01';
                                            $end_date = date('Y-m') . '-31';
                                        @endphp
                                        @foreach($totalReciveds as $totalRecived)
                                        @php
                                            $i++;
                                            $withdraw = App\Models\Withdraw::where('host_id', $totalRecived->id)
                                                ->whereDate('date', '>=', $start_date)
                                                ->whereDate('date', '<=', $end_date)
                                                ->sum('total');
                                            $user = \App\RedisCache\RedisCache::UserfindById($totalRecived->id);
                                            $balance = ($totalRecived->total_sand + $user->previous_coin) - $withdraw;
                                            
                                            $totalRecived_total += $totalRecived->total_sand;
                                            $total_withdraw += $withdraw;
                                            $total_withdraw_due += $balance;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalRecived->id }}</td>
                                            <td>{{ $totalRecived->name }}</td>
                                            <td class="text-right">{{ number_format($user->previous_coin) }}</td>
                                            <td class="text-right">{{ number_format($totalRecived->total_sand) }}</td>
                                            <td class="text-right">{{ number_format($withdraw) }}</td>
                                            <td class="text-right @if($balance < 400000) negative-balance @endif">
                                                ${{ number_format($balance) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format(array_sum(array_column($totalReciveds->toArray(), 'previous_coin'))) }}</th>
                                            <th class="text-right">{{ number_format($totalRecived_total) }}</th>
                                            <th class="text-right">{{ number_format($total_withdraw) }}</th>
                                            <th class="text-right">{{ number_format($total_withdraw_due) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Running Family Tab -->
                        <div class="tab-pane fade" id="running-family" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="runningFamilyTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; $totalfamillyRecived_total = 0; @endphp
                                        @foreach($totalfamillyReciveds as $totalfamillyRecived)
                                        @php
                                            $i++;
                                            $totalfamillyRecived_total += $totalfamillyRecived->total_sand;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalfamillyRecived->code }}</td>
                                            <td>{{ $totalfamillyRecived->name }}</td>
                                            <td class="text-right">{{ number_format($totalfamillyRecived->total_sand) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($totalfamillyRecived_total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Previous Sanding Tab -->
                        <div class="tab-pane fade" id="previous-sanding" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="previousSandingTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; $pretotalSand_total = 0; @endphp
                                        @foreach($previous_totalSands as $totalSand)
                                        @php
                                            $i++;
                                            $pretotalSand_total += $totalSand->total_sand;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalSand->id }}</td>
                                            <td>{{ $totalSand->name }}</td>
                                            <td class="text-right">{{ number_format($totalSand->total_sand) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($pretotalSand_total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Previous Receiver Tab -->
                        <div class="tab-pane fade" id="previous-receiver" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="previousReceiverTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; $pretotalRecived_total = 0; @endphp
                                        @foreach($previous_totalReciveds as $totalRecived)
                                        @php
                                            $i++;
                                            $pretotalRecived_total += $totalRecived->total_sand;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalRecived->id }}</td>
                                            <td>{{ $totalRecived->name }}</td>
                                            <td class="text-right">{{ number_format($totalRecived->total_sand) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($pretotalRecived_total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Previous Family Tab -->
                        <div class="tab-pane fade" id="previous-family" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="previousFamilyTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; $pretotalfamillyRecived_total = 0; @endphp
                                        @foreach($previous_totalfamillyReciveds as $totalfamillyRecived)
                                        @php
                                            $i++;
                                            $pretotalfamillyRecived_total += $totalfamillyRecived->total_sand;
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $totalfamillyRecived->code }}</td>
                                            <td>{{ $totalfamillyRecived->name }}</td>
                                            <td class="text-right">{{ number_format($totalfamillyRecived->total_sand) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($pretotalfamillyRecived_total) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery first, then Popper.js, then Bootstrap JS, then DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize all DataTables with modern options
    $('.datatable').each(function() {
        $(this).DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            dom: '<"top d-flex justify-content-between"f>rt<"bottom d-flex justify-content-between align-items-center"lip><"clear">',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "🔍 Search records...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [
                { targets: [3,4,5,6], className: 'text-right' }
            ],
            initComplete: function() {
                // Add animation to search input
                $('.dataTables_filter input').addClass('form-control');
            }
        });
    });

    // Handle tab switching to properly adjust DataTables
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
        
        // Add active tab animation
        $(e.target).addClass('pulse');
        setTimeout(() => {
            $(e.target).removeClass('pulse');
        }, 500);
    });
    
    // Add hover effect to table rows
    $('.table tbody tr').hover(
        function() {
            $(this).css('transform', 'translateY(-3px) scale(1.01)');
        },
        function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        }
    );
});
</script>
@endpush