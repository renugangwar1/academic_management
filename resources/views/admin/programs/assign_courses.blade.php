@extends('layouts.admin')
@section('title', 'Assign Courses to Students')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- 🔷 Page Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-primary mb-1">Assign Courses</h3>
                <p class="mb-0 text-muted fs-6">
                    Configure and manage course assignments for 
                    <strong class="text-dark">{{ $program->name }}</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Excel Import Section --}}
    <div class="card mb-4 shadow-sm rounded-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="mb-0 fw-semibold">Bulk Import Assignments</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.programs.import.assigned.courses', $program->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Upload Excel File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">
                            <i class="bi bi-upload me-1"></i> Import Assignments
                        </button>
                        <a href="{{ route('admin.programs.template.assigned.courses', $program->id) }}" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="bi bi-download me-1"></i> Download Template
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Manual Assignment Section --}}
    <form method="POST" action="{{ route('admin.programs.save.assigned.courses', $program->id) }}">
        @csrf

        @foreach($students as $semester => $group)
            <div class="card mb-4 shadow-sm border-0 rounded-4">
                <div class="card-header bg-secondary text-white rounded-top-4">
                    <h5 class="mb-0 fw-semibold">Semester {{ $semester }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light sticky-top shadow-sm">
                            <tr>
                                <th style="width: 50px">#</th>
                                <th>Name</th>
                                <th>NCHM Roll No</th>
                                <th>Assign Courses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">{{ $student->name }}</td>
                                    <td><span class="badge bg-primary">{{ $student->nchm_roll_number }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($program->courses as $course)
                                                @if($course->pivot->semester == $semester)
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="courses[{{ $student->id }}][]"
                                                            value="{{ $course->id }}"
                                                            id="student{{ $student->id }}_course{{ $course->id }}"
                                                            {{ $student->courses->contains($course->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="student{{ $student->id }}_course{{ $course->id }}">
                                                            {{ $course->course_code }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

             {{-- Pagination --}}
@if ($group->hasPages())
    <div class="d-flex justify-content-center mt-3">
        <nav>
            <ul class="pagination mb-0">

                {{-- Previous --}}
                @if ($group->onFirstPage())
                    <li class="page-item disabled"><span class="page-link rounded-pill">&lt;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link rounded-pill" href="{{ $group->previousPageUrl() }}" rel="prev">&lt;</a>
                    </li>
                @endif

                {{-- Range --}}
                @php
                    $start = max($group->currentPage() - 2, 1);
                    $end = min($group->lastPage(), $group->currentPage() + 2);
                @endphp

                @if ($start > 1)
                    <li class="page-item"><a class="page-link rounded-pill" href="{{ $group->url(1) }}">1</a></li>
                    @if ($start > 2)
                        <li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $group->currentPage())
                        <li class="page-item active">
                            <span class="page-link rounded-pill bg-primary border-primary">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link rounded-pill" href="{{ $group->url($page) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endfor

                @if ($end < $group->lastPage())
                    @if ($end < $group->lastPage() - 1)
                        <li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link rounded-pill" href="{{ $group->url($group->lastPage()) }}">{{ $group->lastPage() }}</a>
                    </li>
                @endif

                {{-- Next --}}
                @if ($group->hasMorePages())
                    <li class="page-item">
                        <a class="page-link rounded-pill" href="{{ $group->nextPageUrl() }}" rel="next">&gt;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link rounded-pill">&gt;</span></li>
                @endif

            </ul>
        </nav>
    </div>
@endif


  </div> {{-- close card-body --}}
            </div> {{-- close card --}}
        @endforeach
        <div class="text-end mt-4">
            <button class="btn btn-success btn-lg px-4 rounded-pill fw-semibold">
                <i class="bi bi-save me-2"></i> Save All Assignments
            </button>
        </div>
    </form>
</div>
@endsection
