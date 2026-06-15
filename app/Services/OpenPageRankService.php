<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Free domain authority scoring via the OpenPageRank API.
 *
 * Endpoint: https://openpagerank.com/api/v1.0/getPageRank
 * - No authentication required
 * - No rate limit
 * - Returns page_rank_decimal (0–10 scale) per domain
 * - Accepts multiple domains in a single call via domains[]= query params
 */
class OpenPageRankService
{
    private const BASE_URL = 'https://openpagerank.com/api/v1.0/getPageRank';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openpagerank.api_key', '');
    }

    /**
     * Check if the OpenPageRank API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the PageRank score (0–10) for a single domain.
     * Cached for 24 hours.
     */
    public function getScore(string $domain): ?float
    {
        $domain = $this->cleanDomain($domain);
        $cacheKey = "openpagerank_score_{$domain}";

        return Cache::remember($cacheKey, 3600 * 24, function () use ($domain) {
            $scores = $this->fetchScores([$domain]);
            return $scores[$domain] ?? null;
        });
    }

    /**
     * Get PageRank scores for multiple domains in one API call.
     * Each result cached individually for 24 hours.
     *
     * @param  array<int, string>  $domains
     * @return array<string, float|null>  domain => score (0–10) or null on failure
     */
    public function getScores(array $domains): array
    {
        $domains = array_unique(array_map(fn ($d) => $this->cleanDomain($d), $domains));

        // Separate cached vs. uncached
        $results = [];
        $toFetch = [];

        foreach ($domains as $domain) {
            $cacheKey = "openpagerank_score_{$domain}";
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $results[$domain] = $cached;
            } else {
                $toFetch[] = $domain;
            }
        }

        if (!empty($toFetch)) {
            $fetched = $this->fetchScores($toFetch);

            foreach ($toFetch as $domain) {
                $score = $fetched[$domain] ?? null;
                $results[$domain] = $score;

                // Cache even null results (shorter TTL) to avoid repeated failed lookups
                Cache::put(
                    "openpagerank_score_{$domain}",
                    $score,
                    $score !== null ? 3600 * 24 : 3600
                );
            }
        }

        return $results;
    }

    /**
     * Convert a 0–10 PageRank score to a 0–100 authority score
     * (compatible with the existing AuthorityCheckerService scale).
     */
    public function toAuthorityScore(?float $pageRank): int
    {
        if ($pageRank === null) {
            return 0;
        }
        // Linear map: 0→0, 10→100
        return (int) min(100, max(0, round($pageRank * 10)));
    }

    /**
     * Human-readable label for a 0–100 authority score.
     */
    public function authorityLabel(int $score): string
    {
        return match (true) {
            $score < 20  => 'New / Unknown',
            $score < 40  => 'Weak',
            $score < 60  => 'Moderate',
            $score < 80  => 'Strong',
            default      => 'Excellent',
        };
    }

    /**
     * Bootstrap color class for a 0–100 authority score.
     */
    public function authorityColor(int $score): string
    {
        return match (true) {
            $score < 20  => 'secondary',
            $score < 40  => 'danger',
            $score < 60  => 'warning',
            $score < 80  => 'info',
            default      => 'success',
        };
    }

    // ─── Internal ──────────────────────────────────────────────────────────────

    /**
     * Fetch scores from the OpenPageRank API for a batch of domains.
     *
     * @param  array<int, string>  $domains
     * @return array<string, float|null>
     */
    private function fetchScores(array $domains): array
    {
        if (empty($domains)) {
            return [];
        }

        try {
            // Build query string: ?domains[]=a.com&domains[]=b.com
            $query = http_build_query(
                array_map(fn ($d) => $d, $domains),
                'domains',
                '&'
            );
            // http_build_query with numeric keys produces domains[0]=, domains[1]= ...
            // OpenPageRank expects domains[]= so rebuild manually:
            $params = array_map(fn ($d) => 'domains[]=' . urlencode($d), $domains);
            $url = self::BASE_URL . '?' . implode('&', $params);

            $response = Http::withHeaders([
                'User-Agent' => 'Seo4ma/1.0',
                'API-OPR'    => $this->apiKey,
            ])->timeout(10)->get($url);

            if (!$response->successful()) {
                Log::warning('OpenPageRank API failed', [
                    'status'  => $response->status(),
                    'domains' => $domains,
                ]);
                return array_fill_keys($domains, null);
            }

            $data = $response->json();

            // Expected response shape:
            // { "status_code": 200, "response": [ { "status_code": 200, "page_rank_integer": 5, "page_rank_decimal": 5.3, "domain": "example.com" }, ... ] }
            $results = [];
            $items = $data['response'] ?? [];

            foreach ($items as $item) {
                $domain = $item['domain'] ?? null;
                if ($domain) {
                    $score = $item['page_rank_decimal'] ?? ($item['page_rank_integer'] ?? null);
                    $results[$domain] = is_numeric($score) ? (float) $score : null;
                }
            }

            // Fill in any missing domains with null
            foreach ($domains as $domain) {
                if (!isset($results[$domain])) {
                    $results[$domain] = null;
                }
            }

            Log::info('OpenPageRank scores fetched', ['results' => $results]);

            return $results;

        } catch (\Throwable $e) {
            Log::warning('OpenPageRank API exception', [
                'message' => $e->getMessage(),
                'domains' => $domains,
            ]);
            return array_fill_keys($domains, null);
        }
    }

    /**
     * Strip protocol and www prefix from a domain.
     */
    private function cleanDomain(string $domain): string
    {
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', trim($domain));
        return explode('/', $domain)[0];
    }
}
