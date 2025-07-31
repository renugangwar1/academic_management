@extends('layouts.admin')
@section('title','Regular Sessions')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm border-0 mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div>
                <h4 class="fw-bold text-primary mb-1">Regular Academic Sessions</h4>
                <p class="text-muted small mb-0">Below is the list of academic sessions for regular programmes.</p>
            </div>
        </div>
    </div>
</div>


    @forelse ($sessions as $session)
        <div class="card mb-3 p-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div class="mb-2">
                    <strong>
                        ID:{{ $session->id }} - {{ $session->year }} – {{ $session->term }} ({{ ucfirst($session->odd_even) }})
                    </strong>
                    <span class="badge bg-{{ $session->active ? 'success' : 'secondary' }} ms-2">
                        {{ $session->active ? 'Active' : 'Inactive' }}
                    </span>
                    <br>
                    <small class="text-muted">Created at: {{ $session->created_at->format('d M Y, h:i A') }}</small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.academic_sessions.regular.show', $session->id) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> Details
                    </a>

                    <a href="{{ route('admin.academic_sessions.edit', $session->id) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>

                    <a href="{{ route('admin.academic_sessions.mapPrograms', ['session' => $session->id, 'type' => 'regular']) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-link-45deg me-1"></i> Map Programs
                    </a>

                    <form method="POST"
                          action="{{ route('admin.academic_sessions.destroy', $session->id) }}"
                          onsubmit="return confirm('Are you sure you want to delete this session?');"
                          class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p>No regular sessions found.</p>
    @endforelse
</div>
@endsection
