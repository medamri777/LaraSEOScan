<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

/**
 * SeoReportService
 * 
 * Comprehensive SEO analysis service combining:
 * - symfony/dom-crawler for HTML parsing
 * - Google PageSpeed Insights API for Core Web Vitals
 * - Custom rules for on-page SEO checks
 */
class SeoReportService
{
    protected ?string $pageSpeedApiKey;

    public function __construct()
    {
        $this->pageSpeedApiKey = config('services.google_pagespeed.api_key');
    }

    /**
     * Run a complete single-page SEO analysis
     */
    public function analyze(string $url): array
    {
        // 1. Fetch the page
        $response = $this->fetchPage($url);
        $html = $response['html'];
        $effectiveUrl = $response['effectiveUrl'];
        $headers = $response['headers'];
        $httpStatus = $response['status'];

        // 2. Parse HTML with Symfony DomCrawler
        $crawler = new Crawler($html, $effectiveUrl);
        $dom = $this->loadDom($html);
        $xpath = new \DOMXPath($dom);

        // 3. Extract all SEO data
        $general = $this->extractGeneral($crawler, $effectiveUrl, $headers);
        $headings = $this->extractHeadings($xpath);
        $images = $this->extractImages($crawler, $effectiveUrl);
        $links = $this->extractLinks($crawler, $effectiveUrl);
        $schemas = $this->extractSchemas($crawler);
        $social = $this->extractSocial($crawler);
        $technical = $this->extractTechnical($crawler, $effectiveUrl, $headers);

        // 4. Fetch robots.txt and sitemap.xml
        $baseUrl = parse_url($effectiveUrl, PHP_URL_SCHEME) . '://' . parse_url($effectiveUrl, PHP_URL_HOST);
        $robotsTxt = $this->fetchRobotsTxt($baseUrl);
        $sitemap = $this->fetchSitemap($baseUrl);

        // 5. Run diagnostics
        $diagnostics = $this->runDiagnostics($general, $headings, $images, $links, $schemas, $social, $technical);

        // 6. Fetch PageSpeed data (non-blocking, graceful on timeout)
        try {
            $pageSpeed = $this->fetchPageSpeed($effectiveUrl);
        } catch (\Throwable $e) {
            $pageSpeed = ['available' => false, 'reason' => 'PageSpeed request failed: ' . $e->getMessage()];
        }

        return [
            'url' => $effectiveUrl,
            'http_status' => $httpStatus,
            'general' => $general,
            'headings' => $headings,
            'images' => $images,
            'links' => $links,
            'schemas' => $schemas,
            'social' => $social,
            'technical' => $technical,
            'robots_txt' => $robotsTxt,
            'sitemap' => $sitemap,
            'diagnostics' => $diagnostics,
            'pagespeed' => $pageSpeed,
        ];
    }

