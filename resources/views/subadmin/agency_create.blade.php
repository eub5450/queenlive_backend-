@extends('subadmin.layouts.main')
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
                        <h4 class="text-center font-weight-bold font-italic mt-3">New Agency</h4>
                    </div>
                </div>
                <form action="{{URL::to('subadmin/sub_admin/agency_store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                    @csrf
                    
                    <div class="form-group col-md-6 mb-3">
                        <label for="member" class="col-sm-4 col-form-label text-right">Member ID</label>
                        <input type="text" name="user_id" class="form-control col-sm-8 user_id" placeholder="Member Id" value="" id="user_id" required>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label for="name" class="col-sm-4 col-form-label text-right">User Name</label>
                        <input type="text" name="name" class="form-control col-sm-8" placeholder="User Name" value="" id="name" readonly="" required>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label for="agency_name" class="col-sm-4 col-form-label text-right">Agency Name</label>
                        <input type="text" name="agency_name" class="form-control col-sm-8" placeholder="Agency Name" value="" id="agency_name" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-6 mb-3">
                        <label for="agencycode" class="col-sm-4 col-form-label text-right">Agency Code</label>
                        <input type="text" name="agency_code" readonly="" class="form-control col-sm-8"  placeholder="Agency Code"  id="agencycode" required="">
                        <span class="text-danger"></span>
                    </div>

                   
                    
                    <div class="form-group col-md-6 mb-3">
                        <label for="logo" class="col-sm-4 col-form-label text-right">Photo Id</label>
                        <input type="file" name="photo_id" class="form-control col-sm-8" id="logo" placeholder="Logo" onchange="readURL2(this);" value="" required="">
                       <img src="http://bplive.site/logo.png" id="two" class="col-sm-4 col-form-label text-left"  >
                        <span class="text-danger"></span>
                    </div> 
                    <div class="form-group col-md-6 mb-3">
                        <label for="logo" class="col-sm-4 col-form-label text-right">Selfie</label>
                        <input type="file" name="selfie" class="form-control col-sm-8" id="logo" placeholder="Logo" onchange="readURL3(this);" value="" required="">.
                       <img src="http://bplive.site/logo.png" id="three" class="col-sm-4 col-form-label text-left"  >
                        <span class="text-danger"></span>
                    </div> 
                    <div class="form-group col-md-6 mb-3">
                        <label for="phone" class="col-sm-4 col-form-label text-right">Phone</label>
                        <input type="text" name="phone" class="form-control col-sm-8" id="phone" placeholder="Phone Number" value=""  required>
                        <span class="text-danger"></span>
                    </div>
                        
                    <!-- NID (2026-07-18: agencies.nid was never collected by this form) -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="nid" class="col-sm-4 col-form-label text-right">NID</label>
                        <input type="text" name="nid" class="form-control col-sm-8" id="nid" placeholder="NID Number" value="" required>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group col-md-12 mb-3">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
   </div>
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
</script>
  <script type="text/javascript">
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
   }
</script>
  <script type="text/javascript">
    function readURL3(input) {
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
<script>
  $(document).ready(function() {
    $(document).on('keyup change','#user_id', function() {
      var number= $(this).val();
      var check_number=number.toString().length;
      $('#has_order_text').text('');
      if (check_number==5) {
        $.ajax({
          url: "subadmin/sub_admin/get/user_info/" + number,
          type: "GET",
          dataType:"json",
          success: function(data) {
            $('#name').val(data.user.name);
            $('#agencycode').val(data.next_agency_code);
            
            if ($.isEmptyObject(data.error)) {
              Toast.fire({
                type: 'success',
                title: data.success
              })
            } else {
              Toast.fire({
                type: 'error',
                title: data.error
              })
            }
          },

        });

      } else {
        // alert('danger');
      }

    });

  });



</script>
 
@endsection