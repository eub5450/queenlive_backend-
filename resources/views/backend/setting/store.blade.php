@extends('backend.layouts.main')
@section('title')
Create New Agency
@endsection
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="body-content">
  <div class="card mb-4">
    <div class="card-body">
      <section class="forms">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h4 class="mb-0">🎨 Effect List</h4>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#addEffectModal"> ➕ Add New Effect</button>
                </div>
               
               
              <div class="table-responsive">
                  <table class="table display table-bordered table-striped table-hover basic">
                    <thead>
                      <tr>
                       <th>Sl</th>
                       <th>Name</th>
                       <th>Price</th>
                       <th>Time</th>
                       <th>Type</th>
                       <th>Status</th>
                       <th>Used</th>
                       <th>Image</th>
                       <th>Actions</th>
                   </tr>
               </thead>
                 <tbody>
                    @php
                    $i=0;
                    @endphp
                    @foreach($data as $row)
                    <tr>
                      <td>{{ ++$i }}</td>
                      <td>{{$row->name}}  </td>
                      <td>{{$row->price}}  </td>
                      <td>{{$row->time}}  </td>
                      <td>@if($row->type==1) Entry Effect @else Frame @endif </td>
                      <td>
                        <span class="badge {{ $row->is_show ? 'badge-success' : 'badge-secondary' }}">
                          {{ $row->is_show ? 'Visible' : 'Hidden' }}
                        </span>
                      </td>
                      <td>{{ $usageCounts[$row->id] ?? 0 }}</td>
                      <td> <img style="width: 73px;" src="{{URL::to($row->image)}}"></td>
                      <td>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal-{{$row->id}}">
                          Edit
                        </button>
                        <form action="{{URL::to('effect_toggle',$row->id)}}" method="post" style="display:inline-block;">
                          @csrf
                          <button type="submit" class="btn btn-sm {{ $row->is_show ? 'btn-warning' : 'btn-success' }}">
                            {{ $row->is_show ? 'Hide' : 'Show' }}
                          </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteStoreModal-{{$row->id}}">
                          Delete
                        </button>
                      </td>
                  </tr>
                  <!-- Modal -->
                      <div class="modal fade" id="exampleModal-{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel">{{$row->name}} Edit</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               <form action="{{URL::to('effect_update',$row->id)}}" method="post">
                                   @csrf
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Name:</label>
                                  <input type="text" name="name" value="{{$row->name}}" class="form-control" id="recipient-name">
                                </div> 
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Time:</label>
                                  <input type="number" name="time" value="{{$row->time}}" class="form-control" id="recipient-name">
                                </div>
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Type:</label>
                                  <select class="form-control" name="type">
                                    <option value="1" @if($row->type==1) selected="" @endif> Entry Effect </option>
                                    <option value="0" @if($row->type==0) selected="" @endif>Frame </option>
                                  </select>
                                </div>
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Is Showing Store:</label>
                                  <select class="form-control" name="is_show">
                                    <option value="1" @if($row->is_show==1) selected="" @endif> Showing </option>
                                    <option value="0" @if($row->is_show==0) selected="" @endif> Private </option>
                                  </select>
                                </div>
                                
                                <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Amount:</label>
                                  <input type="number" name="amount" value="{{$row->price}}" class="form-control" id="recipient-name">
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
                      <div class="modal fade" id="deleteStoreModal-{{$row->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <form action="{{URL::to('effect_delete',$row->id)}}" method="post">
                              @csrf
                              <div class="modal-header">
                                <h5 class="modal-title">Delete {{$row->name}}?</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                              </div>
                              <div class="modal-body">
                                @php $usedCount = $usageCounts[$row->id] ?? 0; @endphp
                                @if($usedCount > 0)
                                  <p class="text-warning mb-0">This effect is used by {{$usedCount}} user inventory row(s). It will be hidden instead of deleted.</p>
                                @else
                                  <p class="text-danger mb-0">This unused effect will be deleted. Continue?</p>
                                @endif
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">{{ $usedCount > 0 ? 'Hide Safely' : 'Delete' }}</button>
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
                  <th>Name</th>
                  <th>Price</th>
                  <th>Time</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Used</th>
                  <th>Image</th>
                  <th>Actions</th>
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
                    <div class="modal fade" id="addEffectModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel">Add New Store File</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ url('effect_store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                             
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"> Effect File Name <span style=" color: red; ">(ex: filename.svga)</span> </label>
                                        <input type="text" name="effect" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Time (Day)</label>
                                        <input type="number" name="time" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" name="price" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Type</label>
                                  <select class="form-control" name="type">
                                    <option value="1">Entry Effect</option>
                                            <option value="0">Frame</option>
                                  </select>
                                </div> <div class="form-group">
                                  <label for="recipient-name" class="col-form-label">Is Showing Store:</label>
                                  <select class="form-control" name="is_show">
                                    <option value="1"> Showing </option>
                                    <option value="0"> Private </option>
                                  </select>
                                </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" class="form-control" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">✅ Add Effect</button>
                                </div>
                            </form>
                          </div>
                        </div>
                      </div>
                      </div>
@endsection
