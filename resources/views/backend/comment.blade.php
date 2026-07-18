@foreach($comments as $comment)
@php
    $host = \App\RedisCache\RedisCache::UserfindById($comment->reciever_id);
    $user = \App\RedisCache\RedisCache::UserfindById($comment->user_id);
    
    // Add null checks to prevent errors
    $hostName = $host->name ?? 'Deleted User';
    $userName = $user->name ?? 'Deleted User';
    $hostProfile = $host->profile ?? URL::to('default-profile.png');
    $hostId = $host->id ?? 0;
    $userId = $user->id ?? 0;
@endphp

<div class="user-chat" data-username="{{ $hostName }}">
    <div class="user-chat-img">
        <img src="{{ $hostProfile }}" alt="{{ $hostName }}" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
        <div class="status-badge {{ $host->is_online ? 'online' : 'offline' }}"></div>
    </div>
    
    <div class="user-chat-text">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">
                {{ $hostId }}-{{ Str::limit($userName, 20) }} - {{ $userId }}
            </h6>
            <span class="badge bg-secondary">
                {{ $comment->created_at->diffForHumans() }}
            </span>
        </div>
        
        <div class="comment-message mb-3">
            <p class="mb-0 text-danger font-weight-bold">{{ $comment->message }}</p>
        </div>
      
    </div>
</div>


@endforeach

<style>
    .user-chat {
        display: flex;
        gap: 15px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .user-chat:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
    }
    
    .status-badge {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: absolute;
        bottom: 5px;
        right: 5px;
        border: 2px solid white;
    }
    
    .online {
        background-color: #28a745;
    }
    
    .offline {
        background-color: #6c757d;
    }
    
    .comment-message {
        background-color: white;
        padding: 10px 15px;
        border-radius: 8px;
        border-left: 3px solid #dc3545;
    }
</style>