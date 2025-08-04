@extends('layouts.admin')

@section('title', 'Regular Session Details')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- 🔷 Page Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-primary mb-1">Regular Session</h3>
                <p class="text-muted mb-0 fs-6">
                    Academic Year: <strong>{{ $academic_session->year }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.academic_sessions.mapPrograms', ['session' => $academic_session->id, 'type' => 'regular']) }}"
               class="btn btn-primary shadow-sm">
                <i class="bi bi-diagram-3-fill me-1"></i> Map Programs
            </a>
        </div>
    </div>

    {{-- 🔶 Session Status --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <p class="fs-5 mb-0">
                <strong>Status:</strong>
                @if($academic_session->active)
                    <span class="badge bg-success px-3 py-2 rounded-pill">Active</span>
                @else
                    <span class="badge bg-secondary px-3 py-2 rounded-pill">Inactive</span>
                @endif
            </p>
        </div>
    </div>

    {{-- 🔷 Mapped Programs --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h5 class="fw-bold text-dark mb-3">
                Mapped Programs <small class="text-muted fw-normal">(Semester Based)</small>
            </h5>

            @forelse ($programs as $program)
                <div class="card mb-3 shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">{{ $program->name }}</h6>

                        <ul class="list-unstyled text-muted mb-0 small">
                            <li><strong>Structure:</strong> {{ ucfirst($program->pivot->structure) }}</li>

                            @if($program->pivot->semester)
                                <li><strong>Semester:</strong> {{ $program->pivot->semester }}</li>
                            @endif

                            @if($program->pivot->start_level)
                                <li>
                                    <strong>Start {{ $program->pivot->structure === 'semester' ? 'Semester' : 'Year' }}:</strong>
                                    {{ $program->pivot->start_level }}
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @empty
                <div class="alert alert-info shadow-sm rounded-3 mb-0">
                    No semester-based programs mapped yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
