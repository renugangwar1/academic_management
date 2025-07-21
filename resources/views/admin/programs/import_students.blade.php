@extends('layouts.admin')
@section('title', 'Import Students - ' . $program->name)

@section('content')
<div class="container py-4">

    {{-- Page Heading --}}
    <div class="mb-4">
        <h4 class="fw-bold text-primary">Import Students for Program: <span class="text-dark">{{ $program->name }}</span></h4>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>There were some errors with your upload:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('failures'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Some rows failed validation:</strong>
            <ul class="small mb-0">
                @foreach(session('failures') as $failure)
                    <li>
                        Row {{ $failure->row() }} – {{ implode(', ', $failure->errors()) }}
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Import Form --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.programs.import', $program->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 align-items-end">
                    @if($program->structure === 'semester')
                        <div class="col-md-3">
                            <label for="semester" class="form-label fw-semibold">Semester</label>
                            <input type="number" name="semester" id="semester" class="form-control" min="1" max="8" value="{{ old('semester') }}" required>
                        </div>
                    @elseif(in_array($program->structure, ['yearly', 'year_wise']))
                        <div class="col-md-3">
                            <label for="year" class="form-label fw-semibold">Year</label>
                            <input type="number" name="year" id="year" class="form-control" min="1" max="10" value="{{ old('year') }}" required>
                        </div>
                    @else
                        <div class="col-md-3">
                            <label for="period" class="form-label fw-semibold">Period</label>
                            <input type="number" name="period" id="period" class="form-control" min="1" max="10" value="{{ old('period') }}" required>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="file" class="form-label fw-semibold">Upload Excel File</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.csv,.txt" required>
                    </div>

                    <div class="col-md-3">
                        <a href="{{ route('admin.programs.students.template', $program->id) }}"
                           class="btn btn-outline-info w-100 rounded-pill"
                           target="_blank" rel="noopener"
                           data-bs-toggle="tooltip" title="Download Excel Template">
                            <i class="bi bi-download me-1"></i> Sample Template
                        </a>
                    </div>
                </div>

                <div class="mt-4 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                        <i class="bi bi-upload me-2"></i> Import Students
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Imported Students --}}
    @if(isset($importedStudents) && count($importedStudents) > 0)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-success text-white fw-bold rounded-top-4">Imported Students</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($importedStudents as $student)
                        <li class="list-group-item">
                            {{ $student->name }} <span class="text-muted">(Roll: {{ $student->nchm_roll_number }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Failed Students --}}
    @if(isset($failedStudents) && count($failedStudents) > 0)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-danger text-white fw-bold rounded-top-4">Failed Rows</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($failedStudents as $fail)
                        <li class="list-group-item text-danger">
                            Row {{ $fail['row'] }}: {{ $fail['reason'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>
@endsection
