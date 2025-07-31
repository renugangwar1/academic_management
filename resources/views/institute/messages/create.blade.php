@extends('layouts.institute')
@section('content')
<div class="container mt-4" style="max-width: 700px; height: 80vh; display: flex; flex-direction: column;">
    <h4 class="mb-3">Messages with Admin</h4>

    {{-- Chat and Form wrapper --}}
    <div style="flex: 1; display: flex; flex-direction: column; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">

        {{-- Chat Box --}}
        <div id="chat-box"
            style="flex: 1; overflow-y: auto; padding: 15px; background: #e5ddd5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            
            {{-- Success and Errors --}}
            @if(session('success'))
                <div style="display: flex; justify-content: flex-end; margin-bottom: 12px;">
                    <div class="sent-message" style="background-color: #d4edda; color: #155724;">
                        ✅ {{ session('success') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Messages --}}
            @if($messages->isEmpty())
                <p class="text-muted text-center">No messages yet.</p>
            @else
              @foreach($messages as $msg)
    @php
        // Admin-sent = received; Institute-sent = sent
        $isSent = !$msg->is_admin;
    @endphp

 <div style="display: flex; justify-content: {{ $isSent ? 'flex-end' : 'flex-start' }}; margin-bottom: 12px;">
    <div class="{{ $isSent ? 'sent-message' : 'received-message' }}"
         style="width: 100%; max-width: 85%; padding: 12px 16px; border-radius: 14px;
                background-color: {{ $isSent ? '#DCF8C6' : '#FFFFFF' }};
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); word-wrap: break-word;">
        
        <div style="font-size: 13px; color: {{ $isSent ? '#34B7F1' : '#555' }}; margin-bottom: 6px;">
            <strong>{{ $isSent ? 'Sent by you' : 'Received from admin' }}</strong>
        </div>

        <div style="white-space: pre-wrap; font-size: 15px; color: #000; margin-bottom: 6px;">
            {{ $msg->message }}
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 11px; color: rgba(0, 0, 0, 0.5);">
            <span>{{ $msg->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            @if($isSent)
                @if($msg->is_read)
                    <span style="color: #34B7F1;">&#10003;&#10003;</span>
                @else
                    <span style="color: gray;">&#10003;</span>
                @endif
            @endif
        </div>
    </div>
</div>

       
@endforeach

            @endif
        </div>

        {{-- Message Input Form (Sticky) --}}
        <form action="{{ route('institute.message.store') }}" method="POST" style="padding: 10px; border-top: 1px solid #ccc; background: #fff;">
            @csrf
          <div style="display: flex; gap: 10px; align-items: center;">
    <textarea name="message" id="message" required
        style="flex: 1; resize: none; font-size: 15px; padding: 10px 15px; border-radius: 20px; border: 1px solid #ccc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; height: 45px; overflow-y: auto;"
        placeholder="Type your message...">{{ old('message') }}</textarea>

    <button type="submit" class="btn btn-success"
        style="border-radius: 20px; padding: 10px 20px; font-weight: 600; white-space: nowrap;">
        Send
    </button>
</div>

            
        </form>
    </div>
</div>


<style>
.sent-message {
    background-color: #dcf8c6;
    color: #000;
    padding: 1px 6px;
    border-radius: 12px 12px 0 12px;
    max-width: 75%;
    word-wrap: break-word;
    word-break: break-word;
    font-size: 11px;
    line-height: 1.05;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    text-align: left; /* 👈 ensures text starts from left */
}

.received-message {
    background-color: #ffffff;
    color: #000;
    padding: 4px 8px;
    border-radius: 12px 12px 12px 0;
    max-width: 75%;
    word-wrap: break-word;
    word-break: break-word;
    font-size: 11px;
    line-height: 1.05;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    text-align: left; /* 👈 ensures text starts from left */
}


    #chat-box::-webkit-scrollbar {
        width: 8px;
    }

    #chat-box::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .sent-message, .received-message {
            max-width: 90%;
        }
    }
</style>

<script>
    // Auto scroll to bottom
    window.onload = function () {
        var chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    };
</script>
@endsection
