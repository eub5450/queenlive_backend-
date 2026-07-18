<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>BP Live Agora System - Account Manager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --primary-light: #60A5FA;
            --secondary: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --dark: #1F2937;
            --light: #F9FAFB;
            --gray: #6B7280;
            --border: #E5E7EB;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.05);
            --radius: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f5ff;
            color: var(--dark);
            line-height: 1.5;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 16px;
        }

        @media (min-width: 768px) {
            .container {
                padding: 24px;
            }
        }

        /* Header */
        .header {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .header {
                flex-direction: row;
                align-items: center;
                margin-bottom: 32px;
                gap: 20px;
            }
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .header-left h1 {
                font-size: 2rem;
            }
        }

        .header-left p {
            color: var(--gray);
            font-size: 0.85rem;
        }

        @media (min-width: 768px) {
            .header-left p {
                font-size: 0.95rem;
            }
        }

        .header-right {
            display: flex;
            gap: 12px;
            align-items: center;
            width: 100%;
        }

        @media (min-width: 768px) {
            .header-right {
                width: auto;
            }
        }

        .btn {
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            width: 100%;
        }

        @media (min-width: 768px) {
            .btn {
                padding: 12px 24px;
                font-size: 0.95rem;
                width: auto;
            }
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-success {
            background: var(--secondary);
            color: white;
        }

        .btn-success:hover {
            background: #0E9F6E;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
        }

        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(6, 1fr);
                gap: 20px;
            }
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }

        @media (min-width: 768px) {
            .stat-card {
                padding: 24px;
                border-radius: var(--radius);
            }
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .stat-info h3 {
            font-size: 0.7rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 4px;
        }

        @media (min-width: 768px) {
            .stat-info h3 {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }
        }

        .stat-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        @media (min-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        @media (min-width: 768px) {
            .stat-icon {
                width: 56px;
                height: 56px;
                border-radius: 16px;
                font-size: 28px;
            }
        }

        .stat-icon.blue { background: #EFF6FF; color: var(--primary); }
        .stat-icon.green { background: #E1F9F0; color: var(--secondary); }
        .stat-icon.orange { background: #FEF3C7; color: var(--warning); }
        .stat-icon.red { background: #FEE2E2; color: var(--danger); }
        .stat-icon.purple { background: #F3E8FF; color: #8B5CF6; }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 768px) {
            .form-card {
                border-radius: var(--radius);
                padding: 32px;
                margin-bottom: 32px;
            }
        }

        .form-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (min-width: 768px) {
            .form-title {
                font-size: 1.5rem;
                margin-bottom: 24px;
                gap: 12px;
            }
        }

        .form-title i {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, #8B5CF6 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        @media (min-width: 768px) {
            .form-title i {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                font-size: 20px;
            }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
        }

        @media (min-width: 768px) {
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: var(--light);
        }

        @media (min-width: 768px) {
            .form-input {
                padding: 14px 18px;
                border-radius: 12px;
                font-size: 0.95rem;
            }
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #8B5CF6 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .btn-submit {
                padding: 16px 40px;
                border-radius: 12px;
                font-size: 1rem;
                margin-top: 16px;
                width: auto;
            }
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 768px) {
            .table-container {
                border-radius: var(--radius);
                padding: 24px;
            }
        }

        .table-header {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .table-header {
                flex-direction: row;
                align-items: center;
                margin-bottom: 24px;
                gap: 16px;
            }
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (min-width: 768px) {
            .table-title {
                font-size: 1.3rem;
                gap: 12px;
            }
        }

        .badge-count {
            background: var(--primary-light);
            color: white;
            padding: 3px 8px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        @media (min-width: 768px) {
            .badge-count {
                padding: 4px 12px;
                font-size: 0.8rem;
            }
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .export-buttons {
                width: auto;
            }
        }

        .export-btn {
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex: 1;
        }

        @media (min-width: 768px) {
            .export-btn {
                padding: 10px 18px;
                font-size: 0.9rem;
                flex: none;
            }
        }

        .export-pdf { background: #FEE2E2; color: var(--danger); }
        .export-excel { background: #E1F9F0; color: var(--secondary); }

        .export-btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.95);
        }

        /* Mobile Card View */
        .mobile-card-view {
            display: block;
        }

        @media (min-width: 768px) {
            .mobile-card-view {
                display: none;
            }
        }

        .account-card {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--border);
        }

        .card-serial {
            font-weight: 700;
            color: var(--primary);
            background: #EFF6FF;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .card-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
            gap: 4px;
        }

        .card-row {
            display: flex;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .card-label {
            width: 100px;
            font-weight: 600;
            color: var(--gray);
        }

        .card-value {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .card-action-btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            justify-content: center;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
            display: none;
        }

        @media (min-width: 768px) {
            .table-responsive {
                display: block;
            }
        }

        .responsive-table {
            width: 100%;
            border-collapse: collapse;
        }

        .responsive-table th {
            background: #F8FAFC;
            padding: 16px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray);
            text-align: left;
            border-bottom: 2px solid var(--border);
        }

        .responsive-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            color: var(--dark);
        }

        .responsive-table tbody tr:hover {
            background: #F8FAFC;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
            gap: 4px;
        }

        @media (min-width: 768px) {
            .status-badge {
                padding: 6px 14px;
                font-size: 0.85rem;
                gap: 6px;
            }
        }

        .status-running { background: #E1F9F0; color: var(--secondary); }
        .status-new { background: #EFF6FF; color: var(--primary); }
        .status-expired { background: #FEE2E2; color: var(--danger); }

        /* Action Buttons */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media (min-width: 768px) {
            .action-btn {
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 0.85rem;
                gap: 6px;
            }
        }

        .btn-active {
            background: var(--secondary);
            color: white;
        }

        .btn-disabled {
            background: #F3F4F6;
            color: var(--gray);
            cursor: not-allowed;
        }

        .btn-copy {
            background: #EFF6FF;
            color: var(--primary);
            padding: 6px 10px;
        }

        @media (min-width: 768px) {
            .btn-copy {
                padding: 8px 12px;
            }
        }

        .btn-copy:hover {
            background: #DBEAFE;
        }

        /* Copy icon */
        .fa-copy {
            cursor: pointer;
            padding: 6px;
            background: #F3F4F6;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .fa-copy {
                padding: 8px;
                border-radius: 8px;
                font-size: 1rem;
            }
        }

        .fa-copy:hover {
            background: var(--primary);
            color: white !important;
        }

        /* Truncate */
        .truncate-text {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .truncate-text {
                max-width: 100%;
                white-space: normal;
                word-break: break-word;
            }
        }

        /* No data */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        @media (min-width: 768px) {
            .no-data {
                padding: 60px;
            }
        }

        .no-data i {
            font-size: 36px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        @media (min-width: 768px) {
            .no-data i {
                font-size: 48px;
                margin-bottom: 16px;
            }
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 16px;
            color: var(--gray);
            font-size: 0.85rem;
        }

        @media (min-width: 768px) {
            .footer {
                margin-top: 40px;
                padding: 20px;
                font-size: 0.95rem;
            }
        }

        /* Utility classes */
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        @media (max-width: 768px) {
            .text-truncate {
                max-width: 100%;
                white-space: normal;
            }
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .w-100 { width: 100%; }

        /* SweetAlert2 mobile adjustments */
        @media (max-width: 576px) {
            .swal2-popup {
                font-size: 0.8rem !important;
                padding: 1rem !important;
            }
            .swal2-title {
                font-size: 1.2rem !important;
            }
            .swal2-html-container {
                font-size: 0.9rem !important;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>BP Live Agora System</h1>
                <p>Manage your Agora accounts securely</p>
            </div>
        </div>

        @php
            // Count statistics
            $totalAccounts = count($data);
            $runningAccounts = $data->where('Status', 1)->where('other_use', 0)->count();
            $newAccounts = $data->where('Status', 0)->where('other_use', 0)->count();
            $expiredAccounts = $data->where('other_use', '!=', 0)->count() + $data->where('Status', '!=', 1)->where('Status', '!=', 0)->count();
            $agoraAccounts = $data->where('type', 1)->count();
            $gmailAccounts = $data->where('type', 0)->count();
        @endphp

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total</h3>
                    <div class="stat-number">{{ $totalAccounts }}</div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Running</h3>
                    <div class="stat-number">{{ $runningAccounts }}</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>New</h3>
                    <div class="stat-number">{{ $newAccounts }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Expired</h3>
                    <div class="stat-number">{{ $expiredAccounts }}</div>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Agora</h3>
                    <div class="stat-number">{{ $agoraAccounts }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-video"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Gmail</h3>
                    <div class="stat-number">{{ $gmailAccounts }}</div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>

        <!-- New Account Form -->
        <div class="form-card">
            <div class="form-title">
                <i class="fas fa-plus-circle"></i>
                Create New Account
            </div>
            <form action="{{URL::to('fontend-agora_account_store')}}" method="post" enctype="multipart/form-data" id="createAccountForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Agora AppId *</label>
                        <input type="text" name="appId" class="form-input" placeholder="Enter AppId" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">AppCertificate *</label>
                        <input type="text" name="appCertificate" class="form-input" placeholder="Enter AppCertificate" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Email *</label>
                        <input type="email" name="AgoraEmail" class="form-input" value="@gmail.com" placeholder="Enter Email" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Password *</label>
                        <input type="text" name="AgoraEmailPassword" value="Ago5248@#" class="form-input" placeholder="Enter Password" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Type *</label>
                        <select name="account_type" class="form-input" required>
                            <option value="">Select Type</option>
                            <option selected value="1">Agora Account</option>
                            <option value="0">Gmail Login</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-plus-circle"></i> Create Account
                </button>
            </form>
        </div>

        <!-- Accounts Table - Desktop View -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list" style="color: var(--primary);"></i>
                    Account List
                    <span class="badge-count">{{ $totalAccounts }} Total</span>
                </div>
                
                <div class="export-buttons">
                    <button onclick="exportAsPDF()" class="export-btn export-pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button onclick="exportAsExcel()" class="export-btn export-excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
            
            <!-- Desktop Table View -->
            <div class="table-responsive">
                <table class="responsive-table" id="salaryTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>AppId / Certificate</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i=0; @endphp
                        @foreach($data as $row)
                        <tr>
                            <td data-label="#">{{ ++$i }}</td>
                            <td data-label="AppId/Certificate">
                                <div class="truncate-text" style="max-width: 160px;" title="AppId: {{$row->appId}}">
                                   {{$row->appId}}<br>
                                     {{$row->appCertificate}}
                                </div>
                            </td>
                            
                            @if($row->type == 0)
                                <td data-label="Email">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="text-truncate">{{$row->AgoraEmail}}</span>
                                        <i class="fa fa-copy" style="color: var(--gray);" onclick="copyEmail('{{$row->AgoraEmail}}', {{$i}})" title="Copy Email"></i>
                                    </div>
                                </td>
                                <td data-label="Password">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="text-truncate">{{$row->AgoraEmailPassword}}</span>
                                        <i class="fa fa-copy" style="color: var(--gray);" onclick="copyText('{{$row->AgoraEmailPassword}}', {{$i}})" title="Copy Password"></i>
                                    </div>
                                </td>
                            @else
                                <td data-label="Email" style="color: var(--primary);">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="text-truncate">{{$row->AgoraEmail}}</span>
                                        <i class="fa fa-copy" style="color: var(--gray);" onclick="copyEmail('{{$row->AgoraEmail}}', {{$i}})" title="Copy Email"></i>
                                    </div>
                                </td>
                                <td data-label="Password">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span class="text-truncate">{{$row->AgoraEmailPassword}}</span>
                                        <i class="fa fa-copy" style="color: var(--gray);" onclick="copyText('{{$row->AgoraEmailPassword}}', {{$i}})" title="Copy Password"></i>
                                        
                                        @if($row->other_use == 0)
                                            <button onclick="copyAndOpen('{{$row->main_email}}', '{{$row->AgoraEmailPassword}}')" class="action-btn btn-active" style="padding: 4px 8px;">
                                                <i class="fas fa-sign-in-alt"></i> Login
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                            
                            <td data-label="Type">
                                @if($row->type == 1)
                                    <span class="status-badge" style="background: #EFF6FF; color: var(--primary);">
                                        <i class="fas fa-video"></i> Agora
                                    </span>
                                @else
                                    <span class="status-badge" style="background: #FEF3C7; color: var(--warning);">
                                        <i class="fas fa-envelope"></i> Gmail
                                    </span>
                                @endif
                            </td>
                            
                            <td data-label="Status">
                                @if($row->other_use == 0)
                                    @if($row->Status == 1)
                                        <span class="status-badge status-running">
                                            <i class="fas fa-check-circle"></i> Running
                                        </span>
                                    @elseif($row->Status == 0)
                                        <span class="status-badge status-new">
                                            <i class="fas fa-clock"></i> New
                                        </span>
                                    @else
                                        <span class="status-badge status-expired">
                                            <i class="fas fa-times-circle"></i> Expired
                                        </span>
                                    @endif
                                @else
                                    <span class="status-badge status-expired">
                                        <i class="fas fa-times-circle"></i> Expired
                                    </span>
                                @endif
                            </td>
                            
                            <td data-label="Actions">
                                <div class="action-group">
                                    @if($row->other_use == 0)    
                                        @if($row->Status == 1)
                                            <span class="action-btn btn-disabled">
                                                <i class="fas fa-check"></i> Running
                                            </span>
                                        @elseif($row->Status == 0)
                                            <a href="{{URL::to('fontend-agora_account_active/'.$row->id)}}" class="action-btn btn-active">
                                                <i class="fas fa-play"></i> Activate
                                            </a>
                                        @else
                                            <span class="action-btn btn-disabled">
                                                <i class="fas fa-ban"></i> Expired
                                            </span>
                                        @endif
                                    @else 
                                        <span class="action-btn btn-disabled">
                                            <i class="fas fa-ban"></i> Expired
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                @php $j=0; @endphp
                @forelse($data as $row)
                @php $j++; @endphp
                <div class="account-card">
                    <div class="card-header">
                        <span class="card-serial">#{{ $j }}</span>
                        @if($row->other_use == 0)
                            @if($row->Status == 1)
                                <span class="card-status status-running">
                                    <i class="fas fa-check-circle"></i> Running
                                </span>
                            @elseif($row->Status == 0)
                                <span class="card-status status-new">
                                    <i class="fas fa-clock"></i> New
                                </span>
                            @else
                                <span class="card-status status-expired">
                                    <i class="fas fa-times-circle"></i> Expired
                                </span>
                            @endif
                        @else
                            <span class="card-status status-expired">
                                <i class="fas fa-times-circle"></i> Expired
                            </span>
                        @endif
                    </div>
                    
                    <div class="card-row">
                        
                        <div class="card-value">
                            <span class="text-truncate">{{ $row->appId }}</span>
                            <i class="fa fa-copy" style="color: var(--gray);" onclick="copyText('{{$row->appId}}', {{$j}})" title="Copy AppId"></i>
                        </div>
                    </div>
                    
                    <div class="card-row">
                        
                        <div class="card-value">
                            <span class="text-truncate">{{ $row->appCertificate }}</span>
                            <i class="fa fa-copy" style="color: var(--gray);" onclick="copyText('{{$row->appCertificate}}', {{$j}})" title="Copy Certificate"></i>
                        </div>
                    </div>
                    
                    <div class="card-row">
                        <div class="card-label">Email:</div>
                        <div class="card-value">
                            <span class="text-truncate">{{ $row->AgoraEmail }}</span>
                            <i class="fa fa-copy" style="color: var(--gray);" onclick="copyEmail('{{$row->AgoraEmail}}', {{$j}})" title="Copy Email"></i>
                        </div>
                    </div>
                    
                    <div class="card-row">
                        <div class="card-label">Password:</div>
                        <div class="card-value">
                            <span class="text-truncate">{{ $row->AgoraEmailPassword }}</span>
                            <i class="fa fa-copy" style="color: var(--gray);" onclick="copyText('{{$row->AgoraEmailPassword}}', {{$j}})" title="Copy Password"></i>
                        </div>
                    </div>
                    
                    <div class="card-row">
                        <div class="card-label">Type:</div>
                        <div class="card-value">
                            @if($row->type == 1)
                                <span class="status-badge" style="background: #EFF6FF; color: var(--primary);">
                                    <i class="fas fa-video"></i> Agora
                                </span>
                            @else
                                <span class="status-badge" style="background: #FEF3C7; color: var(--warning);">
                                    <i class="fas fa-envelope"></i> Gmail
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        @if($row->other_use == 0 && $row->type == 1)
                            <button onclick="copyAndOpen('{{$row->main_email}}', '{{$row->AgoraEmailPassword}}')" class="card-action-btn btn-active">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        @endif
                        
                        @if($row->other_use == 0)    
                            @if($row->Status == 0)
                                <a href="{{URL::to('fontend-agora_account_active/'.$row->id)}}" class="card-action-btn btn-active">
                                    <i class="fas fa-play"></i> Activate
                                </a>
                            @endif
                        @endif
                        
                        @if($row->other_use != 0 || $row->Status != 1)
                            <span class="card-action-btn btn-disabled" style="flex: 1;">
                                <i class="fas fa-ban"></i> Disabled
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <h3>No Accounts Found</h3>
                    <p>Create your first Agora account using the form above</p>
                </div>
                @endforelse
            </div>
            
            @if(count($data) === 0)
            <div class="no-data desktop-only">
                <i class="fas fa-inbox"></i>
                <h3>No Accounts Found</h3>
                <p>Create your first Agora account using the form above</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>BP Live Agora System • {{ date('Y') }}</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Form loading with SweetAlert
        document.getElementById('createAccountForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;
            
            // Show loading alert
            Swal.fire({
                title: 'Creating Account...',
                text: 'Please wait while we create your account.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Check for success/error messages from server
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            });
        @endif

        // Export functions
        function exportAsPDF() {
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while we prepare your document.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4');
                
                doc.setFontSize(18);
                doc.text('BP Live Agora Accounts', 14, 20);
                doc.setFontSize(10);
                doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 28);
                
                doc.autoTable({
                    html: '#salaryTable',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [59, 130, 246] }
                });
                
                doc.save('agora-accounts.pdf');

                Swal.fire({
                    icon: 'success',
                    title: 'PDF Exported!',
                    text: 'Your PDF has been downloaded successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 500);
        }

        function exportAsExcel() {
            Swal.fire({
                title: 'Generating Excel...',
                text: 'Please wait while we prepare your spreadsheet.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                const table = document.getElementById("salaryTable");
                const workbook = XLSX.utils.table_to_book(table, { sheet: "Agora Accounts" });
                XLSX.writeFile(workbook, "agora-accounts.xlsx");

                Swal.fire({
                    icon: 'success',
                    title: 'Excel Exported!',
                    text: 'Your Excel file has been downloaded successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 500);
        }

        // Copy functions with SweetAlert
        function copyEmail(email, serial) {
            navigator.clipboard.writeText(email).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Copied!',
                    text: `Account #${serial}: Email has been copied to clipboard.`,
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Copy Failed',
                    text: 'Unable to copy email. Please try again.',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        function copyText(text, serial) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: `Account #${serial}: Text has been copied to clipboard.`,
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Copy Failed',
                    text: 'Unable to copy text. Please try again.',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        function copyAndOpen(email, password) {
            navigator.clipboard.writeText(email).then(() => {
                localStorage.setItem('agora_pass', password);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Email Copied!',
                    text: 'Email copied to clipboard. Redirecting to Agora login...',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                setTimeout(() => {
                    window.open("https://sso2.agora.io/en/login", "_blank");
                }, 1500);
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Copy Failed',
                    text: 'Unable to copy email. Please try again.',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        // Handle responsive behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Adjust SweetAlert2 for mobile
            if (window.innerWidth <= 576) {
                Swal.bindClickHandler();
            }
        });
    </script>
</body>
</html>