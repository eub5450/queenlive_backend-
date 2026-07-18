@extends('author.layouts.main')
@section('content')
<div class="body-content container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Add Banner</h4>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#bannerModal">New Banner Add</button>
            </div>

            <div class="modal fade" id="bannerModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('country.author.banner-store') }}" enctype="multipart/form-data" method="post">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Banner</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-0">
                                    <label>Banner Image</label>
                                    <input type="file" name="image" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Banner</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover basic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Banner</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sliders as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><img src="{{ URL::to($item->image) }}" style="max-width:320px;" alt="Banner {{ $index + 1 }}"></td>
                                <td><a href="{{ route('country.author.banner-remove', ['id' => $item->id]) }}" class="btn btn-danger btn-sm">Remove</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No banner found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
