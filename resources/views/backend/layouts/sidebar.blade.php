  @php
  $sidebar_counts = \Illuminate\Support\Facades\Cache::remember('admin.sidebar.counts.v1', now()->addSeconds(30), function () {
      return array(
          'image_pending' => App\Models\ProfilePending::count(),
          'withdraw' => App\Models\Withdraw::where('status', 0)->count(),
          'luck_star_pending' => App\Models\luckyStar::where('status', 0)->count(),
          'help_pending' => 0,
      );
  });

  $image_pending = $sidebar_counts['image_pending'];
  $withdraw = $sidebar_counts['withdraw'];
  $luck_star_pending = $sidebar_counts['luck_star_pending'];
  $help_pending = $sidebar_counts['help_pending'];
  $adminCan = function ($key, $default = false) {
      return \App\Models\AdminParmisiton::allowed(Auth::id(), $key, $default);
  };
  $adminAny = function ($keys) use ($adminCan) {
      foreach ($keys as $key) {
          if ($adminCan($key)) {
              return true;
          }
      }
      return false;
  };
  $profileSearchKeys = [
      'profile_search',
      'profile_balance',
      'profile_email_info',
      'profile_phone_info',
      'profile_sensitive_info',
      'profile_other_ids',
      'profile_power_buttons',
      'profile_vip_frames_edit',
      'profile_password_daytime',
  ];
  @endphp
@if(Auth::check() && $adminCan('sidebar_access'))
<nav class="sidebar sidebar-bunker">
    <div class="sidebar-header">
     <a href="{{URL::to('/dashboard')}}" class="logo"><img src="" alt=""></a> 
 </div><!--/.sidebar header-->
 @if($adminAny($profileSearchKeys))
 <form class="search sidebar-form" action="{{URL::to('id_search')}}" method="get" >
    @csrf
    <div class="search__inner">
        <input type="number" name="id" class="search__text" placeholder="Search..." >
        <i class="typcn typcn-zoom-outline search__helper" data-sa-action="search-close"></i>
    </div>
</form><!--/.search-->
 @endif
