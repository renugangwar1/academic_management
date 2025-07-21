@extends('layouts.admin')

@section('title', 'Programme Dashboard')

@section('content')
<div class="container py-5">
    <div class="mb-5 text-center">
        <h2 class="text-primary fw-bold">Examination Dashboard</h2>
        <p class="text-secondary">Manage and navigate between different academic programmes.</p>
    </div>

    <div class="row g-4 justify-content-center">

        {{-- Regular Programme Card --}}
        <div class="col-md-5">
            <div class="card h-100 shadow-sm border-0 rounded-4 transition">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-book fs-2 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-semibold">Regular Programme</h5>
                    <p class="text-muted">This section is for Regular academic programmes.</p>
                    <a href="{{ route('admin.programme.switch', 'regular') }}"
                       class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                        Switch to Regular
                    </a>
                </div>
            </div>
        </div>

        {{-- Diploma Programme Card --}}
        <div class="col-md-5">
            <div class="card h-100 shadow-sm border-0 rounded-4 transition">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-mortarboard fs-2 text-success"></i>
                    </div>
                    <h5 class="card-title fw-semibold">Diploma Programme</h5>
                    <p class="text-muted">This section is for Diploma academic programmes.</p>
                    <a href="{{ route('admin.programme.switch', 'diploma') }}"
                       class="btn btn-success btn-sm px-4 rounded-pill mt-2">
                        Switch to Diploma
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-5 text-center">
        <p class="text-muted">You can now perform operations for both programme types. Add more options here if needed.</p>
    </div>
</div>

{{-- Optional: Add a little hover effect --}}
<style>
    .card.transition {
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }
    .card.transition:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
    }
</style>
@endsection
