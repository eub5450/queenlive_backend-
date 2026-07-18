<!-- [ Layout navbar ( Header ) ] Start -->

<nav class="layout-navbar navbar navbar-expand-lg align-items-lg-center bg-dark container-p-x" id="layout-navbar">

	<!-- Brand demo (see {{asset('public/author/assets/css/demo/demo.css')}}) -->
	<a href="index.html" class="navbar-brand app-brand demo d-lg-none py-0 mr-4">
		<span class="app-brand-logo demo">
			<img src="{{asset('public/author/assets/img/logo-dark.png')}}" alt="Brand Logo" class="img-fluid">
		</span>
		<span class="app-brand-text demo font-weight-normal ml-2">Empire</span>
	</a>

	<!-- Sidenav toggle (see {{asset('public/author/assets/css/demo/demo.css')}}) -->
	<div class="layout-sidenav-toggle navbar-nav d-lg-none align-items-lg-center mr-auto">
		<a class="nav-item nav-link px-0 mr-lg-4" href="javascript:">
			<i class="ion ion-md-menu text-large align-middle"></i>
		</a>
	</div>

	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#layout-navbar-collapse">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="navbar-collapse collapse" id="layout-navbar-collapse">
		<!-- Divider -->
		<hr class="d-lg-none w-100 my-2">

		<div class="navbar-nav align-items-lg-center">
			<!-- Search -->
			<form action="{{route('author.profile.search')}}" method="get">
			@csrf
			<label class="nav-item navbar-text navbar-search-box p-0 active">
				<i class="feather icon-search navbar-icon align-middle"></i>
				<span class="navbar-search-input pl-2">
					<input type="number" name="id"  class="form-control navbar-text mx-2" placeholder="Search...">
				</span>
			</label>
		 </form>
		</div>

		<div class="navbar-nav align-items-lg-center ml-auto">
			<div class="demo-navbar-notifications nav-item dropdown mr-lg-3">
				<a class="nav-link dropdown-toggle hide-arrow" href="#" data-toggle="dropdown">
					<i class="feather icon-bell navbar-icon align-middle"></i>
					<span class="badge badge-danger badge-dot indicator"></span>
					<span class="d-lg-none align-middle">&nbsp; Event</span>
				</a>
				<div class="dropdown-menu dropdown-menu-right">
					<div class="bg-primary text-center text-white font-weight-bold p-3">
						 New Event
					</div>
					<div class="list-group list-group-flush">
						
						<a href="javascript:" class="list-group-item list-group-item-action media d-flex align-items-center">
							<div class="ui-icon ui-icon-sm feather icon-home bg-secondary border-0 text-white"></div>
							<div class="media-body line-height-condenced ml-3">
								<div class="text-dark">Login from 192.168.1.1</div>
								<div class="text-light small mt-1">
									gg
								</div>
								<div class="text-light small mt-1">dddddd</div>
							</div>
						</a>

						
						</div>
						<a href="#" class="d-block text-center text-light small p-2 my-1">Show all notifications</a>
					</div>
				</div>

				<!-- Divider -->
				<div class="nav-item d-none d-lg-block text-big font-weight-light line-height-1 opacity-25 mr-3 ml-1">|</div>
				<div class="demo-navbar-user nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
						<span class="d-inline-flex flex-lg-row-reverse align-items-center align-middle">
							<img src="#" alt class="d-block ui-w-30 rounded-circle">
							<span class="px-1 mr-lg-2 ml-2 ml-lg-0">{{Auth::user()->name}}</span>
						</span>
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a href="javascript:" class="dropdown-item">
							<i class="feather icon-user text-muted"></i> &nbsp; My profile</a>
							
									<div class="dropdown-divider"></div>
									<a  href="{{ route('logout') }} " onclick="event.preventDefault();
									document.getElementById('logout-form').submit();" class="dropdown-item">
									<i class="feather icon-power text-danger"></i> &nbsp; Log Out</a>
									<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
										@csrf
									</form>
								</div>
							</div>
						</div>
					</div>
				</nav>
				<!-- [ Layout navbar ( Header ) ] End -->
