<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', 'Google authentication failed. Please try again.');
        }

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Link Google account if not already linked
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            // Create new user
            $user = User::create([
                'name'       => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
                'email'      => $googleUser->getEmail(),
                'google_id'  => $googleUser->getId(),
                'password'   => bcrypt(str()->random(24)),
                'email_verified_at' => now(),
            ]);

            // Create tenant
            $tenant = Tenant::create([
                'name'               => $user->name . "'s Workspace",
                'plan'               => 'free',
                'scan_limit_per_day' => 5,
            ]);
            $user->update([
                'tenant_id'   => $tenant->id,
                'tenant_role' => 'owner',
            ]);
        }

        Auth::login($user, true);

        // Pending domain from landing page?
        $pendingDomain = session('pending_domain');
        if ($pendingDomain) {
            session()->forget('pending_domain');
            return app(ProjectController::class)->findOrCreateAndRedirect($pendingDomain);
        }

        return redirect()->route('projects.index');
    }
}
