<?php

namespace App\Http\Controllers;

use App\Jobs\CheckKeywordRankings;
use App\Models\Keyword;
use App\Models\KeywordRanking;
use App\Models\Project;
use App\Models\RankCheckBatch;
use App\Models\ProjectCompetitor;
use App\Services\AiKeywordGenerator;
use App\Services\OrganicResearchService;
use App\Support\PlanLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectKeywordController extends Controller
{
    /**
     * Display keywords list for a project.
     */
    public function index(Request $request, int $projectId)
    {
        $project = $this->authorizedProject($projectId);
        $user = Auth::user();

        $keywords = Keyword::forProject($project->id)
            ->with('latestRanking')
            ->orderBy('keyword')
            ->paginate(50);

        // Calculate summary stats
        $allKeywords = Keyword::forProject($project->id)
            ->active()
            ->with('latestRanking')
            ->get();

        $today = Carbon::today()->toDateString();

        $stats = [
            'total'         => $allKeywords->count(),
            'tracked_today' => $allKeywords->filter(
                fn($kw) => $kw->last_checked_at?->toDateString() === $today
            )->count(),
            'in_top_10'     => $allKeywords->filter(
                fn($kw) => $kw->latestRanking?->rank !== null && $kw->latestRanking->rank <= 10
            )->count(),
            'in_top_100'    => $allKeywords->filter(
                fn($kw) => $kw->latestRanking?->rank !== null
            )->count(),
            'not_ranked'    => $allKeywords->filter(
                fn($kw) => $kw->latestRanking?->rank === null
            )->count(),
        ];

        // Retrieve current active rank checks batch for status updates
        $recentBatch = RankCheckBatch::where('project_id', $project->id)
            ->latest()
            ->first();

        // Get plan constraints
        $plan = $user->tenant->plan ?? 'free';
        $limit = PlanLimits::keywordLimit($plan);
        $currentCount = Keyword::where('tenant_id', $user->tenant_id)->count();

        // Standard locales
        $locations = [
            2504 => 'Maroc (MA)',
            2250 => 'France (FR)',
            2840 => 'États-Unis (US)',
            2826 => 'Royaume-Uni (GB)',
        ];

        $languages = [
            'fr' => 'Français',
            'ar' => 'Arabe',
            'en' => 'Anglais',
        ];

        return view('projects.keywords', compact(
            'project',
            'keywords',
            'stats',
            'recentBatch',
            'limit',
            'currentCount',
            'plan',
            'locations',
            'languages'
        ));
    }

    /**
     * Store new keywords for the project.
     */
    public function store(Request $request, int $projectId)
    {
        $project = $this->authorizedProject($projectId);
        $user = Auth::user();

        $request->validate([
            'keywords_input' => ['required', 'string'],
            'location_code'  => ['nullable', 'integer'],
            'language_code'  => ['nullable', 'string', 'max:10'],
            'device'         => ['nullable', 'in:desktop,mobile'],
        ]);

        // Parse keywords from textarea (split by newline, comma, or semicolon)
        $rawKeywords = preg_split('/[\n\r,;]+/', $request->input('keywords_input'));
        $parsedKeywords = array_filter(array_map('trim', $rawKeywords));

        if (empty($parsedKeywords)) {
            return redirect()->back()->withErrors(['keywords_input' => 'Veuillez saisir au moins un mot-clé valide.']);
        }

        // Plan limit check
        $plan = $user->tenant->plan ?? 'free';
        $limit = PlanLimits::keywordLimit($plan);

        if ($limit !== null) {
            $current = Keyword::where('tenant_id', $user->tenant_id)->count();
            $adding = count($parsedKeywords);
            if ($current + $adding > $limit) {
                return redirect()->back()->withErrors([
                    'limit' => "Vous dépasseriez votre limite de {$limit} mots-clés (Plan: " . ucfirst($plan) . "). Vous en utilisez actuellement {$current} et tentez d'en ajouter {$adding}."
                ]);
            }
        }

        $locationCode = $request->input('location_code') ?? 2504; // Morocco default
        $languageCode = $request->input('language_code') ?? 'fr';
        $device       = $request->input('device') ?? 'desktop';

        $created = 0;
        $skipped = 0;

        foreach ($parsedKeywords as $kw) {
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
                    $created++;
                } else {
                    $skipped++;
                }
                continue;
            }

            Keyword::create([
                'project_id'    => $project->id,
                'tenant_id'     => $project->tenant_id,
                'keyword'       => $kw,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'device'        => $device,
            ]);

            $created++;
        }

        $msg = "{$created} mots-clés ajoutés avec succès.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} doublons ignorés)";
        }

        return redirect()->route('projects.keywords.index', $project->id)->with('success', $msg);
    }

    /**
     * Remove keyword.
     */
    public function destroy(Request $request, int $projectId, int $keywordId)
    {
        $project = $this->authorizedProject($projectId);

        $keyword = Keyword::where('id', $keywordId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $keyword->delete();

        return redirect()->route('projects.keywords.index', $project->id)->with('success', 'Mot-clé supprimé avec succès.');
    }

    /**
     * Manually trigger rank check for all active keywords.
     */
    public function check(Request $request, int $projectId)
    {
        $project = $this->authorizedProject($projectId);

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
            return redirect()->back()->with('error', 'Aucun mot-clé actif à analyser.');
        }

        $batch = RankCheckBatch::create([
            'project_id'     => $project->id,
            'tenant_id'      => $project->tenant_id,
            'status'         => 'pending',
            'keywords_count' => $keywords->count(),
        ]);

        // Dispatch background rank check job
        CheckKeywordRankings::dispatch($batch, $keywords->pluck('id')->all());

        return redirect()->route('projects.keywords.index', $project->id)
            ->with('success', "Analyse lancée pour {$keywords->count()} mots-clés. Le processus tourne en arrière-plan.");
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

    /**
     * AI-powered keyword suggestions.
     */
    public function suggest(Request $request, int $projectId, AiKeywordGenerator $generator)
    {
        $project = $this->authorizedProject($projectId);

        $request->validate([
            'topic' => ['required', 'string', 'max:500'],
            'ville' => ['required', 'string', 'max:200'],
            'language' => ['nullable', 'in:fr,ar,en'],
            'modifiers' => ['nullable', 'string', 'max:500'],
        ]);

        if (!$generator->isConfigured()) {
            return response()->json([
                'error' => 'AI generation non configurée. Ajoutez GROQ_API_KEY dans votre fichier .env.',
            ], 400);
        }

        $domain = parse_url($project->url ?? config('app.url'), PHP_URL_HOST) ?? $project->name;
        $topic = $request->input('topic');
        $ville = $request->input('ville');
        $language = $request->input('language', 'fr');
        $modifiers = $request->input('modifiers') ?? '';

        $result = $generator->generate($domain, $topic, $ville, $language, $modifiers);

        if (empty($result)) {
            return response()->json([
                'error' => 'Erreur lors de la génération. Veuillez réessayer.',
            ], 500);
        }

        return response()->json($result);
    }

    /**
     * Keyword Gap Analysis - compare your keywords vs competitors.
     */
    public function keywordGap(Request $request, int $projectId, OrganicResearchService $organicService)
    {
        $project = $this->authorizedProject($projectId);

        $competitors = ProjectCompetitor::where('project_id', $projectId)
            ->whereNull('deleted_at')
            ->get();

        $yourDomain = parse_url($project->url ?? config('app.url'), PHP_URL_HOST);
        $competitorDomains = $competitors->pluck('url')->map(fn($url) => parse_url($url, PHP_URL_HOST))->filter()->toArray();

        $location = $request->input('location', 2504);
        $language = $request->input('language', 'fr');

        $yourKeywords = [];
        $competitorKeywords = [];

        if ($yourDomain) {
            $yourData = $organicService->getOrganicResearch($yourDomain, (int) $location, $language);
            $yourKeywords = collect($yourData['keywords'] ?? [])->pluck('keyword')->toArray();
        }

        foreach ($competitorDomains as $compDomain) {
            $compData = $organicService->getOrganicResearch($compDomain, (int) $location, $language);
            $compKws = collect($compData['keywords'] ?? [])->pluck('keyword')->toArray();
            $competitorKeywords[$compDomain] = $compKws;
        }

        $allKeywords = array_unique(array_merge($yourKeywords, ...array_values($competitorKeywords)));

        $gapAnalysis = [];
        foreach ($allKeywords as $kw) {
            $inYours = in_array($kw, $yourKeywords);
            $inCompetitors = [];
            foreach ($competitorKeywords as $domain => $compKws) {
                if (in_array($kw, $compKws)) {
                    $inCompetitors[] = $domain;
                }
            }

            if (!$inYours && count($inCompetitors) > 0) {
                $gapAnalysis[] = [
                    'keyword' => $kw,
                    'competitors_ranking' => $inCompetitors,
                    'competitor_count' => count($inCompetitors),
                ];
            }
        }

        usort($gapAnalysis, fn($a, $b) => $b['competitor_count'] <=> $a['competitor_count']);
        $gapAnalysis = array_slice($gapAnalysis, 0, 100);

        $locations = [2504 => 'Maroc', 2250 => 'France', 2840 => 'États-Unis', 2826 => 'Royaume-Uni'];
        $languages = ['fr' => 'Français', 'ar' => 'Arabe', 'en' => 'Anglais'];

        return view('projects.keyword-gap', compact(
            'project', 'competitors', 'yourDomain', 'competitorDomains',
            'gapAnalysis', 'yourKeywords', 'competitorKeywords',
            'locations', 'languages', 'location', 'language'
        ));
    }
}
