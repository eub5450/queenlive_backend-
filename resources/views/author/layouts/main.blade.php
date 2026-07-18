<!DOCTYPE html>

<html lang="en" class="default-style layout-fixed layout-navbar-fixed">

@include('author.layouts.header')

<body>
	<!-- [ Preloader ] Start -->
	<div class="page-loader">
		<div class="bg-primary"></div>
	</div>
	<!-- [ Preloader ] End -->
@php
$contry=App\Models\Country::find(Auth::user()->country_id);
@endphp
	<!-- [ Layout wrapper ] Start -->
	<div class="layout-wrapper layout-2">
		<div class="layout-inner">
			<!-- [ Layout sidenav ] Start -->
			<div id="layout-sidenav" class="layout-sidenav sidenav sidenav-vertical bg-white logo-dark">
				
				<div class="app-brand demo">
					<span class="app-brand-logo demo">
						<img src="{{URL::to($contry->flag)}}" alt="Brand Logo" class="img-fluid">
					</span>
					<a href="#" class="app-brand-text demo sidenav-text font-weight-normal ml-2">{{$contry->name}}</a>
					<a href="javascript:" class="layout-sidenav-toggle sidenav-link text-large ml-auto">
						<i class="ion ion-md-menu align-middle"></i>
					</a>
				</div>
				<div class="sidenav-divider mt-0"></div>

				<!-- Links -->
				@include('author.layouts.sidebar')
			</div>
			<!-- [ Layout sidenav ] End -->
			<!-- [ Layout container ] Start -->
			<div class="layout-container">
				@include('author.layouts.topbar')
				<!-- [ Layout content ] Start -->
				<div class="layout-content">

					<!-- [ content ] Start -->
					@yield('content')
					<!-- [ content ] End -->

					@include('author.layouts.footer')
				</body>
				</html>
