@extends('backend.layouts.main')
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
                        <h4 class="text-center font-weight-bold font-italic mt-3">New Protal Recharge</h4>
                    </div>
                </div>
                <form action="{{URL::to('protal_recharge_store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                    @csrf
                    
                    <div class="form-group col-md-6 mb-3">
                        <label for="member" class="col-sm-4 col-form-label text-right">Protal Id</label>
                        <select name="user_id" class="form-control select_agency_id" required="" id="user_id">
                        	@foreach($users as $user)
                        	<option value="{{$user->id}}">{{$user->id}} -- {{$user->name}}</option>
                        	@endforeach
                        </select>
                        <span class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-6 mb-3">
                        <label for="name" class="col-sm-4 col-form-label text-right">Deposit Amount</label>
                        <input type="number"  name="deposit" class="form-control col-sm-8" placeholder="Deposit  Amount" value="0" id="deposit" required>
                        <span class="text-danger"></span>
                    </div>
                    
                        
                    <div class="form-group col-md-12 mb-3">
                        <button type="submit" class="btn btn-success">Active</button>
                    </div>
                </form>
            </div>
        </div>
   </div>
     
 
@endsection