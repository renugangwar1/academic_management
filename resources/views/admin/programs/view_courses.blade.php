@extends('layouts.admin')
@section('title', 'Mapped Courses')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">
        Mapped Courses for Program: <strong>{{ $program->name }}</strong>
    </h4>

    @if($coursesBySemester->isNotEmpty())
        <div class="row">
            @foreach($coursesBySemester as $semOrYear => $courses)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                @if($program->structure === 'semester')
                                    Semester {{ $semOrYear ?? 'N/A' }}
                                @elseif($program->structure === 'yearly')
                                    Year {{ $semOrYear ?? 'N/A' }}
                                @else
                                    {{ $semOrYear ?? 'N/A' }}
                                @endif
                            </h5>
                            <span class="badge bg-light text-dark">{{ $courses->count() }} courses</span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($courses as $course)
                                <li class="list-group-item">
                                    <strong>{{ $course->course_code }}</strong>: {{ $course->course_title }}
                                    <br>
                                    <small class="text-muted">
                                        Type: {{ ucfirst($course->type) }} |
                                        Credits: {{ $course->credit_value }} ({{ $course->credit_hours }} hrs)
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">No courses mapped for this program.</p>
    @endif
</div>
@endsection
