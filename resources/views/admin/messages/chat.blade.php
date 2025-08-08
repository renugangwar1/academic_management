@extends('layouts.admin')
@section('title', 'Chat with ' . $institute->name)

@section('content')
<style>
    body {
        background-color: #e5ddd5;
    }

    .chat-container {
        max-width: 900px;
        margin: 20px auto;
        background: #fefefe;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 100px);
        overflow: hidden;
    }

    .chat-header {
        background: #128C7E;
        color: #fff;
        padding: 16px 24px;
        font-size: 18px;
        font-weight: 600;
    }

    .chat-box {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background-color: #ece5dd;
    }

    .chat-message {
        display: flex;
        margin-bottom: 20px;
        align-items: flex-end;
    }

    .chat-message.admin {
        justify-content: flex-end;
    }

    .chat-message.institute {
        justify-content: flex-start;
    }

    .chat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #bbb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
        margin: 0 10px;
        flex-shrink: 0;
    }

    .chat-bubble {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 20px;
        font-size: 14px;
        line-height: 1.5;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        position: relative;
        background-color: #ffffff;
    }

    .chat-message.admin .chat-bubble {
        background-color: #dcf8c6;
        border-bottom-right-radius: 4px;
    }

    .chat-message.institute .chat-bubble {
        background-color: #ffffff;
        border-bottom-left-radius: 4px;
    }

    .chat-meta {
        font-weight: bold;
        font-size: 13px;
        color: #333;
        margin-bottom: 4px;
    }

    .chat-time {
        font-size: 11px;
        color: #777;
        margin-top: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tick {
        margin-left: 8px;
        font-size: 14px;
    }

    .tick.read {
        color: #34B7F1;
    }

    .tick.unread {
        color: gray;
    }

    .chat-form {
        padding: 15px 20px;
        background-color: #fff;
        border-top: 1px solid #ddd;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-form textarea {
        flex: 1;
        resize: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        border: 1px solid #ccc;
    }

    .chat-form textarea:focus {
        border-color: #128C7E;
        outline: none;
    }

    .chat-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
    }

    .no-messages {
        text-align: center;
        color: #777;
        font-style: italic;
        padding: 40px 0;
        font-size: 16px;
    }

    @media (max-width: 576px) {
        .chat-bubble {
            max-width: 100%;
        }

        .chat-form {
            flex-direction: column;
        }

        .chat-form textarea,
        .chat-form .btn {
            width: 100%;
        }

        .chat-container {
            border-radius: 0;
        }
    }
</style>

<div class="chat-container">
    <div class="chat-header">Chat with {{ $institute->name }}</div>

    <div class="chat-box">
        @forelse($messages as $msg)
            <div class="chat-message {{ $msg->is_admin ? 'admin' : 'institute' }}">
                <div class="chat-avatar">{{ $msg->is_admin ? 'A' : 'I' }}</div>
                <div class="chat-bubble">
                    <div class="chat-meta">{{ $msg->is_admin ? 'Admin' : 'Institute' }}</div>
                    <div style="word-wrap: break-word; white-space: pre-wrap;">{{ $msg->message }}</div>
                    <div class="chat-time">
                        <span>{{ $msg->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                        @if($msg->is_admin)
                            @if($msg->is_read)
                                <span class="tick read">&#10003;&#10003;</span>
                            @else
                                <span class="tick unread">&#10003;</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="no-messages">No messages yet.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.messages.reply', $institute->id) }}" class="chat-form">
        @csrf
        <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required></textarea>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const chatBox = document.querySelector('.chat-box');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endpush
