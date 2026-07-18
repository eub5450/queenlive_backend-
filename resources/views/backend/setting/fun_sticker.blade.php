@extends('backend.layouts.main')

@section('title')
Fun Sticker Setting
@endsection

@php
    $initialRows = old('sticker_id') ? collect(old('sticker_id'))->count() : count($stickers);
    $initialRows = max($initialRows, 1);
    $allowedTypes = ['webp', 'gif', 'svga', 'image'];
@endphp

@section('content')
<div class="body-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div>
                    <h4 class="mb-1">Fun Sticker Setting</h4>
                    <small class="text-muted">Manage the live catalog used by <code>/api/v5/room-fun-emoji</code>.</small>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge badge-success">Live Dynamic Source</span>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>How it works:</strong>
                whatever you save here becomes the live Fun Sticker catalog for the app.
                Use a full URL or a relative server path like <code>store/banner/offer.webp</code>.
                If you clear all rows, the API falls back to the built-in default stickers.
            </div>

            <form action="{{ route('admin.fun_sticker.save') }}" method="POST">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="funStickerTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width: 180px;">Sticker ID</th>
                                <th style="width: 140px;">Type</th>
                                <th style="min-width: 280px;">URL / Path</th>
                                <th style="width: 120px;">Preview</th>
                                <th style="width: 90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="funStickerRows">
                            @for ($i = 0; $i < $initialRows; $i++)
                                @php
                                    $row = $stickers[$i] ?? ['id' => '', 'type' => 'image', 'url' => '', 'preview_url' => ''];
                                    $idValue = old("sticker_id.$i", $row['id'] ?? '');
                                    $typeValue = old("sticker_type.$i", $row['type'] ?? 'image');
                                    $urlValue = old("sticker_url.$i", $row['url'] ?? '');
                                    $previewValue = old("sticker_url.$i")
                                        ? old("sticker_url.$i")
                                        : ($row['preview_url'] ?? '');
                                @endphp
                                <tr class="fun-sticker-row">
                                    <td>
                                        <input type="text" name="sticker_id[]" class="form-control" value="{{ $idValue }}" placeholder="fun_my_sticker">
                                    </td>
                                    <td>
                                        <select name="sticker_type[]" class="form-control">
                                            @foreach ($allowedTypes as $type)
                                                <option value="{{ $type }}" {{ $typeValue === $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="sticker_url[]" class="form-control sticker-url-input" value="{{ $urlValue }}" placeholder="store/banner/offer.webp or https://...">
                                    </td>
                                    <td>
                                        <div class="border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden sticker-preview-box" style="height: 72px;">
                                            @if ($previewValue && $typeValue !== 'svga')
                                                <img src="{{ preg_match('#^https?://#i', $previewValue) ? $previewValue : url('/' . ltrim($previewValue, '/')) }}" class="img-fluid sticker-preview-image" style="max-height: 68px;" alt="preview">
                                            @elseif ($typeValue === 'svga' && $urlValue)
                                                <span class="badge badge-warning">SVGA</span>
                                            @else
                                                <span class="text-muted small sticker-preview-empty">No preview</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">Remove</button>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                    <button type="button" class="btn btn-outline-primary mb-2 mb-md-0" id="addFunStickerRow">
                        <i class="fas fa-plus mr-1"></i>Add Sticker
                    </button>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-success mr-2">Save Fun Sticker Catalog</button>
                        <button type="button" class="btn btn-outline-secondary" id="clearFunStickerRows">Clear Rows</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="funStickerRowTemplate">
    <tr class="fun-sticker-row">
        <td>
            <input type="text" name="sticker_id[]" class="form-control" value="" placeholder="fun_my_sticker">
        </td>
        <td>
            <select name="sticker_type[]" class="form-control">
                <option value="webp">WEBP</option>
                <option value="gif">GIF</option>
                <option value="svga">SVGA</option>
                <option value="image" selected>IMAGE</option>
            </select>
        </td>
        <td>
            <input type="text" name="sticker_url[]" class="form-control sticker-url-input" value="" placeholder="store/banner/offer.webp or https://...">
        </td>
        <td>
            <div class="border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden sticker-preview-box" style="height: 72px;">
                <span class="text-muted small sticker-preview-empty">No preview</span>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">Remove</button>
        </td>
    </tr>
</template>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rowsRoot = document.getElementById('funStickerRows');
    var addBtn = document.getElementById('addFunStickerRow');
    var clearBtn = document.getElementById('clearFunStickerRows');
    var template = document.getElementById('funStickerRowTemplate');
    var baseUrl = "{{ url('/') }}";

    function resolvePreview(url) {
        var value = (url || '').trim();
        if (!value) {
            return '';
        }
        if (/^https?:\/\//i.test(value)) {
            return value;
        }
        return baseUrl.replace(/\/$/, '') + '/' + value.replace(/^\/+/, '');
    }

    function bindRow(row) {
        var removeBtn = row.querySelector('.remove-row-btn');
        var urlInput = row.querySelector('.sticker-url-input');
        var typeSelect = row.querySelector('select[name="sticker_type[]"]');

        removeBtn.addEventListener('click', function () {
            var currentRows = rowsRoot.querySelectorAll('.fun-sticker-row');
            if (currentRows.length === 1) {
                row.querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
                row.querySelector('select[name="sticker_type[]"]').value = 'image';
                renderPreview(row);
                return;
            }
            row.remove();
        });

        urlInput.addEventListener('input', function () {
            renderPreview(row);
        });

        typeSelect.addEventListener('change', function () {
            renderPreview(row);
        });

        renderPreview(row);
    }

    function renderPreview(row) {
        var input = row.querySelector('.sticker-url-input');
        var typeSelect = row.querySelector('select[name="sticker_type[]"]');
        var previewBox = row.querySelector('.sticker-preview-box');
        var url = resolvePreview(input.value);
        var type = (typeSelect.value || '').toLowerCase();

        if (!url) {
            previewBox.innerHTML = '<span class="text-muted small sticker-preview-empty">No preview</span>';
            return;
        }

        if (type === 'svga') {
            previewBox.innerHTML = '<span class="badge badge-warning">SVGA</span>';
            return;
        }

        previewBox.innerHTML = '<img src="' + url + '" class="img-fluid sticker-preview-image" style="max-height:68px;" alt="preview">';
    }

    addBtn.addEventListener('click', function () {
        var clone = document.importNode(template.content, true);
        rowsRoot.appendChild(clone);
        bindRow(rowsRoot.lastElementChild);
    });

    clearBtn.addEventListener('click', function () {
        rowsRoot.querySelectorAll('.fun-sticker-row').forEach(function (row, index) {
            if (index === 0) {
                row.querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
                row.querySelector('select[name="sticker_type[]"]').value = 'image';
                renderPreview(row);
            } else {
                row.remove();
            }
        });
    });

    rowsRoot.querySelectorAll('.fun-sticker-row').forEach(bindRow);
});
</script>
@endsection
