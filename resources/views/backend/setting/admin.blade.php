@extends('backend.layouts.main')
@section('title')Admin Users@endsection
@section('content')
<style>
.jambo-admin-page{background:#f3f7fb;border-radius:14px;padding:14px}.jambo-hero{background:linear-gradient(135deg,#08172d,#0c315f 56%,#1765a6);border-radius:14px;color:#fff;padding:18px 20px;margin-bottom:16px;box-shadow:0 10px 28px rgba(8,23,45,.18)}.jambo-hero h4{margin:0;font-weight:800;color:#fff}.jambo-hero p{margin:6px 0 0;color:#d9edff}.jambo-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-bottom:14px}.jambo-toolbar form{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.jambo-panel{border:1px solid #d8e6f3;border-radius:14px;background:#fff;box-shadow:0 8px 20px rgba(20,54,92,.08);overflow:hidden;margin-bottom:16px}.jambo-panel-head{padding:14px 16px;border-bottom:1px solid #e7eef7;display:flex;justify-content:space-between;align-items:center;gap:10px;background:#fbfdff}.jambo-panel-head h5{margin:0;color:#17365d;font-weight:800}.permission-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}.permission-box{border:1px solid #dce8f4;border-radius:12px;padding:10px;background:#f8fbff}.permission-box h5{margin:0 0 8px;font-size:13px;color:#0d3b66;font-weight:800}.permission-item{display:flex;gap:7px;align-items:flex-start;font-size:12px;color:#263b53;margin-bottom:6px;line-height:1.25;cursor:pointer}.permission-item input{margin-top:2px}.admin-permission-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:8px;align-items:center;margin-bottom:12px}.admin-permission-actions .btn{min-height:38px}.preset-row{display:flex;flex-wrap:wrap;gap:6px;margin:8px 0 12px}.preset-row .btn{border-radius:20px}.admin-table{margin-bottom:0;background:#fff}.admin-table thead th{background:#102a49;color:#eaf6ff;border-color:#102a49;vertical-align:middle}.admin-table tbody td{vertical-align:top;color:#243447}.admin-user-title{font-weight:800;color:#16395f}.admin-user-sub{color:#64748b;font-size:12px;word-break:break-all}.access-badge{display:inline-block;border-radius:20px;padding:5px 10px;background:#e9f4ff;color:#0d4d82;font-size:12px;font-weight:700}.perm-chip{display:inline-block;border-radius:20px;padding:4px 9px;background:#edf7f0;color:#166534;font-size:12px;font-weight:700}.jambo-permission-panel{border:1px solid #dde8f5;border-radius:12px;background:#fbfdff;padding:0}.jambo-permission-panel summary{cursor:pointer;padding:10px 12px;color:#14365c;font-weight:800;list-style:none}.jambo-permission-panel summary::-webkit-details-marker{display:none}.jambo-permission-panel summary:after{content:'Open';float:right;color:#0b84d8;font-size:12px}.jambo-permission-panel[open] summary:after{content:'Close'}.jambo-permission-body{padding:0 12px 12px}.jambo-danger-zone{display:flex;justify-content:flex-end;margin-top:8px}.country-admin-card{border:1px solid #c7ddff;border-radius:14px;padding:14px;background:linear-gradient(135deg,#f7fbff,#eef6ff);box-shadow:0 8px 18px rgba(22,78,155,.08)}.country-admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;align-items:end}.country-admin-note{font-size:12px;color:#48637f;margin-top:8px}@media(max-width:768px){.admin-permission-actions{grid-template-columns:1fr}.jambo-toolbar{display:block}.jambo-toolbar form{margin-bottom:8px}}
</style>
@php
    $modeLabels = [
        'admin' => 'Admin (is_admin = 1)',
        'subadmin' => 'Sub Admin (is_admin = 3)',
        'country' => 'Country Admin (is_admin = 2)',
        'normal' => 'Normal User (is_admin = 0)',
    ];
@endphp
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <section class="forms">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="jambo-admin-page">
                                <div class="jambo-hero">
                                    <h4>QueenLive Admin Permission Control</h4>
                                    <p>Set Normal, Admin, Sub Admin, or Country Admin access. Sub Admin saves as <code style="color:#fff;">is_admin=3</code>; Country Admin saves as <code style="color:#fff;">is_admin=2</code>, <code style="color:#fff;">role=2</code>, and selected <code style="color:#fff;">country_id</code>.</p>
                                </div>
                                <div class="jambo-toolbar">
                                    <form action="{{URL::to('setting/admin')}}" method="get">
                                        <input type="text" name="q" value="{{$q}}" class="form-control" placeholder="Search ID, name, email">
                                        <button type="submit" class="btn btn-info">Search</button>
                                        @if($q !== '')
                                            <a href="{{URL::to('setting/admin')}}" class="btn btn-default">Reset</a>
                                        @endif
                                    </form>
                                    <span class="access-badge">Table: adminparmisiton</span>
                                </div>

                                <div class="jambo-panel">
                                    <div class="jambo-panel-head">
                                        <h5>Country Admin Panel</h5>
                                        <span class="perm-chip">{{$countries->count()}} countries</span>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{URL::to('setting/country-admin-store')}}" method="post" class="country-admin-card">
                                            @csrf
                                            <div class="country-admin-grid">
                                                <div>
                                                    <label>Existing user ID or email</label>
                                                    <input type="text" name="target_user" class="form-control" value="{{old('target_user')}}" placeholder="Select existing user">
                                                </div>
                                                <div>
                                                    <label>Country</label>
                                                    <select name="country_id" class="form-control" required>
                                                        @foreach($countries as $country)
                                                            <option value="{{$country->id}}" @if((int) old('country_id', 1) === (int) $country->id) selected @endif>{{ucfirst($country->name)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label>New user name</label>
                                                    <input type="text" name="name" class="form-control" value="{{old('name')}}" placeholder="Required only for new">
                                                </div>
                                                <div>
                                                    <label>New user email</label>
                                                    <input type="email" name="email" class="form-control" value="{{old('email')}}" placeholder="Required only for new">
                                                </div>
                                                <div>
                                                    <label>Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="{{old('phone')}}" placeholder="Optional">
                                                </div>
                                                <div>
                                                    <label>Password</label>
                                                    <input type="password" name="password" class="form-control" placeholder="Required for new / optional reset">
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary btn-block">Save Country Admin</button>
                                                </div>
                                            </div>
                                            <div class="country-admin-note">If existing user is provided, the user is promoted to Country Admin. If existing user is blank, name, email, and password create a new Country Admin. Controller always saves <b>is_admin=2</b>, <b>role=2</b>, <b>status=1</b>, and selected <b>country_id</b>.</div>
                                        </form>
                                        <div style="font-size:10px;text-align:center;color:#999;padding-top:12px;">Powered by JAMBOai</div>
                                    </div>
                                </div>

                                <div class="jambo-panel" style="margin-bottom:16px;">
                                    <div class="jambo-panel-head"><h5>Role Permission Presets</h5><span class="perm-chip">Default permissions per role</span></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4"><div style="background:#e8f5e9;border-radius:8px;padding:10px;"><b style="color:#1b5e20;">Admin</b><div style="font-size:12px;color:#388e3c;margin-top:3px;">All {{count($adminPreset)}} permissions · full access</div></div></div>
                                            <div class="col-md-4"><div style="background:#fff3e0;border-radius:8px;padding:10px;"><b style="color:#e65100;">Country Admin</b><div style="font-size:12px;color:#f57c00;margin-top:3px;">{{count($countryAdminPreset ?? [])}} permissions · scoped to assigned country only</div></div></div>
                                            <div class="col-md-4"><div style="background:#e3f2fd;border-radius:8px;padding:10px;"><b style="color:#0d47a1;">Sub Admin</b><div style="font-size:12px;color:#1565c0;margin-top:3px;">{{count($subadminPreset)}} permissions · limited access</div></div></div>
                                        </div>
                                        <p style="font-size:11px;color:#999;margin:8px 0 0;">Presets apply automatically when assigning roles. Add extra per-user permissions using the form below.</p>
                                    </div>
                                </div>

                                <div class="jambo-panel">
                                    <div class="jambo-panel-head">
                                        <h5>Add or Update Admin</h5>
                                        <span class="perm-chip">MFA/session protected</span>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{URL::to('setting/admin-update')}}" method="post" class="jambo-permission-form">
                                            @csrf
                                            <div class="admin-permission-actions">
                                                <input type="text" name="target_user" class="form-control" placeholder="User ID or email" required>
                                                <select name="admin_mode" class="form-control" required>
                                                    <option value="normal">Normal User (is_admin = 0)</option>
                                                    <option value="admin">Admin (is_admin = 1)</option>
                                                    <option value="country">Country Admin (is_admin = 2)</option>
                                                    <option value="subadmin">Sub Admin (is_admin = 3)</option>
                                                </select>
                                                <select name="country_id" class="form-control">
                                                    @foreach($countries as $country)
                                                        <option value="{{$country->id}}">{{ucfirst($country->name)}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="password" name="password" class="form-control" placeholder="New password optional">
                                                <button type="submit" class="btn btn-primary">Save Permission</button>
                                            </div>
                                            <div class="preset-row">
                                                <button type="button" class="btn btn-xs btn-success js-permission-preset" data-preset="admin" data-mode="admin">Admin Full</button>
                                                <button type="button" class="btn btn-xs btn-info js-permission-preset" data-preset="subadmin" data-mode="subadmin">Sub Admin Basic</button>
                                                <button type="button" class="btn btn-xs btn-warning js-permission-preset" data-preset="country">Country Admin</button>
                                                <button type="button" class="btn btn-xs btn-default js-permission-preset" data-preset="clear">Clear All</button>
                                            </div>
                                            <details class="jambo-permission-panel" open>
                                                <summary>Select permissions for this user</summary>
                                                <div class="jambo-permission-body">
                                                    <div class="permission-grid">
                                                        @foreach($permissionGroups as $groupName => $items)
                                                            <div class="permission-box" data-group="{{ Str::slug($groupName) }}">
                                                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                                                    <h5 style="margin:0;">{{$groupName}}</h5>
                                                                    <button type="button" class="btn btn-xs btn-default js-group-toggle" data-group="{{ Str::slug($groupName) }}" style="font-size:10px;padding:2px 6px;">All</button>
                                                                </div>
                                                                @foreach($items as $key => $label)
                                                                    <label class="permission-item"><input type="checkbox" name="permissions[]" value="{{$key}}"><span>{{$label}}</span></label>
                                                                @endforeach
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </details>
                                        </form>
                                    </div>
                                </div>

                                <div class="jambo-panel">
                                    <div class="jambo-panel-head">
                                        <h5>Admin / Sub Admin Users</h5>
                                        <span class="perm-chip">{{$users->count()}} users</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered admin-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:240px;">User</th>
                                                    <th style="width:190px;">Access</th>
                                                    <th>Permissions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($users as $user)
                                                    @php
                                                        $row = $permissionRows->get($user->id);
                                                        $selected = \App\Http\Controllers\Admin\AdminSettingController::perms($row);
                                                        $mode = $row ? $row->admin_mode : ((int) $user->is_admin === 2 ? 'country' : ((int) $user->is_admin === 3 ? 'subadmin' : ((int) $user->is_admin === 1 ? 'admin' : 'normal')));
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="admin-user-title">#{{$user->id}} - {{$user->name}}</div>
                                                            <div class="admin-user-sub">{{$user->email}}</div>
                                                        </td>
                                                        <td>
                                                            <span class="access-badge">{{$modeLabels[$mode] ?? $mode}}</span>
                                                            <div style="margin-top:5px;color:#64748b;font-size:12px;">DB is_admin={{(int)$user->is_admin}}</div>
                                                            <div style="margin-top:5px;color:#64748b;font-size:12px;">Country ID={{(int)$user->country_id}}</div>
                                                            @if((int)$user->is_admin === 2 && $user->country_id)
                                                            <span style="background:#fff3e0;color:#e65100;border-radius:10px;padding:2px 7px;font-size:11px;font-weight:600;display:inline-block;margin-top:2px;">Country: {{$user->country->name ?? 'ID '.$user->country_id}}</span>
                                                            @endif
                                                            <div style="margin-top:8px;"><span class="perm-chip">{{count($selected)}} permissions on</span></div>
                                                        </td>
                                                        <td>
                                                            <form action="{{URL::to('setting/admin-update')}}" method="post" class="jambo-permission-form">
                                                                @csrf
                                                                <input type="hidden" name="target_user" value="{{$user->id}}">
                                                                <div class="admin-permission-actions">
                                                                    <select name="admin_mode" class="form-control" required>
                                                                        <option value="normal" @if($mode === 'normal') selected @endif>Normal User (is_admin = 0)</option>
                                                                        <option value="admin" @if($mode === 'admin') selected @endif>Admin (is_admin = 1)</option>
                                                                        <option value="country" @if($mode === 'country') selected @endif>Country Admin (is_admin = 2)</option>
                                                                        <option value="subadmin" @if($mode === 'subadmin') selected @endif>Sub Admin (is_admin = 3)</option>
                                                                    </select>
                                                                    <select name="country_id" class="form-control">
                                                                        @foreach($countries as $country)
                                                                            <option value="{{$country->id}}" @if((int)$user->country_id === (int)$country->id) selected @endif>{{ucfirst($country->name)}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="password" name="password" class="form-control" placeholder="New password optional">
                                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                                </div>
                                                                <div class="preset-row">
                                                                    <button type="button" class="btn btn-xs btn-success js-permission-preset" data-preset="admin" data-mode="admin">Admin Full</button>
                                                                    <button type="button" class="btn btn-xs btn-info js-permission-preset" data-preset="subadmin" data-mode="subadmin">Sub Admin Basic</button>
                                                                    <button type="button" class="btn btn-xs btn-warning js-permission-preset" data-preset="country">Country Admin</button>
                                                                    <button type="button" class="btn btn-xs btn-default js-permission-preset" data-preset="clear">Clear All</button>
                                                                </div>
                                                                <details class="jambo-permission-panel">
                                                                    <summary>Edit Admin permissions</summary>
                                                                    <div class="jambo-permission-body">
                                                                        <div class="permission-grid">
                                                                            @foreach($permissionGroups as $groupName => $items)
                                                                                <div class="permission-box" data-group="{{ Str::slug($groupName) }}">
                                                                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;"><h5 style="margin:0;">{{$groupName}}</h5><button type="button" class="btn btn-default btn-xs js-group-toggle" data-group="{{ Str::slug($groupName) }}" style="font-size:10px;padding:1px 6px;">All</button></div>
                                                                                    @foreach($items as $key => $label)
                                                                                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="{{$key}}" @if(in_array($key, $selected, true)) checked @endif><span>{{$label}}</span></label>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </details>
                                                            </form>
                                                            @if((int)$user->id !== (int)Auth::id())
                                                                <form action="{{URL::to('setting/admin-delete')}}" method="post" onsubmit="return confirm('Remove admin permission for {{$user->id}}?')" class="jambo-danger-zone">
                                                                    @csrf
                                                                    <input type="hidden" name="target_user" value="{{$user->id}}">
                                                                    <button type="submit" class="btn btn-sm btn-danger">Delete Admin Permission</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center text-muted">No admin users found.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var presets = {admin: @json($adminPreset), subadmin: @json($subadminPreset), country: @json($countryAdminPreset ?? []), clear: []};
    document.querySelectorAll('.js-permission-preset').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = button.closest('form');
            if (!form) return;
            var selected = presets[button.getAttribute('data-preset')] || [];
            var mode = button.getAttribute('data-mode');
            var select = form.querySelector('select[name="admin_mode"]');
            if (mode && select) select.value = mode;
            if (button.getAttribute('data-preset') === 'clear' && select) select.value = 'normal';
            form.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) {
                cb.checked = selected.indexOf(cb.value) !== -1;
            });
        });
    });

    document.querySelectorAll('.js-group-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var group = btn.getAttribute('data-group');
            var form = btn.closest('form');
            if (!form) return;
            var boxes = form.querySelectorAll('.permission-box[data-group="' + group + '"] input[type="checkbox"]');
            var anyUnchecked = Array.prototype.some.call(boxes, function(cb) { return !cb.checked; });
            boxes.forEach(function(cb) { cb.checked = anyUnchecked; });
        });
    });

    document.querySelectorAll('form[action$="setting/country-admin-store"]').forEach(function (form) {
        var existing = form.querySelector('input[name="target_user"]');
        var newFields = Array.prototype.slice.call(form.querySelectorAll('input[name="name"],input[name="email"],input[name="password"]'));
        function hasNewValue() {
            return newFields.some(function (field) { return field.value.trim() !== ''; });
        }
        function sync(source) {
            var hasExisting = existing && existing.value.trim() !== '';
            var hasNew = hasNewValue();
            if (source === 'existing' && hasExisting) {
                newFields.forEach(function (field) { field.value = ''; field.disabled = true; });
                existing.disabled = false;
                return;
            }
            if (source === 'new' && hasNew) {
                if (existing) {
                    existing.value = '';
                    existing.disabled = true;
                }
                newFields.forEach(function (field) { field.disabled = false; });
                return;
            }
            if (existing) existing.disabled = hasNew;
            newFields.forEach(function (field) { field.disabled = hasExisting; });
        }
        if (existing) existing.addEventListener('input', function () { sync('existing'); });
        newFields.forEach(function (field) { field.addEventListener('input', function () { sync('new'); }); });
        sync();
    });
});
</script>
@endsection
