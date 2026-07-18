@extends('subadmin.layouts.main')


@section('title')
Supplier  
@endsection
@section('content')
<style type="text/css">
	.resume {
    background-color: #D5F5E3;
}
.card-header {
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    background-image: url(../img/profile-bg.jpg);
    background-size: cover;
    background-position: center center;
    padding: 30px 15px;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}
</style>

<div class="body-content">
	<div class="row">
   <div class="col-sm-12 col-md-4 employee-cv">
      <div class="card-header resume">
         <div><img src="{{URL::to($user->profile)}}" style=" border-radius: 50%; " width="100px;" height="100px;" class="img-circle"></div>
      </div>
      <div class="card-content">
         <div class="card-content-member">
            <h4 class="m-t-0">Name : {{ $user->name }}</h4>
            <h5> ID :{{$user->id}}</h5>
           {{--  <p class="m-0"><i class="fa fa-mobile" aria-hidden="true"></i>
               {{number_format($user->balance)}}
            </p> --}}
         </div>
         <div class="card-content-languages">
            <div class="card-content-languages-group"></div>
            <div class="card-content-languages-group">
               <table class="table table-hover" width="100%">
                  <caption class="resumecaption">Agency Information</caption>
                  <tbody>
                     <tr>
                        <th>Lavel</th>
                        <td>{{$user->level}}</td>
                     </tr>
                   
                    
                     <tr>
                        <th>Vip Lavel</th>
                        <td>{{$user->is_vip}}</td>
                     </tr>
                     <tr>
                        <th>Entry </th>
                        <td>{{$user->entry_level}}</td>
                     </tr> 
                     @php
                     $contry=App\Models\Country::find($user->country_id);
                     @endphp
                     <tr>
                        <th>Country</th>
                        <td>@if($contry) {{$contry->name}} @endif</td>
                     </tr>
                    
                  </tbody>
               </table>
            </div>
						
            	 @if($agency_info)
            <div class="card-content-languages-group">
               <table class="table table-hover" width="100%">
                 
                  <tbody>
                     <tr>
                        <th>Join Agency Name</th>
                        <td> {{$agency_info->name}}</td>
                     </tr>
                     <tr>
                        <th>Code</th>
                        <td>{{$agency_info->code}}</td>
                     </tr>
                   

                  </tbody>
               </table>
            </div>
               @endif



         </div>
       
      </div>
   </div>
   <div class="col-sm-12 col-md-8 employee-cv-info">
      <div class="row">
      			
         <div class="col-sm-12 col-md-12 rating-block">
         	 @if($agency)
            <table class="table table-hover" width="100%">
               <caption class="resumecaption">Agency Woner</caption>
               <tbody>
                  <tr>
                     <th><b>Name: </b> </th>
                     <td>{{$agency->name}}</td>
                  </tr>
                  <tr>
                     <th>Code</th>
                     <td>{{$agency->code}}</td>
                  </tr>
                 
               </tbody>
            </table>
            @endif
         </div>
         @php
                          $date= Carbon\Carbon::now('Europe/London'); // Replace this with your desired date
                            $user_id=$user->id;
                             $start_date = date('Y-m') . '-01';
                          
                             $end_date = date('Y-m') . '-31';
                             
                             $type = DB::table('users')
                            ->join('host_data', 'host_data.user_id', 'users.id')
                            ->where('users.id', $user_id)
                            ->select('host_data.hosting_type','host_data.id')
                            ->first();

                                if ($type) {
                                  $dayTimeHistory = DB::table('day_times')
                                ->where('user_id', $user_id)
                                ->where('live_time', '>=', $start_date)
                                ->where('live_time', '<=', $end_date)
                                ->get();
                                	$running_durations = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('live_time', '>=', $start_date)
                                    ->where('live_time', '<=', $end_date)
                    				->where('brd_type',$type->hosting_type)
                    				->where('day_times', '>', '00:19:59')
                    				->select('day_times')
                    				->get();
                    
                                    function addDurations($duration1, $duration2) {
                                    $time1 = explode(':', $duration1);
                                    $time2 = explode(':', $duration2);
                            
                                    $hours = intval($time1[0]) + intval($time2[0]);
                                    $minutes = intval($time1[1]) + intval($time2[1]);
                                    $seconds = intval($time1[2]) + intval($time2[2]);
                            
                                    if ($seconds >= 60) {
                                        $minutes += 1;
                                        $seconds -= 60;
                                    }
                            
                                    if ($minutes >= 60) {
                                        $hours += 1;
                                        $minutes -= 60;
                                    }
                            
                                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                }
                                
                                $totalDuration = '00:00:00';
                                foreach ($running_durations as $duration){
                        
                                // Parse the duration as a DateTime object
                                $durationTime = new DateTime($duration->day_times);
                        
                                // Add the current duration to the total
                                $totalDuration = addDurations($totalDuration, $durationTime->format('H:i:s'));
                                }
                           
                    
                    	   // $running_totalDurationFormatted = $running_totalDuration->format('H:i:s');
                    
                    	          
                                    
                    				$total_coin= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                                    ->whereDate('date', '<=', $end_date)->select('gifts.date','gifts.value')->sum('gifts.value');
                                    
                                    $day_time_hostory = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('live_time', '>=', $start_date)
                                    ->where('live_time', '<=', $end_date)
                                    ->orderby('id','desc')
                    				->get();
                    				
                    				
               $day_time_data = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('live_time', '>=', $start_date)
                                    ->where('live_time', '<=', $end_date)
                                    ->orderby('id','desc')
                    				->get();

                                  $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $user_id)
                                        ->where('live_time', '>=', $start_date)
                                        ->where('live_time', '<=', $end_date)
                                        ->where('brd_type', $type->hosting_type)
                                        ->where('day_times', '>', '00:19:59')
                                        ->select('live_time', 'day_times')
                                        ->get();
                                    
                                    $running_day_count = 0;
                                    $current_date = null;
                                    $total_duration = 0;
                                    
                                    foreach ($day_time_duration as $day_time_duration) {
                                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                        $time = $day_time_duration->day_times;
                                    
                                        if ($current_date === null || $current_date !== $date) {
                                            // Check if the previous day's total duration exceeds 01:01:00
                                            if ($current_date !== null && $total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                                $running_day_count++;
                                            }
                                    
                                            $current_date = $date;
                                            $total_duration = 0;
                                        }
                                    
                                        $duration_parts = explode(':', $time);
                                        $hours = intval($duration_parts[0]);
                                        $minutes = intval($duration_parts[1]);
                                        $seconds = intval($duration_parts[2]);
                                        $total_duration += ($hours * 3600) + ($minutes * 60) + $seconds;
                                    }
                                    
                                    // Check the total duration of the last date
                                    if ($total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                        $running_day_count++;
                                    }
                    				}
                    				$day_time = "00:00:00";
