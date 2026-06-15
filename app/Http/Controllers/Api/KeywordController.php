<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CheckKeywordRankings;
use App\Models\Keyword;
use App\Models\KeywordRanking;
use App\Models\Project;
use App\Models\RankCheckBatch;
use App\Support\PlanLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KeywordController extends Controller
{
    // -------------------------------------------------------------------------
    // Keywords CRUD
    // -------------------------------------------------------------------------

    public function index(Request $request, int $projectId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $keywords = Keyword::forProject($project->id)
            ->with('latestRanking')
            ->orderBy('keyword')
            ->paginate(50);

        return response()->json(['keywords' => $keywords]);
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);
        $user    = $request->user();

        $validated = $request->validate([
            'keywords'      => ['required', 'array', 'min:1', 'max:100'],
            'keywords.*'    => ['required', 'string', 'max:255'],
            'location_code' => ['nullable', 'integer'],
            'language_code' => ['nullable', 'string', 'max:10'],
            'device'        => ['nullable', 'in:desktop,mobile'],
        ]);

        // Plan limit: total active keywords per tenant
        $plan  = $user->hasTenant() ? ($user->tenant->plan ?? 'free') : 'free';
        $limit = PlanLimits::keywordLimit($plan);

        if ($limit !== null) {
            $current = Keyword::where('tenant_id', $user->tenant_id)->count();
            $adding  = count(array_filter(array_map('trim', $validated['keywords'])));
            if ($current + $adding > $limit) {
                return response()->json(
                    PlanLimits::limitResponse('keywords', $limit, $current, $plan),
                    402
                );
            }
        }

        $locationCode = $validated['location_code'] ?? 2504;
        $languageCode = $validated['language_code'] ?? 'fr';
        $device       = $validated['device'] ?? 'desktop';

        $created = [];
        $skipped = [];

        foreach ($validated['keywords'] as $kw) {
            $kw = trim($kw);
            if (empty($kw)) continue;

            $existing = Keyword::withTrashed()
                ->where('project_id', $project->id)
                ->where('keyword', $kw)
                ->where('location_code', $locationCode)
                ->where('language_code', $languageCode)
                ->where('device', $device)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $created[] = $existing;
                } else {
                    $skipped[] = $kw;
                }
                continue;
            }

            $keyword = Keyword::create([
                'project_id'    => $project->id,
                'tenant_id'     => $project->tenant_id,
                'keyword'       => $kw,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'device'        => $device,
            ]);

            $created[] = $keyword;
        }

        return response()->json([
            'message' => count($created) . ' keyword(s) added.',
            'created' => count($created),
            'skipped' => count($skipped),
        ], 201);
    }

    public function destroy(Request $request, int $projectId, int $keywordId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $keyword = Keyword::where('id', $keywordId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $keyword->delete();

        return response()->json(['message' => 'Keyword removed.']);
    }

    // -------------------------------------------------------------------------
    // Rank Checking
    // -------------------------------------------------------------------------

    public function checkRankings(Request $request, int $projectId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $validated = $request->validate([
            'keyword_ids' => ['nullable', 'array'],
            'keyword_ids.*' => ['integer'],
        ]);

        $query = Keyword::active()->forProject($project->id);

        if (! empty($validated['keyword_ids'])) {
            $query->whereIn('id', $validated['keyword_ids']);
        }

        $keywords = $query->get();

        if ($keywords->isEmpty()) {
            return response()->json(['message' => 'No active keywords to check.'], 422);
        }

        $batch = RankCheckBatch::create([
            'project_id'     => $project->id,
            'tenant_id'      => $project->tenant_id,
            'status'         => 'pending',
            'keywords_count' => $keywords->count(),
        ]);

        CheckKeywordRankings::dispatch($batch, $keywords->pluck('id')->all());

        return response()->json([
            'message'   => "Rank check started for {$keywords->count()} keyword(s).",
            'batch_id'  => $batch->id,
            'keywords'  => $keywords->count(),
        ], 202);
    }

    public function batchStatus(Request $request, int $projectId, int $batchId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $batch = RankCheckBatch::where('id', $batchId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        return response()->json(['batch' => $batch]);
    }

    // -------------------------------------------------------------------------
    // Rankings History
    // -------------------------------------------------------------------------

    public function rankings(Request $request, int $projectId, int $keywordId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $keyword = Keyword::where('id', $keywordId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $days = (int) $request->query('days', 30);
        $days = min(max($days, 7), 365);

        $rankings = KeywordRanking::where('keyword_id', $keyword->id)
            ->where('checked_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('checked_at')
            ->get()
            ->map(fn($r) => [
                'id'            => $r->id,
                'checked_at'    => $r->checked_at->toDateString(),
                'rank'          => $r->rank,
                'previous_rank' => $r->previous_rank,
                'trend'         => $r->trend,
                'url'           => $r->url,
                'title'         => $r->title,
                'search_volume' => $r->search_volume,
                'cpc'           => $r->cpc,
                'competition'   => $r->competition,
                'serp_features' => $r->serp_features,
                'data_source'   => $r->data_source,
            ]);

        $latest = $keyword->latestRanking;

        return response()->json([
            'keyword'  => $this->formatKeyword($keyword, $latest),
            'rankings' => $rankings,
            'days'     => $days,
        ]);
    }

    public function projectSummary(Request $request, int $projectId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $keywords = Keyword::forProject($project->id)
            ->active()
            ->with('latestRanking')
            ->orderBy('keyword')
            ->get();

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $summary = $keywords->map(fn(Keyword $kw) => $this->formatKeyword($kw, $kw->latestRanking));

        $stats = [
            'total'         => $keywords->count(),
            'tracked_today' => $keywords->filter(
                fn($kw) => $kw->last_checked_at?->toDateString() === $today
            )->count(),
            'in_top_10'     => $keywords->filter(
                fn($kw) => $kw->latestRanking?->rank !== null && $kw->latestRanking->rank <= 10
            )->count(),
            'in_top_100'    => $keywords->filter(
                fn($kw) => $kw->latestRanking?->rank !== null
            )->count(),
            'not_ranked'    => $keywords->filter(
                fn($kw) => $kw->latestRanking?->rank === null
            )->count(),
        ];

        $recentBatch = RankCheckBatch::where('project_id', $project->id)
            ->latest()
            ->first();

        return response()->json([
            'keywords'     => $summary,
            'stats'        => $stats,
            'recent_batch' => $recentBatch,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authorizedProject(Request $request, int $projectId): Project
    {
        $user = $request->user();

        if (! $user->hasTenant()) {
            abort(403, 'No workspace.');
        }

        return Project::where('id', $projectId)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();
    }

    private function formatKeyword(Keyword $keyword, $latestRanking): array
    {
        return [
            'id'              => $keyword->id,
            'keyword'         => $keyword->keyword,
            'location_code'   => $keyword->location_code,
            'language_code'   => $keyword->language_code,
            'device'          => $keyword->device,
            'is_active'       => $keyword->is_active,
            'last_checked_at' => $keyword->last_checked_at?->toDateString(),
            'latest_rank'     => $latestRanking?->rank,
            'previous_rank'   => $latestRanking?->previous_rank,
            'trend'           => $latestRanking?->trend,
            'search_volume'   => $latestRanking?->search_volume,
            'url'             => $latestRanking?->url,
            'data_source'     => $latestRanking?->data_source,
        ];
    }
}
