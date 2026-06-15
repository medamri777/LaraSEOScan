<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Legal - Seo4ma')</title>
    <meta name="description" content="@yield('meta_description', 'Seo4ma Legal Information')">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
</head>
<body class="antialiased d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="{{ url('/') }}">
                <i class="bi bi-bar-chart-fill me-2 fs-4"></i> Seo4ma
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ url('/') }}#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ url('/') }}#how-it-works">How It Works</a>
                    </li>
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item ms-lg-3">
                                <a href="{{ route('dashboard') }}" class="btn btn-primary-gradient shadow-sm">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                        @else
                            <li class="nav-item ms-lg-3">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary px-4 me-2 rounded-pill">Log in</a>
                            </li>
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <a class="navbar-brand fw-bold text-primary d-flex align-items-center mb-3" href="{{ url('/') }}">
                        <i class="bi bi-bar-chart-fill me-2 fs-4"></i> Seo4ma
                    </a>
                    <p class="text-muted small">
                        Seo4ma is an open-source SEO crawling tool designed for modern web development teams.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <a href="{{ url('/') }}#features" class="footer-link">Features</a>
                    <a href="{{ url('/') }}#how-it-works" class="footer-link">How it Works</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3">Resources</h6>
                    <a href="#" class="footer-link">Documentation</a>
                    <a href="#" class="footer-link">Community</a>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <a href="{{ route('legal.privacy') }}" class="footer-link">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="footer-link">Terms of Service</a>
                    <a href="{{ route('legal.cookies') }}" class="footer-link">Cookie Policy</a>
                </div>
            </div>
             <div class="border-top mt-5 pt-4 text-center text-muted small">
                &copy; {{ date('Y') }} Seo4ma. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
