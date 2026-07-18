@extends('backend.layouts.main')


@section('title')
Recharge List
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
                          <h4> Recharge List</h4>
                      </div>
                      <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                <thead>
                  <tr>
                  	<th>Sl</th>
                  	<th>ID</th>
                  	<th>Image</th>
                  	<th>Name</th>
                  	<th>Amount</th>
                  	<th>TxID</th>
                  	<th>Date</th>
                  	<th>Create By</th>
                  	<th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i=0;
                  @endphp
                  @foreach($data as $item)
              	@php
              	$recharge_by=App\Models\User::find($item->recharge_by);
              	$user=App\Models\User::find($item->user_id);
              
              	@endphp
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td> @if($user) {{$user->id}}  @endif</td>
                    <td> @if($user)<img style="width: 73px;" src="{{URL::to($user->profile)}}">@endif</td>
                    <td>  @if($user){{$user->name}} @endif </td>
                    <td>  {{$item->amount}}  </td>
                    <td>  {{$item->trxid}}  </td>
                    <td>  {{$item->date}}  </td>
                    <td>  @if($recharge_by) {{$recharge_by->name}} @endif  </td>
                
                    <td>
                       
                    </td>
                    
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sl</th>
                  	<th>ID</th>
                  	<th>Image</th>
                  	<th>Name</th>
                  	<th>Amount</th>
                  	<th>TxID</th>
                  	<th>Date</th>
                  	<th>Create By</th>
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