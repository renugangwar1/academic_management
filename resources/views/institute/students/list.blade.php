@extends('layouts.institute')

@section('title', 'Manage Students')

@section('content')
<div class="container py-4 px-4">
    <h2 class="mb-4 fw-bold">Enrolled Students</h2>

    <div class="d-flex justify-content-between align-items-center mb-2">
        {{-- ⬇️ Download Button --}}
        <a href="{{ route('institute.students.export', request()->query()) }}" class="btn btn-success">
            Download Excel
        </a>

        {{-- 🔍 Search Form --}}
        <form action="{{ route('institute.students.list') }}" method="GET" class="d-flex" style="max-width: 300px;">
            <input type="text" name="search" class="form-control me-2" placeholder="Search students..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Roll Number</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Mobile</th>
                <th>Email</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->nchm_roll_number }}</td>
                    <td>{{ $student->program->name ?? 'N/A' }}</td>
                    <td>{{ $student->semester ?? 'N/A' }}</td>
                    <td>{{ $student->mobile }}</td>
                    <td>{{ $student->email }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No students enrolled.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

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
