@extends('layouts.admin')
@section('title', 'Enrolled Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Students Enrolled in {{ $program->name }}</h4>

    <form method="GET" action="{{ route('admin.programs.view_students', $program->id) }}" class="d-flex align-items-center">
        <label class="me-2 fw-bold">Semester:</label>
        <select name="semester" class="form-select me-2" onchange="this.form.submit()">
            <option value="all" {{ request('semester') == 'all' ? 'selected' : '' }}>All</option>
            @foreach($availableSemesters as $sem)
                <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>
                    Semester {{ $sem }}
                </option>
            @endforeach
        </select>
    </form>

    <a href="{{ route('admin.programs.students.export', $program->id) }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Download Excel
    </a>
</div>

@if($students->count())
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>NCHM Roll No</th>
                    <th>Enrolment No</th>
                    <th>Institute</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Category</th>
                    <th>Father Name</th>
                    <th>Status</th>
                    <th>Courses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $stu)
                    <tr>
                        <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                        <td>{{ $stu->name }}</td>
                        <td>{{ $stu->nchm_roll_number }}</td>
                        <td>{{ $stu->enrolment_number }}</td>
                        <td>{{ $stu->institute->name ?? 'N/A' }}</td>
                        <td>{{ $stu->email }}</td>
                        <td>{{ $stu->mobile }}</td>
                        <td>{{ $stu->category }}</td>
                        <td>{{ $stu->father_name }}</td>
                        <td>
                            @if($stu->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                       <td>
    @php
        $studentCourseIds = $stu->courses->pluck('id')->toArray();
    @endphp

    @if(isset($mappedCourses) && $mappedCourses->count())
        <ul class="mb-0 ps-3">
            @foreach($mappedCourses as $course)
                <li>
                    {{ $course->course_code }} - {{ $course->course_title }}
                    @if(in_array($course->id, $studentCourseIds))
                        <span class="badge bg-success ms-1">Assigned</span>
                    @else
                        <span class="badge bg-secondary ms-1">Not Assigned</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <span class="text-muted">No courses mapped for this semester.</span>
    @endif
</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Custom Styled Pagination --}}
    @if ($students->hasPages())
        <div class="d-flex justify-content-center mt-3">
            <nav>
                <ul class="pagination mb-0">
                    {{-- Previous --}}
                    @if ($students->onFirstPage())
                        <li class="page-item disabled"><span class="page-link rounded-pill">&lt;</span></li>
                    @else
                        <li class="page-item"><a class="page-link rounded-pill" href="{{ $students->previousPageUrl() }}" rel="prev">&lt;</a></li>
                    @endif

                    {{-- Page Range --}}
                    @php
                        $start = max($students->currentPage() - 2, 1);
                        $end = min($students->lastPage(), $students->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link rounded-pill" href="{{ $students->url(1) }}">1</a></li>
                        @if ($start > 2)
                            <li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>
                        @endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $students->currentPage())
                            <li class="page-item active"><span class="page-link rounded-pill bg-primary border-primary">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link rounded-pill" href="{{ $students->url($page) }}">{{ $page }}</a></li>
                        @endif
                    @endfor

                    @if ($end < $students->lastPage())
                        @if ($end < $students->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>
                        @endif
                        <li class="page-item"><a class="page-link rounded-pill" href="{{ $students->url($students->lastPage()) }}">{{ $students->lastPage() }}</a></li>
                    @endif

                    {{-- Next --}}
                    @if ($students->hasMorePages())
                        <li class="page-item"><a class="page-link rounded-pill" href="{{ $students->nextPageUrl() }}" rel="next">&gt;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link rounded-pill">&gt;</span></li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif

@else
    <p class="text-muted">No students enrolled.</p>
@endif
@endsection
