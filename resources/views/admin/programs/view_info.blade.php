@extends('layouts.admin')
@section('title', 'Program Info')

@section('content')
<div class="container py-4">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Program Information</h5>
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
