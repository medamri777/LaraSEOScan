<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataForSeoService
{
    private string $baseUrl = 'https://api.dataforseo.com/v3';
    private string $login;
    private string $password;

    public function __construct()
    {
        $this->login    = config('services.dataforseo.login', '');
        $this->password = config('services.dataforseo.password', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->login) && ! empty($this->password);
    }

    /**
     * Batch check: multiple keywords for ONE target domain.
     * Returns array keyed by keyword string → result array.
     */
    public function checkRankingsBatch(
        array  $keywords,
        string $targetDomain,
        int    $locationCode = 2504,
        string $languageCode = 'fr',
        string $device       = 'desktop'
    ): array {
        if (! $this->isConfigured()) {
            return collect($keywords)->mapWithKeys(fn($kw) => [
                $kw => $this->mockResult($kw, $targetDomain),
            ])->all();
        }

        $serpData = $this->fetchSerpBatch($keywords, $locationCode, $languageCode, $device);

        $out = [];
        foreach ($serpData as $kw => $result) {
            $out[$kw] = $this->extractDomainResult($result, $targetDomain);
        }

        return $out;
    }

    /**
     * Batch check for MULTIPLE domains (own + competitors) from a single SERP call.
     * Returns: ['keyword' => ['domain' => ['rank' => ?, 'url' => ?, 'title' => ?], ...], ...]
     *
     * Efficient: one API call gives positions for all tracked domains simultaneously.
     */
    public function checkRankingsBatchMultiDomain(
        array  $keywords,
        array  $domains,       // ['acme.dz', 'competitor1.com', 'competitor2.ma']
        int    $locationCode = 2504,
        string $languageCode = 'fr',
        string $device       = 'desktop'
    ): array {
        if (! $this->isConfigured()) {
            $out = [];
            foreach ($keywords as $kw) {
                $out[$kw] = [];
                foreach ($domains as $domain) {
                    $out[$kw][$domain] = $this->mockResult($kw, $domain);
                }
            }
            return $out;
        }

        $serpData = $this->fetchSerpBatch($keywords, $locationCode, $languageCode, $device);

        $out = [];
        foreach ($serpData as $kw => $result) {
            $out[$kw] = [];
            foreach ($domains as $domain) {
                $out[$kw][$domain] = $this->extractDomainResult($result, $domain);
            }
        }

        return $out;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Make the actual batch API call. Returns raw SERP items keyed by keyword.
     */
    private function fetchSerpBatch(
        array  $keywords,
        int    $locationCode,
        string $languageCode,
        string $device
    ): array {
        $payload = collect($keywords)->map(fn($kw) => [
            'keyword'       => $kw,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'device'        => $device,
            'depth'         => 100,
            'se_type'       => 'organic',
        ])->values()->all();

        try {
            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(60)
                ->post("{$this->baseUrl}/serp/google/organic/live/advanced", $payload);

            if (! $response->successful()) {
                Log::warning('DataForSEO batch error', ['status' => $response->status()]);
                return collect($keywords)->mapWithKeys(fn($kw) => [$kw => []])->all();
            }

            $data  = $response->json();
            $tasks = $data['tasks'] ?? [];
            $out   = [];

            foreach ($tasks as $task) {
                $kw     = $task['data']['keyword'] ?? null;
                $result = $task['result'][0] ?? null;
                if ($kw) {
                    $out[$kw] = $result ?? [];
                }
            }

            return $out;

        } catch (\Throwable $e) {
            Log::error('DataForSEO batch exception', ['message' => $e->getMessage()]);
            return collect($keywords)->mapWithKeys(fn($kw) => [$kw => []])->all();
        }
    }

    /**
     * From a raw SERP result task, find the position of a specific domain.
     */
    private function extractDomainResult(array $result, string $targetDomain): array
    {
        if (empty($result)) {
            return $this->emptyResult();
        }

        $items        = $result['items'] ?? [];
        $searchVolume = $result['keyword_data']['keyword_info']['search_volume'] ?? null;
        $cpc          = $result['keyword_data']['keyword_info']['cpc'] ?? null;
        $competition  = $result['keyword_data']['keyword_info']['competition'] ?? null;
        $serpFeatures = $result['keyword_data']['serp_info']['serp_item_types'] ?? [];
        $targetHost   = $this->extractHost($targetDomain);

        $rank  = null;
        $url   = null;
        $domain = null;
        $title = null;

        foreach ($items as $item) {
            if (($item['type'] ?? '') !== 'organic') {
                continue;
            }
            $itemDomain = $this->extractHost($item['url'] ?? '');
            if ($itemDomain === $targetHost || str_contains($itemDomain, $targetHost)) {
                $rank   = $item['rank_absolute'] ?? null;
                $url    = $item['url'] ?? null;
                $domain = $itemDomain;
                $title  = $item['title'] ?? null;
                break;
            }
        }

        return [
            'rank'          => $rank,
            'url'           => $url,
            'domain'        => $domain,
            'title'         => $title,
            'search_volume' => is_numeric($searchVolume) ? (int) $searchVolume : null,
            'cpc'           => is_numeric($cpc) ? (float) $cpc : null,
            'competition'   => is_numeric($competition) ? (int) round($competition * 100) : null,
            'serp_features' => $serpFeatures,
        ];
    }

    public function emptyResult(): array
    {
        return [
            'rank'          => null,
            'url'           => null,
            'domain'        => null,
            'title'         => null,
            'search_volume' => null,
            'cpc'           => null,
            'competition'   => null,
            'serp_features' => [],
            'data_source'   => 'free_tier',
        ];
    }

    /**
     * Mock result — returned when DataForSEO API is not configured.
     * Clearly indicates that live ranking data requires a paid API.
     */
    public function mockResult(string $keyword, string $targetDomain): array
    {
        return [
            'rank'          => null,
            'url'           => null,
            'domain'        => $targetDomain,
            'title'         => null,
            'search_volume' => null,
            'cpc'           => null,
            'competition'   => null,
            'serp_features' => [],
            'data_source'   => 'free_tier',
            '_mock'         => true,
            'note'          => 'Live SERP ranking data requires a paid API (e.g. Serper.dev or DataForSEO).',
        ];
    }

    public function extractHost(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        return strtolower(preg_replace('/^www\./', '', $host));
    }

    /**
     * Enrich ranking results with GSC data (real position from Google Search Console).
     * Adds 'gsc_position', 'gsc_clicks', 'gsc_impressions' to each keyword result.
     */
    public function enrichWithGscData(array $rankingResults, ?int $projectId): array
    {
        if (! $projectId) {
            return $rankingResults;
        }

        $connection = \App\Models\GscConnection::where('project_id', $projectId)->first();
        if (! $connection) {
            return $rankingResults;
        }

        try {
            $gsc = app(GoogleSearchConsoleService::class);
            $keywords = array_keys($rankingResults);
            $gscPositions = $gsc->getKeywordPositions($connection, $keywords);

            foreach ($rankingResults as $kw => &$result) {
                $gscData = $gscPositions[$kw] ?? null;
                $result['gsc_position']    = $gscData['position'] ?? null;
                $result['gsc_clicks']      = $gscData['clicks'] ?? null;
                $result['gsc_impressions'] = $gscData['impressions'] ?? null;
                $result['gsc_ctr']         = $gscData['ctr'] ?? null;
                $result['gsc_data_source'] = $gscData ? 'Google Search Console' : null;
            }

            return $rankingResults;
        } catch (\Throwable $e) {
            Log::warning('GSC enrichment failed', ['error' => $e->getMessage()]);
            return $rankingResults;
        }
    }
}
