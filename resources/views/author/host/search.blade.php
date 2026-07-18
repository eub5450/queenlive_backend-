@extends('author.layouts.main')
@section('content')
<div class="body-content container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">Search Profile</h4>
            <form action="{{ route('author.profile.search') }}" method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="id" class="mr-2">User ID</label>
                    <input type="number" name="id" id="id" class="form-control" placeholder="Enter country user ID" required>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Search Profile</button>
            </form>
            <div class="small text-muted mt-3">Only users from this country can be opened.</div>
        </div>
    </div>
</div>
@endsection
