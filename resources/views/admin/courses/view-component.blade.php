@extends('layouts.admin')
@section('title', 'View Component')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h3 class="text-primary">
            Component Details for 
            <span class="text-dark">{{ $course->course_title }}</span> 
            <small class="text-muted">({{ $course->course_code }})</small>
        </h3>
    </div>

    {{-- Component Table --}}
    @if($component)
    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th>Component</th>
                        <th>Max Marks</th>
                        <th>Min Marks</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @if($course->has_internal)
                    <tr>
                        <td>Internal</td>
                        <td>{{ $component->internal_max }}</td>
                        <td>{{ $component->internal_min }}</td>
                    </tr>
                    @endif

                    @if($course->has_external)
                    <tr>
                        <td>External</td>
                        <td>{{ $component->external_max }}</td>
                        <td>{{ $component->external_min }}</td>
                    </tr>
                    @endif

                    @if($course->has_attendance)
                    <tr>
                        <td>Attendance</td>
                        <td>{{ $component->attendance_max }}</td>
                        <td>{{ $component->attendance_min }}</td>
                    </tr>
                    @endif

                    <tr class="table-info fw-semibold">
                        <td>Total Marks</td>
                        <td colspan="2">{{ $component->total_marks }}</td>
                    </tr>

                    <tr class="table-warning fw-semibold">
                        <td>Minimum Passing Marks</td>
                        <td colspan="2">{{ $component->min_passing_marks }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Total From --}}
            @if($component->total_from)
            <div class="alert alert-info mt-4 mb-0">
                <strong>Total Calculated From:</strong> 
                {{ ucfirst(str_replace('+', ' + ', $component->total_from)) }}
            </div>
            @endif
        </div>
    </div>
    @else
        <div class="alert alert-warning">Component structure not defined yet for this course.</div>
    @endif

    {{-- Action Buttons --}}
    <div class="d-flex gap-3 mt-3">
        <a href="{{ route('admin.courses.components') }}" class="btn btn-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="#" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#copyComponentModal">
            <i class="bi bi-files"></i> Copy Component to Other Courses
        </a>
    </div>

</div>

{{-- Modal --}}
<div class="modal fade" id="copyComponentModal" tabindex="-1" aria-labelledby="copyComponentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('admin.courses.component.copy', $course->id) }}" method="POST">
        @csrf
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="copyComponentModalLabel">Copy Component to Other Courses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">Select courses to which you want to copy the current component structure:</p>
                
                <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                    @forelse(App\Models\Course::where('id', '!=', $course->id)->orderBy('course_title')->get() as $c)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_courses[]" value="{{ $c->id }}" id="course{{ $c->id }}">
                            <label class="form-check-label" for="course{{ $c->id }}">
                                {{ $c->course_title }} ({{ $c->course_code }})
                            </label>
                        </div>
                    @empty
                        <p class="text-muted">No other courses available.</p>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Copy Component</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>
@endsection
