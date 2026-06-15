<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GscConnection;
use App\Models\Project;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchConsoleController extends Controller
{
    public function __construct(protected GoogleSearchConsoleService $gsc)
    {
    }

    /**
     * Get the Google OAuth authorization URL for connecting a project.
     */
    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $state = encrypt([
            'project_id' => $project->id,
            'user_id'    => $request->user()->id,
        ]);

        $authUrl = $this->gsc->getAuthUrl() . '&state=' . urlencode($state);

        return response()->json(['auth_url' => $authUrl]);
    }

    /**
     * Handle the OAuth callback from Google.
     */
    public function callback(Request $request): JsonResponse
    {
        $request->validate([
            'code'  => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $state = decrypt($request->state);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid state parameter'], 400);
        }

        $projectId = $state['project_id'] ?? null;
        $project = Project::findOrFail($projectId);

        try {
            $tokenData = $this->gsc->authenticate($request->code);
        } catch (\Exception $e) {
            Log::error('GSC OAuth error: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 400);
        }

        // List properties the user has access to
        $properties = [];
        try {
            $tempConnection = new GscConnection(array_merge($tokenData, [
                'project_id'   => $project->id,
                'property_url' => 'https://example.com',
            ]));
            $properties = $this->gsc->listProperties($tempConnection);
        } catch (\Exception $e) {
            Log::warning('Could not list GSC properties: ' . $e->getMessage());
        }

        return response()->json([
            'message'    => 'Connected. Select a property to link.',
            'properties' => $properties,
            'token_data' => $tokenData,
            'project_id' => $project->id,
        ]);
    }

    /**
     * Store the GSC connection after user selects a property.
     */
    public function storeConnection(Request $request): JsonResponse
    {
        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'property_url'  => 'required|string',
            'access_token'  => 'required|string',
            'refresh_token' => 'required|string',
            'expires_in'    => 'required|integer',
        ]);

        $project = Project::where('id', $request->project_id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $connection = GscConnection::updateOrCreate(
            ['project_id' => $project->id, 'property_url' => $request->property_url],
            [
                'access_token'     => $request->access_token,
                'refresh_token'    => $request->refresh_token,
                'expires_in'       => $request->expires_in,
                'token_expires_at' => now()->addSeconds($request->expires_in),
            ]
        );

        return response()->json([
            'message'    => 'Search Console property linked successfully.',
            'connection' => [
                'id'           => $connection->id,
                'property_url' => $connection->property_url,
                'project_id'   => $connection->project_id,
            ],
        ], 201);
    }

    /**
     * Check GSC connection status for a project.
     */
    public function status(Request $request, int $projectId): JsonResponse
    {
        $project = Project::where('id', $projectId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $connections = GscConnection::where('project_id', $project->id)->get();

        return response()->json([
            'connected'   => $connections->isNotEmpty(),
            'connections' => $connections->map(fn ($c) => [
                'id'            => $c->id,
                'property_url'  => $c->property_url,
                'last_sync_at'  => $c->last_sync_at,
                'token_expired' => $c->isTokenExpired(),
            ]),
        ]);
    }

    /**
     * Disconnect a GSC property.
     */
    public function disconnect(Request $request, int $connectionId): JsonResponse
    {
        $connection = GscConnection::whereHas('project', function ($q) use ($request) {
            $q->where('tenant_id', $request->user()->tenant_id);
        })->findOrFail($connectionId);

        $connection->delete();

        return response()->json(['message' => 'Search Console disconnected.']);
    }

    /**
     * Get top search queries.
     */
    public function topQueries(Request $request, int $connectionId): JsonResponse
    {
        $connection = $this->getConnection($connectionId, $request);

        $data = $this->gsc->getTopQueries(
            $connection,
            $request->input('limit', 50),
            $request->input('start_date'),
            $request->input('end_date')
        );

        $connection->update(['last_sync_at' => now()]);

        return response()->json($data);
    }

    /**
     * Get top pages by clicks.
     */
    public function topPages(Request $request, int $connectionId): JsonResponse
    {
        $connection = $this->getConnection($connectionId, $request);

        $data = $this->gsc->getTopPages(
            $connection,
            $request->input('limit', 50),
            $request->input('start_date'),
            $request->input('end_date')
        );

        $connection->update(['last_sync_at' => now()]);

        return response()->json($data);
    }

    /**
     * Get daily performance data (clicks, impressions, CTR, position over time).
     */
    public function dailyPerformance(Request $request, int $connectionId): JsonResponse
    {
        $connection = $this->getConnection($connectionId, $request);

        $data = $this->gsc->getDailyPerformance(
            $connection,
            $request->input('start_date'),
            $request->input('end_date')
        );

        $connection->update(['last_sync_at' => now()]);

        return response()->json($data);
    }

    /**
     * Custom search analytics query with full filter support.
     */
    public function searchAnalytics(Request $request, int $connectionId): JsonResponse
    {
        $connection = $this->getConnection($connectionId, $request);

        $request->validate([
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date|after_or_equal:start_date',
            'dimensions' => 'sometimes|array',
            'row_limit'  => 'sometimes|integer|min:1|max:25000',
            'type'       => 'sometimes|string|in:web,image,video,news,discover,googleNews,WEB,IMAGE,VIDEO,NEWS,DISCOVER,GOOGLE_NEWS',
        ]);

        $data = $this->gsc->getSearchAnalytics($connection, $request->only([
            'start_date', 'end_date', 'dimensions', 'row_limit', 'type',
        ]));

        $connection->update(['last_sync_at' => now()]);

        return response()->json($data);
    }

    /**
     * Inspect a specific URL's indexing status.
     */
    public function inspectUrl(Request $request, int $connectionId): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $connection = $this->getConnection($connectionId, $request);

        try {
            $data = $this->gsc->inspectUrl($connection, $request->url);
            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error('GSC URL inspection error: ' . $e->getMessage());
            return response()->json(['error' => 'URL inspection failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List sitemaps submitted to Google.
     */
    public function sitemaps(Request $request, int $connectionId): JsonResponse
    {
        $connection = $this->getConnection($connectionId, $request);

        try {
            $data = $this->gsc->listSitemaps($connection);
            return response()->json(['sitemaps' => $data]);
        } catch (\Throwable $e) {
            Log::error('GSC sitemaps error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to list sitemaps: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit a sitemap to Google.
     */
    public function submitSitemap(Request $request, int $connectionId): JsonResponse
    {
        $request->validate([
            'sitemap_url' => 'required|url',
        ]);

        $connection = $this->getConnection($connectionId, $request);

        try {
            $this->gsc->submitSitemap($connection, $request->sitemap_url);
            return response()->json(['message' => 'Sitemap submitted successfully to Google.']);
        } catch (\Throwable $e) {
            Log::error('GSC sitemap submit error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to submit sitemap: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a sitemap from Google.
     */
    public function deleteSitemap(Request $request, int $connectionId): JsonResponse
    {
        $request->validate([
            'sitemap_url' => 'required|url',
        ]);

        $connection = $this->getConnection($connectionId, $request);

        try {
            $this->gsc->deleteSitemap($connection, $request->sitemap_url);
            return response()->json(['message' => 'Sitemap removed from Google.']);
        } catch (\Throwable $e) {
            Log::error('GSC sitemap delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete sitemap: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Resolve a tenant-scoped GSC connection.
     */
    protected function getConnection(int $connectionId, Request $request): GscConnection
    {
        return GscConnection::whereHas('project', function ($q) use ($request) {
            $q->where('tenant_id', $request->user()->tenant_id);
        })->findOrFail($connectionId);
    }
}
