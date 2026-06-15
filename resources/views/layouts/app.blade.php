<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Seo4ma')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CDN (needed by 60+ child views) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Tailwind Play CDN (provides Tailwind classes without build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
                            600: '#059669', 700: '#047857', 800: '#065f46',
                            900: '#064e3b', 950: '#022c22',
                        },
                    },
                },
            },
            corePlugins: { preflight: false },
        }
    </script>

    <!-- Filament-style component classes (works without build) -->
    <style>
        .card-filament { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
        .btn-filament { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; transition: all 0.15s; }
        .btn-filament-primary { background: #10b981; color: #fff; }
        .btn-filament-primary:hover { background: #059669; color: #fff; }
        .btn-filament-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .btn-filament-secondary:hover { background: #e5e7eb; }
        .badge-filament { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; border-radius: 9999px; }

        /* Sidebar: hide on mobile by default, show on desktop */
        @media (max-width: 1023px) {
            .sidebar-mobile-hidden { transform: translateX(-100%); }
        }

        /* Smooth content animation */
        .main-content-area { animation: fadeInUp 0.3s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Override Bootstrap dark defaults for our layout cards */
        .card-filament table { color: #111827; }
        .card-filament table th { background: #f9fafb; color: #6b7280; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #e5e7eb; padding: 0.75rem 1.25rem; }
        .card-filament table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f3f4f6; color: #111827; font-size: 0.875rem; }
        .card-filament table tbody tr:last-child td { border-bottom: 0; }
        .card-filament table tbody tr:hover { background: #f9fafb; }
    </style>

    <!-- ECharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>
    <!-- Custom Dashboard JS -->
    <script src="{{ asset('js/seo-dashboard.js') }}" defer></script>
    <!-- Bootstrap JS (needed by child view modals/dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <!-- Vite (Tailwind + Alpine.js compiled assets) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased" style="font-family: 'Inter', system-ui, -apple-system, sans-serif;"
      x-data="{ sidebarOpen: false, userDropdown: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
         style="display: none;"
         @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    @hasSection('sidebar')
        @yield('sidebar')
    @else
        @include('layouts.sidebar')
    @endif

    {{-- Main wrapper --}}
    <div class="lg:pl-[260px] min-h-screen flex flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 h-16 flex items-center justify-between px-4 md:px-6 bg-white border-b border-gray-200">
            <div class="flex items-center gap-3">
                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                    <i class="bi bi-list text-xl"></i>
                </button>
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-1.5 text-sm">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-emerald-600 transition" style="text-decoration:none;">Dashboard</a>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-3">
                {{-- User dropdown --}}
                <div class="relative">
                    <button @click="userDropdown = !userDropdown"
                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition"
                            style="background: #fff;">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                             style="background: linear-gradient(135deg, #10b981, #34d399);">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="hidden md:inline text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down text-gray-400" style="font-size: 10px;"></i>
                    </button>

                    <div x-show="userDropdown"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         @click.away="userDropdown = false"
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 z-50"
                         style="display: none;">
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                           style="text-decoration: none;">
                            <i class="bi bi-person text-gray-400"></i> Profile
                        </a>
                        <hr class="my-1.5 border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                               style="text-decoration: none;">
                                <i class="bi bi-box-arrow-right text-gray-400"></i> Log Out
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex-1 p-4 md:p-6 main-content-area">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="py-4 px-6 border-t border-gray-200 text-center" style="background: #fff;">
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} Seo4ma. All rights reserved.</p>
            <div class="mt-1 flex items-center justify-center gap-4">
                <a href="{{ route('legal.privacy') }}" class="text-xs text-gray-400 hover:text-emerald-600 transition" style="text-decoration:none;">Privacy</a>
                <a href="{{ route('legal.terms') }}" class="text-xs text-gray-400 hover:text-emerald-600 transition" style="text-decoration:none;">Terms</a>
                <a href="{{ route('legal.cookies') }}" class="text-xs text-gray-400 hover:text-emerald-600 transition" style="text-decoration:none;">Cookies</a>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
