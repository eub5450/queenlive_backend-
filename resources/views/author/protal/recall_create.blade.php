@extends('author.layouts.main')
@section('content')
<div class="body-content container-fluid flex-grow-1 container-p-y">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">Location ID Recall</h4>
            <form action="{{ route('country.author.protal-recall-store') }}" method="post" class="form-inline">
                @csrf
                <div class="form-group col-md-6 mb-3">
                    <label class="col-sm-4 col-form-label text-right">User ID</label>
                    <select name="user_id" class="form-control col-sm-8" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->id }} -- {{ $user->name }} -- {{ number_format($user->balance) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label class="col-sm-4 col-form-label text-right">Recall Amount</label>
                    <input type="number" name="amount" class="form-control col-sm-8" min="1" value="0" required>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label class="col-sm-4 col-form-label text-right">Portal ID</label>
                    <select name="protal_id" class="form-control col-sm-8" required>
                        @foreach($protals as $portal)
                            <option value="{{ $portal->id }}">{{ $portal->id }} -- {{ $portal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-12 mb-3">
                    <button type="submit" class="btn btn-success">Submit Recall</button>
                    <a href="{{ route('country.author.protal-recall-list') }}" class="btn btn-outline-primary ml-2">Recall History</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
