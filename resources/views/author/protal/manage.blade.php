@extends('author.layouts.main')
@section('content')
<!--/.Content Header (Page header)--> 
<div class="body-content container-fluid flex-grow-1 container-p-y">
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
                     </tr>
                   </thead>
                   <tbody>
                    @php
                    $i=0;
                    @endphp
                    @foreach($data as $user)
                    @php
                    $total_recived=App\Models\PortalRecharge::where('user_id',$user->id)->where('status','Approved')->sum('amount');
                    $total_sand=App\Models\PortalTransfer::where('portal_user_id',$user->id)->sum('amount');
                     $total_recall=App\Models\PortalRecall::where('protal_id',$user->id)->sum('amount');
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
                      <td>  {{$total_recived-($total_sand+$total_recall)}}  </td>             
                    </tr>
               
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
                     <th>Total Recall</th>
                     <th>Balance</>
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