<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Traits\UsesProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class SitemapCrawlerController extends Controller
{
    use UsesProjectDomain;
    public function index(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'sitemap_crawler')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Please upgrade your plan to access Sitemap Crawler.'], 403);
            }
            return redirect()->route('pricing')->with('error', 'Please upgrade your plan to access Sitemap Crawler.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $this->crawl($request);
        }

        return view('tools.sitemap-crawler', [
            'languages' => [
                'fr' => 'French',
                'ar' => 'Arabic',
                'ary' => 'Darija',
                'en' => 'English',
            ],
            'pageOptions' => [
                10 => '10 pages',
                50 => '50 pages',
                100 => '100 pages',
                500 => '500 pages',
                0 => 'Unlimited',
            ],
        ]);
    }

    public function status($taskId)
    {
        $progress = Cache::get("sitemap_progress_{$taskId}");
        if (!$progress) {
            return response()->json(['status' => 'unknown']);
        }
        return response()->json($progress);
    }

    public function result($taskId)
    {
        $result = Cache::get("sitemap_result_{$taskId}");
        if (!$result) {
            return response()->json(['error' => 'Result not found'], 404);
        }
        return response()->json($result);
    }

    private function crawl(Request $request)
    {
        // Enforce daily limit before crawling
        $user = auth()->user();
        $result = app(\App\Services\PlanLimitService::class)->checkAndRecordDailyUsage($user?->tenant, $user?->id, 'sitemap_crawler');
        if (! $result['allowed']) {
            return response()->json([
                'error'            => $result['message'] ?? 'Daily sitemap crawler limit reached.',
                'upgrade_required' => true,
            ], 402);
        }

        $request->validate([
            'url' => ['nullable'],
        ]);

        // Force URL from active project — ignore whatever was submitted
        $url = $this->requireProject();
        $rawMaxPages = (int) $request->input('max_pages', 50);
        $maxPages = $rawMaxPages > 0 ? $rawMaxPages : 999999;
        $taskId = $request->input('task_id', Str::uuid()->toString());

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL — please enter a full URL like https://example.com'], 422);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'http_errors' => false,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; Seo4ma-SitemapCrawler/1.0)',
                ],
            ]);

            $result = $this->runCrawl($client, $url, $maxPages, $taskId);

            Cache::put("sitemap_progress_{$taskId}", [
                'status' => 'completed',
                'progress' => 100,
                'found' => count($result['urls']),
                'crawled' => $result['crawled'],
            ], 600);

            Cache::put("sitemap_result_{$taskId}", $result, 600);

            return response()->json([
                'task_id' => $taskId,
                'sitemap' => $result['xml'],
                'stats' => $result['stats'],
                'status' => 'completed',
            ]);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Cache::put("sitemap_progress_{$taskId}", ['status' => 'error', 'error' => 'Cannot reach the website'], 600);
            return response()->json(['error' => 'Cannot reach the website — ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            Cache::put("sitemap_progress_{$taskId}", ['status' => 'error', 'error' => $e->getMessage()], 600);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function runCrawl($client, $startUrl, $maxPages, $taskId)
    {
        $parsed = parse_url($startUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $baseUrl = "{$scheme}://{$host}";

        $visited = [];
        $queue = [['url' => $this->normalizeUrl($startUrl), 'depth' => 0]];
        $foundUrls = [];

        $crawled = 0;
        $cap = min($maxPages, 500);
        $startTime = time();
        $timeout = 300;

        while (!empty($queue) && $crawled < $cap) {
            if (time() - $startTime > $timeout) {
                break;
            }

            $item = array_shift($queue);
            $crawlUrl = $item['url'];
            $depth = $item['depth'];

            if (in_array($crawlUrl, $visited)) continue;
            $visited[] = $crawlUrl;

            if (!in_array($crawlUrl, $foundUrls)) {
                $foundUrls[] = $crawlUrl;
            }

            $crawled++;

            Cache::put("sitemap_progress_{$taskId}", [
                'status' => 'crawling',
                'progress' => min(round(($crawled / $cap) * 100), 99),
                'crawled' => $crawled,
                'total' => $cap,
                'found' => count($foundUrls),
                'current_url' => $crawlUrl,
            ], 600);

            try {
                $response = $client->get($crawlUrl, ['timeout' => 10]);
                $html = (string) $response->getBody();

                if (!empty($html)) {
                    $links = $this->extractLinks($html, $crawlUrl);

                    foreach ($links as $href) {
                        $linkParsed = parse_url($href);
                        if (!$linkParsed || !isset($linkParsed['host'])) continue;
                        if ($linkParsed['host'] !== $host) continue;
                        if (preg_match('/\.(pdf|zip|rar|doc|docx|xls|xlsx|ppt|pptx|jpg|jpeg|png|gif|svg|webp|ico|css|js|woff2?|ttf|eot|mp[34]|avi|mov|flv|xml|json|txt|mpg|mpeg)$/i', $href)) continue;

                        if (preg_match('#/(admin|login|logout|register|favorites|auth|user-profile|cdn-cgi)/#i', $href)) continue;
                        if (preg_match('#/trap_#i', $href)) continue;
                        if (stripos($href, 'email-protection') !== false) continue;
                        if (preg_match('/[?&](logout|ref)=/i', $href)) continue;
                        if (!$this->isUsefulUrl($href)) continue;

                        $normalized = $this->normalizeUrl($href);

                        if (!$this->isInternal($normalized, $host)) continue;
                        if (in_array($normalized, $visited)) continue;
                        if (in_array($normalized, $foundUrls)) continue;

                        $foundUrls[] = $normalized;

                        if ($depth < 10 && count($foundUrls) < $cap) {
                            $queue[] = ['url' => $normalized, 'depth' => $depth + 1];
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $elapsed = time() - $startTime;
        $timedOut = $elapsed >= $timeout;

        $xml = $this->generateSitemapXml($foundUrls, $baseUrl);

        return [
            'xml' => $xml,
            'urls' => $foundUrls,
            'crawled' => $crawled,
            'stats' => [
                'total_urls' => count($foundUrls),
                'pages_crawled' => $crawled,
                'elapsed' => $elapsed,
                'timed_out' => $timedOut,
                'host' => $host,
            ],
        ];
    }

    private function extractLinks($html, $baseUri)
    {
        $links = [];
        if (empty(trim($html))) return $links;

        try {
            $crawler = new DomCrawler($html, $baseUri);
            $linkNodes = $crawler->filter('a')->links();

            foreach ($linkNodes as $link) {
                try {
                    $uri = $link->getUri();
                    if (filter_var($uri, FILTER_VALIDATE_URL)) {
                        $links[] = $uri;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            return $links;
        }

        return $links;
    }

    private function isInternal($url, $host)
    {
        $parsed = parse_url($url);
        return isset($parsed['host']) && $parsed['host'] === $host;
    }

    private function normalizeUrl($url)
    {
        $url = preg_replace('/#.*$/', '', $url);
        $url = preg_replace('/\?.*$/', '', $url);
        $url = rtrim($url, '/');
        $parts = parse_url($url);
        if (!isset($parts['path']) || $parts['path'] === '') {
            $url .= '/';
        }
        return $url;
    }

    private function isUsefulUrl(string $url): bool
    {
        if (str_contains($url, '_detail.php') && !str_contains($url, '?id=')) {
            return false;
        }
        return true;
    }

    private function generateSitemapXml($urls, $baseUrl)
    {
        $date = now()->format('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $changefreq = $this->detectChangeFreq($url);
            $priority = $this->detectPriority($url);

            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url, ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $date . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $priority . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function detectChangeFreq($url)
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';

        if ($path === '/' || $path === '') return 'weekly';
        if (preg_match('#/(blog|news|article|post|actualit|202[0-9]|20[0-9]{2})#i', $path)) return 'weekly';

        $trimmed = trim($path, '/');
        if (substr_count($trimmed, '/') <= 1) return 'monthly';

        return 'monthly';
    }

    private function detectPriority($url)
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';

        if ($path === '/' || $path === '') return '1.0';

        $trimmed = trim($path, '/');
        $depth = count(explode('/', $trimmed));

        if ($depth <= 1) return '0.8';
        if (preg_match('#(blog|news|article|post|product|item)#i', $path)) return '0.6';
        if (preg_match('#(archive|tag|category|page|author|search|categorie|etiquette)#i', $path)) return '0.3';
        if ($depth >= 4) return '0.3';

        return '0.5';
    }
}
