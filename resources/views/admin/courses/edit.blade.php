@extends('layouts.admin')
@section('title', 'Edit Course')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit Course</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Course Code</label>
                        <input type="text" name="course_code" class="form-control" required
                               value="{{ old('course_code', $course->course_code) }}" placeholder="e.g. CS101">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course Title</label>
                        <input type="text" name="course_title" class="form-control" required
                               value="{{ old('course_title', $course->course_title) }}" placeholder="e.g. Programming Basics">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="Theory" {{ old('type', $course->type) == 'Theory' ? 'selected' : '' }}>Theory</option>
                            <option value="Practical" {{ old('type', $course->type) == 'Practical' ? 'selected' : '' }}>Practical</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Credit Hours</label>
                        <input type="number" name="credit_hours" class="form-control" required
                               value="{{ old('credit_hours', $course->credit_hours) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Credit Value</label>
                        <input type="text" name="credit_value" class="form-control" required
                               value="{{ old('credit_value', $course->credit_value) }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12 d-flex align-items-center pt-2">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="checkbox" name="has_attendance" value="1" id="attendanceCheck"
                                   {{ old('has_attendance', $course->has_attendance) ? 'checked' : '' }}>
                            <label class="form-check-label" for="attendanceCheck">Has Attendance</label>
                        </div>
                        <div class="form-check me-4">
                            <input class="form-check-input" type="checkbox" name="has_internal" value="1" id="internalCheck"
                                   {{ old('has_internal', $course->has_internal) ? 'checked' : '' }}>
                            <label class="form-check-label" for="internalCheck">Has Internal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_external" value="1" id="externalCheck"
                                   {{ old('has_external', $course->has_external) ? 'checked' : '' }}>
                            <label class="form-check-label" for="externalCheck">Has External</label>
                        </div>
                    </div>
                </div>

                <!-- Program-Semester Mapping -->
                <h5 class="mb-3">Mapped Programs & Semesters</h5>
                <div id="mapping-container">
                    @forelse($course->programs as $index => $mapping)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-6">
                                <select name="program_ids[{{ $index }}][program_id]" class="form-select" required>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}"
                                            {{ $mapping->id == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="program_ids[{{ $index }}][semester]"
                                       class="form-control" placeholder="Semester" required
                                       value="{{ $mapping->pivot->semester }}">
                            </div>
                        </div>
                    @empty
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-6">
                                <select name="program_ids[0][program_id]" class="form-select" required>
                                    <option value="">-- Select Program --</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="program_ids[0][semester]" class="form-control" placeholder="Semester" required>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
