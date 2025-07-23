@extends('layouts.admin')
@section('title', 'Add Institute')

@section('content')
<div class="container py-4">

    {{-- ===== Page Title ===== --}}
    <h4 class="mb-4 fw-bold text-primary">Add New Institute</h4>

    {{-- ===== Flash Messages ===== --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success:</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('failures'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Import completed with some issues:</strong>
            <ul class="mt-2 mb-0">
                @foreach (session('failures') as $failure)
                    <li>
                        Row {{ $failure->row() }}:
                        @foreach ($failure->errors() as $error)
                            {{ $error }}
                        @endforeach
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ===== Create Institute Form ===== --}}
    <div class="card shadow-sm border-0 rounded-4 mb-5">
        <div class="card-header bg-light border-bottom-0 rounded-top-4">
            <h5 class="mb-0 text-dark">➕ Add Institute Manually</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.institutes.store') }}" method="POST" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Institute Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control">
                </div>

                {{-- Future login credentials --}}
                {{-- <div class="col-md-6">
                    <label class="form-label">Login Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div> --}}

                <div class="col-12">
                    <button class="btn btn-success w-100">
                        <i class="bi bi-plus-circle me-1"></i> Create Institute
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Bulk Upload Section ===== --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light border-bottom-0 rounded-top-4">
            <h5 class="mb-0 text-dark">📥 Bulk Upload Institutes</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('admin.institutes.template') }}" class="btn btn-outline-primary btn-sm">
                    ⬇️ Download Excel Template
                </a>
            </div>

            <form action="{{ route('admin.institutes.bulk-upload') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label" for="file">Upload Excel File (.xlsx)</label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls" class="form-control" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