    /**
     * Fetch a page with proper headers
     */
    protected function fetchPage(string $url): array
    {
        $response = Http::withoutVerifying()
            ->timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Upgrade-Insecure-Requests' => '1',
            ])
            ->get($url);

        return [
            'html' => $response->body(),
            'effectiveUrl' => $response->effectiveUri() ? (string) $response->effectiveUri() : $url,
            'headers' => $response->headers(),
            'status' => $response->status(),
        ];
    }

    /**
     * Load DOMDocument for XPath queries
     */
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

    /**
     * Extract general meta information
     */
    protected function extractGeneral(Crawler $crawler, string $baseUrl, array $headers): array
    {
        // Title
        $titleText = $crawler->filter('title')->count() > 0 ? trim($crawler->filter('title')->text()) : '';
        $titleLen = mb_strlen($titleText);
        $titleStatus = $titleLen === 0 ? 'missing' : ($titleLen < 30 ? 'short' : ($titleLen > 60 ? 'long' : 'optimal'));

        // Meta Description
        $descNode = $crawler->filter('meta[name="description"], meta[name="Description"]');
        $descText = $descNode->count() > 0 ? trim($descNode->attr('content') ?? '') : '';
        $descLen = mb_strlen($descText);
        $descStatus = $descLen === 0 ? 'missing' : ($descLen < 110 ? 'short' : ($descLen > 160 ? 'long' : 'optimal'));

        // Canonical
        $canonical = $crawler->filter('link[rel="canonical"]')->count() > 0
            ? trim($crawler->filter('link[rel="canonical"]')->attr('href') ?? '')
            : null;
        $canonicalMatches = !empty($canonical) && (rtrim($canonical, '/') === rtrim($baseUrl, '/'));

        // Robots Meta
        $robotsNode = $crawler->filter('meta[name="robots"], meta[name="Robots"]');
        $robotsMeta = $robotsNode->count() > 0 ? trim($robotsNode->attr('content') ?? '') : '';

        // X-Robots-Tag header
        $headerKeys = array_change_key_case($headers, CASE_LOWER);
        $xRobots = isset($headerKeys['x-robots-tag'])
            ? (is_array($headerKeys['x-robots-tag']) ? implode(', ', $headerKeys['x-robots-tag']) : $headerKeys['x-robots-tag'])
            : '';

        // Author & Generator
        $authorNode = $crawler->filter('meta[name="author"], link[rel="author"]');
        $author = $authorNode->count() > 0 ? ($authorNode->attr('content') ?? $authorNode->attr('href') ?? '') : '';
        $genNode = $crawler->filter('meta[name="generator"]');
        $generator = $genNode->count() > 0 ? ($genNode->attr('content') ?? '') : '';

        // Viewport
        $viewportNode = $crawler->filter('meta[name="viewport"]');
        $viewport = $viewportNode->count() > 0 ? ($viewportNode->attr('content') ?? '') : '';

        // Language
        $lang = $crawler->filter('html')->count() > 0 ? ($crawler->filter('html')->attr('lang') ?? '') : '';

        // Favicon
        $faviconNode = $crawler->filter('link[rel="icon"], link[rel="shortcut icon"]');
        $favicon = $faviconNode->count() > 0 ? ($faviconNode->attr('href') ?? '') : '';

        // HTTP Status
        $isHttps = Str::startsWith($baseUrl, 'https://');

        // Content-Type
        $contentType = isset($headerKeys['content-type'])
            ? (is_array($headerKeys['content-type']) ? $headerKeys['content-type'][0] : $headerKeys['content-type'])
            : '';

        // Response time approximation
        $serverHeader = isset($headerKeys['server'])
            ? (is_array($headerKeys['server']) ? $headerKeys['server'][0] : $headerKeys['server'])
            : '';

        return [
            'title' => ['text' => $titleText, 'length' => $titleLen, 'status' => $titleStatus],
            'description' => ['text' => $descText, 'length' => $descLen, 'status' => $descStatus],
            'canonical' => $canonical,
            'canonical_matches' => $canonicalMatches,
            'robots' => $robotsMeta,
            'x_robots' => $xRobots,
            'author' => $author,
            'generator' => $generator,
            'viewport' => $viewport,
            'language' => $lang,
            'favicon' => $favicon,
            'is_https' => $isHttps,
            'content_type' => $contentType,
            'server' => $serverHeader,
        ];
    }

    /**
     * Extract headings structure
     */
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

    /**
     * Extract images with alt text analysis
     */
    protected function extractImages(Crawler $crawler, string $baseUrl): array
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
            'total' => $total,
            'with_alt' => $withAlt,
            'missing_alt' => $missingAlt,
            'empty_alt' => $emptyAlt,
            'list' => $list,
        ];
    }

    /**
     * Extract links with internal/external classification
     */
    protected function extractLinks(Crawler $crawler, string $baseUrl): array
    {
        $list = [];
        $total = 0;
        $internal = 0;
        $external = 0;
        $nofollow = 0;
        $dofollow = 0;
        $broken = 0;

        $baseHost = parse_url($baseUrl, PHP_URL_HOST);

        $crawler->filter('a')->each(function ($node) use (&$list, &$total, &$internal, &$external, &$nofollow, &$dofollow, $baseUrl, $baseHost) {
            $href = $node->attr('href') ?? '';
            $text = trim($node->text());

            if (empty($href) || Str::startsWith($href, ['mailto:', 'tel:', '#', 'javascript:'])) {
                return;
            }

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
            'total' => $total,
            'internal' => $internal,
            'external' => $external,
            'nofollow' => $nofollow,
            'dofollow' => $dofollow,
            'list' => $list,
        ];
    }

    /**
     * Extract JSON-LD structured data
     */
    protected function extractSchemas(Crawler $crawler): array
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

    /**
     * Extract Open Graph and Twitter Card data
     */
    protected function extractSocial(Crawler $crawler): array
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

    /**
     * Extract technical SEO signals
     */
    protected function extractTechnical(Crawler $crawler, string $baseUrl, array $headers): array
    {
        // Hreflang
        $hreflangs = [];
        $crawler->filter('link[rel="alternate"][hreflang]')->each(function ($node) use (&$hreflangs) {
            $hreflangs[] = [
                'hreflang' => $node->attr('hreflang') ?? '',
                'href' => $node->attr('href') ?? '',
            ];
        });

        // Pagination
        $pagination = [];
        $crawler->filter('link[rel="prev"], link[rel="next"]')->each(function ($node) use (&$pagination) {
            $rel = $node->attr('rel') ?? '';
            $pagination[$rel] = $node->attr('href') ?? '';
        });

        // Response headers
        $headerKeys = array_change_key_case($headers, CASE_LOWER);
        $contentType = isset($headerKeys['content-type']) ? (is_array($headerKeys['content-type']) ? $headerKeys['content-type'][0] : $headerKeys['content-type']) : '';
        $server = isset($headerKeys['server']) ? (is_array($headerKeys['server']) ? $headerKeys['server'][0] : $headerKeys['server']) : '';
        $xPoweredBy = isset($headerKeys['x-powered-by']) ? (is_array($headerKeys['x-powered-by']) ? $headerKeys['x-powered-by'][0] : $headerKeys['x-powered-by']) : '';

        // Check for redirect
        $redirectChain = [];
        if (isset($headerKeys['location'])) {
            $redirectChain = is_array($headerKeys['location']) ? $headerKeys['location'] : [$headerKeys['location']];
        }

        return [
            'hreflangs' => $hreflangs,
            'pagination' => $pagination,
            'content_type' => $contentType,
            'server' => $server,
            'x_powered_by' => $xPoweredBy,
            'redirect_chain' => $redirectChain,
            'is_https' => Str::startsWith($baseUrl, 'https://'),
        ];
    }

    /**
     * Fetch robots.txt
     */
    protected function fetchRobotsTxt(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->get($baseUrl . '/robots.txt');
            return [
                'exists' => $response->successful(),
                'content' => $response->successful() ? $response->body() : null,
                'url' => $baseUrl . '/robots.txt',
            ];
        } catch (\Exception $e) {
            return ['exists' => false, 'content' => null, 'url' => $baseUrl . '/robots.txt'];
        }
    }

    /**
     * Fetch sitemap.xml
     */
    protected function fetchSitemap(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->get($baseUrl . '/sitemap.xml');
            $urls = [];
            if ($response->successful()) {
                $xml = @simplexml_load_string($response->body());
                if ($xml) {
                    foreach ($xml->url ?? [] as $url) {
                        $urls[] = (string) $url->loc;
                    }
                }
            }
            return [
                'exists' => $response->successful(),
                'url_count' => count($urls),
                'urls' => array_slice($urls, 0, 100),
                'url' => $baseUrl . '/sitemap.xml',
            ];
        } catch (\Exception $e) {
            return ['exists' => false, 'url_count' => 0, 'urls' => [], 'url' => $baseUrl . '/sitemap.xml'];
        }
    }

    /**
     * Fetch Google PageSpeed Insights data
     */
    protected function fetchPageSpeed(string $url): array
    {
        if (empty($this->pageSpeedApiKey)) {
            return ['available' => false, 'reason' => 'API key not configured'];
        }

        try {
            $response = Http::timeout(15)->get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
                'url' => $url,
                'key' => $this->pageSpeedApiKey,
                'strategy' => 'mobile',
                'category' => ['performance', 'seo', 'accessibility', 'best-practices'],
            ]);

            if (!$response->successful()) {
                return ['available' => false, 'reason' => 'API error: ' . $response->status()];
            }

            $json = $response->json();
            $lighthouse = $json['lighthouseResult'] ?? [];
            $categories = $lighthouse['categories'] ?? [];
            $audits = $lighthouse['audits'] ?? [];

            return [
                'available' => true,
                'scores' => [
                    'performance' => isset($categories['performance']['score']) ? (int) round($categories['performance']['score'] * 100) : null,
                    'seo' => isset($categories['seo']['score']) ? (int) round($categories['seo']['score'] * 100) : null,
                    'accessibility' => isset($categories['accessibility']['score']) ? (int) round($categories['accessibility']['score'] * 100) : null,
                    'best_practices' => isset($categories['best-practices']['score']) ? (int) round($categories['best-practices']['score'] * 100) : null,
                ],
                'core_vitals' => [
                    'lcp' => $audits['largest-contentful-paint']['numericValue'] ?? null,
                    'fcp' => $audits['first-contentful-paint']['numericValue'] ?? null,
                    'cls' => $audits['cumulative-layout-shift']['numericValue'] ?? null,
                    'ttfb' => $audits['server-response-time']['numericValue'] ?? null,
                    'inp' => $audits['interaction-to-next-paint']['numericValue'] ?? null,
                ],
                'opportunities' => collect($audits)->filter(function ($audit, $key) {
                    return isset($audit['details']['type']) && $audit['details']['type'] === 'opportunity' && ($audit['score'] ?? 1) < 0.9;
                })->map(function ($audit, $key) {
                    return [
                        'key' => $key,
                        'title' => $audit['title'] ?? '',
                        'savings_ms' => $audit['details']['overallSavingsMs'] ?? 0,
                    ];
                })->sortByDesc('savings_ms')->values()->take(5)->toArray(),
            ];
        } catch (\Throwable $e) {
            return ['available' => false, 'reason' => $e->getMessage()];
        }
    }

    /**
     * Run comprehensive diagnostics
     */
    protected function runDiagnostics(array $general, array $headings, array $images, array $links, array $schemas, array $social, array $technical): array
    {
        $errors = [];
        $warnings = [];
        $successes = [];

        // 1. Title Tag
        if (empty($general['title']['text'])) {
            $errors[] = $this->issue('Meta Tags', 'Missing Title Tag', 'No <title> tag found. This is the most important on-page SEO element.', 'Add a unique <title> tag in the <head> section (30-60 characters).');
        } elseif ($general['title']['status'] === 'short') {
            $warnings[] = $this->issue('Meta Tags', 'Title Too Short', "Title is {$general['title']['length']} characters. Aim for 30-60 characters.", 'Expand your title to include relevant keywords.');
        } elseif ($general['title']['status'] === 'long') {
            $warnings[] = $this->issue('Meta Tags', 'Title Too Long', "Title is {$general['title']['length']} characters and may be truncated in search results.", 'Shorten your title to under 60 characters.');
        } else {
            $successes[] = $this->issue('Meta Tags', 'Title Tag Optimized', "Title is {$general['title']['length']} characters — perfect length.", null, true);
        }

        // 2. Meta Description
        if (empty($general['description']['text'])) {
            $errors[] = $this->issue('Meta Tags', 'Missing Meta Description', 'No meta description found. This affects click-through rates.', 'Add a compelling meta description (110-160 characters).');
        } elseif ($general['description']['status'] === 'short') {
            $warnings[] = $this->issue('Meta Tags', 'Description Too Short', "Description is {$general['description']['length']} characters.", 'Expand to at least 110 characters.');
        } elseif ($general['description']['status'] === 'long') {
            $warnings[] = $this->issue('Meta Tags', 'Description Too Long', "Description is {$general['description']['length']} characters and may be truncated.", 'Shorten to under 160 characters.');
        } else {
            $successes[] = $this->issue('Meta Tags', 'Meta Description Optimized', "Description is {$general['description']['length']} characters — perfect.", null, true);
        }

        // 3. H1 Tag
        $h1Count = $headings['counts']['h1'] ?? 0;
        if ($h1Count === 0) {
            $errors[] = $this->issue('Headings', 'Missing H1 Tag', 'No H1 tag found. The H1 indicates the main topic of the page.', 'Add exactly one <h1> tag with your primary keyword.');
        } elseif ($h1Count > 1) {
            $warnings[] = $this->issue('Headings', 'Multiple H1 Tags', "Found {$h1Count} H1 tags. This dilutes the page's main topic signal.", 'Use only one H1 tag. Convert extras to H2 or H3.');
        } else {
            $successes[] = $this->issue('Headings', 'Single H1 Tag', 'Exactly one H1 tag found — perfect structure.', null, true);
        }

        // 4. H2 Tags
        $h2Count = $headings['counts']['h2'] ?? 0;
        if ($h2Count === 0) {
            $warnings[] = $this->issue('Headings', 'No H2 Tags', 'No H2 tags found. H2s structure your content into sections.', 'Add H2 tags to organize your content into logical sections.');
        } else {
            $successes[] = $this->issue('Headings', 'H2 Tags Present', "Found {$h2Count} H2 tags — good content structure.", null, true);
        }

        // 5. Canonical URL
        if (empty($general['canonical'])) {
            $errors[] = $this->issue('Indexation', 'Missing Canonical URL', 'No canonical tag found. This can lead to duplicate content issues.', 'Add <link rel="canonical" href="..."> in the <head> section.');
        } elseif (!$general['canonical_matches']) {
            $warnings[] = $this->issue('Indexation', 'Canonical Mismatch', "Canonical URL points to a different page than the current URL.", 'Verify this is intentional. If not, update the canonical tag.');
        } else {
            $successes[] = $this->issue('Indexation', 'Canonical URL Set', 'Canonical tag matches the current page URL.', null, true);
        }

        // 6. HTTPS
        if (!$general['is_https']) {
            $errors[] = $this->issue('Security', 'Not Using HTTPS', 'Page is served over HTTP. HTTPS is a ranking signal.', 'Install an SSL certificate and redirect HTTP to HTTPS.');
        } else {
            $successes[] = $this->issue('Security', 'HTTPS Enabled', 'Page is served over HTTPS — secure connection.', null, true);
        }

        // 7. Viewport
        if (empty($general['viewport'])) {
            $errors[] = $this->issue('Mobile', 'Missing Viewport Meta', 'No viewport meta tag found. This affects mobile rendering.', 'Add <meta name="viewport" content="width=device-width, initial-scale=1">.');
        } else {
            $successes[] = $this->issue('Mobile', 'Viewport Meta Present', 'Viewport meta tag is configured for responsive design.', null, true);
        }

        // 8. Images Alt
        if ($images['total'] > 0) {
            if ($images['missing_alt'] > 0) {
                $warnings[] = $this->issue('Images', "Missing Alt Text ({$images['missing_alt']})", "{$images['missing_alt']} of {$images['total']} images lack alt text.", 'Add descriptive alt text to all images for accessibility and SEO.');
            } else {
                $successes[] = $this->issue('Images', 'All Images Have Alt Text', "All {$images['total']} images have alt attributes.", null, true);
            }
        }

        // 9. Structured Data
        if (count($schemas) === 0) {
            $warnings[] = $this->issue('Structured Data', 'No Schema.org Found', 'No JSON-LD structured data detected.', 'Add JSON-LD schema (LocalBusiness, Article, FAQ, etc.) for rich snippets.');
        } else {
            $types = collect($schemas)->map(function ($s) {
                return $s['@type'] ?? (isset($s['@graph'][0]['@type']) ? $s['@graph'][0]['@type'] : 'Unknown');
            })->unique()->implode(', ');
            $successes[] = $this->issue('Structured Data', 'Schema.org Detected', "Found " . count($schemas) . " schema(s): {$types}", null, true);
        }

        // 10. Open Graph
        $ogComplete = !empty($social['og']['title']) && !empty($social['og']['description']) && !empty($social['og']['image']);
        if (!$ogComplete) {
            $missing = [];
            if (empty($social['og']['title'])) $missing[] = 'og:title';
            if (empty($social['og']['description'])) $missing[] = 'og:description';
            if (empty($social['og']['image'])) $missing[] = 'og:image';
            $warnings[] = $this->issue('Social', 'Incomplete Open Graph', 'Missing: ' . implode(', ', $missing), 'Add complete Open Graph tags for better social sharing.');
        } else {
            $successes[] = $this->issue('Social', 'Open Graph Complete', 'All essential Open Graph tags are present.', null, true);
        }

        // 11. Robots NoIndex
        if (Str::contains(strtolower($general['robots']), 'noindex')) {
            $warnings[] = $this->issue('Indexation', 'Page Blocked from Indexing', 'Meta robots contains "noindex". This page will not appear in search results.', 'Remove noindex if this page should be indexed.');
        } else {
            $successes[] = $this->issue('Indexation', 'Page Indexable', 'No restrictive robots directives found.', null, true);
        }

        // 12. Language
        if (empty($general['language'])) {
            $warnings[] = $this->issue('Accessibility', 'Missing HTML Lang Attribute', 'No lang attribute on the <html> tag.', 'Add lang="en" (or appropriate language) to the <html> tag.');
        } else {
            $successes[] = $this->issue('Accessibility', 'Language Declared', "HTML lang attribute is set to \"{$general['language']}\".", null, true);
        }

        // 13. Links
        if ($links['total'] === 0) {
            $warnings[] = $this->issue('Links', 'No Internal Links Found', 'No links detected on this page.', 'Add internal links to improve site navigation and SEO.');
        } elseif ($links['internal'] === 0) {
            $warnings[] = $this->issue('Links', 'No Internal Links', 'All links are external. Add internal links.', 'Link to other pages on your site for better crawlability.');
        } else {
            $successes[] = $this->issue('Links', 'Internal Links Present', "Found {$links['internal']} internal links.", null, true);
        }

        // Calculate score
        $deductions = (count($errors) * 12) + (count($warnings) * 5);
        $score = max(10, 100 - $deductions);

        return [
            'score' => $score,
            'errors' => $errors,
            'warnings' => $warnings,
            'successes' => $successes,
        ];
    }

    /**
     * Create a diagnostic issue
     */
    protected function issue(string $category, string $title, string $description, ?string $recommendation = null, bool $isSuccess = false): array
    {
        return [
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Resolve relative URL to absolute
     */
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
}
