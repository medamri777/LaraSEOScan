<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Illuminate\Support\Str;

class SitemapService
{
    protected array $urls = [];

    public function fetch(string $url): array
    {
        $this->urls = []; // Reset
        
        // Try sitemap.xml if URL is root
        if (!Str::endsWith($url, '.xml')) {
             $url = rtrim($url, '/') . '/sitemap.xml';
        }

        $this->parseSitemap($url);
        return array_unique($this->urls);
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    protected function parseSitemap(string $url): void
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return;
            }

            $content = $response->body();
            
            // Handle gzip
            if (Str::startsWith($content, "\x1f\x8b")) {
                $content = gzdecode($content);
            }

            try {
                $xml = new SimpleXMLElement($content);
            } catch (\Exception $e) {
                return;
            }

            // Check if sitemap index
            if (isset($xml->sitemap)) {
                foreach ($xml->sitemap as $sitemap) {
                    $this->parseSitemap((string) $sitemap->loc);
                }
            }
            // Check if regular sitemap
            elseif (isset($xml->url)) {
                foreach ($xml->url as $urlNode) {
                    $this->urls[] = (string) $urlNode->loc;
                }
            }
            
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
}
