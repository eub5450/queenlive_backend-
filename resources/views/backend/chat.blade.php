@foreach($chat_data_all as $comment)

<div class="user-chat" data-username="{{$comment['receiver']}}">
    
    
    <div class="user-chat-text">
         <p class="mt-0 mb-0">Sander : {{$comment['sender']}}</p>
        
        
        <small style="font-size: 21px;color: red;">Text : {{$comment['message']}}</small>
       <p class="mt-0 mb-0">Reciver : {{$comment['receiver']}}</p>
    </div>
</div>
@endforeach