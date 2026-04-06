<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') - Nyuci.id@else Nyuci.id@endif</title>
    <link rel="icon" type="image/x-icon" href="{{ url('/storage/icon_blue.ico') }}">
    <link rel="shortcut icon" href="{{ url('/storage/icon_blue.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --nyuci-bg: #020617;
            --nyuci-surface: #0b1220;
            --nyuci-border: #1e293b;
            --nyuci-text: #e2e8f0;
            --nyuci-muted: #94a3b8;
            --nyuci-accent: #3b82f6;
            --nyuci-accent-soft: #111827;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.22), transparent 28rem),
                var(--nyuci-bg);
            color: var(--nyuci-text);
        }

        .navbar-shell {
            background: rgba(2, 6, 23, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(30, 41, 59, 0.9);
        }

        .brand-logo {
            width: 2.4rem;
            height: 2.4rem;
            object-fit: contain;
        }

        .card,
        .surface-card {
            border: 1px solid var(--nyuci-border);
            border-radius: 1.2rem;
            background: var(--nyuci-surface);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .btn {
            border-radius: 999px;
            font-weight: 600;
            padding-inline: 1rem;
        }

        .btn-accent {
            background: var(--nyuci-accent);
            border-color: var(--nyuci-accent);
            color: #fff;
        }

        .btn-accent:hover,
        .btn-accent:focus {
            background: #0b5d57;
            border-color: #0b5d57;
            color: #fff;
        }

        .btn-soft {
            background: var(--nyuci-accent-soft);
            border-color: var(--nyuci-border);
            color: var(--nyuci-text);
        }

        .table {
            overflow: hidden;
            border-radius: 1rem;
        }

        .alert {
            border: 0;
            border-radius: 1rem;
        }

        .alert-success {
            background: #111827;
            color: #93c5fd;
        }

        .alert-danger {
            background: #3f1d24;
            color: #fecaca;
        }

        footer {
            color: var(--nyuci-muted);
            border-color: var(--nyuci-border) !important;
            background: rgba(11, 18, 32, 0.7);
        }

        .navbar .nav-link {
            color: var(--nyuci-muted);
        }

        .navbar .nav-link.active,
        .navbar .nav-link:hover {
            color: #93c5fd;
        }

        .text-secondary {
            color: var(--nyuci-muted) !important;
        }
    </style>
    @yield('extra_css')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-shell sticky-top">
        <div class="container py-2">
            <a class="navbar-brand d-flex align-items-center gap-3 fw-semibold mb-0" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
                <img src="{{ url('/storage/icon.white.png') }}" alt="Nyuci.id Logo" class="brand-logo">
                <span>
                    <span class="d-block text-light">Nyuci.id</span>
                    <small class="text-secondary fw-normal">Laundry digital yang rapi</small>
                    </span>
                </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                    <ul class="navbar-nav ms-auto me-lg-3 gap-lg-1">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('laundry.*') ? 'active fw-semibold' : '' }}" href="{{ route('laundry.index') }}">Laundry</a>
                        </li>

                    </ul>

                    <div class="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
                        <a href="{{ route('profile.edit') }}" class="btn btn-soft">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-accent">Keluar</button>
                        </form>
                    </div>
                @else
                    <div class="ms-auto d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-soft">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-accent">Daftar</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container py-4 py-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi kesalahan.</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="border-top py-4">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <p class="mb-0">Nyuci.id membantu operasional laundry tetap singkat dan jelas.</p>
            <small>&copy; 2026 Nyuci.id</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('extra_js')
</body>
</html>
