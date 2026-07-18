@extends('backend.layouts.main')

@section('title')
Update Setting| 
@endsection
@section('content')

<!--Content Start-->
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="col-12 pl-0 pr-0">
                <div class="form-group">
                    <div class="col-sm-12">
                        <h4 class="text-center font-weight-bold font-italic mt-3">Satting</h4>
                    </div>
                </div>
                <form action="{{URL::to('setting/update/'.$data->id)}}" method="post" enctype="multipart/form-data" class="form-inline">
                    @csrf
                    <div class="form-group col-md-6 mb-3">
                        <label for="ProductName" class="col-sm-4 col-form-label text-right">Company name</label>
                        <input type="text" name="company_name" class="form-control col-sm-8"  value="{{$data->company_name}}" id="ProductName" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-6 mb-3">
                        <label for="Productcode" class="col-sm-4 col-form-label text-right">Phone Number</label>
                        <input type="text" name="phone" class="form-control col-sm-8" placeholder="Product code" value="{{$data->phone}}" id="Productcode" required>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label for="sellingprice" class="col-sm-4 col-form-label text-right">Email </label>
                        <input type="email" name="email" class="form-control col-sm-8" id="sellingprice" placeholder="Enter Your Email" value="{{$data->email}}"  required>
                        <span class="text-danger"></span>
                    </div>  
                    <div class="form-group col-md-6 mb-3">
                        <label for="sellingprice" class="col-sm-4 col-form-label text-right">Address </label>
                        <input type="text" name="address" class="form-control col-sm-8" id="sellingprice" placeholder="Enter Your Address" value="{{$data->address}}"  required>
                        <span class="text-danger"></span>
                    </div>
                     <div class="form-group col-md-6 mb-3">
                        <label for="servicing_center" class="col-sm-4 col-form-label text-right">Servicing Center </label>
                        <input type="text" name="servicing_center" class="form-control col-sm-8" id="servicing_center" placeholder="Enter Your servicing_center" value="{{$data->servicing_center}}"  required>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        <label for="photo" class="col-sm-4 col-form-label text-right">logo</label>
                        <input type="file" name="logo" class="form-control col-sm-4" id="photo" >
                        <img src="{{URL::to($data->logo)}}" style="height: 40px; width: 70px;">
                        <input type="hidden" name="old_logo" value="{{$data->logo}}">
                    </div>       
                    <div class="form-group col-md-12 ">
                        <label for="product_details" class="col-sm-6 col-form-label text-right"> Emi Conditions</label>
                      <textarea class="form-control" name="emi_details" id="summernote" rows="3" required="">{!!$data->emi_details!!}</textarea>
                      <span class="text-danger"></span>
                  </div> 

                    <div class="form-group col-md-12 mb-3">
                        <button type="submit" class="btn btn-success my-btn-submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Content End-->
<script src="{{asset('public/backend/it-solutionsbd/assets/plugins/jQuery/jquery-3.4.1.min.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function() {
  $('#summernote').summernote();
});
</script>
@endsection