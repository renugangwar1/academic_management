@extends('layouts.admin')
@section('title', 'All Programs')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header: Title + Add Button --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="row align-items-end g-3">

                {{-- Column 1: Title --}}
                <div class="col-lg-8 col-md-12">
                    <div>
                        <h3 class="fw-bold text-primary mb-1">Program Management</h3>
                        <p class="text-muted small mb-0">Manage, add, and edit all academic programs.</p>
                    </div>
                </div>

                {{-- Column 2: Add Button --}}
                <div class="col-lg-4 col-md-12 text-end">
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-success shadow-sm rounded-pill px-4 py-2">
                        <i class="bi bi-plus-circle me-1"></i> Add Program
                    </a>
                </div>

            </div>
        </div>
    </div>


    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Program Table --}}
    <div class="table-responsive shadow-sm ">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-white">
                <tr>
                    <th scope="col">Program ID</th>
                    <th scope="col">Program Name</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Duration Unit</th>
                    <th scope="col">Structure</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr>
                    <td><span class="badge bg-warning text-dark">{{ $program->id }}</span></td>
                    <td>{{ $program->name }}</td>
                    <td>{{ $program->duration }}</td>
                    <td class="text-capitalize">{{ $program->duration_unit }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $program->structure) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.programs.settings', $program->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1" data-bs-toggle="tooltip" title="Settings">
                            <i class="bi bi-gear"></i>
                        </a>
                        <a href="{{ route('admin.programs.edit', $program->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.programs.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this program?')">
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
                    <td colspan="6" class="text-center text-muted">No programs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $programs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
