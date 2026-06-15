<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1" style="color: #111827;">Welcome back</h4>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Sign in to your Seo4ma account</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.9rem; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
            {{ session('status') }}
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.85rem; background: #fef2f2; border-color: #fecaca; color: #991b1b;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Google Button -->
    <a href="{{ route('auth.google') }}" class="btn btn-google w-100 d-flex align-items-center justify-content-center mb-3">
        <svg width="18" height="18" viewBox="0 0 48 48" class="me-2">
            <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.9 33.5 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.2-2.7-.4-3.9z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.1 18.9 12 24 12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
            <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.7-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.5l6.2 5.2C36.7 39.5 44 34 44 24c0-1.3-.2-2.7-.4-3.9z"/>
        </svg>
        Continue with Google
    </a>

    <!-- Divider -->
    <div class="divider my-3">
        <span>or sign in with email</span>
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                   placeholder="you@example.com" required autofocus autocomplete="username"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link" style="font-size: 0.82rem;">Forgot password?</a>
                @endif
            </div>
            <input type="password" class="form-control mt-1" id="password" name="password"
                   placeholder="••••••••" required autocomplete="current-password"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <div class="d-flex align-items-center mb-3">
            <input type="checkbox" class="form-check-input me-2" id="remember_me" name="remember"
                   style="border-color: #d1d5db;">
            <label for="remember_me" class="form-check-label text-muted" style="font-size: 0.88rem;">Remember me</label>
        </div>

        <button type="submit" class="btn btn-brand w-100">
            Sign In <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.88rem;">
        Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create free account</a>
    </p>
</x-guest-layout>
