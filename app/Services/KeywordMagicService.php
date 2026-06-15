<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KeywordMagicService
{
    private DataForSeoService $dataForSeo;
    private GoogleAutocompleteService $autocomplete;

    public function __construct(DataForSeoService $dataForSeo, GoogleAutocompleteService $autocomplete)
    {
        $this->dataForSeo = $dataForSeo;
        $this->autocomplete = $autocomplete;
    }

    public function getKeywordSuggestions(string $seedKeyword, int $locationCode = 2504, string $languageCode = 'fr', array $filters = []): array
    {
        $cacheKey = "keyword_magic_{$seedKeyword}_{$locationCode}_{$languageCode}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, 3600 * 24, function () use ($seedKeyword, $locationCode, $languageCode, $filters) {
            return $this->fetchKeywordSuggestions($seedKeyword, $locationCode, $languageCode, $filters);
        });
    }

    protected function fetchKeywordSuggestions(string $seedKeyword, int $locationCode, string $languageCode, array $filters): array
    {
        if (!$this->dataForSeo->isConfigured()) {
            return $this->freeSuggestions($seedKeyword, $locationCode, $languageCode, $filters);
        }

        try {
            $payload = [
                'keyword' => $seedKeyword,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'limit' => 100,
            ];

            if (!empty($filters['min_volume'])) {
                $payload['min_search_volume'] = (int) $filters['min_volume'];
            }
            if (!empty($filters['max_difficulty'])) {
                $payload['max_competition'] = (int) $filters['max_difficulty'] / 100;
            }
            if (!empty($filters['match_type'])) {
                $payload['match_type'] = $filters['match_type'];
            }

            $response = Http::withBasicAuth(
                config('services.dataforseo.login'),
                config('services.dataforseo.password')
            )
            ->timeout(30)
            ->post('https://api.dataforseo.com/v3/kwrd/google/keywords_data/search_volume/live', [$payload]);

            if (!$response->successful()) {
                return $this->freeSuggestions($seedKeyword, $locationCode, $languageCode, $filters);
            }

            $data = $response->json();
            $items = $data['tasks'][0]['result'] ?? [];

            $keywords = [];
            foreach ($items as $item) {
                $keyword = $item['keyword'] ?? '';
                $volume = $item['search_volume'] ?? 0;
                $cpc = $item['cpc'] ?? 0;
                $competition = $item['competition'] ?? 0;

                $keywords[] = [
                    'keyword' => $keyword,
                    'search_volume' => $volume,
                    'cpc' => round($cpc, 2),
                    'competition' => (int) ($competition * 100),
                    'difficulty' => $this->calculateDifficulty($volume, $competition, $cpc),
                    'trend' => $item['monthly_searches'] ?? [],
                ];
            }

            return [
                'seed' => $seedKeyword,
                'total' => count($keywords),
                'keywords' => $keywords,
                'groups' => $this->groupByCategory($keywords),
                'data_source' => 'DataForSEO API',
            ];
        } catch (\Throwable $e) {
            return $this->freeSuggestions($seedKeyword, $locationCode, $languageCode, $filters);
        }
    }

    protected function calculateDifficulty(int $volume, float $competition, float $cpc): int
    {
        $score = 0;
        if ($volume > 10000) $score += 40;
        elseif ($volume > 1000) $score += 30;
        elseif ($volume > 100) $score += 20;
        else $score += 10;

        $score += $competition * 40;
        if ($cpc > 5) $score += 20;
        elseif ($cpc > 2) $score += 10;

        return min(100, (int) $score);
    }

    protected function groupByCategory(array $keywords): array
    {
        $groups = [
            'questions' => [],
            'comparisons' => [],
            'commercial' => [],
            'long_tail' => [],
            'local' => [],
        ];

        foreach ($keywords as $kw) {
            $keyword = strtolower($kw['keyword']);
            if (preg_match('/^(comment|pourquoi|ou|quand|qui|que|combien|comment|how|what|why|where)/', $keyword)) {
                $groups['questions'][] = $kw;
            } elseif (preg_match('/^(meilleur|vs|comparatif|versus|contre|ou choisir)/', $keyword)) {
                $groups['comparisons'][] = $kw;
            } elseif (preg_match('/(pas cher|prix|achat|acheter|commander|devis)/', $keyword)) {
                $groups['commercial'][] = $kw;
            } elseif (str_word_count($keyword) >= 4) {
                $groups['long_tail'][] = $kw;
            } elseif (preg_match('/(maroc|casablanca|rabat|marrakech|fes|tanger)/', $keyword)) {
                $groups['local'][] = $kw;
            }
        }

        return array_map('array_values', $groups);
    }

    /**
     * Free-tier keyword suggestions using Google Autocomplete alphabet expansion.
     * Returns real keyword ideas from Google — no volume/CPC data (those require a paid API).
     */
    protected function freeSuggestions(string $seedKeyword, int $locationCode, string $languageCode, array $filters): array
    {
        // Map location code to country code for Google Autocomplete
        $country = $this->locationCodeToCountry($locationCode);
        $language = $languageCode;

        // Use alphabet expansion to generate 200+ real suggestions
        $suggestions = $this->autocomplete->alphabetExpand($seedKeyword, $country, $language, 250);

        $keywords = [];
        foreach ($suggestions as $suggestion) {
            $wordCount = str_word_count($suggestion);
            $keywords[] = [
                'keyword'       => $suggestion,
                'search_volume' => null,  // Not available from Google Suggest
                'cpc'           => null,
                'competition'   => null,
                'difficulty'    => null,
                'source'        => 'Google Autocomplete',
                'word_count'    => $wordCount,
            ];
        }

        // Apply min_volume filter: skip since volume is null in free tier
        // Apply max_difficulty filter: skip since difficulty is null in free tier

        return [
            'seed'        => $seedKeyword,
            'total'       => count($keywords),
            'keywords'    => $keywords,
            'groups'      => $this->groupByCategory($keywords),
            'data_source' => 'Google Autocomplete (Free)',
            'note'        => 'Volume, CPC, and difficulty data require a paid API upgrade.',
        ];
    }

    /**
     * Map a DataForSEO location code to a two-letter country code.
     */
    protected function locationCodeToCountry(int $locationCode): string
    {
        $map = [
            2504 => 'ma', // Morocco
            2250 => 'fr', // France
            2840 => 'us', // United States
            2826 => 'gb', // United Kingdom
            2124 => 'ca', // Canada
            2012 => 'dz', // Algeria
            2788 => 'tn', // Tunisia
            2682 => 'sn', // Senegal
            2276 => 'de', // Germany
            2724 => 'es', // Spain
            2784 => 'ae', // UAE
            2818 => 'eg', // Egypt
        ];
        return $map[$locationCode] ?? 'ma';
    }
}
