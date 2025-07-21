@php
    $sessionId = session('exam_session_id');
    $session   = \App\Models\AcademicSession::find($sessionId);
    $type      = $session->type ?? null;

    $uploadRoute = $type === 'regular'
        ? route('admin.regular.exams.marks.upload', $sessionId)
        : ($type === 'diploma'
            ? route('admin.diploma.exams.marks.upload', $sessionId)
            : '#');

    $admitRoute = $type === 'regular'
        ? route('admin.regular.exams.admitcard', $sessionId)
        : ($type === 'diploma'
            ? route('admin.diploma.exams.admitcard', $sessionId)
            : '#');

    $resultsRoute = $type === 'regular'
        ? route('admin.regular.exams.results', $sessionId)
        : ($type === 'diploma'
            ? route('admin.diploma.exams.results', $sessionId)
            : '#');

    $otherType = $type === 'regular' ? 'diploma' : 'regular';
@endphp

<nav class="navbar navbar-expand-lg bg-gradient-primary sticky-top shadow">
  <div class="container">
    <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="{{ route('admin.exams.calculated') }}">
      <i class="bi bi-journal-text fs-4"></i>
      <span class="fs-5">Examination</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#examNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse mt-3 mt-lg-0" id="examNavbar">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

        {{-- Switch Programme --}}
        <li class="nav-item">
          <form method="POST" action="{{ route('admin.programme.switch', $otherType) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light px-3 rounded-pill d-flex align-items-center">
              <i class="bi bi-arrow-left-right me-2"></i> Switch to {{ ucfirst($otherType) }}
            </button>
          </form>
        </li>

        {{-- Navigation Links --}}
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium {{ request()->routeIs('admin.examination.index') ? 'active-link' : 'text-white' }}" 
             href="{{ route('admin.examination.index') }}">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link px-3 fw-medium {{ request()->routeIs('admin.*.exams.marks.upload') ? 'active-link' : 'text-white' }}" 
             href="{{ $uploadRoute }}">
            <i class="bi bi-upload me-1"></i> Upload Marks
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link px-3 fw-medium {{ request()->routeIs('admin.*.exams.admitcard') ? 'active-link' : 'text-white' }}" 
             href="{{ $admitRoute }}">
            <i class="bi bi-card-text me-1"></i> Admit Cards
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link px-3 fw-medium {{ request()->routeIs('admin.*.exams.results') ? 'active-link' : 'text-white' }}" 
             href="{{ $resultsRoute }}">
            <i class="bi bi-bar-chart me-1"></i> Results
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>
<!-- @if($session)
    <div class="text-center mt-3">
        <h4 class="fw-bold text-uppercase text-info">
            {{ $session->type === 'diploma' ? 'Diploma ' : 'Regular ' }}
        </h4>
    </div>
@endif -->

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0056b3, #007bff);
    }

    .navbar-nav .nav-link {
        transition: all 0.3s ease-in-out;
        font-size: 0.95rem;
        border-radius: 0.375rem;
    }

    .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffc107 !important;
    }

    .navbar-nav .active-link {
        color: #ffc107 !important;
        background-color: rgba(255, 255, 255, 0.15);
        border-radius: 0.375rem;
    }

    .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.2);
        border-color: #fff;
    }

    .navbar .btn {
        transition: all 0.3s ease;
    }
</style>
@endpush
