@extends('layouts.admin')
@section('title', 'Examination Sessions')

@section('content')
@include('admin.examination.partials.navbar')

{{-- Session info removed as requested --}}
{{-- <pre>Session ID: {{ session('exam_session_id') ?? 'None set' }}</pre> --}}

<div class="container-fluid px-4 py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h2 class="mb-4 text-center text-primary fw-bold">Manage Examination Activities</h2>

    <div class="row g-4">
        {{-- Upload Marks --}}
        <div class="col-md-4">
            <div class="card shadow-lg h-100 border-start border-4 border-secondary rounded-4 transition-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-upload fs-3 text-secondary me-3"></i>
                        <h5 class="card-title text-secondary mb-0">Upload Marks</h5>
                    </div>
                    <p class="card-text">Upload internal and external marks for students by session, semester, etc.</p>
                       
@if ($academicSession)
    <a href="{{ route('admin.regular.exams.marks.upload', ['session' => $academicSession->id]) }}"
       class="btn btn-outline-secondary w-100">
        <i class="bi bi-upload"></i> Upload Marks
    </a>
@else
    <p class="text-danger">No active academic session found.</p>
@endif

                </div>
            </div>
        </div>

        {{-- Generate Admit Cards --}}
        <div class="col-md-4">
            <div class="card shadow-lg h-100 border-start border-4 border-success rounded-4 transition-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-card-checklist fs-3 text-success me-3"></i>
                        <h5 class="card-title text-success mb-0">Generate Admit Cards</h5>
                    </div>
                    <p class="card-text">Generate admit cards based on academic session, program, semester.</p>
                    <a href="{{ route('admin.regular.exams.admitcard') }}" class="btn btn-outline-success w-100">
                        Generate Admit Cards
                    </a>
                </div>
            </div>
        </div>

        {{-- Process Results --}}
        <div class="col-md-4">
            <div class="card shadow-lg h-100 border-start border-4 border-warning rounded-4 transition-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-graph-up-arrow fs-3 text-warning me-3"></i>
                        <h5 class="card-title text-warning mb-0">Process Results</h5>
                    </div>
                    <p class="card-text">Manage and publish student results after exams.</p>
                  <a href="{{ route('admin.regular.exams.results') }}" class="btn btn-outline-warning w-100">
    Process Results
</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .transition-card {
        transition: transform 0.2s ease-in-out;
    }
    .transition-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush
@endsection
