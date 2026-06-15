<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['tenant' => null, 'needs_onboarding' => true]);
        }

        $tenant = $user->tenant->load('projects');

        return response()->json([
            'tenant'           => $this->formatTenant($tenant),
            'needs_onboarding' => false,
            'role'             => $user->tenant_role,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTenant()) {
            return response()->json(['message' => 'You already belong to a workspace.'], 409);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant = Tenant::create(['name' => $validated['name']]);

        $user->update([
            'tenant_id'   => $tenant->id,
            'tenant_role' => 'owner',
        ]);

        return response()->json([
            'message' => 'Workspace created.',
            'tenant'  => $this->formatTenant($tenant),
            'role'    => 'owner',
        ], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }

        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only the workspace owner can update it.'], 403);
        }

        $validated = $request->validate([
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'agency_name'     => ['nullable', 'string', 'max:255'],
            'agency_website'  => ['nullable', 'url', 'max:2048'],
            'primary_color'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $user->tenant->update($validated);

        return response()->json([
            'message' => 'Workspace updated.',
            'tenant'  => $this->formatTenant($user->tenant->fresh()),
        ]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }

        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only the workspace owner can upload a logo.'], 403);
        }

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,gif,webp,svg', 'max:2048'],
        ]);

        $tenant = $user->tenant;

        // Delete previous logo if it exists
        if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        $path = $request->file('logo')->store("logos/{$tenant->id}", 'public');

        $tenant->update(['logo_path' => $path]);

        return response()->json([
            'message'  => 'Logo uploaded successfully.',
            'logo_url' => $tenant->fresh()->logo_url,
        ]);
    }

    public function deleteLogo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }

        if (! $user->isOwner()) {
            return response()->json(['message' => 'Only the workspace owner can remove the logo.'], 403);
        }

        $tenant = $user->tenant;

        if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        $tenant->update(['logo_path' => null]);

        return response()->json(['message' => 'Logo removed.']);
    }

    public function members(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace found.'], 404);
        }

        $members = $user->tenant->users()
            ->select(['id', 'name', 'email', 'tenant_role', 'created_at'])
            ->orderByRaw("CASE WHEN tenant_role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id'           => $m->id,
                'name'         => $m->name,
                'email'        => $m->email,
                'tenant_role'  => $m->tenant_role,
                'is_you'       => $m->id === $user->id,
                'joined_at'    => $m->created_at,
            ]);

        return response()->json(['members' => $members]);
    }

    private function formatTenant(Tenant $tenant): array
    {
        return [
            'id'                 => $tenant->id,
            'name'               => $tenant->name,
            'slug'               => $tenant->slug,
            'plan'               => $tenant->plan,
            'scan_limit_per_day' => $tenant->scan_limit_per_day,
            'agency_name'        => $tenant->agency_name,
            'agency_website'     => $tenant->agency_website,
            'primary_color'      => $tenant->primary_color ?? '#3B82F6',
            'logo_url'           => $tenant->logo_url,
            'has_logo'           => ! is_null($tenant->logo_path),
            'projects_count'     => $tenant->projects()->count(),
            'created_at'         => $tenant->created_at,
        ];
    }
}
