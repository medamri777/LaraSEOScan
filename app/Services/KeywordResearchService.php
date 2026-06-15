<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class KeywordResearchService
{
    private DataForSeoService $dataForSeo;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    public function __construct(DataForSeoService $dataForSeo)
    {
        $this->dataForSeo = $dataForSeo;
    }

    /**
     * Full keyword research using REAL Google data.
     */
    public function research(string $keyword, string $gl = 'ma', string $hl = 'fr'): array
    {
        $cacheKey = "kw_research_v2_" . md5($keyword . $gl . $hl);

        return Cache::remember($cacheKey, 3600 * 6, function () use ($keyword, $gl, $hl) {
            return $this->fetchResearch($keyword, $gl, $hl);
        });
    }

    protected function fetchResearch(string $keyword, string $gl, string $hl): array
    {
        // 1. Get REAL related keywords from Google Autocomplete
        $autocompleteSuggestions = $this->fetchGoogleAutocomplete($keyword, $gl, $hl);

        // 2. Get REAL Google SERP results (who ranks)
        $serpResults = $this->scrapeGoogleSerp($keyword, $gl, $hl);

        // 3. Get keyword metrics from DataForSEO (if configured) or estimate from SERP
        $metrics = $this->getKeywordMetrics($keyword, $serpResults, $gl, $hl);

        // 4. Build the related keywords list with real suggestions
        $relatedKeywords = $this->buildRelatedKeywords($autocompleteSuggestions, $gl, $hl);

        // 5. Calculate quality score
        $qualityScore = $this->calculateQualityScore($metrics);

        return [
            'keyword' => $keyword,
            'metrics' => $metrics,
            'quality_score' => $qualityScore,
            'serp_results' => $serpResults,
            'related_keywords' => $relatedKeywords,
            'autocomplete' => $autocompleteSuggestions,
        ];
    }

    // ─── Google Autocomplete (FREE, no API key) ─────────────────────────────

    /**
     * Fetch real keyword suggestions from Google Autocomplete API.
     * This is the same API Google uses for the search-as-you-type suggestions.
     */
    protected function fetchGoogleAutocomplete(string $keyword, string $gl, string $hl): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
            ])->timeout(10)->get('https://suggestqueries.google.com/complete/search', [
                'client' => 'firefox',
                'q' => $keyword,
                'gl' => $gl,
                'hl' => $hl,
            ]);

            if (!$response->successful()) {
                Log::warning('Google Autocomplete failed', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            // Response format: ["keyword", ["suggestion1", "suggestion2", ...]]
            $suggestions = $data[1] ?? [];

            // Filter out the exact keyword itself
            $keywordLower = mb_strtolower(trim($keyword));
            return array_values(array_filter($suggestions, function ($s) use ($keywordLower) {
                return mb_strtolower(trim($s)) !== $keywordLower;
            }));
        } catch (\Throwable $e) {
            Log::warning('Google Autocomplete error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    // ─── SERP Scraping (Multi-engine strategy) ──────────────────────────────

    /**
     * Scrape search results using multiple engines for reliability.
     * Google blocks server IPs, so we fall back to DuckDuckGo.
     */
    protected function scrapeGoogleSerp(string $keyword, string $gl, string $hl): array
    {
        // Strategy 1: Try Google with full browser simulation
        $results = $this->tryScrapeGoogle($keyword, $gl, $hl);
        if (count($results) >= 3) {
            return $results;
        }

        // Strategy 2: Fall back to DuckDuckGo (reliable, no blocking)
        $ddgResults = $this->scrapeDuckDuckGo($keyword);
        if (count($ddgResults) >= 3) {
            return $ddgResults;
        }

        // Strategy 3: Try Bing
        $bingResults = $this->scrapeBing($keyword);
        if (count($bingResults) >= 3) {
            return $bingResults;
        }

        // Return whatever we got
        return $results ?: $ddgResults ?: $bingResults ?: [];
    }

    /**
     * Try scraping Google with improved headers and cookie simulation.
     */
    protected function tryScrapeGoogle(string $keyword, string $gl, string $hl): array
    {
        try {
            $googleDomain = $this->getGoogleDomain($gl);

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'DNT' => '1',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Cache-Control' => 'max-age=0',
            ])->timeout(15)->get("https://www.$googleDomain/search", [
                'q' => $keyword,
                'num' => 20,
                'hl' => $hl,
                'gl' => $gl,
                'gbv' => '1',
            ]);

            if (!$response->successful()) {
                return [];
            }

            $body = $response->body();
            // Check if Google returned a consent/captcha page
            if (str_contains($body, 'consent.google') || str_contains($body, 'captcha') || str_contains($body, 'unusual traffic')) {
                return [];
            }

            return $this->parseGoogleSerpHtml($body);
        } catch (\Throwable $e) {
            Log::info('Google SERP scrape failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Scrape DuckDuckGo HTML search results (reliable, no blocking).
     */
    protected function scrapeDuckDuckGo(string $keyword): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(15)->get('https://html.duckduckgo.com/html/', [
                'q' => $keyword,
            ]);

            if (!$response->successful()) {
                Log::warning('DuckDuckGo scrape failed', ['status' => $response->status()]);
                return [];
            }

            return $this->parseDuckDuckGoHtml($response->body());
        } catch (\Throwable $e) {
            Log::info('DuckDuckGo scrape error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Scrape Bing search results as additional fallback.
     */
    protected function scrapeBing(string $keyword): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(15)->get('https://www.bing.com/search', [
                'q' => $keyword,
                'count' => 20,
            ]);

            if (!$response->successful()) {
                return [];
            }

            return $this->parseBingHtml($response->body());
        } catch (\Throwable $e) {
            Log::info('Bing scrape error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    // ─── HTML Parsers for each engine ────────────────────────────────────────

    /**
     * Parse Google SERP HTML.
     */
    protected function parseGoogleSerpHtml(string $html): array
    {
        $results = [];
        try {
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);

            // Method 1: Find div.g blocks
            $selectors = ['#search .g', '#rso .g', '#rso > div > div', '.g'];
            foreach ($selectors as $selector) {
                try {
                    $nodes = $crawler->filter($selector);
                    if ($nodes->count() >= 3) {
                        $nodes->each(function (Crawler $node) use (&$results) {
                            if (count($results) >= 15) return;
                            $result = $this->extractResultFromNode($node);
                            if ($result) {
                                $result['position'] = count($results) + 1;
                                $results[] = $result;
                            }
                        });
                        if (count($results) >= 3) break;
                        $results = []; // Reset if too few
                    }
                } catch (\Throwable $e) { continue; }
            }

            // Method 2: Find a > h3 pattern (most reliable for Google)
            if (count($results) < 3) {
                $results = [];
                $crawler->filter('a')->each(function (Crawler $node) use (&$results) {
                    if (count($results) >= 15) return;
                    try {
                        $h3 = $node->filter('h3');
                        if ($h3->count() === 0) return;

                        $url = $node->attr('href') ?? '';
                        if (!str_starts_with($url, 'http')) return;
                        $domain = $this->extractDomain($url);
                        if (str_contains($domain, 'google.')) return;

                        // Avoid duplicates
                        foreach ($results as $r) {
                            if ($r['domain'] === $domain && $r['title'] === trim($h3->first()->text())) return;
                        }

                        $results[] = [
                            'position' => count($results) + 1,
                            'title' => trim($h3->first()->text()),
                            'url' => $url,
                            'domain' => $domain,
                            'description' => '',
                        ];
                    } catch (\Throwable $e) { /* skip */ }
                });
            }

            libxml_clear_errors();
        } catch (\Throwable $e) {
            Log::warning('Google parse error', ['message' => $e->getMessage()]);
        }
        return $results;
    }

    /**
     * Parse DuckDuckGo HTML results.
     */
    protected function parseDuckDuckGoHtml(string $html): array
    {
        $results = [];
        try {
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);

            // DDG uses .result class for each organic result
            $crawler->filter('.result')->each(function (Crawler $node) use (&$results) {
                if (count($results) >= 15) return;

                $title = '';
                $url = '';
                $description = '';

                // Title & URL: inside .result__a
                try {
                    $titleNode = $node->filter('.result__a');
                    if ($titleNode->count() > 0) {
                        $title = trim($titleNode->first()->text());
                        $href = $titleNode->first()->attr('href') ?? '';
                        // DDG wraps URLs in redirect, extract real URL
                        if (str_contains($href, 'uddg=')) {
                            parse_str(parse_url($href, PHP_URL_QUERY) ?? '', $params);
                            $url = $params['uddg'] ?? $href;
                        } else {
                            $url = $href;
                        }
                    }
                } catch (\Throwable $e) { /* skip */ }

                if (empty($title) || empty($url)) return;

                // Snippet: inside .result__snippet
                try {
                    $snippetNode = $node->filter('.result__snippet');
                    if ($snippetNode->count() > 0) {
                        $description = trim($snippetNode->first()->text());
                    }
                } catch (\Throwable $e) { /* skip */ }

                $domain = $this->extractDomain($url);
                if (str_contains($domain, 'duckduckgo.')) return;

                $results[] = [
                    'position' => count($results) + 1,
                    'title' => $title,
                    'url' => $url,
                    'domain' => $domain,
                    'description' => $description,
                ];
            });

            libxml_clear_errors();
        } catch (\Throwable $e) {
            Log::warning('DuckDuckGo parse error', ['message' => $e->getMessage()]);
        }
        return $results;
    }

    /**
     * Parse Bing search results HTML.
     */
    protected function parseBingHtml(string $html): array
    {
        $results = [];
        try {
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);

            // Bing uses li.b_algo for organic results
            $crawler->filter('li.b_algo')->each(function (Crawler $node) use (&$results) {
                if (count($results) >= 15) return;

                $title = '';
                $url = '';
                $description = '';

                try {
                    $h2 = $node->filter('h2 a');
                    if ($h2->count() > 0) {
                        $title = trim($h2->first()->text());
                        $url = $h2->first()->attr('href') ?? '';
                    }
                } catch (\Throwable $e) { /* skip */ }

                if (empty($title) || empty($url)) return;

                try {
                    $snippetNode = $node->filter('.b_caption p, .b_paractl p, p');
                    if ($snippetNode->count() > 0) {
                        $description = trim($snippetNode->first()->text());
                    }
                } catch (\Throwable $e) { /* skip */ }

                $domain = $this->extractDomain($url);
                if (str_contains($domain, 'bing.')) return;

                $results[] = [
                    'position' => count($results) + 1,
                    'title' => $title,
                    'url' => $url,
                    'domain' => $domain,
                    'description' => $description,
                ];
            });

            libxml_clear_errors();
        } catch (\Throwable $e) {
            Log::warning('Bing parse error', ['message' => $e->getMessage()]);
        }
        return $results;
    }

    /**
     * Extract a result from a generic SERP node.
     */
    protected function extractResultFromNode(Crawler $node): ?array
    {
        $title = '';
        $url = '';
        $description = '';

        try {
            $h3 = $node->filter('h3');
            if ($h3->count() > 0) {
                $title = trim($h3->first()->text());
            }
        } catch (\Throwable $e) { /* skip */ }

        if (empty($title)) return null;

        try {
            $link = $node->filter('a[href^="http"]');
            if ($link->count() > 0) {
                $url = $link->first()->attr('href');
            }
        } catch (\Throwable $e) { /* skip */ }

        if (empty($url) || str_contains($url, 'google.')) return null;

        try {
            foreach (['.VwiCnb', '.IsZvec', 'span.st'] as $sel) {
                try {
                    $desc = $node->filter($sel);
                    if ($desc->count() > 0) {
                        $description = trim($desc->first()->text());
                        break;
                    }
                } catch (\Throwable $e) { continue; }
            }
        } catch (\Throwable $e) { /* skip */ }

        return [
            'title' => $title,
            'url' => $url,
            'domain' => $this->extractDomain($url),
            'description' => $description,
        ];
    }

    // ─── Keyword Metrics ─────────────────────────────────────────────────────

    /**
     * Get keyword metrics. Uses DataForSEO if configured, otherwise estimates from SERP data.
     */
    protected function getKeywordMetrics(string $keyword, array $serpResults, string $gl, string $hl): array
    {
        // Try DataForSEO first
        if ($this->dataForSeo->isConfigured()) {
            $locationCode = $this->glToLocationCode($gl);
            $languageCode = $hl;

            try {
                $response = Http::withBasicAuth(
                    config('services.dataforseo.login'),
                    config('services.dataforseo.password')
                )
                ->timeout(30)
                ->post('https://api.dataforseo.com/v3/serp/google/organic/live/advanced', [
                    [
                        'keyword' => $keyword,
                        'location_code' => $locationCode,
                        'language_code' => $languageCode,
                        'depth' => 20,
                        'se_type' => 'organic',
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $task = $data['tasks'][0]['result'][0] ?? [];

                    if (!empty($task)) {
                        $kwInfo = $task['keyword_data']['keyword_info'] ?? [];
                        $serpInfo = $task['keyword_data']['serp_info'] ?? [];

                        $searchVolume = (int) ($kwInfo['search_volume'] ?? 0);
                        $cpc = (float) ($kwInfo['cpc'] ?? 0);
                        $competition = (float) ($kwInfo['competition'] ?? 0);

                        $difficulty = $this->calculateDifficulty(
                            $searchVolume, $competition, $cpc, count($serpResults)
                        );

                        return [
                            'search_volume' => $searchVolume,
                            'cpc' => round($cpc, 2),
                            'competition' => round($competition * 100),
                            'competition_level' => $kwInfo['competition_level'] ?? 'UNKNOWN',
                            'difficulty' => $difficulty,
                            'total_results' => (int) ($serpInfo['se_results_count'] ?? count($serpResults)),
                            'data_source' => 'DataForSEO API',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('DataForSEO metrics error', ['message' => $e->getMessage()]);
            }
        }

        // Fallback: estimate metrics from SERP analysis
        return $this->estimateMetricsFromSerp($keyword, $serpResults);
    }

    /**
     * Estimate keyword metrics from SERP results when API is not available.
     * Uses SERP analysis signals to provide meaningful insights.
     */
    protected function estimateMetricsFromSerp(string $keyword, array $serpResults): array
    {
        $numResults = count($serpResults);

        if ($numResults === 0) {
            $difficulty = $this->calculateDifficulty(0, 0, 0, 0);
            return [
                'search_volume' => null,
                'cpc' => null,
                'competition' => 0,
                'competition_level' => 'UNKNOWN',
                'difficulty' => $difficulty,
                'total_results' => 0,
                'data_source' => 'No results found',
            ];
        }

        // Analyze domain diversity (more unique domains = more competitive)
        $uniqueDomains = collect($serpResults)->pluck('domain')->unique()->count();

        // Check for big authority sites in top results
        $authorityDomains = [
            'wikipedia.org', 'youtube.com', 'amazon.', 'facebook.com',
            'twitter.com', 'x.com', 'instagram.com', 'linkedin.com',
            'reddit.com', 'quora.com', 'microsoft.com', 'apple.com',
        ];
        $authorityCount = 0;
        $authorityInTop3 = false;
        foreach ($serpResults as $r) {
            foreach ($authorityDomains as $auth) {
                if (str_contains($r['domain'], $auth)) {
                    $authorityCount++;
                    if ($r['position'] <= 3) $authorityInTop3 = true;
                    break;
                }
            }
        }

        // Check if niche sites or blogs rank (easier keyword indicator)
        $nicheIndicators = ['blog', 'forum', 'wordpress', 'blogspot', 'medium.com', 'reddit.com'];
        $nicheCount = 0;
        foreach ($serpResults as $r) {
            foreach ($nicheIndicators as $niche) {
                if (str_contains($r['domain'], $niche) || str_contains($r['url'] ?? '', $niche)) {
                    $nicheCount++;
                    break;
                }
            }
        }

        // Competition score (0-100)
        $competitionScore = 0;
        $competitionScore += min(25, $uniqueDomains * 2.5); // domain diversity
        $competitionScore += min(20, $authorityCount * 5); // authority sites
        if ($authorityInTop3) $competitionScore += 15;
        if ($numResults > 10) $competitionScore += 15;
        elseif ($numResults > 5) $competitionScore += 10;
        // Niche sites reduce competition score (easier to compete)
        $competitionScore -= min(15, $nicheCount * 3);
        $competitionScore = (int) min(95, max(5, $competitionScore));

        // Estimate difficulty from SERP signals
        $difficulty = $this->calculateDifficulty(0, $competitionScore / 100, 0, $numResults);

        // Build analysis summary
        $analysis = [];
        $analysis[] = "$uniqueDomains unique domains ranking";
        if ($authorityCount > 0) $analysis[] = "$authorityCount authority sites";
        if ($nicheCount > 0) $analysis[] = "$nicheCount niche/blog sites (opportunity)";

        return [
            'search_volume' => null,
            'cpc' => null,
            'competition' => $competitionScore,
            'competition_level' => $competitionScore < 30 ? 'LOW' : ($competitionScore < 60 ? 'MEDIUM' : 'HIGH'),
            'difficulty' => $difficulty,
            'total_results' => $numResults,
            'data_source' => 'Search Engine SERP Analysis',
            'serp_analysis' => $analysis,
        ];
    }

    // ─── Related Keywords Builder ────────────────────────────────────────────

    /**
     * Build related keywords list from REAL Google Autocomplete suggestions.
     */
    protected function buildRelatedKeywords(array $suggestions, string $gl, string $hl): array
    {
        $related = [];

        foreach ($suggestions as $suggestion) {
            $related[] = [
                'keyword' => $suggestion,
                'source' => 'Google Autocomplete',
            ];
        }

        return $related;
    }

    // ─── Difficulty & Quality Calculation ────────────────────────────────────

    protected function calculateDifficulty(int $volume, float $competition, float $cpc, int $serpResultCount): array
    {
        $score = 0;

        // Volume factor (0-25)
        if ($volume > 50000) $score += 25;
        elseif ($volume > 10000) $score += 20;
        elseif ($volume > 5000) $score += 15;
        elseif ($volume > 1000) $score += 10;
        elseif ($volume > 100) $score += 5;

        // Competition factor (0-35)
        $score += (int) ($competition * 35);

        // CPC factor (0-15)
        if ($cpc > 10) $score += 15;
        elseif ($cpc > 5) $score += 12;
        elseif ($cpc > 2) $score += 8;
        elseif ($cpc > 0.5) $score += 5;
        elseif ($cpc > 0) $score += 2;

        // SERP density factor (0-25) - more results = more competitive
        if ($serpResultCount > 15) $score += 25;
        elseif ($serpResultCount > 10) $score += 20;
        elseif ($serpResultCount > 5) $score += 15;
        elseif ($serpResultCount > 0) $score += 10;

        $score = min(100, max(0, $score));

        if ($score < 25) {
            $label = 'Easy'; $color = '#53FC18'; $icon = 'check-circle';
        } elseif ($score < 50) {
            $label = 'Moderate'; $color = '#fbbf24'; $icon = 'dash-circle';
        } elseif ($score < 75) {
            $label = 'Hard'; $color = '#f97316'; $icon = 'exclamation-circle';
        } else {
            $label = 'Very Hard'; $color = '#ef4444'; $icon = 'x-circle';
        }

        return ['score' => $score, 'label' => $label, 'color' => $color, 'icon' => $icon];
    }

    protected function calculateQualityScore(array $metrics): array
    {
        $score = 0;
        $reasons = [];

        // Volume assessment
        $vol = $metrics['search_volume'];
        if ($vol === null) {
            $score += 15;
            $reasons[] = 'Volume from SERP analysis';
        } elseif ($vol > 10000) {
            $score += 30;
            $reasons[] = 'High search volume';
        } elseif ($vol > 1000) {
            $score += 20;
            $reasons[] = 'Good search volume';
        } elseif ($vol > 100) {
            $score += 10;
            $reasons[] = 'Moderate search volume';
        } else {
            $reasons[] = 'Low search volume';
        }

        // Difficulty assessment
        $diff = $metrics['difficulty']['score'];
        if ($diff < 25) {
            $score += 30;
            $reasons[] = 'Easy to rank';
        } elseif ($diff < 50) {
            $score += 20;
            $reasons[] = 'Moderate difficulty';
        } elseif ($diff < 75) {
            $score += 10;
            $reasons[] = 'Competitive keyword';
        } else {
            $reasons[] = 'Very competitive';
        }

        // CPC assessment
        if ($metrics['cpc'] !== null) {
            if ($metrics['cpc'] > 2) {
                $score += 20;
                $reasons[] = 'High commercial value';
            } elseif ($metrics['cpc'] > 0.5) {
                $score += 15;
                $reasons[] = 'Good commercial value';
            } else {
                $score += 5;
                $reasons[] = 'Low commercial intent';
            }
        } else {
            $score += 10;
        }

        // Competition opportunity
        if ($metrics['competition'] < 30) {
            $score += 20;
            $reasons[] = 'Low advertiser competition';
        } elseif ($metrics['competition'] < 60) {
            $score += 10;
            $reasons[] = 'Moderate advertiser competition';
        } else {
            $reasons[] = 'High advertiser competition';
        }

        $score = min(100, $score);

        if ($score >= 70) { $verdict = 'Excellent'; $verdictColor = '#53FC18'; }
        elseif ($score >= 50) { $verdict = 'Good'; $verdictColor = '#14b8a6'; }
        elseif ($score >= 30) { $verdict = 'Average'; $verdictColor = '#fbbf24'; }
        else { $verdict = 'Poor'; $verdictColor = '#ef4444'; }

        return [
            'score' => $score,
            'verdict' => $verdict,
            'verdict_color' => $verdictColor,
            'reasons' => $reasons,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        return strtolower(preg_replace('/^www\./', '', $host));
    }

    protected function getGoogleDomain(string $gl): string
    {
        $map = [
            'ma' => 'google.com.ma', 'fr' => 'google.fr', 'us' => 'google.com',
            'gb' => 'google.co.uk', 'uk' => 'google.co.uk', 'ca' => 'google.ca',
            'dz' => 'google.dz', 'tn' => 'google.tn', 'sn' => 'google.sn',
            'de' => 'google.de', 'es' => 'google.es', 'sa' => 'google.com.sa',
            'ae' => 'google.ae', 'eg' => 'google.com.eg',
        ];
        return $map[strtolower($gl)] ?? 'google.com';
    }

    protected function glToLocationCode(string $gl): int
    {
        $map = [
            'ma' => 2504, 'fr' => 2250, 'us' => 2840, 'gb' => 2826, 'uk' => 2826,
            'ca' => 2124, 'dz' => 2012, 'tn' => 2788, 'sn' => 2682,
            'de' => 2276, 'es' => 2724, 'sa' => 2682, 'ae' => 2784, 'eg' => 2818,
        ];
        return $map[strtolower($gl)] ?? 2504;
    }
}
