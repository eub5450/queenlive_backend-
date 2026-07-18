@extends('subadmin.layouts.main')


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
                          <h4> Agency List</h4>
                      </div>
                      <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                <thead>
                  <tr>
                   <th>Sl</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Level</th>
                   
                    <th>Agency Name</th>
                    <th>Status</th>
                    <th>Agency Code</th>
                    
                    <th>Country</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                  @endphp
                  @foreach($agencys as $item)
                 @php
                 $user=App\Models\User::find($item->user_id);
                 $country=App\Models\Country::find($item->country_id);
                 @endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td>@if($user) <img style="width: 73px;" src="{{URL::to($item->logo)}}"> @endif</td>
                    <td> @if($user) {{$user->name}} @else @endif </td>
                    <td> @if($user) {{$user->id}} @else @endif </td>
                    <td> @if($user) {{$user->level}} @else @endif </td>
 
                    <td>{{$item->name}}</td>
                    <td>
                        @if($item->status==0)
                     
                       <a href="{{URL::to('sub_admin/admin-agency-active',$item->id)}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span> Actived</a>
                       @else
                       
                       @endif</td>
                    <td>{{$item->code}}</td>
                    <td> @if($country) <img style="width: 73px;" src="{{URL::to($country->flag)}}">  @else @endif </td>
                   
                    <td>
                     
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModals{{$item->id}}">
Edit
</button>
<!-- Modal -->
<div class="modal fade" id="exampleModals{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="#" enctype="multipart/form-data" method="post">
			             @csrf
			       
			          
			          <div class="form-group">
			            <label for="recipient-name" class="col-form-label">Name:</label>
			            <input type="text" name="name" value="{{ $item->name }}" class="form-control" id="recipient-name" required="">
			          </div>
			          <div class="form-group">
			            <label for="recipient-name" class="col-form-label">logo:</label>
			            <input type="file" name="logo" class="form-control" id="recipient-name">
			            <input type="hidden" value="{{$item->logo}}"  name="old_logo" class="form-control" id="recipient-name">
			          </div>
			        
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			        <button type="submit" class="btn btn-primary">Save</button>
			      </div>
			      </form>
                 </div>
            </div>
        </div>
                     
                  
                  
                    </td>
           
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
            
                    <th>Agency Name</th>
                    <th>Agency Code</th>
                    <th>Country</th>
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