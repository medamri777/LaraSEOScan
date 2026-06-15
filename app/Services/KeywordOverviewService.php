<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KeywordOverviewService
{
    private DataForSeoService $dataForSeo;
    private GoogleAutocompleteService $autocomplete;
    private OpenPageRankService $openPageRank;
    private KeywordResearchService $keywordResearch;
    private GoogleSearchConsoleService $gsc;

    public function __construct(
        DataForSeoService $dataForSeo,
        GoogleAutocompleteService $autocomplete,
        OpenPageRankService $openPageRank,
        KeywordResearchService $keywordResearch,
        GoogleSearchConsoleService $gsc
    ) {
        $this->dataForSeo = $dataForSeo;
        $this->autocomplete = $autocomplete;
        $this->openPageRank = $openPageRank;
        $this->keywordResearch = $keywordResearch;
        $this->gsc = $gsc;
    }

    public function getKeywordOverview(string $keyword, int $locationCode = 2504, string $languageCode = 'fr', ?int $projectId = null): array
    {
        $cacheKey = "keyword_overview_{$keyword}_{$locationCode}_{$languageCode}";

        return Cache::remember($cacheKey, 3600 * 24, function () use ($keyword, $locationCode, $languageCode, $projectId) {
            return $this->fetchKeywordData($keyword, $locationCode, $languageCode, $projectId);
        });
    }

    protected function fetchKeywordData(string $keyword, int $locationCode, string $languageCode, ?int $projectId = null): array
    {
        if (!$this->dataForSeo->isConfigured()) {
            return $this->freeKeywordOverview($keyword, $locationCode, $languageCode, $projectId);
        }

        try {
            $response = Http::withBasicAuth(
                config('services.dataforseo.login'),
                config('services.dataforseo.password')
            )
            ->timeout(30)
            ->post('https://api.dataforseo.com/v3/kwrd/google/keywords_data/live', [
                [
                    'keyword' => $keyword,
                    'location_code' => $locationCode,
                    'language_code' => $languageCode,
                ],
            ]);

            if (!$response->successful()) {
                return $this->freeKeywordOverview($keyword, $locationCode, $languageCode, $projectId);
            }

            $data = $response->json();
            $task = $data['tasks'][0]['result'][0] ?? null;

            if (!$task) {
                return $this->freeKeywordOverview($keyword, $locationCode, $languageCode, $projectId);
            }

            $searchVolume = $task['search_volume'] ?? 0;
            $cpc = $task['cpc'] ?? 0;
            $competition = $task['competition'] ?? 0;
            $keywordDifficulty = $this->calculateDifficulty($searchVolume, $competition, $cpc);

            $relatedKeywords = $this->getRelatedKeywords($keyword, $locationCode, $languageCode);
            $serpResults = $this->getSerpPreview($keyword, $locationCode, $languageCode);

            return [
                'keyword' => $keyword,
                'search_volume' => $searchVolume,
                'cpc' => round($cpc, 2),
                'competition' => round($competition * 100),
                'difficulty' => $keywordDifficulty,
                'trend' => $this->generateTrend($searchVolume),
                'related_keywords' => $relatedKeywords,
                'serp_preview' => $serpResults,
            ];
        } catch (\Throwable $e) {
            return $this->freeKeywordOverview($keyword, $locationCode, $languageCode, $projectId);
        }
    }

    protected function calculateDifficulty(int $volume, float $competition, float $cpc): array
    {
        $score = 0;

        if ($volume > 10000) $score += 40;
        elseif ($volume > 1000) $score += 30;
        elseif ($volume > 100) $score += 20;
        else $score += 10;

        $score += $competition * 40;

        if ($cpc > 5) $score += 20;
        elseif ($cpc > 2) $score += 10;

        $score = min(100, (int) $score);

        $label = $score < 30 ? 'easy' : ($score < 60 ? 'medium' : 'hard');
        $color = $score < 30 ? 'success' : ($score < 60 ? 'warning' : 'danger');

        return ['score' => $score, 'label' => $label, 'color' => $color];
    }

    protected function generateTrend(int $volume): array
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('M Y');
            $variation = rand(-20, 20) / 100;
            $trend[] = [
                'month' => $month,
                'volume' => max(0, (int) ($volume * (1 + $variation))),
            ];
        }
        return $trend;
    }

    protected function getRelatedKeywords(string $keyword, int $locationCode, string $languageCode): array
    {
        // Use real Google Autocomplete data
        $country = $this->locationCodeToCountry($locationCode);
        $suggestions = $this->autocomplete->suggest($keyword, $country, $languageCode);

        $related = [];
        foreach ($suggestions as $s) {
            $related[] = [
                'keyword'       => $s,
                'search_volume' => null,
                'difficulty'    => null,
                'cpc'           => null,
                'source'        => 'Google Autocomplete',
            ];
        }

        return $related;
    }

    protected function getSerpPreview(string $keyword, int $locationCode, string $languageCode): array
    {
        if (!$this->dataForSeo->isConfigured()) {
            return $this->freeSerpPreview($keyword);
        }

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
                    'depth' => 10,
                ],
            ]);

            if (!$response->successful()) {
                return $this->freeSerpPreview($keyword);
            }

            $data = $response->json();
            $items = $data['tasks'][0]['result'][0]['items'] ?? [];

            $serp = [];
            foreach ($items as $item) {
                if (($item['type'] ?? '') !== 'organic') continue;
                $serp[] = [
                    'position' => $item['rank_absolute'] ?? 0,
                    'title' => $item['title'] ?? '',
                    'url' => $item['url'] ?? '',
                    'domain' => parse_url($item['url'] ?? '', PHP_URL_HOST) ?? '',
                    'description' => $item['description'] ?? '',
                ];
                if (count($serp) >= 10) break;
            }

            return $serp ?: $this->freeSerpPreview($keyword);
        } catch (\Throwable $e) {
            return $this->freeSerpPreview($keyword);
        }
    }
    
    /**
     * Free-tier keyword overview using real Google data + OpenPageRank DIY difficulty.
     */
    protected function freeKeywordOverview(string $keyword, int $locationCode, string $languageCode, ?int $projectId = null): array
    {
        $country = $this->locationCodeToCountry($locationCode);
    
        // 1. Get related keywords from Google Autocomplete
        $suggestions = $this->autocomplete->suggest($keyword, $country, $languageCode);
        $relatedKeywords = [];
        foreach ($suggestions as $s) {
            $relatedKeywords[] = [
                'keyword'       => $s,
                'search_volume' => null,
                'difficulty'    => null,
                'cpc'           => null,
                'source'        => 'Google Autocomplete',
            ];
        }
    
        // 2. Use KeywordResearchService to scrape SERP (Google -> DDG -> Bing fallback)
        $research = $this->keywordResearch->research($keyword, $country, $languageCode);
        $serpResults = $research['serp_results'] ?? [];
    
        // 3. Get OpenPageRank scores for top-10 SERP domains for DIY difficulty
        $topDomains = [];
        foreach (array_slice($serpResults, 0, 10) as $result) {
            if (!empty($result['domain'])) {
                $topDomains[] = $result['domain'];
            }
        }
    
        $pageRankScores = $this->openPageRank->getScores($topDomains);
        $avgScore = 0;
        $scoredDomains = 0;
        foreach ($pageRankScores as $score) {
            if ($score !== null) {
                $avgScore += $score;
                $scoredDomains++;
            }
        }
        $avgPageRank = $scoredDomains > 0 ? $avgScore / $scoredDomains : 0;
    
        // 4. Calculate DIY difficulty: average OpenPageRank (0-10) scaled to 0-100
        $difficultyScore = $this->openPageRank->toAuthorityScore($avgPageRank);
        $difficultyLabel = $this->openPageRank->authorityLabel($difficultyScore);
        $difficultyColor = $difficultyScore < 30 ? 'success' : ($difficultyScore < 60 ? 'warning' : 'danger');
    
        // 5. Build SERP preview from scraped results
        $serpPreview = [];
        foreach ($serpResults as $r) {
            $serpPreview[] = [
                'position'    => $r['position'] ?? 0,
                'title'       => $r['title'] ?? '',
                'url'         => $r['url'] ?? '',
                'domain'      => $r['domain'] ?? '',
                'description' => $r['description'] ?? '',
                'page_rank'   => $pageRankScores[$r['domain'] ?? ''] ?? null,
            ];
            if (count($serpPreview) >= 10) break;
        }
    
        // 6. Build trend from SERP analysis signals (no real trend data without API)
        $competition = $research['metrics']['competition'] ?? null;

        // 7. Try GSC data for your site's performance on this keyword
        $gscStats = null;
        if ($projectId) {
            $connection = \App\Models\GscConnection::where('project_id', $projectId)->first();
            if ($connection) {
                $gscStats = $this->gsc->getKeywordStats($connection, $keyword);
            }
        }

        $result = [
            'keyword'          => $keyword,
            'search_volume'    => null,
            'cpc'              => null,
            'competition'      => $competition,
            'difficulty'       => [
                'score' => $difficultyScore,
                'label' => $difficultyLabel,
                'color' => $difficultyColor,
                'method' => 'OpenPageRank DIY (avg of top-10 SERP domains)',
            ],
            'trend'            => [],
            'related_keywords' => $relatedKeywords,
            'serp_preview'     => $serpPreview,
            'data_source'      => 'Google Autocomplete + SERP Scraping + OpenPageRank (Free)',
            'serp_source'      => $research['metrics']['data_source'] ?? 'SERP Scraping',
            'note'             => 'Volume and CPC data require a paid API upgrade.',
        ];

        // Inject GSC real data when available
        if ($gscStats) {
            $result['gsc_performance'] = $gscStats;
            $result['trend'] = $gscStats['trend'] ?? [];
            $result['data_source'] = 'Google Search Console + SERP Scraping + OpenPageRank (Free)';
        }

        return $result;
    }
    
    /**
     * Free SERP preview using KeywordResearchService scraper.
     */
    protected function freeSerpPreview(string $keyword): array
    {
        $research = $this->keywordResearch->research($keyword, 'ma', 'fr');
        $serpResults = $research['serp_results'] ?? [];
    
        $serp = [];
        foreach ($serpResults as $r) {
            $serp[] = [
                'position'    => $r['position'] ?? 0,
                'title'       => $r['title'] ?? '',
                'url'         => $r['url'] ?? '',
                'domain'      => $r['domain'] ?? '',
                'description' => $r['description'] ?? '',
            ];
            if (count($serp) >= 10) break;
        }
    
        return $serp;
    }
    
    /**
     * Map a DataForSEO location code to a two-letter country code.
     */
    protected function locationCodeToCountry(int $locationCode): string
    {
        $map = [
            2504 => 'ma', 2250 => 'fr', 2840 => 'us', 2826 => 'gb',
            2124 => 'ca', 2012 => 'dz', 2788 => 'tn', 2682 => 'sn',
            2276 => 'de', 2724 => 'es', 2784 => 'ae', 2818 => 'eg',
        ];
        return $map[$locationCode] ?? 'ma';
    }

}
