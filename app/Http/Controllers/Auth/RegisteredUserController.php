<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:100'],
        ], [
            'phone.regex' => 'The phone number must contain only digits, +, spaces, dashes, or parentheses.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? null,
            'company' => $request->company ?? null,
            'role' => $request->role ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Create tenant for new user
        $tenant = Tenant::create([
            'name' => $user->name . "'s Workspace",
            'plan' => 'free',
            'scan_limit_per_day' => 5,
        ]);
        $user->update([
            'tenant_id' => $tenant->id,
            'tenant_role' => 'owner',
        ]);

        // Pending domain from landing page? Create project and go to hub
        $pendingDomain = session('pending_domain');
        if ($pendingDomain) {
            session()->forget('pending_domain');
            return app(ProjectController::class)->findOrCreateAndRedirect($pendingDomain);
        }

        // No pending domain — go to projects index (with "enter domain" prompt)
        return redirect()->route('projects.index');
    }
}
