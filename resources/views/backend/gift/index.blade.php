@extends('backend.layouts.main')

@section('title')
Create New Agency
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger gift-alert">
        <strong>Error!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    :root {
        --gift-bg: #0b0f17;
        --gift-card: #151b26;
        --gift-card-2: #1d2633;
        --gift-border: #2c3545;
        --gift-border-hover: #4f9eff;
        --gift-text: #e8edf7;
        --gift-muted: #9aa7bd;
        --gift-primary: #4f9eff;
        --gift-success: #22c55e;
        --gift-danger: #ef4444;
        --gift-warning: #f59e0b;
        --gift-input: #101722;
    }

    .body-content {
        background: var(--gift-bg);
        padding: 22px;
        color: var(--gift-text);
    }

    .gift-page-card {
        background: linear-gradient(145deg, #171f2d 0%, #111722 100%);
        border: 1px solid var(--gift-border);
        border-radius: 22px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.38);
        overflow: visible;
        margin-bottom: 28px;
    }

    .gift-card-body {
        padding: 28px;
    }

    .gift-title {
        color: var(--gift-text);
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: .3px;
        text-align: center;
        margin: 0 0 26px;
    }

    .gift-title span {
        color: var(--gift-primary);
    }

    .gift-form-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -12px;
    }

    .gift-form-group {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 12px;
        margin-bottom: 18px;
    }

    .gift-form-group.full {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .gift-label {
        display: block;
        color: var(--gift-muted);
        font-weight: 700;
        font-size: .94rem;
        margin-bottom: 8px;
    }

    .gift-input,
    .gift-select,
    .gift-file {
        width: 100%;
        height: 48px;
        border-radius: 13px;
        border: 2px solid var(--gift-border);
        background: var(--gift-input);
        color: var(--gift-text);
        padding: 10px 14px;
        outline: none;
        transition: border-color .22s ease, box-shadow .22s ease;
    }

    .gift-file {
        padding: 9px 12px;
    }

    .gift-input:focus,
    .gift-select:focus,
    .gift-file:focus {
        border-color: var(--gift-primary);
        box-shadow: 0 0 0 4px rgba(79, 158, 255, .16);
        background: var(--gift-input);
        color: var(--gift-text);
    }

    .gift-input::placeholder {
        color: #697589;
    }

    .gift-submit-wrap {
        display: flex;
        justify-content: center;
        margin-top: 8px;
    }

    .gift-btn-submit {
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: #fff;
        padding: 13px 34px;
        font-weight: 800;
        box-shadow: 0 12px 25px rgba(34, 197, 94, .22);
        transition: transform .22s ease, box-shadow .22s ease;
    }

    .gift-btn-submit:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(34, 197, 94, .32);
    }

    .gift-list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 26px;
        border-bottom: 1px solid var(--gift-border);
        background: linear-gradient(90deg, #1d2633 0%, #141b27 100%);
        border-radius: 22px 22px 0 0;
    }

    .gift-list-header h4 {
        margin: 0;
        color: var(--gift-text);
        font-weight: 800;
    }

    .gift-count {
        background: rgba(79, 158, 255, .14);
        color: var(--gift-primary);
        border: 1px solid rgba(79, 158, 255, .3);
        padding: 7px 14px;
        border-radius: 999px;
        font-weight: 800;
        font-size: .88rem;
    }

    .gift-table-wrap {
        padding: 22px;
    }

    .gift-table {
        width: 100%;
        color: var(--gift-text);
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .gift-table thead th,
    .gift-table tfoot th {
        background: #101722;
        color: var(--gift-muted);
        border-color: var(--gift-border) !important;
        white-space: nowrap;
        font-weight: 800;
        font-size: .88rem;
    }

    .gift-table tbody td {
        background: #151d2a;
        color: var(--gift-text);
        border-color: var(--gift-border) !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    .gift-table tbody tr:hover td {
        background: #1b2636;
    }

    .gift-img-preview {
        width: 66px;
        height: 66px;
        object-fit: contain;
        background: #0d131d;
        border: 1px solid var(--gift-border);
        border-radius: 14px;
        padding: 6px;
    }

    .gift-type-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 82px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 800;
        font-size: .78rem;
        background: rgba(79, 158, 255, .14);
        color: var(--gift-primary);
        border: 1px solid rgba(79, 158, 255, .28);
    }

    .gift-action {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gift-btn-edit,
    .gift-btn-delete {
        border-radius: 10px;
        font-weight: 800;
        padding: 7px 13px;
        border: none;
        color: #fff;
        font-size: .84rem;
    }

    .gift-btn-edit {
        background: linear-gradient(135deg, #4f9eff 0%, #2563eb 100%);
    }

    .gift-btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    }

    .gift-btn-edit:hover,
    .gift-btn-delete:hover {
        color: #fff;
        opacity: .92;
    }

    .gift-alert {
        border-radius: 14px;
        border: 1px solid rgba(239, 68, 68, .25);
        background: rgba(239, 68, 68, .12);
        color: #fecaca;
    }

    .modal {
        overflow-y: auto;
    }

    .modal-backdrop {
        opacity: .72 !important;
    }

    .gift-modal .modal-dialog {
        transform: none !important;
    }

    .gift-modal .modal-content {
        background: #151d2a;
        color: var(--gift-text);
        border: 1px solid var(--gift-border);
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, .55);
        overflow: hidden;
    }

    .gift-modal .modal-header {
        background: linear-gradient(90deg, #1d2633 0%, #121a26 100%);
        border-bottom: 1px solid var(--gift-border);
    }

    .gift-modal .modal-title {
        color: var(--gift-text);
        font-weight: 800;
    }

    .gift-modal .close {
        color: #fff;
        opacity: .9;
        text-shadow: none;
    }

    .gift-modal .modal-footer {
        border-top: 1px solid var(--gift-border);
    }

    .gift-modal label {
        color: var(--gift-muted);
        font-weight: 800;
    }

    .gift-modal .form-control {
        height: 46px;
        border-radius: 12px;
        border: 2px solid var(--gift-border);
        background: var(--gift-input);
        color: var(--gift-text);
    }

    .gift-modal .form-control:focus {
        border-color: var(--gift-primary);
        box-shadow: 0 0 0 4px rgba(79, 158, 255, .16);
        background: var(--gift-input);
        color: var(--gift-text);
    }

    body.modal-open {
        padding-right: 0 !important;
    }

    @media (max-width: 768px) {
        .body-content {
            padding: 14px;
        }

        .gift-card-body,
        .gift-table-wrap {
            padding: 16px;
        }

        .gift-form-group {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .gift-list-header {
            padding: 18px;
            flex-direction: column;
            align-items: flex-start;
        }

        .gift-action {
            flex-direction: column;
            align-items: stretch;
        }

        .gift-btn-edit,
        .gift-btn-delete {
            width: 100%;
        }
    }
</style>

<div class="body-content">
    <div class="gift-page-card">
        <div class="gift-card-body">
            <h4 class="gift-title">New <span>Gift</span></h4>

            <form action="{{ URL::to('admin-gift-data-store') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="gift-form-grid">
                    <div class="gift-form-group">
                        <label for="gift_name" class="gift-label">Name *</label>
                        <input type="text" name="name" class="gift-input" placeholder="Enter Gift Name" value="" id="gift_name" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_value" class="gift-label">Amount *</label>
                        <input type="number" name="value" class="gift-input" placeholder="Enter Gift Value" value="" id="gift_value" min="0" step="1" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_image_name" class="gift-label">Image Name(.png) *</label>
                        <input type="text" name="image_name" class="gift-input" placeholder="Enter Gift image Name" value="" id="gift_image_name" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_image" class="gift-label">Image *</label>
                        <input type="file" name="image" class="gift-file" value="" id="gift_image" accept="image/png,image/jpeg,image/webp" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_svga_name" class="gift-label">Svga Name(.svga) *</label>
                        <input type="text" name="svga_name" class="gift-input" placeholder="Enter Gift .Svga" value="" id="gift_svga_name" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_svga" class="gift-label">SVGA *</label>
                        <input type="file" name="svga" class="gift-file" value="" id="gift_svga" accept=".svga" required>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group">
                        <label for="gift_category" class="gift-label">Category *</label>
                        <select name="category" class="gift-select select_agency_id" required id="gift_category">
                            <option value="1">Popular</option>
                            <option value="2">Luxury</option>
                            <option value="3">Festival</option>
                            <option value="4">Entry</option>
                            <option value="5">Frame</option>
                        </select>
                        <span class="text-danger"></span>
                    </div>

                    <div class="gift-form-group full">
                        <div class="gift-submit-wrap">
                            <button type="submit" class="gift-btn-submit">Active</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="gift-page-card">
        <div class="gift-list-header">
            <h4>Gift List</h4>
            <span class="gift-count">Total: {{ count($gifts) }}</span>
        </div>

        <div class="gift-table-wrap">
            <div class="table-responsive">
                <table class="table display table-bordered table-striped table-hover basic gift-table">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Svga Name</th>
                            <th>Image Name</th>
                            <th>Image</th>
                            <th>Value</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $i = 0;
                        @endphp

                        @foreach($gifts as $gift)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $gift->id }}</td>
                                <td>{{ $gift->name }}</td>
                                <td>{{ $gift->svga_name }}</td>
                                <td>{{ $gift->image_name }}</td>
                                <td>
                                    <img class="gift-img-preview" src="{{ URL::to($gift->image) }}" alt="{{ $gift->name }}">
                                </td>
                                <td>{{ $gift->value }}</td>
                                <td>
                                    @if($gift->category == 1)
                                        <span class="gift-type-badge">Popular</span>
                                    @elseif($gift->category == 2)
                                        <span class="gift-type-badge">Luxury</span>
                                    @elseif($gift->category == 3)
                                        <span class="gift-type-badge">Festival</span>
                                    @elseif($gift->category == 4)
                                        <span class="gift-type-badge">Entry</span>
                                    @else
                                        <span class="gift-type-badge">Frame</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="gift-action">
                                        <button type="button" class="gift-btn-edit" data-toggle="modal" data-target="#giftEditModal{{ $gift->id }}">
                                            Edit
                                        </button>

                                        <a href="{{ URL::to('gift_data_delete/'.$gift->id) }}" class="gift-btn-delete" onclick="return confirm('Are you sure you want to delete this gift?');">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>Sl</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Svga Name</th>
                            <th>Image Name</th>
                            <th>Image</th>
                            <th>Value</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($gifts as $gift)
    <div class="modal fade gift-modal" id="giftEditModal{{ $gift->id }}" tabindex="-1" role="dialog" aria-labelledby="giftEditModalLabel{{ $gift->id }}" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ URL::to('update_gift_data', $gift->id) }}" enctype="multipart/form-data" method="post">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="giftEditModalLabel{{ $gift->id }}">Update Gift Data</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_gift_name_{{ $gift->id }}" class="col-form-label">Name:</label>
                            <input type="text" name="name" value="{{ $gift->name }}" class="form-control" id="edit_gift_name_{{ $gift->id }}" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_gift_value_{{ $gift->id }}" class="col-form-label">Price:</label>
                            <input type="number" name="value" value="{{ $gift->value }}" class="form-control" id="edit_gift_value_{{ $gift->id }}" min="0" step="1" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_gift_category_{{ $gift->id }}" class="col-form-label">Category:</label>
                            <select name="category" class="form-control select_agency_id" id="edit_gift_category_{{ $gift->id }}" required>
                                <option value="1" {{ $gift->category == 1 ? 'selected' : '' }}>Popular</option>
                                <option value="2" {{ $gift->category == 2 ? 'selected' : '' }}>Luxury</option>
                                <option value="3" {{ $gift->category == 3 ? 'selected' : '' }}>Festival</option>
                                <option value="4" {{ $gift->category == 4 ? 'selected' : '' }}>Entry</option>
                                <option value="5" {{ $gift->category == 5 ? 'selected' : '' }}>Frame</option>
                            </select>
                            <span class="text-danger"></span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
$(document).ready(function () {
    $('.gift-modal').on('show.bs.modal', function () {
        $('.basic').css('pointer-events', 'none');
    });

    $('.gift-modal').on('hidden.bs.modal', function () {
        $('.basic').css('pointer-events', '');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });
});
</script>
@endsection