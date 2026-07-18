@extends('backend.layouts.main')


@section('title')
Supplier  
@endsection
@section('content')

<div class="body-content">
	
	<div class="row ">
		<div class="col-sm-6 col-xs-12 contact">
			<div class="card">

				<div class="box-body">
					<center><h3 class="widget-user-username">Fruits</h3></center>
					<address class="mb-0 text-center">
					Game Balance: <h1><b>{{$balance->game_balance}}</b></h1>	
					</address>
			
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xs-12 balance">
			<div class="card">
				<div class="box box-info">
					<div class="box-header with-border text-center">
						<h3 class="box-title">Game On / Off Controll</h3>
					</div>
					<div class="box-body">
						@if($balance->game_status==1)
							<a href="{{URL::to('admin/five_game_off')}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span>Off</a>
							@else 
							<a href="{{URL::to('admin/five_game_on')}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span>On</a>
							@endif

					</div>
				</div>
			</div>		
		</div>
		<br>
	
	

		<div class="body-content" style="width: 100%;">


		<div class="col-xl-12 col-sm-12 py-2">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">Bet Details</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					 <table class="table display table-bordered table-striped table-hover basic">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>Tray ID</th>
			                       <th>Winner</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner"> Serve</th>
			                       <th><img src="{{asset('public/game/fivestar/image')}}/lemon.png" style=" width: 30px; " alt="Saven Winner"> Serve</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner"> Serve</th>
			                       <th>Win Balance</th>
			                       <th>Total Bet</th>
			                       <th>Total Balance Have</th>
			                       <th>Game Balance Now</th>
			                       <th>Start Time</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($game_serve_details as $game_serve_detail)
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$game_serve_detail->tray_id}}  </td>
			                      <td>@if($game_serve_detail->winner=='saven_win') <img src="{{asset('public/game/fivestar/image')}}/lemon.png" style=" width: 48px; " alt="Saven Winner"> @elseif($game_serve_detail->winner=='watermelon')  <img  style=" width: 48px; "src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner"> @else <img  style=" width: 48px; "src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner"> @endif  </td>
			                      <td>{{$game_serve_detail->apple_serve}}  </td>
			                      <td>{{$game_serve_detail->lemon_serve}}  </td>
			                      <td>{{$game_serve_detail->watermalon_serve}}  </td>
			                      <td>{{$game_serve_detail->win_balance}}  </td>
			                      <td>{{$game_serve_detail->game_balance}}  </td>
			                      <td>{{$game_serve_detail->real_game_balance}}  </td>
			                      <td>{{$game_serve_detail->after_win_balance}}  </td>
			                      <td>{{$game_serve_detail->created_at}}  </td>
			                      
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                 <th>Tray ID</th>
			                 <th>Winner</th>
			                 <th>Apple Serve</th>
			                 <th>Lemon Serve</th>
			                 <th>Watermalon Serve</th>
			                 <th>Win Balance</th>
			                 <th>Total Bet</th>
			                 <th>Total Balance Have</th>
			                 <th>Game Balance Now</th>
			                 <th>Time</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>
		</div>
</div>

<div class="body-content" style="width: 100%;">


		
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">User Bets Details With Tray</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					 <table class="table display table-bordered table-striped table-hover basic">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>User ID</th>
			                       <th>Tray ID</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner"> Amount</th>
			                       <th><img src="{{asset('public/game/fivestar/image')}}/lemon.png" style=" width: 30px; " alt="Saven Winner"> Amount</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner"> Amount</th>
			                       <th>Status</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($game_serve_users_details as $game_serve_users_detail)
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$game_serve_users_detail->user_id}}  </td>
			                      <td>{{$game_serve_users_detail->tray_id}}  </td>
			                      <td>@if($game_serve_users_detail->pot_no=='apple') {{$game_serve_users_detail->amount}} @else 0 @endif </td>
			                      <td>@if($game_serve_users_detail->pot_no=='saven_win') {{$game_serve_users_detail->amount}} @else 0 @endif </td>
			                      <td>@if($game_serve_users_detail->pot_no=='watermelon') {{$game_serve_users_detail->amount}} @else 0 @endif </td>
			                      <td>@if($game_serve_users_detail->status==0) Hold @elseif($game_serve_users_detail->status==1) Win @else Loss @endif </td>
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                 <th>User ID</th>
			                 <th>Tray ID</th>
			                 <th>Apple Amount</th>
			                 <th>Lemon Amount</th>
			                 <th>Watermalon Amount</th>
			                 <th>Status</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>

</div>

</div>

@endsection