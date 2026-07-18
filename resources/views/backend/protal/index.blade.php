@extends('backend.layouts.main')


@section('title')
Agency List
@endsection
@section('content')
<!--Content Start-->
<div class="body-content">
  <div class="card mb-4">
    <div class="card-body">
      <section class="forms">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header d-flex align-items-center">
                  <h4> Agency List</h4>
                </div>
                <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                    <thead>
                      <tr>
                       <th>Sl</th>
                       <th>ID</th>
                       <th>Profile</th>
                       <th>Name</th>
                       <th>Level</th>
                       <th>Total Recived</th>
                       <th>Total Sand</th>
                         <th>Total Recall</th>
                       <th>Balance</th>
                       <th>Action</th>
                     </tr>
                   </thead>
                   <tbody>
                    @php
                    $i=0;
                    @endphp
                    @foreach($users as $user)
                    @php
                    $total_recived=App\Models\PortalRecharge::where('user_id',$user->id)->where('status','Approved')->sum('amount');
                    $total_sand=App\Models\PortalTransfer::where('portal_user_id',$user->id)->sum('amount');
                     $total_recall=App\Models\PortalRecall::where('protal_id',$user->id)->sum('amount');
                    $ProtalToPTransfer=App\Models\ProtalToPTransfer::where('user_id',$user->id)->sum('amount');
                    $ProtalToPTransferRecived=App\Models\ProtalToPTransfer::where('portal_user_id',$user->id)->sum('amount');
                    @endphp
                    <tr>
                      <td>{{ ++$i }}</td>
                      <td>  {{$user->id}}  </td>
                      <td> <img style="width: 73px;" src="{{URL::to($user->profile)}}"></td>
                      <td>  {{$user->name}}  </td>
                      <td>  {{$user->level}}  </td>
                      <td>  {{$total_recived}}  </td>
                      <td>  {{$total_sand}}  </td>
                       <td>  {{$total_recall}}  </td>
                       <td>  {{($total_recived+$ProtalToPTransferRecived)-($total_sand+$total_recall+$ProtalToPTransfer)}}  </td>
                      
                      <td>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal-{{$user->id}}">
                          Recall
                        </button>
                      </td>
                      
                    </tr>
                       <!-- Modal -->
                      <div class="modal fade" id="exampleModal-{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel">{{$user->name}} Recall</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               <form action="{{URL::to('protal_recall')}}" method="post">
                                   @csrf
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Amount:</label>
                                  <input type="number" name="amount" value="{{$total_recived-($total_sand+$total_recall)}}" class="form-control" id="recipient-name">
                                  <input type="hidden" name="user_id" value="{{$user->id}}" class="form-control" id="recipient-name">
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
                  <tfoot>
                    <tr>
                     <th>Sl</th>
                     <th>ID</th>
                     <th>Profile</th>
                     <th>Name</th>
                     <th>Level</th>
                     <th>Total Recived</th>
                     <th>Total Sand</th>
                     <th>Balance</th>
                     <th>Action</th>
                   </tr>
                 </tfoot>
               </table>
             </div>
           </div>
         </div>
       </div>
     </div>
   </section>
 </div>
</div>
</div>
  @endsection