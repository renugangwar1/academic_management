@extends('layouts.admin')
@section('title', 'Institute Messages')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header Card --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="fw-bold text-primary mb-1">📨 Messages from Institutes</h3>
                <p class="text-muted small mb-0">View latest messages and manage institute communication.</p>
            </div>
        </div>
    </div>

    {{-- Messages List --}}
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
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4 message-item">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $institute->name }}</div>
                                    <div class="text-muted small message-snippet">{{ Str::limit($latestMessage->message, 70) }}</div>
                                </div>
                                <div class="text-end">
                                    @if($newMessages > 0)
                                        <span class="badge bg-primary rounded-pill me-1">New</span>
                                    @endif
                                    @if($unreadCount > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="p-5 text-center text-muted fst-italic">
                    No messages from any institute yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .message-item {
        transition: all 0.3s ease-in-out;
    }

    .message-item:hover {
        background-color: #f9f9f9;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.02);
    }

    .message-snippet {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.7rem;
    }

    @media (max-width: 767px) {
        .message-item {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.5rem;
        }

        .text-end {
            align-self: flex-end;
        }
    }
</style>
@endpush
