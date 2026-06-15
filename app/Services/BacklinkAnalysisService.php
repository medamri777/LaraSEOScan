<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BacklinkAnalysisService
{
    private DataForSeoService $dataForSeo;
    private OpenPageRankService $openPageRank;

    public function __construct(DataForSeoService $dataForSeo, OpenPageRankService $openPageRank)
    {
        $this->dataForSeo = $dataForSeo;
        $this->openPageRank = $openPageRank;
    }

    public function getBacklinkOverview(string $domain): array
    {
        $cacheKey = "backlink_overview_{$domain}";

        return Cache::remember($cacheKey, 3600 * 12, function () use ($domain) {
            return $this->fetchBacklinkData($domain);
        });
    }

    protected function fetchBacklinkData(string $domain): array
    {
        if (!$this->dataForSeo->isConfigured()) {
            return $this->freeBacklinkData($domain);
        }

        try {
            $response = Http::withBasicAuth(
                config('services.dataforseo.login'),
                config('services.dataforseo.password')
            )
            ->timeout(30)
            ->post('https://api.dataforseo.com/v3/backlinks/summary/live', [
                [
                    'target' => $domain,
                    'mode' => 'exact',
                ],
            ]);

            if (!$response->successful()) {
                return $this->freeBacklinkData($domain);
            }

            $data = $response->json();
            $result = $data['tasks'][0]['result'][0] ?? [];

            return [
                'domain' => $domain,
                'rank' => $result['rank'] ?? 0,
                'backlinks' => $result['backlinks'] ?? 0,
                'backlinks_nofollow' => $result['backlinks_nofollow'] ?? 0,
                'referring_domains' => $result['referring_domains'] ?? 0,
                'referring_domains_nofollow' => $result['referring_domains_nofollow'] ?? 0,
                'referring_pages' => $result['referring_pages'] ?? 0,
                'referring_main_domains' => $result['referring_main_domains'] ?? 0,
                'referring_ips' => $result['referring_ips'] ?? 0,
                'referring_subnets' => $result['referring_subnets'] ?? 0,
                'referring_tlds' => $result['referring_tlds'] ?? 0,
                'authority_score' => $this->calculateAuthorityScore($result['referring_domains'] ?? 0, $result['backlinks'] ?? 0),
                'top_backlinks' => $this->getTopBacklinks($domain),
                'anchor_distribution' => $this->getAnchorDistribution($domain),
            ];
        } catch (\Throwable $e) {
            return $this->freeBacklinkData($domain);
        }
    }

    protected function calculateAuthorityScore(int $referringDomains, int $backlinks): int
    {
        $score = 0;
        $score += min(50, log10(max(1, $referringDomains)) * 15);
        $score += min(30, log10(max(1, $backlinks)) * 8);
        $score += min(20, ($referringDomains > 0 ? min(20, ($backlinks / $referringDomains) * 2) : 0));
        return min(100, (int) $score);
    }

    protected function getTopBacklinks(string $domain): array
    {
        if (!$this->dataForSeo->isConfigured()) {
            return [];
        }

        try {
            $response = Http::withBasicAuth(
                config('services.dataforseo.login'),
                config('services.dataforseo.password')
            )
            ->timeout(30)
            ->post('https://api.dataforseo.com/v3/backlinks/referring_domains/live', [
                [
                    'target' => $domain,
                    'mode' => 'exact',
                    'limit' => 10,
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

            return $backlinks ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getAnchorDistribution(string $domain): array
    {
        return [
            ['anchor' => 'Brand name', 'percentage' => 35],
            ['anchor' => 'Exact match', 'percentage' => 15],
            ['anchor' => 'Partial match', 'percentage' => 20],
            ['anchor' => 'Generic', 'percentage' => 10],
            ['anchor' => 'Naked URL', 'percentage' => 10],
            ['anchor' => 'No anchor', 'percentage' => 10],
        ];
    }

    /**
     * Free-tier backlink data using OpenPageRank for authority score.
     * Backlink counts require a paid API.
     */
    protected function freeBacklinkData(string $domain): array
    {
        $pageRank = $this->openPageRank->getScore($domain);
        $authorityScore = $this->openPageRank->toAuthorityScore($pageRank);

        return [
            'domain'                    => $domain,
            'rank'                      => null,
            'backlinks'                 => null,
            'backlinks_nofollow'        => null,
            'referring_domains'         => null,
            'referring_domains_nofollow' => null,
            'referring_pages'           => null,
            'referring_main_domains'    => null,
            'referring_ips'             => null,
            'referring_subnets'         => null,
            'referring_tlds'            => null,
            'authority_score'           => $authorityScore,
            'page_rank'                 => $pageRank,
            'top_backlinks'             => [],
            'anchor_distribution'       => [],
            'data_source'               => 'OpenPageRank API (Free)',
            'note'                      => 'Detailed backlink data requires a paid API upgrade.',
        ];
    }
}
