@extends('layouts.admin')
@section('title', 'Programme Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header Card --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold text-primary mb-1">Examination Dashboard</h3>
                    <p class="text-muted small mb-0">Manage and navigate between different academic programmes.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Cards Row --}}
    <div class="row g-4">

        {{-- Regular Programme --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg h-100 bg-light hover-card rounded-4">
                <div class="card-body">
                    <span class="badge bg-primary mb-2 px-3 py-1">Semester Based</span>
                    <h5 class="card-title text-primary fw-bold">📘 Regular Programme</h5>
                    <p class="card-text text-muted">Manage operations related to Regular academic programmes.</p>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.programme.switch', 'regular') }}" class="btn btn-outline-primary btn-sm action-btn px-4">
                            Go to Regular
                        </a>
                        <!-- <a href="{{ route('admin.programme.switch', 'regular') }}" class="btn btn-primary btn-sm action-btn px-4">
                            Switch Now
                        </a> -->
                    </div>
                </div>
            </div>
        </div>

        {{-- Diploma Programme --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg h-100 bg-white hover-card rounded-4">
                <div class="card-body">
                    <span class="badge bg-success mb-2 px-3 py-1">Yearly Based</span>
                    <h5 class="card-title text-success fw-bold">🎓 Diploma Programme</h5>
                    <p class="card-text text-muted">Manage operations related to Diploma or Certificate courses.</p>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.programme.switch', 'diploma') }}" class="btn btn-outline-success btn-sm action-btn px-4">
                            Go to Diploma
                        </a>
                        <!-- <a href="{{ route('admin.programme.switch', 'diploma') }}" class="btn btn-success btn-sm action-btn px-4">
                            Switch Now
                        </a> -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Footer Note --}}
   

</div>
@endsection

@push('styles')
<style>
    .card-text {
        min-height: 70px;
    }

    .hover-card {
        transition: all 0.4s ease-in-out;
        border-radius: 1rem;
    }

    .hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }

    .card-title {
        font-size: 1.2rem;
    }

    .btn-sm {
        font-size: 0.95rem;
        padding: 0.4rem 1.2rem;
        border-radius: 999px;
        transition: 0.2s ease;
    }

    .action-btn:hover {
        box-shadow: 0 0 0.5rem rgba(0, 123, 255, 0.3);
        transform: scale(1.02);
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 999px;
    }

    @media (max-width: 767px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endpush
