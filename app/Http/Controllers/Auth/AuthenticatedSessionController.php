<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // 1. Pending domain from landing page? Create/find project and go to hub
        $pendingDomain = session('pending_domain');
        if ($pendingDomain) {
            session()->forget('pending_domain');
            return app(ProjectController::class)->findOrCreateAndRedirect($pendingDomain);
        }

        // 2. Has projects? Go to scan history
        if ($user->hasTenant()) {
            $hasProjects = Project::where('tenant_id', $user->tenant_id)->exists();
            if ($hasProjects) {
                return redirect()->route('dashboard');
            }
        }

        // 3. No projects — go to projects index to create one
        return redirect()->route('projects.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