list($hours, $minutes, $seconds) = explode(':', $day_time);

$total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
			   $total_withdraw= DB::table('withdraws')->where('host_id',$user_id)->whereDate('date', '>=', $start_date)
                                    ->whereDate('date', '<=', $end_date)->sum('total');            
         @endphp

		
         	 @if($type)
         	
         <div class="col-sm-12 col-md-12 rating-block">
            <table class="table table-hover" width="100%">
               <caption class="resumecaption">Live Data {{$total_seconds}}</caption>
               <tbody>
                  <tr>
                     <th><b>Hosting Type </b> </th>
                     <td> @if($type->hosting_type==2) Video @else Audio @endif <a href="{{URL::to('subadmin/hosting_type_change/'.$type->id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-check"></span>@if($type->hosting_type==2) Make Audio @else Make Video @endif</a></td>
                  </tr>
                  <tr>
                  
                     <th>Day</th>
                     <td>{{$running_day_count}}</td>
                  </tr>
                  <tr>
                     <th>Time</th>
                     <td> {{$totalDuration}}</td>
                  </tr>
                  <tr>
                     <th>Point Collect</th>
                     <td> {{number_format($total_coin)}}</td>
                  </tr>
                   <tr>
                     <th>Total Withdaw</th>
                     <td> {{number_format($total_withdraw)}}</td>
                  </tr>
                  <tr>
                     <th>Now Points Have</th>
                     <td> {{number_format($total_coin-$total_withdraw)}}</td>
                  </tr>
                  <tr>
                     <th>Hosting ID</th>
                     <td> 
                     	 @if($user->is_host_id!=1)
					
					<a href="{{URL::to('subadmin/sub_admin/reject_host/'.$user->id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span>Host Reject</a>
					@endif
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
            @endif

            <div class="col-sm-12 col-md-12 rating-block">
            <table class="table table-hover" width="100%">
               <caption class="resumecaption">Power Button</caption>
               <tbody>

                 
                  <tr>
                     <th>Hosting Access</th>
                     <td> 
                        @if($user->is_host_id==0)
                        No Hosting Data Found
                        @endif
                        @if($user->is_host_id==2)
                        <a href="{{URL::to('subadmin/sub_admin/active_host/'.$user->id)}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span> Approved Hosting</a>
                        @endif

                     </td>
                  </tr>
                  
               </tbody>
            </table>
         </div>
          
      </div>
   </div>
