@extends('backend.layouts.main')


@section('title')
Supplier  
@endsection
@section('content')
@php
$use=App\Models\User::find(Auth::id());
@endphp
@if(Auth::id() == 22222 || Auth::id() == 1)
<div class="body-content">

	<div class="row ">
		<div class="col-sm-6 col-xs-12 contact">
			<div class="card">

				<div class="box-body">
					<center><h3 class="widget-user-username">Teen Patti : locked id :{{$balance->block_id}}--{{$balance->lock_parcent}}% <br>Winner id :{{$balance->winner_id}}</h3></center>
					<address class="mb-0 text-center">
					Game Balance: <h1><b>{{round($balance->game_balance)}}</b></h1><br>
						Game 2nd Balance: <h1><b>{{round($balance->second_balance)}}</b></h1>
						Game 3rd Balance: <h1><b>{{round($balance->third_balance)}}</b></h1>
					</address>
				
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xs-12 balance">
			<div class="card">
				<div class="box box-info">
					<div class="box-header with-border text-center">
						<h3 class="box-title">Game On / Off Controll</h3>
						<!--<iframe src="https://bplive.site/betel/fruits?token={{$use->password}}&id=7&user={{$use->email}}" height="325px" width="400px"></iframe>-->
					</div>
					<div class="box-body">
						@if($balance->game_status==1)
							<a href="{{URL::to('admin/teen-patti_game_off')}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span>Game Off</a>
							@else 
							<a href="{{URL::to('admin/teen-patti_game_on')}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span>Game On</a>
							@endif
                     
                      
					</div>
				</div>
			</div>		
		</div>
		<br>
	
	<div class="modal fade" id="idlocked" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Lock Id For Teen Patti Game</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <form action="{{URL::to('teen_patti_id_lock')}}" method="post">
             @csrf

          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Lock ID Number:</label>
            <input type="number" value="{{$balance->block_id}}"  name="block_id" class="form-control" id="recipient-name">
          </div>
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">%:</label>
            <input type="number"  name="lock_parcent" class="form-control" value="{{$balance->lock_parcent}}" id="recipient-name">
          </div>
        <div class="form-group">
            <label for="recipient-name" class="col-form-label">Winner ID Number:</label>
            <input type="number" value="{{$balance->winner_id}}"  name="winner_id" class="form-control" id="recipient-name">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

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
			                        <th><img  style=" width: 30px; "src="{{asset('public/game/teenpatti/image')}}/ChairRed.png" alt="Saven Winner"> Serve</th>
			                       <th><img src="{{asset('public/game/teenpatti/image')}}/ChairBlue.png" style=" width: 30px; " alt="Saven Winner"> Serve</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/teenpatti/image')}}/ChairGreen.png" alt="Saven Winner"> Serve</th>
			                       <th>Win Balance</th>
			                       <th>Total Bet</th>
			                       <th>Total Balance Have</th>
			                       <th>Game Balance Now</th>
			                       <th>Start Time</th>
			                       <th>Pattern</th>
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
			                       <td>@if($game_serve_detail->winner=='saven_win') <img src="{{asset('public/game/teenpatti/image')}}/ChairBlue.png" style=" width: 48px; " alt="Saven Winner"> @elseif($game_serve_detail->winner=='watermelon')  <img  style=" width: 48px; "src="{{asset('public/game/teenpatti/image')}}/ChairGreen.png" alt="Saven Winner"> @else <img  style=" width: 48px; "src="{{asset('public/game/teenpatti/image')}}/ChairRed.png" alt="Saven Winner"> @endif  </td>
			                      <td>{{$game_serve_detail->no_one_pots}}  </td>
			                      <td>{{$game_serve_detail->no_two_pots}}  </td>
			                      <td>{{$game_serve_detail->no_three_pots}}  </td>
			                      <td>{{$game_serve_detail->win_balance}}  </td>
			                      <td>{{$game_serve_detail->game_balance}}  </td>
			                      <td>{{$game_serve_detail->real_game_balance}}  </td>
			                      <td>{{$game_serve_detail->after_win_balance}}  </td>
			                      <td>{{$game_serve_detail->created_at}}  </td>
			                      <td>{{$game_serve_detail->randomPercentage}}  </td>
			                      
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
			                 <th>Pattern</th>
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
			                       <th>User </th>
			                       <th>User ID</th>
			                       <th>Tray ID</th>
			                        <th><img  style=" width: 30px; "src="{{asset('public/game/teenpatti/image')}}/ChairRed.png" alt="Saven Winner"> Amount</th>
			                       <th><img src="{{asset('public/game/teenpatti/image')}}/ChairBlue.png" style=" width: 30px; " alt="Saven Winner"> Amount</th>
			                       <th><img  style=" width: 30px; "src="{{asset('public/game/teenpatti/image')}}/ChairGreen.png" alt="Saven Winner"> Amount</th>
			                       <th>Status</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($game_serve_users_details as $game_serve_users_detail)
			                    @php
			                    $user=App\Models\User::find($game_serve_users_detail->user_id);
			                    @endphp
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>@if($user){{$user->name}} @else @endif  </td>
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
			                 <th>User </th>
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
@endif
@endsection