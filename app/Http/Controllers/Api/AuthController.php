<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }

        return response()->json(['message' => __($status)], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        return response()->json(['message' => __($status)], 422);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'        => ['required', 'confirmed', PasswordRules::defaults()],
            'phone'           => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
            'company'         => ['nullable', 'string', 'max:255'],
            'role'            => ['nullable', 'string', 'max:100'],
            'workspace_name'  => ['nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'password'        => Hash::make($validated['password']),
                'phone'           => $validated['phone'] ?? null,
                'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                'whatsapp_opt_in' => $validated['whatsapp_opt_in'] ?? false,
                'company'         => $validated['company'] ?? null,
                'role'            => $validated['role'] ?? null,
            ]);

            // Auto-create a workspace using company name, workspace_name, or user's name
            $workspaceName = $validated['workspace_name']
                ?? $validated['company']
                ?? $validated['name'] . "'s Workspace";

            $tenant = Tenant::create(['name' => $workspaceName]);

            $user->update([
                'tenant_id'   => $tenant->id,
                'tenant_role' => 'owner',
            ]);

            return $user->fresh('tenant');
        });

        $token = $user->createToken('api-token')->plainTextToken;

        event(new Registered($user));

        return response()->json([
            'user'   => $this->formatUser($user),
            'token'  => $token,
        ], 201);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $wantsJson = $request->expectsJson();

        if (! $request->hasValidSignature()) {
            return $wantsJson
                ? response()->json(['error' => 'invalid_signature'], 422)
                : redirect()->route('verification.notice')->withErrors(['email' => 'Invalid verification link.']);
        }

        $user = User::find($id);

        if (! $user || ! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return $wantsJson
                ? response()->json(['error' => 'invalid_hash'], 422)
                : redirect()->route('verification.notice')->withErrors(['email' => 'Invalid verification link.']);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return $wantsJson
            ? response()->json(['success' => true])
            : redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 422);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user  = Auth::user()->load('tenant');
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->formatUser($request->user()->load('tenant'))]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'phone'             => $user->phone,
            'whatsapp_number'   => $user->whatsapp_number,
            'whatsapp_opt_in'   => $user->whatsapp_opt_in,
            'company'           => $user->company,
            'role'              => $user->role,
            'tenant_id'         => $user->tenant_id,
            'tenant_role'       => $user->tenant_role,
            'tenant'            => $user->tenant ? [
                'id'   => $user->tenant->id,
                'name' => $user->tenant->name,
                'slug' => $user->tenant->slug,
                'plan' => $user->tenant->plan,
            ] : null,
            'created_at'  => $user->created_at,
        ];
    }
}