</div>
	
	
</div>
@if($user->is_host_id==2)
            <div class="body-content">
			 <div class="row">
		<div class="col-xl-4 col-sm-4 py-2">
               	<div class="card ">
			 		<div class="card-body">
			 		
                    <img style=" width: 80%; " src="{{URL::to($info->image)}}">
                </div>
            </div>
		</div>
		<div class="col-xl-4 col-sm-4 py-2">
               	<div class="card ">
			 		<div class="card-body">
                    <img style=" width: 80%; " src="{{URL::to($info->photo_id)}}">
                </div>
            </div>
		</div>
		<div class="col-xl-4 col-sm-4 py-2">
               	<div class="card ">
			 		<div class="card-body">
                    <img style=" width: 80%; " src="{{URL::to($info->selfie)}}">
                </div>
            </div>
		</div>
	</div>
</div>
@endif
@if($agency)
@php
$host_lists=DB::table('host_data')->join('users','users.id','host_data.user_id')->where('agency_code',$agency->code)->get();
@endphp
<div class="body-content">
	
	<div class="row">

		<div class="col-xl-12 col-sm-12 py-2">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">Host Data</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					 <table class="table display table-bordered table-striped table-hover basic">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                       <th>Day Time</th>
			                       <th>Action</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($host_lists as $host_list)
			                     @php
		                         $date = Carbon\Carbon::now(); // Replace this with your desired date
                            $user_id=$host_list->id;
               
                     $host_start_date = 2023-06-16;
                             $host_end_date = 2023-06-31;
                             $host_type = DB::table('users')
                            ->join('host_data', 'host_data.user_id', 'users.id')
                            ->where('users.id', $user_id)
                            ->select('host_data.hosting_type','host_data.id')
                            ->first();

                                if ($host_type) {
                                	$durations = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('live_time', '>=', $host_start_date)
                                    ->where('live_time', '<=', $host_end_date)
                    				->where('brd_type',$host_type->hosting_type)
                    				->where('day_times', '>', '00:19:59')
                    				->select('day_times')
                    				->get();
                    
                    
        
                    	        $totalDuration = Carbon\Carbon::createFromTime(0, 0, 0);
                    
                    	        foreach ($durations as $duration) {
                    				$parts = explode(':', $duration->day_times);
                    
                    				$hours = intval($parts[0]);
                    				$minutes = intval($parts[1]);
                    				$seconds = intval($parts[2]);
                    
                    				$interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
                    				$totalDuration->add($interval);
                    	        }
                    
                    	    $totalDurationFormatted = $totalDuration->format('H:i:s');
                    
                    	          
                                    
                    				$total_coin= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->where('date', '>=', $host_start_date)
                                    ->where('date', '<=', $host_end_date)->select('users.profile','users.name','gifts.value')->sum('value');
                                    
                                    $day_time_hostory = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('live_time', '>=', $host_start_date)
                                    ->where('live_time', '<=', $host_end_date)
                                    ->orderby('id','desc')
                    				->get();
                    				
                    				
               

                                  $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $user_id)
                                        ->where('live_time', '>=', $host_start_date)
                                        ->where('live_time', '<=', $host_end_date)
                                        ->where('brd_type', $host_type->hosting_type)
                                        ->where('day_times', '>', '00:19:59')
                                        ->select('live_time', 'day_times')
                                        ->get();
                                    
                                    $day_count = 0;
                                    $current_date = null;
                                    $total_duration = 0;
                                    
                                    foreach ($day_time_duration as $day_time_duration) {
                                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                        $time = $day_time_duration->day_times;
                                    
                                        if ($current_date === null || $current_date !== $date) {
                                            // Check if the previous day's total duration exceeds 01:01:00
                                            if ($current_date !== null && $total_duration >= 3660) { // 3660 seconds = 1 hour 1 minute
                                                $day_count++;
                                            }
                                    
                                            $current_date = $date;
                                            $total_duration = 0;
                                        }
                                    
                                        $duration_parts = explode(':', $time);
                                        $hours = intval($duration_parts[0]);
                                        $minutes = intval($duration_parts[1]);
                                        $seconds = intval($duration_parts[2]);
                                        $total_duration += ($hours * 3600) + ($minutes * 60) + $seconds;
                                    }
                                    
                                    // Check the total duration of the last date
                                    if ($total_duration >= 3660) { // 3660 seconds = 1 hour 1 minute
                                        $day_count++;
                                    }
                    				}
			                    @endphp
			                
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$host_list->id}}  </td>
			              
			                      <td>{{$host_list->name}}  </td>
			                      
			                      <td>Day : {{$day_count}} <br>Time : {{$totalDurationFormatted}} </td>
			                      <td>
			                          <a href="#" class="btn btn-sm btn-success" ><span class="fa fa-eye"></span> View</a>
			                          <a href="#" class="btn btn-sm btn-danger" ><span class="fa fa-times"></span> Inactive</a>
			                      </td>
			                      
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>

			                       <th>Action</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>
		</div>
		
	</div>
