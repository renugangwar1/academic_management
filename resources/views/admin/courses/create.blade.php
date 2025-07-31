@extends('layouts.admin')
@section('title', 'Add Course')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Manual Course Form --}}
    <div class="card mb-5 shadow-sm border-0 rounded-4">
        <div class="card-header bg-success text-white rounded-top-4">
            <h5 class="mb-0">➕ Add New Course</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                        <input type="text" name="course_code" class="form-control" required placeholder="e.g. CS101">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="course_title" class="form-control" required placeholder="e.g. Programming Basics">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="Theory">Theory</option>
                            <option value="Practical">Practical</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Credit Hours <span class="text-danger">*</span></label>
                        <input type="number" name="credit_hours" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Credit Value <span class="text-danger">*</span></label>
                        <input type="text" name="credit_value" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12 d-flex align-items-center flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_attendance" value="1" id="attendanceCheck">
                            <label class="form-check-label" for="attendanceCheck">Has Attendance</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_internal" value="1" id="internalCheck">
                            <label class="form-check-label" for="internalCheck">Has Internal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_external" value="1" id="externalCheck">
                            <label class="form-check-label" for="externalCheck">Has External</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success px-4">
                        💾 Save Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Excel Upload --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0">📥 Import Courses via Excel</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Select Program <span class="text-danger">*</span></label>
                        <select name="program_id" class="form-select" required id="excelProgramSelect">
                            <option value="">-- Select Program --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Upload Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Accepted formats: .xlsx, .xls, .csv</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="#" id="download-template-btn" class="btn btn-outline-info">
                        ⬇️ Download Sample Excel
                    </a>
                    <button type="submit" class="btn btn-primary px-4">📤 Import Courses</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('download-template-btn').addEventListener('click', function (e) {
    e.preventDefault();
    const programId = document.getElementById('excelProgramSelect').value;

    if (!programId) {
        alert('Please select a program first.');
        return;
    }

    const downloadUrl = "{{ url('/admin/courses/template-download') }}/" + programId;
    window.location.href = downloadUrl;
});
</script>
@endpush
