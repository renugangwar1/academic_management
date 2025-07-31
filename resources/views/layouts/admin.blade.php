<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - @yield('title')</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('styles')
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            background-color: #212529;
            overflow-y: auto;
            padding-top: 1rem;
            z-index: 1030;
        }

        .sidebar .nav-link {
            color: #ccc;
            padding: 10px 20px;
            transition: all 0.2s ease-in-out;
            border-radius: 0.375rem;
        }

        .sidebar .nav-link:hover {
            background-color: #343a40;
            color: #fff;
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            background-color: #f8f9fa;
            min-height: 100vh;
            width: calc(100% - 250px);
        }

        .nav-link i {
            margin-right: 10px;
        }

        .logout-btn {
            margin-top: 2rem;
            padding-left: 20px;
            padding-right: 20px;
        }

        /* Google Translate Cleanup */
        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0 !important; }
        .goog-logo-link, .goog-te-gadget span { display: none !important; }
        #google_translate_element { font-size: 0; }


        /* Hide Google Translate top banner */
.goog-te-banner-frame.skiptranslate {
    display: none !important;
}

/* Prevent shifting of the page */
body {
    top: 0px !important;
}

/* Hide Google Translate toolbar */
.goog-te-gadget-icon,
.goog-te-gadget-simple,
.goog-te-gadget span,
.goog-te-banner-frame,
#goog-gt-tt,
.goog-tooltip,
.activity-root,
.status-message,
#google_translate_element2 {
    display: none !important;
    visibility: hidden !important;
}

/* Remove white bar below header */
iframe.skiptranslate {
    display: none !important;
}

    </style>
</head>
<body>

<!-- Google Translate Hidden Widget -->
<div id="google_translate_element" style="display: none;"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <!-- Header with Language Toggle -->
        <div class="d-flex justify-content-between align-items-center px-3 mb-3">
            <h4 class="text-white mb-0">Admin Panel</h4>
            <button onclick="toggleLanguage()" class="btn btn-sm btn-outline-light" title="Switch Language">
    <i class="bi bi-translate"></i>
</button>

        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.institutes.index') }}" class="nav-link {{ request()->routeIs('admin.institutes.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Institutes
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.programs.index') }}" class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                    <i class="bi bi-book"></i> Program
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-code"></i> Courses
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.academic_sessions.index') }}" class="nav-link {{ request()->routeIs('admin.academic_sessions.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Sessions
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.examination.index') }}" class="nav-link {{ request()->routeIs('admin.examination.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i> Examination
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reappears.index') }}" class="nav-link {{ request()->routeIs('admin.reappears.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat"></i> Reappear
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.messages.index') }}" class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-paper"></i> Messages
                    @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadMessageCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <!-- Logout button -->
        <div class="logout-btn mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
</div>

@stack('scripts')

<!-- Clear Cache Button -->
<form action="{{ route('admin.clear.cache') }}" method="POST" onsubmit="return confirm('Clear all caches?')" 
      class="position-fixed bottom-0 end-0 m-3" style="z-index: 1050;">
    @csrf
    <button class="btn btn-warning rounded-circle p-2 d-flex align-items-center justify-content-center" 
            style="width: 36px; height: 36px;" title="Clear Cache">
        <i class="bi bi-arrow-clockwise fs-6"></i>
    </button>
</form>

<!-- Google Translate Script -->
<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement(
            { pageLanguage: 'en', includedLanguages: 'en,hi' },
            'google_translate_element'
        );
    }

    function toggleLanguage() {
        const select = document.querySelector("select.goog-te-combo");
        if (select) {
            select.value = select.value === 'hi' ? 'en' : 'hi';
            select.dispatchEvent(new Event('change'));
        }
    }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
