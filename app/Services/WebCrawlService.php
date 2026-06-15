<?php

namespace App\Services;

use App\Models\SeoScan;
use App\Models\SeoPage;
use App\Models\SeoLink;
use App\Models\SeoImage;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class WebCrawlService
{
    protected array $visited = [];
    protected int $pageCount = 0;
    protected int $maxPages = 200;
    protected int $maxDepth = 10;
    protected int $concurrency = 5;
    protected int $timeout = 300;
    protected int $startTime;
    protected string $taskId;
    protected ?SeoScan $scan = null;
    protected string $baseHost = '';
    protected string $baseScheme = 'https';

    public function startCrawl(string $url, array $config = []): array
    {
        set_time_limit(0); // Prevent PHP execution timeout on long crawls

        $this->maxPages = $config['max_pages'] ?? 200;
        $this->maxDepth = $config['max_depth'] ?? 10;
        $this->concurrency = $config['concurrency'] ?? 5;
        $this->timeout = $config['timeout'] ?? 300;
        $this->taskId = $config['task_id'] ?? (string) Str::uuid();

        $parsed = parse_url($url);
        $this->baseScheme = $parsed['scheme'] ?? 'https';
        $this->baseHost = $parsed['host'] ?? '';
        $baseUrl = "{$this->baseScheme}://{$this->baseHost}";

        $this->scan = SeoScan::create([
            'uuid' => $this->taskId,
            'url' => $baseUrl,
            'status' => 'CRAWLING',
            'user_id' => $config['user_id'] ?? auth()->id(),
            'crawl_config' => $config,
        ]);

        Log::debug('WebCrawl: started', ['task_id' => $this->taskId, 'url' => $url, 'max_pages' => $this->maxPages]);

        $this->startTime = time();
        $this->visited = [];
        $this->pageCount = 0;

        $startUrl = $this->normalizeUrl($url);
        $this->markProgress('starting', 0, 0, $startUrl);

        $client = new Client([
            'timeout' => 30,
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; Seo4ma-WebCrawl/1.0; +https://seo4ma.local)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);

        try {
            Log::debug('WebCrawl: fetching seed', ['url' => $startUrl]);
            $response = $client->get($startUrl, ['timeout' => 30]);
        } catch (\Exception $e) {
            Log::warning('WebCrawl: seed fetch failed', ['url' => $startUrl, 'error' => $e->getMessage()]);
            $this->scan->update(['status' => 'FAILED']);
            $this->markProgress('error', 0, 0, $e->getMessage());
            return ['task_id' => $this->taskId, 'error' => 'Failed to fetch seed URL: ' . $e->getMessage()];
        }

        $html = (string) $response->getBody();
        $this->visited[] = $startUrl;

        $crawler = new DomCrawler($html, $startUrl);

        $seedPage = $this->processPageFromResponse($response, $startUrl, 0);

        if ($seedPage && !empty(trim($html))) {
            $links = $this->extractLinksFromNode($crawler, $startUrl);
            $queue = [];
            foreach ($links as $href) {
                $normalized = $this->normalizeUrl($href);
                if ($this->isInternal($normalized) && !in_array($normalized, $this->visited)) {
                    $queue[] = ['url' => $normalized, 'depth' => 1];
                    $this->visited[] = $normalized;
                }
            }
            $this->batchCrawl($client, $queue);
        }

        $elapsed = time() - $this->startTime;

        Log::debug('WebCrawl: completed', [
            'task_id' => $this->taskId, 'pages' => $this->pageCount, 'elapsed' => $elapsed,
        ]);

        $this->scan->update([
            'status' => $this->pageCount > 0 ? 'COMPLETED' : 'FAILED',
            'time_elapsed' => $elapsed,
            'total_urls_found' => $this->pageCount,
        ]);

        $this->markProgress('completed', 100, $this->pageCount);

        return [
            'task_id' => $this->taskId,
            'scan_id' => $this->scan->id,
            'total_pages' => $this->pageCount,
            'elapsed' => $elapsed,
        ];
    }

    protected function batchCrawl(Client $client, array $queue): void
    {
        $cap = min($this->maxPages, 500);

        while (!empty($queue) && $this->pageCount < $cap) {
            if (time() - $this->startTime > $this->timeout) break;

            $batch = array_splice($queue, 0, $this->concurrency);

            $requests = function () use ($client, $batch) {
                foreach ($batch as $item) {
                    yield function () use ($client, $item) {
                        return $client->getAsync($item['url'], ['timeout' => 30]);
                    };
                }
            };

            $nextLinks = [];

            $pool = new Pool($client, $requests(), [
                'concurrency' => $this->concurrency,
                'fulfilled' => function ($response, $index) use ($batch, &$nextLinks, $cap) {
                    if ($this->pageCount >= $this->maxPages) return;

                    $item = $batch[$index];
                    $url = $item['url'];
                    $depth = $item['depth'];

                    $html = (string) $response->getBody();
                    if (empty(trim($html))) return;

                    $crawler = new DomCrawler($html, $url);

                    $page = $this->processPageFromResponse($response, $url, $depth);

                    if ($page && $depth < $this->maxDepth) {
                        $links = $this->extractLinksFromNode($crawler, $url);
                        foreach ($links as $href) {
                            $normalized = $this->normalizeUrl($href);
                            if ($this->isInternal($normalized) && !in_array($normalized, $this->visited)) {
                                $nextLinks[] = ['url' => $normalized, 'depth' => $depth + 1];
                                $this->visited[] = $normalized;
                            }
                        }
                    }

                    $this->markProgress('crawling', $this->getProgressPct($cap), $this->pageCount, $url);
                },
                'rejected' => function ($reason, $index) use ($batch) {
                    // silent fail
                },
            ]);

            $pool->promise()->wait();

            foreach ($nextLinks as $link) {
                if (!in_array($link['url'], $this->visited)) {
                    $queue[] = $link;
                    $this->visited[] = $link['url'];
                }
            }
        }
    }

    protected function processPageFromResponse($response, string $url, int $depth): ?SeoPage
    {
        try {
            $statusCode = $response->getStatusCode();
            $html = (string) $response->getBody();
            $headers = array_change_key_case($response->getHeaders(), CASE_LOWER);

            try {
                $effectiveUri = $response->getEffectiveUri();
                $effectiveUrl = (string) ($effectiveUri ?? $url);
            } catch (\Throwable $e) {
                $effectiveUrl = $url;
            }

            if (empty(trim($html))) return null;

            $parsingStart = microtime(true);

            $crawler = new DomCrawler($html, $effectiveUrl);
            $dom = $this->loadDom($html);
            $xpath = new \DOMXPath($dom);

            $this->pageCount++;

            $general = $this->extractGeneral($crawler, $effectiveUrl, $headers);
            $headings = $this->extractHeadings($xpath);
            $images = $this->extractImages($crawler, $effectiveUrl);
            $links = $this->extractLinks($crawler, $effectiveUrl);
            $social = $this->extractSocial($crawler);
            $schemas = $this->extractSchemas($crawler);
            $technical = $this->extractTechnical($crawler, $effectiveUrl, $headers);

            $parsingTime = (int) ((microtime(true) - $parsingStart) * 1000);

            $page = SeoPage::create([
                'seo_scan_id' => $this->scan->id,
                'url' => $effectiveUrl,
                'status_code' => $statusCode,
                'title' => $general['title']['text'],
                'description' => $general['description']['text'],
                'canonical' => $general['canonical'],
                'robots' => $general['robots'],
                'headings' => $headings['list'],
                'word_count' => str_word_count(strip_tags($html)),
                'structured_data' => $schemas,
                'redirect_history' => $this->getRedirectHistory($response),
                'og_tags' => $social['og'],
                'twitter_tags' => $social['twitter'],
                'hreflangs' => $technical['hreflangs'],
                'content_type' => $technical['content_type'],
                'server' => $technical['server'],
                'x_powered_by' => $technical['x_powered_by'],
                'content_length' => strlen($html),
                'lang' => $general['language'],
                'viewport' => $general['viewport'],
                'favicon' => $general['favicon'],
                'author' => $general['author'],
                'generator' => $general['generator'],
                'x_robots_tag' => $general['x_robots'],
                'discovery_source' => $depth === 0 ? 'seed' : 'link',
                'response_time_ms' => $parsingTime,
                'depth' => $depth,
            ]);

            foreach ($images['list'] as $img) {
                SeoImage::create([
                    'seo_page_id' => $page->id,
                    'src' => $img['src'],
                    'alt' => $img['alt'],
                    'has_alt' => $img['has_alt'],
                    'is_empty_alt' => $img['is_empty_alt'],
                    'loading' => $img['loading'],
                    'width' => $img['width'],
                    'height' => $img['height'],
                ]);
            }

            foreach ($links['list'] as $link) {
                SeoLink::create([
                    'seo_page_id' => $page->id,
                    'href' => $link['href'],
                    'anchor_text' => $link['text'],
                    'status_code' => null,
                    'is_internal' => $link['is_internal'],
                    'rel' => $link['rel'],
                    'is_nofollow' => $link['is_nofollow'],
                ]);
            }

            return $page;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function extractGeneral(DomCrawler $crawler, string $baseUrl, array $headers): array
    {
        $titleText = $crawler->filter('title')->count() > 0 ? trim($crawler->filter('title')->text()) : '';
        $titleLen = mb_strlen($titleText);
        $titleStatus = $titleLen === 0 ? 'missing' : ($titleLen < 30 ? 'short' : ($titleLen > 60 ? 'long' : 'optimal'));

        $descNode = $crawler->filter('meta[name="description"], meta[name="Description"]');
        $descText = $descNode->count() > 0 ? trim($descNode->attr('content') ?? '') : '';
        $descLen = mb_strlen($descText);
        $descStatus = $descLen === 0 ? 'missing' : ($descLen < 110 ? 'short' : ($descLen > 160 ? 'long' : 'optimal'));

        $canonical = $crawler->filter('link[rel="canonical"]')->count() > 0
            ? trim($crawler->filter('link[rel="canonical"]')->attr('href') ?? '')
            : null;

        $robotsNode = $crawler->filter('meta[name="robots"], meta[name="Robots"]');
        $robotsMeta = $robotsNode->count() > 0 ? trim($robotsNode->attr('content') ?? '') : '';

        $headerKeys = array_change_key_case($headers, CASE_LOWER);
        $xRobots = isset($headerKeys['x-robots-tag'])
            ? (is_array($headerKeys['x-robots-tag']) ? implode(', ', $headerKeys['x-robots-tag']) : $headerKeys['x-robots-tag'])
            : '';

        $authorNode = $crawler->filter('meta[name="author"], link[rel="author"]');
        $author = $authorNode->count() > 0 ? ($authorNode->attr('content') ?? $authorNode->attr('href') ?? '') : '';
        $genNode = $crawler->filter('meta[name="generator"]');
        $generator = $genNode->count() > 0 ? ($genNode->attr('content') ?? '') : '';
        $viewportNode = $crawler->filter('meta[name="viewport"]');
        $viewport = $viewportNode->count() > 0 ? ($viewportNode->attr('content') ?? '') : '';
        $lang = $crawler->filter('html')->count() > 0 ? ($crawler->filter('html')->attr('lang') ?? '') : '';
        $faviconNode = $crawler->filter('link[rel="icon"], link[rel="shortcut icon"]');
        $favicon = $faviconNode->count() > 0 ? ($faviconNode->attr('href') ?? '') : '';
        $isHttps = Str::startsWith($baseUrl, 'https://');

        $contentType = $headers['content-type'][0] ?? '';

        return [
            'title' => ['text' => $titleText, 'length' => $titleLen, 'status' => $titleStatus],
            'description' => ['text' => $descText, 'length' => $descLen, 'status' => $descStatus],
            'canonical' => $canonical,
            'robots' => $robotsMeta,
            'x_robots' => $xRobots,
            'author' => $author,
            'generator' => $generator,
            'viewport' => $viewport,
            'language' => $lang,
            'favicon' => $favicon,
            'is_https' => $isHttps,
        ];
    }

    protected function extractHeadings(\DOMXPath $xpath): array
    {
        $list = [];
        $counts = ['h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0];
        $nodes = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        if ($nodes) {
            foreach ($nodes as $node) {
                $tag = strtolower($node->nodeName);
                $text = trim($node->textContent);
                $list[] = ['tag' => $tag, 'text' => $text];
                $counts[$tag]++;
            }
        }
        return ['counts' => $counts, 'list' => $list];
    }

    protected function extractImages(DomCrawler $crawler, string $baseUrl): array
    {
        $list = [];
        $total = 0;
        $withAlt = 0;
        $missingAlt = 0;
        $emptyAlt = 0;

        $crawler->filter('img')->each(function ($node) use (&$list, &$total, &$withAlt, &$missingAlt, &$emptyAlt, $baseUrl) {
            $src = $node->attr('src') ?? '';
            if (empty($src)) return;
            $alt = $node->attr('alt');
            $hasAlt = ($alt !== null && trim($alt) !== '');
            $isEmptyAlt = ($alt !== null && trim($alt) === '');
            $absSrc = $this->resolveUrl($src, $baseUrl);

            $list[] = [
                'src' => $absSrc,
                'alt' => $alt ?? '',
                'has_alt' => $hasAlt,
                'is_empty_alt' => $isEmptyAlt,
                'loading' => $node->attr('loading') ?? '',
                'width' => $node->attr('width') ?? '',
                'height' => $node->attr('height') ?? '',
            ];
            $total++;
            if ($hasAlt) $withAlt++;
            elseif ($isEmptyAlt) $emptyAlt++;
            else $missingAlt++;
        });

        return [
            'total' => $total, 'with_alt' => $withAlt, 'missing_alt' => $missingAlt, 'empty_alt' => $emptyAlt, 'list' => $list,
        ];
    }

    protected function extractLinks(DomCrawler $crawler, string $baseUrl): array
    {
        $list = [];
        $total = 0;
        $internal = 0;
        $external = 0;
        $nofollow = 0;
        $dofollow = 0;
        $baseHost = parse_url($baseUrl, PHP_URL_HOST);

        $crawler->filter('a')->each(function ($node) use (&$list, &$total, &$internal, &$external, &$nofollow, &$dofollow, $baseUrl, $baseHost) {
            $href = $node->attr('href') ?? '';
            $text = trim($node->text());
            if (empty($href) || Str::startsWith($href, ['mailto:', 'tel:', '#', 'javascript:'])) return;
            $absHref = $this->resolveUrl($href, $baseUrl);
            $linkHost = parse_url($absHref, PHP_URL_HOST);
            $isInternal = ($linkHost === $baseHost || $linkHost === 'www.' . $baseHost || 'www.' . $linkHost === $baseHost);
            $rel = strtolower($node->attr('rel') ?? '');
            $isNofollow = Str::contains($rel, 'nofollow');

            $list[] = [
                'href' => $absHref,
                'text' => empty($text) ? '[No anchor text]' : Str::limit($text, 60),
                'is_internal' => $isInternal,
                'is_nofollow' => $isNofollow,
                'rel' => $rel,
            ];
            $total++;
            if ($isInternal) $internal++; else $external++;
            if ($isNofollow) $nofollow++; else $dofollow++;
        });

        return [
            'total' => $total, 'internal' => $internal, 'external' => $external,
            'nofollow' => $nofollow, 'dofollow' => $dofollow, 'list' => $list,
        ];
    }

    protected function extractLinksFromNode(DomCrawler $crawler, string $baseUri): array
    {
        $links = [];
        if (!empty(trim($crawler->html() ?? ''))) {
            try {
                $linkNodes = $crawler->filter('a')->links();
                foreach ($linkNodes as $link) {
                    try {
                        $uri = $link->getUri();
                        if (filter_var($uri, FILTER_VALIDATE_URL)) {
                            if (preg_match('/\.(pdf|zip|rar|doc|docx|xls|xlsx|ppt|pptx|jpg|jpeg|png|gif|svg|webp|ico|css|js|woff2?|ttf|eot|mp[34]|avi|mov|flv|xml|json|txt)$/i', $uri)) continue;
                            if (preg_match('#/(admin|login|logout|register|auth|wp-admin|cdn-cgi)/#i', $uri)) continue;
                            $links[] = $uri;
                        }
                    } catch (\Exception $e) { continue; }
                }
            } catch (\Exception $e) {}
        }
        return $links;
    }

    protected function extractSocial(DomCrawler $crawler): array
    {
        $social = [
            'og' => ['title' => '', 'description' => '', 'image' => '', 'url' => '', 'site_name' => '', 'type' => '', 'locale' => ''],
            'twitter' => ['card' => '', 'title' => '', 'description' => '', 'image' => '', 'site' => '', 'creator' => ''],
        ];
        $crawler->filter('meta[property^="og:"]')->each(function ($node) use (&$social) {
            $property = substr($node->attr('property') ?? '', 3);
            if (array_key_exists($property, $social['og'])) {
                $social['og'][$property] = $node->attr('content') ?? '';
            }
        });
        $crawler->filter('meta[name^="twitter:"]')->each(function ($node) use (&$social) {
            $name = substr($node->attr('name') ?? '', 8);
            if (array_key_exists($name, $social['twitter'])) {
                $social['twitter'][$name] = $node->attr('content') ?? '';
            }
        });
        return $social;
    }

    protected function extractSchemas(DomCrawler $crawler): array
    {
        $list = [];
        $crawler->filter('script[type="application/ld+json"]')->each(function ($node) use (&$list) {
            $rawJson = trim($node->text());
            if (empty($rawJson)) return;
            $decoded = json_decode($rawJson, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                $list[] = $decoded;
            }
        });
        return $list;
    }

    protected function extractTechnical(DomCrawler $crawler, string $baseUrl, array $headers): array
    {
        $hreflangs = [];
        $crawler->filter('link[rel="alternate"][hreflang]')->each(function ($node) use (&$hreflangs) {
            $hreflangs[] = [
                'hreflang' => $node->attr('hreflang') ?? '',
                'href' => $node->attr('href') ?? '',
            ];
        });
        $pagination = [];
        $crawler->filter('link[rel="prev"], link[rel="next"]')->each(function ($node) use (&$pagination) {
            $rel = $node->attr('rel') ?? '';
            $pagination[$rel] = $node->attr('href') ?? '';
        });
        $headerKeys = array_change_key_case($headers, CASE_LOWER);
        $contentType = $headerKeys['content-type'][0] ?? '';
        $server = $headerKeys['server'][0] ?? '';
        $xPoweredBy = $headerKeys['x-powered-by'][0] ?? '';
        return [
            'hreflangs' => $hreflangs,
            'pagination' => $pagination,
            'content_type' => $contentType,
            'server' => $server,
            'x_powered_by' => $xPoweredBy,
            'is_https' => Str::startsWith($baseUrl, 'https://'),
        ];
    }

    protected function getRedirectHistory($response): array
    {
        try {
            $history = $response->getHeader('X-Guzzle-Redirect-Status-History');
            if (!empty($history)) return $history;
        } catch (\Exception $e) {}
        return [];
    }

    protected function loadDom(string $html): \DOMDocument
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
        return $dom;
    }

    protected function resolveUrl(string $relative, string $base): string
    {
        try {
            return (string) \GuzzleHttp\Psr7\UriResolver::resolve(
                new \GuzzleHttp\Psr7\Uri($base),
                new \GuzzleHttp\Psr7\Uri($relative)
            );
        } catch (\Exception $e) {
            return $relative;
        }
    }

    protected function normalizeUrl(string $url): string
    {
        $url = preg_replace('/#.*$/', '', $url);
        $url = rtrim($url, '/');
        $parts = parse_url($url);
        if (!isset($parts['path']) || $parts['path'] === '') {
            $url .= '/';
        }
        return $url;
    }

    protected function isInternal(string $url): bool
    {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) return false;
        $host = $parsed['host'];
        return $host === $this->baseHost
            || $host === 'www.' . $this->baseHost
            || 'www.' . $host === $this->baseHost;
    }

    protected function markProgress(string $status, int $progress, int $found, ?string $currentUrl = null): void
    {
        Cache::put("crawl_progress_{$this->taskId}", [
            'status' => $status,
            'progress' => min($progress, 100),
            'found' => $found,
            'current_url' => $currentUrl,
            'max_pages' => $this->maxPages,
            'elapsed' => time() - $this->startTime,
        ], now()->addMinutes(30));
    }

    protected function getProgressPct(int $cap): int
    {
        return $cap > 0 ? (int) round(($this->pageCount / $cap) * 100) : 0;
    }

    public function getProgress(string $taskId): ?array
    {
        return Cache::get("crawl_progress_{$taskId}");
    }

    public function getCrawlData(int $scanId): array
    {
        $scan = SeoScan::with(['pages.links', 'pages.images', 'pages.issues'])->findOrFail($scanId);
        $pages = $scan->pages;
        $links = $pages->flatMap->links;
        $images = $pages->flatMap->images;

        $linkStats = ['total' => 0, 'internal' => 0, 'external' => 0, 'nofollow' => 0, 'dofollow' => 0, 'broken' => 0];
        foreach ($links as $link) {
            $linkStats['total']++;
            if ($link->is_internal) $linkStats['internal']++; else $linkStats['external']++;
            if ($link->is_nofollow) $linkStats['nofollow']++; else $linkStats['dofollow']++;
            if ($link->status_code >= 400) $linkStats['broken']++;
        }

        $imageStats = ['total' => 0, 'with_alt' => 0, 'missing_alt' => 0];
        foreach ($images as $img) {
            $imageStats['total']++;
            if ($img->has_alt) $imageStats['with_alt']++;
            else $imageStats['missing_alt']++;
        }

        $titleStats = ['total' => 0, 'missing' => 0, 'short' => 0, 'long' => 0, 'optimal' => 0, 'duplicates' => 0];
        $descStats = ['total' => 0, 'missing' => 0, 'short' => 0, 'long' => 0, 'optimal' => 0];
        $h1Stats = ['total' => 0, 'missing' => 0, 'multiple' => 0, 'single' => 0];
        $statusCodes = [];
        $titleTexts = [];
        $titles = [];
        $descriptions = [];
        $h1Statuses = [];

        foreach ($pages as $page) {
            $titleLen = mb_strlen($page->title ?? '');
            $descLen = mb_strlen($page->description ?? '');
            $titleStats['total']++;
            $descStats['total']++;

            if (empty($page->title)) $titleStats['missing']++;
            elseif ($titleLen < 30) $titleStats['short']++;
            elseif ($titleLen > 60) $titleStats['long']++;
            else $titleStats['optimal']++;

            if (empty($page->description)) $descStats['missing']++;
            elseif ($descLen < 110) $descStats['short']++;
            elseif ($descLen > 160) $descStats['long']++;
            else $descStats['optimal']++;

            $h1Count = 0;
            if ($page->headings) {
                $h1Count = collect($page->headings)->where('tag', 'h1')->count();
            }
            if ($h1Count === 0) $h1Stats['missing']++;
            elseif ($h1Count > 1) $h1Stats['multiple']++;
            else $h1Stats['single']++;
            $h1Statuses[] = ['url' => $page->url, 'count' => $h1Count];

            $code = $page->status_code ?? 0;
            $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;

            if (!empty($page->title)) $titleTexts[] = $page->title;
            $titles[] = ['url' => $page->url, 'title' => $page->title ?? '', 'length' => $titleLen];
            $descriptions[] = ['url' => $page->url, 'description' => $page->description ?? '', 'length' => $descLen];
        }

        $titleDuplicates = collect($titleTexts)->duplicates()->count();

        $schemas = $pages->filter(fn($p) => !empty($p->structured_data))->values();
        $schemaTypes = collect();
        foreach ($schemas as $page) {
            foreach ($page->structured_data ?? [] as $s) {
                $type = $s['@type'] ?? 'Unknown';
                $schemaTypes->push($type);
            }
        }

        return [
            'scan' => $scan,
            'pages' => $pages,
            'total_pages' => $pages->count(),
            'elapsed' => $scan->time_elapsed,
            'start_url' => $scan->url,
            'links' => $linkStats,
            'links_list' => $links,
            'images' => $imageStats,
            'images_list' => $images,
            'titles' => $titleStats,
            'title_list' => $titles,
            'descriptions' => $descStats,
            'desc_list' => $descriptions,
            'h1' => $h1Stats,
            'h1_list' => $h1Statuses,
            'status_codes' => $statusCodes,
            'title_duplicates' => $titleDuplicates,
            'schema_types' => $schemaTypes->unique()->values(),
            'has_robots' => optional($this->fetchUrl($scan->url . '/robots.txt'))->exists ?? false,
            'has_sitemap' => optional($this->fetchUrl($scan->url . '/sitemap.xml'))->exists ?? false,
        ];
    }

    protected function fetchUrl(string $url)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
            return (object) ['exists' => $response->successful(), 'body' => $response->body()];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getPage(int $pageId): ?SeoPage
    {
        return SeoPage::with(['links', 'images', 'issues'])->find($pageId);
    }
}
