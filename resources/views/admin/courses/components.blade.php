@extends('layouts.admin')
@section('title', 'Add Components')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold mb-0">Select Course to Add Component</h3>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark text-center text-white">
                        <tr>
                            <th style="width: 35%;">Program & Semester</th>
                            <th style="width: 15%;">Course Code</th>
                            <th style="width: 30%;">Course Title</th>
                            <th style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                {{-- Program Info --}}
                                <td>
                                    @forelse($course->programs as $program)
                                        <div class="mb-1">
                                            <span class="fw-semibold">{{ $program->name }}</span>
                                            @if($program->structure === 'semester' && $program->pivot->semester)
                                                <span class="badge bg-info ms-2">Sem {{ $program->pivot->semester }}</span>
                                            @elseif($program->structure === 'yearly' && $program->pivot->year)
                                                <span class="badge bg-warning text-dark ms-2">Year {{ $program->pivot->year }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted">No programs linked</span>
                                    @endforelse
                                </td>

                                {{-- Course Code --}}
                                <td class="text-center fw-medium">{{ $course->course_code }}</td>

                                {{-- Course Title --}}
                                <td>{{ $course->course_title }}</td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <a href="{{ route('admin.courses.component.add', $course->id) }}"
                                       class="btn btn-sm btn-success rounded-pill me-2">
                                        <i class="bi bi-plus-circle me-1"></i> Add
                                    </a>
                                    <a href="{{ route('admin.courses.component.view', $course->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye-fill me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No courses available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $courses->links('pagination::simple-bootstrap-5') }}
    </div>
</div>
@endsection
