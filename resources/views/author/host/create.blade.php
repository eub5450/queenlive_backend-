@extends('author.layouts.main')
@section('content')
<!--/.Content Header (Page header)-->
<div class="body-content container-fluid flex-grow-1 container-p-y">
  <div class="card mb-4">
    <h6 class="card-header">Host Create</h6>
    <div class="card-body">
      <form action="{{route('country.author.host-store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
          <div class="form-group col-md-4">
            <label class="form-label">Host ID</label>
           <select class="custom-select" name="host_id" required="">
            <option>Select User</option>
              @foreach($host as $host)
              <option value="{{$host->id}}">{{$host->id}}-{{$host->name}}</option>
              @endforeach
            </select>
            <div class="clearfix"></div>
          </div>
          <div class="form-group col-md-4">
            <label class="form-label">Agency</label>
             <select class="custom-select" name="agency_id" required="">
            <option>Select Agency</option>
              @foreach($agencys as $agency)
              <option value="{{$agency->code}}">{{$agency->code}}-{{$agency->name}}</option>
              @endforeach
            </select>
            <div class="clearfix"></div>
          </div>
          <div class="form-group col-md-4">
          <label class="form-label">Hosting Type</label>
          <select class="custom-select" name="hosting_type" required="">
              <option>Select Host Type</option>
              <option value="2">Video</option>
              <option value="1">Audio</option>
            </select>
          <div class="clearfix"></div>
        </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label class="form-label">Selfie</label>
            <input type="file" name="selfie"   class="form-control " placeholder="Agency Name" value="" id="selfie" onchange="readURL1(this);" required>
            <img src="#/logo.png" id="one" class="col-sm-4 col-form-label text-left"  >
          
            <div class="clearfix"></div>
          </div>
          <div class="form-group col-md-4">
            <label class="form-label">Nid</label>
                <input type="file" name="nid"   class="form-control " placeholder="Agency Name" value="" onchange="readURL2(this);" id="nid" required>
                         <img src="http://#/logo.png" id="two" class="col-sm-4 col-form-label text-left"  >
            <div class="clearfix"></div>
          </div>
          <div class="form-group col-md-4">
          <label class="form-label">Image</label>
         <input type="file" name="image"  class="form-control image"  placeholder="Agency Code" onchange="readURL3(this);" id="agency_code" required="">
        <img src="http://#/logo.png" id="three" class="col-sm-4 col-form-label text-left"  >
          <div class="clearfix"></div>
        </div>
        </div>
        
        
        <div class="form-row">
          <div class="form-group col-md-6">
            <label class="form-label">Nid Number</label>
           <input type="number" name="nid"  class="form-control image"  placeholder="Enter NID Number"  id="agency_code" required="">
            <div class="clearfix"></div>
          </div>
          <div class="form-group col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone_number"  class="form-control image"  placeholder="Enter Phone Number"  id="agency_code" required="">
          </div>
         
        </div>
        <div class="form-group">
          <label class="custom-control custom-checkbox m-0">
            <input type="checkbox" class="custom-control-input" required="">
            <span class="custom-control-label">Check this  checkbox</span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    function readURL1(input) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
              $('#one')
                  .attr('src', e.target.result)
                  .width(80)
                  .height(80);
          };
          reader.readAsDataURL(input.files[0]);
      }
   }
   function readURL2(input) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
              $('#two')
                  .attr('src', e.target.result)
                  .width(80)
                  .height(80);
          };
          reader.readAsDataURL(input.files[0]);
      }
   }function readURL3(input) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
              $('#three')
                  .attr('src', e.target.result)
                  .width(80)
                  .height(80);
          };
          reader.readAsDataURL(input.files[0]);
      }
   }
</script>
@endsection