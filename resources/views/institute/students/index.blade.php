@extends('layouts.institute')

@section('title', 'My Students')

@section('content')
<div class="container py-5">
    <h2 class="text-center fw-bold text-primary display-5 mb-4 animate__animated animate__fadeInDown">
        📋 My Students
    </h2>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success text-center rounded-3 shadow-sm fw-semibold">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger text-center rounded-3 shadow-sm fw-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Import & Export Section --}}
    <div class="row justify-content-center g-4 mt-4">

        {{-- Add Student --}}
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-lg rounded-4 border-0 bg-gradient-success transition-card">
                <div class="card-body d-flex flex-column justify-content-between text-white text-center p-4">
                    <div>
                        <h4 class="card-title fw-bold mb-2">➕ Add Student</h4>
                        <p class="card-text">Manually add a new student to your records.</p>
                    </div>
                    <a href="{{ route('institute.students.create') }}" class="btn btn-light fw-semibold rounded-pill mt-3">
                        Add Now
                    </a>
                </div>
            </div>
        </div>

        {{-- Export Students --}}
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-lg rounded-4 border-0 bg-gradient-dark transition-card">
                <div class="card-body d-flex flex-column justify-content-between text-white text-center p-4">
                    <div>
                        <h4 class="card-title fw-bold mb-2">⬇️ Export Students</h4>
                        <p class="card-text">Download all student records in Excel format.</p>
                    </div>
                    <a href="{{ route('institute.students.export') }}" class="btn btn-light fw-semibold rounded-pill mt-3">
                        Download Excel
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

{{-- Enhanced Styles --}}
<style>
    .transition-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .transition-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745, #218838);
    }

    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40, #23272b);
    }

    .card-title {
        font-size: 1.5rem;
    }

    .card-text {
        font-size: 1rem;
    }

    .btn-light {
        color: #000;
    }
</style>
