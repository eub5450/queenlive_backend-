@extends('backend.layouts.main')


@section('title')
Employee  | 
@endsection
@section('content')

<!--Content Start-->
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="col-12 pl-0 pr-0">
                <div class="form-group">
                    <div class="col-sm-12">
                        <h4 class="text-center font-weight-bold font-italic mt-3">Greedy Pattan List  <button type="button" class="btn btn-success my-btn-submit" data-toggle="modal" data-target="#brandadd">Add Pattan</button></h4>
                        <!-- Button trigger modal -->
                        <!-- Modal -->
                        <div class="modal fade" id="brandadd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">New Pattan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                          <div class="modal-body">
                            <section class="container-fluid">
                                <div class="row content">
                                    <div class="col-12 pl-0 pr-0">
                                     
                                        <form action="{{URL::to('admin/greedy-game-pattarn-store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                                            @csrf

                                            <div class="form-group col-md-12 mb-3">
                                                <label for="amount" class="col-sm-4 col-form-label text-right">Name</label>
                                                <select class="form-control" required="" name="pots">
                                                    <option value="apple">Apple (X5)</option>
                                                    <option value="grapes">Cabbage (X5)</option>
                                                    <option value="banana">Corn (X5)</option>
                                                    <option value="lemon">Carrot (X5)</option>
                                                    <option value="horse">HotDog (X15)</option>
                                                    <option value="tiger">Meat (X25)</option>
                                                    <option value="cat">Kabab (X10)</option>
                                                    <option value="lion">Steak (X45)</option>
                                                    <option value="animals">Pizza (X All Big)</option>
                                                    <option value="vegetable">Salad (X All Small)</option>
                                                </select>
                                                <span class="text-danger"></span>
                                            </div>
                                            
                                           <span class="text-danger"></span>
                                            </div>
                                            <div class="form-group col-md-12 mb-3">
                                                <button type="submit" class="btn btn-success my-btn-submit">Save </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </section>
                            <!--Content End-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="table-responsive">
                <table class="table display table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sl.</th>
                    <th>Pots</th>
                    <th style="width: 100px;">Action</th>
                </tr>
            </thead>
            
            <tbody>
                @php $i=0; @endphp
                @foreach($data as $row)
                <tr>
                    <td>{{++$i}}</td>
                    <td>
                          @if($row->pots == "grapes")
				                  <img src="{{asset('public/game/grady/')}}/image/cabbage_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "banana")
				                  <img src="{{asset('public/game/grady/')}}/image/corn_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "apple")
				                  <img src="{{asset('public/game/grady/')}}/image/tomoto_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "lemon")
				                  <img src="{{asset('public/game/grady/')}}/image/carrot_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "lion")
				                  <img src="{{asset('public/game/grady/')}}/image/steak_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "cat")
				                  <img src="{{asset('public/game/grady/')}}/image/kabab_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "tiger")
				                  <img src="{{asset('public/game/grady/')}}/image/meat_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "horse")
				                  <img src="{{asset('public/game/grady/')}}/image/hotdog_win.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "vegetable")
				                  <img src="{{asset('public/game/grady/')}}/image/salad.png" class="trendcoing" style=" width: 48px; ">;
				               @elseif($row->pots == "animals")
				                  <img src="{{asset('public/game/grady/')}}/image/pizza.png" class="trendcoing" style=" width: 48px; ">;
				               @else
				               @endif
                    
                    </td>
                    <td>
                        
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal-{{$row->id}}">
                          Edit
                        </button>
                        <!--<a href="{{URL::to('admin/fruts-game-pattarn-delete/'.$row->id)}}" class="btn btn-sm btn-danger" id="delete"><span class="fa fa-trash"></span></a>-->
   

                        
            </td>
        </tr>
         <!-- Modal -->
        <div class="modal fade" id="exampleModal-{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Patten Change</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              
              <form action="{{URL::to('admin/greedy-game-pattarn-update',$row->id)}}" enctype="multipart/form-data" method="post">
                         @csrf
                   
                      
                      <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Name:</label>
                       <select class="form-control" required="" name="pots">
                                                    <option value="apple">Apple (X5)</option>
                                                    <option value="grapes">Cabbage (X5)</option>
                                                    <option value="banana">Corn (X5)</option>
                                                    <option value="lemon">Carrot (X5)</option>
                                                    <option value="horse">HotDog (X15)</option>
                                                    <option value="tiger">Meat (X25)</option>
                                                    <option value="cat">Kabab (X10)</option>
                                                    <option value="lion">Steak (X45)</option>
                                                    <option value="animals">Pizza (X All Big)</option>
                                                    <option value="vegetable">Salad (X All Small)</option>
                                                </select>
                      </div>
                     
                    
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                  </form>
            </div>
          </div>
        </div>
        @endforeach
    </tbody>
</table>
</div>
</div>
</div>
</div>


<!--Content End-->

@endsection