<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GscConnection;
use App\Models\Project;
use App\Services\GoogleSearchConsoleService;
use App\Support\PlanLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace. Complete onboarding first.'], 403);
        }

        $projects = Project::where('tenant_id', $user->tenant_id)
            ->withCount('scans')
            ->latest()
            ->paginate(20);

        return response()->json(['projects' => $projects]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            return response()->json(['message' => 'No workspace. Complete onboarding first.'], 403);
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'url'         => ['required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Plan limit: projects per tenant
        $plan  = $user->tenant->plan ?? 'free';
        $limit = PlanLimits::projectLimit($plan);

        if ($limit !== null) {
            $current = Project::where('tenant_id', $user->tenant_id)->count();
            if ($current >= $limit) {
                return response()->json(
                    PlanLimits::limitResponse('projects', $limit, $current, $plan),
                    402
                );
            }
        }

        $project = Project::create([
            'tenant_id'   => $user->tenant_id,
            'name'        => $validated['name'],
            'url'         => $validated['url'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Project created successfully.',
            'project' => $this->formatProject($project),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $project = $this->authorizedProject($request, $id);

        $scans = $project->scans()
            ->latest()
            ->paginate(10);

        $stats = [
            'total'     => $project->scans()->count(),
            'completed' => $project->scans()->where('status', 'COMPLETED')->count(),
            'pending'   => $project->scans()->where('status', '!=', 'COMPLETED')->count(),
        ];

        // GSC connection status
        $gscConnection = GscConnection::where('project_id', $project->id)->first();
        $gscStatus = [
            'connected'     => $gscConnection !== null,
            'property_url'  => $gscConnection?->property_url,
            'last_sync_at'  => $gscConnection?->last_sync_at,
            'connection_id' => $gscConnection?->id,
        ];

        return response()->json([
            'project'    => $this->formatProject($project),
            'scans'      => $scans,
            'stats'      => $stats,
            'gsc_status' => $gscStatus,
        ]);
    }

    /**
     * Get GSC dashboard data for a project (performance summary, top queries, top pages).
     */
    public function searchConsoleStats(Request $request, int $id, GoogleSearchConsoleService $gsc): JsonResponse
    {
        $project = $this->authorizedProject($request, $id);

        $connection = GscConnection::where('project_id', $project->id)->first();
        if (! $connection) {
            return response()->json([
                'connected' => false,
                'message'   => 'Google Search Console not connected for this project.',
            ]);
        }

        $days = (int) $request->input('days', 28);
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();

        try {
            // Daily performance (clicks, impressions, CTR, position over time)
            $performance = $gsc->getDailyPerformance($connection, $startDate, $endDate);

            // Top 10 queries
            $topQueries = $gsc->getTopQueries($connection, 10, $startDate, $endDate);

            // Top 10 pages
            $topPages = $gsc->getTopPages($connection, 10, $startDate, $endDate);

            // Calculate totals from daily performance
            $totalClicks = 0;
            $totalImpressions = 0;
            $positionSum = 0;
            foreach ($performance['rows'] ?? [] as $row) {
                $totalClicks += $row['clicks'];
                $totalImpressions += $row['impressions'];
                $positionSum += $row['position'];
            }
            $dayCount = count($performance['rows'] ?? []);

            $connection->update(['last_sync_at' => now()]);

            return response()->json([
                'connected'        => true,
                'property_url'     => $connection->property_url,
                'connection_id'    => $connection->id,
                'period'           => ['start' => $startDate, 'end' => $endDate, 'days' => $days],
                'summary'          => [
                    'total_clicks'      => $totalClicks,
                    'total_impressions' => $totalImpressions,
                    'avg_ctr'           => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0,
                    'avg_position'      => $dayCount > 0 ? round($positionSum / $dayCount, 1) : 0,
                ],
                'daily_performance' => $performance['rows'] ?? [],
                'top_queries'       => $topQueries['rows'] ?? [],
                'top_pages'         => $topPages['rows'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('GSC dashboard error', ['project' => $project->id, 'error' => $e->getMessage()]);
            return response()->json([
                'connected' => true,
                'error'     => 'Failed to fetch Search Console data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $project = $this->authorizedProject($request, $id);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'url'         => ['sometimes', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated.',
            'project' => $this->formatProject($project->fresh()),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $project = $this->authorizedProject($request, $id);
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }

    private function authorizedProject(Request $request, int $id): Project
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            abort(403, 'No workspace.');
        }

        return Project::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();
    }

    private function formatProject(Project $project): array
    {
        return [
            'id'          => $project->id,
            'tenant_id'   => $project->tenant_id,
            'name'        => $project->name,
            'url'         => $project->url,
            'description' => $project->description,
            'scans_count' => $project->scans()->count(),
            'created_at'  => $project->created_at,
            'updated_at'  => $project->updated_at,
        ];
    }
}
