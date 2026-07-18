@extends('backend.layouts.main')


@section('title')
Pending Host List
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
                          <h4> Pending Host List</h4>
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
                    <th>Email</th>
                    <th>Agency</th>
                    <th>Country</th>
                    <th>Active</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                  @endphp
                  @foreach($users as $item)
                 @php
                 $agency=DB::table('host_data')->join('agencies','agencies.code','host_data.agency_code')->where('host_data.user_id',$item->id)->first();
                 $country=App\Models\Country::find($item->country_id);
                 @endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td><img style="width: 73px;" src="{{ \App\Support\MediaPathHelper::publicUrl($item->profile) }}"></td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->id}}</td>
                    <td>{{$item->level}}</td>
                    <td>{{$item->email}}</td>
                    <td> @if($agency) {{$agency->name}} @else @endif </td>
                       <td> @if($country) <img style="width: 73px;" src="{{ \App\Support\MediaPathHelper::publicUrl($country->flag) }}">  @else @endif </td>
                    <td>
                        {{-- <a href="{{URL::to('active_host/'.$item->id)}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span></a> --}}
                        <a href="{{URL::to('host/view/'.$item->id)}}" class="btn btn-sm btn-info" ><span class="fa fa-eye"></span></a>

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
                    <th>Email</th>
                    <th>Agency</th>
                    <th>Country</th>
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
