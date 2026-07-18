@extends('backend.layouts.main')

@section('title')
Moment View
@endsection

@section('content')
<style>
    .moment-show-page{padding:24px;background:#f5f7fb;min-height:calc(100vh - 70px)}
    .moment-show-shell{max-width:100%;margin:0 auto}
    .moment-show-grid{display:grid;grid-template-columns:minmax(320px,420px) minmax(0,1fr);gap:22px}
    .moment-panel{background:#fff;border:1px solid #e3e8f3;border-radius:18px;box-shadow:0 12px 30px rgba(16,24,40,.06);overflow:hidden}
    .moment-panel-head{padding:18px 20px;border-bottom:1px solid #edf2f7;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .moment-title{margin:0;font-size:20px;font-weight:800;color:#111827}
    .moment-back{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:12px;background:#eef2ff;color:#1f3a8a;font-weight:700;text-decoration:none}
    .moment-media{padding:20px}
    .moment-player{width:100%;max-height:720px;border-radius:16px;background:#0f172a}
    .moment-body{padding:20px}
    .moment-info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .moment-stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px}
    .moment-stat-label{font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase}
    .moment-stat-value{margin-top:6px;font-size:18px;color:#111827;font-weight:800}
    .moment-section{padding:20px;border-top:1px solid #edf2f7}
    .moment-section h3{margin:0 0 12px;font-size:16px;font-weight:800;color:#111827}
    .moment-meta{font-size:14px;color:#334155;line-height:1.7;word-break:break-word}
    .moment-meta a{word-break:break-all}
    .moment-table-wrap{overflow:auto}
    .moment-table{width:100%;border-collapse:collapse}
    .moment-table th,.moment-table td{padding:10px 8px;border-bottom:1px solid #edf2f7;text-align:left;font-size:13px;vertical-align:top}
    .moment-table th{color:#475569;text-transform:uppercase;font-size:11px}
    .moment-delete{border:0;border-radius:12px;padding:10px 14px;background:#fee2e2;color:#b91c1c;font-weight:700;cursor:pointer}
    .moment-empty{color:#64748b;font-size:13px;font-weight:600}
    .moment-head-actions{display:flex;gap:10px;flex-wrap:wrap}
    .moment-head-actions form{margin:0}
    @media(max-width:960px){.moment-show-grid{grid-template-columns:1fr}.moment-info{grid-template-columns:1fr 1fr}}
    @media(max-width:640px){
        .moment-show-page{padding:12px}
        .moment-panel-head,.moment-media,.moment-body,.moment-section{padding:16px}
        .moment-info{grid-template-columns:1fr}
        .moment-head-actions{width:100%;flex-direction:column}
        .moment-back,.moment-delete{width:100%}
    }
</style>

<div class="body-content moment-show-page">
    <div class="moment-show-shell">
    <div class="moment-panel" style="margin-bottom:20px;">
        <div class="moment-panel-head">
            <div>
                <h1 class="moment-title">Moment #{{ $video->id }}</h1>
            </div>
            <div class="moment-head-actions">
                <a href="{{ URL::to('admin/moments-list') }}" class="moment-back">Back to list</a>
                <form action="{{ URL::to('admin/moments-delete/'.$video->id) }}" method="post" onsubmit="return confirm('Remove this moment?');">
                    @csrf
                    <button type="submit" class="moment-delete">Delete Moment</button>
                </form>
            </div>
        </div>
    </div>

    <div class="moment-show-grid">
        <div class="moment-panel">
            <div class="moment-media">
                <video class="moment-player" controls playsinline preload="metadata">
                    <source src="{{ $video->video_url }}" type="video/mp4">
                </video>
            </div>
        </div>

        <div class="moment-panel">
            <div class="moment-body">
                <div class="moment-info">
                    <div class="moment-stat">
                        <div class="moment-stat-label">User</div>
                        <div class="moment-stat-value">{{ $video->user->name ?? 'QueenLive User' }}</div>
                        <div class="moment-meta">User ID: {{ $video->user_id }}</div>
                    </div>
                    <div class="moment-stat">
                        <div class="moment-stat-label">Status</div>
                        <div class="moment-stat-value">{{ ucfirst($video->status) }}</div>
                        <div class="moment-meta">Created: {{ optional($video->created_at)->format('d M Y h:i A') }}</div>
                    </div>
                    <div class="moment-stat">
                        <div class="moment-stat-label">Views / Likes</div>
                        <div class="moment-stat-value">{{ number_format($video->views_count) }} / {{ number_format($video->likes_count) }}</div>
                        <div class="moment-meta">Comments: {{ number_format($video->comments_count) }}</div>
                    </div>
                    <div class="moment-stat">
                        <div class="moment-stat-label">Gifts</div>
                        <div class="moment-stat-value">{{ number_format($video->gifts_count) }}</div>
                        <div class="moment-meta">Gift Value: {{ number_format($video->gift_value) }}</div>
                    </div>
                </div>
            </div>

            <div class="moment-section">
                <h3>Caption</h3>
                <div class="moment-meta">{{ $video->caption ?: 'No caption' }}</div>
            </div>

            <div class="moment-section">
                <h3>Media Info</h3>
                <div class="moment-meta">
                    Video URL: <a href="{{ $video->video_url }}" target="_blank">{{ $video->video_url }}</a><br>
                    Thumb URL: @if($video->thumb_url)<a href="{{ $video->thumb_url }}" target="_blank">{{ $video->thumb_url }}</a>@else N/A @endif<br>
                    Duration: {{ $video->duration }} seconds<br>
                    Size: {{ $video->width }} x {{ $video->height }}
                </div>
            </div>

            <div class="moment-section">
                <h3>Recent Comments</h3>
                @if($comments->isEmpty())
                    <div class="moment-empty">No comments found.</div>
                @else
                    <div class="moment-table-wrap">
                    <table class="moment-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Comment</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td>#{{ $comment->id }}</td>
                                    <td>{{ $comment->user->name ?? 'QueenLive User' }}<br><span class="moment-meta">ID: {{ $comment->user_id }}</span></td>
                                    <td>{{ $comment->comment }}</td>
                                    <td>{{ optional($comment->created_at)->format('d M Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>

            <div class="moment-section">
                <h3>Recent Gifts</h3>
                @if($gifts->isEmpty())
                    <div class="moment-empty">No gifts found.</div>
                @else
                    <div class="moment-table-wrap">
                    <table class="moment-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Coin</th>
                                <th>Qty</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gifts as $gift)
                                <tr>
                                    <td>#{{ $gift->id }}</td>
                                    <td>{{ $gift->sender_id }}</td>
                                    <td>{{ $gift->receiver_id }}</td>
                                    <td>{{ number_format($gift->coin) }}</td>
                                    <td>{{ number_format($gift->quantity) }}</td>
                                    <td>{{ optional($gift->created_at)->format('d M Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
