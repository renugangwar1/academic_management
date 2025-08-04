@extends('layouts.admin')
@section('title', 'Program Info')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- 🔷 Page Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-primary mb-1">Program Info</h3>
               
            </div>
        </div>
   

        <div class="card-body">
            <div class="mb-3">
                <strong>Name:</strong>
                <div class="text-muted">{{ $program->name }}</div>
            </div>

            <div class="mb-3">
                <strong>Duration:</strong>
                <div class="text-muted">{{ $program->duration }} {{ ucfirst($program->duration_unit) }}</div>
            </div>

            <div class="mb-3">
                <strong>Structure:</strong>
                <div class="text-muted">
                    @switch($program->structure)
                        @case('semester')
                            Semester-wise
                            @break
                        @case('yearly')
                            Yearly
                            @break
                        @case('short_term')
                            Short Course
                            @break
                        @default
                            N/A
                    @endswitch
                </div>
            </div>

            @if($program->structure === 'semester')
                <div class="mb-3">
                    <strong>Total Semesters:</strong>
                    <div class="text-muted">{{ $program->duration * 2 }}</div>
                </div>
            @elseif($program->structure === 'yearly')
                <div class="mb-3">
                    <strong>Total Years:</strong>
                    <div class="text-muted">{{ $program->duration }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">Back to Programs</a>
    </div>
</div>
@endsection
