@extends('layouts.admin')
@section('title', 'Define Course Components')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h3 class="text-primary fw-bold">
            Add Components for 
            <span class="text-dark">{{ $course->course_title }}</span> 
            <small class="text-muted">({{ $course->course_code }})</small>
        </h3>
    </div>

    {{-- Form Card --}}
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('admin.courses.component.save', $course->id) }}" method="POST">
                @csrf

                {{-- Internal --}}
                @if($course->has_internal)
                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label fw-semibold">Internal Max Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="internal_max" class="form-control" required placeholder="e.g. 30">
                    </div>

                    <label class="col-sm-2 col-form-label fw-semibold">Internal Min Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="internal_min" class="form-control" required placeholder="e.g. 12">
                    </div>
                </div>
                @endif

                {{-- External --}}
                @if($course->has_external)
                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label fw-semibold">External Max Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="external_max" class="form-control" required placeholder="e.g. 70">
                    </div>

                    <label class="col-sm-2 col-form-label fw-semibold">External Min Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="external_min" class="form-control" required placeholder="e.g. 28">
                    </div>
                </div>
                @endif

                {{-- Attendance --}}
                @if($course->has_attendance)
                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label fw-semibold">Attendance Max Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="attendance_max" class="form-control" required placeholder="e.g. 5">
                    </div>

                    <label class="col-sm-2 col-form-label fw-semibold">Attendance Min Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="attendance_min" class="form-control" required placeholder="e.g. 2">
                    </div>
                </div>
                @endif

                {{-- Total & Min Passing --}}
                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label fw-semibold">Total Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="total_marks" class="form-control" required placeholder="e.g. 100">
                    </div>

                    <label class="col-sm-2 col-form-label fw-semibold">Min Passing Marks</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" name="min_passing_marks" class="form-control" required placeholder="e.g. 40">
                    </div>
                </div>

                {{-- Total From --}}
                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label fw-semibold">Total From</label>
                    <div class="col-sm-6">
                        <select name="total_from" class="form-select" required>
                            <option value="">-- Select Components --</option>

                            @if($course->has_internal && $course->has_external && $course->has_attendance)
                                <option value="internal+external+attendance">Internal + External + Attendance</option>
                            @endif
                            @if($course->has_internal && $course->has_external)
                                <option value="internal+external">Internal + External</option>
                            @endif
                            @if($course->has_external && $course->has_attendance)
                                <option value="external+attendance">External + Attendance</option>
                            @endif
                            @if($course->has_internal && $course->has_attendance)
                                <option value="internal+attendance">Internal + Attendance</option>
                            @endif
                            @if($course->has_internal)
                                <option value="internal">Internal Only</option>
                            @endif
                            @if($course->has_external)
                                <option value="external">External Only</option>
                            @endif
                            @if($course->has_attendance)
                                <option value="attendance">Attendance Only</option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-4 d-flex justify-content-start gap-3">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">
                        <i class="bi bi-check-circle me-1"></i> Save Component
                    </button>
                    <a href="{{ route('admin.courses.components') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
