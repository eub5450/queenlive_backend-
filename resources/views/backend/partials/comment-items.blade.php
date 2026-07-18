@foreach($comments as $comment)
<div class="comment-item p-3 mb-2 border rounded" data-id="{{ $comment['id'] }}" data-timestamp="{{ $comment['timestamp'] ?? time() }}">
    <div class="d-flex">
        <!-- Receiver Image -->
        <div class="position-relative mr-3">
            <img src="{{ $comment['receiver_profile'] }}" alt="{{ $comment['receiver_name'] }}" 
                 class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
            <span class="status-badge {{ $comment['receiver_online'] ? 'online' : 'offline' }}"></span>
        </div>
        
        <!-- Comment Content -->
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong class="text-primary">{{ $comment['receiver_name'] }}</strong>
                    <small class="text-muted ml-2">ID: {{ $comment['receiver_id'] }}</small>
                    @if($comment['channel_name'] != 'General')
                        <span class="badge bg-info ml-2">{{ $comment['channel_name'] }}</span>
                    @endif
                    <span class="private-channel-badge ml-2">
                        <i class="fas fa-lock mr-1"></i>{{ $comment['private_channel'] ?? 'private-*-room.channel' }}
                    </span>
                </div>
                <small class="text-muted">
                    <i class="far fa-clock mr-1"></i>{{ $comment['time'] }}
                </small>
            </div>
            
            <div class="comment-message p-2 bg-light rounded mb-2">
                <p class="mb-0 text-danger">{{ $comment['full_message'] }}</p>
              
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-success">
                    <i class="fas fa-user mr-1"></i> From: {{ $comment['user_name'] }} 
                    <span class="text-muted">(ID: {{ $comment['user_id'] }})</span>
                </small>
                
            </div>
        </div>
    </div>
</div>
@endforeach
