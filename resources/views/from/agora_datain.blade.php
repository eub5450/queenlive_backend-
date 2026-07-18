<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
<title>BP Live Agora • Mobile & Desktop</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* ----------------------------------------------
   DUAL MODE WITH VISUAL CHECKING STATE
   MOBILE CARDS + DESKTOP TABLE - BOTH UPDATE WITH COLOR
---------------------------------------------- */
:root {
  --primary: #4361ee;
  --primary-dark: #3a56d4;
  --secondary: #7209b7;
  --success: #06d6a0;
  --danger: #ef476f;
  --warning: #ffd166;
  --dark: #0b132b;
  --gray: #6c757d;
  --light: #f8f9fa;
  --white: #ffffff;
  --checking-bg: #fff3cd;
  --checking-border: #ffc107;
  --updated-bg: #d1e7dd;
  --updated-border: #0f5132;
  --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
  --radius-sm: 12px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  --safe-top: env(safe-area-inset-top, 0px);
  --safe-bottom: env(safe-area-inset-bottom, 0px);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  -webkit-tap-highlight-color: transparent;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: linear-gradient(145deg, #f5f7ff 0%, #eef2f6 100%);
  color: var(--dark);
  min-height: 100vh;
  padding: var(--safe-top) 0 var(--safe-bottom) 0;
  line-height: 1.5;
}

/* App Container */
.app-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

/* ========== MOBILE VIEW (DEFAULT - CARDS) ========== */
.mobile-view {
  display: block;
}

/* ========== DESKTOP VIEW (HIDDEN ON MOBILE) ========== */
.desktop-view {
  display: none;
}

/* Header */
.app-header {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg) var(--spacing-md);
  box-shadow: var(--shadow-sm);
  text-align: center;
  position: relative;
  border: 1px solid rgba(255,255,255,0.5);
}

.app-header:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.app-header h1 {
  font-size: 2rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: var(--spacing-xs);
  letter-spacing: -0.5px;
}

.app-header p {
  color: var(--gray);
  font-size: 0.9rem;
  font-weight: 500;
}

/* ========== MOBILE STATS CARDS ========== */
.mobile-stats-grid {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.stat-item {
  background: var(--white);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-sm);
  border: 1px solid rgba(0,0,0,0.03);
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
}

.stat-item.checking {
  background: var(--checking-bg);
  border-color: var(--checking-border);
}

.stat-item.updated {
  background: var(--updated-bg);
  border-color: var(--updated-border);
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(145deg, var(--primary), var(--secondary));
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  flex-shrink: 0;
  box-shadow: 0 6px 12px rgba(67, 97, 238, 0.2);
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--gray);
  margin-bottom: 4px;
}

.stat-value {
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
  word-break: break-word;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--spacing-sm);
}

/* Mobile badges */
.code-badge-mobile {
  background: #e3f2fd;
  color: #1976d2;
  padding: 6px 14px;
  border-radius: 40px;
  font-weight: 700;
  font-family: 'SF Mono', 'Courier New', monospace;
  font-size: 1.1rem;
  letter-spacing: 2px;
  display: inline-block;
}

.password-mask-mobile {
  font-family: 'SF Mono', 'Courier New', monospace;
  background: #f1f3f5;
  padding: 6px 14px;
  border-radius: 40px;
  font-size: 0.95rem;
  border: 1px solid #dee2e6;
  display: inline-block;
}

.badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 40px;
  font-weight: 600;
  font-size: 0.95rem;
}

.bg-light {
  background: #f1f3f5;
  color: #2d3748;
}

/* ========== DESKTOP TABLE VIEW ========== */
.desktop-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  box-shadow: var(--shadow-sm);
  width: 100%;
  overflow-x: auto;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: var(--spacing-lg);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  color: var(--dark);
}

.section-title i {
  color: var(--primary);
}

.verification-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  min-width: 1200px;
}

.verification-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  padding: 16px 20px;
  text-align: left;
  border-bottom: 2px solid #dee2e6;
  font-size: 0.9rem;
}

.verification-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #e9ecef;
  color: #212529;
  vertical-align: middle;
  transition: all 0.3s ease;
}

.verification-table tr.checking td {
  background: var(--checking-bg);
}

.verification-table tr.updated td {
  background: var(--updated-bg);
}

.verification-table tr:hover {
  background-color: #f8f9fa;
}

.code-badge {
  background: #e3f2fd;
  color: #1976d2;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-family: monospace;
  font-size: 0.9rem;
  display: inline-block;
}

