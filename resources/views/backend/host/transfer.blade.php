@extends('backend.layouts.main')


@section('title')
Supplier  
@endsection
@section('content')


<div class="body-content">
	<form action="{{URL::to('/host-agency-transfer')}}" method="get">
		@csrf
	
	<div class="row">

		<div class="col-xl-6 col-sm-6 py-2">
			<div class="card ">
				<div class="card-body">
					<div class="form-group">
						<label for="member" class="col-sm-4 col-form-label text-right">Host ID</label>
						<select name="host_id" class="form-control host_id  select_host_id" id="host_id" required="">
							@foreach($users as $user)
							<option value="{{$user->id}}">{{$user->id}} - {{$user->name}}</option>
							@endforeach
							
						</select>
						<span class="text-danger"></span>
					</div>
					
					<div class="form-group">
						<label for="agency_name" class="col-sm-4 col-form-label text-right">Agency Name</label>
						<input type="text" name="agency_name" readonly=""  class="form-control agency_name" placeholder="Agency Name" value="" id="agency_name" required>
						<span class="text-danger"></span>
					</div>

					<div class="form-group">
						<label for="agencycode" class="col-sm-4 col-form-label text-right">Agency Code</label>
						<input type="text" name="agency_code" readonly="" class="form-control agency_code"  placeholder="Agency Code"  id="agency_code" required="">
						<span class="text-danger"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-6 col-sm-6 py-2">
			<div class="card ">
				<div class="card-body">
					<div class="form-group">
						<label for="member" class="col-sm-4 col-form-label text-right">Transfer Agency</label>
						<select name="agency_id" class="form-control agency_id  select_agency_id" id="agency_id" required="">
							@foreach($agencys as $agency)
							<option value="{{$agency->id}}">{{$agency->code}} - {{$agency->name}}</option>
							@endforeach
						</select>
						<span class="text-danger"></span>
					</div>
					<button type="submit" class="btn btn-primary"> Transfer </button>
				</div>
			</div>
		</div>
	</div>
	</form>
</div>
@endsection
@section('script')
<script>
  $(document).ready(function() {
    $(document).on('keyup change','#host_id', function() {
      var id= $(this).val();
      
        $.ajax({
          url: "{{ URL::to('get/host_agency_info') }}/" + id,
          type: "GET",
          dataType:"json",
          success: function(data) {
            $('#agency_code').val(data.data.code);
            $('#agency_name').val(data.data.name);


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

      

    });

  });



</script>
@endsection