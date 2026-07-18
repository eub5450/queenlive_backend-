@foreach($chats as $chat)
<div class="chat-item p-3 mb-2 border rounded" data-id="{{ $chat['id'] }}" data-timestamp="{{ $chat['timestamp'] ?? time() }}">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-primary font-weight-bold">
                    <i class="fas fa-paper-plane mr-1"></i> {{ $chat['sender_name'] }} 
                    <span class="text-muted">(ID: {{ $chat['sender_id'] }})</span>
                </small>
                <small class="text-muted">
                    <i class="far fa-clock mr-1"></i>{{ $chat['time'] }}
                </small>
            </div>
            
            <div class="message-content p-2 bg-light rounded mb-2">
                <small class="text-danger font-weight-bold">
                    <i class="fas fa-comment mr-1"></i> {{ $chat['full_message'] }}
                </small>
                
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-success">
                    <i class="fas fa-inbox mr-1"></i> To: {{ $chat['receiver_name'] }} 
                    <span class="text-muted">(ID: {{ $chat['receiver_id'] }})</span>
                </small>
                <small class="private-channel-badge text-right">
                    <i class="fas fa-lock mr-1"></i>{{ $chat['private_channel'] ?? 'private-chat.channel' }}
                </small>
            </div>
        </div>
    </div>
</div>
@endforeach
