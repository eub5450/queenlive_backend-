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
                    <h4 class="text-center font-weight-bold font-italic mt-3">New Country</h4>
                </div>
            </div>
            <form action="{{URL::to('admin-country-store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                @csrf


            <div class="form-group col-md-6 mb-3">
                <label for="name" class="col-sm-4 col-form-label text-right">Enter New Country Name*</label>
                <input type="text"  name="name" class="form-control col-sm-8" placeholder="Enter Country" value="" id="deposit" required>
                <span class="text-danger"></span>
            </div>

            <div class="form-group col-md-6 mb-3">
                <label for="name" class="col-sm-4 col-form-label text-right"> Flag ** </label>
                <input type="file"  name="flag" class="form-control col-sm-8" placeholder="Enter The Id Number For Confirm" value="" id="deposit" required>
               
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
                    <h4> Country List</h4>
                </div>
              <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                    <thead>
                      <tr>
                       <th>Sl</th>
                       <th>Name</th>
                       <th>Flag</th>
                   </tr>
               </thead>
                 <tbody>
                    @php
                    $i=0;
                    @endphp
                    @foreach($data as $row)
                    <tr>
                      <td>{{ ++$i }}</td>
                      <td>{{$row->name}}  </td>
                      <td> <img style="width: 73px;" src="{{URL::to($row->flag)}}"></td>
                  </tr>
                  @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th>Sl</th>
                       <th>Name</th>
                       <th>Flag</th>
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