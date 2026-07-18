  @php
  $image_pending=App\Models\ProfilePending::count();
  $luck_star_pending=App\Models\luckyStar::where('status',0)->count();
  @endphp
<style>
    .subadmin-profile-search {
        padding: 0 14px 16px;
    }
    .subadmin-profile-search__label {
        display: block;
        margin: 0 0 7px;
        color: rgba(255,255,255,.72);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .7px;
        text-transform: uppercase;
    }
    .subadmin-profile-search__row {
        display: flex;
        gap: 7px;
        align-items: center;
    }
    .subadmin-profile-search .search__text {
        min-width: 0;
        height: 38px;
        padding-left: 13px;
        border-radius: 9px;
        color: #fff;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.16);
    }
    .subadmin-profile-search .search__text::placeholder {
        color: rgba(255,255,255,.62);
    }
    .subadmin-profile-search__button {
        width: 42px;
        height: 38px;
        border: 0;
        border-radius: 9px;
        color: #fff;
        background: linear-gradient(135deg,#2563eb,#14b8a6);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
</style>
<nav class="sidebar sidebar-bunker">
    <div class="sidebar-header">
     <a href="{{ route('subadmin.dashboard') }}" class="logo text-white font-weight-bold" style="padding:16px 22px;display:block;letter-spacing:.08em;">QueenLive SUBADMIN</a>
 </div><!--/.sidebar header-->
 <form class="search sidebar-form subadmin-profile-search" action="{{URL::to('subadmin/sub_admin/profile_search/view/')}}" method="get" >
    @csrf
    <label class="subadmin-profile-search__label">Profile Search</label>
    <div class="subadmin-profile-search__row">
        <input type="number" name="id" class="search__text" placeholder="User ID" required>
        <button type="submit" class="subadmin-profile-search__button" aria-label="Search profile">
            <i class="typcn typcn-zoom-outline"></i>
        </button>
    </div>
</form><!--/.search-->
<div class="sidebar-body">
    <nav class="sidebar-nav">
        <ul class="metismenu">
            <li class="nav-label">Control Panel</li>
            <li><a href="{{ route('subadmin.dashboard') }}"><i class="typcn typcn-home-outline mr-2"></i>Dashboard</a></li>
            <li><a href="{{ URL::to('subadmin/sub_admin/profile_search/view/') }}"><i class="typcn typcn-zoom-outline mr-2"></i>Profile Search</a></li>
            <li><a href="{{ URL::to('subadmin/sub_admin/profile_pending') }}"><i class="typcn typcn-image-outline mr-2"></i>Pending Profile</a></li>
            <li><a href="{{ URL::to('subadmin/sub_admin/ranking') }}"><i class="typcn typcn-chart-bar-outline mr-2"></i>Ranking</a></li>
            <li><a href="{{ URL::to('subadmin/sub_admin/live-list') }}"><i class="typcn typcn-media-play-outline mr-2"></i>Live List</a></li>
            
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-user-add-outline mr-2"></i>
                    Host
                </a>
                <ul class="nav-second-level">
                    <li><a href="{{URL::to('subadmin/sub_admin/add-host')}}">Add Host</a></li>
                    <li><a href="{{URL::to('subadmin/sub_admin/pending_host')}}">Pending Host</a></li>
                    
                </ul>
            </li> 
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-group-outline mr-2"></i>
                    Agency 
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('subadmin/sub_admin/agency_create')}}">Create Agency</a></li>
                    <li><a href="{{URL::to('subadmin/sub_admin/agency_list')}}">Agency List</a></li>
                </ul>
            </li> 
            
            
           

            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-warning-outline mr-2"></i>
                    Ban ID 
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('subadmin/sub_admin/ban_id')}}">Ban</a></li>
                </ul>
            </li> 
           
        </nav>
    </div><!-- sidebar-body -->
</nav>
