@extends('subadmin.layouts.main')
@section('title')
Create New Agency
@endsection
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<!--Content Start-->
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-group">
                <div class="col-sm-12">
                    <h4 class="text-center font-weight-bold font-italic mt-3">New ID Ban</h4>
                </div>
            </div>
            <form action="{{URL::to('subadmin/sub_admin/banned_store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                @csrf

                <div class="form-group col-md-6 mb-3">
                    <label for="member" class="col-sm-4 col-form-label text-right">User Id</label>
                    <select name="user_id" class="form-control select_agency_id" required="" id="user_id">
                       @foreach($users as $user)
                       <option value="{{$user->id}}">{{$user->id}} -- {{$user->name}}</option>
                       @endforeach
                   </select>
                   <span class="text-danger"></span>
                </div>

            <div class="form-group col-md-6 mb-3">
                <label for="name" class="col-sm-4 col-form-label text-right">Confirm Id Number*</label>
                <input type="number"  name="id_number" class="form-control col-sm-8" placeholder="Enter The Id Number For Confirm" value="" id="deposit" required>
                <span class="text-danger"></span>
            </div>
            <div class="form-group col-md-6 mb-3">
                <label for="name" class="col-sm-4 col-form-label text-right">Ban Type*</label>
                <select name="ban_type" class="form-control" required="" id="ban_type">
                	<option value="D">Ban Type (D)</option>
                	<option value="C">Ban Type (C)</option>
                	<option value="B">Ban Type (B)</option>
                	
                </select>
                <span class="text-danger"></span>
            </div>
            <div class="form-group col-md-6 mb-3">
                <label for="name" class="col-sm-4 col-form-label text-right"> Give Proved** </label>
                <input type="file"  name="proved" class="form-control col-sm-8" placeholder="Enter The Id Number For Confirm" value="" id="deposit" required>
                <small style=" color: red; ">Without Proved You Can't Banned</small>
                <span class="text-danger"></span>
            </div>

            <div class="form-group col-md-12 mb-3">
                <button type="submit" class="btn btn-success">Active</button>
            </div>
        </form>
    </div>
</div>
</div>
<div class="body-content">
  <div class="card mb-4">
    <div class="card-body">
      <section class="forms">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h4> Invisibal ID List</h4>
                </div>
              <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                    <thead>
                      <tr>
                       <th>Sl</th>
                       <th>ID</th>
                       <th>Profile</th>
                       <th>Name</th>
                       <th>Level</th>
                       <th>Type</th>
                       <th>Proved</th>
                       <th>Action</th>
                   </tr>
               </thead>
                 <tbody>
                    @php
                    $i=0;
                    @endphp
                    @foreach($ban_ids as $ban_id)
                    <tr>
                      <td>{{ ++$i }}</td>
                      <td>{{$ban_id->id}}  </td>
                      <td> <img style="width: 73px;" src="{{URL::to($ban_id->profile)}}"></td>
                      <td>{{$ban_id->name}}  </td>
                      <td>{{$ban_id->level}}  </td>                      
                      <td>{{$ban_id->ban_type}}  </td>    
                        <td> <img style="width: 73px;" src="{{URL::to($ban_id->ban_proved)}}"></td>
                      <td>
                          {{-- <a href="{{URL::to('ban_id_reject/'.$ban_id->id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-cross"></span> Reject</a> --}}
                      </td>
                      
                  </tr>
                  @endforeach
              </tbody>
              <tfoot>
                <tr>
                   <th>Sl</th>
                   <th>ID</th>
                   <th>Profile</th>
                   <th>Name</th>
                   <th>Level</th>
                   <th>Type</th>
                   <th>Proved</th>
                   <th>Action</th>
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