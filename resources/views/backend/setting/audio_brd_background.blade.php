@extends('backend.layouts.main')

@section('title', 'Audio Board Backgrounds')

@section('content')
@php
  use App\Support\MediaPathHelper;
@endphp

<style>
  .brd-bg-page {
    --brd-ink: #172033;
    --brd-muted: #68758d;
    --brd-line: rgba(23, 32, 51, .1);
    --brd-gold: #f8b938;
    --brd-blue: #246bfe;
    --brd-bg: #f5f7fb;
  }
  .brd-bg-hero {
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    background:
      radial-gradient(circle at 12% 18%, rgba(248, 185, 56, .28), transparent 28%),
      linear-gradient(135deg, #111b35 0%, #234277 54%, #0f1729 100%);
    color: #fff;
    box-shadow: 0 20px 50px rgba(21, 35, 70, .22);
  }
  .brd-bg-hero .card-body {
    padding: 26px 28px;
  }
  .brd-bg-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .2);
    font-size: 12px;
    letter-spacing: .08em;
    text-transform: uppercase;
  }
  .brd-bg-title {
    margin: 14px 0 6px;
    font-size: 30px;
    line-height: 1.1;
    font-weight: 800;
  }
  .brd-bg-subtitle {
    margin: 0;
    color: rgba(255, 255, 255, .76);
    max-width: 760px;
  }
  .brd-bg-shell {
    border: 0;
    border-radius: 22px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
  }
  .brd-bg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 18px;
  }
  .brd-bg-card {
    border: 1px solid var(--brd-line);
    border-radius: 18px;
    overflow: hidden;
    background: #fff;
    transition: transform .18s ease, box-shadow .18s ease;
  }
  .brd-bg-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(15, 23, 42, .1);
  }
  .brd-bg-preview {
    position: relative;
    min-height: 160px;
    background:
      linear-gradient(45deg, rgba(148, 163, 184, .16) 25%, transparent 25%),
      linear-gradient(-45deg, rgba(148, 163, 184, .16) 25%, transparent 25%),
      linear-gradient(45deg, transparent 75%, rgba(148, 163, 184, .16) 75%),
      linear-gradient(-45deg, transparent 75%, rgba(148, 163, 184, .16) 75%);
    background-size: 22px 22px;
    background-position: 0 0, 0 11px, 11px -11px, -11px 0;
  }
  .brd-bg-preview img {
    width: 100%;
    height: 190px;
    object-fit: cover;
    display: block;
  }
  .brd-bg-badge {
    position: absolute;
    left: 12px;
    top: 12px;
    padding: 6px 11px;
    border-radius: 999px;
    background: rgba(15, 23, 42, .78);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    backdrop-filter: blur(8px);
  }
  .brd-bg-meta {
    padding: 16px;
  }
  .brd-bg-meta h5 {
    color: var(--brd-ink);
    font-weight: 800;
    margin-bottom: 4px;
  }
  .brd-bg-meta p {
    color: var(--brd-muted);
    margin-bottom: 14px;
    font-size: 13px;
    word-break: break-word;
  }
  .brd-bg-btn {
    border: 0;
    border-radius: 12px;
    padding: 9px 15px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--brd-blue), #18a2ff);
    color: #fff;
    box-shadow: 0 10px 20px rgba(36, 107, 254, .22);
  }
  .brd-bg-btn:hover {
    color: #fff;
    filter: brightness(.96);
  }
  .brd-bg-empty {
    border: 1px dashed rgba(104, 117, 141, .35);
    border-radius: 18px;
    padding: 36px 18px;
    color: var(--brd-muted);
    text-align: center;
    background: var(--brd-bg);
  }
  .brd-bg-modal-preview {
    border-radius: 16px;
    background: var(--brd-bg);
    border: 1px solid var(--brd-line);
    overflow: hidden;
    min-height: 170px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .brd-bg-modal-preview img {
    width: 100%;
    max-height: 280px;
    object-fit: contain;
  }
  .brd-bg-help {
    color: var(--brd-muted);
    font-size: 12px;
    margin-top: 8px;
  }
  @media (max-width: 575.98px) {
    .brd-bg-hero .card-body {
      padding: 22px 18px;
    }
    .brd-bg-title {
      font-size: 24px;
    }
    .brd-bg-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="body-content brd-bg-page">
  <div class="card brd-bg-hero mb-4">
    <div class="card-body">
      <span class="brd-bg-kicker">QueenLive Admin</span>
      <h2 class="brd-bg-title">Audio Board Backgrounds</h2>
      <p class="brd-bg-subtitle">
        Manage the live audio board background images served from the QueenLive store path. Uploads are validated and saved to the active public storage location.
      </p>
    </div>
  </div>

  <div class="card brd-bg-shell">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
          <h4 class="mb-1">Default Background Set</h4>
          <p class="text-muted mb-0">Only default audio board backgrounds are shown here.</p>
        </div>
        <span class="badge badge-pill badge-primary mt-2 mt-sm-0">{{ $data->count() }} item{{ $data->count() === 1 ? '' : 's' }}</span>
      </div>

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      @if ($data->count())
        <div class="brd-bg-grid">
          @foreach ($data as $item)
            @php
              $imageUrl = MediaPathHelper::publicUrl($item->image);
            @endphp
            <div class="brd-bg-card">
              <div class="brd-bg-preview">
                <span class="brd-bg-badge">#{{ $loop->iteration }}</span>
                <img
                  src="{{ $imageUrl }}"
                  alt="Audio board background {{ $loop->iteration }}"
                  loading="lazy"
                  onerror="this.style.display='none'; this.parentNode.classList.add('bg-light');"
                >
              </div>
              <div class="brd-bg-meta">
                <h5>Background {{ $loop->iteration }}</h5>
                <p>{{ $imageUrl }}</p>
                <button
                  type="button"
                  class="btn brd-bg-btn"
                  data-toggle="modal"
                  data-target="#editAudioBrdBackgroundModal"
                  data-action="{{ route('audio-backgrounds.update', $item->id) }}"
                  data-image="{{ $imageUrl }}"
                  data-title="Background {{ $loop->iteration }}"
                >
                  Replace Image
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="brd-bg-empty">
          <h5 class="mb-1">No backgrounds found</h5>
          <p class="mb-0">No default audio board background records are available.</p>
        </div>
      @endif
    </div>
  </div>
</div>

<div class="modal fade" id="editAudioBrdBackgroundModal" tabindex="-1" role="dialog" aria-labelledby="editAudioBrdBackgroundLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="editAudioBrdBackgroundLabel">Replace Background</h5>
          <small class="text-muted" id="editAudioBrdBackgroundTitle">Choose a new image</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="editAudioBrdBackgroundForm" action="#" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="brd-bg-modal-preview mb-3">
            <img id="currentAudioBrdPreview" src="" alt="Current audio board background">
          </div>

          <div class="form-group mb-0">
            <label for="backgroundImage" class="font-weight-bold">Upload Image</label>
            <input type="file" name="image" id="backgroundImage" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <div class="brd-bg-help">Allowed: JPG, PNG, GIF, WEBP. Maximum size: 5 MB.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn brd-bg-btn">Save Background</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = $('#editAudioBrdBackgroundModal');
    var form = document.getElementById('editAudioBrdBackgroundForm');
    var currentPreview = document.getElementById('currentAudioBrdPreview');
    var title = document.getElementById('editAudioBrdBackgroundTitle');
    var fileInput = document.getElementById('backgroundImage');

    modal.on('show.bs.modal', function (event) {
      var button = event.relatedTarget || event.delegateTarget;
      var action = button ? button.getAttribute('data-action') : '#';
      var image = button ? button.getAttribute('data-image') : '';
      var label = button ? button.getAttribute('data-title') : 'Choose a new image';

      form.setAttribute('action', action || '#');
      currentPreview.setAttribute('src', image || '');
      title.textContent = label || 'Choose a new image';
      fileInput.value = '';
    });

    fileInput.addEventListener('change', function () {
      var file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
      if (!file) {
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        alert('Image must be 5 MB or smaller.');
        fileInput.value = '';
        return;
      }

      var reader = new FileReader();
      reader.onload = function (event) {
        currentPreview.setAttribute('src', event.target.result);
      };
      reader.readAsDataURL(file);
    });
  });
</script>
@endsection
