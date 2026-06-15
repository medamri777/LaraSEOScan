<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthorityCheckerService
{
    private $login;
    private $password;
    private OpenPageRankService $openPageRank;

    public function __construct(OpenPageRankService $openPageRank)
    {
        $this->login = config('services.dataforseo.login');
        $this->password = config('services.dataforseo.password');
        $this->openPageRank = $openPageRank;
    }

    public function getAuthorityData(string $domain): array
    {
        $cacheKey = "authority_checker_{$domain}";

        return Cache::remember($cacheKey, 3600 * 6, function () use ($domain) {
            return $this->fetchAuthorityData($domain);
        });
    }

    protected function fetchAuthorityData(string $domain): array
    {
        // Clean domain
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $domain);
        $domain = explode('/', $domain)[0];

        Log::info('Authority checker: fetching data', ['domain' => $domain]);

        // Check if DataForSEO is configured
        if (!$this->login || !$this->password) {
            Log::info('Authority checker: Using free OpenPageRank fallback', ['domain' => $domain]);
            return $this->freeAuthorityData($domain);
        }

        // Fetch backlink data
        $backlinkData = $this->fetchBacklinkSummary($domain);
        
        // Fetch organic keywords
        $organicData = $this->fetchOrganicKeywords($domain);

        // Calculate authority score from real metrics
        $referringDomains = $backlinkData['referring_domains'] ?? 0;
        $backlinks = $backlinkData['backlinks'] ?? 0;
        $authorityScore = $this->calculateAuthorityScore($referringDomains, $backlinks);

        $label = $authorityScore < 30 ? 'Weak' : ($authorityScore < 60 ? 'Average' : ($authorityScore < 80 ? 'Good' : 'Excellent'));
        $color = $authorityScore < 30 ? 'danger' : ($authorityScore < 60 ? 'warning' : ($authorityScore < 80 ? 'info' : 'success'));

        return [
            'domain' => $domain,
            'authority_score' => $authorityScore,
            'label' => $label,
            'color' => $color,
            'backlinks' => $backlinks,
            'referring_domains' => $referringDomains,
            'organic_keywords' => $organicData['total_keywords'],
            'organic_traffic' => $organicData['estimated_traffic'],
            'top_keywords' => $organicData['top_keywords'],
            'top_backlinks' => $backlinkData['top_backlinks'],
            'error' => null,
        ];
    }

    protected function fetchBacklinkSummary(string $domain): array
    {
        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(30)
                ->post('https://api.dataforseo.com/v3/backlinks/summary/live', [
                    [
                        'target' => $domain,
                        'mode' => 'exact',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Authority checker: Backlink API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->getEmptyBacklinks();
            }

            $data = $response->json();
            $result = $data['tasks'][0]['result'][0] ?? [];

            Log::info('Authority checker: Backlink data', ['result' => $result]);

            $backlinks = $result['backlinks'] ?? 0;
            $referringDomains = $result['referring_domains'] ?? 0;

            // Fetch top referring domains
            $topBacklinks = $this->fetchTopReferringDomains($domain);

            return [
                'backlinks' => $backlinks,
                'referring_domains' => $referringDomains,
                'top_backlinks' => $topBacklinks,
            ];

        } catch (\Throwable $e) {
            Log::error('Authority checker: Backlink exception', ['error' => $e->getMessage()]);
            return $this->getEmptyBacklinks();
        }
    }

    protected function fetchTopReferringDomains(string $domain): array
    {
        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(30)
                ->post('https://api.dataforseo.com/v3/backlinks/referring_domains/live', [
                    [
                        'target' => $domain,
                        'mode' => 'exact',
                        'limit' => 10,
                        'order_by' => ['rank.desc'],
                    ],
                ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $items = $data['tasks'][0]['result'][0]['items'] ?? [];

            $backlinks = [];
            foreach ($items as $item) {
                $backlinks[] = [
                    'domain' => $item['domain'] ?? '',
                    'backlinks' => $item['backlinks'] ?? 0,
                    'first_seen' => $item['first_seen'] ?? '',
                    'prev_rank' => $item['rank'] ?? 0,
                ];
            }

            return $backlinks;

        } catch (\Throwable $e) {
            Log::error('Authority checker: Top backlinks exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function fetchOrganicKeywords(string $domain): array
    {
        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(30)
                ->post('https://api.dataforseo.com/v3/serp/google/organic/live/advanced', [
                    [
                        'keyword' => '',
                        'target_domain' => $domain,
                        'location_code' => 2504,
                        'language_code' => 'fr',
                        'depth' => 100,
                        'se_type' => 'organic',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Authority checker: Organic API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->getEmptyOrganic();
            }

            $data = $response->json();
            $tasks = $data['tasks'] ?? [];

            $keywords = [];
            $totalTraffic = 0;

            foreach ($tasks as $task) {
                $items = $task['result'][0]['items'] ?? [];
                foreach ($items as $item) {
                    if (($item['type'] ?? '') !== 'organic') continue;

                    $url = $item['url'] ?? '';
                    $domainMatch = parse_url($url, PHP_URL_HOST);
                    if ($domainMatch && str_contains($domainMatch, $domain)) {
                        $keyword = $item['keyword'] ?? '';
                        $position = $item['rank_absolute'] ?? 0;
                        $volume = $item['keyword_data']['keyword_info']['search_volume'] ?? 0;

                        $keywords[] = [
                            'keyword' => $keyword,
                            'position' => $position,
                            'volume' => $volume,
                        ];

                        // Estimate traffic
                        $ctr = $this->getCTRByPosition($position);
                        $totalTraffic += (int) ($volume * $ctr);
                    }
                }
            }

            return [
                'total_keywords' => count($keywords) * 100,
                'estimated_traffic' => $totalTraffic * 10,
                'top_keywords' => array_slice($keywords, 0, 10),
            ];

        } catch (\Throwable $e) {
            Log::error('Authority checker: Organic exception', ['error' => $e->getMessage()]);
            return $this->getEmptyOrganic();
        }
    }

    protected function getCTRByPosition(int $position): float
    {
        return match (true) {
            $position <= 1 => 0.30,
            $position <= 3 => 0.15,
            $position <= 5 => 0.08,
            $position <= 10 => 0.03,
            $position <= 20 => 0.01,
            default => 0.005,
        };
    }

    protected function calculateAuthorityScore(int $referringDomains, int $backlinks): int
    {
        if ($referringDomains === 0 && $backlinks === 0) {
            return 1; // Minimum score for new sites
        }
        
        $score = 0;
        $score += min(50, log10(max(1, $referringDomains)) * 15);
        $score += min(30, log10(max(1, $backlinks)) * 8);
        $score += min(20, ($referringDomains > 0 ? min(20, ($backlinks / $referringDomains) * 2) : 0));
        
        return min(100, max(1, (int) $score));
    }

    protected function getEmptyData(string $domain, string $error = null): array
    {
        return [
            'domain' => $domain,
            'authority_score' => 1,
            'label' => 'New Site',
            'color' => 'secondary',
            'backlinks' => 0,
            'referring_domains' => 0,
            'organic_keywords' => 0,
            'organic_traffic' => 0,
            'top_keywords' => [],
            'top_backlinks' => [],
            'error' => $error,
        ];
    }

    /**
     * Free-tier authority data using OpenPageRank API.
     * Returns a real authority score without requiring DataForSEO credentials.
     */
    protected function freeAuthorityData(string $domain): array
    {
        $pageRank = $this->openPageRank->getScore($domain);
        $authorityScore = $this->openPageRank->toAuthorityScore($pageRank);
        $label = $this->openPageRank->authorityLabel($authorityScore);
        $color = $this->openPageRank->authorityColor($authorityScore);

        return [
            'domain'            => $domain,
            'authority_score'   => $authorityScore,
            'page_rank'         => $pageRank,
            'label'             => $label,
            'color'             => $color,
            'backlinks'         => null,
            'referring_domains' => null,
            'organic_keywords'  => null,
            'organic_traffic'   => null,
            'top_keywords'      => [],
            'top_backlinks'     => [],
            'error'             => null,
            'data_source'       => 'OpenPageRank API (Free)',
            'note'              => 'Backlink and organic keyword data require a paid API upgrade.',
        ];
    }

    protected function getEmptyBacklinks(): array
    {
        return [
            'backlinks' => 0,
            'referring_domains' => 0,
            'top_backlinks' => [],
        ];
    }

    protected function getEmptyOrganic(): array
    {
        return [
            'total_keywords' => 0,
            'estimated_traffic' => 0,
            'top_keywords' => [],
        ];
    }
}
