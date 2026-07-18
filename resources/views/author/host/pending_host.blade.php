@extends('author.layouts.main')
@section('content')
<!--/.Content Header (Page header)--> 
<div class="body-content container-fluid flex-grow-1 container-p-y">

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-4">Panding Host List</p>
            <div class="table-responsive">
                <table class="table display table-bordered table-striped table-hover basic">
                    <thead>

                   <th>Sl</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Level</th>
                    <th>Email</th>
                    <th>Agency</th>
                     <th>Country</th>
                    <th>Action</th>
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
                    <td><img style="width: 73px;" src="{{URL::to($item->profile)}}"></td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->id}}</td>
                    <td>{{$item->level}}</td>
                    <td>{{$item->email}}</td>
                    <td> @if($agency) {{$agency->name}} @else @endif </td>
                     <td> @if($country) <img style="width: 73px;" src="{{URL::to($country->flag)}}">  @else @endif </td>
                    <td>
                       <a href="{{route('author.host.profile', ['id' => $item->id])}}" class="btn btn-sm btn-info" ><span class="fa fa-eye"></span>View</a>
                    </td>
                    
                  </tr>
                  @endforeach
                 </tbody>
             </table>
         </div>
     </div>
 </div>
</div>
 @endsection