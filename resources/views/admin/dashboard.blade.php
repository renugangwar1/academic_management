@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if($latestUnreadMessage)
<!-- Floating toast notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080; width: 360px;">
    <div class="toast show shadow border-0 text-dark" role="alert">
        <div class="toast-header d-flex justify-content-between align-items-center">
            <strong class="me-auto"> New Message</strong>
            <small>{{ $latestUnreadMessage->created_at->diffForHumans() }}</small>
            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <p class="fw-bold text-primary mb-1">{{ $latestUnreadMessage->institute->name }}</p>
            <p class="text-muted mb-3">{{ Str::limit($latestUnreadMessage->message, 80) }}</p>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.messages.chat', $latestUnreadMessage->institute_id) }}" class="btn btn-sm btn-outline-primary">
                     Reply
                </a>
                <form action="{{ route('admin.messages.markAsRead', $latestUnreadMessage->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary"> Mark as Read</button>
                </form>
            </div>
        </div>
    </div>
</div>


@endif

<div class="container-fluid px-4 py-4">
    <!-- <h2 class="mb-4 text-primary fw-bold animate__animated animate__fadeInDown">Welcome, Master Admin</h2> -->

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

    {{-- Upload Activity --}}
    <div class="card shadow-sm mt-5 animate__animated animate__fadeInUp">
        <div class="card-header bg-secondary text-white d-flex align-items-center">
            <i class="fas fa-file-upload me-2"></i>
            <h5 class="mb-0">Recent Uploaded files</h5>
        </div>
        <div class="card-body">
            @if($recentUploads->count())
                <ul class="list-group">
                    @foreach ($recentUploads as $upload)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
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
                                <div class="mt-2">
                                    <a href="{{ route('admin.studentUploads.download', $upload) }}"
                                       class="btn btn-sm btn-outline-primary me-2">⬇️ Download Excel</a>

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
                            <span class="badge bg-light text-muted">{{ $upload->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No recent uploads.</p>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .stat-card, .quick-link-card {
        border-radius: 0.75rem;
        transition: all 0.3s ease-in-out;
    }

    .stat-card {
        border-left: 4px solid transparent;
        background-color: #f8f9fa;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
    }

    .quick-link-card {
        background-color: #f8f9fa;
        padding: 1rem;
        border-left-width: 5px;
        border-left-style: solid;
    }

    .quick-link-card:hover {
        transform: translateY(-4px);
        background-color: #e9ecef;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    a.text-decoration-none:hover .card-body h3,
    a.text-decoration-none:hover .fw-semibold {
        text-decoration: underline;
    }

    .list-group-item .btn {
        margin-right: 0.5rem;
    }

    .badge {
        font-size: 0.8rem;
        padding: 0.4em 0.6em;
        border-radius: 0.3rem;
    }

    .list-group-item {
        transition: background-color 0.2s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fc;
    }

 .toast {
    animation: fadeSlideIn 0.6s ease;
    border-left: 5px solid #0d6efd;
    background-color: rgba(255, 255, 255, 0.7); /* More transparent */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    border-radius: 1rem;
    overflow: hidden;
}

.toast-header {
    background: rgba(13, 110, 253, 0.9); /* Primary blue with transparency */
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-weight: 600;
    padding: 0.75rem 1rem;
}

.toast-body {
    padding: 1rem;
    font-size: 0.95rem;
}

.toast-body p {
    margin-bottom: 0.5rem;
}

.toast-body .btn {
    transition: all 0.2s ease-in-out;
    border-radius: 0.5rem;
}

.toast-body .btn:hover {
    transform: scale(1.05);
}



@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>
@endpush

@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toastElList = [].slice.call(document.querySelectorAll('.toast'))
        toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, {
                autohide: false
            }).show()
        });
    });
</script>
@endpush
