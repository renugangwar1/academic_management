@extends('layouts.institute')

@section('title', 'Manage Students')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold mb-0">Enrolled Students</h2>
        <a href="{{ route('institute.students.export', request()->query()) }}" class="btn btn-success mb-3">
            Download Excel
        </a>
    </div>

    {{-- ✅ Wrap everything inside the form --}}
    <form method="GET" action="{{ route('institute.students.list') }}" id="filter-form">
        <div class="table-responsive shadow rounded">
            <table id="students-table" class="table table-bordered table-striped">
                <thead class="table-dark align-middle">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Roll Number</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Mobile</th>
                        <th>Email</th>
                    </tr>
                    <tr>
                        @php
                            $filters = ['name', 'roll_number', 'program', 'semester', 'mobile', 'email'];
                        @endphp
                        <th></th> {{-- Serial Number --}}
                        @foreach ($filters as $field)
                            <th class="position-relative">
                                <input type="text" name="{{ $field }}" value="{{ request($field) }}" class="form-control form-control-sm" placeholder="Search {{ ucfirst(str_replace('_', ' ', $field)) }}">
                                @if(request($field))
                                    <button type="button" class="btn btn-sm btn-link clear-filter" data-field="{{ $field }}">×</button>
                                @endif
                            </th>
                        @endforeach
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
    </form>
</div>
@endsection

@push('styles')
<style>
    .position-relative input.form-control-sm {
        padding-right: 28px;
    }

    .clear-filter {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        font-size: 1.2rem;
        color: #dc3545;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filter-form');
        let debounceTimer;

        // Debounced auto-submit after typing
        filterForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        });

        // Clear button logic
        document.querySelectorAll('.clear-filter').forEach(button => {
            button.addEventListener('click', function () {
                const field = this.getAttribute('data-field');
                const input = filterForm.querySelector(`[name="${field}"]`);
                if (input) {
                    input.value = '';
                    filterForm.submit();
                }
            });
        });
    });
</script>
@endpush
