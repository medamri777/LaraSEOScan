<?php

namespace App\Http\Controllers;

use App\Services\AiKeywordResearchService;
use App\Services\AiSchemaGenerator;
use App\Services\AuthorityCheckerService;
use App\Services\KeywordOverviewService;
use App\Services\KeywordResearchService;
use App\Services\BacklinkAnalysisService;
use App\Services\KeywordMagicService;
use App\Services\OrganicResearchService;
use App\Services\PlanLimitService;
use App\Http\Traits\UsesProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FreeToolsController extends Controller
{
    use UsesProjectDomain;
    /**
     * Enforce daily tool usage limit. Returns a redirect response if limit exceeded,
     * or null if the user is allowed to proceed.
     */
    private function enforceDailyLimit(string $toolSlug, string $errorMessage): ?\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $tenant = $user?->tenant;

        $result = app(PlanLimitService::class)->checkAndRecordDailyUsage($tenant, $user?->id, $toolSlug);

        if (! $result['allowed']) {
            return redirect()->route('pricing')
                ->with('error', $result['message'] ?? $errorMessage);
        }

        return null;
    }

    public function keywordOverview(Request $request, KeywordOverviewService $service)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Keyword Overview.');
        }

        $keyword = $request->input('keyword');

        // Auto-detect locale from keyword content
        $isArabic  = $keyword && preg_match('/\p{Arabic}/u', $keyword);
        $isFrench  = $keyword && preg_match('/[àâçéèêëîïôùûüÿœæ]/u', $keyword);

        if ($isArabic) {
            $location = 2818; // Egypt — biggest Arabic Google index
            $language = 'ar';
        } elseif ($isFrench) {
            $location = 2250; // France
            $language = 'fr';
        } else {
            $location = 2840; // US / Global
            $language = 'en';
        }

        $data = null;
        if ($keyword) {
            // Enforce daily limit only when actually using the tool
            $denied = $this->enforceDailyLimit('keyword_research', 'Daily keyword research limit reached.');
            if ($denied) return $denied;

            $projectId = null;
            if (auth()->check() && auth()->user()->tenant_id) {
                $project = \App\Models\Project::where('tenant_id', auth()->user()->tenant_id)->first();
                $projectId = $project?->id;
            }
            $data = $service->getKeywordOverview($keyword, $location, $language, $projectId);
        }

        return view('tools.keyword-overview', compact('data', 'keyword'));
    }

    public function serpSimulator(Request $request)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'serp_simulator')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access SERP Simulator.');
        }

        $title = $request->input('title', '');
        $description = $request->input('description', '');
        $url = $request->input('url', '');
        $keyword = $request->input('keyword', '');

        // Record usage only when there's actual input
        if ($title || $keyword) {
            $denied = $this->enforceDailyLimit('serp_simulator', 'Daily SERP simulator limit reached.');
            if ($denied) return $denied;
        }

        return view('tools.serp-simulator', compact('title', 'description', 'url', 'keyword'));
    }

    public function authorityChecker(Request $request, AuthorityCheckerService $service)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Authority Checker.');
        }

        $domain = $request->input('domain');
        $data = null;

        if ($domain) {
            // Force domain from active project — ignore whatever was submitted
            $domain = $this->requireProjectDomain();

            $denied = $this->enforceDailyLimit('authority_checker', 'Daily authority checker limit reached.');
            if ($denied) return $denied;

            $data = $service->getAuthorityData($domain);
        }

        return view('tools.authority-checker', compact('domain', 'data'));
    }

    public function reviewLinkGenerator(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Review Link Generator.');
        }

        return view('tools.review-link-generator');
    }

    /**
     * AJAX: Parse a Google Maps URL or Place ID and generate review links.
     */
    public function reviewLinkSearch(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return response()->json(['error' => 'Please upgrade your plan to access this tool'], 403);
        }

        $request->validate([
            'input' => 'required|string|min:3|max:2000',
        ]);

        $input = trim($request->input('input'));
        $businessName = '';
        $placeId = '';
        $cid = '';
        $mapsUrl = '';
        $reviewUrl = '';
        $coords = null;

        // Case 1: Direct Place ID (starts with ChIJ or E)
        if (preg_match('/^(ChIJ|E)[A-Za-z0-9_-]+$/', $input)) {
            $placeId = $input;
            $reviewUrl = "https://search.google.com/local/writereview?placeid={$placeId}";
            $mapsUrl = "https://www.google.com/maps/place/?api=1&place_id={$placeId}";
        }
        // Case 2: Google Maps URL
        elseif (preg_match('/google\.com\/maps|maps\.google/i', $input)) {
            $mapsUrl = $input;

            // Extract place_id from query param
            $parsed = parse_url($input);
            parse_str($parsed['query'] ?? '', $params);

            if (!empty($params['place_id'])) {
                $placeId = $params['place_id'];
                $reviewUrl = "https://search.google.com/local/writereview?placeid={$placeId}";
            }
            // Extract !1s internal ID
            elseif (preg_match('/!1s([A-Za-z0-9_:-]+)/', $input, $m)) {
                $placeId = $m[1];
                $reviewUrl = "https://search.google.com/local/writereview?placeid=" . urlencode($placeId);
            }
            // Extract CID
            elseif (!empty($params['cid'])) {
                $cid = $params['cid'];
                $reviewUrl = "https://search.google.com/local/writereview?cid={$cid}";
            }

            // Extract business name from URL path
            if (preg_match('/\/place\/([^\/@]+)/', $input, $nm)) {
                $businessName = urldecode(str_replace('+', ' ', $nm[1]));
            }

            // Extract coordinates
            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $input, $cm)) {
                $coords = ['lat' => (float) $cm[1], 'lng' => (float) $cm[2]];
            }
        }
        // Case 3: Plain text = business name (generate search-based link)
        else {
            $businessName = $input;
            $encoded = urlencode($input);
            $reviewUrl = "https://search.google.com/local/writereview?placeid=search:{$encoded}";
            // Fallback: Google search link
            $mapsUrl = "https://www.google.com/maps/search/{$encoded}";
        }

        // If we don't have a proper review URL yet, build a Google search fallback
        if (!$reviewUrl) {
            $encoded = urlencode($businessName ?: $input);
            $reviewUrl = "https://www.google.com/search?q={$encoded}+google+reviews";
        }

        // Short review link (g.page format)
        $shortLink = '';
        if ($placeId) {
            $shortLink = "https://g.page/r/{$placeId}/review";
        }

        // Google Maps link
        $mapsDirectLink = '';
        if ($placeId) {
            $mapsDirectLink = "https://www.google.com/maps/place/?api=1&place_id=" . urlencode($placeId);
        } elseif ($mapsUrl) {
            $mapsDirectLink = $mapsUrl;
        }

        return response()->json([
            'business_name' => $businessName,
            'place_id'      => $placeId,
            'cid'           => $cid,
            'review_url'    => $reviewUrl,
            'short_link'    => $shortLink,
            'maps_link'     => $mapsDirectLink,
            'maps_url'      => $mapsUrl,
            'coords'        => $coords,
        ]);
    }

    protected function calculateAuthority(string $domain): array
    {
        $seed = crc32($domain);
        srand($seed);

        $authorityScore = rand(10, 95);
        $backlinks = rand(100, 500000);
        $referringDomains = rand(10, 5000);
        $organicKeywords = rand(50, 50000);
        $organicTraffic = rand(1000, 1000000);

        $label = $authorityScore < 30 ? 'Faible' : ($authorityScore < 60 ? 'Moyen' : ($authorityScore < 80 ? 'Bon' : 'Excellent'));
        $color = $authorityScore < 30 ? 'danger' : ($authorityScore < 60 ? 'warning' : ($authorityScore < 80 ? 'info' : 'success'));

        $topKeywords = [];
        $words = explode('.', $domain);
        $baseWord = $words[0] ?? 'site';
        for ($i = 0; $i < 5; $i++) {
            $topKeywords[] = [
                'keyword' => "$baseWord " . ['maroc', 'service', 'avis', 'prix', 'meilleur'][$i],
                'position' => rand(1, 50),
                'volume' => rand(100, 5000),
            ];
        }

        return [
            'domain' => $domain,
            'authority_score' => $authorityScore,
            'label' => $label,
            'color' => $color,
            'backlinks' => $backlinks,
            'referring_domains' => $referringDomains,
            'organic_keywords' => $organicKeywords,
            'organic_traffic' => $organicTraffic,
            'top_keywords' => $topKeywords,
        ];
    }

    public function backlinkChecker(Request $request, BacklinkAnalysisService $service)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Backlink Checker.');
        }

        $domain = $request->input('domain');
        $data = null;

        if ($domain) {
            // Force domain from active project — ignore whatever was submitted
            $domain = $this->requireProjectDomain();

            $denied = $this->enforceDailyLimit('backlink_checker', 'Daily backlink checker limit reached.');
            if ($denied) return $denied;

            $data = $service->getBacklinkOverview($domain);
        }

        return view('tools.backlink-checker', compact('domain', 'data'));
    }

    public function keywordMagic(Request $request, AiKeywordResearchService $aiResearch)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Please upgrade to access Keyword Magic.'], 403);
            }
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Keyword Magic.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            // Enforce daily limit on AJAX calls
            $user = auth()->user();
            $result = app(PlanLimitService::class)->checkAndRecordDailyUsage($user?->tenant, $user?->id, 'keyword_magic');
            if (! $result['allowed']) {
                return response()->json([
                    'error'            => $result['message'] ?? 'Daily keyword magic limit reached.',
                    'upgrade_required' => true,
                ], 402);
            }

            $domain = $request->input('domain');
            $language = $request->input('language', 'fr');
            $filters = $request->input('filters', []);

            // Force domain from active project — ignore whatever was submitted
            $domain = $this->requireProjectDomain();

            $domainClean = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $domain);
            $domainClean = explode('/', $domainClean)[0];

            $data = $aiResearch->research($domainClean, $language);

            return response()->json($data)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        $languages = [
            'fr' => 'Français',
            'ar' => 'العربية',
            'en' => 'English',
        ];

        return view('tools.keyword-magic', compact('languages'));
    }

    public function organicResearch(Request $request, OrganicResearchService $service)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Organic Research.');
        }

        $domain = $request->input('domain');
        $location = $request->input('location', 2504);
        $language = $request->input('language', 'fr');

        $data = null;
        if ($domain) {
            // Force domain from active project — ignore whatever was submitted
            $domain = $this->requireProjectDomain();

            $denied = $this->enforceDailyLimit('organic_research', 'Daily organic research limit reached.');
            if ($denied) return $denied;

            // Pass project ID for GSC data
            $projectId = null;
            if (auth()->check() && auth()->user()->tenant_id) {
                $project = \App\Models\Project::where('tenant_id', auth()->user()->tenant_id)
                    ->where('url', 'like', "%{$domain}%")
                    ->first();
                $projectId = $project?->id;
            }
            $data = $service->getOrganicResearch($domain, (int) $location, $language, $projectId);
        }

        $locations = [2504 => 'Maroc', 2250 => 'France', 2840 => 'États-Unis', 2826 => 'Royaume-Uni'];
        $languages = ['fr' => 'Français', 'ar' => 'Arabe', 'en' => 'Anglais'];

        return view('tools.organic-research', compact('data', 'domain', 'locations', 'languages', 'location', 'language'));
    }

    public function schemaGenerator(Request $request, AiSchemaGenerator $aiSchema)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'schema_generator')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Schema Generator.');
        }

        // Force URL from active project — ignore whatever was submitted
        $url = (string) ($this->getProjectUrl() ?? '');
        $name = (string) $request->input('name', '');
        $topic = (string) $request->input('topic', '');
        $description = (string) $request->input('description', '');
        $city = (string) $request->input('city', '');
        $language = (string) $request->input('language', 'french');

        $extras = [
            'telephone' => (string) $request->input('telephone', ''),
            'street_address' => (string) $request->input('street_address', ''),
            'postal_code' => (string) $request->input('postal_code', ''),
            'opening_hours' => (string) $request->input('opening_hours', ''),
            'image' => (string) $request->input('image', ''),
            'cuisine' => (string) $request->input('cuisine', ''),
            'price_range' => (string) $request->input('price_range', ''),
            'rating' => (string) $request->input('rating', ''),
            'review_count' => (string) $request->input('review_count', ''),
        ];

        $result = null;
        $meta = null;
        if ($request->has('generate') && $url && $name) {
            $denied = $this->enforceDailyLimit('schema_generator', 'Daily schema generator limit reached.');
            if ($denied) return $denied;

            $meta = $aiSchema->generateMetaDescription($name, $topic, $city, $language);
            $schemaDesc = trim($description) ? $description : ($meta['description'] ?? '');
            $result = $aiSchema->generate($url, $name, $topic, $schemaDesc, $city, $language, $extras);
        }

        $languages = [
            'french' => 'Français',
            'darija' => 'Darija (Marocain)',
            'arabic' => 'Arabe (Fusha)',
        ];

        return view('tools.schema-generator', compact('result', 'meta', 'url', 'name', 'topic', 'description', 'city', 'language', 'languages'));
    }

    public function keywordResearch(Request $request, KeywordResearchService $service)
    {
        if (!app(PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'keyword_research')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade to access Keyword Research.');
        }

        $keyword = $request->input('keyword');

        $data = null;
        if ($keyword) {
            $denied = $this->enforceDailyLimit('keyword_research', 'Daily keyword research limit reached.');
            if ($denied) return $denied;

            $data = $service->research($keyword);
        }

        return view('tools.keyword-research', compact('data', 'keyword'));
    }

    public function generateDescription(Request $request, AiSchemaGenerator $aiSchema)
    {
        $topic = $request->input('topic');
        $city = $request->input('city', '');

        $description = $aiSchema->generateDescription($topic, $city);

        return response()->json([
            'description' => $description,
        ]);
    }
}