.password-mask {
  font-family: 'Courier New', monospace;
  background: #f1f3f5;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  border: 1px solid #dee2e6;
}

/* Checking Loader Animation */
.checking-loader {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #856404;
}

.loader-small {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(67,97,238,0.2);
  border-radius: 50%;
  border-top-color: var(--primary);
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 18px;
  border-radius: 40px;
  font-size: 0.85rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  background: white;
  color: var(--dark);
  border: 1px solid #e9ecef;
}

.btn i { font-size: 0.9rem; }

.btn-success {
  background: var(--success);
  color: white;
  border: none;
  box-shadow: 0 8px 16px rgba(6, 214, 160, 0.25);
}

.btn-primary {
  background: linear-gradient(145deg, var(--primary), var(--primary-dark));
  color: white;
  border: none;
  box-shadow: 0 8px 16px rgba(67, 97, 238, 0.25);
}

.btn-sm {
  padding: 6px 14px;
  font-size: 0.8rem;
}

.btn-lg {
  padding: 14px 24px;
  font-size: 1rem;
  width: 100%;
}

.btn:active {
  transform: scale(0.97);
}

.signup-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 40px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  box-shadow: 0 8px 16px rgba(40, 167, 69, 0.25);
  transition: all 0.2s;
}

.signup-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 20px rgba(40, 167, 69, 0.3);
}

.signup-btn:active {
  transform: translateY(0);
}

/* Form */
.form-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  box-shadow: var(--shadow-sm);
}

.form-title {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: var(--spacing-lg);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.form-group {
  margin-bottom: var(--spacing-md);
}

.form-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--gray);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.form-input {
  width: 100%;
  padding: 16px 18px;
  border: 1.5px solid #e9ecef;
  border-radius: 16px;
  font-size: 1rem;
  background: #fafbfc;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary);
  background: white;
  box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
}

/* Footer */
.app-footer {
  text-align: center;
  padding: var(--spacing-lg) var(--spacing-md);
  color: var(--gray);
  font-size: 0.8rem;
  font-weight: 500;
}

.text-muted { color: var(--gray); }

/* ========== MEDIA QUERIES ========== */
@media (max-width: 767px) {
  .mobile-view { display: block; }
  .desktop-view { display: none; }
}

@media (min-width: 768px) {
  .mobile-view { display: none; }
  .desktop-view { display: block; }
  
  .app-container {
    padding: var(--spacing-xl);
  }
  
  .form-row {
    display: flex;
    gap: var(--spacing-md);
  }
  
  .form-group {
    flex: 1;
  }
}

/* Toast Styling */
.toast {
    visibility: hidden;
    min-width: 300px;
    margin-left: -150px;
    background-color: #4caf50;
    color: white;
    text-align: center;
    border-radius: 50px;
    padding: 16px 24px;
    position: fixed;
    z-index: 9999;
    left: 50%;
    top: 20px;
    font-size: 16px;
    font-weight: 500;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    opacity: 0;
    transition: opacity 0.3s, top 0.3s;
}

.toast.show {
    visibility: visible;
    opacity: 1;
    top: 30px;
}