</div>
@endif


@if($type)

<div class="body-content">
	
	<div class="row">

		<div class="col-xl-12 col-sm-12 py-2">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">Day Time History</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					 <table class="table display table-bordered table-striped table-hover basic">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>Channel Name</th>
			                       <th>Time</th>
			                       <th>Live Date</th>
			                       <th>Type</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($day_time_data as $day_time_h)
			                   
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$day_time_h->channelName}}  </td>
			              
			                      <td>{{$day_time_h->day_times}}  </td>
			                      <td>{{$day_time_h->live_time}}  </td>
			                      <td>@if($day_time_h->brd_type==2) Video @else Audio @endif  </td>
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                       <th>Channel Name</th>
			                       <th>Time</th>
			                       <th>Live Date</th>
			                       <th>Type</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>
		</div>
		
	</div>
</div>
@endif
@if($type)

<div class="body-content">
	
	<div class="row">

		<div class="col-xl-12 col-sm-12 py-2">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">Day Time Last RoundHistory</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					 <table class="table display table-bordered table-striped table-hover basic">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>Channel Name</th>
			                       <th>Time</th>
			                       <th>Live Date</th>
			                       <th>Type</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    @endphp
			                    @foreach($dayTimeHistory as $dayTimeHistory)
			                   
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$dayTimeHistory->channelName}}  </td>
			              
			                      <td>{{$dayTimeHistory->day_times}}  </td>
			                      <td>{{$dayTimeHistory->live_time}}  </td>
			                      <td>@if($dayTimeHistory->brd_type==2) Video @else Audio @endif  </td>
			                  </tr>
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                 <th>Sl</th>
			                       <th>Channel Name</th>
			                       <th>Time</th>
			                       <th>Live Date</th>
			                       <th>Type</th>
			               </tr>
			           </tfoot>
			       </table>
					
				</div>
			</div>
		</div>
		
	</div>
</div>
@endif

@endsection