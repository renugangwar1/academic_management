@extends('layouts.admin')
@section('title', 'Academic Sessions')

@section('content')
<div class="container-fluid px-4 py-4">
       <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>
                    <h3 class="fw-bold text-primary mb-1">Academic Sessions</h3>
                    <p class="text-muted small mb-0">Manage and organize academic session timelines.</p>
                </div>
</div>
</div>
</div>
    @if(session('success'))
        <div class="alert alert-success shadow-sm rounded-pill px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        {{-- Card 1: Regular Programs --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg h-100 bg-light hover-card rounded-4">
                <div class="card-body">
                    <span class="badge bg-primary mb-2 px-3 py-1">Semester Based</span>
                    <h5 class="card-title text-primary fw-bold">🎓 Regular Programs</h5>
                    <p class="card-text text-muted">Manage sessions for UG/PG programs that follow a semester pattern.</p>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.academic_sessions.regular.index') }}" class="btn btn-outline-primary btn-sm action-btn px-4">
                            View Sessions
                        </a>
                        <a href="{{ route('admin.academic_sessions.create', ['type' => 'regular']) }}" class="btn btn-primary btn-sm action-btn px-4">
                            + Add Session
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Diploma / Certificate --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg h-100 bg-white hover-card rounded-4">
                <div class="card-body">
                    <span class="badge bg-secondary mb-2 px-3 py-1">Yearly/Custom</span>
                    <h5 class="card-title text-secondary fw-bold">📜 Diploma / Certificate</h5>
                    <p class="card-text text-muted">Create and manage sessions for diploma or certificate courses with flexible structure.</p>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.academic_sessions.diploma.index') }}" class="btn btn-outline-secondary btn-sm action-btn px-4">
                            View Sessions
                        </a>
                        <a href="{{ route('admin.academic_sessions.create', ['type' => 'diploma']) }}" class="btn btn-secondary btn-sm action-btn px-4">
                            + Add Session
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
 .pagination {
    margin-bottom: 0;
    gap: 0.4rem;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination .page-link {
    min-width: 2.25rem;
    min-height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    border-radius: 50rem !important;
    border: none;
    color: #0d6efd;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    background-color: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.pagination .page-link:hover {
    background-color: #e9f3ff;
    color: #084298;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 0.25rem 0.75rem rgba(13, 110, 253, 0.25);
}

.pagination .page-item.disabled .page-link {
    background-color: #f8f9fa;
    color: #6c757d;
    pointer-events: none;
    box-shadow: none;
}

/* ✅ REMOVE previous/next arrows */
.pagination .page-item:first-child,
.pagination .page-item:last-child {
    display: none !important;
}


</style>
@endpush
