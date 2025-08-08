<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Institute Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styling -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #1c1c1c !important;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
            color: #f1c40f !important;
        }

        .navbar .nav-link {
            padding: 0.5rem 1rem;
            color: #ddd !important;
            font-weight: 500;
        }

        .navbar .nav-link i {
            width: 18px;
        }

        .navbar .nav-link.active,
        .navbar .nav-link:hover {
            color: #fff !important;
            background-color: #f1c40f !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sticky-top-shadow {
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .user-info {
            font-size: 0.95rem;
            margin-right: 1rem;
        }

        .badge {
            font-size: 0.75rem;
        }

        .container-fluid {
            padding-top: 1rem;
        }

        .btn-outline-light {
            border-color: #f1c40f;
            color: #f1c40f;
        }

        .btn-outline-light:hover {
            background-color: #f1c40f;
            color: #1c1c1c;
        }
    </style>

    @yield('styles')
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top-shadow">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('institute.dashboard') }}">
            <i class="fas fa-school me-2"></i> Institute Panel
        </a>
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#instituteNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="instituteNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.dashboard') ? 'active' : '' }}"
                       href="{{ route('institute.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.students.*') ? 'active' : '' }}"
                       href="{{ route('institute.students.index') }}">
                        <i class="fas fa-user-graduate me-1"></i> Students
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.programs.*') ? 'active' : '' }}"
                       href="{{ route('institute.programs.index') }}">
                        <i class="fas fa-layer-group me-1"></i> Programs
                    </a>
                </li>

              <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('institute.examinations.*') || request()->routeIs('institute.admitcard.*') || request()->routeIs('institute.results.*') ? 'active' : '' }}"
       href="#" id="examinationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-file-alt me-1"></i> Examinations
    </a>
    <ul class="dropdown-menu" aria-labelledby="examinationsDropdown">
        <li>
            <a class="dropdown-item {{ request()->routeIs('institute.admitcard.index') ? 'active' : '' }}"
               href="{{ route('institute.admitcards.index') }}">
               Admit Card
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ request()->routeIs('institute.examination.index') ? 'active' : '' }}"
               href="{{ route('institute.examination.index') }}">
               Examination
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ request()->routeIs('institute.results.index') ? 'active' : '' }}"
               href="#">
               Results
            </a>
        </li>
    </ul>
</li>


                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('institute.reappears') ? 'active' : '' }}"
                       href="{{ route('institute.reappears') }}">
                        <i class="fas fa-repeat me-1"></i> Reappears
                    </a>
                </li>

                @php $user = auth()->user(); @endphp
                <li class="nav-item">
                    <a href="{{ route('institute.message.create') }}"
                       class="nav-link {{ request()->routeIs('institute.message.*') ? 'active' : '' }}">
                        <i class="fas fa-comments me-1"></i> Messages
                        @if($user && $user->role === 'admin' && isset($unreadMessageCount) && $unreadMessageCount > 0)
                            <span class="badge bg-danger ms-2">{{ $unreadMessageCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            @auth
                <div class="d-flex align-items-center text-white">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="user-info">{{ Auth::user()->name }}</span>

                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="ms-2 d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
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
