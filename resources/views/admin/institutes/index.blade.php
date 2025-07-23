@extends('layouts.admin')

@section('title', 'All Institutes')

@section('content')
<div class="container-fluid px-4 py-4">
{{-- Header: Title + Search + Add Button --}}
<div class="card shadow-sm border-0 mb-4 rounded-4">
    <div class="card-body">

        <div class="row align-items-end g-3">

            {{-- Column 1: Title --}}
            <div class="col-lg-4 col-md-12">
                <div>
                    <h3 class="fw-bold text-primary mb-1">Institute Management</h3>
                    <p class="text-muted small mb-0">Manage, add, and edit all registered institutes.</p>
                </div>
            </div>

            {{-- Column 2: Search --}}
            <div class="col-lg-5 col-md-8">
                <form action="{{ route('admin.institutes.index') }}" method="GET" class="d-flex flex-wrap align-items-end gap-2">
                    <div class="flex-grow-1">
                      
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control shadow-sm" placeholder="Search by name, code, email...">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                    @if(request('search'))
                    <div>
                        <a href="{{ route('admin.institutes.index') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                    @endif
                </form>
            </div>

            {{-- Column 3: Add Button --}}
            <div class="col-lg-3 col-md-4 text-end">
                <a href="{{ route('admin.institutes.create') }}" class="btn btn-success shadow-sm rounded-pill px-4 py-2">
                    <i class="bi bi-plus-circle me-1"></i> Add Institute
                </a>
            </div>

        </div>

    </div>
</div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-pill px-4 py-2" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th class="text-start">Name</th>
                            <th>Code</th>
                            <th class="text-start">Email</th>
                            <th>Phone</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institutes as $index => $institute)
                        <tr>
                            <td class="text-muted">{{ $institutes->firstItem() + $index }}</td>
                            <td class="text-start fw-semibold text-primary">{{ $institute->name }}</td>
                            <td><span class="badge bg-info text-dark">{{ $institute->code }}</span></td>
                            <td class="text-start">{{ $institute->email ?? '-' }}</td>
                            <td>{{ $institute->contact_phone ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.institutes.edit', $institute->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.institutes.destroy', $institute->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this institute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No institutes found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 bg-light border-top rounded-bottom-4">
                <div class="small text-muted mb-2 mb-md-0">
                    Showing {{ $institutes->firstItem() ?? 0 }} to {{ $institutes->lastItem() ?? 0 }} of {{ $institutes->total() }} entries
                </div>
                <nav>
                    {{ $institutes->links('vendor.pagination.institutes-custom') }}

                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Tooltip Script --}}
@push('scripts')
<script>
    const tooltipTriggerList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')];
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
</script>
@endpush

{{-- Styling --}}
@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: #f9fbfd;
        transition: background 0.3s ease;
    }

    .table th, .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.65em;
        border-radius: 0.5rem;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }

    .btn-outline-primary:hover,
    .btn-outline-secondary:hover,
    .btn-outline-danger:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }

        .btn {
            font-size: 0.85rem;
        }

        .form-label {
            font-size: 0.9rem;
        }
    }
  .pagination {
    margin-bottom: 0;
    gap: 0.4rem;
}

.pagination .page-link {
    min-width: 2.25rem;
    min-height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.75rem;
    font-size: 0.9rem;
    border-radius: 50rem !important;
    border: none;
    color: #0d6efd;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
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
}


</style>
@endpush
