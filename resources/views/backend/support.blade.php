@extends('backend.layouts.main')

@section('title')
Help Desk Support
@endsection

@section('content')
@php
    $threadCollection = $threads ?? collect();
@endphp
<style>
    .bd-helpdesk-wrap,
    .bd-helpdesk-wrap * { box-sizing: border-box; }
    .bd-helpdesk-wrap {
        --bd-hd-blue: #2a7de1;
        --bd-hd-blue-dark: #1e63c2;
        --bd-hd-red: #e74c5e;
        --bd-hd-green: #31c48d;
        --bd-hd-bg: #eef3fb;
        --bd-hd-soft: #f2f6fd;
        --bd-hd-line: #e8edf5;
        --bd-hd-text: #14243e;
        --bd-hd-muted: #64748b;
        background:
            radial-gradient(circle at 12% 0%, rgba(42, 125, 225, .12), transparent 26%),
            linear-gradient(135deg, #f7faff 0%, var(--bd-hd-bg) 100%);
        border: 1px solid #dde7f5;
        border-radius: 24px;
        padding: 14px;
    }
    .bd-helpdesk-titlebar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }
    .bd-helpdesk-titlebar h4 { margin: 0; color: var(--bd-hd-text); font-weight: 800; letter-spacing: -.02em; }
    .bd-helpdesk-titlebar p { margin: 4px 0 0; color: var(--bd-hd-muted); }
    .bd-helpdesk-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 13px;
        background: #fff;
        color: var(--bd-hd-text);
        box-shadow: 0 8px 22px rgba(20, 36, 62, .08);
        font-weight: 700;
        white-space: nowrap;
    }
    .bd-helpdesk-status span {
        display: inline-flex;
        min-width: 22px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--bd-hd-red);
        color: #fff;
        font-size: 12px;
    }
    .bd-helpdesk-panel {
        height: calc(100vh - 178px);
        min-height: 570px;
        max-height: 790px;
        background: #fff;
        border: 1px solid var(--bd-hd-line);
        border-radius: 28px;
        box-shadow: 0 24px 50px rgba(20, 36, 62, .13);
        display: flex;
        overflow: hidden;
    }
    .bd-helpdesk-users {
        width: 36%;
        min-width: 320px;
        max-width: 460px;
        display: flex;
        flex-direction: column;
        min-height: 0;
        background: #fbfdff;
        border-right: 1px solid var(--bd-hd-line);
    }
    .bd-helpdesk-users-head {
        padding: 18px 20px 12px;
        border-bottom: 1px solid #eef2f8;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }
    .bd-helpdesk-users-head h5 {
        margin: 0;
        color: var(--bd-hd-text);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .bd-helpdesk-live-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--bd-hd-green);
        box-shadow: 0 0 0 5px rgba(49, 196, 141, .14);
    }
    .bd-helpdesk-search { padding: 12px 16px; }
    .bd-helpdesk-search input {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 999px;
        background: var(--bd-hd-soft);
        color: var(--bd-hd-text);
        outline: none;
        padding: 12px 16px;
        transition: all .16s ease;
    }
    .bd-helpdesk-search input:focus {
        background: #fff;
        border-color: var(--bd-hd-blue);
        box-shadow: 0 8px 18px rgba(42, 125, 225, .12);
    }
    .bd-helpdesk-list { flex: 1; min-height: 0; overflow-y: auto; padding: 8px 10px 14px; }
    .bd-helpdesk-list::-webkit-scrollbar,
    .bd-helpdesk-chat-scroll::-webkit-scrollbar { width: 5px; }
    .bd-helpdesk-list::-webkit-scrollbar-track,
    .bd-helpdesk-chat-scroll::-webkit-scrollbar-track { background: #eef2f8; border-radius: 10px; }
    .bd-helpdesk-list::-webkit-scrollbar-thumb,
    .bd-helpdesk-chat-scroll::-webkit-scrollbar-thumb { background: #cbd5e3; border-radius: 10px; }
    .bd-helpdesk-thread {
        display: flex;
        gap: 13px;
        align-items: center;
        padding: 11px 12px;
        border: 1px solid transparent;
        border-radius: 16px;
        color: var(--bd-hd-text);
        text-decoration: none;
        transition: all .15s ease;
        margin-bottom: 6px;
        position: relative;
    }
    .bd-helpdesk-thread:hover,
    .bd-helpdesk-thread.active {
        background: #eef6ff;
        border-color: #c8d9f5;
        color: var(--bd-hd-text);
        text-decoration: none;
        box-shadow: 0 9px 20px rgba(42, 125, 225, .08);
    }
    .bd-helpdesk-thread.active:before {
        content: "";
        position: absolute;
        left: 0;
        top: 12px;
        bottom: 12px;
        width: 4px;
        border-radius: 0 999px 999px 0;
        background: var(--bd-hd-blue);
    }
    .bd-helpdesk-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        flex: 0 0 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        color: #fff;
        font-weight: 800;
        background: linear-gradient(135deg, #2a7de1, #7c5cff);
        border: 2px solid #fff;
        box-shadow: 0 4px 14px rgba(20, 36, 62, .08);
        overflow: hidden;
    }
    .bd-helpdesk-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .bd-helpdesk-avatar:after {
        content: "";
        position: absolute;
        right: 1px;
        bottom: 1px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: var(--bd-hd-green);
        border: 2px solid #fff;
    }
    .bd-helpdesk-thread-main { min-width: 0; flex: 1; }
    .bd-helpdesk-thread-top { display: flex; justify-content: space-between; gap: 10px; align-items: center; }
    .bd-helpdesk-name {
        min-width: 0;
        color: var(--bd-hd-text);
        font-weight: 800;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .bd-helpdesk-user-id { color: #6d7e99; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .bd-helpdesk-preview {
        margin-top: 4px;
        color: var(--bd-hd-muted);
        font-size: 12px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .bd-helpdesk-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex: 0 0 auto; }
    .bd-helpdesk-time { color: #7e8da6; font-size: 11px; white-space: nowrap; }
    .bd-helpdesk-unread {
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--bd-hd-blue);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 0 7px;
    }
    .bd-helpdesk-unread.urgent { background: var(--bd-hd-red); }
    .bd-helpdesk-room { width: 64%; min-width: 0; flex: 1; display: flex; flex-direction: column; min-height: 0; background: #fff; }
    .bd-helpdesk-room > .tab-pane {
        display: none;
        height: 100%;
        min-height: 0;
    }
    .bd-helpdesk-room > .tab-pane.show.active {
        display: block;
    }
    .bd-helpdesk-chat { display: flex; flex-direction: column; height: 100%; min-height: 0; }
    .bd-helpdesk-chat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 22px;
        border-bottom: 1px solid var(--bd-hd-line);
        background: #fff;
        flex: 0 0 auto;
    }
    .bd-helpdesk-chat-person { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .bd-helpdesk-chat-title { min-width: 0; }
    .bd-helpdesk-chat-title h5 {
        margin: 0;
        color: var(--bd-hd-text);
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .bd-helpdesk-chat-title p { margin: 4px 0 0; color: var(--bd-hd-muted); font-size: 12px; }
    .bd-helpdesk-chat-actions { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; justify-content: flex-end; }
    .bd-helpdesk-chip {
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 800;
        background: #f0f4fe;
        color: var(--bd-hd-blue);
        border: 1px solid #dce8fb;
        white-space: nowrap;
    }
    .bd-helpdesk-chip.danger { background: #fff1f3; color: var(--bd-hd-red); border-color: #ffd4da; }
    .bd-helpdesk-chat-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 22px 24px 18px;
        scroll-behavior: smooth;
        background:
            radial-gradient(circle at 20% 0%, rgba(42, 125, 225, .07), transparent 28%),
            linear-gradient(180deg, #fbfdff 0%, #f5f8fc 100%);
    }
    .bd-helpdesk-message { max-width: 82%; display: flex; gap: 10px; margin-bottom: 14px; }
    .bd-helpdesk-message.admin { margin-left: auto; flex-direction: row-reverse; }
    .bd-helpdesk-message .bd-helpdesk-mini-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex: 0 0 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, #6b7b9a, #9fb2d0);
    }
    .bd-helpdesk-message.admin .bd-helpdesk-mini-avatar { background: linear-gradient(135deg, #2a7de1, #7c5cff); }
    .bd-helpdesk-bubble {
        background: #f0f4fe;
        color: #1d2b44;
        border-radius: 20px 20px 20px 6px;
        padding: 13px 17px;
        line-height: 1.5;
        box-shadow: 0 6px 18px rgba(20, 36, 62, .06);
        white-space: pre-wrap;
        word-break: break-word;
    }
    .bd-helpdesk-message.admin .bd-helpdesk-bubble {
        background: var(--bd-hd-blue);
        color: #fff;
        border-radius: 20px 20px 6px 20px;
        box-shadow: 0 10px 22px rgba(42, 125, 225, .18);
    }
    .bd-helpdesk-msg-time {
        display: block;
        margin-top: 7px;
        font-size: 10px;
        opacity: .68;
        text-align: right;
        font-weight: 700;
    }
    .bd-helpdesk-reply {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        padding: 14px 22px 18px;
        border-top: 1px solid var(--bd-hd-line);
        background: #fff;
        flex: 0 0 auto;
    }
    .bd-helpdesk-reply textarea {
        min-height: 48px;
        max-height: 140px;
        resize: vertical;
        flex: 1;
        border: 1px solid transparent;
        border-radius: 24px;
        background: var(--bd-hd-soft);
        padding: 13px 18px;
        color: var(--bd-hd-text);
        outline: none;
        transition: all .16s ease;
        box-shadow: inset 0 0 0 1px rgba(221, 231, 245, .75);
    }
    .bd-helpdesk-reply textarea:focus {
        background: #fff;
        border-color: var(--bd-hd-blue);
        box-shadow: 0 8px 18px rgba(42, 125, 225, .10);
    }
    .bd-helpdesk-send {
        border: 0;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 50%;
        background: var(--bd-hd-blue);
        color: #fff;
        box-shadow: 0 8px 18px rgba(42, 125, 225, .20);
        font-weight: 900;
        transition: all .16s ease;
        font-size: 18px;
    }
    .bd-helpdesk-send:hover { background: var(--bd-hd-blue-dark); }
    .bd-helpdesk-empty {
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--bd-hd-muted);
        background: #fff;
        border: 1px dashed #d9e3f2;
        border-radius: 24px;
    }
    .bd-helpdesk-no-result { display: none; padding: 18px; text-align: center; color: var(--bd-hd-muted); font-size: 13px; }
    @media (max-width: 991px) {
        .bd-helpdesk-panel { height: auto; min-height: 0; flex-direction: column; }
        .bd-helpdesk-users,
        .bd-helpdesk-room { width: 100%; max-width: none; min-width: 0; }
        .bd-helpdesk-users { border-right: 0; border-bottom: 1px solid var(--bd-hd-line); max-height: 390px; }
        .bd-helpdesk-chat-scroll { max-height: 560px; }
    }
    @media (max-width: 575px) {
        .bd-helpdesk-wrap { padding: 10px; border-radius: 18px; }
        .bd-helpdesk-titlebar,
        .bd-helpdesk-chat-head,
        .bd-helpdesk-reply { align-items: flex-start; flex-direction: column; }
        .bd-helpdesk-status,
        .bd-helpdesk-chat-actions { width: 100%; justify-content: space-between; }
        .bd-helpdesk-message { max-width: 96%; }
        .bd-helpdesk-reply textarea,
        .bd-helpdesk-send { width: 100%; }
    }
</style>

<div class="body-content">
    <div class="container-fluid">
        <div class="bd-helpdesk-wrap">
            <div class="bd-helpdesk-titlebar">
                <div>
                    <h4>Help Desk Support</h4>
                    <p>Messenger-style support inbox grouped by user ID.</p>
                </div>
                <div class="bd-helpdesk-status">
                    Pending replies <span>{{ $pendingCount ?? 0 }}</span>
                </div>
            </div>

            @if($threadCollection->isEmpty())
                <div class="bd-helpdesk-empty">
                    <div>
                        <h5 class="mb-2">No help requests yet</h5>
                        <div>New app support messages will appear here.</div>
                    </div>
                </div>
            @else
                <div class="bd-helpdesk-panel">
                    <aside class="bd-helpdesk-users">
                        <div class="bd-helpdesk-users-head">
                            <h5><span class="bd-helpdesk-live-dot"></span> User conversations</h5>
                            <small class="text-muted">{{ $threadCollection->count() }} grouped user threads</small>
                        </div>
                        <div class="bd-helpdesk-search">
                            <input type="text" id="bdHelpdeskSearch" placeholder="Search by user, ID, or message">
                        </div>
                        <div class="bd-helpdesk-list" id="bdHelpdeskList" role="tablist">
                            @foreach($threadCollection as $thread)
                                @php
                                    $user = $thread->user;
                                    $latest = $thread->latest;
                                    $name = $user->name ?? 'User '.$thread->user_id;
                                    $initial = strtoupper(mb_substr($name, 0, 1));
                                    $latestText = $latest->problem ?? '';
                                    $lastAt = $thread->last_at ?: optional($latest)->created_at;
                                    $avatar = $user->image ?? $user->photo ?? $user->avatar ?? null;
                                    $searchText = strtolower(trim($name.' '.$thread->user_id.' '.$latestText));
                                @endphp
                                <a
                                    class="bd-helpdesk-thread {{ $loop->first ? 'active' : '' }}"
                                    id="thread-tab-{{ $thread->user_id }}"
                                    data-toggle="pill"
                                    href="#thread-pane-{{ $thread->user_id }}"
                                    role="tab"
                                    data-user-search="{{ e($searchText) }}"
                                >
                                    <div class="bd-helpdesk-avatar">
                                        @if(!empty($avatar))
                                            <img src="{{ asset($avatar) }}" alt="{{ $name }}">
                                        @else
                                            {{ $initial }}
                                        @endif
                                    </div>
                                    <div class="bd-helpdesk-thread-main">
                                        <div class="bd-helpdesk-thread-top">
                                            <span class="bd-helpdesk-name">{{ $name }}</span>
                                            <span class="bd-helpdesk-user-id">#{{ $thread->user_id }}</span>
                                        </div>
                                        <div class="bd-helpdesk-preview">{{ \Illuminate\Support\Str::limit($latestText, 58) }}</div>
                                    </div>
                                    <div class="bd-helpdesk-meta">
                                        <span class="bd-helpdesk-time">{{ optional($lastAt)->diffForHumans() }}</span>
                                        @if($thread->pending > 0)
                                            <span class="bd-helpdesk-unread {{ $thread->pending > 1 ? 'urgent' : '' }}">{{ $thread->pending }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                            <div class="bd-helpdesk-no-result" id="bdHelpdeskNoResult">No user thread matched your search.</div>
                        </div>
                    </aside>

                    <section class="bd-helpdesk-room tab-content">
                        @foreach($threadCollection as $thread)
                            @php
                                $user = $thread->user;
                                $name = $user->name ?? 'User '.$thread->user_id;
                                $initial = strtoupper(mb_substr($name, 0, 1));
                                $openTicket = $thread->messages->where('status', 0)->last();
                                $replyTicket = $openTicket ?: $thread->latest;
                                $avatar = $user->image ?? $user->photo ?? $user->avatar ?? null;
                            @endphp
                            <div
                                class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="thread-pane-{{ $thread->user_id }}"
                                role="tabpanel"
                                aria-labelledby="thread-tab-{{ $thread->user_id }}"
                            >
                                <div class="bd-helpdesk-chat">
                                    <div class="bd-helpdesk-chat-head">
                                        <div class="bd-helpdesk-chat-person">
                                            <div class="bd-helpdesk-avatar">
                                                @if(!empty($avatar))
                                                    <img src="{{ asset($avatar) }}" alt="{{ $name }}">
                                                @else
                                                    {{ $initial }}
                                                @endif
                                            </div>
                                            <div class="bd-helpdesk-chat-title">
                                                <h5>{{ $name }}</h5>
                                                <p>User ID: {{ $thread->user_id }} | Messages: {{ $thread->messages->count() }}</p>
                                            </div>
                                        </div>
                                        <div class="bd-helpdesk-chat-actions">
                                            @if($thread->pending > 0)
                                                <span class="bd-helpdesk-chip danger">Pending {{ $thread->pending }}</span>
                                            @else
                                                <span class="bd-helpdesk-chip">All replied</span>
                                            @endif
                                            <span class="bd-helpdesk-chip">Last: {{ optional($thread->last_at)->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="bd-helpdesk-chat-scroll">
                                        @foreach($thread->messages as $item)
                                            <div class="bd-helpdesk-message user">
                                                <div class="bd-helpdesk-mini-avatar">{{ $initial }}</div>
                                                <div class="bd-helpdesk-bubble">
                                                    {{ $item->problem }}
                                                    <span class="bd-helpdesk-msg-time">{{ optional($item->created_at)->format('d M Y h:i A') }}</span>
                                                </div>
                                            </div>
                                            @if(!empty($item->replay))
                                                <div class="bd-helpdesk-message admin">
                                                    <div class="bd-helpdesk-mini-avatar">AD</div>
                                                    <div class="bd-helpdesk-bubble">
                                                        {{ $item->replay }}
                                                        <span class="bd-helpdesk-msg-time">{{ optional($item->replied_at ?: $item->updated_at)->format('d M Y h:i A') }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <form class="bd-helpdesk-reply" action="{{ URL::to('support_replay', $replyTicket->id) }}" method="post">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $thread->user_id }}">
                                        <textarea name="replay" placeholder="Type admin reply to {{ $name }}..." required></textarea>
                                        <button type="submit" class="bd-helpdesk-send" title="Send reply">&gt;</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </section>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('bdHelpdeskSearch');
        var list = document.getElementById('bdHelpdeskList');
        var empty = document.getElementById('bdHelpdeskNoResult');
        if (!input || !list) {
            return;
        }

        function scrollChatToBottom(target) {
            var scroll = target ? target.querySelector('.bd-helpdesk-chat-scroll') : null;
            if (!scroll) {
                return;
            }

            var run = function () {
                scroll.scrollTop = scroll.scrollHeight;
            };

            run();
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(run);
            }
            window.setTimeout(run, 80);
        }

        function activateThread(thread) {
            if (!thread) {
                return;
            }

            var targetSelector = thread.getAttribute('href');
            if (!targetSelector || targetSelector.charAt(0) !== '#') {
                return;
            }

            var target = document.querySelector(targetSelector);
            if (!target) {
                return;
            }

            var threads = list.querySelectorAll('.bd-helpdesk-thread');
            Array.prototype.forEach.call(threads, function (item) {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });

            var panes = document.querySelectorAll('.bd-helpdesk-room .tab-pane');
            Array.prototype.forEach.call(panes, function (pane) {
                pane.classList.remove('show');
                pane.classList.remove('active');
            });

            thread.classList.add('active');
            thread.setAttribute('aria-selected', 'true');
            target.classList.add('show');
            target.classList.add('active');

            scrollChatToBottom(target);
        }

        list.addEventListener('click', function (event) {
            var target = event.target;
            while (target && target !== list && !target.classList.contains('bd-helpdesk-thread')) {
                target = target.parentNode;
            }

            if (!target || target === list) {
                return;
            }

            event.preventDefault();
            activateThread(target);
        });

        input.addEventListener('input', function () {
            var query = (input.value || '').toLowerCase().trim();
            var items = list.querySelectorAll('.bd-helpdesk-thread');
            var visible = 0;
            var firstVisible = null;

            Array.prototype.forEach.call(items, function (item) {
                var haystack = item.getAttribute('data-user-search') || '';
                var matched = !query || haystack.indexOf(query) !== -1;
                item.style.display = matched ? 'flex' : 'none';
                if (matched) {
                    visible += 1;
                    if (!firstVisible) {
                        firstVisible = item;
                    }
                }
            });

            if (empty) {
                empty.style.display = visible ? 'none' : 'block';
            }

            if (firstVisible && firstVisible.style.display !== 'none') {
                activateThread(firstVisible);
            }
        });

        activateThread(list.querySelector('.bd-helpdesk-thread.active') || list.querySelector('.bd-helpdesk-thread'));
    })();
</script>
@endsection
