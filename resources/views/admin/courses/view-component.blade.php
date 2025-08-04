@extends('layouts.admin')
@section('title', 'View Component')

@section('content')
<div class="container py-4 px-4">

    {{-- Header --}}
   <div class="card shadow-sm border-0 mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    Component Details for 
                    <span class="text-dark">{{ $course->course_title }}</span> 
                    <small class="text-muted">({{ $course->course_code }})</small>
                </h4>
                <p class="text-muted small mb-0">Below is the list of components associated with this course.</p>
            </div>
        </div>
    </div>
</div>



{{-- Component Table --}}
@if($component)
<div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
    <div class="card-body px-4 py-4">
        <h4 class="fw-semibold text-dark mb-4">
            <i class="bi bi-diagram-3 me-2 text-secondary"></i>Component Structure
        </h4>

        <table class="table table-bordered align-middle text-center shadow-sm">
            <thead class="bg-light text-dark">
                <tr>
                    <th scope="col">Component</th>
                    <th scope="col">Max Marks</th>
                    <th scope="col">Min Marks</th>
                </tr>
            </thead>
            <tbody class="bg-white">
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

                <tr class="bg-secondary text-white fw-semibold">
                    <td>Total Marks</td>
                    <td colspan="2">{{ $component->total_marks }}</td>
                </tr>

                <tr class="bg-dark text-white fw-semibold">
                    <td>Minimum Passing Marks</td>
                    <td colspan="2">{{ $component->min_passing_marks }}</td>
                </tr>
            </tbody>
        </table>

        @if($component->total_from)
        <div class="alert bg-light border-start border-4 border-dark mt-4 mb-0 rounded-3 shadow-sm">
            <i class="bi bi-calculator me-2 text-secondary"></i>
            <strong>Total Calculated From:</strong>
            {{ ucfirst(str_replace('+', ' + ', $component->total_from)) }}
        </div>
        @endif
    </div>
</div>
@else
<div class="alert alert-secondary d-flex align-items-center shadow-sm rounded-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-dark"></i>
    Component structure not defined yet for this course.
</div>
@endif

{{-- Action Buttons --}}
<div class="d-flex flex-wrap gap-3 mt-4">
    <a href="{{ route('admin.courses.components') }}" class="btn btn-outline-dark rounded-pill px-4">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    <a href="#" class="btn btn-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#copyComponentModal">
        <i class="bi bi-files me-1"></i> Copy to Other Courses
    </a>
</div>

{{-- Modal --}}
<div class="modal fade" id="copyComponentModal" tabindex="-1" aria-labelledby="copyComponentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('admin.courses.component.copy', $course->id) }}" method="POST">
        @csrf
        <div class="modal-content border-0 rounded-4 shadow bg-white">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title" id="copyComponentModalLabel">
                    <i class="bi bi-files me-2"></i>Copy Component to Other Courses
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3 text-dark">Select the courses to which you want to copy this component structure:</p>

                <div class="border rounded-3 p-3 bg-light shadow-sm" style="max-height: 300px; overflow-y: auto;">
                    @forelse(App\Models\Course::where('id', '!=', $course->id)->orderBy('course_title')->get() as $c)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_courses[]" value="{{ $c->id }}" id="course{{ $c->id }}">
                            <label class="form-check-label text-dark" for="course{{ $c->id }}">
                                {{ $c->course_title }} <span class="text-muted">({{ $c->course_code }})</span>
                            </label>
                        </div>
                    @empty
                        <p class="text-muted">No other courses available.</p>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="submit" class="btn btn-dark rounded-pill px-4">
                    <i class="bi bi-check2-circle me-1"></i> Copy Component
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
            </div>
        </div>
    </form>
  </div>
</div>



@endsection
