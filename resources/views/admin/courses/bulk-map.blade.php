@extends('layouts.admin')
@section('title', 'Bulk Map Courses')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h3 class="mb-4 text-primary fw-bold">Bulk Map Courses to Programs</h3>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.courses.bulk.map.store') }}" method="POST">
                @csrf

                {{-- Program Select --}}
                <div class="mb-4">
                    <label for="program_id" class="form-label fw-semibold">Select Program</label>
                    <select name="program_id" id="program_id" class="form-select rounded-3 shadow-sm" required>
                        <option value="">-- Select Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" data-structure="{{ $program->structure }}">
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Courses Checkbox List --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Courses to Map</label>
                    <div class="border rounded-3 shadow-sm p-3" style="max-height: 300px; overflow-y: auto;">
                        @foreach($courses as $course)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="courses[]" value="{{ $course->id }}" id="course_{{ $course->id }}">
                                <label class="form-check-label" for="course_{{ $course->id }}">
                                    {{ $course->course_code }} - {{ $course->course_title }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Semester / Year Input --}}
                <div class="row mb-4">
                    <div class="col-md-6" id="semesterInput" style="display: none;">
                        <label class="form-label fw-semibold">Semester</label>
                        <input type="number" name="semester" class="form-control rounded-3 shadow-sm" min="1" max="10">
                    </div>

                    <div class="col-md-6" id="yearInput" style="display: none;">
                        <label class="form-label fw-semibold">Year</label>
                        <input type="number" name="year" class="form-control rounded-3 shadow-sm" min="1" max="6">
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-success px-4 rounded-pill">
                    <i class="bi bi-link-45deg me-1"></i> Map Selected Courses
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const programSelect = document.getElementById('program_id');
    const semesterInput = document.getElementById('semesterInput');
    const yearInput = document.getElementById('yearInput');

    programSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const structure = selectedOption.dataset.structure;

        semesterInput.style.display = 'none';
        yearInput.style.display = 'none';

        if (structure === 'semester') {
            semesterInput.style.display = 'block';
        } else if (structure === 'yearly') {
            yearInput.style.display = 'block';
        }
    });
});
</script>
