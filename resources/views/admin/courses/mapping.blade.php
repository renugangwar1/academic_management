@extends('layouts.admin')
@section('title', 'Map Course to Program')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Map Course to Program & Semester</h4>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="card-body">
            <form action="{{ route('admin.courses.mapping.store') }}" method="POST">
                @csrf

                {{-- Course Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Courses</label>

                    @if(isset($selectedCourse))
                        {{-- Individual Course Display --}}
                        <div class="card border-primary mb-3 shadow-sm">
                            <div class="card-body py-2">
                                <input type="hidden" name="course_ids[]" value="{{ $selectedCourse->id }}">
                                <h5 class="mb-0 text-primary">
                                    <i class="bi bi-bookmark-check me-1"></i>
                                    {{ $selectedCourse->course_code }} - {{ $selectedCourse->course_title }}
                                </h5>
                                <p class="mb-0 mt-1 small text-muted">
                                    Type: <strong>{{ $selectedCourse->type }}</strong> |
                                    Credit Hours: <strong>{{ $selectedCourse->credit_hours }}</strong> |
                                    Credit Value: <strong>{{ $selectedCourse->credit_value }}</strong>
                                </p>
                            </div>
                        </div>
                    @else
                        {{-- Bulk Mapping with Select All --}}
                        <div class="mb-2">
                            <input type="checkbox" id="selectAllCheckbox"> <strong>Select All</strong>
                        </div>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @forelse($courses as $course)
                                <div class="form-check">
                                    <input class="form-check-input course-checkbox" type="checkbox" name="course_ids[]" value="{{ $course->id }}" id="course{{ $course->id }}">
                                    <label class="form-check-label" for="course{{ $course->id }}">
                                        {{ $course->course_code }} - {{ $course->course_title }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted">No courses available for mapping.</p>
                            @endforelse
                        </div>
                    @endif
                </div>

              

  <div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Program</label>
        <select name="program_id" class="form-select" required>
            <option value="">-- Select Program --</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}">{{ $program->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4" id="yearInput" style="display: none;">
        <label class="form-label fw-semibold">Year</label>
        <input type="number" name="year" class="form-control" placeholder="e.g. 1">
    </div>

    <div class="col-md-4" id="semesterInput" style="display: none;">
        <label class="form-label fw-semibold">Semester</label>
        <input type="number" name="semester" class="form-control" placeholder="e.g. 1">
    </div>


                </div>

                <div class="text-end">
                    <button class="btn btn-primary">
                        <i class="bi bi-link-45deg me-1"></i> Map Courses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- remove the condition – the script must always load --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const programStructures = @json($programs->pluck('structure', 'id'));

    // Select‑all checkbox (bulk page only)
    document.getElementById('selectAllCheckbox')?.addEventListener('change', e => {
        document.querySelectorAll('.course-checkbox').forEach(cb =>
            cb.checked = e.target.checked
        );
    });

    // Show / hide Year & Semester
    document.querySelector('select[name="program_id"]').addEventListener('change', function () {
        const structure = programStructures[this.value] || null;

        // hide both
        document.getElementById('semesterInput').style.display = 'none';
        document.getElementById('yearInput').style.display     = 'none';

        if (structure === 'semester') {
            document.getElementById('semesterInput').style.display = 'block';
        } else if (structure === 'yearly') {
            document.getElementById('yearInput').style.display = 'block';
        }
    });
});
</script>





@endsection
