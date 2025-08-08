@extends('layouts.institute')

@section('title', 'Institute Dashboard')

@section('content')
<div class="container py-4">
    <h2 class="mb-5 fw-bold text-dark display-5 animate__animated animate__fadeInDown">
        👋 Welcome back, {{ $user->name }}
    </h2>

    {{-- 🔹 Summary Cards --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="{{ route('institute.students.list') }}" class="text-decoration-none">
                <div class="card glass-card text-center shadow animate__fadeInUp h-100">
                    <div class="card-body py-5">
                        <i class="fas fa-user-graduate fa-3x icon-gold mb-3"></i>
                        <h6 class="text-muted">Enrolled Students</h6>
                        <h2 class="fw-bold text-dark">{{ $studentCount ?? '--' }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <div class="card glass-card text-center shadow animate__fadeInUp h-100">
                <div class="card-body py-5">
                    <i class="fas fa-book fa-3x icon-blue mb-3"></i>
                    <h6 class="text-muted">Programs Offered</h6>
                    <h2 class="fw-bold text-dark">{{ $programCount ?? '--' }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card glass-card text-center shadow animate__fadeInUp h-100">
                <div class="card-body py-5">
                    <i class="fas fa-calendar-alt fa-3x icon-green mb-3"></i>
                    <h6 class="text-muted">Active Sessions</h6>
                    <h2 class="fw-bold text-dark">{{ $sessionCount ?? '--' }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔗 Quick Links --}}
    <div class="card border-0 shadow-lg animate__fadeInUp overflow-hidden">
        <div class="card-header gradient-header text-dark rounded-top d-flex align-items-center">
            <i class="fas fa-link me-2"></i>
            <h5 class="mb-0 fw-semibold">Quick Access</h5>
        </div>
        <div class="card-body bg-light">
            <div class="row g-4">
                @php
                    $links = [
                        ['route' => route('institute.programs.index'), 'label' => 'Programs', 'icon' => 'book', 'color' => 'info'],
                        ['route' => route('institute.students.index'), 'label' => 'Manage Students', 'icon' => 'user-graduate', 'color' => 'primary'],
                        ['route' => route('institute.examinations.dashboard'), 'label' => 'Examinations', 'icon' => 'file-alt', 'color' => 'success'],
                        ['route' => route('institute.reappears'), 'label' => 'Reappears', 'icon' => 'redo', 'color' => 'danger'],
                    ];
                @endphp

                @foreach ($links as $link)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ $link['route'] }}" class="text-decoration-none">
                            <div class="card quick-link-card text-center p-4 h-100 shadow-sm">
                                <i class="fas fa-{{ $link['icon'] }} fa-2x text-{{ $link['color'] }} mb-2"></i>
<div class="fw-semibold text-dark">{{ $link['label'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>



    /* Icons */
    .icon-gold { color: #f1c40f; }
    .icon-blue { color: #3498db; }
    .icon-green { color: #2ecc71; }

    /* Glass-style summary card */
    .glass-card {
        border: none;
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        transition: all 0.3s ease-in-out;
    }

    .glass-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
    }

    /* Quick Links */
    .quick-link-card {
        background: linear-gradient(135deg, #1f1f1f, #3a3a3a);
        border-radius: 1.25rem;
        color: white;
        transition: transform 0.3s ease;
    }

    .quick-link-card:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #000, #2b2b2b);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .quick-link-card i {
        color: inherit;
    }

    .gradient-header {
        background: linear-gradient(90deg, #f1c40f, #c49b0f);
        padding: 1rem 1.5rem;
        font-weight: 600;
    }

    /* Typography */
    h2, h3, h5 {
        font-family: 'Segoe UI', sans-serif;
        font-weight: 700;
    }

    .text-muted {
        color: #7a7a7a !important;
    }

    /* Responsive Fixes */
    @media (max-width: 768px) {
        .card-body.py-5 {
            padding: 2rem 1rem !important;
        }
    }
</style>
@endpush
