@extends('layouts.admin')
@section('title', isset($student) ? 'Edit Student' : 'Add Student')

@section('content')
<div class="container py-4">

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h4 class="mb-3">{{ isset($student) ? 'Edit Student' : 'Add Student' }}</h4>

    <form action="{{ isset($student) ? route('admin.students.update', $student->id) : route('admin.students.store') }}" method="POST">
        @csrf
        @if(isset($student)) @method('PUT') @endif

        {{-- Name and NCHM --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $student->name ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">NCHM Roll Number</label>
                <input type="text" name="nchm_roll_number" class="form-control" value="{{ old('nchm_roll_number', $student->nchm_roll_number ?? '') }}">
            </div>
        </div>

        {{-- Program + Semester/Year --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Program</label>
                <select name="program_id" id="programSelect" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}"
                            data-structure="{{ $program->structure }}"
                            {{ old('program_id', $student->program_id ?? '') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4" id="semesterField">
                <label class="form-label">Semester</label>
                <input type="number" name="semester" class="form-control" value="{{ old('semester', $student->semester ?? '') }}">
            </div>

            <div class="col-md-4 d-none" id="yearField">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="{{ old('year', $student->year ?? '') }}">
            </div>
        </div>

        {{-- Institute / Year / Session --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Institute</label>
                <select name="institute_id" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($institutes as $institute)
                        <option value="{{ $institute->id }}" {{ old('institute_id', $student->institute_id ?? '') == $institute->id ? 'selected' : '' }}>
                            {{ $institute->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Academic Year</label>
                <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2024-2025" required value="{{ old('academic_year', $student->academic_year ?? '') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Session</label>
                <select name="session" class="form-select" required>
                    <option value="">Select</option>
                    <option value="January" {{ old('session', $student->session ?? '') == 'January' ? 'selected' : '' }}>January</option>
                    <option value="July" {{ old('session', $student->session ?? '') == 'July' ? 'selected' : '' }}>July</option>
                </select>
            </div>
        </div>

        {{-- DOB / Category / Father --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $student->category ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Father's Name</label>
                <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name ?? '') }}">
            </div>
        </div>

        {{-- Email / Mobile --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Mobile</label>
                <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $student->mobile ?? '') }}">
            </div>
        </div>

        {{-- Enrolment Number --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Enrolment Number</label>
                <input type="text" name="enrolment_number" class="form-control" value="{{ old('enrolment_number', $student->enrolment_number ?? '') }}">
            </div>
        </div>

        {{-- Status --}}
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" {{ old('status', $student->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="status">Active</label>
        </div>

        {{-- Buttons --}}
        <div class="text-end">
            <button class="btn btn-primary px-4">{{ isset($student) ? 'Update' : 'Save' }}</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const programSelect = document.getElementById('programSelect');
    const semesterField = document.getElementById('semesterField');
    const yearField     = document.getElementById('yearField');
    const semesterInput = semesterField?.querySelector('input');
    const yearInput     = yearField?.querySelector('input');

    function toggleFields() {
        const selectedOption = programSelect.options[programSelect.selectedIndex];
        const structure = selectedOption?.dataset.structure ?? 'semester';

        if (structure === 'yearly' || structure === 'year_wise') {
            yearField.classList.remove('d-none');
            semesterField.classList.add('d-none');
            if (semesterInput) semesterInput.disabled = true;
            if (yearInput) yearInput.disabled = false;
        } else {
            semesterField.classList.remove('d-none');
            yearField.classList.add('d-none');
            if (semesterInput) semesterInput.disabled = false;
            if (yearInput) yearInput.disabled = true;
        }
    }

    programSelect?.addEventListener('change', toggleFields);
    toggleFields(); // Call on load
});
</script>
@endpush
