@extends('backend.layouts.main')

@section('title')
Moments List
@endsection

@section('content')
<style>
    .moments-page{padding:24px;background:#f5f7fb;min-height:calc(100vh - 70px)}
    .moments-shell{max-width:100%;margin:0 auto}
    .moments-card{background:#fff;border:1px solid #e3e8f3;border-radius:18px;box-shadow:0 12px 30px rgba(16,24,40,.06);overflow:hidden}
    .moments-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px 22px;border-bottom:1px solid #edf2f7;flex-wrap:wrap}
    .moments-title{margin:0;font-size:22px;font-weight:800;color:#111827}
    .moments-sub{margin:4px 0 0;color:#64748b;font-size:13px}
    .moments-search{display:flex;gap:10px;flex-wrap:wrap}
    .moments-input{height:42px;min-width:260px;max-width:100%;border:1px solid #d6deeb;border-radius:12px;padding:0 14px;font-size:14px;background:#fff;color:#111827}
    .moments-btn{height:42px;border:0;border-radius:12px;padding:0 16px;font-weight:700;cursor:pointer;line-height:42px;white-space:nowrap}
    .moments-btn-primary{background:#0f62fe;color:#fff}
    .moments-btn-light{background:#eef2ff;color:#1f3a8a;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
    .moments-table-wrap{overflow:auto;padding:18px}
    .moments-table{width:100%;border-collapse:collapse;min-width:1080px;table-layout:fixed}
    .moments-table th,.moments-table td{padding:14px 12px;border-bottom:1px solid #edf2f7;vertical-align:top;text-align:left}
    .moments-table th{font-size:12px;color:#475569;text-transform:uppercase;letter-spacing:.04em}
    .moments-preview{width:84px;height:132px;border-radius:12px;overflow:hidden;background:#0f172a;display:flex;align-items:center;justify-content:center}
    .moments-preview img,.moments-preview video{width:100%;height:100%;object-fit:cover;display:block}
    .moments-user{font-weight:700;color:#111827}
    .moments-meta{font-size:12px;color:#64748b;line-height:1.5;word-break:break-word}
    .moments-caption{max-width:260px;font-size:13px;color:#334155;line-height:1.5;white-space:normal;word-break:break-word}
    .moments-stats{font-size:12px;color:#334155;line-height:1.7;word-break:break-word}
    .moments-pill{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
    .moments-pill-active{background:#dcfce7;color:#166534}
    .moments-pill-processing{background:#fef3c7;color:#92400e}
    .moments-pill-blocked{background:#fee2e2;color:#b91c1c}
    .moments-actions{display:flex;gap:8px;flex-wrap:wrap}
    .moments-action{border:0;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
    .moments-action-view{background:#e0f2fe;color:#075985}
    .moments-action-delete{background:#fee2e2;color:#b91c1c}
    .moments-empty{padding:36px 22px;color:#64748b;font-weight:600}
    .moments-pagination{padding:0 18px 18px}
    .moments-pagination .pagination{margin:0;display:flex;flex-wrap:wrap;gap:8px}
    .moments-pagination .page-item .page-link{border-radius:10px;border:1px solid #d6deeb;color:#1e293b;padding:8px 13px;line-height:1.2}
    .moments-pagination .page-item.active .page-link{background:#0f62fe;border-color:#0f62fe;color:#fff}
    .moments-pagination .page-item.disabled .page-link{color:#94a3b8;background:#f8fafc}
    .moments-actions form{margin:0}
    @media(max-width:991px){
        .moments-page{padding:16px}
        .moments-head{padding:18px}
        .moments-search{width:100%}
        .moments-input{min-width:0;flex:1 1 220px}
    }
    @media(max-width:640px){
        .moments-page{padding:12px}
        .moments-head{padding:16px}
        .moments-title{font-size:20px}
        .moments-sub{font-size:12px}
        .moments-table-wrap{padding:12px}
        .moments-search{flex-direction:column;align-items:stretch}
        .moments-input,.moments-btn,.moments-btn-light{width:100%}
        .moments-btn,.moments-btn-light{justify-content:center}
    }
</style>

<div class="body-content moments-page">
    <div class="moments-shell">
    <div class="moments-card">
        <div class="moments-head">
            <div>
                <h1 class="moments-title">Moments List</h1>
                <p class="moments-sub">View and remove QueenLive moments from admin panel.</p>
            </div>
            <form action="{{ URL::to('admin/moments-list') }}" method="get" class="moments-search">
                <input type="text" name="search" value="{{ $search }}" class="moments-input" placeholder="Search by moment id, user id, caption">
                <button type="submit" class="moments-btn moments-btn-primary">Search</button>
                <a href="{{ URL::to('admin/moments-list') }}" class="moments-btn moments-btn-light">Reset</a>
            </form>
        </div>

        @if($videos->count() === 0)
            <div class="moments-empty">No moments found.</div>
        @else
            <div class="moments-table-wrap">
                <table class="moments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>User</th>
                            <th>Caption</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($videos as $video)
                            @php
                                $statusClass = $video->status === 'active'
                                    ? 'moments-pill-active'
                                    : ($video->status === 'processing' ? 'moments-pill-processing' : 'moments-pill-blocked');
                            @endphp
                            <tr>
                                <td>#{{ $video->id }}</td>
                                <td>
                                    <div class="moments-preview">
                                        @if(!empty($video->thumb_url))
                                            <img src="{{ $video->thumb_url }}" alt="Moment thumbnail">
                                        @else
                                            <video preload="metadata" muted playsinline>
                                                <source src="{{ $video->video_url }}" type="video/mp4">
                                            </video>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="moments-user">{{ $video->user->name ?? 'QueenLive User' }}</div>
                                    <div class="moments-meta">User ID: {{ $video->user_id }}</div>
                                    <div class="moments-meta">Video: {{ $video->duration }}s | {{ $video->width }}x{{ $video->height }}</div>
                                </td>
                                <td class="moments-caption">{{ \Illuminate\Support\Str::limit($video->caption ?? '', 140) ?: 'No caption' }}</td>
                                <td class="moments-stats">
                                    Views: {{ number_format($video->views_count) }}<br>
                                    Likes: {{ number_format($video->likes_count) }}<br>
                                    Comments: {{ number_format($video->comments_count) }}<br>
                                    Gifts: {{ number_format($video->gifts_count) }}<br>
                                    Gift Value: {{ number_format($video->gift_value) }}
                                </td>
                                <td><span class="moments-pill {{ $statusClass }}">{{ ucfirst($video->status) }}</span></td>
                                <td class="moments-meta">{{ optional($video->created_at)->format('d M Y h:i A') }}</td>
                                <td>
                                    <div class="moments-actions">
                                        <a href="{{ URL::to('admin/moments-view/'.$video->id) }}" class="moments-action moments-action-view">View</a>
                                        <form action="{{ URL::to('admin/moments-delete/'.$video->id) }}" method="post" onsubmit="return confirm('Remove this moment?');">
                                            @csrf
                                            <button type="submit" class="moments-action moments-action-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="moments-pagination">
                {{ $videos->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
    </div>
</div>
@endsection
