@extends('author.layouts.main')
@section('content')
<!--/.Content Header (Page header)--> 
<div class="body-content container-fluid flex-grow-1 container-p-y">
	<div class="row">
                            <div class="col-md">
                                <div class="card text-center mb-3">
                                    <div class="card-header">
                                        <ul class="nav nav-tabs card-header-tabs nav-responsive-md">
                                            <li class="nav-item">
                                                <a class="nav-link active show" data-toggle="tab" href="#navs-wc-home">Active</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#navs-wc-recharge">Recharge</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#trnsfer">Transfer</a>
                                            </li>
                                            
                                        </ul>
                                    </div>
                                    <div class="tab-content">
                                        <div class="tab-pane fade active show" id="navs-wc-home">
                                            <div class="card-body">
                                                <h4 class="card-title">Master Protal Balance</h4>
                                                <p class="card-text" style=" font-size: 24px; font-weight: 900; ">{{$protal_recharge-$protal_transfer}}</p>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
														  Transfer
														</button>
                                                
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-wc-recharge">
                                            <div class="card-body">
                                                <h4 class="card-title">Recharge History</h4>
                                                 <div class="table-responsive">
									                 <div class="table-responsive">
									                 <table class="table display table-bordered table-striped table-hover basic">
									                    <thead>
									                      <tr>
									                       <th>Sl</th>
									                       <th>ID</th>
									                       <th>Date</th>
									                       <th>Approved By</th>
									                       <th>Amount</th>
									                       
									                   </tr>
									               </thead>
									                 <tbody>
									                    @php
									                    $i=0;
									                    $total_potal_history=0;
									                    @endphp
									                    @foreach($protal_recharge_details as $protal_recharge_detail)
									                    @php
									                    $approved_by=App\Models\User::find($protal_recharge_detail->recharge_by);
									                    @endphp
									                    <tr>
									                      <td>{{ ++$i }}</td>
									                      <td>{{$protal_recharge_detail->trxid}}  </td>
									              
									                      <td>{{$protal_recharge_detail->date}}  </td>
									                      <td>@if($approved_by){{$approved_by->name}} @else  @endif </td>
									                      <td>{{$protal_recharge_detail->amount}}  </td>
									                      
									                      
									                  </tr>
									                  @php
									                  $total_potal_history+=$protal_recharge_detail->amount;
									                  @endphp
									                  @endforeach
									              </tbody>
									              <tfoot>
									                <tr>
									                  <th>Sl</th>
									                  <th>ID</th>
									                  <th>Date</th>
									                  <th>Approved By</th>
									                  <th>{{$total_potal_history}}</th>
									                  
									               </tr>
									           </tfoot>
									       </table>
									            </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade " id="trnsfer">
                                            <div class="card-body">
                                                <h4 class="card-title">Transfer Details</h4>
                                                	<table class="table display table-bordered table-striped table-hover basic">
									                    <thead>
									                      <tr>
									                       <th>Sl</th>
									                       <th>ID</th>
									                       <th>Date</th>
									                       <th>Recived By</th>
									                       <th>Amount</th>
									                       
									                   </tr>
									               </thead>
									                 <tbody>
									                    @php
									                    $i=0;
									                    $transer=0;
									                    @endphp
									                    @foreach($protal_transfer_details as $protal_transfer_detail)
									                    
									                    <tr>
									                      <td>{{ ++$i }}</td>
									                      <td>{{$protal_transfer_detail->trxid}}  </td>
									              
									                      <td>{{$protal_transfer_detail->date}}  </td>
									                      <td>{{$protal_transfer_detail->user_id}}  </td>
									                      
									                      <td>{{$protal_transfer_detail->amount}}  </td>
									                      
									                      
									                  </tr>
									                  @php
									                  $transer+=$protal_transfer_detail->amount;
									                  @endphp
									                  @endforeach
									              </tbody>
									              <tfoot>
									                <tr>
									                  <th>Sl</th>
									                  <th>ID</th>
									                  <th>Date</th>
									                  <th>Recived By</th>
									                  <th>{{$transer}}</th>
									                  
									               </tr>
									           </tfoot>
									       </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal -->
						<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header">
						        <h5 class="modal-title" id="exampleModalLabel">Transfer In Protal</h5>
						        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
						          <span aria-hidden="true">&times;</span>
						        </button>
						      </div>
						      <form action="{{route('country.author.protal-transfer.store')}}" method="post">
						      	@csrf
						     
						      <div class="modal-body">
						        <div class="card mb-4">
						       
						                <div class="form-group">
						                    <div class="col-sm-12">
						                        <h4 class="text-center font-weight-bold font-italic mt-3">New Protal Recharge</h4>
						                    </div>
						                </div>
						                                    
						                    <div class="form-group col-md-12 mb-3">
						                        <label for="member" class="col-sm-4 col-form-label text-right">Protal Id</label>
						                       <select name="protal_id" class="form-control select_agency_id select2-hidden-accessible" required="" >
						                       	@foreach($protal_users as $protal_user)
						                        <option value="{{$protal_user->id}}">{{$protal_user->id}} -- {{$protal_user->name}}</option>
						                        @endforeach
						                      </select>
						                        <span class="text-danger"></span>
						                    </div>

						                    <div class="form-group col-md-12 mb-3">
						                        <label for="name" class="col-sm-4 col-form-label text-right">Deposit Amount</label>
						                        <input type="number" name="deposit" class="form-control col-sm-8" placeholder="Deposit  Amount" value="0" id="deposit" required="">
						                        <span class="text-danger"></span>
						                    </div>
						        </div>
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						        <button type="submit" class="btn btn-primary">Submit</button>
						      </div>

						      </form>
						    </div>
						  </div>
						</div>
                        </div>
                        </div>
</div>
@endsection