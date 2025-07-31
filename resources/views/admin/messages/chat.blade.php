@extends('layouts.admin')
@section('title', 'Chat with ' . $institute->name)

@section('content')
<style>
    .chat-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .chat-box {
        max-height: 65vh;
        overflow-y: auto;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 12px;
        background-color: #f8f9fa;
        margin-bottom: 20px;
    }

    .chat-message {
        display: flex;
        margin-bottom: 12px;
        align-items: flex-start;
    }

    .chat-message.admin {
        justify-content: flex-end;
    }

    .chat-message.institute {
        justify-content: flex-start;
    }

    .chat-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        position: relative;
    }

    .chat-message.admin .chat-bubble {
        background-color: #d1f7c4;
        border-bottom-right-radius: 0;
        text-align: left;
    }

    .chat-message.institute .chat-bubble {
        background-color: #ffffff;
        border-bottom-left-radius: 0;
        text-align: left;
    }

    .chat-meta {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #444;
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
        font-size: 13px;
    }

    .tick.read {
        color: #34B7F1;
    }

    .tick.unread {
        color: gray;
    }

    .chat-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .chat-form textarea {
        flex: 1;
        resize: none;
    }

    .no-messages {
        text-align: center;
        color: #888;
        font-style: italic;
        padding: 20px 0;
    }

    @media (max-width: 576px) {
        .chat-bubble {
            max-width: 100%;
        }

        .chat-container {
            padding: 0 10px;
        }
    }
</style>

<div class="container chat-container">
    <h4 class="mb-4">Chat with {{ $institute->name }}</h4>

    <div class="chat-box">
        @forelse($messages as $msg)
            <div class="chat-message {{ $msg->is_admin ? 'admin' : 'institute' }}">
                <div class="chat-bubble">
                    <div class="chat-meta">{{ $msg->is_admin ? 'Admin' : 'Institute' }}</div>
                    <div>{{ $msg->message }}</div>
                    <div class="chat-time">
                        <span>{{ $msg->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                        @if($msg->is_admin)
                            {{-- Only show ticks for admin messages --}}
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
