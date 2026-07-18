@extends('subadmin.layouts.main')

@section('content')
<div class="body-content">
	
	<div class="row">

		<div class="col-xl-12 col-sm-12 py-2">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="fs-17 font-weight-600 mb-0">Ranking History</h6>
						</div>
						
					</div>
				</div>
				<div class="card-body">
					
					<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
						
						<li class="nav-item">
							<a class="nav-link active show" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="true">Sanding </a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="pills-contact-tab" data-toggle="pill" href="#pills-contact" role="tab" aria-controls="pills-contact" aria-selected="false">Reciver</a>
						</li>
							<li class="nav-item">
							<a class="nav-link" id="pills-family-tab" data-toggle="pill" href="#pills-family" role="tab" aria-controls="pills-family" aria-selected="false">Family</a>
						</li>
						
					</ul>
					<div class="tab-content" id="pills-tabContent">
						
						<div class="tab-pane fade active show" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
						 <table class="table display table-bordered table-striped table-hover ">
			                    <thead>
			                      <tr>
			                       <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                       <th>Amount</th>
			                     
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    $totalSand_total=0;
			                    @endphp
			                    @foreach($totalSands as $totalSand)
			                    
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$totalSand->id}}  </td>
			              
			                      <td>{{$totalSand->name}}  </td>
			               
			                      <td>{{$totalSand->total_sand}}  </td>
			                  </tr>
			                  @php
			                  $totalSand_total+=$totalSand->total_sand;
			                  @endphp
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                  <th>Sl</th>
			                  <th>ID</th>
			                  <th>Name</th>
			                  <th>{{$totalSand_total}}</th>
			                
			               </tr>
			           </tfoot>
			       </table>
						</div>
						<div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
							<table class="table display table-bordered table-striped table-hover ">
			                    <thead>
			                      <tr>
			                        <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                       <th>Amount</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    $totalRecived_total=0;
			                    @endphp
			                    @foreach($totalReciveds as $totalRecived)
			                    
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$totalRecived->id}}  </td>
			              
			                      <td>{{$totalRecived->name}}  </td>
			                      <td>{{$totalRecived->total_sand}}  </td>
			                      
			                  </tr>
			                  @php
			                  $totalRecived_total+=$totalRecived->total_sand;
			                  @endphp
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                  <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                    
			                  <th>{{$totalRecived_total}}</th>
			              
			               </tr>
			           </tfoot>
			       </table>
						</div>
							<div class="tab-pane fade" id="pills-family" role="tabpanel" aria-labelledby="pills-family-tab">
							<table class="table display table-bordered table-striped table-hover ">
			                    <thead>
			                      <tr>
			                        <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                       <th>Amount</th>
			                   </tr>
			               </thead>
			                 <tbody>
			                    @php
			                    $i=0;
			                    $totalfamillyRecived_total=0;
			                    @endphp
			                    @foreach($totalfamillyReciveds as $totalfamillyRecived)
			                    
			                    <tr>
			                      <td>{{ ++$i }}</td>
			                      <td>{{$totalfamillyRecived->code}}  </td>
			              
			                      <td>{{$totalfamillyRecived->name}}  </td>
			                      <td>{{$totalfamillyRecived->total_sand}}  </td>
			                      
			                  </tr>
			                  @php
			                  $totalfamillyRecived_total+=$totalfamillyRecived->total_sand;
			                  @endphp
			                  @endforeach
			              </tbody>
			              <tfoot>
			                <tr>
			                  <th>Sl</th>
			                       <th>ID</th>
			                       <th>Name</th>
			                   
			                  <th>{{$totalfamillyRecived_total}}</th>
			              
			               </tr>
			           </tfoot>
			       </table>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
@endsection