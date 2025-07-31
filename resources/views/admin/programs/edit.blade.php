@extends('layouts.admin')
@section('title', 'Edit Program')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-primary text-white rounded-top-4 d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Edit Program</h4>
        </div>

        <div class="card-body px-4 py-5">
            <form action="{{ route('admin.programs.update', $program->id) }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control shadow-sm rounded-3" required
                               value="{{ old('name', $program->name) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                        <input type="number" name="duration" class="form-control shadow-sm rounded-3" min="1" required
                               value="{{ old('duration', $program->duration) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Duration Unit <span class="text-danger">*</span></label>
                        <select name="duration_unit" class="form-select shadow-sm rounded-3" required>
                            <option disabled selected>-- Select Unit --</option>
                            <option value="year" {{ old('duration_unit', $program->duration_unit) == 'year' ? 'selected' : '' }}>Year</option>
                            <option value="month" {{ old('duration_unit', $program->duration_unit) == 'month' ? 'selected' : '' }}>Month</option>
                            <option value="day" {{ old('duration_unit', $program->duration_unit) == 'day' ? 'selected' : '' }}>Day</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Program Structure <span class="text-danger">*</span></label>
                    <select name="structure" class="form-select shadow-sm rounded-3" required>
                        <option disabled selected>-- Select Structure --</option>
                        <option value="semester" {{ old('structure', $program->structure) == 'semester' ? 'selected' : '' }}>Semester-wise</option>
                        <option value="yearly" {{ old('structure', $program->structure) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="short_term" {{ old('structure', $program->structure) == 'short_term' ? 'selected' : '' }}>Short Course</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="bi bi-arrow-left-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success px-4 rounded-pill">
                        <i class="bi bi-save2 me-1"></i> Update Program
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
