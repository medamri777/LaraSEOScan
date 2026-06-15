<?php

namespace App\Http\Controllers;

use App\Models\CompetitorRanking;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\ProjectCompetitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectCompetitorController extends Controller
{
    private const MAX_COMPETITORS = 5;

    /**
     * Display competitors list and comparison matrix.
     */
    public function index(Request $request, int $projectId)
    {
        $project = $this->authorizedProject($projectId);
        $user = Auth::user();

        $competitors = ProjectCompetitor::where('project_id', $project->id)
            ->orderBy('name')
            ->get();

        $keywords = Keyword::active()
            ->where('project_id', $project->id)
            ->with('latestRanking')
            ->orderBy('keyword')
            ->get();

        // Pre-load latest competitor rankings
        $kwIds = $keywords->pluck('id')->all();
        $compIds = $competitors->pluck('id')->all();

        $compRankings = CompetitorRanking::whereIn('keyword_id', $kwIds)
            ->whereIn('competitor_id', $compIds)
            ->where('checked_at', function ($q) {
                $q->selectRaw('MAX(cr2.checked_at)')
                  ->from('competitor_rankings as cr2')
                  ->whereColumn('cr2.keyword_id', 'competitor_rankings.keyword_id')
                  ->whereColumn('cr2.competitor_id', 'competitor_rankings.competitor_id');
            })
            ->get()
            ->groupBy(fn($r) => "{$r->keyword_id}_{$r->competitor_id}");

        // Build matrix
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
                $row['competitors'][$comp->id] = [
                    'rank' => $cr?->rank,
                    'url'  => $cr?->url,
                ];
            }

            return $row;
        });

        $maxCompetitors = self::MAX_COMPETITORS;

        return view('projects.competitors', compact('project', 'competitors', 'matrix', 'maxCompetitors'));
    }

    /**
     * Store new competitor.
     */
    public function store(Request $request, int $projectId)
    {
        $project = $this->authorizedProject($projectId);

        $existingCount = ProjectCompetitor::where('project_id', $project->id)->count();
        if ($existingCount >= self::MAX_COMPETITORS) {
            return redirect()->back()->withErrors([
                'limit' => "Vous avez atteint la limite de " . self::MAX_COMPETITORS . " concurrents par projet."
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url'  => ['required', 'url', 'max:2048'],
        ]);

        // Duplicate URL check
        $existing = ProjectCompetitor::withTrashed()
            ->where('project_id', $project->id)
            ->where('url', $validated['url'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update(['name' => $validated['name']]);
                return redirect()->route('projects.competitors.index', $project->id)->with('success', 'Concurrent restauré avec succès.');
            }
            return redirect()->back()->withErrors(['url' => 'Ce site concurrent est déjà suivi dans votre projet.']);
        }

        ProjectCompetitor::create([
            'project_id' => $project->id,
            'tenant_id'  => $project->tenant_id,
            'name'       => $validated['name'],
            'url'        => $validated['url'],
        ]);

        return redirect()->route('projects.competitors.index', $project->id)->with('success', 'Concurrent ajouté avec succès.');
    }

    /**
     * Remove competitor.
     */
    public function destroy(Request $request, int $projectId, int $competitorId)
    {
        $project = $this->authorizedProject($projectId);

        $competitor = ProjectCompetitor::where('id', $competitorId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $competitor->delete();

        return redirect()->route('projects.competitors.index', $project->id)->with('success', 'Concurrent supprimé avec succès.');
    }

    /**
     * Helper: check authorized project.
     */
    private function authorizedProject(int $projectId): Project
    {
        $user = Auth::user();

        if (! $user->hasTenant()) {
            abort(403, 'Workspace non initialisé.');
        }

        return Project::where('id', $projectId)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();
    }
}
