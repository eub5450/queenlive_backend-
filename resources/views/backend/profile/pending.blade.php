@extends('backend.layouts.main')


@section('title')
Agency List
@endsection
@section('content')
<style>
    .zoom:hover, .zoom:active, .zoom:focus {
    -ms-transform: scale(2.5);
    -moz-transform: scale(2.5);
    -webkit-transform: scale(2.5);
    -o-transform: scale(2.5);
    transform: scale(2.5);
    position: relative;
    z-index: 100;
    height: 150px !important;
    width: 120px !important;
}
.zoom {
    -webkit-transition: all 0.35s ease-in-out;
    -moz-transition: all 0.35s ease-in-out;
    transition: all 0.35s ease-in-out;
    cursor: -webkit-zoom-in;
    cursor: -moz-zoom-in;
    cursor: zoom-in;
}

</style>
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
                          <h4> Pending List</h4>
                      </div>
                      <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                <thead>
                  <tr>
                   <th>Sl</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    
                    <th>Active</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                  @endphp
                  @foreach($data as $item)
                 @php
                 $user=App\Models\User::find($item->user_id);
                
                 @endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td> @if($user) {{$user->id}} @else @endif </td>
                    <td> {{$item->name}}  </td>
                    <td> <img class="img-fluid thumbnail zoom" style="width: 73px;" src="{{URL::to($item->image)}}"></td>
                    <td>
                       <a href="{{URL::to('profile_approved',$item->id)}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span> Approved</a>
                       <a href="{{URL::to('profile_reject',$item->id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span> Reject</a>
                      
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sl</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    
                    <th>Active</th>
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