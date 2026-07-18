@extends('backend.layouts.main')
@section('title')
Admin Settings
@endsection
@section('content')
<style>
  .admin-tools-page {
    padding: 24px;
    background:
      radial-gradient(circle at 12% 12%, rgba(35, 219, 255, .16), transparent 28%),
      radial-gradient(circle at 85% 8%, rgba(255, 122, 198, .18), transparent 30%),
      linear-gradient(135deg, #f7fbff 0%, #eef4ff 44%, #fff8fb 100%);
    min-height: calc(100vh - 70px);
  }
  .admin-hero {
    position: relative;
    overflow: hidden;
    border-radius: 26px;
    padding: 28px;
    color: #fff;
    background: linear-gradient(135deg, #071321 0%, #123b79 54%, #7a1f8f 100%);
    box-shadow: 0 22px 55px rgba(15, 32, 70, .22);
    margin-bottom: 22px;
  }
  .admin-hero:before {
    content: "";
    position: absolute;
    right: -90px;
    top: -120px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0, 229, 255, .35), rgba(0, 229, 255, 0) 70%);
  }
  .admin-hero:after {
    content: "";
    position: absolute;
    left: 42%;
    bottom: -150px;
    width: 400px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 214, 102, .24), rgba(255, 214, 102, 0) 68%);
  }
  .admin-hero-inner {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }
  .admin-kicker {
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 12px;
    font-weight: 900;
    color: #8eeaff;
    margin-bottom: 8px;
  }
  .admin-title {
    margin: 0;
    color: #fff;
    font-size: 34px;
    font-weight: 900;
    line-height: 1.05;
  }
  .admin-copy {
    color: #dcecff;
    margin: 10px 0 0;
    max-width: 720px;
  }
  .admin-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 10px 14px;
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .22);
    color: #fff;
    font-weight: 800;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
  }
  .admin-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(320px, .9fr);
    gap: 18px;
    align-items: start;
  }
  .admin-panel {
    background: rgba(255, 255, 255, .92);
    border: 1px solid rgba(188, 204, 230, .78);
    border-radius: 24px;
    box-shadow: 0 18px 45px rgba(26, 46, 90, .13);
    overflow: hidden;
  }
  .admin-panel-head {
    padding: 18px 20px;
    color: #fff;
    background: linear-gradient(135deg, #1c64f2 0%, #7c3aed 55%, #db2777 100%);
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
  }
  .admin-panel-head h3 {
    margin: 0;
    color: #fff;
    font-size: 20px;
    font-weight: 900;
  }
  .admin-panel-head span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  .admin-panel-body {
    padding: 20px;
  }
  .admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }
  .admin-field-full {
    grid-column: 1 / -1;
  }
  .admin-field label {
    display: block;
    color: #26374f;
    font-weight: 900;
    margin-bottom: 6px;
    font-size: 13px;
  }
  .admin-field .form-control {
    height: 44px;
    border-radius: 14px;
    border: 1px solid #cfd9ea;
    box-shadow: none;
    background: #fbfdff;
  }
  .admin-field .form-control:focus {
    border-color: #4f7cff;
    box-shadow: 0 0 0 4px rgba(79, 124, 255, .14);
  }
  .admin-field .form-control:disabled {
    background: #eef3f9;
    color: #8a97aa;
    cursor: not-allowed;
  }
  .admin-btn {
    border: 0;
    border-radius: 16px;
    padding: 13px 18px;
    font-weight: 900;
    color: #fff;
    background: linear-gradient(135deg, #05c7ff, #3758f9 48%, #8b5cf6);
    box-shadow: 0 12px 26px rgba(55, 88, 249, .24);
    cursor: pointer;
    width: 100%;
  }
  .admin-btn-green {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    box-shadow: 0 12px 26px rgba(22, 163, 74, .22);
  }
  .admin-note {
    margin-top: 14px;
    padding: 13px 14px;
    border-radius: 16px;
    background: #eef6ff;
    border: 1px solid #cfe3ff;
    color: #365477;
    font-size: 12px;
    line-height: 1.6;
  }
  .admin-side-card {
    padding: 18px;
  }
  .admin-rule {
    display: flex;
    gap: 12px;
    padding: 13px 0;
    border-bottom: 1px solid #edf2f8;
  }
  .admin-rule:last-child {
    border-bottom: 0;
  }
  .admin-rule-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    flex: 0 0 auto;
    font-weight: 900;
  }
  .admin-rule strong {
    display: block;
    color: #1f2a44;
    margin-bottom: 2px;
  }
  .admin-rule small {
    color: #64748b;
  }
  .admin-powered {
    color: rgba(15, 23, 42, .15);
    font-size: 5px;
    text-align: center;
    padding-top: 14px;
    font-weight: 900;
  }
  @media (max-width: 991px) {
    .admin-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 640px) {
    .admin-tools-page { padding: 14px; }
    .admin-title { font-size: 26px; }
    .admin-form-grid { grid-template-columns: 1fr; }
  }
</style>

