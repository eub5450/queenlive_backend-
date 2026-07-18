@extends('backend.layouts.main')

@section('title')
Thomas Game Control
@endsection

@section('content')
<div class="body-content">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
                <div class="mb-3 mb-md-0">
                    <h4 class="mb-1">Thomas Game Control</h4>
                    <small class="text-muted">QueenLive admin gateway for the locked Thomas game panel.</small>
                </div>
                <div class="d-flex flex-wrap">
                    <span class="badge badge-success mr-2 mb-2">QueenLive Admin Locked</span>
                    <span class="badge badge-warning mb-2">Thomas Security Locked</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-dark text-white">
                            <strong>Control Access</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                The old local Thomas route is missing in this checkout. This gateway restores the item inside QueenLive admin and sends allowed admins to the live Thomas control surface.
                            </p>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Tenant Host</div>
                                <div class="font-weight-bold">{{ $tenantHost }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="small text-uppercase text-muted mb-1">Lock Flow</div>
                                <div class="text-dark">1. QueenLive admin login</div>
                                <div class="text-dark">2. Thomas admin login</div>
                                <div class="text-dark">3. Thomas security gate</div>
                            </div>

                            <div class="d-flex flex-wrap">
                                <a href="{{ route('admin.thomas_game_control.security') }}" class="btn btn-primary mr-2 mb-2">
                                    Open Secure Control
                                </a>
                                <a href="{{ route('admin.thomas_game_control.login') }}" class="btn btn-outline-dark mr-2 mb-2">
                                    Thomas Login
                                </a>
                                <a href="{{ route('admin.thomas_game_control.lobby') }}" class="btn btn-outline-info mb-2">
                                    Preview Lobby
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <strong>Live URLs</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Secure Control</div>
                                <code class="d-block">{{ $securityUrl }}</code>
                            </div>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Thomas Login</div>
                                <code class="d-block">{{ $loginUrl }}</code>
                            </div>

                            <div>
                                <div class="small text-uppercase text-muted mb-1">Lobby Preview</div>
                                <code class="d-block">{{ $lobbyUrl }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
