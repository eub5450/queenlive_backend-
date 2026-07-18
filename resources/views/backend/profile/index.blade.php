@extends('backend.layouts.main')

@section('title')
Supplier | Employee Profile
@endsection

@push('styles')
<style>
    /* ===== DARK THEME VARIABLES ===== */
    :root {
        --dark-bg: #0a0c0f;
        --dark-card: #1a1e24;
        --dark-surface: #252b33;
        --dark-surface-2: #2f3740;
        --dark-border: #2f3740;
        --dark-text: #e0e0e0;
        --dark-text-muted: #a0a8b5;
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --primary-green: #28a745;
        --primary-warning: #ffc107;
        --primary-danger: #dc3545;
        --primary-info: #17a2b8;
    }

    /* ===== MAIN BACKGROUND ===== */
    body {
        background: var(--dark-bg);
        color: var(--dark-text);
    }
    
    .body-content {
        background: transparent;
        padding: 20px;
    }
    
    /* ===== CARD STYLES ===== */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
        overflow: hidden;
        background: var(--dark-card);
    }
    
    .card:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.8);
    }
    
    .card-header {
        background: #1a1e24;
        border-bottom: 1px solid #2f3740;
        padding: 1.2rem 1.5rem;
    }
    
    .card-header h4, .card-header h5 {
        color: #ffc107;
        font-weight: 600;
        margin: 0;
        font-size: 1.2rem;
    }
    
    .card-header h4 i, .card-header h5 i {
        margin-right: 8px;
        color: #ffc107;
    }
    
    .card-body {
        background: var(--dark-card);
        padding: 1.5rem;
    }
    
    /* ===== PROFILE CARD STYLES ===== */
    .employee-cv {
        margin-bottom: 24px;
    }
    
    .card-header.resume {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 15px;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        min-height: 180px;
    }
    
    .card-header.resume img {
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .card-header.resume img:hover {
        transform: scale(1.05);
        border-color: #ffc107;
    }
    
    .card-content {
        background: var(--dark-card);
        border: 1px solid var(--dark-border);
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        padding: 24px;
    }
    
    .card-content-member {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .card-content-member h4 {
        color: var(--dark-text);
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .card-content-member h5 {
        color: var(--primary-green);
        font-weight: 600;
    }
    
    .card-content-member p {
        color: var(--dark-text-muted);
        font-size: 16px;
    }
    
    .card-content-member p i {
        color: #ffc107;
        margin-right: 8px;
    }
    
    .card-content-languages-group {
        margin-bottom: 20px;
    }
    
    .resumecaption {
        background: #252b33;
        color: #ffc107;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        caption-side: top;
        margin-bottom: 10px;
        border-left: 4px solid #ffc107;
    }
    
    /* ===== TABLE STYLES ===== */
    .table-responsive {
        border-radius: 16px;
        overflow-x: auto;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        margin: 0;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 0;
        background: var(--dark-card);
    }
    
    .table thead th {
        background: #252b33;
        color: #ffc107;
        font-weight: 600;
        padding: 12px 10px;
        font-size: 0.85rem;
        text-transform: uppercase;
        border-bottom: 2px solid #ffc107;
        white-space: nowrap;
    }
    
    .table tbody tr {
        background: #252b33;
        transition: all 0.3s ease;
        border-bottom: 1px solid #2f3740;
    }
    
    .table tbody tr:hover {
        background: #2f3740;
    }
    
    .table tbody td {
        padding: 10px 10px;
        border: none;
        vertical-align: middle;
        font-size: 0.9rem;
        color: var(--dark-text);
        white-space: nowrap;
    }
    
    .table tfoot th {
        background: #252b33;
        color: #ffc107;
        font-weight: 700;
        padding: 12px 10px;
        border-top: 2px solid #ffc107;
    }
    
    .rating-block table {
        background: var(--dark-card);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }
    
    .rating-block caption {
        background: #252b33;
        color: #ffc107;
        padding: 10px 15px;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        margin: 0;
        border-left: 4px solid #ffc107;
    }
    
    .rating-block table tr {
        transition: all 0.3s ease;
    }
    
    .rating-block table tr:hover {
        background: rgba(255, 193, 7, 0.1);
    }
    
    .rating-block table th {
        width: 40%;
        color: var(--dark-text-muted);
        font-weight: 600;
        padding: 10px 15px;
    }
    
    .rating-block table td {
        color: var(--dark-text);
        font-weight: 500;
        padding: 10px 15px;
    }
    
    /* ===== TEXT COLOR UTILITIES ===== */
    .amount-highlight {
        color: #ffc107 !important;
        font-weight: 700 !important;
    }
    
    .balance-value {
        color: #28a745 !important;
        font-weight: 700 !important;
    }
    
    .text-success {
        color: #28a745 !important;
        font-weight: 600;
    }
    
    .text-warning {
        color: #ffc107 !important;
        font-weight: 600;
    }
    
    .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }
    
    .text-info {
        color: #17a2b8 !important;
        font-weight: 600;
    }
    
    /* ===== BADGE STYLES ===== */
    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .badge.bg-info {
        background: #17a2b8 !important;
        color: white;
    }
    
    .badge.bg-success {
        background: #28a745 !important;
        color: white;
    }
    
    .badge.bg-warning {
        background: #ffc107 !important;
        color: #1a1e24;
    }
    
    .badge.bg-danger {
        background: #dc3545 !important;
        color: white;
    }
    
    .badge.bg-secondary {
        background: #6c757d !important;
        color: white;
    }
    
    .badge.bg-primary {
        background: #007bff !important;
        color: white;
    }
    
    .badge-id {
        background: #17a2b8;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    
    /* ===== BUTTON STYLES ===== */
    .btn {
        position: relative;
        overflow: hidden;
        border-radius: 20px !important;
        font-weight: 500 !important;
        padding: 5px 15px !important;
        transition: all 0.3s ease !important;
        margin: 2px;
        border: none !important;
        font-size: 0.85rem;
    }
    
    .btn-sm {
        padding: 4px 12px !important;
        font-size: 0.8rem !important;
    }
    
    .btn-xs {
        padding: 2px 8px !important;
        font-size: 0.7rem !important;
    }
    
    .btn-success {
        background: #28a745 !important;
        color: #fff !important;
    }
    
    .btn-danger {
        background: #dc3545 !important;
        color: #fff !important;
    }
    
    .btn-info {
        background: #17a2b8 !important;
        color: #fff !important;
    }
    
    .btn-warning {
        background: #ffc107 !important;
        color: #212529 !important;
    }
    
    .btn-primary {
        background: #007bff !important;
        color: #fff !important;
    }
    
    .btn-secondary {
        background: #6c757d !important;
        color: #fff !important;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        filter: brightness(1.1);
    }
    
    /* ===== POWER CONTROLS - 6 COLUMN GRID ===== */
    .power-controls-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        padding: 5px;
    }
    
    @media (max-width: 1200px) {
        .power-controls-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .power-controls-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .power-controls-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .power-control-item {
        display: flex;
        flex-direction: column;
        background: #252b33;
        border-radius: 8px;
        padding: 8px;
        border: 1px solid #2f3740;
        transition: all 0.3s ease;
    }
    
    .power-control-item:hover {
        border-color: #ffc107;
    }
    
    .power-control-label {
        color: #a0a8b5;
        font-size: 11px;
        margin-bottom: 5px;
    }
    
    .power-control-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
    }
    
    /* ===== VIP BUTTON GRID ===== */
    .vip-button-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .vip-btn-1 { background: #FFD700 !important; color: #000 !important; }
    .vip-btn-2 { background: #C0C0C0 !important; color: #000 !important; }
    .vip-btn-3 { background: #CD7F32 !important; color: #fff !important; }
    .vip-btn-4 { background: #4B0082 !important; color: #fff !important; }
    .vip-btn-5 { background: #FF1493 !important; color: #fff !important; }
    .vip-btn-6 { background: #00CED1 !important; color: #000 !important; }
    .vip-btn-7 { background: #8B4513 !important; color: #fff !important; }
    
    /* ===== ENTRY FRAME - COMPACT GRID ===== */
    .entry-frame-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 8px;
        margin-top: 5px;
    }
    
    .entry-frame-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        background: #252b33;
        border-radius: 8px;
        padding: 6px;
        transition: all 0.3s ease;
        border: 1px solid #2f3740;
    }
    
    .entry-frame-item:hover {
        border-color: #ffc107;
    }
    
    .entry-frame-item img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 4px;
    }
    
    /* ===== SPECIAL FRAME BUTTONS ===== */
    .special-frame-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .special-frame-admin {
        background: #ff6b6b !important;
        color: white !important;
    }
    
    .special-frame-official {
        background: #4834d4 !important;
        color: white !important;
    }
    
    /* ===== TAB STYLES ===== */
    .nav-pills {
        background: #252b33;
        padding: 5px;
        border-radius: 30px;
        display: inline-flex;
        margin-bottom: 20px;
        flex-wrap: wrap;
        border: 1px solid #2f3740;
    }
    
    .nav-pills .nav-link {
        color: #a0a8b5;
        font-weight: 500;
        padding: 6px 15px;
        border-radius: 30px;
        transition: all 0.3s ease;
        font-size: 0.85rem;
    }
    
    .nav-pills .nav-link.active {
        background: #ffc107;
        color: #1a1e24;
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        color: #ffc107;
    }
    
    /* ===== IMAGE GALLERY ===== */
    .image-gallery {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }
    
    .image-gallery .col-xl-4, .image-gallery .col-sm-4 {
        padding: 10px;
    }
    
    .image-gallery .card {
        background: #252b33;
        border: 1px solid #2f3740;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 0;
    }
    
    .image-gallery .card-body {
        padding: 15px;
        text-align: center;
    }
    
    .image-gallery img {
        border-radius: 8px;
        max-width: 100%;
        height: 120px;
        object-fit: cover;
        border: 1px solid #2f3740;
    }
    
    /* ===== MODAL STYLES ===== */
    .modal-content {
        background: var(--dark-card);
        border: 1px solid var(--dark-border);
        border-radius: 16px !important;
    }
    
    .modal-header {
        background: #252b33;
        border-bottom: 1px solid #2f3740;
        padding: 1rem 1.5rem;
    }
    
    .modal-title {
        color: #ffc107;
        font-weight: 600;
    }
    
    .modal-header .close {
        color: #ffc107;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        border-top: 1px solid #2f3740;
        padding: 1rem 1.5rem;
    }
    
    .form-control {
        background: #252b33;
        border: 1px solid #2f3740;
        border-radius: 20px;
        padding: 8px 15px;
        color: var(--dark-text);
    }
    
    .form-control:focus {
        border-color: #ffc107;
        outline: none;
        background: #2f3740;
    }
    
    /* ===== DATA TABLES CUSTOMIZATION ===== */
    .dataTables_wrapper {
        color: var(--dark-text);
        padding: 10px 0;
    }
    
    .dataTables_filter {
        margin-bottom: 15px;
    }
    
    .dataTables_filter input {
        border: 1px solid #2f3740;
        border-radius: 20px;
        padding: 5px 12px;
        background: #252b33;
        color: var(--dark-text);
        margin-left: 5px;
    }
    
    .dataTables_length select {
        background: #252b33;
        color: var(--dark-text);
        border: 1px solid #2f3740;
        border-radius: 5px;
        padding: 3px;
    }
    
    .dataTables_paginate .paginate_button {
        background: #252b33;
        color: var(--dark-text) !important;
        border: 1px solid #2f3740;
        border-radius: 5px;
        padding: 5px 10px;
        margin: 0 2px;
        cursor: pointer;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: #ffc107;
        color: #1a1e24 !important;
    }
    
    .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #2f3740;
    }
    
    /* Disable sorting indicators */
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_desc:after,
    table.dataTable thead .sorting_asc_disabled:after,
    table.dataTable thead .sorting_desc_disabled:after {
        display: none !important;
    }
    
    table.dataTable thead th {
        cursor: default !important;
        background-image: none !important;
    }
    
    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1e24;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #2f3740;
        border-radius: 3px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .card-header {
            padding: 1rem;
        }
        
        .card-header h4 {
            font-size: 1rem;
        }
        
        .nav-pills .nav-link {
            padding: 4px 10px;
            font-size: 0.8rem;
        }
    }
    
    /* ===== STATUS ROW COLORS ===== */
    tr.bg-danger {
        background: rgba(220, 53, 69, 0.1) !important;
    }
    
    tr.bg-success {
        background: rgba(40, 167, 69, 0.1) !important;
    }
    
    /* ===== GAME ICONS ===== */
    .game-icon {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* ===== QUEENLIVE LIGHT PROFILE UI OVERRIDE ===== */
    :root {
        --ql-page: #f5f7fb;
        --ql-panel: #ffffff;
        --ql-panel-soft: #f8fafc;
        --ql-border: #dfe6f0;
        --ql-border-strong: #cbd5e1;
        --ql-text: #0f172a;
        --ql-muted: #64748b;
        --ql-primary: #2563eb;
        --ql-primary-dark: #1d4ed8;
        --ql-success: #059669;
        --ql-warning: #d97706;
        --ql-danger: #dc2626;
        --ql-info: #0891b2;
        --ql-shadow: 0 12px 30px rgba(15, 23, 42, .08);
    }

    body {
        background: var(--ql-page) !important;
        color: var(--ql-text) !important;
    }

    .body-content {
        background: var(--ql-page) !important;
        color: var(--ql-text) !important;
        padding: 18px;
    }

    .body-content > .row:first-child {
        background: var(--ql-panel);
        border: 1px solid var(--ql-border);
        border-radius: 12px;
        box-shadow: var(--ql-shadow);
        padding: 14px 16px;
        margin-left: 0;
        margin-right: 0;
    }

    .body-content > .row:first-child h4,
    .profile-page-title {
        color: var(--ql-text) !important;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0;
    }

    .card,
    .rating-block table,
    .image-gallery .card {
        background: var(--ql-panel) !important;
        border: 1px solid var(--ql-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--ql-shadow) !important;
    }

    .card:hover {
        box-shadow: 0 16px 36px rgba(15, 23, 42, .10) !important;
    }

    .card-header,
    .modal-header {
        background: var(--ql-panel-soft) !important;
        border-bottom: 1px solid var(--ql-border) !important;
    }

    .card-header h4,
    .card-header h5,
    .modal-title {
        color: var(--ql-text) !important;
        font-weight: 800;
    }

    .card-header.resume {
        background:
            radial-gradient(circle at 20% 20%, rgba(37, 99, 235, .20), transparent 32%),
            linear-gradient(135deg, #ffffff 0%, #eef4ff 55%, #e8f7ff 100%) !important;
        border-bottom: 1px solid var(--ql-border) !important;
        min-height: 150px;
        padding: 28px 15px;
    }

    .card-header.resume img {
        border: 4px solid #ffffff !important;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .18) !important;
    }

    .card-header.resume img:hover {
        border-color: var(--ql-primary) !important;
    }

    .card-content,
    .card-body,
    .modal-content,
    .modal-body,
    .image-gallery .card-body {
        background: var(--ql-panel) !important;
        color: var(--ql-text) !important;
        border-color: var(--ql-border) !important;
    }

    .card-content-member {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #dbe4ef !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08) !important;
        padding: 16px 14px !important;
        margin-bottom: 20px !important;
    }

    .card-content-member h4 {
        color: #0f172a !important;
        font-size: 20px;
        font-weight: 850;
        line-height: 1.25;
        margin-bottom: 6px;
    }

    .card-content-member h5 {
        color: #111827 !important;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .card-content-member p {
        color: #111827 !important;
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 0;
    }

    .card-content-member p i {
        color: #d97706 !important;
    }

    .card-content-member .balance-value {
        color: #15803d !important;
        font-weight: 900 !important;
    }

    .power-control-label,
    .dataTables_wrapper,
    .dataTables_info,
    .text-muted {
        color: var(--ql-muted) !important;
    }

    .resumecaption,
    .rating-block caption {
        background: var(--ql-panel-soft) !important;
        color: var(--ql-text) !important;
        border: 1px solid var(--ql-border) !important;
        border-left: 4px solid var(--ql-primary) !important;
        border-radius: 10px 10px 0 0 !important;
        font-weight: 850;
    }

    .table-responsive {
        background: var(--ql-panel) !important;
        border: 1px solid var(--ql-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--ql-shadow) !important;
    }

    .table,
    .rating-block table {
        background: var(--ql-panel) !important;
        color: var(--ql-text) !important;
    }

    .table thead th {
        background: #eef4ff !important;
        color: #334155 !important;
        border-bottom: 1px solid var(--ql-border-strong) !important;
        font-weight: 800;
    }

    .table tbody tr,
    .rating-block table tr {
        background: var(--ql-panel) !important;
        border-bottom: 1px solid #edf2f7 !important;
    }

    .table tbody tr:hover,
    .rating-block table tr:hover {
        background: #f8fbff !important;
    }

    .table tbody td,
    .rating-block table td {
        color: var(--ql-text) !important;
        border-top: 1px solid #edf2f7 !important;
        white-space: normal;
        word-break: break-word;
    }

    .rating-block table th,
    .table tbody th {
        color: #475569 !important;
        border-top: 1px solid #edf2f7 !important;
        font-weight: 800;
    }

    .table tfoot th {
        background: var(--ql-panel-soft) !important;
        color: var(--ql-text) !important;
        border-top: 1px solid var(--ql-border-strong) !important;
    }

    .amount-highlight,
    .balance-value {
        color: var(--ql-success) !important;
        font-weight: 850 !important;
    }

    .text-success { color: var(--ql-success) !important; }
    .text-warning { color: var(--ql-warning) !important; }
    .text-danger { color: var(--ql-danger) !important; }
    .text-info { color: var(--ql-info) !important; }
    .text-primary { color: var(--ql-primary) !important; }

    .badge {
        border-radius: 999px;
        font-weight: 800;
        letter-spacing: 0;
    }

    .badge.bg-info,
    .badge-info {
        background: #e0f2fe !important;
        color: #075985 !important;
    }

    .badge.bg-success,
    .badge-success {
        background: #dcfce7 !important;
        color: #166534 !important;
    }

    .badge.bg-warning,
    .badge-warning {
        background: #fef3c7 !important;
        color: #92400e !important;
    }

    .badge.bg-danger,
    .badge-danger {
        background: #fee2e2 !important;
        color: #991b1b !important;
    }

    .badge.bg-secondary,
    .badge-secondary,
    .badge-id {
        background: #eef2f7 !important;
        color: #334155 !important;
    }

    .btn {
        border-radius: 10px !important;
        box-shadow: none !important;
        font-weight: 800 !important;
    }

    .btn:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, .14) !important;
    }

    .btn-primary { background: var(--ql-primary) !important; color: #fff !important; }
    .btn-primary:hover { background: var(--ql-primary-dark) !important; }
    .btn-success { background: var(--ql-success) !important; color: #fff !important; }
    .btn-warning { background: #f59e0b !important; color: #111827 !important; }
    .btn-danger { background: var(--ql-danger) !important; color: #fff !important; }
    .btn-info { background: var(--ql-info) !important; color: #fff !important; }
    .btn-secondary { background: #475569 !important; color: #fff !important; }

    .power-control-item,
    .entry-frame-item {
        background: var(--ql-panel-soft) !important;
        border: 1px solid var(--ql-border) !important;
        border-radius: 12px !important;
    }

    .power-control-item:hover,
    .entry-frame-item:hover {
        border-color: var(--ql-primary) !important;
        background: #f0f6ff !important;
    }

    .nav-pills {
        background: var(--ql-panel) !important;
        border: 1px solid var(--ql-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--ql-shadow);
    }

    .nav-pills .nav-link {
        color: var(--ql-muted) !important;
        border-radius: 9px !important;
        font-weight: 800;
    }

    .nav-pills .nav-link.active {
        background: var(--ql-primary) !important;
        color: #ffffff !important;
    }

    .nav-pills .nav-link:hover:not(.active) {
        color: var(--ql-primary) !important;
        background: #eff6ff;
    }

    .modal-content {
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22) !important;
    }

    .modal-footer {
        border-top: 1px solid var(--ql-border) !important;
    }

    .modal-header .close {
        color: var(--ql-text) !important;
        opacity: .8;
    }

    .form-control,
    .dataTables_filter input,
    .dataTables_length select {
        background: #ffffff !important;
        border: 1px solid var(--ql-border-strong) !important;
        border-radius: 10px !important;
        color: var(--ql-text) !important;
    }

    .form-control:focus,
    .dataTables_filter input:focus {
        border-color: var(--ql-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    .dataTables_paginate .paginate_button {
        background: #ffffff !important;
        color: var(--ql-text) !important;
        border: 1px solid var(--ql-border) !important;
        border-radius: 8px !important;
    }

    .dataTables_paginate .paginate_button.current {
        background: var(--ql-primary) !important;
        color: #ffffff !important;
    }

    .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #eff6ff !important;
        color: var(--ql-primary) !important;
    }

    .image-gallery img {
        border-color: var(--ql-border) !important;
        background: #ffffff;
    }

    tr.bg-danger,
    tr.bg-danger td,
    tr.bg-danger th {
        background: #fef2f2 !important;
        color: #991b1b !important;
    }

    tr.bg-success,
    tr.bg-success td,
    tr.bg-success th {
        background: #f0fdf4 !important;
        color: #166534 !important;
    }

    ::-webkit-scrollbar-track {
        background: #edf2f7;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--ql-primary);
    }

    @media (max-width: 768px) {
        .body-content {
            padding: 12px;
        }

        .body-content > .row:first-child {
            padding: 12px;
        }

        .card-content,
        .card-body {
            padding: 14px;
        }
    }
</style>
@endpush

@section('content')
@php
    $adminCan = function ($key, $default = false) {
        return \App\Models\AdminParmisiton::allowed(Auth::id(), $key, $default);
    };
@endphp

@if($adminCan('profile_search'))
<div class="body-content">
    <!-- Profile Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="profile-page-title">
                    <i class="fas fa-user-circle mr-2"></i>
                    User Profile: {{ $user->name }}
                </h4>
                <span class="badge bg-info">ID: {{ $user->id }}</span>
            </div>
        </div>
    </div>

    <!-- Main Profile Row -->
    <div class="row">
        <!-- Left Column - Profile Card -->
        <div class="col-sm-12 col-md-4 employee-cv">
            <div class="card">
                <div class="card-header resume">
                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($user->profile) }}" class="img-circle" alt="Profile" onerror="this.src='https://via.placeholder.com/120x120?text=No+Image'">
                </div>
                <div class="card-content">
                    <div class="card-content-member">
                        <h4 class="m-t-0">{{ $user->name }}</h4>
                        <h5>ID: {{$user->id}}</h5>
                        <p class="m-0">
                            <i class="fas fa-coins text-warning"></i>
                            @if($adminCan('profile_balance'))
                            <span class="balance-value">{{number_format($user->balance)}}</span>
                            @else
                            <span class="text-muted">Hidden</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="card-content-languages">
                        <!-- Basic Information -->
                        <div class="card-content-languages-group">
                            <table class="table table-hover">
                                <caption class="resumecaption">
                                    <i class="fas fa-info-circle mr-2"></i>Basic Information
                                </caption>
                                <tbody>
                                    <tr>
                                        <th><i class="fas fa-level-up-alt text-info mr-2"></i>Level</th>
                                        <td>{{$user->level}}</td>
                                    </tr>
                                    @if($adminCan('profile_email_info'))
                                    <tr>
                                        <th><i class="fas fa-envelope text-warning mr-2"></i>Email</th>
                                        <td>{{$user->email}}</td>
                                    </tr>
                                    @endif
                                    @if($info)
                                    @if($adminCan('profile_sensitive_info'))
                                    <tr>
                                        <th><i class="fas fa-id-card text-success mr-2"></i>NID</th>
                                        <td>{{$info->nid}}</td>
                                    </tr>
                                    @endif
                                    @if($adminCan('profile_phone_info'))
                                    <tr>
                                        <th><i class="fas fa-phone text-info mr-2"></i>Phone</th>
                                        <td>{{$info->phone}}</td>
                                    </tr>
                                    @endif
                                    @endif
                                    @if($adminCan('profile_sensitive_info'))
                                    <tr>
                                        <th><i class="fas fa-crown text-warning mr-2"></i>VIP</th>
                                        <td>
                                            <div class="vip-button-grid">
                                                @foreach($my_vips as $index => $my_vip)
                                                @php
                                                    $vipClass = 'vip-btn-' . (($index % 7) + 1);
                                                @endphp
                                                <a href="{{URL::to('/vips_remove',$my_vip->id)}}" class="btn btn-sm {{$vipClass}}">
                                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($my_vip->image) }}" alt="VIP" style="width: 16px; height: 16px; border-radius: 50%; margin-right: 3px;" onerror="this.style.display='none'"> Remove
                                                </a>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($adminCan('profile_sensitive_info'))
                                    <tr>
                                        <th><i class="fas fa-frame text-info mr-2"></i>Entry Frame</th>
                                        <td>
                                            <div class="power-button-grid">
                                                @foreach($my_begs as $my_beg)
                                                <img src="{{ \App\Support\MediaPathHelper::publicUrl($my_beg->image) }}" alt="Frame" class="rounded-circle mr-1" style="width: 25px; height: 25px; border: 2px solid #ffc107;" onerror="this.style.display='none'">
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th><i class="fas fa-sign-in-alt text-success mr-2"></i>Entry</th>
                                        <td>{{$user->entry_level}}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar-plus text-warning mr-2"></i>Join Date</th>
                                        <td>{{$user->created_at instanceof \Carbon\Carbon ? $user->created_at->format('d M Y, h:i A') : $user->created_at}}</td>
                                    </tr>
                                    @php
                                    $contry=App\Models\Country::find($user->country_id);
                                    $other_devices=App\Models\User::where('id','!=',$user->id)
                                        ->where(function($query) use ($user) {
                                            if(!empty($user->imei_number)) {
                                                $query->orWhere('imei_number',$user->imei_number);
                                            }
                                            if(!empty($user->device_id)) {
                                                $query->orWhere('device_id',$user->device_id);
                                            }
                                        })
                                        ->get();
                                    @endphp
                                    <tr>
                                        <th><i class="fas fa-globe text-info mr-2"></i>Country</th>
                                        <td>@if($contry) {{$contry->name}} @endif</td>
                                    </tr>
                                    @if($adminCan('profile_other_ids'))
                                    <tr>
                                        <th><i class="fas fa-mobile-alt text-danger mr-2"></i>Other ID's</th>
                                        <td>
                                            @if($other_devices && $other_devices->count()) 
                                                @foreach($other_devices as $other_device)
                                                    <span class="badge bg-secondary mr-1">{{$other_device->id}}-{{$other_device->name}}</span>
                                                @endforeach 
                                                <form action="{{URL::to('id_search_clear_device_ids/'.$user->id)}}" method="post" style="display:inline-block;margin-left:8px;" onsubmit="return confirm('Clear device id from other matching users only?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-warning" style="border-radius:999px;">Clear Other Device IDs</button>
                                                </form>
                                            @else
                                                <span class="text-muted">No matching device users</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Agency Information -->
                        @if($agency_info)
                        <div class="card-content-languages-group">
                            <table class="table table-hover">
                                <caption class="resumecaption">
                                    <i class="fas fa-building mr-2"></i>Agency Information
                                </caption>
                                <tbody>
                                    <tr>
                                        <th><i class="fas fa-tag text-warning mr-2"></i>Join Agency Name</th>
                                        <td>{{$agency_info->name}}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-code text-info mr-2"></i>Code</th>
                                        <td>{{$agency_info->code}}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-phone text-success mr-2"></i>Agency Phone</th>
                                        <td>{{$agency_info->phone}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Information -->
        <div class="col-sm-12 col-md-8 employee-cv-info">
            <div class="row">
                <!-- Portal Active -->
                @if($user->is_coin_protal_active==1)
                @php
                $ProtalToPTransfer = App\Models\ProtalToPTransfer::where('user_id',$user->id)->sum('amount');
                $ProtalToPTransferRecived = App\Models\ProtalToPTransfer::where('portal_user_id',$user->id)->sum('amount');
                @endphp
                <div class="col-sm-12 col-md-12 rating-block">
                    <table class="table table-hover">
                        <caption class="resumecaption">
                            <i class="fas fa-exchange-alt text-info mr-2"></i>Portal Active
                        </caption>
                        <tbody>
                            <tr>
                                <th>Recharge</th>
                                <td><span class="balance-value" id="portal_recharge">{{number_format($protal_recharge)}}</span></td>
                            </tr>
                            <tr>
                                <th>Transfer</th>
                                <td><span class="text-warning" id="portal_transfer">{{number_format($protal_transfer)}}</span></td>
                            </tr>
                            <tr>
                                <th>Recall</th>
                                <td><span class="text-danger" id="portal_recall">{{number_format($recall_protal_recharge)}}</span></td>
                            </tr>
                            <tr>
                                <th>Portal Transfer Send</th>
                                <td><span class="text-warning" id="portal_send">{{number_format($ProtalToPTransfer)}}</span></td>
                            </tr>
                            <tr>
                                <th>Portal Transfer Received</th>
                                <td><span class="text-success" id="portal_received">{{number_format($ProtalToPTransferRecived)}}</span></td>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <td><span class="balance-value" id="portal_balance">{{number_format(($protal_recharge+$ProtalToPTransferRecived)-($protal_transfer+$recall_protal_recharge+$ProtalToPTransfer))}}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>	
                @endif
                
                <!-- Agency Owner -->
                @if($agency)
                <div class="col-sm-12 col-md-12 rating-block">
                    <table class="table table-hover">
                        <caption class="resumecaption">
                            <i class="fas fa-crown text-warning mr-2"></i>Agency Owner
                        </caption>
                        <tbody>
                            <tr>
                                <th>Name</th>
                                <td>{{$agency->name}}</td>
                            </tr>
                            <tr>
                                <th>Code</th>
                                <td>{{$agency->code}}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{$agency->phone}}</td>
                            </tr>
                            <tr>
                                <th>Withdraw Commission</th>
                                <td><span class="balance-value" id="withdraw_commission">{{number_format($approved_balance)}}</span></td>
                            </tr>
                            <tr>
                                <th>Convert</th>
                                <td><span class="text-warning" id="agency_convert">{{number_format($agency_convart_balance)}}</span></td>
                            </tr>
                            <tr>
                                <th>Available</th>
                                <td><span class="text-success" id="agency_available">{{number_format($approved_balance-$agency_convart_balance)}}</span></td>
                            </tr>
                            <tr class="{{ $check_host_balance < 5 ? 'bg-danger' : 'bg-success' }}">
                                <th>Total Host Withdraw</th>
                                <td><span class="{{ $check_host_balance < 5 ? 'text-white' : 'text-white' }} fw-bold" id="host_withdraw">{{$check_host_balance}}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Live Data -->
                @php
                $date = Carbon\Carbon::now('Europe/London');
                $user_id = $user->id;
                $start_date = date('Y-m') . '-01';
                $end_date = date('Y-m') . '-31';

                $type = DB::table('users')
                    ->join('host_data', 'host_data.user_id', 'users.id')
                    ->where('users.id', $user_id)
                    ->select('host_data.hosting_type','host_data.id')
                    ->first();

                if ($type) {
                    $dayTimeHistory = DB::table('day_times')
                        ->where('user_id', $user_id)
                        ->get();
                    
                    $running_durations = DB::table('day_times')
                        ->where('user_id', $user_id)
                        ->where('brd_type',$type->hosting_type)
                        ->where('day_times', '>', '00:14:59')
                        ->select('day_times')
                        ->get();
                    
                    function addDurations($duration1, $duration2) {
                        $time1 = explode(':', $duration1);
                        $time2 = explode(':', $duration2);

                        $hours = intval($time1[0]) + intval($time2[0]);
                        $minutes = intval($time1[1]) + intval($time2[1]);
                        $seconds = intval($time1[2]) + intval($time2[2]);

                        if ($seconds >= 60) {
                            $minutes += 1;
                            $seconds -= 60;
                        }

                        if ($minutes >= 60) {
                            $hours += 1;
                            $minutes -= 60;
                        }

                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }
                    
                    $totalDuration = '00:00:00';
                    foreach ($running_durations as $duration) {
                        $durationTime = new DateTime($duration->day_times);
                        $totalDuration = addDurations($totalDuration, $durationTime->format('H:i:s'));
                    }
                    
                    $total_coin = DB::table('gifts')
                        ->join('users','users.id','gifts.sander_id')
                        ->where('gifts.reciever_id',$user_id)
                        ->whereDate('date', '>=', $start_date)
                        ->whereDate('date', '<=', $end_date)
                        ->sum('gifts.value');
                    
                    $day_time_data = DB::table('day_times')
                        ->where('user_id', $user_id)
                        ->orderby('id','desc')
                        ->get();

                    $day_time_duration = DB::table('day_times')
                        ->where('user_id', $user_id)
                        ->where('brd_type', $type->hosting_type)
                        ->where('day_times', '>', '00:14:59')
                        ->select('live_time', 'day_times')
                        ->get();
                    
                    $running_day_count = 0;
                    $current_date = null;
                    $total_duration_seconds = 0;
                    
                    foreach ($day_time_duration as $day_time_duration) {
                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                        $time = $day_time_duration->day_times;
                    
                        if ($current_date === null || $current_date !== $date) {
                            if ($current_date !== null && $total_duration_seconds >= 3600) {
                                $running_day_count++;
                            }
                            $current_date = $date;
                            $total_duration_seconds = 0;
                        }
                    
                        $duration_parts = explode(':', $time);
                        $hours = intval($duration_parts[0]);
                        $minutes = intval($duration_parts[1]);
                        $seconds = intval($duration_parts[2]);
                        $total_duration_seconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                    }
                    
                    if ($total_duration_seconds >= 3600) {
                        $running_day_count++;
                    }
                }
                $day_time = "00:00:00";
                $total_seconds = 0;
                
                $total_withdraw = DB::table('withdraws')
                    ->where('host_id',$user_id)
                    ->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)
                    ->sum('total');        
                @endphp

                @if($type)
                <div class="col-sm-12 col-md-12 rating-block">
                    <table class="table table-hover">
                        <caption class="resumecaption">
                            <i class="fas fa-broadcast-tower text-danger mr-2"></i>Live Data
                        </caption>
                        <tbody>
                            <tr>
                                <th>Hosting Type</th>
                                <td>
                                    @if($type->hosting_type==2) 
                                        <span class="badge bg-info mr-2">Video</span> 
                                    @else 
                                        <span class="badge bg-secondary mr-2">Audio</span> 
                                    @endif
                                    <a href="{{URL::to('hosting_type_change/'.$type->id)}}" class="btn btn-sm btn-danger">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        @if($type->hosting_type==2) Make Audio @else Make Video @endif
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Day</th>
                                <td><span class="badge bg-success" id="live_days">{{$running_day_count ?? 0}}</span></td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td><span class="badge bg-info" id="live_time">{{$totalDuration ?? '00:00:00'}}</span></td>
                            </tr>
                            <tr>
                                <th>Point Collect</th>
                                <td><span class="balance-value" id="point_collect">{{number_format($total_coin ?? 0)}}</span></td>
                            </tr>
                            <tr>
                                <th>Total Withdraw</th>
                                <td><span class="text-danger" id="total_withdraw">{{number_format($total_withdraw ?? 0)}}</span></td>
                            </tr>
                            <tr>
                                <th>Previous Points</th>
                                <td><span class="text-info" id="previous_points">{{number_format($user->previous_coin ?? 0)}}</span></td>
                            </tr>
                            <tr>
                                <th>Now Points Have</th>
                                <td><span class="balance-value" id="current_points">{{number_format(($total_coin ?? 0)+($user->previous_coin ?? 0)-($total_withdraw ?? 0))}}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Power Buttons - 6 Column Grid -->
                @if($adminCan('profile_power_buttons'))
                <div class="col-sm-12 col-md-12 rating-block">
                    <table class="table table-hover">
                        <caption class="resumecaption">
                            <i class="fas fa-power-off text-danger mr-2"></i>Power Controls
                        </caption>
                        <tbody>
                            <tr>
                                <td colspan="2" style="padding: 15px;">
                                    <div class="power-controls-grid">
                                        <!-- Admin Role -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-user-shield text-danger mr-1"></i> Admin Role
                                                @php
                                                    $panelRole = (int) ($user->is_admin ?? 0);
                                                    $panelRoleLabels = [0 => 'User', 1 => 'Main Admin', 2 => 'Country Admin', 3 => 'Sub Admin'];
                                                @endphp
                                                <span class="badge badge-secondary ml-1">{{ $panelRoleLabels[$panelRole] ?? ('Role '.$panelRole) }}</span>
                                            </div>
                                            <div class="power-control-buttons" style="display:flex;flex-wrap:wrap;gap:4px;">
                                                @foreach([1 => 'Main', 2 => 'Country', 3 => 'Sub', 0 => 'User'] as $roleValue => $roleLabel)
                                                    <a href="{{ URL::to('admin/user-role/'.$user->id.'/'.$roleValue) }}" class="btn btn-xs {{ $panelRole === $roleValue ? 'btn-success' : ($roleValue === 0 ? 'btn-danger' : 'btn-primary') }}">
                                                        {{ $panelRole === $roleValue ? 'Active' : 'Make' }} {{ $roleLabel }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                                                                <!-- Top Position -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-arrow-up text-warning mr-1"></i> Top Position
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->prosss_top==1)
                                                    <a href="{{URL::to('/admin/top-position',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/admin/top-position',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Brd Off Power -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-power-off text-danger mr-1"></i> Brd Off
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->brd_off_power!=1)
                                                    <a href="{{URL::to('/brd_off_power_on',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/brd_off_power_off',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Screenshot -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-camera text-info mr-1"></i> Screenshot
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->sceen_short_power!=1)
                                                    <a href="{{URL::to('/sceenshort_on',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/sceenshort_off',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Kick -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-user-slash mr-1" style="color: #9b59b6;"></i> Kick
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->kick_power!=1)
                                                    <a href="{{URL::to('/kick_power_on',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/kick_power_off',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Comment Mute -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-comment-slash text-warning mr-1"></i> Comment Mute
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->comment_mute_power!=1)
                                                    <a href="{{URL::to('/comment_mute_power_on',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/comment_mute_power_off',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Invisible -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-eye-slash text-info mr-1"></i> Invisible
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->is_invisible!=1)
                                                    <a href="{{URL::to('/invisibal_on',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/invisibal_off',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Withdraw -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-money-bill-wave text-success mr-1"></i> Withdraw
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->withdraw_active!=1)
                                                    <a href="{{URL::to('/withdraw_active',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/withdraw_active',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Agora -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-video text-primary mr-1"></i> Agora
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->agora_access!=1)
                                                    <a href="{{URL::to('/agora_access',$user->id)}}" class="btn btn-xs btn-success">On</a>
                                                @else
                                                    <a href="{{URL::to('/agora_access',$user->id)}}" class="btn btn-xs btn-danger">Off</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Hosting -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-server text-info mr-1"></i> Hosting
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->is_host_id!=1)
                                                    <a href="{{URL::to('active_host/'.$user->id)}}" class="btn btn-xs btn-success">Active</a>
                                                @else
                                                    <a href="{{URL::to('reject_host/'.$user->id)}}" class="btn btn-xs btn-danger">Reject</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Portal -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-coins text-warning mr-1"></i> Portal
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->is_coin_protal_active!=1)
                                                    <a href="{{URL::to('active_protal/'.$user->id)}}" class="btn btn-xs btn-success">Active</a>
                                                @else
                                                    <a href="{{URL::to('reject_protal/'.$user->id)}}" class="btn btn-xs btn-danger">Reject</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Official -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-certificate text-primary mr-1"></i> Official
                                            </div>
                                            <div class="power-control-buttons">
                                                @if($user->is_official_id!=1)
                                                    <a href="{{URL::to('active_official_id/'.$user->id)}}" class="btn btn-xs btn-success">Active</a>
                                                @else
                                                    <a href="{{URL::to('reject_official_id/'.$user->id)}}" class="btn btn-xs btn-danger">Reject</a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Profile Edit -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-user-edit text-info mr-1"></i> Profile
                                            </div>
                                            <div class="power-control-buttons">
                                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#editProfileModal">Edit</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Day Time -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-clock text-warning mr-1"></i> Day Time
                                            </div>
                                            <div class="power-control-buttons">
                                                <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#addDayTimeModal">Add</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Password -->
                                        <div class="power-control-item">
                                            <div class="power-control-label">
                                                <i class="fas fa-key text-danger mr-1"></i> Password
                                            </div>
                                            <div class="power-control-buttons">
                                                <a href="{{URL::to('password_change_user/'.$user->id)}}" class="btn btn-xs btn-danger">Change</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- VIP Activation Section -->
                            <tr>
                                <th><i class="fas fa-crown text-warning mr-2"></i> VIP Activation</th>
                                <td>
                                    <div class="vip-button-grid">
                                        @for($vipNo = 1; $vipNo <= 7; $vipNo++)
                                            @php
                                                $vipActive = $my_vips->firstWhere('vip_no', $vipNo) && (int) optional($my_vips->firstWhere('vip_no', $vipNo))->is_active === 1;
                                            @endphp
                                            <a href="{{ URL::to('active_vip_manual', ['id' => $user->id, 'vip' => $vipNo]) }}" class="btn btn-sm {{ $vipActive ? 'btn-danger' : 'vip-btn-'.$vipNo }}">
                                                VIP {{$vipNo}} {{ $vipActive ? 'Inactive' : 'Active' }}
                                            </a>
                                        @endfor
                                    </div>
                                    <div style="font-size:10px;color:#888;margin-top:6px;">Powerd by JAMBOai</div>
                                </td>
                            </tr>
                            
                            <!-- Entry Frame Section -->
                            <tr>
                                <td colspan="2" style="padding: 15px;">
                                    <div class="entry-frame-grid">
                                        @foreach($entry_frame_list as $index => $entry_frame)
                                        @php
                                            $ownedEntryFrame = $my_begs->where('store_id', $entry_frame->id)->first();
                                            $entryFrameActive = $ownedEntryFrame && (int) $ownedEntryFrame->status === 1;
                                        @endphp
                                        <div class="entry-frame-item">
                                            <img src="{{ \App\Support\MediaPathHelper::publicUrl($entry_frame->image) }}" alt="Frame" onerror="this.src='https://via.placeholder.com/30x30?text=Frame'">
                                            <span class="badge {{ $entryFrameActive ? 'badge-success' : 'badge-secondary' }}" style="display:block;margin:4px 0;">
                                                {{ $entryFrameActive ? 'Active' : 'Inactive' }}
                                            </span>
                                            <a href="{{ URL::to('active_effect_manual',['user_id' => $user->id, 'id' => $entry_frame->id]) }}" class="btn btn-xs {{ $entryFrameActive ? 'btn-danger' : 'btn-primary' }}">
                                                {{ $entryFrameActive ? 'Deactivate' : 'Activate' }}
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Special Frame Section -->
                            <tr>
                                <th><i class="fas fa-star text-warning mr-2"></i> Special Frame</th>
                                <td>
                                    <div class="special-frame-grid">
                                        @php
                                            $specialFrames = [
                                                ['label'=>'Admin Frame','flag'=>'is_admin_frame','active'=>'active_special_admin_frame_manual','inactive'=>'inactive_special_admin_frame_manual','class'=>'special-frame-admin'],
                                                ['label'=>'Official Frame','flag'=>'is_official_frame','active'=>'active_special_official_frame_manual','inactive'=>'inactive_special_official_frame_manual','class'=>'special-frame-official'],
                                            ];
                                        @endphp
                                        @foreach($specialFrames as $specialFrame)
                                            @php $specialActive = (int) $user->{$specialFrame['flag']} === 1; @endphp
                                            <a href="{{ URL::to(($specialActive ? $specialFrame['inactive'] : $specialFrame['active']).'/'.$user->id) }}" class="btn btn-sm {{ $specialActive ? 'btn-danger' : $specialFrame['class'] }}">
                                                <i class="fas {{ $specialActive ? 'fa-times' : 'fa-check' }} mr-1"></i>
                                                {{ $specialFrame['label'] }} {{ $specialActive ? 'Inactive' : 'Active' }}
                                            </a>
                                        @endforeach
                                    </div>
                                    <div style="font-size:10px;color:#888;margin-top:6px;">Powerd by JAMBOai</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    @if($adminCan('profile_vip_frames_edit'))
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit mr-2"></i>Edit Profile
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('user_profile_update',$user->id)}}" enctype="multipart/form-data" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" value="{{ $user->name }}" class="form-control" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="profile">Profile Image</label>
                            <input type="file" name="profile" class="form-control" id="profile">
                            <input type="hidden" value="{{$user->profile}}" name="old_profile">
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag</label>
                            <input type="text" name="teg" class="form-control" id="tag" placeholder="Enter tag">
                        </div>
                        <div class="form-group">
                            <label for="top_value">Top Value</label>
                            <input type="number" name="top_value" value="{{$user->top_value}}" class="form-control" id="top_value">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Day Time Modal -->
    @if($adminCan('profile_password_daytime'))
    <div class="modal fade" id="addDayTimeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-clock mr-2"></i>Add Day Time
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{URL::to('user_day_time_add',$user->id)}}" enctype="multipart/form-data" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" id="date" required>
                        </div>
                        <div class="form-group">
                            <label for="time">Time</label>
                            <input type="text" name="time" value="01:01:03" class="form-control" id="time" required>
                        </div>
                        <div class="form-group">
                            <label for="brd_type">Broadcast Type</label>
                            <select name="brd_type" class="form-control" required>
                                <option value="2">Video</option>
                                <option value="1">Audio</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Image Gallery -->
    @if($adminCan('profile_sensitive_info'))
    @if($info)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-images mr-2"></i>Document Gallery
                    </h5>
                </div>
                <div class="card-body">
                    <div class="image-gallery">
                        <div class="col-xl-4 col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-white mb-2">NID Image</h6>
                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($info->image) }}" alt="NID" onerror="this.src='https://via.placeholder.com/300x180?text=No+Image'">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-white mb-2">Photo ID</h6>
                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($info->photo_id) }}" alt="Photo ID" onerror="this.src='https://via.placeholder.com/300x180?text=No+Image'">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-white mb-2">Selfie</h6>
                                    <img src="{{ \App\Support\MediaPathHelper::publicUrl($info->selfie) }}" alt="Selfie" onerror="this.src='https://via.placeholder.com/300x180?text=No+Image'">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Portal History -->
    @if($user->is_coin_protal_active==1)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history mr-2"></i>Portal History
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="recharge-tab" data-toggle="pill" href="#recharge" role="tab">Recharge</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="transfer-tab" data-toggle="pill" href="#transfer" role="tab">Transfer</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="pills-tabContent">
                        <!-- Recharge Tab -->
                        <div class="tab-pane fade show active" id="recharge" role="tabpanel">
                           <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th>TrxID</th>
                                        <th>Date</th>
                                        <th>Approved By</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i=0; $total_potal_history=0; @endphp
                                    @foreach($protal_recharge_details as $protal_recharge_detail)
                                    @php
                                    $approved_by = \App\RedisCache\RedisCache::UserfindById($protal_recharge_detail->recharge_by);
                                    @endphp
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td><span class="badge bg-info">{{$protal_recharge_detail->trxid}}</span></td>
                                        <td>{{$protal_recharge_detail->date}}</td>
                                        <td>@if($approved_by){{$approved_by->name}} @endif</td>
                                        <td><span class="text-success">{{$protal_recharge_detail->amount}}</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </td>
                                    </tr>
                                    @php $total_potal_history += $protal_recharge_detail->amount; @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total:</th>
                                        <th>{{$total_potal_history}}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        </div>
                        
                        <!-- Transfer Tab -->
                        <div class="tab-pane fade" id="transfer" role="tabpanel">
                            <div class="table-responsive">
                             <table class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th>TrxID</th>
                                        <th>Date</th>
                                        <th>Received By</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i=0; $transer=0; @endphp
                                    @foreach($protal_transfer_details as $protal_transfer_detail)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td><span class="badge bg-info">{{$protal_transfer_detail->trxid}}</span></td>
                                        <td>{{$protal_transfer_detail->date}}</td>
                                        <td>{{$protal_transfer_detail->user_id}}</td>
                                        <td><span class="text-warning">{{$protal_transfer_detail->amount}}</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </td>
                                    </tr>
                                    @php $transer += $protal_transfer_detail->amount; @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total:</th>
                                        <th>{{$transer}}</th>
                                        <th></th>
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
    @endif

    <!-- Host Data -->
    @if($agency)
    @php
    $host_lists = DB::table('host_data')
        ->join('users','users.id','host_data.user_id')
        ->where('agency_code',$agency->code)
        ->get();
    @endphp
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-server mr-2"></i>Host Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Balance</th>
                                <th>Day Time</th>
                                <th>Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @foreach($host_lists as $host_list)
                            @php
                            $date = Carbon\Carbon::now();
                            $user_id = $host_list->id;
                            $host_start_date = '2023-06-16';
                            $host_end_date = '2023-06-31';
                            $host_type = DB::table('users')
                                ->join('host_data', 'host_data.user_id', 'users.id')
                                ->where('users.id', $user_id)
                                ->select('host_data.hosting_type','host_data.id')
                                ->first();

                            if ($host_type) {
                                $durations = DB::table('day_times')
                                    ->where('user_id', $user_id)
                                    ->where('live_time', '>=', $host_start_date)
                                    ->where('live_time', '<=', $host_end_date)
                                    ->where('brd_type',$host_type->hosting_type)
                                    ->where('day_times', '>', '00:19:59')
                                    ->select('day_times')
                                    ->get();

                                $totalDuration = Carbon\Carbon::createFromTime(0, 0, 0);

                                foreach ($durations as $duration) {
                                    $parts = explode(':', $duration->day_times);
                                    $hours = intval($parts[0]);
                                    $minutes = intval($parts[1]);
                                    $seconds = intval($parts[2]);
                                    $interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
                                    $totalDuration->add($interval);
                                }

                                $totalDurationFormatted = $totalDuration->format('H:i:s');

                                $total_coin = DB::table('gifts')
                                    ->join('users','users.id','gifts.sander_id')
                                    ->where('gifts.reciever_id',$user_id)
                                    ->where('date', '>=', $host_start_date)
                                    ->where('date', '<=', $host_end_date)
                                    ->sum('value');

                                $day_time_duration = DB::table('day_times')
                                    ->where('user_id', $user_id)
                                    ->where('live_time', '>=', $host_start_date)
                                    ->where('live_time', '<=', $host_end_date)
                                    ->where('brd_type', $host_type->hosting_type)
                                    ->where('day_times', '>', '00:19:59')
                                    ->select('live_time', 'day_times')
                                    ->get();

                                $day_count = 0;
                                $current_date = null;
                                $total_duration = 0;

                                foreach ($day_time_duration as $day_time_duration) {
                                    $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                    $time = $day_time_duration->day_times;

                                    if ($current_date === null || $current_date !== $date) {
                                        if ($current_date !== null && $total_duration >= 3660) {
                                            $day_count++;
                                        }
                                        $current_date = $date;
                                        $total_duration = 0;
                                    }

                                    $duration_parts = explode(':', $time);
                                    $hours = intval($duration_parts[0]);
                                    $minutes = intval($duration_parts[1]);
                                    $seconds = intval($duration_parts[2]);
                                    $total_duration += ($hours * 3600) + ($minutes * 60) + $seconds;
                                }

                                if ($total_duration >= 3660) {
                                    $day_count++;
                                }
                            }
                            @endphp
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td><span class="badge bg-info">{{$host_list->id}}</span></td>
                                <td>{{$host_list->name}}</td>
                                <td><span class="text-success">{{number_format($host_list->balance)}}</span></td>
                                <td>
                                    <span class="badge bg-success">Days: {{$day_count}}</span><br>
                                    <span class="badge bg-info">Time: {{$totalDurationFormatted ?? '00:00:00'}}</span>
                                </td>
                                <td>{{$host_list->phone}}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-success">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger">
                                        <i class="fas fa-ban"></i> Inactive
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Game History -->
    @if($game_history)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-gamepad mr-2"></i>Game History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Time</th>
                                <th>Tray ID</th>
                                <th>Game</th>
                                <th>Position</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Add Balance</th>
                                <th>Due Balance</th>
                                <th>Old Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @foreach($game_history as $game)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ optional($game->created_at)->format('d/m H:i') ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{$game->tray_id}}</span></td>
                                <td>
                                     @if($game->game_type=='firust')
                                        <span class="badge bg-success">Fruits</span>
                                    @elseif($game->game_type=='Teen_patti')
                                        <span class="badge bg-primary">Teen Patti</span>
                                    @elseif($game->game_type=='greedy')
                                        <span class="badge bg-warning">Greedy</span>
                                    @elseif($game->game_type=='five')
                                        <span class="badge bg-info">Five Star</span>
                                    @endif
                                </td>
                                <td>
                                   @if($game->game_type=='firust')
                                        @if($game->pot_no=='saven_win')
                                            <img src="{{asset('public/game/new/image/lemon.png')}}" style="width: 30px;" alt="Lemon">
                                        @elseif($game->pot_no=='watermelon')
                                            <img src="{{asset('public/game/new/image/watermelon.png')}}" style="width: 30px;" alt="Watermelon">
                                        @else
                                            <img src="{{asset('public/game/new/image/apple.png')}}" style="width: 30px;" alt="Apple">
                                        @endif
                                    @elseif($game->game_type=='Teen_patti')
                                        @if($game->pot_no=='saven_win')
                                            <img src="{{asset('public/game/teenpatti/image/ChairBlue.png')}}" style="width: 30px;" alt="Blue">
                                        @elseif($game->pot_no=='watermelon')
                                            <img src="{{asset('public/game/teenpatti/image/ChairGreen.png')}}" style="width: 30px;" alt="Green">
                                        @else
                                            <img src="{{asset('public/game/teenpatti/image/ChairRed.png')}}" style="width: 30px;" alt="Red">
                                        @endif
                                    @endif
                                </td>
                                <td><span class="text-warning">{{$game->amount}}</span></td>
                                <td>
                                    @if($game->status==0)
                                        <span class="badge bg-warning">Hold</span>
                                    @elseif($game->status==1)
                                        <span class="badge bg-success">Win</span>
                                    @else
                                        <span class="badge bg-danger">Loss</span>
                                    @endif
                                </td>
                                <td>{{$game->serve_balance}}</td>
                                <td>{{$game->balance_give}}</td>
                                <td>{{$game->total_serve}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Day Time History -->
    @if($type && isset($day_time_data))
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock mr-2"></i>Day Time History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Channel Name</th>
                                <th>Time</th>
                                <th>Live Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @foreach($day_time_data as $day_time_h)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$day_time_h->channelName}}</td>
                                <td><span class="badge bg-info">{{$day_time_h->day_times}}</span></td>
                                <td>{{$day_time_h->live_time}}</td>
                                <td>
                                    @if($day_time_h->brd_type==2)
                                        <span class="badge bg-success">Video</span>
                                    @else
                                        <span class="badge bg-secondary">Audio</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Portal Transfer History -->
    @if($protal_to_protal_transfer)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt mr-2"></i>Portal Transfer Send
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Portal User</th>
                                <th>Amount</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @foreach($protal_to_protal_transfer as $protal_to_protals_transfer)
                            @php
                            $protal_user = \App\RedisCache\RedisCache::UserfindById($protal_to_protals_transfer->portal_user_id);
                            @endphp
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$protal_user->name ?? 'N/A'}} ({{$protal_user->id ?? ''}})</td>
                                <td><span class="text-danger">{{$protal_to_protals_transfer->amount}}</span></td>
                                <td>{{$protal_to_protals_transfer->created_at->format('d M Y H:i')}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Portal Transfer Received -->
    @if($protal_to_protal_transfer_recived)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt mr-2"></i>Portal Transfer Received
                    </h5>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>From User</th>
                                <th>Amount</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @foreach($protal_to_protal_transfer_recived as $protal_to_protals_transfer_recived)
                            @php
                            $protal_user = \App\RedisCache\RedisCache::UserfindById($protal_to_protals_transfer_recived->user_id);
                            @endphp
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$protal_user->name ?? 'N/A'}} ({{$protal_user->id ?? ''}})</td>
                                <td><span class="text-success">{{$protal_to_protals_transfer_recived->amount}}</span></td>
                                <td>{{$protal_to_protals_transfer_recived->created_at->format('d M Y H:i')}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Convert History -->
    @if($convart_history)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sync-alt mr-2"></i>Convert History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>TrxID</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; $total=0; @endphp
                            @foreach($convart_history as $convart_historys)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td><span class="badge bg-info">{{$convart_historys->trxid}}</span></td>
                                <td>{{$convart_historys->date}}</td>
                                <td><span class="text-warning">{{$convart_historys->amount}}</span></td>
                            </tr>
                            @php $total += $convart_historys->amount; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>{{$total}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Monthly Recharge History -->
    @if($monthly_recharge_historys)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt mr-2"></i>Running Month Recharge - {{Carbon\Carbon::now()->format('m-Y')}}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Portal ID</th>
                                <th>TxID</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; $monthly_recharge_history_total=0; @endphp
                            @foreach($monthly_recharge_historys as $monthly_recharge_history)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$monthly_recharge_history->portal_user_id}}</td>
                                <td><span class="badge bg-info">{{$monthly_recharge_history->trxid}}</span></td>
                                <td><span class="text-success">{{$monthly_recharge_history->amount}}</span></td>
                                <td>{{$monthly_recharge_history->created_at->format('d M Y H:i')}}</td>
                            </tr>
                            @php $monthly_recharge_history_total += $monthly_recharge_history->amount; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>{{$monthly_recharge_history_total}}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recharge History -->
    @if($recharge_historys)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card mr-2"></i>Recharge History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Portal ID</th>
                                <th>TxID</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; $recharge_history_total=0; @endphp
                            @foreach($recharge_historys as $recharge_history)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$recharge_history->portal_user_id}}</td>
                                <td><span class="badge bg-info">{{$recharge_history->trxid}}</span></td>
                                <td><span class="text-success">{{$recharge_history->amount}}</span></td>
                                <td>{{$recharge_history->created_at->format('d M Y H:i')}}</td>
                            </tr>
                            @php $recharge_history_total += $recharge_history->amount; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>{{$recharge_history_total}}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Gift Sending History -->
    @if($sanding_historys)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-gift mr-2"></i>Gift Sending History @if($old_sum_sending_historys>0 )(In Total +{{$old_sum_sending_historys}} From Archive Gift) @endif
                    </h5>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Receiver ID</th>
                                <th>Gift Name</th>
                                <th>Amount</th>
                                <th>Time</th>
                                <th>Recall</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; $sanding_history_total=$old_sum_sending_historys; @endphp
                            @foreach($sanding_historys as $sanding_history)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$sanding_history->reciever_id}}</td>
                                <td>{{$sanding_history->name}}</td>
                                <td><span class="text-warning">{{$sanding_history->value}}</span></td>
                                <td>{{$sanding_history->date}}</td>
                                <td>
                                    <a href="{{URL::to('gift_recall',$sanding_history->id)}}" class="btn btn-sm btn-danger">
                                        <i class="fas fa-undo-alt"></i> Recall
                                    </a>
                                </td>
                            </tr>
                            @php $sanding_history_total += $sanding_history->value; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>{{$sanding_history_total}}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Gift Receiving History -->
    @if($reciving_historys)
    <div class="row mt-4">
        <div class="col-xl-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-gift mr-2"></i>Gift Receiving History  @if($old_sum_reciving_historys>0 )(In Total +{{$old_sum_reciving_historys}} From Archive Gift) @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                  <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Sender ID</th>
                                <th>Gift Name</th>
                                <th>Amount</th>
                                <th>Time</th>
                                <th>Device ID</th>
                                <th>Recall</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; $reciving_history_total=$old_sum_reciving_historys; @endphp
                            @foreach($reciving_historys as $reciving_history)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{$reciving_history->sander_id}}</td>
                                <td>{{$reciving_history->name}}</td>
                                <td><span class="text-success">{{$reciving_history->value}}</span></td>
                                <td>{{$reciving_history->date}}</td>
                                <td>{{$reciving_history->imie}}</td>
                                <td>
                                    <a href="{{URL::to('gift_recall',$reciving_history->id)}}" class="btn btn-sm btn-danger">
                                        <i class="fas fa-undo-alt"></i> Recall
                                    </a>
                                </td>
                            </tr>
                            @php $reciving_history_total += $reciving_history->value; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>{{$reciving_history_total}}</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
