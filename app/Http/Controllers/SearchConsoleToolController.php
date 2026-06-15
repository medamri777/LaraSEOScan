<?php

namespace App\Http\Controllers;

use App\Models\GscConnection;
use App\Models\Project;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchConsoleToolController extends Controller
{
    public function __construct(protected GoogleSearchConsoleService $gsc)
    {
    }

    public function index(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'search_console')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade your plan to access Google Search Console.');
        }

        $user = auth()->user();
        $projects = [];
        $selectedProject = null;
        $connection = null;
        $performanceData = null;
        $topQueries = null;
        $topPages = null;
        $inspectionResult = null;
        $sitemaps = null;
        $error = null;

        if ($user && $user->tenant_id) {
            $projects = Project::where('tenant_id', $user->tenant_id)->get();
        }

        $projectId = $request->input('project_id');
        if ($projectId && $projects) {
            $selectedProject = $projects->firstWhere('id', $projectId);
        }

        if ($selectedProject) {
            $connection = GscConnection::where('project_id', $selectedProject->id)->first();
        }

        $days = (int) $request->input('days', 28);
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();
        $searchType = $request->input('search_type', 'web');

        // Fetch data if connected
        if ($connection) {
            try {
                $performanceData = $this->gsc->getDailyPerformance($connection, $startDate, $endDate);
                $topQueries = $this->gsc->getTopQueries($connection, 25, $startDate, $endDate);
                $topPages = $this->gsc->getTopPages($connection, 25, $startDate, $endDate);
                $connection->update(['last_sync_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('GSC tool data error: ' . $e->getMessage());
                $error = 'Failed to fetch Search Console data: ' . $e->getMessage();
            }
        }

        // URL inspection (if requested)
        if ($request->has('inspect_url') && $connection) {
            try {
                $inspectionResult = $this->gsc->inspectUrl($connection, $request->input('inspect_url'));
            } catch (\Throwable $e) {
                Log::error('GSC URL inspection error: ' . $e->getMessage());
                $error = 'URL inspection failed: ' . $e->getMessage();
            }
        }

        // Sitemap list (if requested)
        if ($request->has('show_sitemaps') && $connection) {
            try {
                $sitemaps = $this->gsc->listSitemaps($connection);
            } catch (\Throwable $e) {
                Log::error('GSC sitemaps error: ' . $e->getMessage());
                $error = 'Failed to list sitemaps: ' . $e->getMessage();
            }
        }

        return view('tools.search-console', compact(
            'projects', 'selectedProject', 'connection', 'performanceData',
            'topQueries', 'topPages', 'inspectionResult', 'sitemaps',
            'error', 'days', 'searchType'
        ));
    }

    /**
     * AJAX: Start OAuth flow for connecting GSC.
     */
    public function startConnect(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'search_console')) {
            return response()->json(['error' => 'Please upgrade your plan to connect Google Search Console.'], 403);
        }

        $request->validate(['project_id' => 'required|exists:projects,id']);

        $user = auth()->user();
        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $state = encrypt([
            'project_id' => $project->id,
            'user_id'    => $user->id,
        ]);

        $authUrl = $this->gsc->getAuthUrl() . '&state=' . urlencode($state);

        return response()->json(['auth_url' => $authUrl]);
    }

    /**
     * AJAX: Handle OAuth callback, return properties list.
     */
    public function handleCallback(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $state = decrypt($request->state);
            $projectId = $state['project_id'];
            $project = Project::findOrFail($projectId);

            $tokenData = $this->gsc->authenticate($request->code);

            // List properties
            $tempConnection = new GscConnection(array_merge($tokenData, [
                'project_id'   => $project->id,
                'property_url' => 'https://example.com',
            ]));
            $properties = $this->gsc->listProperties($tempConnection);

            return response()->json([
                'properties' => $properties,
                'token_data' => $tokenData,
                'project_id' => $project->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('GSC callback error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: Store the selected GSC property connection.
     */
    public function storeProperty(Request $request)
    {
        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'property_url'  => 'required|string',
            'access_token'  => 'required|string',
            'refresh_token' => 'nullable|string',
            'expires_in'    => 'required|integer',
        ]);

        $user = auth()->user();
        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $dataToUpdate = [
            'access_token'     => $request->access_token,
            'expires_in'       => $request->expires_in,
            'token_expires_at' => now()->addSeconds($request->expires_in),
        ];

        if ($request->refresh_token && $request->refresh_token !== 'null') {
            $dataToUpdate['refresh_token'] = $request->refresh_token;
        }

        $connection = GscConnection::updateOrCreate(
            ['project_id' => $project->id, 'property_url' => $request->property_url],
            $dataToUpdate
        );

        return response()->json([
            'message'      => 'Search Console connected!',
            'property_url' => $connection->property_url,
        ]);
    }

    /**
     * AJAX: Submit a sitemap to Google.
     */
    public function submitSitemap(Request $request)
    {
        $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'sitemap_url' => 'required|url',
        ]);

        $user = auth()->user();
        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $connection = GscConnection::where('project_id', $project->id)->firstOrFail();

        try {
            $this->gsc->submitSitemap($connection, $request->sitemap_url);
            return response()->json(['message' => 'Sitemap submitted to Google!']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Disconnect GSC.
     */
    public function disconnect(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $user = auth()->user();
        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        GscConnection::where('project_id', $project->id)->delete();

        return response()->json(['message' => 'Disconnected.']);
    }
}
