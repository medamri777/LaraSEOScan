<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tenant;
use App\Support\PlanLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Handle domain entry from landing page (SEMrush-style).
     * - If authenticated: find or create project, redirect to project hub
     * - If guest: store domain in session, redirect to login
     */
    public function domainEntry(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $url = $validated['url'];

        // Normalize: prepend https:// if no scheme present
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        // Re-validate as proper URL
        $validator = \Illuminate\Support\Facades\Validator::make(['url' => $url], [
            'url' => ['required', 'url', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors(['url' => 'Please enter a valid website URL.'])->withInput();
        }

        // Guest flow: store in session, redirect to login
        if (!Auth::check()) {
            session(['pending_domain' => $url]);
            return redirect()->route('login');
        }

        // Authenticated flow: find or create project, go to hub
        return $this->findOrCreateAndRedirect($url);
    }

    /**
     * Find existing project by URL or create a new one, then redirect to show.
     */
    public function findOrCreateAndRedirect(string $url)
    {
        $user = Auth::user();

        // Ensure user has a tenant
        if (! $user->hasTenant()) {
            $tenant = Tenant::create([
                'name' => $user->name . "'s Workspace",
                'plan' => 'free',
                'scan_limit_per_day' => 5,
            ]);
            $user->update([
                'tenant_id' => $tenant->id,
                'tenant_role' => 'owner',
            ]);
            $user = $user->fresh();
        }

        // Try to find existing project with same URL
        $project = Project::where('tenant_id', $user->tenant_id)
            ->where('url', $url)
            ->first();

        if (! $project) {
            // Plan limit check
            $plan  = $user->tenant->plan ?? 'free';
            $limit = PlanLimits::projectLimit($plan);
            $current = Project::where('tenant_id', $user->tenant_id)->count();

            if ($limit !== null && $current >= $limit) {
                return redirect()->route('projects.index')->withErrors([
                    'limit' => "You've reached the {$limit} project limit for the " . ucfirst($plan) . " plan. Upgrade to add more."
                ]);
            }

            // Derive name from domain
            $host = parse_url($url, PHP_URL_HOST) ?: $url;
            $host = preg_replace('/^www\./', '', $host);

            $project = Project::create([
                'tenant_id' => $user->tenant_id,
                'name'      => $host,
                'url'       => $url,
            ]);
        }

        // Auto-select this project as the active one for tools
        session(['current_project_id' => $project->id]);

        return redirect()->route('dashboard');
    }

    /**
     * Display a listing of the projects.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Lazy tenant onboarding
        if (! $user->hasTenant()) {
            $tenant = Tenant::create([
                'name' => $user->name . "'s Workspace",
                'plan' => 'free',
                'scan_limit_per_day' => 5,
            ]);
            $user->update([
                'tenant_id' => $tenant->id,
                'tenant_role' => 'owner',
            ]);
            $user = $user->fresh();
        }

        $projects = Project::where('tenant_id', $user->tenant_id)
            ->withCount('scans')
            ->latest()
            ->paginate(20);

        // Get plan constraints
        $plan = $user->tenant->plan ?? 'free';
        $limit = PlanLimits::projectLimit($plan);
        $currentCount = Project::where('tenant_id', $user->tenant_id)->count();

        return view('projects.index', compact('projects', 'limit', 'currentCount', 'plan'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Lazy tenant onboarding just in case
        if (! $user->hasTenant()) {
            $tenant = Tenant::create([
                'name' => $user->name . "'s Workspace",
                'plan' => 'free',
                'scan_limit_per_day' => 5,
            ]);
            $user->update([
                'tenant_id' => $tenant->id,
                'tenant_role' => 'owner',
            ]);
            $user = $user->fresh();
        }

        $validated = $request->validate([
            'name'        => ['nullable', 'string', 'max:255'],
            'url'         => ['required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Auto-derive name from URL if not provided
        if (empty($validated['name'])) {
            $host = parse_url($validated['url'], PHP_URL_HOST) ?: $validated['url'];
            $validated['name'] = preg_replace('/^www\./', '', $host);
        }

        // Plan limit: projects per tenant
        $plan  = $user->tenant->plan ?? 'free';
        $limit = PlanLimits::projectLimit($plan);

        if ($limit !== null) {
            $current = Project::where('tenant_id', $user->tenant_id)->count();
            if ($current >= $limit) {
                return redirect()->route('projects.index')->withErrors([
                    'limit' => "Vous avez atteint la limite de {$limit} projets pour le plan " . ucfirst($plan) . ". Veuillez passer à un plan supérieur."
                ]);
            }
        }

        $project = Project::create([
            'tenant_id'   => $user->tenant_id,
            'name'        => $validated['name'],
            'url'         => $validated['url'],
            'description' => $validated['description'] ?? null,
        ]);

        // Auto-select this project as the active one for tools
        session(['current_project_id' => $project->id]);

        return redirect()->route('dashboard')->with('success', 'Project created successfully!');
    }

    /**
     * Set the current active project in session (used by all tools).
     */
    public function select(Request $request)
    {
        $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $user = Auth::user();

        // Ensure the project belongs to this user's tenant
        $project = Project::where('id', $request->input('project_id'))
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        session(['current_project_id' => $project->id]);

        return redirect()->back()->with('success', "Active project set to: {$project->name}");
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, int $id)
    {
        $user = Auth::user();

        if (! $user->hasTenant()) {
            return redirect()->route('projects.index');
        }

        $project = Project::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $scans = $project->scans()
            ->latest()
            ->paginate(10);

        $stats = [
            'total'     => $project->scans()->count(),
            'completed' => $project->scans()->where('status', 'COMPLETED')->count(),
            'pending'   => $project->scans()->where('status', '!=', 'COMPLETED')->count(),
        ];

        // Fetch keyword count and competitor count for quick stats
        $keywordsCount = $project->keywords()->count();
        $competitorsCount = $project->competitors()->count();

        return view('projects.show', compact('project', 'scans', 'stats', 'keywordsCount', 'competitorsCount'));
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Request $request, int $id)
    {
        $user = Auth::user();

        if (! $user->hasTenant()) {
            return redirect()->route('projects.index');
        }

        $project = Project::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Projet supprimé avec succès.');
    }
}
