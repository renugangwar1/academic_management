@extends('layouts.institute')

@section('title', 'Examinations')

@section('content')
<div class="container py-5 d-flex flex-column align-items-center justify-content-center text-center">

    <h3 class="text-success fw-bold mb-3 display-6">🎓 Examinations</h3>
    <p class="lead text-muted w-75">This section allows institutes to manage examinations: scheduling, admit cards, uploading marks, and viewing results.</p>

    <div class="row justify-content-center row-cols-1 row-cols-md-2 g-4 mt-4 w-100" style="max-width: 900px;">

        <!-- Admit Card Card -->
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary fw-semibold mb-2">
                        <i class="bi bi-file-earmark-person-fill me-2"></i> Admit Cards
                    </h5>
                    <p class="card-text text-secondary">Generate and manage admit cards for students appearing in exams.</p>
                    <a href="{{ route('institute.admitcards.index') }}" class="btn btn-outline-primary mt-2">Manage Admit Cards</a>
                </div>
            </div>
        </div>

        <!-- Examination Card -->
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-success fw-semibold mb-2">
                        <i class="bi bi-clipboard-data-fill me-2"></i> Examinations
                    </h5>
                    <p class="card-text text-secondary">View schedules, upload marks, and review exam results.</p>
                    <a href="{{ route('institute.examination.index') }}" class="btn btn-outline-success mt-2">Manage Examinations</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
