@extends('layouts.admin')

@section('title', 'Regular Session Details')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 text-primary">Regular Session</h3>
            <h5><strong>{{ $academic_session->year }}</strong></h5>
        </div>

        <a href="{{ route('admin.academic_sessions.mapPrograms', ['session' => $academic_session->id, 'type' => 'regular']) }}"
           class="btn btn-primary shadow-sm">
            <i class="bi bi-diagram-3-fill me-1"></i> Map Programs
        </a>
    </div>

    <div class="mb-4">
        <p class="fs-5">
            <strong>Status:</strong> 
            @if($academic_session->active)
                <span class="badge bg-success px-3 py-2">Active</span>
            @else
                <span class="badge bg-secondary px-3 py-2">Inactive</span>
            @endif
        </p>
    </div>

    {{-- Mapped Programs --}}
    <div class="mb-4">
        <h4 class="mb-3 text-dark">Mapped Programs <small class="text-muted">(Semester Based)</small></h4>

       @forelse ($programs as $program)
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title">{{ $program->name }}</h5>

            <p class="text-muted mb-1">
                Structure: <strong>{{ ucfirst($program->pivot->structure) }}</strong>
            </p>

            @if($program->pivot->semester)
                <p class="text-muted mb-1">
                    Semester: <strong>{{ $program->pivot->semester }}</strong>
                </p>
            @endif

            @if($program->pivot->start_level)
                <p class="text-muted mb-0">
                    Start {{ $program->pivot->structure === 'semester' ? 'Semester' : 'Year' }}:
                    <strong>{{ $program->pivot->start_level }}</strong>
                </p>
            @endif

        </div>
    </div>
@empty
    <div class="alert alert-info shadow-sm">No semester-based programs mapped yet.</div>
@endforelse

    </div>
</div>
@endsection
