@extends('layouts.admin')
@section('title', 'Create Program')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-gradient bg-primary text-white rounded-top-4">
                    <h5 class="mb-0 fw-bold">➕ Add New Program</h5>
                </div>

                <div class="card-body px-4 py-4">
                    <form action="{{ route('admin.programs.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf

                        {{-- Program Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-pill" placeholder="Enter program name" required>
                        </div>

                        {{-- Duration & Unit --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                                <input type="number" name="duration" class="form-control rounded-pill" min="1" placeholder="e.g. 3" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Duration Unit <span class="text-danger">*</span></label>
                                <select name="duration_unit" class="form-select rounded-pill" required>
                                    <option value="">-- Select Unit --</option>
                                    <option value="year">Year</option>
                                    <option value="month">Month</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>
                        </div>

                        {{-- Structure --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Program Structure <span class="text-danger">*</span></label>
                            <select name="structure" class="form-select rounded-pill" required>
                                <option value="">-- Select Structure --</option>
                                <option value="semester">Semester-wise</option>
                                <option value="yearly">Yearly</option>
                                <option value="short_term">Short Course</option>
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                💾 Save Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