<div class="sidebar-body">
    <nav class="sidebar-nav">
        <ul class="metismenu">
            <li class="nav-label">Main Menu</li>
            @if($adminCan('sidebar_menu_dashboard'))
            <li><a href="{{URL::to('/dashboard')}}"><i class="typcn typcn-home-outline mr-2"></i>Dashboard</a></li>
            @endif
            @if($adminCan('sidebar_menu_host') && $adminAny(['sidebar_host_add', 'sidebar_host_active', 'sidebar_host_pending', 'sidebar_host_transfer']))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Host
                </a>
                <ul class="nav-second-level">
                    @if($adminCan('sidebar_host_add'))<li><a href="{{URL::to('add-host')}}">Add Host</a></li>@endif
                    @if($adminCan('sidebar_host_active'))<li><a href="{{URL::to('active_host')}}">Active Host</a></li>@endif
                    @if($adminCan('sidebar_host_pending'))<li><a href="{{URL::to('pending_host')}}">Pending Host</a></li>@endif
                    @if($adminCan('sidebar_host_transfer'))<li><a href="{{URL::to('transfer_host')}}">Transfer Host</a></li>@endif
                    
                </ul>
            </li>
            @endif
            @if($adminCan('sidebar_menu_agency') && $adminAny(['sidebar_agency_create', 'sidebar_agency_list']))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Agency 
                </a>
                <ul class="nav-second-level">
                    
                    @if($adminCan('sidebar_agency_create'))<li><a href="{{URL::to('agency_create')}}">Create Agency</a></li>@endif
                    @if($adminCan('sidebar_agency_list'))<li><a href="{{URL::to('agency_list')}}">Agency List</a></li>@endif
                    @if($adminCan('sidebar_agency_list'))<li><a href="{{URL::to('master-agency_list')}}">Master Agency List</a></li>@endif
                </ul>
            </li> 
            @endif
            @if($adminCan('sidebar_menu_protal') && $adminAny(['sidebar_protal_create', 'sidebar_protal_list', 'sidebar_protal_recall_create', 'sidebar_protal_recall_history', 'sidebar_protal_recharge', 'sidebar_protal_recharge_list', 'sidebar_protal_new_recall', 'sidebar_protal_recall_list']))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Protal 
                </a>
                <ul class="nav-second-level">
                    
                    @if($adminCan('sidebar_protal_create'))<li><a href="{{URL::to('protal_create')}}">Create Protal</a></li>@endif
                    @if($adminCan('sidebar_protal_list'))<li><a href="{{URL::to('protal_list')}}">Protal List</a></li>@endif
                    @if($adminCan('sidebar_protal_recharge'))<li><a href="{{URL::to('master_recharge')}}">Master Recharge</a></li>@endif
                    @if($adminCan('sidebar_protal_recharge'))<li><a href="{{URL::to('recharge_otp')}}">Recharge</a></li>@endif
                    @if($adminCan('sidebar_protal_recharge_list'))<li><a href="{{URL::to('recharge-list')}}">Recharge List</a></li>@endif
                    @if($adminCan('sidebar_protal_new_recall'))<li><a href="{{URL::to('recall')}}">New ReCall</a></li>@endif
                    @if($adminCan('sidebar_protal_recall_list'))<li><a href="{{URL::to('recall-list')}}">ReCall List</a></li>@endif
                </ul>
            </li>
            @endif
            @if($adminCan('sidebar_menu_user_balance'))
             <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                   Withdraw @if($withdraw>0)<span class="badge badge-danger" >{{$withdraw}}</span> @endif
                </a>
                <ul class="nav-second-level">
                    
                    @if($adminCan('sidebar_user_balance_wallet') || $adminCan('sidebar_withdraw'))<li><a href="{{URL::to('admin/withdraw')}}">Withdraw</a></li>@endif
                </ul>
            </li>
            @endif
            <!--<li>-->
            <!--    <a class="has-arrow material-ripple" href="#">-->
            <!--        <i class="typcn typcn-book mr-2"></i>-->
            <!--        Profile Pending @if($image_pending>0)<span class="badge badge-danger" >{{ $image_pending }}</span> @endif-->
            <!--    </a>-->
            <!--    <ul class="nav-second-level">-->
                    
            <!--        <li><a href="{{URL::to('profile_pending')}}">Pending</a></li>-->
            <!--    </ul>-->
            <!--</li>-->
            @if($adminCan('sidebar_menu_ban') && $adminAny(['sidebar_ban_id', 'sidebar_invisible_id', 'sidebar_official_id']))
             <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Invisibal ID
                </a>
                <ul class="nav-second-level">

                    @if($adminCan('sidebar_invisible_id'))<li><a href="{{URL::to('invisibal')}}">Invisibal</a></li>@endif
                </ul>
            </li>
            @endif
            @if($adminCan('sidebar_menu_ban') && $adminAny(['sidebar_ban_id', 'sidebar_invisible_id', 'sidebar_official_id']))
             <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Official ID
                </a>
                <ul class="nav-second-level">

                    @if($adminCan('sidebar_official_id'))<li><a href="{{URL::to('official_id')}}">Official</a></li>@endif
                </ul>
            </li>
            @endif
            @if($adminCan('sidebar_menu_support') && $adminCan('sidebar_support_index'))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                   Help Desk @if($help_pending>0)<span class="badge badge-danger" >{{$help_pending}}</span> @endif
                </a>
                <ul class="nav-second-level">
                    <li><a href="{{URL::to('support')}}">Support Tickets @if($help_pending>0)<span class="badge badge-danger" >{{$help_pending}}</span> @endif</a></li>
                </ul>
            </li>
            @endif
            <!--<li>-->
            <!--    <a class="has-arrow material-ripple" href="#">-->
            <!--        <i class="typcn typcn-book mr-2"></i>-->
            <!--        Lucky Star @if($luck_star_pending>0)<span class="badge badge-danger" >{{ $luck_star_pending }}</span> @endif-->
            <!--    </a>-->
            <!--    <ul class="nav-second-level">-->
                    
            <!--        <li><a href="{{URL::to('lucky_star_pending')}}">Pending  @if($luck_star_pending>0)<span class="badge badge-danger" >{{ $luck_star_pending }}</span> @endif</a></li>-->
            <!--        <li><a href="{{URL::to('lucky_star_active')}}">Active</a></li>-->
            <!--    </ul>-->
            <!--</li> -->
            @if($adminCan('sidebar_menu_ranking') && $adminCan('sidebar_ranking_list'))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                   Ranking
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('rankingList')}}">Ranking</a></li>
                </ul>
            </li>
            @endif
            @if($adminCan('sidebar_menu_user_balance') && $adminCan('sidebar_user_balance_wallet'))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    User Balance
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('user_have_balance')}}">Wallet</a></li>
                </ul>
            </li> 
            @endif
            @if($adminCan('sidebar_menu_ban') && $adminCan('sidebar_ban_id'))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Ban ID 
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('ban_id')}}">Ban</a></li>
                </ul>
            </li> 
            @endif
            @if($adminCan('sidebar_menu_live') && $adminCan('sidebar_live_list'))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Live
                </a>
                <ul class="nav-second-level">
                    
                    <li><a href="{{URL::to('live-list')}}"> Live List</a></li>                        
                </ul>
            </li> 
            @endif
            @if($adminCan('sidebar_menu_game_control') && $adminAny(['sidebar_game_fruits_detail', 'sidebar_game_fruits_lock', 'sidebar_game_teenpatti_detail', 'sidebar_game_greedy_detail', 'sidebar_game_fruits_pattern']))
            <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                    Game Control
                </a>
                <ul class="nav-second-level">
                    
                    @if($adminCan('sidebar_game_teenpatti_detail'))<li><a href="{{ route('admin.thomas_game_control') }}"> Thomas Game</a></li>@endif
                    @if($adminCan('sidebar_game_fruits_detail'))<li><a href="{{URL::to('admin/fruts-game-detail')}}"> Fruits Game</a></li>@endif
                    @if($adminCan('sidebar_game_fruits_lock'))<li><a href="{{URL::to('admin/fruts-game-lock-list')}}"> Fruits Lock </a></li>@endif
                    @if($adminCan('sidebar_game_teenpatti_detail'))<li><a href="{{URL::to('admin/teen-patti-game-detail')}}"> Teenpati Game</a></li>@endif
                    @if($adminCan('sidebar_game_greedy_detail'))<li><a href="{{URL::to('admin/five-game-detail')}}"> Five Game</a></li>@endif
                    @if($adminCan('sidebar_game_greedy_detail'))<li><a href="{{URL::to('admin/grady-game-detail')}}"> Greedy Game</a></li>@endif
                    @if($adminCan('sidebar_game_fruits_pattern'))<li><a href="{{URL::to('admin/fruts-game-pattarn')}}"> Fruits Pattan</a></li>@endif
                    @if($adminCan('sidebar_game_fruits_pattern'))<li><a href="{{URL::to('admin/greedy-game-pattarn')}}"> Greedy Pattan</a></li>@endif
                </ul>
            </li> 
            @endif
            @if($adminCan('sidebar_menu_setting'))
           <li>
                <a class="has-arrow material-ripple" href="#">
                    <i class="typcn typcn-book mr-2"></i>
                   Setting
                </a>
                <ul class="nav-second-level">
                    
                    @if($adminCan('sidebar_setting_banner'))<li><a href="{{URL::to('admin-slider')}}">Banner </a></li>@endif
                    @if($adminCan('sidebar_setting_country'))<li><a href="{{URL::to('admin-country')}}">Country </a></li>@endif
                    @if($adminCan('sidebar_setting_store'))<li><a href="{{URL::to('admin-store')}}">Store </a></li>@endif
                    @if($adminCan('sidebar_setting_store'))<li><a href="{{URL::to('admin-lucky_id')}}">Lucky ID </a></li>@endif     
                    @if($adminCan('sidebar_setting_fanclub'))<li><a href="{{URL::to('admin/fanclub-settings')}}">Fan Club / Guardian</a></li>@endif
                    @if($adminCan('sidebar_setting_combo'))<li><a href="{{URL::to('admin/combo-settings')}}">Gift Combo</a></li>@endif
                    @if($adminCan('sidebar_setting_checkin'))<li><a href="{{URL::to('admin/checkin-settings')}}">Daily Check-in</a></li>@endif
                    @if($adminCan('sidebar_setting_level'))<li><a href="{{URL::to('admin/level-setting')}}">Level</a></li>@endif
                    @if($adminCan('sidebar_setting_fun_sticker'))<li><a href="{{URL::to('admin/fun-sticker')}}">Fun Sticker</a></li>@endif
                  
                    @if($adminCan('sidebar_setting_agora'))<li><a href="{{URL::to('admin-agora_account_setting')}}">Agora Setting</a></li>@endif
                    @if($adminCan('sidebar_setting_system'))<li><a href="{{URL::to('admin-system-setting')}}">System Setting</a></li>@endif 
                    @if($adminCan('sidebar_setting_admin') && $adminCan('setting_admin_manage'))<li><a href="{{URL::to('setting/admin')}}">Admin Users</a></li>@endif
                    @if($adminCan('sidebar_setting_admin') && $adminCan('setting_admin_manage'))<li><a href="{{URL::to('setting/admin')}}#permission-system">Permission System</a></li>@endif
                    @if($adminCan('sidebar_setting_email_change'))<li><a href="{{URL::to('admin-user-emailchange')}}">Email Change</a></li>@endif
                    @if($adminCan('sidebar_setting_gift_data'))<li><a href="{{URL::to('admin-gift-data')}}">Gift Data </a></li>@endif
                    @if($adminCan('sidebar_setting_audio_background'))<li><a href="{{URL::to('admin-audio-brd-background')}}">Audio Brd Background </a></li>@endif
                    
                </ul>
            </li> 
            @endif
        </ul>
        </nav>
    </div><!-- sidebar-body -->
</nav>

@endif
