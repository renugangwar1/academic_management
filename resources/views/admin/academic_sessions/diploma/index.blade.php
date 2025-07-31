@extends('layouts.admin')
@section('title','Diploma/Certificate Sessions')

@section('content')
<div class="container">
    <h4 class="mb-3">Diploma/Certificate Academic Sessions</h4>

    @forelse ($sessions as $session)
        <div class="card mb-3 p-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
               <div class="mb-2">
    <strong>
        ID:{{ $session->id }} - {{ $session->year }} – {{ $session->term }} ({{ ucfirst($session->odd_even) }})
        @if($session->diploma_year)
            – Year {{ $session->diploma_year }}
        @endif
    </strong>
    <span class="badge bg-{{ $session->active ? 'success' : 'secondary' }} ms-2">
        {{ $session->active ? 'Active' : 'Inactive' }}
    </span>
    <br>
    <small class="text-muted">Created at: {{ $session->created_at->format('d M Y, h:i A') }}</small>
</div>


                <div class="d-flex flex-wrap gap-2">
                    {{-- Details --}}
                    <a href="{{ route('admin.academic_sessions.diploma.show', $session->id) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> Details
                    </a>

                    {{-- Edit --}}
                    <a href="{{ route('admin.academic_sessions.edit', $session->id) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>

                    {{-- Map Programs --}}
                    <a href="{{ route('admin.academic_sessions.mapPrograms', ['session' => $session->id, 'type' => 'diploma']) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-link-45deg me-1"></i> Map Programs
                    </a>

                    {{-- Delete --}}
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
        <p>No diploma/certificate sessions found.</p>
    @endforelse
</div>
@endsection
