@extends('layouts.admin')
@section('title', 'All Courses')

@section('content')
<div class="container py-4">

    {{-- Header with actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="text-primary mb-0">Courses List</h3>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.courses.components') }}" class="btn btn-warning shadow-sm">
                <i class="bi bi-plus-square me-1"></i> Add Component
            </a>
            <a href="{{ route('admin.courses.bulk.map.form') }}" class="btn btn-dark shadow-sm">
                <i class="bi bi-layers me-1"></i> Bulk Map
            </a>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Course
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle shadow-sm">
            <thead class="table-dark text-white text-center">
                <tr>
                    <th style="min-width: 180px;">Program & Semester</th>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Type</th>
                    <th>Hours</th>
                    <th>Attend.</th>
                    <th>Internal</th>
                    <th>External</th>
                    <th>Credit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>
                            @forelse($course->programs as $program)
                                <div class="mb-1">
                                    <span class="fw-semibold text-dark">{{ $program->name }}</span>
                                    @if($program->structure === 'semester' && $program->pivot->semester)
                                        <span class="badge bg-info ms-1">Sem {{ $program->pivot->semester }}</span>
                                    @elseif($program->structure === 'yearly' && $program->pivot->year)
                                        <span class="badge bg-warning ms-1">Year {{ $program->pivot->year }}</span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted fst-italic">No Program</span>
                            @endforelse
                        </td>
                        <td class="text-center">{{ $course->course_code ?? '-' }}</td>
                        <td>{{ $course->course_title ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $course->type == 'Theory' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $course->type }}
                            </span>
                        </td>
                        <td class="text-center">{{ $course->credit_hours }}</td>
                        <td class="text-center">
                            <span class="badge {{ $course->has_attendance ? 'bg-success' : 'bg-secondary' }}">
                                {{ $course->has_attendance ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $course->has_internal ? 'bg-success' : 'bg-secondary' }}">
                                {{ $course->has_internal ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $course->has_external ? 'bg-success' : 'bg-secondary' }}">
                                {{ $course->has_external ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="text-center">{{ $course->credit_value }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center flex-wrap gap-1">
                                <a href="{{ route('admin.courses.mapping', ['course_id' => $course->id]) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-link"></i> Map
                                </a>
                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Delete this course?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $courses->links('pagination::simple-bootstrap-5') }}
    </div>

</div>
@endsection
