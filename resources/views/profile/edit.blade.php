@extends('layouts.app')

@section('title', 'Profile Settings - Seo4ma')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 1.5rem;">

    <div class="mb-4">
        <h2 style="font-weight:700;color:#111827;font-size:1.5rem;margin:0;">Profile Settings</h2>
        <p style="color:#6b7280;font-size:.875rem;margin:0.25rem 0 0;">Manage your account settings and preferences.</p>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="max-width:640px;">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="max-width:640px;">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="max-width:640px;">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