.toast.error {
    background-color: #dc3545;
}
</style>
</head>
<body>
<div class="app-container">

    <!-- ========== HEADER ========== -->
    <header class="app-header">
        <h1>BP Live Agora</h1>
        <p>Secure Account Management • Real-time Sync</p>
        <p>Today Entry : {{$today_data}}</p>
    </header>
    
    <div id="toast" class="toast"></div>
    
    <!-- ========== MOBILE VIEW (CARDS WITH CHECKING STATE) ========== -->
    <div class="mobile-view">
        <!-- Verification Code Card - Dynamic -->
        <div class="stat-item" id="mobile-code-card">
            <div class="stat-icon">
                <i class="fas fa-qrcode"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Verification Code</div>
                <div class="stat-value" id="mobile-code-value">
                    @if($verificationCode)
                        <span class="code-badge-mobile">{{ $verificationCode }}</span>
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $verificationCode }}', this)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    @else
                        <span class="text-muted">No verification code yet</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Received Time Card - Dynamic -->
        <div class="stat-item" id="mobile-time-card">
            <div class="stat-icon" style="background: linear-gradient(145deg, #f97316, #fb923c);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Received At</div>
                <div class="stat-value" id="mobile-time-value">
                    {{ $mailTime ?? '—' }}
                </div>
            </div>
        </div>

        <!-- First Name Card - Static -->
        <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(145deg, #4361ee, #4895ef);">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">First Name</div>
                <div class="stat-value">
                    @if($firstName ?? null)
                        <span class="badge bg-light">{{ $firstName }}</span>
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $firstName }}', this)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Last Name Card - Static -->
        <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(145deg, #7209b7, #b5179e);">
                <i class="fas fa-user-tag"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Last Name</div>
                <div class="stat-value">
                    @if($lastName ?? null)
                        <span class="badge bg-light">{{ $lastName }}</span>
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $lastName }}', this)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Company Name Card - Static -->
        <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(145deg, #059669, #10b981);">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Company</div>
                <div class="stat-value">
                    @if($companyWebsite ?? null)
                        <span class="badge bg-light" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">{{ $companyWebsite }}</span>
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $companyWebsite }}', this)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Email Address Card - Static -->
        <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(145deg, #0f172a, #1e293b);">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Email Address</div>
                <div class="stat-value" style="font-family: monospace; word-break: break-all;">
                    {{ $next_email->generated_email ?? '—' }}
                </div>
                @if($next_email->generated_email)
                    <div style="margin-top: 8px;">
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $next_email->generated_email }}', this)">
                            <i class="fas fa-copy"></i> Copy Email
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Password Card - Static -->
        <div class="stat-item">
            <div class="stat-icon" style="background: linear-gradient(145deg, #059669, #10b981);">
                <i class="fas fa-lock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Password</div>
                <div class="stat-value">
                    <span class="password-mask-mobile">{{ $next_email->login_password ?? '—' }}</span>
                </div>
                @if($next_email->login_password)
                    <div style="margin-top: 8px;">
                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $next_email->login_password }}', this)">
                            <i class="fas fa-copy"></i> Copy Password
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- NEW: Copy All Data & Open Agora Button -->
        <button class="signup-btn" onclick="copyAllDataAndOpenAgora()">
            <i class="fas fa-copy"></i> Copy All Data & Open Agora
        </button>

        <p style="font-size: 0.7rem; color: var(--gray); text-align: center; margin-top: 6px;">
            <i class="fas fa-shield-alt"></i> All form data will be copied to clipboard
        </p>
    </div>

    <!-- ========== DESKTOP VIEW (TABLE WITH CHECKING STATE) ========== -->
    <div class="desktop-view">
        <div class="desktop-card">
            <div class="section-title">
                <i class="fas fa-shield-alt"></i> Latest Agora Account Information
            </div>
            
            <table class="verification-table">
                <thead>
                    <tr>
                        <th>Verification Code</th>
                        <th>Received At</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Company</th>
                        <th>Email Address</th>
                        <th>Email Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="desktop-table-row">
                        <!-- Dynamic: Verification Code -->
                        <td>
                            <div id="desktop-code-container">
                                @if($verificationCode)
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="code-badge">{{ $verificationCode }}</span>
                                        <button class="btn btn-sm" onclick="copyToClipboard('{{ $verificationCode }}', this)">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted">No code</span>
                                @endif
                            </div>
                        </td>
                        <!-- Dynamic: Received Time -->
                        <td id="desktop-time-value">
                            {{ $mailTime ?? '—' }}
                        </td>
                        <!-- Static: First Name -->
                        <td>
                            @if($firstName ?? null)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span>{{ $firstName }}</span>
                                    <button class="btn btn-sm" onclick="copyToClipboard('{{ $firstName }}', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <!-- Static: Last Name -->
                        <td>
                            @if($lastName ?? null)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span>{{ $lastName }}</span>
                                    <button class="btn btn-sm" onclick="copyToClipboard('{{ $lastName }}', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <!-- Static: Company -->
                        <td>
                            @if($companyWebsite ?? null)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;" title="{{ $companyWebsite }}">{{ $companyWebsite }}</span>
                                    <button class="btn btn-sm" onclick="copyToClipboard('{{ $companyWebsite }}', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <!-- Static: Email -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-family: monospace;">{{ $next_email->generated_email ?? '—' }}</span>
                                @if($next_email->generated_email)
                                    <button class="btn btn-sm" onclick="copyToClipboard('{{ $next_email->generated_email }}', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <!-- Static: Password -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span class="password-mask">{{ $next_email->login_password ?? '—' }}</span>
                                @if($next_email->login_password)
                                    <button class="btn btn-sm" onclick="copyToClipboard('{{ $next_email->login_password }}', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <!-- NEW: Desktop Action -->
                        <td>
                            <button class="signup-btn" onclick="copyAllDataAndOpenAgora()" style="padding: 8px 16px; font-size: 14px;">
                                <i class="fas fa-copy"></i> Copy & Open
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========== NEW AGORA ACCOUNT FORM ========== -->
    <div class="form-card">
        <div class="form-title">
            <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Create New Agora Account
        </div>
        
        <form action="{{URL::to('adata-agora_account_store')}}" method="post">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Agora AppId *</label>
                    <input type="text" name="appId" class="form-input" placeholder="Enter AppId" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Agora AppCertificate *</label>
                    <input type="text" name="appCertificate" class="form-input" placeholder="Enter AppCertificate" required>
                </div>
            </div>
            
            <!-- Hidden fields -->
            <input type="hidden" name="AgoraEmail" id="agoraEmailInput" value="{{ $next_email->generated_email ?? '' }}">
            <input type="hidden" name="AgoraEmailPassword" id="agoraPasswordInput" value="{{ $next_email->login_password ?? '' }}">
            
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" style="margin-top: var(--spacing-md);">
                <i class="fas fa-cloud-upload-alt"></i> Create Account
            </button>
        </form>
    </div>

    <!-- ========== FOOTER ========== -->
    <footer class="app-footer">
        <i class="fas fa-shield-alt"></i> BP Live Agora System • Auto-refresh every 10s
        <br>© {{ date('Y') }} All rights reserved
    </footer>

