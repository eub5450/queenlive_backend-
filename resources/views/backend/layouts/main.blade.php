<!doctype html>
<html lang="en">


<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="QueenLive Admin Dashboard">
    <meta name="author" content="QueenLive">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>QueenLive Admin</title>
    <!-- Bootstrap -->

    @include('backend.layouts.style')
</head>
<body class="fixed">
    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-green">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Please wait...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <div class="wrapper">
        @include('backend.layouts.sidebar')
        <!-- Page Content  -->
        <div class="content-wrapper">
            <div class="main-content">
               @include('backend.layouts.topbar')
               <!--Content Header (Page header)-->
               @if ($errors->any())
               <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @yield('content')
            
            <!--/.body content-->
        </div><!--/.main content-->
        
    </body>

    @include('backend.layouts.footer')
    </html>