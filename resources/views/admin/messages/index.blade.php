@extends('layouts.admin')
@section('title', 'Institute Messages')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row g-4">

        {{-- Left Section: Institute Messages --}}
      
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-light-subtle">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold text-primary mb-1">📨 Institute Messages</h2>
                        <p class="text-secondary mb-0">
                            Check the latest messages and manage institute communication efficiently.
                        </p>
                    </div>

                    <!-- Button aligned to right -->
                    <div>
                        <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                            <i class="fas fa-paper-plane me-1"></i> New Message
                        </button>
                    </div>
                </div>
            </div>
        
            </div>
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        @if($institutes->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($institutes as $institute)
                    @php
                        $latestMessage = $institute->messages->first();
                        $unreadCount = $institute->messages->where('is_read', false)->count();
                        $newMessages = $institute->messages->where('created_at', '>=', now()->subHours(24))->count();
                    @endphp

                    @if($latestMessage)
                        <a href="{{ route('admin.messages.chat', $institute->id) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-4 py-3 message-item">

                            <div class="d-flex align-items-center gap-3">
                                {{-- Avatar with first letter --}}
                                <div class="chat-avatar">
                                    {{ strtoupper(substr($institute->name, 0, 1)) }}
                                </div>

                                <div>
                                    <div class="fw-semibold fs-5 text-dark mb-1">{{ $institute->name }}</div>
                                    <div class="text-muted small message-snippet">
                                        {{ Str::limit($latestMessage->message, 70) }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-end d-flex flex-column align-items-end gap-1">
                                @if($newMessages > 0)
                                    <span class="badge bg-gradient bg-success shadow-sm">New</span>
                                @endif
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger shadow-sm">{{ $unreadCount }} unread</span>
                                @endif
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="p-5 text-center text-muted fst-italic">
                🚫 No messages from any institute yet.
            </div>
        @endif
    </div>
</div>

{{-- Add avatar styling --}}
@push('styles')
<style>
    .chat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #128C7E;
        color: white;
        font-weight: bold;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endpush


       {{-- Right Section: Send Message --}}


<!-- Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="sendMessageModalLabel">Send Message to Institute</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.messages.send') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Institute Selection --}}
                    <div class="mb-3">
                        <label for="institute_id" class="form-label fw-semibold">Select Institute</label>
                        <select name="institute_id" id="institute_id" class="form-select" required>
                            <option value="">-- Choose Institute --</option>
                            @foreach($allInstitutes as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Message Box --}}
                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold">Your Message</label>
                        <textarea name="message" id="message" class="form-control" rows="4" placeholder="Type your message..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .message-item {
        transition: all 0.25s ease-in-out;
        border-bottom: 1px solid #f1f1f1;
        border-left: 4px solid transparent;
    }
    .message-item:hover {
        background-color: #fdfdfd;
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.03);
    }
    .message-snippet {
        color: #6c757d;
        font-size: 0.9rem;
        max-width: 350px;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
        border-radius: 50rem;
    }
    .badge.bg-gradient.bg-success {
        background-image: linear-gradient(45deg, #00c851, #007E33);
        color: white;
    }
    @media (max-width: 991px) {
        .message-snippet {
            max-width: 100%;
        }
    }
</style>
@endpush
