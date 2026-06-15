<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Seo4ma') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 (same as landing page) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand: #10b981;
            --brand-dark: #059669;
            --brand-light: #ecfdf5;
            --dark: #111827;
        }
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #ffffff 100%);
            min-height: 100vh;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(16, 185, 129, 0.08), 0 1px 4px rgba(0,0,0,0.04);
            border: 1px solid rgba(16, 185, 129, 0.12);
        }
        .brand-logo {
            color: var(--brand);
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
        }
        .brand-logo:hover { color: var(--brand-dark); }
        .btn-brand {
            background: var(--brand);
            color: #fff;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            transition: all 0.2s;
        }
        .btn-brand:hover {
            background: var(--brand-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-google {
            background: #fff;
            color: #374151;
            border: 1.5px solid #e5e7eb;
            font-weight: 500;
            border-radius: 10px;
            padding: 10px 20px;
            transition: all 0.2s;
        }
        .btn-google:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .divider {
            display: flex;
            align-items: center;
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        .divider span { padding: 0 12px; }
        .form-label { font-weight: 500; color: #374151; font-size: 0.9rem; }
        .auth-link { color: var(--brand); text-decoration: none; font-weight: 500; }
        .auth-link:hover { color: var(--brand-dark); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center py-4 px-3">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="brand-logo d-flex align-items-center mb-4">
            <i class="bi bi-bar-chart-fill me-2 fs-4"></i> Seo4ma
        </a>

        <!-- Auth Card -->
        <div class="auth-card w-100" style="max-width: 440px;">
            <div class="p-4 p-sm-5">
                {{ $slot }}
            </div>
        </div>

        <!-- Footer links -->
        <div class="mt-4 text-center" style="font-size: 0.82rem;">
            <a href="{{ route('legal.privacy') }}" class="text-muted text-decoration-none mx-2">Privacy</a>
            <span class="text-muted">·</span>
            <a href="{{ route('legal.terms') }}" class="text-muted text-decoration-none mx-2">Terms</a>
            <span class="text-muted">·</span>
            <a href="{{ route('legal.cookies') }}" class="text-muted text-decoration-none mx-2">Cookies</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
