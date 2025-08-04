@extends('layouts.institute')

@section('title', 'Institute Dashboard')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold text-gradient bg-gradient animate__animated animate__fadeInDown">
        Welcome, {{ $user->name }}
    </h2>

    {{-- Summary Cards --}}
   <div class="row g-4 mb-5">
    <div class="col-md-4">
        <a href="{{ route('institute.students.list') }}" class="text-decoration-none">
            <div class="card dashboard-card border-0 shadow-lg h-100 animate__animated animate__fadeInUp">
                <div class="card-body text-center">
                    <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                    <h5 class="text-muted">Enrolled Students</h5>
                    <h3 class="text-dark fw-bold">{{ $studentCount ?? '--' }}</h3>
                </div>
            </div>
        </a>
    </div>

        <div class="col-md-4">
            <div class="card dashboard-card border-0 shadow-lg h-100 animate__animated animate__fadeInUp">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">Programs Offered</h5>
                    <h3 class="text-dark fw-bold">{{ $programCount ?? '--' }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card border-0 shadow-lg h-100 animate__animated animate__fadeInUp">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-warning mb-3"></i>
                    <h5 class="text-muted">Active Sessions</h5>
                    <h3 class="text-dark fw-bold">{{ $sessionCount ?? '--' }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
   <div class="card border-0 shadow-lg animate__animated animate__fadeInUp">
    <div class="card-header gradient-header text-black d-flex align-items-center">
        <i class="fas fa-link me-2"></i>
        <h5 class="mb-0">Quick Links</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
                @php
                    $links = [
['route' => route('institute.programs.index'), 'label' => 'Programs', 'icon' => 'book', 'color' => 'info'],
                        ['route' => route('institute.students.index'), 'label' => 'Manage Students', 'icon' => 'user-graduate', 'color' => 'primary'],
                        ['route' => route('institute.examinations'), 'label' => 'Examinations', 'icon' => 'file-alt', 'color' => 'success'],
                        ['route' => route('institute.reappears'), 'label' => 'Reappears', 'icon' => 'redo', 'color' => 'danger'],
                      
                    ];
                @endphp

                @foreach ($links as $link)
                    <div class="col-md-3">
                        <a href="{{ $link['route'] }}" class="text-decoration-none">
                            <div class="card quick-link-card shadow-sm border-0 h-100 text-center p-4">
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

@push('styles')
<style>
    .text-black {
        color: #000 !important;
    }

    .dashboard-card {
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #ddd;
        transition: transform 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .gradient-header {
        background: linear-gradient(90deg, #000 0%, #333 100%);
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .quick-link-card {
        background-color: #1f1f1f;
        color: #fff;
        border-radius: 1rem;
        transition: all 0.3s ease;
    }

    .quick-link-card:hover {
        background-color: #000;
        transform: scale(1.03);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .quick-link-card i,
    .quick-link-card .fw-semibold {
        color: #fff !important;
    }
</style>
@endpush


@endsection