</div>

<script>
// ---------- GLOBAL COPY FUNCTION ----------
function copyToClipboard(text, buttonElement) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
        buttonElement.style.background = '#06d6a0';
        buttonElement.style.color = 'white';
        buttonElement.style.border = 'none';
        
        setTimeout(() => {
            buttonElement.innerHTML = originalHTML;
            buttonElement.style.background = '';
            buttonElement.style.color = '';
            buttonElement.style.border = '';
        }, 2000);
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('✓ Copied to clipboard', 2000);
    });
}

// ---------- NEW FUNCTION: COPY ALL DATA AND OPEN AGORA ----------
function copyAllDataAndOpenAgora() {
    // Get all the data from your page (using Laravel variables)
    const firstName = @json($firstName ?? '');
    const lastName = @json($lastName ?? '');
    const company = @json($companyWebsite ?? '');
    const email = @json($next_email->generated_email ?? '');
    const password = @json($next_email->login_password ?? '');
    
    // Format the data nicely for easy copying
    const signupData = `🚀 AGORA SIGNUP FORM DATA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👤 First Name: ${firstName}
👤 Last Name: ${lastName}
🏢 Company: ${company}
📧 Email: ${email}
🔑 Password: ${password}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👉 PASTE THESE VALUES INTO THE AGORA FORM FIELDS`;

    // Copy to clipboard
    navigator.clipboard.writeText(signupData).then(() => {
        // Show success message
        showToast("✅ All data copied! Opening Agora...", 3000);
        
        // Open Agora in new tab after a short delay
        setTimeout(() => {
            window.open("https://sso2.agora.io/en/v6/signup", "_blank");
        }, 500);
    }).catch(() => {
        showToast("❌ Failed to copy data. Please try again.", 3000);
    });
}

// ---------- FORM SUBMIT LOADER ----------
document.querySelector('form')?.addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.innerHTML = '<span class="loader-small" style="margin-right: 8px;"></span> Creating...';
        submitBtn.disabled = true;
    }
});

// ---------- AUTO-REFRESH VERIFICATION (ONLY CODE & TIME) ----------
document.addEventListener('DOMContentLoaded', function() {
    fetchVerificationData();
    setInterval(fetchVerificationData, 10000);
});

function fetchVerificationData() {
    // Show CHECKING state only for code and time cards
    showCheckingState();
    
    fetch('{{ url("/adata-verification-check") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update only verification code and time
        updateVerificationData(data);
        // Show UPDATED state
        showUpdatedState();
        // Remove color after 1 second
        setTimeout(removeHighlight, 1000);
    })
    .catch(error => {
        console.error('Fetch error:', error);
        removeHighlight();
    });
}

