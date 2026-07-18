@extends('author.layouts.main')
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
<div class="body-content container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-body">
                <div class="form-group">
                    <div class="col-sm-12">
                        <h4 class="text-center font-weight-bold font-italic mt-3">New Agency</h4>
                    </div>
                </div>
                <form action="{{ route('country.author.agency-store') }}" method="post" enctype="multipart/form-data" class="form-inline">
                    @csrf
                    
                    <div class="form-group col-md-6 mb-3">
                        <label for="member" class="col-sm-4 col-form-label text-right">Users ID</label>
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
                        <input type="file" name="photo_id" class="form-control col-sm-8" id="logo" placeholder="Logo" onchange="readURL2(this);" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" value="" required="">
                       <img src="http://#/logo.png" id="two" class="col-sm-4 col-form-label text-left"  >
                        <small class="col-sm-8 offset-sm-4" style="color: red; display: block; margin-top: 6px;">Only JPG, JPEG, PNG or WEBP. Max 2MB.</small>
                        <span class="text-danger"></span>
                    </div> 
                    <div class="form-group col-md-6 mb-3">
                        <label for="logo" class="col-sm-4 col-form-label text-right">Selfie</label>
                        <input type="file" name="selfie" class="form-control col-sm-8" id="logo" placeholder="Logo" onchange="readURL3(this);" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" value="" required="">.
                       <img src="http://#/logo.png" id="three" class="col-sm-4 col-form-label text-left"  >
                        <small class="col-sm-8 offset-sm-4" style="color: red; display: block; margin-top: 6px;">Only JPG, JPEG, PNG or WEBP. Max 2MB.</small>
                        <span class="text-danger"></span>
                    </div> 
                    <div class="form-group col-md-6 mb-3">
                        <label for="phone" class="col-sm-4 col-form-label text-right">Phone</label>
                        <input type="text" name="phone" class="form-control col-sm-8" id="phone" placeholder="Phone Number" value=""  required>
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
    function validateAgencyImage(input) {
      if (!input.files || !input.files[0]) {
          return true;
      }

      var file = input.files[0];
      var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
      var allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
      var extension = file.name.split('.').pop().toLowerCase();

      if (allowedTypes.indexOf(file.type) === -1 || allowedExtensions.indexOf(extension) === -1) {
          alert('Only JPG, JPEG, PNG or WEBP files are allowed.');
          input.value = '';
          return false;
      }

      if (file.size > 2 * 1024 * 1024) {
          alert('Image size must be 2MB or less.');
          input.value = '';
          return false;
      }

      return true;
   }
</script>
  <script type="text/javascript">
    function readURL2(input) {
      if (!validateAgencyImage(input)) {
          $('#two').attr('src', 'http://#/logo.png');
          return;
      }
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
      if (!validateAgencyImage(input)) {
          $('#three').attr('src', 'http://#/logo.png');
          return;
      }
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
 <script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous"></script>
<script>
  $(document).ready(function() {
    $(document).on('keyup change','#user_id', function() {
      var id= $(this).val();
      var check_number=id.toString().length;
      $('#has_order_text').text('');
      if (check_number==5) {


        $.ajax({
            url:"{{ route('author.user.info')}}",

          type: "GET",
          data: { id: id },
          dataType:"json",
          success: function(data) {
            if (data.user) {
              $('#name').val(data.user.name);
              $('#agencycode').val(data.next_agency_code || '');
              Toast.fire({
                type: 'success',
                title: data.success || 'User found'
              });
            } else {
              $('#name').val('');
              $('#agencycode').val('');
              Toast.fire({
                type: 'error',
                title: data.error || 'User not found for this country'
              });
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
