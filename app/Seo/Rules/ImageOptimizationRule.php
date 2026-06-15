<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class ImageOptimizationRule implements SeoRule
{
    public function key(): string { return 'image.optimization'; }
    public function title(): string { return 'Image optimization checks'; }
    public function category(): string { return 'images'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        $images = [];
        
        // Extract images
        $nodes = $xpath->query('//img[@src]');
        foreach ($nodes as $node) {
            $src = $node->getAttribute('src');
            $loading = $node->getAttribute('loading');
            
            if (!$src) continue;
            
            // Check lazy loading
            if ($loading !== 'lazy') {
                 $issues[] = [
                    'rule' => 'image.no_lazy_loading',
                    'severity' => 'warning',
                    'message' => 'Image missing lazy loading attribute.',
                    'selector' => 'img[src="' . $src . '"]',
                    'context' => ['src' => $src],
                ];
            }
            
            // Resolve URL
            $fullUrl = $this->resolveUrl($src, $page->url);
            $images[] = ['src' => $src, 'url' => $fullUrl];
        }

        if (empty($images)) return $issues;

        // Check size and format via HEAD requests
        $client = new Client(['timeout' => 5, 'http_errors' => false]);
        
        $totalSize = 0;
        $unoptimizedCount = 0;
        
        $requests = function ($images) use ($client) {
            foreach ($images as $img) {
                yield function() use ($client, $img) {
                    return $client->sendAsync(new Request('HEAD', $img['url']));
                };
            }
        };

        $pool = new Pool($client, $requests($images), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use (&$issues, $images, &$totalSize, &$unoptimizedCount) {
                $img = $images[$index];
                
                // Check format
                $contentType = $response->getHeaderLine('Content-Type');
                if ($contentType && !preg_match('/image\/(webp|avif)/', $contentType)) {
                     // Only warn if it's jpeg/png
                     if (preg_match('/image\/(jpeg|png)/', $contentType)) {
                         $unoptimizedCount++;
                         $issues[] = [
                            'rule' => 'image.unoptimized_format',
                            'severity' => 'warning',
                            'message' => 'Image format is not WebP or AVIF.',
                            'selector' => 'img[src="' . $img['src'] . '"]',
                            'context' => ['src' => $img['src'], 'type' => $contentType],
                        ];
                     }
                }

                // Check size
                $contentLength = $response->getHeaderLine('Content-Length');
                if ($contentLength) {
                    $sizeKb = (int) $contentLength / 1024;
                    // Aggregate total size
                    // Since Guzzle promises run sequentially in loop, this is safeish
                    $totalSize += (int) $contentLength;
                    
                    if ($sizeKb > 200) {
                        $issues[] = [
                            'rule' => 'image.large_size',
                            'severity' => 'warning',
                            'message' => "Image size ({$sizeKb}KB) exceeds 200KB.",
                            'selector' => 'img[src="' . $img['src'] . '"]',
                            'context' => ['src' => $img['src'], 'size_kb' => $sizeKb],
                        ];
                    }
                }
            },
            'rejected' => function ($reason, $index) {
                // Ignore failed image checks
            },
        ]);

        $pool->promise()->wait();
        
        // Update Page model
        $page->image_total_size = $totalSize;
        $page->image_unoptimized_count = $unoptimizedCount;
        $page->saveQuietly();

        return $issues;
    }

    private function resolveUrl($url, $base)
    {
        if (parse_url($url, PHP_URL_SCHEME)) return $url;
        // Simple resolve, reusing logic or use library
        // Since we are inside a rule, we might duplicate slight logic or assume simple relative
        if (str_starts_with($url, '/')) {
             $baseParts = parse_url($base);
             return $baseParts['scheme'] . '://' . $baseParts['host'] . $url;
        }
        return rtrim($base, '/') . '/' . $url;
    }
}
