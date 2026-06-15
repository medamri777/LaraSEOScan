<?php

namespace App\Services;

use App\Models\GscConnection;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\ApiDimensionFilter;
use Google\Service\SearchConsole\ApiDimensionFilterGroup;
use Google\Service\SearchConsole\InspectUrlIndexRequest;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google_search_console.client_id'));
        $this->client->setClientSecret(config('services.google_search_console.client_secret'));
        $this->client->setRedirectUri(config('services.google_search_console.redirect_uri'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes(config('services.google_search_console.scopes'));
    }

    // ── OAuth Flow ──────────────────────────────────────────────────────────

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException(
                'Google OAuth error: ' . ($token['error_description'] ?? $token['error'])
            );
        }

        return [
            'access_token'     => $token['access_token'],
            'refresh_token'    => $token['refresh_token'] ?? null,
            'expires_in'       => $token['expires_in'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
        ];
    }

    // ── Properties ──────────────────────────────────────────────────────────

    public function listProperties(GscConnection $connection): array
    {
        $searchConsole = $this->getClient($connection);
        $sites = $searchConsole->sites->listSites();

        $properties = [];
        foreach ($sites->getSiteEntry() ?? [] as $site) {
            $properties[] = [
                'url'        => $site->getSiteUrl(),
                'permission' => $site->getPermissionLevel(),
            ];
        }

        return $properties;
    }

    // ── Search Analytics ────────────────────────────────────────────────────

    public function getSearchAnalytics(GscConnection $connection, array $params = []): array
    {
        $searchConsole = $this->getClient($connection);

        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($params['start_date'] ?? now()->subDays(28)->toDateString());
        $request->setEndDate($params['end_date'] ?? now()->toDateString());
        $request->setDimensions($params['dimensions'] ?? ['query', 'date']);
        $request->setRowLimit($params['row_limit'] ?? 100);

        if (! empty($params['type'])) {
            $request->setType(strtoupper($params['type']));
        }

        if (! empty($params['dimension_filter_groups'])) {
            $filterGroups = [];
            foreach ($params['dimension_filter_groups'] as $group) {
                $filterGroup = new ApiDimensionFilterGroup();
                $filters = [];
                foreach ($group['filters'] ?? [] as $f) {
                    $filter = new ApiDimensionFilter();
                    $filter->setDimension($f['dimension']);
                    $filter->setOperator($f['operator'] ?? 'contains');
                    $filter->setExpression($f['expression']);
                    $filters[] = $filter;
                }
                $filterGroup->setFilters($filters);
                $filterGroup->setGroupType($group['group_type'] ?? 'and');
                $filterGroups[] = $filterGroup;
            }
            $request->setDimensionFilterGroups($filterGroups);
        }

        $response = $searchConsole->searchanalytics->query($connection->property_url, $request);

        return [
            'rows'                       => $this->formatRows($response->getRows() ?? []),
            'response_aggregation_type'  => $response->getResponseAggregationType(),
        ];
    }

    public function getTopQueries(
        GscConnection $connection,
        int $limit = 50,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return $this->getSearchAnalytics($connection, [
            'start_date' => $startDate ?? now()->subDays(28)->toDateString(),
            'end_date'   => $endDate ?? now()->toDateString(),
            'dimensions' => ['query'],
            'row_limit'  => $limit,
        ]);
    }

    public function getTopPages(
        GscConnection $connection,
        int $limit = 50,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return $this->getSearchAnalytics($connection, [
            'start_date' => $startDate ?? now()->subDays(28)->toDateString(),
            'end_date'   => $endDate ?? now()->toDateString(),
            'dimensions' => ['page'],
            'row_limit'  => $limit,
        ]);
    }

    public function getDailyPerformance(
        GscConnection $connection,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return $this->getSearchAnalytics($connection, [
            'start_date' => $startDate ?? now()->subDays(28)->toDateString(),
            'end_date'   => $endDate ?? now()->toDateString(),
            'dimensions' => ['date'],
            'row_limit'  => 1000,
        ]);
    }

    /**
     * Get GSC stats for a specific keyword (used by KeywordOverviewService).
     */
    public function getKeywordStats(
        GscConnection $connection,
        string $keyword,
        ?string $startDate = null,
        ?string $endDate = null
    ): ?array {
        try {
            $data = $this->getSearchAnalytics($connection, [
                'start_date' => $startDate ?? now()->subDays(28)->toDateString(),
                'end_date'   => $endDate ?? now()->toDateString(),
                'dimensions' => ['query', 'date'],
                'row_limit'  => 1000,
                'dimension_filter_groups' => [
                    [
                        'group_type' => 'and',
                        'filters' => [
                            [
                                'dimension'  => 'query',
                                'operator'   => 'contains',
                                'expression' => $keyword,
                            ],
                        ],
                    ],
                ],
            ]);

            $rows = $data['rows'] ?? [];
            if (empty($rows)) {
                return null;
            }

            $totalClicks = 0;
            $totalImpressions = 0;
            $positionSum = 0;
            $trend = [];

            foreach ($rows as $row) {
                $totalClicks += $row['clicks'];
                $totalImpressions += $row['impressions'];
                $positionSum += $row['position'];

                if (count($row['keys']) >= 2) {
                    $trend[] = [
                        'date'        => $row['keys'][1],
                        'clicks'      => $row['clicks'],
                        'impressions' => $row['impressions'],
                        'ctr'         => $row['ctr'],
                        'position'    => $row['position'],
                    ];
                }
            }

            $count = count($rows);

            return [
                'keyword'       => $keyword,
                'clicks'        => $totalClicks,
                'impressions'   => $totalImpressions,
                'ctr'           => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0,
                'avg_position'  => round($positionSum / max(1, $count), 1),
                'trend'         => $trend,
                'data_source'   => 'Google Search Console (Real)',
            ];
        } catch (\Throwable $e) {
            Log::warning('GSC keyword stats error', ['keyword' => $keyword, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get GSC-based ranking data for a list of keywords (used by DataForSeoService).
     */
    public function getKeywordPositions(GscConnection $connection, array $keywords): array
    {
        $results = [];

        foreach ($keywords as $keyword) {
            try {
                $data = $this->getSearchAnalytics($connection, [
                    'start_date' => now()->subDays(28)->toDateString(),
                    'end_date'   => now()->toDateString(),
                    'dimensions' => ['query'],
                    'row_limit'  => 5,
                    'dimension_filter_groups' => [
                        [
                            'group_type' => 'and',
                            'filters' => [
                                [
                                    'dimension'  => 'query',
                                    'operator'   => 'equals',
                                    'expression' => $keyword,
                                ],
                            ],
                        ],
                    ],
                ]);

                $rows = $data['rows'] ?? [];
                if (! empty($rows)) {
                    $row = $rows[0];
                    $results[$keyword] = [
                        'position'     => $row['position'],
                        'clicks'       => $row['clicks'],
                        'impressions'  => $row['impressions'],
                        'ctr'          => $row['ctr'],
                        'data_source'  => 'GSC',
                    ];
                } else {
                    $results[$keyword] = null;
                }
            } catch (\Throwable $e) {
                $results[$keyword] = null;
            }
        }

        return $results;
    }

    // ── URL Inspection ──────────────────────────────────────────────────────

    public function inspectUrl(GscConnection $connection, string $inspectionUrl): array
    {
        $searchConsole = $this->getClient($connection);

        $request = new InspectUrlIndexRequest();
        $request->setInspectionUrl($inspectionUrl);
        $request->setSiteUrl($connection->property_url);

        $response = $searchConsole->urlInspection_index->inspect($request);
        $result = $response->getInspectionResult();
        $indexStatus = $result->getIndexStatusResult();

        $data = [
            'verdict'          => $indexStatus ? $indexStatus->getVerdict() : null,
            'coverage_state'   => $indexStatus ? $indexStatus->getCoverageState() : null,
            'indexing_state'   => $indexStatus ? $indexStatus->getIndexingState() : null,
            'last_crawl'       => $indexStatus ? $indexStatus->getLastCrawlTime() : null,
            'page_fetch'       => $indexStatus ? $indexStatus->getPageFetchState() : null,
            'robots_txt'       => $indexStatus ? $indexStatus->getRobotsTxtState() : null,
            'crawled_as'       => $indexStatus ? $indexStatus->getCrawledAs() : null,
            'google_canonical' => $indexStatus ? $indexStatus->getGoogleCanonical() : null,
            'user_canonical'   => $indexStatus ? $indexStatus->getUserCanonical() : null,
            'referring_urls'   => $indexStatus ? ($indexStatus->getReferringUrls() ?? []) : [],
            'sitemaps'         => $indexStatus ? ($indexStatus->getSitemap() ?? []) : [],
            'inspection_link'  => $result->getInspectionResultLink(),
        ];

        // Mobile usability (deprecated but still returned)
        $mobileResult = $result->getMobileUsabilityResult();
        $data['mobile_usability'] = $mobileResult ? $mobileResult->getVerdict() : null;

        // Rich results
        $richResult = $result->getRichResultsResult();
        $data['rich_results'] = $richResult ? $richResult->getVerdict() : null;

        return $data;
    }

    // ── Sitemaps ────────────────────────────────────────────────────────────

    public function listSitemaps(GscConnection $connection): array
    {
        $searchConsole = $this->getClient($connection);
        $sitemaps = $searchConsole->sitemaps->listSitemaps($connection->property_url);

        $list = [];
        foreach ($sitemaps->getSitemap() ?? [] as $sitemap) {
            $contents = [];
            foreach ($sitemap->getContents() ?? [] as $c) {
                $contents[] = [
                    'type'      => $c->getType(),
                    'submitted' => $c->getSubmitted(),
                    'indexed'   => $c->getIndexed(),
                ];
            }

            $list[] = [
                'path'           => $sitemap->getPath(),
                'last_submitted' => $sitemap->getLastSubmitted(),
                'is_pending'     => $sitemap->getIsPending(),
                'is_sitemapped'  => $sitemap->getIsSitemapped(),
                'contents'       => $contents,
            ];
        }

        return $list;
    }

    public function submitSitemap(GscConnection $connection, string $sitemapUrl): bool
    {
        $searchConsole = $this->getClient($connection);
        $searchConsole->sitemaps->submit($connection->property_url, $sitemapUrl);

        return true;
    }

    public function deleteSitemap(GscConnection $connection, string $sitemapUrl): bool
    {
        $searchConsole = $this->getClient($connection);
        $searchConsole->sitemaps->delete($connection->property_url, $sitemapUrl);

        return true;
    }

    // ── Internal ────────────────────────────────────────────────────────────

    protected function getClient(GscConnection $connection): SearchConsole
    {
        if ($connection->isTokenExpired()) {
            $this->refreshToken($connection);
        }

        $this->client->setAccessToken([
            'access_token'  => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in'    => $connection->expires_in,
        ]);

        return new SearchConsole($this->client);
    }

    protected function refreshToken(GscConnection $connection): void
    {
        $this->client->fetchAccessTokenWithRefreshToken($connection->refresh_token);
        $token = $this->client->getAccessToken();

        if (isset($token['error'])) {
            Log::error('GSC token refresh failed', ['error' => $token['error']]);
            throw new \RuntimeException('Token refresh failed: ' . ($token['error_description'] ?? $token['error']));
        }

        $connection->update([
            'access_token'     => $token['access_token'],
            'expires_in'       => $token['expires_in'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
        ]);

        // Update the in-memory model so subsequent calls use fresh token
        $connection->access_token = $token['access_token'];
        $connection->expires_in   = $token['expires_in'];
    }

    protected function formatRows(array $rows): array
    {
        return array_map(fn ($row) => [
            'keys'        => $row->getKeys(),
            'clicks'      => (int) $row->getClicks(),
            'impressions' => (int) $row->getImpressions(),
            'ctr'         => round($row->getCtr() * 100, 2),
            'position'    => round($row->getPosition(), 1),
        ], $rows);
    }

    /**
     * Check if GSC is configured (credentials exist).
     */
    public function isConfigured(): bool
    {
        return ! empty(config('services.google_search_console.client_id'))
            && ! empty(config('services.google_search_console.client_secret'));
    }
}
