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
                          <h4> Active Live  List</h4>
                      </div>
                      <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                <thead>
                  <tr>
                   <th>Sl</th>
                    <th>Channal</th>
                    <th>Host Name</th>
                    <th>ID</th>
                    <th>Brd Type</th>
                    <th>Active</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                  @endphp
                  @foreach($lives as $item)
                 @php
                 $user=App\Models\User::find($item->user_id);
                 @endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    
                    <td>{{$item->channelName}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->user_id}}</td>
                    <td>@if($item->type==2) Video @else Audio @endif</td>
                    <td>
                        <a href="{{URL::to('sub_admin/admin-brd-off/'.$item->id)}}" class="btn btn-sm btn-danger" ><span class="fa fa-close"></span> Off</a>
                    </td>
                    
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sl</th>
                    <th>Channal</th>
                    <th>Host Name</th>
                    <th>ID</th>
                    <th>Brd Type</th>
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