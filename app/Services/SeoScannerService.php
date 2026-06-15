<?php

namespace App\Services;

use App\Models\SeoScan;
use App\Models\SeoPage;
use App\Models\SeoLink;
use App\Models\SeoImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use App\Seo\Rules\Registry;
use App\Models\SeoIssue;
use App\Services\Seo\RobotsTxtService;
use App\Services\Seo\SitemapService;
use App\Services\Seo\KeywordDensityService;
use Illuminate\Support\Facades\Log;

class SeoScannerService
{
    protected $visited = [];
    protected $maxDepth = 5;
    protected $maxPages = 200;
    protected $pageCount = 0;

    protected RobotsTxtService $robotsService;
    protected SitemapService $sitemapService;
    protected KeywordDensityService $keywordService;

    public function __construct(
        RobotsTxtService $robotsService,
        SitemapService $sitemapService,
        KeywordDensityService $keywordService
    ) {
        $this->robotsService = $robotsService;
        $this->sitemapService = $sitemapService;
        $this->keywordService = $keywordService;
    }

    public function scan(SeoScan $scan)
    {
        $this->crawlBatch([$scan->url], $scan, 0);

        $metrics = [
            'sitemap_urls_listed' => 0,
            'pages_matched' => 0,
            'extra_pages' => 0,
            'visited_sitemaps' => 0,
        ];

        // Post-crawl: Sitemap Analysis
        $sitemapUrlsFromService = $this->sitemapService->getUrls();
        if (!empty($sitemapUrlsFromService)) {
            $crawledUrls = SeoPage::where('seo_scan_id', $scan->id)->pluck('url')->toArray();
            $crawledUrls = array_map(fn($u) => rtrim($u, '/'), $crawledUrls);

            $sitemapUrls = array_map(fn($u) => rtrim($u, '/'), $sitemapUrlsFromService);
            
            $metrics['sitemap_urls_listed'] = count($sitemapUrls);
            $metrics['visited_sitemaps'] = 1;

            $matched = array_intersect($sitemapUrls, $crawledUrls);
            $metrics['pages_matched'] = count($matched);
            
            $extra = array_diff($crawledUrls, $sitemapUrls);
            $metrics['extra_pages'] = count($extra);

            // Orphan check (in Sitemap but not crawled)
            $missing = array_diff($sitemapUrls, $crawledUrls);

            $firstPage = $scan->pages()->first();
            if ($firstPage && !empty($missing)) {
                foreach (array_slice($missing, 0, 50) as $missingUrl) {
                    SeoIssue::create([
                        'seo_page_id' => $firstPage->id,
                        'rule_key'    => 'sitemap.missing_page',
                        'severity'    => 'warning',
                        'message'     => 'Page in sitemap but not crawled.',
                        'context'     => ['url' => $missingUrl],
                    ]);
                }
            }
        }

        $totalFound = SeoLink::whereHas('page', function($q) use ($scan) {
            $q->where('seo_scan_id', $scan->id);
        })->distinct('href')->count('href');

        // Mark scan as completed and save metrics
        $scan->crawled_metrics = $metrics;
        $scan->total_urls_found = $totalFound;
        $scan->status = 'COMPLETED';
        $scan->save();
    }

    protected function crawlAndScan(string $url, SeoScan $scan, int $depth = 0)
    {
        if ($depth > $this->maxDepth || isset($this->visited[$url]) || $this->pageCount >= $this->maxPages) {
            return;
        }

        $this->visited[$url] = true;
        $this->pageCount++;

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'])
                ->get($url);
        } catch (\Exception $e) {
            return;
        }

        if (!$response->successful()) {
            return;
        }

        $html = $response->body();
        $crawler = new Crawler($html, $url);

        $headings = [];
        foreach (range(1, 6) as $level) {
            $crawler->filter("h{$level}")->each(function ($node) use (&$headings, $level) {
                $headings[] = [
                    'tag'  => "h{$level}",
                    'text' => trim($node->text()),
                ];
            });
        }

        $page = SeoPage::create([
            'seo_scan_id' => $scan->id,
            'url'         => $url,
            'title'       => optional($crawler->filter('title'))->count() ? $crawler->filter('title')->text() : null,
            'description' => optional($crawler->filter('meta[name="description"]'))->count() ? $crawler->filter('meta[name="description"]')->attr('content') : null,
            'canonical'   => $crawler->filter('link[rel=canonical]')->count() ? $crawler->filter('link[rel=canonical]')->attr('href') : null,
            'headings'    => $headings,
        ]);
        $this->runRules($page, $html);

