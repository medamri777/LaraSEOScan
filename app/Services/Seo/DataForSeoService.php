<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DataForSeoService
{
    protected ?string $login;
    protected ?string $password;
    protected string $baseUrl = 'https://api.dataforseo.com/v3';

    public function __construct()
    {
        $this->login = config('services.dataforseo.login');
        $this->password = config('services.dataforseo.password');
    }

    public function isConfigured(): bool
    {
        return !empty($this->login) && !empty($this->password);
    }

    public function getKeywordSuggestions(string $keyword, string $language = 'English', string $location = 'United States', int $limit = 10): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $cacheKey = "dataforseo_suggestions_{$keyword}_{$language}_{$location}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($keyword, $language, $location, $limit) {
            try {
                $response = Http::withBasicAuth($this->login, $this->password)
                    ->timeout(30)
                    ->post("{$this->baseUrl}/dataforseo_labs/google/keyword_suggestions/live", [
                        [
                            'keyword' => $keyword,
                            'language_name' => $language,
                            'location_name' => $location,
                            'limit' => $limit,
                        ]
                    ]);

                if (!$response->successful()) {
                    Log::warning("DataForSEO API error: {$response->status()}");
                    return [];
                }

                $data = $response->json();
                $tasks = $data['tasks'] ?? [];

                if (empty($tasks)) {
                    return [];
                }

                $results = $tasks[0]['result'] ?? [];
                if (empty($results)) {
                    return [];
                }

                $items = $results[0]['items'] ?? [];

                return collect($items)->map(function ($item) {
                    return [
                        'keyword' => $item['keyword'] ?? '',
                        'search_volume' => $item['search_volume'] ?? 0,
                        'cpc' => $item['cpc'] ?? 0,
                        'competition' => $item['competition'] ?? 0,
                        'difficulty' => $item['keyword_difficulty'] ?? 0,
                    ];
                })->toArray();
            } catch (\Exception $e) {
                Log::error("DataForSEO keyword suggestions failed: {$e->getMessage()}");
                return [];
            }
        });
    }

    public function getKeywordMetrics(array $keywords, string $language = 'English', string $location = 'United States'): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $cacheKey = "dataforseo_metrics_" . md5(implode(',', $keywords)) . "_{$language}_{$location}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($keywords, $language, $location) {
            try {
                $response = Http::withBasicAuth($this->login, $this->password)
                    ->timeout(30)
                    ->post("{$this->baseUrl}/keywords_data/google_ads/search_volume/live", [
                        [
                            'keywords' => $keywords,
                            'language_name' => $language,
                            'location_name' => $location,
                        ]
                    ]);

                if (!$response->successful()) {
                    Log::warning("DataForSEO API error: {$response->status()}");
                    return [];
                }

                $data = $response->json();
                $tasks = $data['tasks'] ?? [];

                if (empty($tasks)) {
                    return [];
                }

                $results = $tasks[0]['result'] ?? [];

                return collect($results)->map(function ($item) {
                    return [
                        'keyword' => $item['keyword'] ?? '',
                        'search_volume' => $item['search_volume'] ?? 0,
                        'cpc' => $item['cpc'] ?? 0,
                        'competition' => $item['competition'] ?? 0,
                        'competition_level' => $item['competition_level'] ?? 'UNKNOWN',
                    ];
                })->toArray();
            } catch (\Exception $e) {
                Log::error("DataForSEO keyword metrics failed: {$e->getMessage()}");
                return [];
            }
        });
    }

    public function getKeywordDifficulty(string $keyword, string $language = 'English', string $location = 'United States'): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = "dataforseo_difficulty_{$keyword}_{$language}_{$location}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($keyword, $language, $location) {
            try {
                $response = Http::withBasicAuth($this->login, $this->password)
                    ->timeout(30)
                    ->post("{$this->baseUrl}/dataforseo_labs/google/keyword_difficulty/live", [
                        [
                            'keywords' => [$keyword],
                            'language_name' => $language,
                            'location_name' => $location,
                        ]
                    ]);

                if (!$response->successful()) {
                    return null;
                }

                $data = $response->json();
                $tasks = $data['tasks'] ?? [];

                if (empty($tasks)) {
                    return null;
                }

                $results = $tasks[0]['result'] ?? [];
                if (empty($results)) {
                    return null;
                }

                $items = $results[0]['items'] ?? [];
                if (empty($items)) {
                    return null;
                }

                return (int) ($items[0]['keyword_difficulty'] ?? 0);
            } catch (\Exception $e) {
                Log::error("DataForSEO keyword difficulty failed: {$e->getMessage()}");
                return null;
            }
        });
    }

    public function enrichKeywords(array $keywords): array
    {
        if (!$this->isConfigured() || empty($keywords)) {
            return $keywords;
        }

        $keywordStrings = array_map(function ($kw) {
            return is_array($kw) ? ($kw['keyword'] ?? $kw) : $kw;
        }, $keywords);

        $keywordStrings = array_filter($keywordStrings);
        $keywordStrings = array_slice($keywordStrings, 0, 100);

        if (empty($keywordStrings)) {
            return $keywords;
        }

        $metrics = $this->getKeywordMetrics($keywordStrings);

        $metricsMap = [];
        foreach ($metrics as $metric) {
            $metricsMap[strtolower($metric['keyword'])] = $metric;
        }

        return array_map(function ($kw) use ($metricsMap) {
            $keywordStr = is_array($kw) ? ($kw['keyword'] ?? $kw) : $kw;
            $lowerKeyword = strtolower($keywordStr);

            if (isset($metricsMap[$lowerKeyword])) {
                $metric = $metricsMap[$lowerKeyword];
                if (is_array($kw)) {
                    return array_merge($kw, [
                        'search_volume' => $metric['search_volume'],
                        'cpc' => $metric['cpc'],
                        'competition' => $metric['competition'],
                    ]);
                }
                return [
                    'keyword' => $keywordStr,
                    'search_volume' => $metric['search_volume'],
                    'cpc' => $metric['cpc'],
                    'competition' => $metric['competition'],
                ];
            }

            if (is_array($kw)) {
                return $kw;
            }
            return ['keyword' => $keywordStr];
        }, $keywords);
    }
}