@else
<div class="body-content">
    <div class="alert alert-warning mb-0">Profile search is not allowed for this account.</div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    function hideProfileLoader() {
        var loader = document.querySelector('.page-loader-wrapper');
        if (loader) {
            loader.style.opacity = '0';
            loader.style.display = 'none';
            loader.style.pointerEvents = 'none';
        }
    }

    window.addEventListener('load', hideProfileLoader);
    setTimeout(hideProfileLoader, 1200);
    setTimeout(hideProfileLoader, 3000);

    if (!window.jQuery) {
        document.addEventListener('DOMContentLoaded', hideProfileLoader);
        return;
    }

    jQuery(function ($) {
        try {
            if ($.fn.DataTable) {
                $('.datatable').each(function () {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50], [10, 25, 50]],
                            deferRender: true,
                            autoWidth: false,
                            dom: '<"row"<"col-sm-12 col-md-6"f><"col-sm-12 col-md-6"l>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                            language: {
                                search: "_INPUT_",
                                searchPlaceholder: "Search...",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                paginate: {
                                    first: '<i class="fas fa-angle-double-left"></i>',
                                    previous: '<i class="fas fa-angle-left"></i>',
                                    next: '<i class="fas fa-angle-right"></i>',
                                    last: '<i class="fas fa-angle-double-right"></i>'
                                }
                            },
                            ordering: false,
                            order: [],
                            columnDefs: [
                                { targets: '_all', orderable: false }
                            ]
                        });
                    }
                });
            }
        } catch (error) {
            if (window.console) {
                console.warn('Profile DataTable skipped:', error);
            }
        }

        $('.modal').on('show.bs.modal', function() {
            $(this).find('.modal-content').css('animation', 'fadeIn 0.3s ease');
        });

        $('.btn-danger[href*="vips_remove"], .btn-danger[href*="gift_recall"]').on('click', function(e) {
            if (!confirm('Are you sure?')) {
                e.preventDefault();
            }
        });

        $('img').on('error', function() {
            if (this.dataset.fallbackApplied) {
                return;
            }
            this.dataset.fallbackApplied = '1';
            this.src = '/store/profile/default.png';
        });

        hideProfileLoader();
    });
})();
</script>
@endpush