function showCheckingState() {
    // Only add checking class to code and time cards
    const mobileCodeCard = document.getElementById('mobile-code-card');
    const mobileTimeCard = document.getElementById('mobile-time-card');
    
    if (mobileCodeCard) {
        mobileCodeCard.classList.add('checking');
        const mobileCodeValue = document.getElementById('mobile-code-value');
        if (mobileCodeValue) {
            mobileCodeValue.innerHTML = `
                <span class="checking-loader">
                    <span class="loader-small"></span>
                    <span style="color: #856404;">Checking for code...</span>
                </span>
            `;
        }
    }
    
    if (mobileTimeCard) {
        mobileTimeCard.classList.add('checking');
        const mobileTimeValue = document.getElementById('mobile-time-value');
        if (mobileTimeValue) {
            mobileTimeValue.innerHTML = `
                <span class="checking-loader">
                    <span class="loader-small"></span>
                    <span style="color: #856404;">Checking...</span>
                </span>
            `;
        }
    }
    
    // Desktop - Add checking class to table row (affects entire row)
    const desktopRow = document.getElementById('desktop-table-row');
    if (desktopRow) {
        desktopRow.classList.add('checking');
        
        // Show loader in desktop code cell
        const desktopCode = document.getElementById('desktop-code-container');
        if (desktopCode) {
            desktopCode.innerHTML = `
                <span class="checking-loader">
                    <span class="loader-small"></span>
                    <span style="color: #856404;">Checking...</span>
                </span>
            `;
        }
        
        // Show loader in desktop time cell
        const desktopTime = document.getElementById('desktop-time-value');
        if (desktopTime) {
            desktopTime.innerHTML = `
                <span class="checking-loader">
                    <span class="loader-small"></span>
                    <span style="color: #856404;">Checking...</span>
                </span>
            `;
        }
    }
}

function showUpdatedState() {
    // Only add updated class to code and time cards
    const mobileCodeCard = document.getElementById('mobile-code-card');
    const mobileTimeCard = document.getElementById('mobile-time-card');
    
    if (mobileCodeCard) {
        mobileCodeCard.classList.remove('checking');
        mobileCodeCard.classList.add('updated');
    }
    
    if (mobileTimeCard) {
        mobileTimeCard.classList.remove('checking');
        mobileTimeCard.classList.add('updated');
    }
    
    // Desktop - Add updated class to table row
    const desktopRow = document.getElementById('desktop-table-row');
    if (desktopRow) {
        desktopRow.classList.remove('checking');
        desktopRow.classList.add('updated');
    }
}

function removeHighlight() {
    // Only remove updated class from code and time cards
    const mobileCodeCard = document.getElementById('mobile-code-card');
    const mobileTimeCard = document.getElementById('mobile-time-card');
    
    if (mobileCodeCard) {
        mobileCodeCard.classList.remove('updated');
    }
    
    if (mobileTimeCard) {
        mobileTimeCard.classList.remove('updated');
    }
    
    // Desktop - Remove updated class from table row
    const desktopRow = document.getElementById('desktop-table-row');
    if (desktopRow) {
        desktopRow.classList.remove('updated');
    }
}

function updateVerificationData(data) {
    // Update mobile verification code
    const mobileCode = document.getElementById('mobile-code-value');
    if (mobileCode) {
        if (data.verificationCode) {
            mobileCode.innerHTML = `
                <span class="code-badge-mobile">${data.verificationCode}</span>
                <button class="btn btn-sm" onclick="copyToClipboard('${data.verificationCode}', this)">
                    <i class="fas fa-copy"></i> Copy
                </button>
            `;
        } else {
            mobileCode.innerHTML = `<span class="text-muted">No verification code yet</span>`;
        }
    }
    
    // Update mobile received time
    const mobileTime = document.getElementById('mobile-time-value');
    if (mobileTime) {
        mobileTime.innerHTML = data.mailTime || '—';
    }
    
    // Update desktop verification code
    const desktopCode = document.getElementById('desktop-code-container');
    if (desktopCode) {
        if (data.verificationCode) {
            desktopCode.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="code-badge">${data.verificationCode}</span>
                    <button class="btn btn-sm" onclick="copyToClipboard('${data.verificationCode}', this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            `;
        } else {
            desktopCode.innerHTML = `<span class="text-muted">No code</span>`;
        }
    }
    
    // Update desktop time
    const desktopTime = document.getElementById('desktop-time-value');
    if (desktopTime) {
        desktopTime.innerHTML = data.mailTime || '—';
    }
}

// Toast notification function
function showToast(message, duration = 3000) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "toast show";
    setTimeout(() => {
        toast.className = "toast";
    }, duration);
}

// Touch optimization
document.addEventListener('touchstart', function(){}, {passive: true});
</script>

</body>
</html>