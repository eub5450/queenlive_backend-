
<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  

    <link rel="apple-touch-icon" type="image/png" href="https://cpwebassets.codepen.io/assets/favicon/apple-touch-icon-5ae1a0698dcc2402e9712f7d01ed509a57814f994c660df9f7a952f3060705ee.png" />

    <meta name="apple-mobile-web-app-title" content="CodePen">

    <link rel="shortcut icon" type="image/x-icon" href="https://cpwebassets.codepen.io/assets/favicon/favicon-aec34940fbc1a6e787974dcd360f2c6b63348d4b1f4e06c77743096d55480f33.ico" />

    <link rel="mask-icon" type="image/x-icon" href="https://cpwebassets.codepen.io/assets/favicon/logo-pin-b4b4269c16397ad2f0f7a01bcdf513a1994f4c94b8af2f191c09eb0d601762b1.svg" color="#111" />



  
  <title>QueenLive Agency Request From</title>
    <link rel="canonical" href="https://codepen.io/Paviethra_A/pen/yLKayOL" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>


  
<link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'>
<link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css'>
<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.0/css/bootstrapValidator.min.css'>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet">
<style>
#success_message{ display: none;}
</style>

  <script>
  window.console = window.console || function(t) {};
</script>

  
  
</head>

<body translate="no">
  <div class="container">

    <form class="well form-horizontal" action="{{URL::to('add_agency_from_submit')}}"enctype="multipart/form-data" method="post"  id="contact_form">
      @csrf
  <fieldset>

<!-- Form Name -->
  <legend><center><h2><b>QueenLive Agency Request Form</b></h2></center></legend><br>

<!-- Text input-->

<div class="form-group">
  <label class="col-md-4 control-label">User ID <span style=" color: red; font-weight: 900; ">*</span></label>  
  <div class="col-md-4 inputGroupContainer">
  <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input type="text" name="user_id" class="form-control" placeholder="Member Id" value="" id="user_id" required>
    </div>
  </div>
</div>

<!-- Text input-->

<div class="form-group">
  <label class="col-md-4 control-label" >Agency Name <span style=" color: red; font-weight: 900; ">*</span></label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
   <input type="text" name="agency_name" class="form-control col-sm-8" placeholder="Agency Name" value="" id="agency_name" required>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" >Phone Number <span style=" color: red; font-weight: 900; ">*</span></label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input name="phone" placeholder="Enter Phone number" class="form-control"  type="number" required="">
    </div>
  </div>
</div>

<!-- Text input-->

<div class="form-group">
  <label class="col-md-4 control-label" >Selfie With NID <span style=" color: red; font-weight: 900; ">*</span></label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input name="selfie" placeholder="Password" class="form-control" onchange="readURL1(this);" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required="" type="file">
  <img src="" id="one" class="col-sm-4 col-form-label text-left"  >
    </div>
    <small style="color: red; display: block; margin-top: 6px;">Only JPG, JPEG, PNG or WEBP. Max 2MB.</small>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" >Nid <span style=" color: red; font-weight: 900; ">*</span></label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input name="nid" placeholder="Password" class="form-control" onchange="readURL2(this);" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required="" type="file">
  <img src="" id="two" class="col-sm-4 col-form-label text-left"  >
    </div>
    <small style="color: red; display: block; margin-top: 6px;">Only JPG, JPEG, PNG or WEBP. Max 2MB.</small>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" >Your Pic <span style=" color: red; font-weight: 900; ">*</span></label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input name="photo_id" placeholder="Password" class="form-control" onchange="readURL3(this);" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required="" type="file">
   <img src="" id="three" class="col-sm-4 col-form-label text-left"  >

    </div>
    <small style="color: red; display: block; margin-top: 6px;">Only JPG, JPEG, PNG or WEBP. Max 2MB.</small>
  </div>
</div>
<div class="form-group"> 
  <label class="col-md-4 control-label">Country <span style=" color: red; font-weight: 900; ">*</span></label>
    <div class="col-md-4 selectContainer">
    <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-list"></i></span>
    <select name="country_id" class="form-control selectpicker" required="">
      <option value="">Select Country</option>
      @foreach($data as $item)
      <option value="{{$item->id}}">{{$item->name}}</option>
     @endforeach
      
    </select>
  </div>
</div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" >Account/Bank Details (Optional)</label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
 <textarea id="w3review" name="bank_details" rows="4" cols="40">

</textarea>
    </div>
  </div>
</div>

<!-- Button -->
<div class="form-group">
  <label class="col-md-4 control-label"></label>
  <div class="col-md-4"><br>
    &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<button type="submit" class="btn btn-warning" >&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspSUBMIT <span class="glyphicon glyphicon-send"></span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</button>
  </div>
</div>

</fieldset>
</form>
</div>
    </div><!-- /.container -->
    <script src="https://cpwebassets.codepen.io/assets/common/stopExecutionOnTimeout-2c7831bb44f98c1391d6a4ffda0e1fd302503391ca806e7fcc7b9b87197aec26.js"></script>

  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.4.5/js/bootstrapvalidator.min.js'></script>
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

    function readURL1(input) {
      if (!validateAgencyImage(input)) {
          $('#one').attr('src', '');
          return;
      }
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
      if (!validateAgencyImage(input)) {
          $('#two').attr('src', '');
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
   function readURL3(input) {
      if (!validateAgencyImage(input)) {
          $('#three').attr('src', '');
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
<script src="http://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>

<script>
  @if(Session::has('messege'))
  var type="{{Session::get('alert-type','info')}}"
  switch(type){
    case 'info':
    toastr.info("{{ Session::get('messege') }}");
    break;
    case 'success':
    toastr.success("{{ Session::get('messege') }}");
    break;
    case 'warning':
    toastr.warning("{{ Session::get('messege') }}");
    break;
    case 'error':
    toastr.error("{{ Session::get('messege') }}");
    break;
  }
  @endif  


</script> 

  
</body>

</html>
