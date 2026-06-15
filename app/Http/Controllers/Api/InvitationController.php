<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WorkspaceInvitationMail;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class InvitationController extends Controller
{
    // ── Owner-only: send invitation ───────────────────────────────────────────

    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }
        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only workspace owners can invite members.'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role'  => ['sometimes', 'in:member'],
        ]);

        $email  = strtolower($validated['email']);
        $tenant = $user->tenant;

        // Prevent inviting an existing member
        if ($tenant->users()->where('email', $email)->exists()) {
            return response()->json(['message' => 'This person is already a member of the workspace.'], 409);
        }

        // Revoke any existing pending invitation to the same email
        WorkspaceInvitation::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->delete();

        $invitation = WorkspaceInvitation::create([
            'tenant_id'  => $tenant->id,
            'invited_by' => $user->id,
            'email'      => $email,
            'role'       => $validated['role'] ?? 'member',
            'token'      => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);

        $acceptUrl = rtrim(config('app.url'), '/')
            . '/invite/' . $invitation->token;

        // Send the invitation email (falls back to log driver in dev)
        try {
            Mail::to($email)->send(new WorkspaceInvitationMail($invitation->load(['tenant', 'inviter']), $acceptUrl));
        } catch (\Exception $e) {
            // Log the error but don't fail the request — link still works
            logger()->error('Invitation email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message'    => "Invitation sent to {$email}.",
            'invitation' => $this->formatInvitation($invitation->fresh(['tenant', 'inviter'])),
            'accept_url' => $acceptUrl, // returned so admins can share it manually in dev
        ], 201);
    }

    // ── Owner-only: list pending invitations ─────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }
        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only workspace owners can view invitations.'], 403);
        }

        $invitations = WorkspaceInvitation::with(['inviter'])
            ->where('tenant_id', $user->tenant_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($inv) => $this->formatInvitation($inv));

        return response()->json(['invitations' => $invitations]);
    }

    // ── Owner-only: revoke ────────────────────────────────────────────────────

    public function revoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only workspace owners can revoke invitations.'], 403);
        }

        $invitation = WorkspaceInvitation::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }

    // ── Public: preview invitation by token ──────────────────────────────────

    public function preview(string $token): JsonResponse
    {
        $invitation = WorkspaceInvitation::with(['tenant', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return response()->json(['status' => 'already_accepted', 'message' => 'This invitation has already been used.'], 410);
        }

        if ($invitation->isExpired()) {
            return response()->json(['status' => 'expired', 'message' => 'This invitation has expired.'], 410);
        }

        return response()->json([
            'status'       => 'pending',
            'invitation'   => [
                'token'          => $invitation->token,
                'email'          => $invitation->email,
                'role'           => $invitation->role,
                'expires_at'     => $invitation->expires_at,
                'workspace_name' => $invitation->tenant->agency_name ?? $invitation->tenant->name,
                'inviter_name'   => $invitation->inviter->name,
                'logo_url'       => $invitation->tenant->logo_url,
                'primary_color'  => $invitation->tenant->primary_color ?? '#3B82F6',
            ],
        ]);
    }

    // ── Auth: accept invitation (logged-in user must match invited email) ─────

    public function accept(Request $request, string $token): JsonResponse
    {
        $invitation = WorkspaceInvitation::with(['tenant'])
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return response()->json(['message' => 'This invitation has already been accepted.'], 409);
        }

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'This invitation has expired.'], 410);
        }

        // Determine user — either logged in or register via body
        $user = $request->user();

        if ($user) {
            // Logged-in path: email must match
            if (strtolower($user->email) !== strtolower($invitation->email)) {
                return response()->json([
                    'message' => 'You are signed in as a different email address. Please sign in as ' . $invitation->email . ' to accept this invitation.',
                ], 403);
            }
        } else {
            // Guest path: allow register-and-accept with name + password
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'password' => ['required', Password::defaults()],
            ]);

            // Find existing user or create new one
            $user = User::where('email', $invitation->email)->first();

            if (! $user) {
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $invitation->email,
                    'password' => Hash::make($validated['password']),
                ]);
            }
        }

        // Already a member of a different workspace?
        if ($user->hasTenant() && $user->tenant_id !== $invitation->tenant_id) {
            return response()->json(['message' => 'You already belong to a different workspace.'], 409);
        }

        DB::transaction(function () use ($user, $invitation) {
            $user->update([
                'tenant_id'   => $invitation->tenant_id,
                'tenant_role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);
        });

        $user = $user->fresh('tenant');

        // Issue a token for the guest path
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'You have successfully joined the workspace.',
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'tenant_role' => $user->tenant_role,
                'tenant'      => [
                    'id'   => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug,
                    'plan' => $user->tenant->plan,
                ],
            ],
            'token' => $token,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function formatInvitation(WorkspaceInvitation $inv): array
    {
        return [
            'id'           => $inv->id,
            'email'        => $inv->email,
            'role'         => $inv->role,
            'status'       => $inv->isAccepted() ? 'accepted' : ($inv->isExpired() ? 'expired' : 'pending'),
            'expires_at'   => $inv->expires_at,
            'accepted_at'  => $inv->accepted_at,
            'inviter_name' => $inv->inviter?->name,
            'created_at'   => $inv->created_at,
        ];
    }
}
