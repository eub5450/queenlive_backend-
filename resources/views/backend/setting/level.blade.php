@extends('backend.layouts.main')

@section('title')
Level Setting
@endsection

@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div>
                    <h4 class="mb-1">Level Setting</h4>
                    <small class="text-muted">Manage the level amount ladder used by app level progress.</small>
                </div>
                <span class="badge badge-info mt-2 mt-md-0">Live Lavel Table</span>
            </div>

            <div class="alert alert-info">
                <strong>Source:</strong> this page updates the existing <code>lavels</code> table.
                <code>update_lavel</code> is the level number to unlock, and <code>amount</code> is the total sent gift amount required for that level.
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <strong>Add New Level</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.level_setting.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Level Number</label>
                                <input type="number" name="update_lavel" min="1" max="1000" class="form-control" value="{{ old('update_lavel', $nextLevel) }}" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label>Required Amount</label>
                                <input type="number" name="amount" min="0" max="2147483647" class="form-control" value="{{ old('amount') }}" placeholder="50000" required>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success btn-block">Add Level</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th style="width: 180px;">Level</th>
                            <th>Required Amount</th>
                            <th style="width: 260px;">Updated</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($levels as $level)
                            <tr>
                                <td>{{ $level->id }}</td>
                                <td>
                                    <form id="level-update-{{ $level->id }}" action="{{ route('admin.level_setting.update', $level->id) }}" method="POST" class="mb-0">
                                        @csrf
                                        <input type="number" name="update_lavel" min="1" max="1000" class="form-control" value="{{ old('update_lavel_' . $level->id, $level->update_lavel) }}" required>
                                    </form>
                                </td>
                                <td>
                                    <input form="level-update-{{ $level->id }}" type="number" name="amount" min="0" max="2147483647" class="form-control" value="{{ old('amount_' . $level->id, $level->amount) }}" required>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Created: {{ $level->created_at ?: 'N/A' }}<br>
                                        Updated: {{ $level->updated_at ?: 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button form="level-update-{{ $level->id }}" type="submit" class="btn btn-sm btn-primary mr-2">Save</button>
                                        <form action="{{ route('admin.level_setting.destroy', $level->id) }}" method="POST" class="mb-0" onsubmit="return confirm('Remove this level?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No levels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
