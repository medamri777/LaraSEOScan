<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1" style="color: #111827;">Create your account</h4>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Start your free SEO audit in 30 seconds</p>
    </div>

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
        Sign up with Google
    </a>

    <!-- Divider -->
    <div class="divider my-3">
        <span>or register with email</span>
    </div>

    <!-- Register Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                   placeholder="Ahmed Ben Ali" required autofocus autocomplete="name"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                   placeholder="you@example.com" required autocomplete="username"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <label for="phone" class="form-label">Phone <span class="text-muted fw-normal">(optional)</span></label>
                <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="+212 6..." autocomplete="tel"
                       pattern="[+]?[0-9\s\-\(\)]+"
                       inputmode="tel"
                       oninput="this.value = this.value.replace(/[^0-9+\s\-\(\)]/g, '')"
                       style="border-radius: 10px; padding: 10px 14px;">
                <small class="text-muted" style="font-size: 0.75rem;">Numbers only</small>
            </div>
            <div class="col-6">
                <label for="company" class="form-label">Company <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" class="form-control" id="company" name="company" value="{{ old('company') }}"
                       placeholder="Your company"
                       style="border-radius: 10px; padding: 10px 14px;">
            </div>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Your role <span class="text-muted fw-normal">(optional)</span></label>
            <select id="role" name="role" class="form-select" style="border-radius: 10px; padding: 10px 14px;">
                <option value="">-- Select Role --</option>
                <option value="Developer" {{ old('role') == 'Developer' ? 'selected' : '' }}>Developer</option>
                <option value="Marketer" {{ old('role') == 'Marketer' ? 'selected' : '' }}>Marketer</option>
                <option value="SEO Analyst" {{ old('role') == 'SEO Analyst' ? 'selected' : '' }}>SEO Analyst</option>
                <option value="Business Owner" {{ old('role') == 'Business Owner' ? 'selected' : '' }}>Business Owner</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Min. 8 characters" required autocomplete="new-password"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                   placeholder="Repeat your password" required autocomplete="new-password"
                   style="border-radius: 10px; padding: 10px 14px;">
        </div>

        <button type="submit" class="btn btn-brand w-100 mt-2">
            Create Account <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.88rem;">
        Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a>
    </p>
</x-guest-layout>
