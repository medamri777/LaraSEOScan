<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;
use App\Services\Seo\RobotsTxtService;
use App\Services\Seo\SitemapService;
use Illuminate\Support\Facades\Http;

class RobotsTxtValidationRule implements SeoRule
{
    protected static ?bool $hasRobotsTxt = null;
    protected static ?RobotsTxtService $robotsService = null;
    protected static ?array $sitemapUrls = null;

    public function key(): string { return 'technical.robots_validation'; }
    public function title(): string { return 'Robots.txt & Sitemap validation'; }
    public function category(): string { return 'technical'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        
        $parsed = parse_url($page->url);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '';
        if (empty($host)) {
            return [];
        }
        $baseUrl = $scheme . '://' . $host;

        // 1. Fetch & cache robots.txt status once per scan session
        if (self::$hasRobotsTxt === null) {
            try {
                $response = Http::timeout(5)->get($baseUrl . '/robots.txt');
                self::$hasRobotsTxt = $response->successful();
                
                if (self::$hasRobotsTxt) {
                    self::$robotsService = new RobotsTxtService();
                    self::$robotsService->fetch($baseUrl);
                }
            } catch (\Throwable $e) {
                self::$hasRobotsTxt = false;
            }
        }

        // 2. Fetch & cache sitemap URLs once per scan session
        if (self::$sitemapUrls === null) {
            try {
                $sitemapService = new SitemapService();
                self::$sitemapUrls = $sitemapService->fetch($baseUrl);
            } catch (\Throwable $e) {
                self::$sitemapUrls = [];
            }
        }

        // --- Rule Checks ---

        // Check A: Info if robots.txt is missing entirely
        if (self::$hasRobotsTxt === false) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'info',
                'message' => 'Robots.txt file is missing. Creating one is recommended to control crawl efficiency.',
                'context' => ['base_url' => $baseUrl],
            ];
        }

        // Check B: Warning if page is Disallow'd in robots.txt but still reachable (crawled)
        if (self::$hasRobotsTxt && self::$robotsService) {
            if (!self::$robotsService->isAllowed($page->url)) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => 'Page is blocked by robots.txt but was crawled. This indicates search engines might crawl it via internal links.',
                    'context' => ['url' => $page->url],
                ];
            }
        }

        // Check C: Error if page has "noindex" meta tag but is listed in sitemap.xml
        $metaRobots = $xpath->query('//meta[@name="robots"]');
        $hasNoindex = false;
        foreach ($metaRobots as $meta) {
            $content = strtolower($meta->getAttribute('content'));
            if (str_contains($content, 'noindex')) {
                $hasNoindex = true;
                break;
            }
        }

        if ($hasNoindex && !empty(self::$sitemapUrls)) {
            $normalizedUrl = rtrim($page->url, '/');
            $normalizedSitemapUrls = array_map(fn($u) => rtrim($u, '/'), self::$sitemapUrls);
            
            if (in_array($normalizedUrl, $normalizedSitemapUrls)) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'error',
                    'message' => 'Page has a "noindex" meta tag but is listed in sitemap.xml. This sends conflicting instructions to search engines.',
                    'context' => ['url' => $page->url],
                ];
            }
        }

        return $issues;
    }
}
