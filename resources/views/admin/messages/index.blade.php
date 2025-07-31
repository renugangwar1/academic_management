@extends('layouts.admin')
@section('title', 'Institute Messages')

@section('content')
<style>
    .message-container {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .message-header {
        font-weight: bold;
        font-size: 1.5rem;
        margin-bottom: 25px;
        color: #333;
    }

    .list-group-item-action {
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .list-group-item-action:hover {
        background-color: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .message-snippet {
        color: #6c757d;
    }

    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        margin-left: 5px;
    }

    .no-messages {
        text-align: center;
        color: #999;
        padding: 30px 0;
        font-style: italic;
    }
</style>

<div class="container py-4">
    <div class="message-container">
        <div class="message-header">📨 Messages from Institutes</div>

        @if($institutes->count() > 0)
            <div class="list-group">
                @foreach($institutes as $institute)
                    @php
                        $latestMessage = $institute->messages->first();
                        $unreadCount = $institute->messages->where('is_read', false)->count();
                        $newMessages = $institute->messages->where('created_at', '>=', now()->subHours(24))->count();
                    @endphp
                    @if($latestMessage)
                        <a href="{{ route('admin.messages.chat', $institute->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div><strong>{{ $institute->name }}</strong></div>
                                <div class="message-snippet">{{ Str::limit($latestMessage->message, 60) }}</div>
                            </div>
                            <div>
                                @if($newMessages > 0)
                                    <span class="badge bg-primary">New</span>
                                @endif
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger">{{ $unreadCount }}</span>
                                @endif
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="no-messages">No messages from any institute yet.</div>
        @endif
    </div>
</div>
@endsection
