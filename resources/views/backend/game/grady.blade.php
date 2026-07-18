@extends('backend.layouts.main')


@section('title')
Gready  
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
					<center><h3 class="widget-user-username">Greedy</h3></center>
					<address class="mb-0 text-center">
					Game Balance: <h1><b id="gready_balance">{{$balance->game_balance}}</b></h1><br>
						Game 2nd Balance: <h1><b id="gready_second_balance">{{$balance->second_balance}}</b></h1>
						Game 3rd Balance: <h1><b id="gready_third_balance">{{$balance->third_balance}}</b></h1>
					</address>
				{{-- 	<a href="{{URL::to('reject_host/'.$user->user_id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-cross"></span>Host Reject</a> --}}
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xs-12 balance">
			<div class="card">
				<div class="box box-info">
					<div class="box-header with-border text-center">
						<h3 class="box-title">Game On / Off Controll</h3>
						<!--<iframe src="https://bplive.site/grady/play?token={{$use->password}}&id=7&user={{$use->email}}" height="325px" width="400px"></iframe>-->
					</div>
					<div class="box-body">
						@if($balance->game_status==1)
							<a href="{{URL::to('admin/grady_game_off')}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span>Game Off</a>
							@else 
							<a href="{{URL::to('admin/grady_game_on')}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span>Game On</a>
							@endif
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#third_balance_setting">
                            Greedy Setting
                            </button>
					</div>
				</div>
			</div>		
		</div>
		<br>
		<div class="modal fade" id="third_balance_setting" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Greedy Setting</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                 <form action="{{URL::to('greedy_setting')}}" method="post">
                     @csrf
        
                  <div class="form-group">
                    <label for="third-take-margin" class="col-form-label">2nd Balance Tray Margin:</label>
                    <input type="number" name="tray_margin" value="{{$balance->tray_margin}}" class="form-control" id="third-take-margin">
                </div>
                
                <div class="form-group">
                    <label for="third-helf-give-margin" class="col-form-label">2nd Balance Take :</label>
                    <input type="number" name="take_parcenage" value="{{$balance->take_parcenage}}" class="form-control" id="third-helf-give-margin">
                </div>
                
                <div class="form-group">
                    <label for="third-full-give-margin" class="col-form-label">Bid Block</label>
                    <input type="number" name="bid_brack" value="{{$balance->bid_brack}}" class="form-control" id="third-full-give-margin">
                </div>
                 
                  <div class="form-group">
                    <label for="third-take-margin" class="col-form-label">Third Take Margin:</label>
                    <input type="number" name="third_take_margin" value="{{$balance->third_take_margin}}" class="form-control" id="third-take-margin">
                </div>
                
                <div class="form-group">
                    <label for="third-helf-give-margin" class="col-form-label">Third 2nd Give Margin:</label>
                    <input type="number" name="third_helf_give_margin" value="{{$balance->third_helf_give_margin}}" class="form-control" id="third-helf-give-margin">
                </div>
                
                <div class="form-group">
                    <label for="third-full-give-margin" class="col-form-label">Third 3rd Give Margin:</label>
                    <input type="number" name="third_full_give_margin" value="{{$balance->third_full_give_margin}}" class="form-control" id="third-full-give-margin">
                </div>
                
                <div class="form-group">
                    <label for="third-take-percentage" class="col-form-label">Third Take Percentage:</label>
                    <input type="number" name="third_take_parcentage" value="{{$balance->third_take_parcentage}}" class="form-control" id="third-take-percentage">
                </div>
                
                <div class="form-group">
                    <label for="third-helf-given-percentage" class="col-form-label">Third 2nd Given Percentage:</label>
                    <input type="number" name="third_helf_given_parcentage" value="{{$balance->third_helf_given_parcentage}}" class="form-control" id="third-helf-given-percentage">
                </div>
                
                <div class="form-group">
                    <label for="third-full-given-percentage" class="col-form-label">Third 3rd Given Percentage:</label>
                    <input type="number" name="third_full_given_parcentage" value="{{$balance->third_full_given_parcentage}}" class="form-control" id="third-full-given-percentage">
                </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
              </div>
              </form>
            </div>
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
			                       <th>Total Bet</th>
			                       <th>Win Balance</th>
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
			                       <td>
							     @if($game_serve_detail->winner == "grapes")
				                  <img src="{{asset('public/game/grady/')}}/image/cabbage_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "banana")
				                  <img src="{{asset('public/game/grady/')}}/image/corn_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "apple")
				                  <img src="{{asset('public/game/grady/')}}/image/tomoto_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "lemon")
				                  <img src="{{asset('public/game/grady/')}}/image/carrot_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "lion")
				                  <img src="{{asset('public/game/grady/')}}/image/steak_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "cat")
				                  <img src="{{asset('public/game/grady/')}}/image/kabab_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "tiger")
				                  <img src="{{asset('public/game/grady/')}}/image/meat_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "horse")
				                  <img src="{{asset('public/game/grady/')}}/image/hotdog_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "vegetable")
				                  <img src="{{asset('public/game/grady/')}}/image/salad.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_detail->winner == "animals")
				                  <img src="{{asset('public/game/grady/')}}/image/pizza.png" class="trendcoing" style=" width: 48px; ">;
				               @else
				               @endif

			                      <td>{{$game_serve_detail->game_balance}}  </td>
			                      <td>{{$game_serve_detail->win_balance}}  </td>
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
			                 <th>Total Bet</th>
			                 <th>Win Balance</th>
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
			                       <th>Pots</th>
			                       <th>Tray ID</th>
			                       <th>Amount</th>
			                     
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
			                      <td>
			                        @if($game_serve_users_detail->pot_no == "grapes")
				                  <img src="{{asset('public/game/grady/')}}/image/cabbage_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "banana")
				                  <img src="{{asset('public/game/grady/')}}/image/corn_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "apple")
				                  <img src="{{asset('public/game/grady/')}}/image/tomoto_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "lemon")
				                  <img src="{{asset('public/game/grady/')}}/image/carrot_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "lion")
				                  <img src="{{asset('public/game/grady/')}}/image/steak_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "cat")
				                  <img src="{{asset('public/game/grady/')}}/image/kabab_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "tiger")
				                  <img src="{{asset('public/game/grady/')}}/image/meat_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "horse")
				                  <img src="{{asset('public/game/grady/')}}/image/hotdog_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no== "vegetable")
				                  <img src="{{asset('public/game/grady/')}}/image/salad.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($game_serve_users_detail->pot_no == "animals")
				                  <img src="{{asset('public/game/grady/')}}/image/pizza.png" class="trendcoing" style=" width: 48px; ">;
				               @else
				               @endif

			                       </td>
			                      <td>{{$game_serve_users_detail->tray_id}}  </td>
			                      <td>{{$game_serve_users_detail->amount}}  </td>
			                      
			                     
			                      <td>@if($game_serve_users_detail->status==0) Hold @elseif($game_serve_users_detail->status==1) Win @else Loss @endif </td>
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                 <th>User </th>
			                 <th>User ID</th>
			                 <th>pots</th>
			                 <th>Tray ID</th>
			                 <th>Tray ID</th>
			                
			                 <th>Status</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>

