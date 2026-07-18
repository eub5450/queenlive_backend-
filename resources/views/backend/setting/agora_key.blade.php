@extends('backend.layouts.main')
@section('title')
Create New Agency
@endsection
@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fa fa-exclamation-triangle"></i> Errors!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="body-content">
    <div class="card mb-4 border-0" style="background-color: #1a1a1a; color: #fff;">
        <div class="card-header py-3" style="background-color: #2d2d2d; border-bottom: 1px solid #404040;">
            <div class="d-flex align-items-center">
                <div style="width: 40px; height: 40px; background-color: #404040; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fa fa-percent" style="color: #ffc107;"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="color: #fff;">Exchange Cut Percentage</h5>
                    <small style="color: #999;">This value controls the live exchange cut used by the app and exchange history.</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4" style="background-color: #1a1a1a;">
            <form action="{{ URL::to('admin-exchange-cut-setting') }}" method="post">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-6 mb-4">
                        <label class="form-label mb-2" style="color: #ccc;">
                            <i class="fa fa-sliders me-2" style="color: #ffc107;"></i>
                            Exchange Cut Percentage <span style="color: #dc3545;">*</span>
                        </label>
                        <input
                            type="number"
                            name="exchange_cut_parcentage"
                            class="form-control bg-dark text-white"
                            min="0"
                            max="100"
                            step="0.01"
                            value="{{ old('exchange_cut_parcentage', number_format($exchangeCutPercentage, 2, '.', '')) }}"
                            style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;"
                            required>
                        <small class="d-block mt-2" style="color: #999;">
                            Current live value: <strong style="color: #ffc107;">{{ number_format($exchangeCutPercentage, 2, '.', '') }}%</strong>
                        </small>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="p-3 rounded" style="background-color: #2d2d2d; border: 1px solid #404040;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="color: #999;">Example</span>
                                <span class="badge" style="background-color: #ffc107; color: #000;">Live Setting</span>
                            </div>
                            <div style="color: #fff;">If host exchanges 100 coins and cut is {{ number_format($exchangeCutPercentage, 2, '.', '') }}%, receive amount will be {{ number_format(100 - ((100 * $exchangeCutPercentage) / 100), 2, '.', '') }}.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-4" style="border-color: #404040 !important;">
                    <button type="submit" class="btn px-5" style="background-color: #ffc107; color: #000; border: none;">
                        <i class="fa fa-save me-2"></i>Update Exchange Cut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create New Account Card - Dark Type -->
<div class="body-content">
    <div class="card mb-4 border-0" style="background-color: #1a1a1a; color: #fff;">
        <div class="card-header py-3" style="background-color: #2d2d2d; border-bottom: 1px solid #404040;">
            <div class="d-flex align-items-center">
                <div style="width: 40px; height: 40px; background-color: #404040; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fa fa-plus-circle" style="color: #ffc107;"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="color: #fff;">Create New Agora Account</h5>
                    <small style="color: #999;">Add a new Agora account to the system</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4" style="background-color: #1a1a1a;">
            <form action="{{URL::to('admin-agora_account_store')}}" method="post" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label mb-2" style="color: #ccc;">
                                <i class="fa fa-key me-2" style="color: #ffc107;"></i>
                                Agora AppId <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" name="appId" class="form-control bg-dark text-white" 
                                   placeholder="Enter Agora AppId" value="" 
                                   style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label mb-2" style="color: #ccc;">
                                <i class="fa fa-shield me-2" style="color: #ffc107;"></i>
                                Agora AppCertificate <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" name="appCertificate" class="form-control bg-dark text-white" 
                                   placeholder="Enter Agora AppCertificate" value="" 
                                   style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label mb-2" style="color: #ccc;">
                                <i class="fa fa-envelope me-2" style="color: #ffc107;"></i>
                                Agora Account Email <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="email" name="AgoraEmail" class="form-control bg-dark text-white" 
                                   placeholder="Enter Agora Account Email" value="" 
                                   style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label mb-2" style="color: #ccc;">
                                <i class="fa fa-lock me-2" style="color: #ffc107;"></i>
                                Agora Account Password <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" name="AgoraEmailPassword" class="form-control bg-dark text-white" 
                                   placeholder="Enter Agora Account Password" value="" 
                                   style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;" required>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-4">
                        <div class="form-group">
                            <label class="form-label mb-2" style="color: #ccc;">
                                <i class="fa fa-sticky-note me-2" style="color: #ffc107;"></i>
                                Notes (Optional)
                            </label>
                            <textarea name="note" class="form-control bg-dark text-white" rows="2" 
                                      placeholder="Add any additional notes about this account" 
                                      style="background-color: #2d2d2d; border: 1px solid #404040; color: #fff;"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 border-top pt-4" style="border-color: #404040 !important;">
                    <button type="reset" class="btn px-4" style="background-color: #404040; color: #fff; border: none;">
                        <i class="fa fa-refresh me-2"></i>Reset
                    </button>
                    <button type="submit" class="btn px-5" style="background-color: #ffc107; color: #000; border: none;">
                        <i class="fa fa-save me-2"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards - Dark Type -->
