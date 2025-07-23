<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Institute Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Sticky navbar */
        .sticky-top-shadow {
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 sticky-top-shadow">
        <a class="navbar-brand fw-bold" href="{{ route('institute.dashboard') }}">
            <i class="fas fa-school me-2"></i>Institute Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#instituteNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="instituteNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.dashboard') ? 'active' : '' }}"
                       href="{{ route('institute.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Students -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.students.*') ? 'active' : '' }}"
                       href="{{ route('institute.students.index') }}">
                        <i class="fas fa-user-graduate me-1"></i> Students
                    </a>
                </li>

                <!-- Programs -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.programs.*') ? 'active' : '' }}"
                       href="{{ route('institute.programs.index') }}">
                        <i class="fas fa-layer-group me-1"></i> Programs
                    </a>
                </li>

                <!-- Examinations -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.examinations') ? 'active' : '' }}"
                       href="{{ route('institute.examinations') }}">
                        <i class="fas fa-file-alt me-1"></i> Examinations
                    </a>
                </li>

                <!-- Reappears -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.reappears') ? 'active' : '' }}"
                       href="{{ route('institute.reappears') }}">
                        <i class="fas fa-repeat me-1"></i> Reappears
                    </a>
                </li>
            </ul>

            @auth
                <div class="d-flex align-items-center text-white">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ Auth::user()->name }}

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="ms-3 d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid my-4">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
