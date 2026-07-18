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
                        <h4 class="text-center font-weight-bold font-italic mt-3">Pattan List  <button type="button" class="btn btn-success my-btn-submit" data-toggle="modal" data-target="#brandadd">Add Pattan</button></h4>
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
                                     
                                        <form action="{{URL::to('admin/fruts-game-pattarn-store')}}" method="post" enctype="multipart/form-data" class="form-inline">
                                            @csrf

                                            <div class="form-group col-md-12 mb-3">
                                                <label for="amount" class="col-sm-4 col-form-label text-right">Name</label>
                                                <select class="form-control" required="" name="pots">
                                                    <option value="apple">Apple</option>
                                                    <option value="saven_win">Lamon</option>
                                                    <option value="watermelon">Watermellon</option>
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
                    <td>@if($row->pots=='saven_win') <img src="{{asset('public/game/new/image')}}/lemon.png" style=" width: 48px; " alt="Saven Winner"> @elseif($row->pots=='watermelon')  <img  style=" width: 48px; "src="{{asset('public/game/new/image')}}/watermelon.png" alt="Saven Winner"> @else <img  style=" width: 48px; "src="{{asset('public/game/new/image')}}/apple.png" alt="Saven Winner"> @endif</td>
                    <td>
                        
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal-{{$row->id}}">
                          Edit
                        </button>
                        <a href="{{URL::to('admin/fruts-game-pattarn-delete/'.$row->id)}}" class="btn btn-sm btn-danger" id="delete"><span class="fa fa-trash"></span></a>
   

                        
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
              
              <form action="{{URL::to('admin/fruts-game-pattarn-update',$row->id)}}" enctype="multipart/form-data" method="post">
                         @csrf
                   
                      
                      <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Name:</label>
                       <select class="form-control" required="" name="pots">
                                                    <option @if($row->pots=='apple') selected="" @endif value="apple">Apple</option>
                                                    <option @if($row->pots=='saven_win') selected="" @endif value="saven_win">Lamon</option>
                                                    <option @if($row->pots=='watermelon') selected="" @endif value="watermelon">Watermellon</option>
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