</div>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script type="text/javascript">
    function fetchData() {
    $.ajax({
        url: '{{ URL::route('gready_fetch.data') }}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Fade out the elements
            $('#gready_balance, #gready_second_balance,#gready_third_balance').fadeOut(500, function() {
                // Update the text with the new data
               $('#gready_balance').text(Math.round(data.game_balance));
                $('#gready_second_balance').text(Math.round(data.second_balance));
                $('#gready_third_balance').text(Math.round(data.third_balance));

                // Check if the amount is negative and apply red color if true
                if (parseFloat(data.game_balance) < 0) {
                    $('#gready_balance').css('color', 'red');
                } else {
                    $('#gready_balance').css('color', 'green'); // Reset to default color
                }

                if (parseFloat(data.second_balance) < 0) {
                    $('#gready_second_balance').css('color', 'red');
                } else {
                    $('#gready_second_balance').css('color', 'green'); // Reset to default color
                }if (parseFloat(data.third_balance) < 0) {
                    $('#gready_third_balance').css('color', 'red');
                } else {
                    $('#gready_third_balance').css('color', 'green'); // Reset to default color
                }

                // Fade in the elements with the new text
                $('#gready_balance, #gready_second_balance,#gready_third_balance').fadeIn(500);
            });

            // Set up the next AJAX request after 5 seconds
            setTimeout(fetchData, 30000);
        },
        error: function(xhr, status, error) {
            console.error(error);

            // Set up the next AJAX request after 4 seconds
            setTimeout(fetchData, 30000);
        }
    });
}

// Start the initial AJAX request
fetchData();


</script>


@endif


@endsection