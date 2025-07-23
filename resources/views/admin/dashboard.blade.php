@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4 text-primary fw-bold animate__animated animate__fadeInDown">Welcome, Master Admin</h2>

    {{-- Summary Cards --}}
   <div class="row g-4 mb-4">
    <!-- Total Students -->
    <div class="col-md-4">
        <a href="{{ route('admin.students.index') }}" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100 bg-light animate__animated animate__fadeInUp">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Students</p>
                        <h3 class="text-primary fw-bold">{{ $studentCount }}</h3>
                    </div>
                    <i class="fas fa-user-graduate fa-3x text-primary"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Total Programs -->
    <div class="col-md-4">
        <a href="{{ route('admin.programs.index') }}" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100 bg-light animate__animated animate__fadeInUp">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Programs</p>
                        <h3 class="text-success fw-bold">{{ $programCount }}</h3>
                    </div>
                    <i class="fas fa-book-open fa-3x text-success"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Total Institutes -->
    <div class="col-md-4">
        <a href="{{ route('admin.institutes.index') }}" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100 bg-light animate__animated animate__fadeInUp">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Institutes</p>
                        <h3 class="text-warning fw-bold">{{ $instituteCount }}</h3>
                    </div>
                    <i class="fas fa-university fa-3x text-warning"></i>
                </div>
            </div>
        </a>
    </div>
</div>


    <hr class="my-4">

    {{-- Quick Links --}}
    <div class="card shadow-sm animate__animated animate__fadeInUp">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-link me-2"></i>
            <h5 class="mb-0">Quick Links</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @php
                    $links = [
                        ['route' => 'admin.programs.index', 'label' => 'Programs', 'icon' => 'book', 'color' => 'success'],
                        ['route' => 'admin.academic_sessions.index', 'label' => 'Academic Sessions', 'icon' => 'calendar-alt', 'color' => 'warning'],
                        ['route' => 'admin.examination.index', 'label' => 'Examination', 'icon' => 'chart-line', 'color' => 'secondary'],
                        ['route' => 'admin.reappears.index', 'label' => 'Reappears', 'icon' => 'redo', 'color' => 'danger'],

                        // Optional:
                        // ['route' => 'admin.institutes.index', 'label' => 'Manage Institutes', 'icon' => 'university', 'color' => 'info'],
                        // ['route' => 'admin.courses.index', 'label' => 'Manage Courses', 'icon' => 'chalkboard-teacher', 'color' => 'primary'],
                       
                    ];
                @endphp

                @foreach ($links as $link)
                    <div class="col-md-4">
                        <a href="{{ route($link['route']) }}" class="card quick-link-card border-start border-4 border-{{ $link['color'] }} text-decoration-none shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-{{ $link['icon'] }} fa-lg text-{{ $link['color'] }} me-3"></i>
                                <div class="text-dark fw-semibold">{{ $link['label'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
{{-- Upload Activity --}}

<div class="card shadow-sm mt-5 animate__animated animate__fadeInUp">
    <div class="card-header bg-secondary text-white d-flex align-items-center">
        <i class="fas fa-file-upload me-2"></i>
        <h5 class="mb-0">Recent Student Uploads</h5>
    </div>
    <div class="card-body">
        @if($recentUploads->count())
            <ul class="list-group">
                @foreach ($recentUploads as $upload)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                           <div class="d-flex flex-column">
    <div>
        📁 <strong>{{ $upload->institute->name }}</strong> uploaded 
        <span class="text-info">{{ $upload->filename }}</span>
        @if($upload->status !== 'approved')
            <span class="badge bg-warning text-dark">{{ ucfirst($upload->status) }}</span>
        @else
            <span class="badge bg-success">Approved</span>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="mt-2">
        <a href="{{ route('admin.studentUploads.download', $upload) }}"
           class="btn btn-sm btn-outline-primary me-2">
            ⬇️ Download Excel
        </a>

        @if($upload->status === 'pending')
            <form action="{{ route('admin.studentUploads.approve', $upload) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">✅ Approve</button>
            </form>
            <form action="{{ route('admin.studentUploads.reject', $upload) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">❌ Reject</button>
            </form>
        @endif
    </div>
</div>

                        </div>
                        <span class="badge bg-light text-muted">{{ $upload->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">No recent uploads.</p>
        @endif
    </div>
</div>

{{-- Optional custom CSS --}}
@push('styles')
<style>
    .stat-card {
        border-left: 4px solid transparent;
        transition: border-color 0.3s ease, transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .quick-link-card {
        transition: transform 0.2s ease;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
    }

    .quick-link-card:hover {
        transform: translateY(-4px);
        background-color: #f1f1f1;
    }

     .stat-card:hover {
        transform: translateY(-5px);
        border-left: 4px solid #0d6efd;
    }
    a.text-decoration-none:hover .card-body h3 {
        text-decoration: underline;
    }

      .list-group-item .btn {
        margin-right: 0.5rem;
    }

    .badge {
        font-size: 0.8rem;
    }  .list-group-item .btn {
        margin-right: 0.5rem;
    }

    .badge {
        font-size: 0.8rem;
    }
</style>
@endpush
@endsection
