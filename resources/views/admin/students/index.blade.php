@extends('layouts.admin')
@section('title', 'Student List')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="text-primary mb-0">Student List</h4>
        <a href="{{ route('admin.students.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-person-plus"></i> Add Student
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.students.index') }}" class="row gy-2 gx-3 align-items-center">

                {{-- Per Page --}}
                <div class="col-auto">
                    <label for="per_page" class="form-label small mb-1">Show</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        @foreach([5,10,20,50,100] as $num)
                            <option value="{{ $num }}" {{ $perPage == $num ? 'selected' : '' }}>{{ $num }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <span class="form-text">entries</span>
                </div>

                {{-- Program Filter --}}
                <div class="col-md-3">
                    <select name="program_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All Programs --</option>
                        @foreach(\App\Models\Program::all() as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search name / roll / email" value="{{ request('search') }}">
                </div>

                {{-- Search Button --}}
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill shadow-sm">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>

                {{-- Export --}}
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.students.export', request()->query()) }}" class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive shadow-sm mb-4" style="overflow-x: auto;">
        <table class="table table-sm table-bordered table-hover align-middle text-center bg-white mb-0">
           <thead class="table-dark">
    <tr class="align-middle">
        <th>#</th>
        <th>NCHM Roll No</th>
        <th>Enrolment No</th>
        <th>Name</th>
        <th>Program</th>
        <th>Semester</th>
        <th>Institute</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>DOB</th>
        <th>Category</th>
        <th>Father's Name</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    {{-- Filter Row --}}
    <tr>
     <form id="columnFilterForm" method="GET" action="{{ route('admin.students.index') }}">

            <td></td>
            <td><input type="text" name="nchm_roll_number" value="{{ request('nchm_roll_number') }}" class="form-control form-control-sm"></td>
            <td><input type="text" name="enrolment_number" value="{{ request('enrolment_number') }}" class="form-control form-control-sm"></td>
            <td><input type="text" name="name" value="{{ request('name') }}" class="form-control form-control-sm"></td>

            {{-- Program --}}
            <td>
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\Program::all() as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </select>
            </td>

            <td><input type="text" name="semester" value="{{ request('semester') }}" class="form-control form-control-sm"></td>

            {{-- Institute --}}
            <td>
                <select name="institute_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\Institute::all() as $inst)
                        <option value="{{ $inst->id }}" {{ request('institute_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                    @endforeach
                </select>
            </td>

            <td><input type="text" name="email" value="{{ request('email') }}" class="form-control form-control-sm"></td>
            <td><input type="text" name="mobile" value="{{ request('mobile') }}" class="form-control form-control-sm"></td>
            <td></td>
            <td><input type="text" name="category" value="{{ request('category') }}" class="form-control form-control-sm"></td>
            <td><input type="text" name="father_name" value="{{ request('father_name') }}" class="form-control form-control-sm"></td>

            {{-- Status --}}
            <td>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </td>

            <td>
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i></button>
            </td>
        </form>
    </tr>
</thead>

            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                        <td>{{ $student->nchm_roll_number ?? '-' }}</td>
                        <td>{{ $student->enrolment_number ?? '-' }}</td>
                        <td class="text-start">{{ $student->name }}</td>
                        <td>{{ $student->program->name ?? '-' }}</td>
                        <td>{{ $student->semester ?? '-' }}</td>
                        <td>{{ $student->institute->name ?? '-' }}</td>
                        <td>{{ $student->email ?? '-' }}</td>
                        <td>{{ $student->mobile ?? '-' }}</td>
                        <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $student->category ?? '-' }}</td>
                        <td>{{ $student->father_name ?? '-' }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $student->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $student->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Delete this student?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center text-muted">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
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

                    {{-- Range --}}
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

</div>
@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('columnFilterForm');
        const inputs = form.querySelectorAll('input, select');

        let timeout = null;

        inputs.forEach(input => {
            if (input.tagName === 'INPUT') {
                input.addEventListener('keyup', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        form.submit();
                    }, 600); // Delay to avoid too frequent reloads
                });
            }

            input.addEventListener('change', () => {
                form.submit();
            });
        });
    });
</script>
@endpush
