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
                @forelse ($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
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
    </div>
</div>
@endsection
