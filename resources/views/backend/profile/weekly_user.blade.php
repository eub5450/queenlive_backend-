@extends('backend.layouts.main')


@section('title')
Agency List
@endsection
@section('content')
<!--Content Start-->
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
<section class="forms">
      <div class="container-fluid">
          <div class="row">
              <div class="col-md-12">
                  <div class="card">
                      <div class="card-header d-flex align-items-center">
                          <h4> Weekly User List</h4>
                      </div>
                      <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover">
                <thead>
                  <tr>
                   <th>Sl</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Level</th>
                    <th>Total Recharge</th>
                    <th>Total Recharge Count</th>
                    <th>Balance</th>
                    <th>Reg</th>
                   
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                 
                  @endphp
                  @foreach($users as $user)
                    @php
                    $main_user_id=App\Models\User::find($user['user_id']);
                    if($main_user_id->game_priority==0){
                         $is_another_id_lock = App\Models\FortuneLock::where('imei_number', $main_user_id->imei_number)->first();
                        if(!$is_another_id_lock){ 
                         $check_id_have_already=App\Models\FortuneLock::where('user_id',$main_user_id->id)->where('type',1)->first();
                        if(!$check_id_have_already){
                        $data = new  App\Models\FortuneLock();
                        $data->user_id = $main_user_id->id;
                        $data->type = 1;
                        $data->imei_number = $main_user_id->imei_number;
                        $data->auto_lock_active = 'Auto Lock New User & and Goes To Priority';
                        $data->parcentage = $is_another_id_lock ? $is_another_id_lock->parcentage : 1;
                        $data->save();
                    }else{
                        $check_id_have_already=App\Models\FortuneLock::where('user_id',$main_user_id->id)->where('auto_lock_active','!=',null)->first();
                        if($check_id_have_already){
                            $check_id_have_already->delete();
                        }
                    }
                    }
                    $main_user_id->game_priority=1;
                    $main_user_id->save();
                    }
                    @endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td>@if($user) <img style="width: 73px;" src="{{URL::to($user['profile'])}}"> @endif</td>
                    <td> @if($user) {{$user['name']}} @else @endif </td>
                    <td> @if($user) {{$user['user_id']}} @else @endif </td>
                    <td> @if($user) {{$user['level']}} @else @endif </td>
                    <td> @if($user) {{$user['recharge_sum']}} @else @endif </td>
                    <td> @if($user) {{$user['recharge_count']}} @else @endif </td>
                    <td> @if($user) {{$user['balance']}} @else @endif </td>
                    <td> @if($user) {{$user['date']}} @else @endif </td>


                    
                  </tr>
             
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sl</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Level</th>
                    <th>Total Recharge</th>
                    <th>Total Recharge Count</th>
                    <th>Balance</th>
                    <th>Reg</th>
                  
                  </tr>
                </tfoot>
              </table>
            </div>
                  </div>
              </div>
          </div>
      </div>
  </section>
</div>
</div>
</div>
  @endsection