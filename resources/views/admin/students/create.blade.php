@extends('layouts.admin')
@section('title', isset($student) ? 'Edit Student' : 'Add Student')

@section('content')
<div class="container py-4">

    {{-- Flash Messages --}}
    @if(session('error') || session('success') || $errors->any())
        <div class="mb-4">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Student Form --}}
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-lines-fill me-1"></i>
                {{ isset($student) ? 'Edit Student' : 'Add Student' }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ isset($student) ? route('admin.students.update', $student->id) : route('admin.students.store') }}" method="POST">
                @csrf
                @if(isset($student)) @method('PUT') @endif

                <h6 class="border-bottom mb-3">Student Identity</h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" value="{{ old('nchm_roll_number', $student->nchm_roll_number ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enrolment Number</label>
                        <input type="text" name="enrolment_number" class="form-control" value="{{ old('enrolment_number', $student->enrolment_number ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $student->name ?? '') }}">
                    </div>
                </div>

                <h6 class="border-bottom mb-3">Program Details</h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" id="programSelect" class="form-select" required data-structures='@json($programs->pluck("structure", "id"))'>
                            <option value="">Select</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id', $student->program_id ?? '') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                        <label id="semesterLabel" class="form-label">Semester</label>
                        <input type="number" name="semester" id="semesterField" class="form-control" required value="{{ old('semester', $student->semester ?? '') }}">
                    </div>
                </div>

                <h6 class="border-bottom mb-3">Academic Info</h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2024-2025" required value="{{ old('academic_year', $student->academic_year ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Session</label>
                        <select name="session" class="form-select" required>
                            <option value="">Select</option>
                            <option value="january" {{ old('session', $student->session ?? '') == 'january' ? 'selected' : '' }}>January</option>
                            <option value="july" {{ old('session', $student->session ?? '') == 'july' ? 'selected' : '' }}>July</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth ?? '') }}">
                    </div>
                </div>

                <h6 class="border-bottom mb-3">Contact Info</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $student->mobile ?? '') }}">
                    </div>
                </div>

                <h6 class="border-bottom mb-3">Other Details</h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $student->category ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Father's Name</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name ?? '') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" {{ old('status', $student->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Cancel
                    </a>
                    <button class="btn btn-success px-4">
                        <i class="bi bi-save me-1"></i> {{ isset($student) ? 'Update' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Excel Import --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-upload me-1"></i> Import Students via Excel</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Program <span class="text-danger">*</span></label>
                        <select name="program_id" id="importProgram" class="form-select" required>
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}"
                                    data-template-link="{{ route('admin.students.template.download', $program->id) }}"
                                    data-structure="{{ $program->structure }}">
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Structure</label>
                        <select name="structure" class="form-select" required>
                            <option value="semester">Semester-wise</option>
                            <option value="yearly">Yearly</option>
                            <option value="year_wise">Year-wise</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Upload Excel File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a id="template-download" href="#" class="btn btn-outline-info disabled">
                        <i class="bi bi-download me-1"></i> Download Sample Excel Template
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import Students
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const programSelect = document.getElementById('programSelect');
    const semesterField = document.getElementById('semesterField');
    const semesterLabel = document.getElementById('semesterLabel');
    const structures = JSON.parse(programSelect.dataset.structures || '{}');

    function updateSemesterLabel() {
        const selected = programSelect.value;
        const structure = structures[selected];
        semesterLabel.textContent = (structure === 'yearly' || structure === 'year_wise') ? 'Year' : 'Semester';
    }

    programSelect.addEventListener('change', updateSemesterLabel);
    updateSemesterLabel();

    const importSelect = document.getElementById('importProgram');
    const templateBtn = document.getElementById('template-download');

    importSelect.addEventListener('change', () => {
        const selected = importSelect.selectedOptions[0];
        const link = selected.dataset.templateLink;
        templateBtn.href = link || '#';
        templateBtn.classList.toggle('disabled', !link);
    });
});
</script>
@endpush
@endsection