<div class="body-content admin-tools-page">
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="admin-hero">
    <div class="admin-hero-inner">
      <div>
        <div class="admin-kicker">QueenLive Settings</div>
        <h1 class="admin-title">Admin</h1>
        <p class="admin-copy">Manage admin roles, user permissions, country admin setup, and email identity from one control page.</p>
      </div>
      <div class="admin-chip">SAFE ADMIN TOOLS</div>
    </div>
  </div>

  <div class="admin-grid">
    <div class="admin-panel">
      <div class="admin-panel-head">
        <div>
          <span>Country Role Generator</span>
          <h3>Country Admin Panel</h3>
        </div>
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="admin-panel-body">
        <form action="{{URL::to('admin-country-admin-store')}}" method="post">
          @csrf
          <div class="admin-form-grid">
            <div class="admin-field admin-field-full">
              <label>Existing user ID or email</label>
              <input type="text" name="country_target_user" class="form-control" value="{{old('country_target_user')}}" placeholder="Use existing user ID or email">
            </div>
            <div class="admin-field">
              <label>Country</label>
              <select name="country_id" class="form-control" required>
                <option value="1" @if(old('country_id') == '1') selected @endif>Bangladesh</option>
                <option value="2" @if(old('country_id') == '2') selected @endif>India</option>
                <option value="3" @if(old('country_id') == '3') selected @endif>Pakistan</option>
              </select>
            </div>
            <div class="admin-field">
              <label>New user name</label>
              <input type="text" name="country_name" class="form-control" value="{{old('country_name')}}" placeholder="Required for new user">
            </div>
            <div class="admin-field">
              <label>New user email</label>
              <input type="email" name="country_email" class="form-control" value="{{old('country_email')}}" placeholder="Required for new user">
            </div>
            <div class="admin-field">
              <label>Phone</label>
              <input type="text" name="country_phone" class="form-control" value="{{old('country_phone')}}" placeholder="Optional">
            </div>
            <div class="admin-field">
              <label>Password</label>
              <input type="password" name="country_password" class="form-control" placeholder="Required for new / optional reset">
            </div>
            <div class="admin-field">
              <label>&nbsp;</label>
              <button type="submit" class="admin-btn">Save Country Admin</button>
            </div>
          </div>
          <div class="admin-note">
            Existing user ID/email promotes that user. If existing user is blank, new user name, email, and password create a new Country Admin. The server always saves <b>is_admin=2</b>, <b>role=2</b>, <b>status=1</b>, and selected <b>country_id</b>.
          </div>
        </form>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-head">
        <div>
          <span>Rules</span>
          <h3>Input Safety</h3>
        </div>
        <i class="fas fa-lock"></i>
      </div>
      <div class="admin-side-card">
        <div class="admin-rule">
          <div class="admin-rule-icon">1</div>
          <div><strong>Existing mode</strong><small>Typing existing ID/email disables all new-user fields.</small></div>
        </div>
        <div class="admin-rule">
          <div class="admin-rule-icon">2</div>
          <div><strong>New user mode</strong><small>Typing new user name/email/password disables existing user input.</small></div>
        </div>
        <div class="admin-rule">
          <div class="admin-rule-icon">3</div>
          <div><strong>Server enforced</strong><small>Controller sets country admin role values; the browser cannot override them.</small></div>
        </div>
        <div class="admin-powered">Powered by JAMBOai</div>
      </div>
    </div>
  </div>

  <div class="admin-panel mt-4">
    <div class="admin-panel-head" style="background: linear-gradient(135deg, #7f1d1d, #dc2626);">
      <div>
        <span>Panel Access</span>
        <h3>Admin Role Control</h3>
      </div>
      <i class="fas fa-user-shield"></i>
    </div>
    <div class="admin-panel-body">
      <form action="{{URL::to('admin-user-role-store')}}" method="post">
        @csrf
        <div class="admin-form-grid">
          <div class="admin-field admin-field-full">
            <label>User ID or email *</label>
            <input type="text" name="admin_role_target_user" class="form-control" value="{{old('admin_role_target_user')}}" placeholder="Enter user ID or email" required>
          </div>
          <div class="admin-field">
            <label>Admin role *</label>
            <select name="admin_role" class="form-control" required>
              <option value="1" @if(old('admin_role') == '1') selected @endif>Main Admin</option>
              <option value="2" @if(old('admin_role') == '2') selected @endif>Country Admin</option>
              <option value="3" @if(old('admin_role') == '3') selected @endif>Sub Admin</option>
              <option value="0" @if(old('admin_role') == '0') selected @endif>Normal User</option>
            </select>
          </div>
          <div class="admin-field">
            <label>&nbsp;</label>
            <button type="submit" class="admin-btn" style="background: linear-gradient(135deg, #ef4444, #7f1d1d);">Save Admin Role</button>
          </div>
        </div>
        <div class="admin-note">
          Main Admin sets <b>is_admin=1</b>, <b>role=1</b>, and required panel powers. Country Admin sets <b>is_admin=2</b>, <b>role=2</b>. Sub Admin sets <b>is_admin=3</b>, <b>role=3</b>. Normal User removes panel admin access.
        </div>
      </form>
    </div>
  </div>

  <div class="admin-panel mt-4" id="permission-system">
    <div class="admin-panel-head" style="background: linear-gradient(135deg, #1d4ed8, #0f172a);">
      <div>
        <span>User Powers</span>
        <h3>Permission System</h3>
      </div>
      <i class="fas fa-sliders-h"></i>
    </div>
    <div class="admin-panel-body">
      <form action="{{URL::to('admin-user-permission-store')}}" method="post">
        @csrf
        @php
          $permissionFields = [
            'agora_access' => 'Agora Access',
            'brd_off_power' => 'Board Off Power',
            'sceen_short_power' => 'Screenshot Power',
            'kick_power' => 'Kick Power',
            'comment_mute_power' => 'Comment Mute Power',
            'is_invisible' => 'Invisible Power',
            'withdraw_active' => 'Withdraw Active',
            'is_host_id' => 'Host Active',
            'is_coin_protal_active' => 'Portal Active',
            'is_official_id' => 'Official ID',
            'is_official_frame' => 'Official Frame',
            'is_admin_frame' => 'Admin Frame',
            'lock_brd_entry' => 'Lock Room Entry',
            'auto_lock_status' => 'Auto Lock Status',
            'can_banned' => 'Can Ban',
            'can_call_cut' => 'Can Cut Call',
            'is_app_admin' => 'App Admin Flag',
            'is_bd_admin' => 'BD Admin Flag',
          ];
        @endphp
        <div class="admin-form-grid">
          <div class="admin-field admin-field-full">
            <label>User ID or email *</label>
            <input type="text" name="admin_permission_target_user" class="form-control" value="{{old('admin_permission_target_user')}}" placeholder="Enter user ID or email" required>
          </div>
          @foreach($permissionFields as $permissionKey => $permissionLabel)
            <div class="admin-field">
              <label>{{ $permissionLabel }}</label>
              <select name="permissions[{{ $permissionKey }}]" class="form-control">
                <option value="">No change</option>
                <option value="1" @if(old('permissions.'.$permissionKey) === '1') selected @endif>On</option>
                <option value="0" @if(old('permissions.'.$permissionKey) === '0') selected @endif>Off</option>
              </select>
            </div>
          @endforeach
          <div class="admin-field admin-field-full">
            <button type="submit" class="admin-btn" style="background: linear-gradient(135deg, #2563eb, #111827);">Save Permission System</button>
          </div>
        </div>
        <div class="admin-note">
          Use <b>No change</b> for powers you do not want to touch. This form updates only selected permission fields for the target user.
        </div>
      </form>
    </div>
  </div>

  <div class="admin-panel mt-4">
    <div class="admin-panel-head" style="background: linear-gradient(135deg, #0f766e, #16a34a);">
      <div>
        <span>User Identity</span>
        <h3>ID Swap</h3>
      </div>
      <i class="fas fa-random"></i>
    </div>
    <div class="admin-panel-body">
      <form action="{{URL::to('admin-user-email-change_store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="admin-form-grid">
          <div class="admin-field">
            <label for="user_id">Selected User ID *</label>
            <input type="number" name="user_id" class="form-control" placeholder="Selected User ID" value="" id="user_id" required>
          </div>
          <div class="admin-field">
            <label for="email">New Email *</label>
            <input type="email" name="email" class="form-control" placeholder="Enter New Email ID" value="" id="email" required>
          </div>
          <div class="admin-field admin-field-full">
            <button type="submit" class="admin-btn admin-btn-green">Submit ID Swap</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[action$="admin-country-admin-store"]').forEach(function (form) {
    var existing = form.querySelector('input[name="country_target_user"]');
    var newFields = Array.prototype.slice.call(form.querySelectorAll('input[name="country_name"], input[name="country_email"], input[name="country_phone"], input[name="country_password"]'));
    function hasNewValue() {
      return newFields.some(function (field) { return field.value.trim() !== ''; });
    }
    function syncCountryAdminInputs(source) {
      var hasExisting = existing && existing.value.trim() !== '';
      var hasNew = hasNewValue();
      if (source === 'existing' && hasExisting) {
        newFields.forEach(function (field) { field.value = ''; field.disabled = true; });
        existing.disabled = false;
        return;
      }
      if (source === 'new' && hasNew) {
        if (existing) { existing.value = ''; existing.disabled = true; }
        newFields.forEach(function (field) { field.disabled = false; });
        return;
      }
      if (existing) existing.disabled = hasNew;
      newFields.forEach(function (field) { field.disabled = hasExisting; });
    }
    if (existing) existing.addEventListener('input', function () { syncCountryAdminInputs('existing'); });
    newFields.forEach(function (field) { field.addEventListener('input', function () { syncCountryAdminInputs('new'); }); });
    syncCountryAdminInputs();
  });
});
</script>
@endsection
