@extends('author.layouts.main')
@section('content')
<!--/.Content Header (Page header)--> 
<div class="body-content container-fluid flex-grow-1 container-p-y">

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-4">Agency List</p>
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
                    <th>Agency Name</th>
                    <th>Agency Code</th>
                     <th>Status</th>
                    <th>Country</th>
                    <th>Active</th>
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
                    <td>@if($user) <img style="width: 73px;" src="{{URL::to($user->profile)}}"> @endif</td>
                    <td> @if($user) {{$user->name}} @else @endif </td>
                    <td> @if($user) {{$user->id}} @else @endif </td>
                    <td> @if($user) {{$user->level}} @else @endif </td>
                    <td> @if($user) {{$user->email}} @else @endif </td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->code}}</td>
                    <td>@if($item->status==0) Pending @else Actived @endif</td>
                    <td> @if($country) <img style="width: 73px;" src="{{URL::to($country->flag)}}">  @else @endif </td>
                    <td>
                      @if($item->status==0)
                     
                       <a href="{{route('country.author.agency-active', ['id' => $item->id])}}" class="btn btn-sm btn-success" ><span class="fa fa-check"></span> Actived</a>
                       @else
                       <a href="{{route('country.author.agency-reject', ['id' => $item->id])}}" class="btn btn-sm btn-warning" ><span class="fa fa-close"></span> Reject</a>
                       @endif
                      
                     
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