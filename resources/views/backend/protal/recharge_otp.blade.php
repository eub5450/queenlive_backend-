@extends('backend.layouts.main')
@section('title')
Create New Agency
@endsection
@section('content')
<!-- Your existing content -->

<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-group">
                <div class="col-sm-12">
                    <h4 class="text-center font-weight-bold font-italic mt-3">New Protal</h4>
                </div>
            </div>

            <!-- OTP input field -->
            <form method="post" action="{{ route('checkOTP') }}">
                @csrf
                <div class="form-group">
                    <label for="otpInput" class="font-weight-bold">Enter OTP:</label>
                    <input type="text" class="form-control" id="otpInput" name="otpInput" placeholder="Enter OTP">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<!-- Your existing content -->
@endsection