        // Extract links
        $crawler->filter('a')->each(function ($node) use ($page, $url) {
            $href = $node->attr('href');
            if (!$href) return;
            $absoluteUrl = $this->resolveUrl($href, $url);
            $status = null;

            try {
                $head = Http::timeout(5)->head($absoluteUrl);
                $status = $head->status();
            } catch (\Exception $e) {
                $status = null;
            }

            SeoLink::create([
                'seo_page_id' => $page->id,
                'href'        => $absoluteUrl,
                'status_code' => $status,
            ]);
        });

        // Extract images
        $crawler->filter('img')->each(function ($node) use ($page) {
            $src = $node->attr('src');
            $alt = $node->attr('alt');
            if (!$src) return;

            SeoImage::create([
                'seo_page_id' => $page->id,
                'src'         => $src,
                'alt'         => $alt,
            ]);
        });

        // Crawl internal links recursively
        $crawler->filter('a')->each(function ($node) use ($scan, $url, $depth) {
            $href = $node->attr('href');
            if (!$href || Str::startsWith($href, ['mailto:', 'tel:', '#'])) return;

            $linkUrl = $this->resolveUrl($href, $url);
            if ($this->isInternal($linkUrl, $scan->url)) {
                $this->crawlAndScan($linkUrl, $scan, $depth + 1);
            }
        });
    }

    protected function resolveUrl($relative, $base)
    {
        return (string) \GuzzleHttp\Psr7\UriResolver::resolve(new \GuzzleHttp\Psr7\Uri($base), new \GuzzleHttp\Psr7\Uri($relative));
    }

    protected function isInternal($url, $base)
    {
        return parse_url($url, PHP_URL_HOST) === parse_url($base, PHP_URL_HOST);
    }

    protected function checkLinks(array $links): array
    {
        $client = $this->getClient([
            'timeout'         => 5,
            'allow_redirects' => true,
            'headers'         => ['User-Agent' => 'Seo4maBot/1.0'],
        ]);

        $results = [];

        $requests = function ($links) use ($client) {
            foreach ($links as $link) {
                yield function () use ($client, $link) {
                    return $client->headAsync($link);
                };
            }
        };

        $pool = new Pool($client, $requests($links), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index) use (&$results, $links) {
                $results[$links[$index]] = $response->getStatusCode();
            },
            'rejected'    => function ($reason, $index) use (&$results, $links) {
                $results[$links[$index]] = null;
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    protected function fetchRobotsTxt(string $url): ?string
    {
        try {
            return Http::timeout(5)->get($url . '/robots.txt')->body();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function fetchSitemap(string $url): ?string
    {
        try {
            return Http::timeout(5)->get($url . '/sitemap.xml')->body();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function runRules(SeoPage $page, string $html): void
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!empty($html)) {
            try {
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
            } catch (\Exception $e) {
                @$dom->loadHTML($html);
            }
        }
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        foreach (Registry::all() as $rule) {
            try {
                $issues = $rule->check($page, $dom, $xpath);

                foreach ($issues as $issue) {
                    SeoIssue::create([
                        'seo_page_id' => $page->id,
                        'rule_key'    => $rule->key(),
                        'severity'    => $issue['severity'] ?? 'info',
                        'message'     => $issue['message'] ?? 'Unknown issue',
                        'selector'    => $issue['selector'] ?? null,
                        'context'     => $issue['context'] ?? null,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Rule failed: " . get_class($rule) . " - " . $e->getMessage());
            }
        }
    }

    /**
     * Crawl multiple URLs in parallel with Pool
     */
    protected function crawlBatch(array $urls, SeoScan $scan, int $depth)
    {
        if ($depth > $this->maxDepth || $this->pageCount >= $this->maxPages) {
            return;
        }

        $client = $this->getClient([
            'timeout'         => 10,
            'allow_redirects' => [
                'track_redirects' => true,
            ],
            'http_errors' => false,
            'verify'      => false,
            'headers'     => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'],
        ]);

        $urls = array_values(array_filter($urls, function ($u) {
            if (isset($this->visited[$u])) return false;
            try {
                if (!$this->robotsService->isAllowed($u)) {
                    return false;
                }
            } catch (\Exception $e) {
                Log::warning("Robots check failed for $u: " . $e->getMessage());
            }
            return true;
        }));

        if (empty($urls)) return;

        foreach ($urls as $u) {
            $this->visited[$u] = true;
        }

        $requests = function ($urls) use ($client, &$responseTimes) {
            foreach ($urls as $url) {
                yield function() use ($client, $url, &$responseTimes) {
                    return $client->getAsync($url, [
                        'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$responseTimes, $url) {
                            $responseTimes[$url] = (int) ($stats->getTransferTime() * 1000);
                        },
                    ]);
                };
            }
        };

        $nextBatch = [];
        $responseTimes = [];

        $pool = new Pool($client, $requests($urls), [
            'concurrency' => 5,
            'fulfilled'   => function ($response, $index) use ($urls, $scan, &$nextBatch, $depth) {
                $url = $urls[$index];
                try {
                    if ($this->pageCount >= $this->maxPages) return;

                    $statusCode = $response->getStatusCode();
                    $html    = (string) $response->getBody();

                    // For non-200 pages, still record them as error pages
                    if ($statusCode >= 400) {
                        SeoPage::create([
                            'seo_scan_id' => $scan->id,
                            'url'         => $url,
                            'status_code' => $statusCode,
                            'title'       => null,
                            'description' => null,
                            'canonical'   => null,
                            'headings'    => [],
                        ]);
                        $this->pageCount++;
                        return;
                    }

                    // For redirects (3xx), record them but don't parse content
                    if ($statusCode >= 300 && $statusCode < 400) {
                        SeoPage::create([
                            'seo_scan_id' => $scan->id,
                            'url'         => $url,
                            'status_code' => $statusCode,
                            'title'       => null,
                            'description' => null,
                            'canonical'   => null,
                            'headings'    => [],
                        ]);
                        $this->pageCount++;
                        return;
                    }

                    $crawler = new Crawler($html, $url);

                    $headings = [];
                    foreach (range(1, 6) as $level) {
                        $crawler->filter("h{$level}")->each(function ($node) use (&$headings, $level) {
                            $headings[] = [
                                'tag'  => "h{$level}",
                                'text' => trim($node->text()),
                            ];
                        });
                    }

                    // Calculate metrics
                    $density = $this->keywordService->analyze($html);

                    // Create page & run rules
                    $page = SeoPage::create([
                        'seo_scan_id'      => $scan->id,
                        'url'              => $url,
                        'status_code'      => $statusCode,
                        'title'            => $crawler->filter('title')->count() ? $crawler->filter('title')->text() : null,
                        'description'      => $crawler->filter('meta[name="description"]')->count() ? $crawler->filter('meta[name="description"]')->attr('content') : null,
                        'canonical'        => $crawler->filter('link[rel=canonical]')->count() ? $crawler->filter('link[rel=canonical]')->attr('href') : null,
                        'headings'         => $headings,
                        'keyword_density'  => $density,
                        'response_time_ms' => $responseTimes[$url] ?? null,
                        'content_type'     => $response->getHeaderLine('Content-Type') ?: null,
                    ]);

                    $page->redirect_history = $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER);

                    $this->pageCount++;

                    // Links
                    $crawler->filter('a')->each(function ($node) use ($page, $url, &$nextBatch, $scan) {
                        $href = $node->attr('href');
                        if (!$href || Str::startsWith($href, ['mailto:', 'tel:', '#'])) return;
                        $absoluteUrl = $this->resolveUrl($href, $url);

                        SeoLink::create([
                            'seo_page_id' => $page->id,
                            'href'        => $absoluteUrl,
                            'status_code' => null,
                            'is_internal' => $this->isInternal($absoluteUrl, $scan->url),
                        ]);

                        if ($this->isInternal($absoluteUrl, $scan->url)) {
                            $nextBatch[] = $absoluteUrl;
                        }
                    });

                    // Images
                    $crawler->filter('img')->each(function ($node) use ($page) {
                        $src = $node->attr('src');
                        if (!$src) return;
                        SeoImage::create([
                            'seo_page_id' => $page->id,
                            'src'         => $src,
                            'alt'         => $node->attr('alt'),
                        ]);
                    });

                    // Run Rules AFTER links/images are saved so rules can access relationships
                    $this->runRules($page, $html);
                } catch (\Exception $e) {
                    Log::error("Failed processing page $url: " . $e->getMessage());
                }
            },
            'rejected' => function ($reason, $index) use ($urls) {
                Log::warning("Failed to crawl {$urls[$index]}: " . $reason->getMessage());
            },
        ]);

        $pool->promise()->wait();

        if (!empty($nextBatch) && $this->pageCount < $this->maxPages) {
            $delay = config('seo.crawler.request_delay_ms', 0);
            if ($delay > 0) {
                usleep($delay * 1000);
            }
            $this->crawlBatch(array_unique($nextBatch), $scan, $depth + 1);
        }
    }

    protected function getClient(array $config = []): Client
    {
        if (app()->bound(Client::class)) {
            return app(Client::class);
        }
        return new Client($config);
    }
}