<div class="body-content">
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100" style="background-color: #1a1a1a;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: #999;">Total Accounts</p>
                            <h3 class="mb-0" style="color: #fff;">{{ count($data) }}</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background-color: #2d2d2d; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-database" style="color: #ffc107; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100" style="background-color: #1a1a1a;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: #999;">Running</p>
                            <h3 class="mb-0" style="color: #28a745;">{{ $data->where('Status',1)->count() }}</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background-color: #2d2d2d; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-play" style="color: #28a745; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100" style="background-color: #1a1a1a;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: #999;">New</p>
                            <h3 class="mb-0" style="color: #17a2b8;">{{ $data->where('Status',0)->count() }}</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background-color: #2d2d2d; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-plus" style="color: #17a2b8; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100" style="background-color: #1a1a1a;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1" style="color: #999;">Expired</p>
                            <h3 class="mb-0" style="color: #dc3545;">{{ $data->where('Status',2)->count() }}</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background-color: #2d2d2d; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-clock-o" style="color: #dc3545; font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Agora Login Count Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0" style="background-color: #1a1a1a;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div style="width: 60px; height: 60px; background-color: #2d2d2d; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                            <i class="fa fa-user-circle" style="color: #ffc107; font-size: 30px;"></i>
                        </div>
                        <div>
                            <p class="mb-1" style="color: #999;">Agora Login Accounts</p>
                            <h2 class="mb-0" style="color: #fff;">{{ $data->where('type',1)->count() }}</h2>
                        </div>
                        <div class="ms-auto">
                            <span class="badge px-3 py-2" style="background-color: #2d2d2d; color: #ffc107;">
                                <i class="fa fa-info-circle me-1"></i>
                                Gmail Login: {{ $data->where('type',0)->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active & New Accounts Table -->
<div class="body-content">
    <div class="card mb-4 border-0" style="background-color: #1a1a1a;">
        <div class="card-header py-3" style="background-color: #2d2d2d; border-bottom: 1px solid #404040;">
            <div class="d-flex align-items-center">
                <div style="width: 40px; height: 40px; background-color: #404040; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fa fa-check-circle" style="color: #28a745;"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="color: #fff;">Active & New Accounts</h5>
                    <small style="color: #999;">Running and New accounts ({{ $data->whereIn('Status', [0,1])->count() }})</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="background-color: #1a1a1a; color: #fff;">
                    <thead style="background-color: #2d2d2d; color: #999;">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">AppId</th>
                            <th class="px-3 py-3">AppCertificate</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Password</th>
                            <th class="px-3 py-3">Note</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $activeNewAccounts = $data->whereIn('Status', [0,1]);
                        $i = 0;
                        @endphp
                        
                        @forelse($activeNewAccounts as $row)
                        <tr style="border-bottom: 1px solid #2d2d2d;">
                            <td class="px-3 py-3">{{ ++$i }}    @if($row->Status==0)
                                    
                                     <a href="{{URL::to('admin-agora_account_pre_active/'.$row->id)}}" 
                                       class="btn btn-sm" style="background-color: #0ed04a; color: #fff; border: none;">
                                        <i class="fa fa-check"></i>
                                    </a>
                                @endif</td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <span>{{ Str::limit($row->appId, 5) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->appId}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <span>{{ Str::limit($row->appCertificate, 5) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->appCertificate}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-envelope me-2" style="color: #999;"></i>
                                    <span>{{ Str::limit($row->AgoraEmail, 5) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->AgoraEmail}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-lock me-2" style="color: #999;"></i>
                                    <span class="password-field" data-password="{{$row->AgoraEmailPassword}}">••••••••</span>
                                    <button class="btn btn-sm btn-link toggle-password ms-1" style="color: #ffc107;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->AgoraEmailPassword}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                @if($row->note)
                                    <span style="color: #999;">{{ Str::limit($row->note, 10) }}</span>
                                @else
                                    <span style="color: #666;">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if($row->type == 1)
                                    <span class="badge" style="background-color: #ffc107; color: #000;">Agora</span>
                                @else
                                    <span class="badge" style="background-color: #404040; color: #fff;">Gmail</span>
                                @endif
                                 @if($row->Status == 1)
                                    <span class="badge" style="background-color: #28a745; color: #fff;">Running</span>
                                @elseif($row->Status == 0)
                                    <span class="badge" style="background-color: #17a2b8; color: #fff;">New</span>
                                @endif
                            </td>
                           
                            <td class="px-3 py-3">
                                @if($row->Status==0 )
                                    <a href="{{URL::to('admin-agora_account_active/'.$row->id)}}" 
                                       class="btn btn-sm" style="background-color: #ffc107; color: #000; border: none;">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5" style="color: #666;">
                                <i class="fa fa-info-circle fa-2x mb-3"></i>
                                <p>No active or new accounts found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Expired Accounts Table -->
<div class="body-content">
    <div class="card border-0" style="background-color: #1a1a1a;">
        <div class="card-header py-3" style="background-color: #2d2d2d; border-bottom: 1px solid #404040;">
            <div class="d-flex align-items-center">
                <div style="width: 40px; height: 40px; background-color: #404040; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fa fa-exclamation-circle" style="color: #dc3545;"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="color: #fff;">Expired Accounts</h5>
                    <small style="color: #999;">Accounts that have expired ({{ $data->where('Status',2)->count() }})</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="background-color: #1a1a1a; color: #fff;">
                    <thead style="background-color: #2d2d2d; color: #999;">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">AppId</th>
                            <th class="px-3 py-3">AppCertificate</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Password</th>
                            <th class="px-3 py-3">Note</th>
                            <th class="px-3 py-3">Type</th>
                            <th class="px-3 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $expiredAccounts = $data->where('Status',2);
                        $j = 0;
                        @endphp
                        
                        @forelse($expiredAccounts as $row)
                        <tr style="border-bottom: 1px solid #2d2d2d;">
                            <td class="px-3 py-3">{{ ++$j }}</td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <span>{{ Str::limit($row->appId, 5) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->appId}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <span>{{ Str::limit($row->appCertificate, 5) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->appCertificate}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-envelope me-2" style="color: #999;"></i>
                                    <span>{{ Str::limit($row->AgoraEmail, 15) }}</span>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->AgoraEmail}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-lock me-2" style="color: #999;"></i>
                                    <span class="password-field" data-password="{{$row->AgoraEmailPassword}}">••••••••</span>
                                    <button class="btn btn-sm btn-link toggle-password ms-1" style="color: #ffc107;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link copy-btn ms-1" data-copy="{{$row->AgoraEmailPassword}}" style="color: #ffc107;">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                @if($row->note)
                                    <span style="color: #999;">{{ Str::limit($row->note, 10) }}</span>
                                @else
                                    <span style="color: #666;">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if($row->type == 1)
                                    <span class="badge" style="background-color: #ffc107; color: #000;">Agora</span>
                                @else
                                    <span class="badge" style="background-color: #404040; color: #fff;">Gmail</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <span class="badge" style="background-color: #dc3545; color: #fff;">Expired</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color: #666;">
                                <i class="fa fa-check-circle fa-2x mb-3"></i>
                                <p>No expired accounts found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3" style="background-color: #2d2d2d; border-top: 1px solid #404040;">
            <small style="color: #666;">
                <i class="fa fa-clock-o me-1"></i>
                Last updated: {{ now()->format('d M Y, h:i A') }}
            </small>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    /* Dark Theme */
    body {
        background-color: #0a0a0a;
    }
    
    .form-control:focus {
        background-color: #2d2d2d;
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        color: #fff;
    }
    
    .form-control::placeholder {
        color: #666;
    }
    
    .copy-btn {
        opacity: 0.5;
        transition: opacity 0.3s;
        text-decoration: none;
    }
    
    .copy-btn:hover {
        opacity: 1;
    }
    
    .table-hover tbody tr:hover {
        background-color: #2d2d2d !important;
    }
    
    .btn-link {
        text-decoration: none;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #404040;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ffc107;
    }
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const textToCopy = this.getAttribute('data-copy');
            
            navigator.clipboard.writeText(textToCopy).then(() => {
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                
                icon.className = 'fa fa-check';
                this.style.color = '#28a745';
                
                setTimeout(() => {
                    icon.className = originalClass;
                    this.style.color = '#ffc107';
                }, 1500);
            });
        });
    });
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const passwordSpan = row.querySelector('.password-field');
            const password = passwordSpan.getAttribute('data-password');
            const icon = this.querySelector('i');
            
            if (passwordSpan.textContent === '••••••••') {
                passwordSpan.textContent = password;
                icon.className = 'fa fa-eye-slash';
            } else {
                passwordSpan.textContent = '••••••••';
                icon.className = 'fa fa-eye';
            }
        });
    });
});
</script>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection
