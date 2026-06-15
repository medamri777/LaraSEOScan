<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompetitorRanking;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\ProjectCompetitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompetitorController extends Controller
{
    private const MAX_COMPETITORS = 5;

    public function index(Request $request, int $projectId): JsonResponse
    {
        $project     = $this->authorizedProject($request, $projectId);
        $competitors = ProjectCompetitor::where('project_id', $project->id)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => $this->formatCompetitor($c));

        return response()->json([
            'competitors' => $competitors,
            'max'         => self::MAX_COMPETITORS,
        ]);
    }

    public function store(Request $request, int $projectId): JsonResponse
    {
        $project = $this->authorizedProject($request, $projectId);

        $existing = ProjectCompetitor::where('project_id', $project->id)->count();
        if ($existing >= self::MAX_COMPETITORS) {
            return response()->json([
                'message' => "Maximum of " . self::MAX_COMPETITORS . " competitors per project.",
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url'  => ['required', 'url', 'max:2048'],
        ]);

        // Check for duplicate URL on this project (including soft-deleted)
        $existing = ProjectCompetitor::withTrashed()
            ->where('project_id', $project->id)
            ->where('url', $validated['url'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update(['name' => $validated['name']]);
                return response()->json([
                    'message'    => 'Competitor restored.',
                    'competitor' => $this->formatCompetitor($existing->fresh()),
                ], 201);
            }
            return response()->json(['message' => 'This competitor URL is already tracked.'], 409);
        }

        $competitor = ProjectCompetitor::create([
            'project_id' => $project->id,
            'tenant_id'  => $project->tenant_id,
            'name'       => $validated['name'],
            'url'        => $validated['url'],
        ]);

        return response()->json([
            'message'    => 'Competitor added.',
            'competitor' => $this->formatCompetitor($competitor),
        ], 201);
    }

    public function destroy(Request $request, int $projectId, int $competitorId): JsonResponse
    {
        $project    = $this->authorizedProject($request, $projectId);
        $competitor = ProjectCompetitor::where('id', $competitorId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $competitor->delete();

        return response()->json(['message' => 'Competitor removed.']);
    }

    /**
     * Latest competitor rankings across all keywords for a project.
     * Returns a matrix: keyword → [own_rank, comp1_rank, comp2_rank, …]
     */
    public function matrix(Request $request, int $projectId): JsonResponse
    {
        $project     = $this->authorizedProject($request, $projectId);
        $competitors = ProjectCompetitor::where('project_id', $project->id)->get();

        $keywords = Keyword::active()
            ->where('project_id', $project->id)
            ->with('latestRanking')
            ->orderBy('keyword')
            ->get();

        $today = Carbon::today()->toDateString();

        // Pre-load all latest competitor rankings for these keywords
        $kwIds = $keywords->pluck('id')->all();
        $compIds = $competitors->pluck('id')->all();

        $compRankings = CompetitorRanking::whereIn('keyword_id', $kwIds)
            ->whereIn('competitor_id', $compIds)
            ->where('checked_at', function ($q) use ($kwIds, $compIds) {
                $q->selectRaw('MAX(cr2.checked_at)')
                  ->from('competitor_rankings as cr2')
                  ->whereColumn('cr2.keyword_id', 'competitor_rankings.keyword_id')
                  ->whereColumn('cr2.competitor_id', 'competitor_rankings.competitor_id');
            })
            ->get()
            ->groupBy(fn($r) => "{$r->keyword_id}_{$r->competitor_id}");

        $matrix = $keywords->map(function (Keyword $kw) use ($competitors, $compRankings) {
            $row = [
                'keyword_id'   => $kw->id,
                'keyword'      => $kw->keyword,
                'own_rank'     => $kw->latestRanking?->rank,
                'own_trend'    => $kw->latestRanking?->trend,
                'search_volume'=> $kw->latestRanking?->search_volume,
                'competitors'  => [],
            ];

            foreach ($competitors as $comp) {
                $cr = $compRankings->get("{$kw->id}_{$comp->id}")?->first();
                $row['competitors'][] = [
                    'competitor_id'   => $comp->id,
                    'competitor_name' => $comp->name,
                    'rank'            => $cr?->rank,
                    'url'             => $cr?->url,
                ];
            }

            return $row;
        });

        return response()->json([
            'competitors' => $competitors->map(fn($c) => $this->formatCompetitor($c)),
            'matrix'      => $matrix,
        ]);
    }

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

    private function formatCompetitor(ProjectCompetitor $c): array
    {
        return [
            'id'         => $c->id,
            'project_id' => $c->project_id,
            'name'       => $c->name,
            'url'        => $c->url,
            'created_at' => $c->created_at,
        ];
    }
}
