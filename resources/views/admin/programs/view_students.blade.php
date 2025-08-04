@extends('layouts.admin')

@section('title', 'Enrolled Students')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- 🔷 Page Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-primary mb-1">Enrolled Students</h3>
                <p class="mb-0 text-muted fs-6">
                    Students enrolled in <strong class="text-dark">{{ $program->name }}</strong>.
                </p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('admin.programs.view_students', $program->id) }}" class="d-flex align-items-center">
                    <label class="me-2 fw-semibold">Semester:</label>
                    <select name="semester" class="form-select form-select-sm me-2">
                        <option value="all" {{ request('semester') == 'all' ? 'selected' : '' }}>All</option>
                        @foreach($availableSemesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
                <a href="{{ route('admin.programs.students.export', $program->id) }}"
                   class="btn btn-outline-success d-inline-flex align-items-center gap-2 rounded-pill shadow-sm"
                   style="font-weight: 600; letter-spacing: 0.4px;">
                    <i class="bi bi-download fs-5"></i> Export
                </a>
            </div>
        </div>
    </div>

    {{-- 🔁 Filter & Table --}}
    <form method="GET" action="{{ route('admin.programs.view_students', $program->id) }}">

        @if($students->count())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark align-middle">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>NCHM Roll No</th>
                                <th>Enrolment No</th>
                                <th>Institute</th>
                                <th>Academic Year</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Category</th>
                                <th>Father Name</th>
                                <th>Status</th>
                                <th>Courses</th>
                            </tr>
                            <tr>
                                <td></td>
                                @php
                                    $filters = [
                                        'name', 'nchm_roll_number', 'enrolment_number', 'institute', 'academic_year',
                                        'email', 'mobile', 'category', 'father_name', 'status', 'course'
                                    ];
                                @endphp
                                @foreach($filters as $field)
                                    <td class="position-relative">
                                        @if($field === 'status')
                                            <select name="{{ $field }}" class="form-select form-select-sm">
                                                <option value="">All</option>
                                                <option value="1" {{ request($field) == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ request($field) == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field }}" value="{{ request($field) }}" class="form-control form-control-sm" placeholder="Filter...">
                                        @endif
                                        @if(request($field))
                                            <button type="button" class="btn btn-sm btn-link position-absolute top-0 end-0 p-0 me-1 mt-1 text-danger clear-filter" data-field="{{ $field }}">×</button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            @foreach($students as $stu)
                                <tr>
                                    <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                    <td>{{ $stu->name }}</td>
                                    <td>{{ $stu->nchm_roll_number }}</td>
                                    <td>{{ $stu->enrolment_number }}</td>
                                    <td>{{ $stu->institute->name ?? 'N/A' }}</td>
                                    <td>{{ $stu->academicSession ? $stu->academicSession->year . ' (' . $stu->academicSession->term . ')' : 'N/A' }}</td>
                                    <td>{{ $stu->email }}</td>
                                    <td>{{ $stu->mobile }}</td>
                                    <td>{{ $stu->category }}</td>
                                    <td>{{ $stu->father_name }}</td>
                                    <td>
                                        <span class="badge {{ $stu->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $stu->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $studentCourseIds = $stu->courses->pluck('id')->toArray(); @endphp
                                        @if(isset($mappedCourses) && $mappedCourses->count())
                                            <ul class="mb-0 ps-3 small">
                                                @foreach($mappedCourses as $course)
                                                    <li>
                                                        {{ $course->course_code }} - {{ $course->course_title }}
                                                        <span class="badge {{ in_array($course->id, $studentCourseIds) ? 'bg-success' : 'bg-secondary' }} ms-1">
                                                            {{ in_array($course->id, $studentCourseIds) ? 'Assigned' : 'Not Assigned' }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">No mapped courses.</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                             </div> {{-- Close .table-responsive --}}

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

                {{-- 🔁 Filter Footer --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <a href="{{ route('admin.programs.view_students', $program->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        Reset Filters
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">Apply Filters</button>
                </div>
            </div> {{-- ✅ Close the .card --}}
        @endif
    </form>
</div>
@endsection


@push('scripts')
<script>
    document.querySelectorAll('thead input, thead select, select[name="semester"]').forEach(input => {
        input.addEventListener('change', () => {
            input.closest('form').submit();
        });
    });

    document.querySelectorAll('.clear-filter').forEach(btn => {
        btn.addEventListener('click', function () {
            const field = this.getAttribute('data-field');
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.value = '';
                this.closest('form').submit();
            }
        });
    });
</script>
@endpush
