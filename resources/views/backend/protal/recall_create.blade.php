@extends('backend.layouts.main')
@section('title', 'Create New Agency - Recall Balance')

@section('content')
@if ($errors->any())
    <div class="alert alert-modern alert-danger-modern alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle fs-4 me-3"></i>
            <div>
                <strong>Error!</strong> Please check the form for errors.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    :root {
        --dark-bg: #0a0c10;
        --dark-surface: #1a1e24;
        --dark-surface-2: #242830;
        --dark-border: #2f3540;
        --dark-border-hover: #404754;
        --primary-dark: #4f9eff;
        --primary-hover: #6baeff;
        --success-dark: #2ecc71;
        --danger-dark: #ff4d4d;
        --warning-dark: #f39c12;
        --text-primary: #e5e7eb;
        --text-secondary: #9ca3af;
        --text-muted: #6b7280;
        --input-bg: #1e2229;
    }

    body {
        background-color: var(--dark-bg);
        color: var(--text-primary);
    }

    .page-header {
        background: linear-gradient(145deg, #1a1f2b 0%, #0f1319 100%);
        color: var(--text-primary);
        padding: 25px 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        border: 1px solid var(--dark-border);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--primary-dark), transparent);
    }

    .page-header h4 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: var(--text-primary);
    }

    .page-header p {
        margin: 10px 0 0;
        opacity: 0.7;
        font-size: 1rem;
        color: var(--text-secondary);
    }

    .main-card {
        background: var(--dark-surface);
        border-radius: 24px;
        border: 1px solid var(--dark-border);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        overflow: visible;
        transition: box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .main-card:hover {
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        border-color: var(--dark-border-hover);
    }

    .card-header-custom {
        background: linear-gradient(90deg, #1e2632 0%, #151b24 100%);
        padding: 20px 30px;
        border-bottom: 1px solid var(--dark-border);
        border-radius: 24px 24px 0 0;
    }

    .card-header-custom h5 {
        color: var(--text-primary);
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header-custom i {
        font-size: 1.5rem;
        color: var(--primary-dark);
    }

    .card-body-custom {
        padding: 40px;
        background: var(--dark-surface);
        border-radius: 0 0 24px 24px;
    }

    .form-section {
        background: var(--dark-surface-2);
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid var(--dark-border);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: border-color 0.25s ease;
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--dark-border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        color: var(--primary-dark);
    }

    .form-group-modern {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group-modern label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-group-modern label i {
        color: var(--primary-dark);
        font-size: 1rem;
    }

    .form-control-modern {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--dark-border);
        border-radius: 14px;
        font-size: 1rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
        background: var(--input-bg);
        color: var(--text-primary);
    }

    .form-control-modern:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 4px rgba(79, 158, 255, 0.15);
        outline: none;
        background: var(--input-bg);
        color: var(--text-primary);
    }

    .form-control-modern[readonly] {
        background-color: #1a1e26;
        cursor: not-allowed;
        border-color: var(--dark-border);
        color: var(--text-muted);
    }

    .form-control-modern::placeholder {
        color: var(--text-muted);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 52px;
        border: 2px solid var(--dark-border);
        border-radius: 14px;
        padding: 10px 18px;
        background: var(--input-bg);
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        color: var(--text-primary);
        padding-left: 0;
        padding-right: 25px;
        font-size: 0.95rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--text-muted);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px;
        right: 15px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: var(--text-secondary) transparent transparent transparent;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent var(--text-secondary) transparent;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 4px rgba(79, 158, 255, 0.15);
        background: var(--input-bg);
    }

    .select2-selection__clear {
        color: var(--text-muted) !important;
        font-size: 1.2rem !important;
        margin-right: 18px !important;
    }

    .select2-selection__clear:hover {
        color: var(--danger-dark) !important;
    }

    .select2-dropdown {
        background: var(--dark-surface-2);
        border: 2px solid var(--dark-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        z-index: 10000;
        box-sizing: border-box;
    }

    .select2-search--dropdown {
        padding: 12px;
        background: var(--dark-surface-2);
        border-bottom: 1px solid var(--dark-border);
    }

    .select2-search--dropdown .select2-search__field {
        background: var(--input-bg);
        border: 2px solid var(--dark-border);
        border-radius: 10px;
        color: var(--text-primary);
        padding: 10px 12px;
        width: 100%;
        font-size: 0.95rem;
        outline: none;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 3px rgba(79, 158, 255, 0.15);
    }

    .select2-results {
        background: var(--dark-surface-2);
    }

    .select2-results__options {
        max-height: 300px !important;
        overflow-y: auto;
        padding: 8px 0;
    }

    .select2-results__option {
        padding: 10px 15px;
        color: var(--text-secondary);
        font-size: 0.95rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.2s ease, color 0.2s ease;
    }

    .select2-results__option:last-child {
        border-bottom: none;
    }

    .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, rgba(79, 158, 255, 0.22) 0%, rgba(79, 158, 255, 0.1) 100%);
        color: var(--text-primary);
    }

    .select2-results__option[aria-selected=true] {
        background: rgba(79, 158, 255, 0.3);
        color: var(--primary-dark);
        font-weight: 600;
    }

    .select2-results__message,
    .select2-results__option.loading-results {
        padding: 15px;
        text-align: center;
        color: var(--text-muted);
    }

    .user-option {
        padding: 8px 12px;
        color: var(--text-primary);
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #3a7bd5 100%);
        color: white;
        border: none;
        padding: 16px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        box-shadow: 0 10px 20px rgba(79, 158, 255, 0.2);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(79, 158, 255, 0.3);
        color: white;
        background: linear-gradient(135deg, #5aa9ff 0%, #4a8cda 100%);
    }

    .btn-submit i {
        font-size: 1.2rem;
    }

    .btn-reset {
        background: transparent;
        border: 2px solid var(--dark-border);
        color: var(--text-secondary);
        padding: 16px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.25s ease, color 0.25s ease, background 0.25s ease;
    }

    .btn-reset:hover {
        border-color: var(--primary-dark);
        color: var(--primary-dark);
        background: rgba(79, 158, 255, 0.1);
    }

    .balance-badge {
        background: linear-gradient(135deg, #1e2a3a 0%, #151f2b 100%);
        color: var(--primary-dark);
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        border: 2px solid var(--primary-dark);
        box-shadow: 0 0 20px rgba(79, 158, 255, 0.2);
    }

    .balance-badge i {
        font-size: 1.3rem;
        color: var(--primary-dark);
    }

    .info-text {
        background: rgba(79, 158, 255, 0.1);
        color: var(--text-secondary);
        padding: 10px 15px;
        border-radius: 12px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        border: 1px solid rgba(79, 158, 255, 0.2);
    }

    .info-text i {
        color: var(--primary-dark);
    }

    .row-custom {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }

    .col-custom {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
    }

    .alert-modern {
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        border: none;
        position: relative;
    }

    .alert-danger-modern {
        background: linear-gradient(135deg, rgba(255, 77, 77, 0.15) 0%, rgba(255, 77, 77, 0.05) 100%);
        border-left: 4px solid var(--danger-dark);
        color: #ffb3b3;
    }

    .alert-danger-modern ul {
        color: #ffb3b3;
    }

    .alert-danger-modern strong {
        color: var(--danger-dark);
    }

    .text-danger {
        color: #ff6b6b !important;
    }

    .summary-section {
        background: linear-gradient(145deg, #1a212b 0%, #12171f 100%);
        border: 2px solid var(--dark-border);
        border-radius: 20px;
        padding: 20px 25px;
    }

    .summary-label {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .summary-value {
        color: var(--primary-dark);
        font-size: 1.3rem;
        font-weight: 700;
    }

    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    ::-webkit-scrollbar-track {
        background: var(--dark-surface);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--dark-border);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--dark-border-hover);
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }

        .page-header .d-flex {
            align-items: flex-start !important;
            gap: 15px;
            flex-direction: column;
        }

        .page-header h4 {
            font-size: 1.35rem;
        }

        .balance-badge {
            font-size: 1rem;
            padding: 8px 18px;
        }

        .col-custom {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .card-body-custom {
            padding: 20px;
        }

        .form-section {
            padding: 18px;
        }

        .btn-submit,
        .btn-reset {
            width: 100%;
            justify-content: center;
            margin-left: 0 !important;
            margin-bottom: 12px;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="fas fa-building me-2" style="color: var(--primary-dark);"></i>Create New Agency - Recall Balance</h4>
                <p>Manage recall balance for agencies in dark mode</p>
            </div>
            <div class="balance-badge">
                <i class="fas fa-wallet"></i>
                <span>System: $0.00</span>
            </div>
        </div>
    </div>

    <div class="main-card">
        <div class="card-header-custom">
            <h5>
                <i class="fas fa-exchange-alt"></i>
                Recall Balance Transfer Form
            </h5>
        </div>

        <div class="card-body-custom">
            <form action="{{ URL::to('protal_recall_submit') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-user-circle"></i>
                        User Information
                    </div>

                    <div class="row-custom">
                        <div class="col-custom">
                            <div class="form-group-modern">
                                <label for="user_id">
                                    <i class="fas fa-id-card"></i>
                                    Select User <span class="text-danger">*</span>
                                </label>
                                <select name="user_id" class="form-control-modern" required id="user_id">
                                    <option value="">🔍 Search for a user...</option>
                                </select>
                                <div class="info-text">
                                    <i class="fas fa-info-circle"></i>
                                    Search by user ID or name (minimum 2 characters)
                                </div>
                            </div>
                        </div>

                        <div class="col-custom">
                            <div class="form-group-modern">
                                <label for="balance_display">
                                    <i class="fas fa-coins"></i>
                                    Current Balance
                                </label>
                                <div class="position-relative">
                                    <input type="number" name="balance_display" class="form-control-modern"
                                           placeholder="0.00" value="0" readonly id="balance_display">
                                    <small class="text-muted d-block mt-2">User's available balance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-hand-holding-usd"></i>
                        Recall Details
                    </div>

                    <div class="row-custom">
                        <div class="col-custom">
                            <div class="form-group-modern">
                                <label for="recall_amount">
                                    <i class="fas fa-calculator"></i>
                                    Recall Amount <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="amount" class="form-control-modern"
                                       placeholder="Enter amount to recall" value="0"
                                       id="recall_amount" min="0" step="0.01" required>
                                <div class="info-text">
                                    <i class="fas fa-lightbulb"></i>
                                    Enter the amount you want to recall
                                </div>
                            </div>
                        </div>

                        <div class="col-custom">
                            <div class="form-group-modern">
                                <label for="protal_id">
                                    <i class="fas fa-globe"></i>
                                    Select Portal <span class="text-danger">*</span>
                                </label>
                                <select name="protal_id" class="form-control-modern select_agency_id" required id="protal_id">
                                    <option value="">Choose a portal...</option>
                                    @foreach($protals as $protal)
                                        <option value="{{ $protal->id }}">
                                            {{ $protal->id }} — {{ $protal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="summary-label d-block">Transaction Summary</span>
                            <small class="text-muted">Please review before submitting</small>
                        </div>
                        <div class="text-end">
                            <span class="summary-label d-block">Amount</span>
                            <span id="summaryAmount" class="summary-value">$0.00</span>
                        </div>
                    </div>
                    <hr class="my-3" style="border-color: var(--dark-border);">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Transaction Type:</span>
                        <span class="text-primary">Recall Balance</span>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Submit Recall Request
                    </button>
                    <button type="reset" class="btn-reset ms-3">
                        <i class="fas fa-undo-alt me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#user_id').select2({
        placeholder: '🔍 Search user by ID or name...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route("users.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || ''
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(user) {
                        return {
                            id: user.id,
                            text: user.name,
                            name: user.name,
                            balance: parseFloat(user.balance || 0),
                            displayText: user.id + ' — ' + user.name
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: formatUserOption,
        templateSelection: formatUserSelection,
        language: {
            searching: function() {
                return 'Searching...';
            },
            noResults: function() {
                return 'No users found';
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    });

    function formatUserOption(user) {
        if (user.loading) {
            return $('<div class="user-option text-muted py-2"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>');
        }

        var balance = parseFloat(user.balance || 0);
        var $container = $('<div>', {
            class: 'user-option d-flex align-items-center justify-content-between',
            css: {
                padding: '8px 12px',
                cursor: 'pointer',
                width: '100%',
                gap: '10px'
            }
        });

        var $infoDiv = $('<div>', {
            css: {
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                minWidth: '0'
            }
        });

        $('<strong>', {
            class: 'text-primary',
            text: user.id
        }).appendTo($infoDiv);

        $('<span>', {
            class: 'text-secondary ms-2',
            text: '— ' + (user.name || '').substring(0, 30)
        }).appendTo($infoDiv);

        var $balanceSpan = $('<span>', {
            class: 'badge',
            css: {
                background: 'rgba(79, 158, 255, 0.2)',
                color: '#4f9eff',
                padding: '4px 8px',
                borderRadius: '6px',
                fontSize: '0.85rem',
                flexShrink: '0'
            },
            html: '<i class="fas fa-coins me-1"></i>$' + balance.toFixed(2)
        });

        $container.append($infoDiv, $balanceSpan);
        return $container;
    }

    function formatUserSelection(user) {
        if (user.displayText) {
            return user.displayText;
        }

        if (user.id && user.name) {
            return user.id + ' — ' + user.name;
        }

        if (user.text) {
            return user.text;
        }

        return '🔍 Select user';
    }

    $('#user_id').on('select2:select', function(e) {
        var data = e.params.data;

        if (data.balance !== undefined) {
            $('#balance_display').val(parseFloat(data.balance || 0).toFixed(2));
            return;
        }

        fetchUserBalance(data.id);
    });

    $('#user_id').on('select2:clear', function() {
        $('#balance_display').val(0);
    });

    function fetchUserBalance(userId) {
        $.ajax({
            url: '{{ route("users.search") }}',
            data: { search: userId },
            success: function(response) {
                if (response.length > 0) {
                    $('#balance_display').val(parseFloat(response[0].balance || 0).toFixed(2));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching user balance:', error);
            }
        });
    }

    $('#recall_amount').on('input', function() {
        var amount = parseFloat($(this).val()) || 0;
        $('#summaryAmount').text('$' + amount.toFixed(2));
        $('#summaryAmount').css('color', amount > 0 ? '#4f9eff' : '#ff4d4d');
    });

    $('form').on('submit', function(e) {
        var recallAmount = parseFloat($('#recall_amount').val()) || 0;
        var userBalance = parseFloat($('#balance_display').val()) || 0;
        var userId = $('#user_id').val();
        var portalId = $('#protal_id').val();

        if (!userId) {
            e.preventDefault();
            showDarkAlert('Error!', 'Please select a user');
            $('#user_id').select2('open');
            return false;
        }

        if (!portalId) {
            e.preventDefault();
            showDarkAlert('Error!', 'Please select a portal');
            $('#protal_id').focus();
            return false;
        }

        if (recallAmount <= 0) {
            e.preventDefault();
            showDarkAlert('Invalid Amount!', 'Please enter a valid amount greater than 0');
            $('#recall_amount').focus();
            return false;
        }

        if (recallAmount > userBalance) {
            e.preventDefault();
            showDarkAlert(
                'Insufficient Balance!',
                'Recall amount ($' + recallAmount.toFixed(2) + ') exceeds user balance ($' + userBalance.toFixed(2) + ')'
            );
            return false;
        }

        if (!confirm('Are you sure you want to recall $' + recallAmount.toFixed(2) + ' from this user?')) {
            e.preventDefault();
            return false;
        }
    });

    function showDarkAlert(title, message) {
        var $alert = $('<div>', {
            class: 'alert alert-modern alert-danger-modern alert-dismissible fade show',
            role: 'alert'
        });

        var $content = $('<div>', { class: 'd-flex align-items-center' });
        $('<i>', {
            class: 'fas fa-exclamation-circle fs-4 me-3',
            css: { color: '#ff4d4d' }
        }).appendTo($content);

        var $textWrap = $('<div>');
        $('<strong>', {
            css: { color: '#ff4d4d' },
            text: title
        }).appendTo($textWrap);
        $('<p>', {
            class: 'mb-0 text-secondary',
            text: message
        }).appendTo($textWrap);

        $textWrap.appendTo($content);
        $content.appendTo($alert);

        $('<button>', {
            type: 'button',
            class: 'btn-close btn-close-white',
            'data-bs-dismiss': 'alert',
            'aria-label': 'Close'
        }).appendTo($alert);

        $('.alert-modern').remove();
        $('.container-fluid').prepend($alert);

        $('html, body').stop(true).animate({
            scrollTop: 0
        }, 350);

        setTimeout(function() {
            $('.alert-modern').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    $('.form-section').hover(
        function() {
            $(this).css('border-color', 'var(--primary-dark)');
        },
        function() {
            $(this).css('border-color', 'var(--dark-border)');
        }
    );

    $('button[type="reset"]').on('click', function(e) {
        e.preventDefault();
        $('#user_id').val(null).trigger('change');
        $('#recall_amount').val(0);
        $('#balance_display').val(0);
        $('#protal_id').val('');
        $('#summaryAmount').text('$0.00').css('color', '#ff4d4d');
    });
});
</script>
@endsection