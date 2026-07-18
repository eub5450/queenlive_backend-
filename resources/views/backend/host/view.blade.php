@extends('backend.layouts.main')


@section('title')
Supplier  
@endsection
@section('content')
@php
$agency=App\Models\Agency::where('code',$data->agency_code)->first();
$user=App\Models\User::find($data->user_id);
@endphp
<div class="body-content">
	
			<div class="row ">
				<div class="col-sm-6 col-xs-12 contact">
					<div class="card">
						
							<div class="box-body">
								<center><h3 class="widget-user-username">{{ $data->name }} -{{$user->id}}</h3></center>
								<address class="mb-0 text-center">
									<img src="{{ \App\Support\MediaPathHelper::publicUrl($user->profile) }}" style="width: 146px;" onerror="this.src='{{ \App\Support\MediaPathHelper::publicUrl('store/profile/default.png') }}'">
									
								</address>
								<a href="{{URL::to('active_host/'.$data->user_id)}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span> Active</a>
								<a href="{{URL::to('reject_host/'.$data->user_id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-cross"></span> Reject</a>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-xs-12 balance">
						<div class="card">
							<div class="box box-info">
								<div class="box-header with-border text-center">
									<h3 class="box-title">Agency Info</h3>
								</div>
								<div class="box-body">
									<address class="mb-0 text-center">
										<p><b>Name: </b> {{$agency->name}} </p>
										<p><b>Code: </b> {{$agency->code}} </p>
										<p><b>Phone: </b> {{$agency->phone}} </p>

									</address>
								</div>
							</div>
						</div>		
					</div>
				</div>
			</div>
            <div class="body-content">
	
			 <div class="row">

               <div class="col-xl-6 col-sm-6 py-2">
               	<div class="card ">
			 		<div class="card-body">
			 				<p><b>Nid: </b> {{$data->nid}} </p>
							<p><b>Phone: </b> {{$data->phone}} </p>
                      <p><b>Hosting For: </b> @if($data->hosting_type==2) Video @else Audio @endif</p>
							
                    
                </div>
            </div>
		</div> 
		<div class="col-xl-6 col-sm-6 py-2">
               	<div class="card ">
			 		<div class="card-body">
			 		
                    <img style=" width: 80%; " src="{{ \App\Support\MediaPathHelper::publicUrl($data->image) }}" onerror="this.src='{{ \App\Support\MediaPathHelper::publicUrl('store/profile/default.png') }}'">
                </div>
            </div>
		</div>
		<div class="col-xl-6 col-sm-6 py-2">
               	<div class="card ">
			 		<div class="card-body">
                    <img style=" width: 80%; " src="{{ \App\Support\MediaPathHelper::publicUrl($data->photo_id) }}" onerror="this.src='{{ \App\Support\MediaPathHelper::publicUrl('store/profile/default.png') }}'">
                </div>
            </div>
		</div>
		<div class="col-xl-6 col-sm-6 py-2">
               	<div class="card ">
			 		<div class="card-body">
                    <img style=" width: 80%; " src="{{ \App\Support\MediaPathHelper::publicUrl($data->selfie) }}" onerror="this.src='{{ \App\Support\MediaPathHelper::publicUrl('store/profile/default.png') }}'">
                </div>
            </div>
		</div>
	</div>
</div>
@endsection
