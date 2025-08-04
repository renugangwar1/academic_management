@extends('layouts.admin')

@section('title', 'Program Settings')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- 🔷 Page Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-primary mb-1">Program Settings</h3>
                <p class="mb-0 text-muted fs-6">
                    Configure and manage details for <strong class="text-dark">{{ $program->name }}</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- 🔶 Settings Table Card --}}
    <div class="card shadow-sm border-0 ">
        <div class="card-header bg-dark text-white  py-3 px-4">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-gear me-2"></i> Program Management Options
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 fs-6">
                    <thead class="table-light text-center">
                        <tr class="text-uppercase small">
                            <th style="width: 60px;">#</th>
                            <th>Option</th>
                            <th>Description</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Row 1 --}}
                        <tr>
                            <td class="text-center">1</td>
                            <td class="fw-semibold">Program Information</td>
                            <td>View duration, structure, and total semesters of the program.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.info', $program->id) }}"
                                   class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="View Info">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr>
                            <td class="text-center">2</td>
                            <td class="fw-semibold">Mapped Courses</td>
                            <td>View all courses linked to this program along with semester mapping.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.courses', $program->id) }}"
                                   class="btn btn-outline-success btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="View Courses">
                                    <i class="bi bi-book me-1"></i> Courses
                                </a>
                            </td>
                        </tr>

                        {{-- Row 3 --}}
                        <tr>
                            <td class="text-center">3</td>
                            <td class="fw-semibold">Students Enrolled</td>
                            <td>List of students enrolled in this program.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.students', $program->id) }}"
                                   class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="View Students">
                                    <i class="bi bi-people me-1"></i> Students
                                </a>
                            </td>
                        </tr>

                        {{-- Row 4 --}}
                        <tr>
                            <td class="text-center">4</td>
                            <td class="fw-semibold">Institutes</td>
                            <td>View institutes related to this program.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.programs.institutes', $program->id) }}"
                                   class="btn btn-outline-info btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="View Institutes">
                                    <i class="bi bi-building me-1"></i> Institutes
                                </a>
                            </td>
                        </tr>

                        {{-- Row 5 --}}
                        <tr>
                            <td class="text-center">5</td>
                            <td class="fw-semibold">Import Students</td>
                            <td>Upload and import students into this program.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.programs.import.form', $program->id) }}"
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="Import Students">
                                    <i class="bi bi-upload me-1"></i> Import
                                </a>
                            </td>
                        </tr>

                        {{-- Row 6 --}}
                        <tr>
                            <td class="text-center">6</td>
                            <td class="fw-semibold">Assign Courses to Students</td>
                            <td>Assign or review which students are mapped to which subjects for each semester.</td>
                            <td class="text-center">
                                <a href="{{ route('admin.programs.assign.courses', $program->id) }}"
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   data-bs-toggle="tooltip" title="Assign Courses">
                                    <i class="bi bi-pencil-square me-1"></i> Assign
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection
