<?php

namespace App\Services;

use App\Models\SitemapUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    const MAX_URLS_PER_SITEMAP = 10000;

    protected int $cacheTtl;
    protected bool $pingEnabled;
    protected bool $gzipEnabled;

    public function __construct()
    {
        $this->cacheTtl = (int) config('seo.sitemap.cache_ttl', 3600);
        $this->pingEnabled = (bool) config('seo.sitemap.ping_on_generate', true);
        $this->gzipEnabled = (bool) config('seo.sitemap.gzip', false);
    }

    public function generate(): array
    {
        $stats = ['manual' => 0, 'total' => 0];
        $index = SitemapIndex::create();
        $part = 0;
        $sitemap = Sitemap::create();

        // Manual URLs in chunks to avoid memory bloat
        SitemapUrl::active()->chunkById(100, function (Collection $urls) use ($sitemap, &$stats, &$part, $index) {
            foreach ($urls as $entry) {
                $url = Url::create($entry->loc)
                    ->setLastModificationDate($entry->lastmod ?? now())
                    ->setChangeFrequency($entry->changefreq)
                    ->setPriority($entry->priority);

                if ($entry->image_url) {
                    $url->addImage($entry->image_url);
                }

                $sitemap->add($url);
                $stats['manual']++;
                $stats['total']++;

                if (count($sitemap->getTags()) >= self::MAX_URLS_PER_SITEMAP) {
                    $this->writePart($sitemap, $part, $index);
                    $part++;
                }
            }
        });

        // Model URLs in chunks
        $modelConfigs = config('seo.sitemap.models', []);
        foreach ($modelConfigs as $modelClass => $config) {
            if (!class_exists($modelClass)) continue;

            $changefreq = $config['changefreq'] ?? config('seo.sitemap.default_changefreq', 'weekly');
            $priority = $config['priority'] ?? config('seo.sitemap.default_priority', 0.5);
            $modelCount = 0;

            $modelClass::query()->chunkById(100, function ($items) use ($sitemap, $changefreq, $priority, &$modelCount, &$part, $index) {
                foreach ($items as $item) {
                    $url = Url::create($item->url ?? url("/{$item->getKey()}"))
                        ->setLastModificationDate($item->updated_at ?? $item->created_at ?? now())
                        ->setChangeFrequency($changefreq)
                        ->setPriority($priority);

                    $sitemap->add($url);
                    $modelCount++;
                }

                if (count($sitemap->getTags()) >= self::MAX_URLS_PER_SITEMAP) {
                    $this->writePart($sitemap, $part, $index);
                    $part++;
                }
            });

            $stats[class_basename($modelClass)] = $modelCount;
            $stats['total'] += $modelCount;
        }

        // Flush remaining URLs
        if (count($sitemap->getTags()) > 0) {
            $this->writePart($sitemap, $part, $index);
            $part++;
        }

        // Write sitemap index (or single sitemap if < 2 parts)
        $path = public_path('sitemap.xml');
        if ($part > 1) {
            $index->writeToFile($path);
        } elseif ($part === 1) {
            // Single sitemap — use it directly, no index needed
            $firstPath = public_path('sitemap_0.xml');
            if (file_exists($firstPath)) {
                rename($firstPath, $path);
            }
        }

        if ($this->gzipEnabled && file_exists($path)) {
            $gzPath = $path . '.gz';
            $gz = gzopen($gzPath, 'w9');
            if ($gz) {
                gzwrite($gz, file_get_contents($path));
                gzclose($gz);
            }
        }

        Cache::put('sitemap_last_generated', now(), $this->cacheTtl);
        Cache::put('sitemap_url_count', $stats['total'], $this->cacheTtl);
        Cache::put('sitemap_stats', $stats, $this->cacheTtl);

        if ($this->pingEnabled) {
            $this->pingSearchEngines();
        }

        return $stats;
    }

    protected function writePart(Sitemap $sitemap, int $part, SitemapIndex $index): void
    {
        $path = public_path("sitemap_{$part}.xml");
        $sitemap->writeToFile($path);
        $index->add(url("sitemap_{$part}.xml"));
        // Clear the sitemap tags to free memory for the next chunk
        $sitemap->getTags(); // forces any internal state
        $ref = new \ReflectionClass($sitemap);
        $prop = $ref->getProperty('tags');
        $prop->setAccessible(true);
        $prop->setValue($sitemap, []);
    }

    public function pingSearchEngines(): void
    {
        $sitemapUrl = url('sitemap.xml');
        $endpoints = [
            "https://www.google.com/ping?sitemap={$sitemapUrl}",
            "https://www.bing.com/ping?sitemap={$sitemapUrl}",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                Http::timeout(10)->get($endpoint);
            } catch (\Throwable $e) {
                Log::warning("Sitemap ping failed: {$e->getMessage()}");
            }
        }
    }

    public function getLastGenerated(): ?\Carbon\Carbon
    {
        return Cache::get('sitemap_last_generated');
    }

    public function getUrlCount(): int
    {
        return (int) Cache::get('sitemap_url_count', 0);
    }

    public function getStats(): array
    {
        return Cache::get('sitemap_stats', []);
    }

    public function getFileSize(): int
    {
        $path = public_path('sitemap.xml');
        return file_exists($path) ? filesize($path) : 0;
    }

    public function clearCache(): void
    {
        Cache::forget('sitemap_last_generated');
        Cache::forget('sitemap_url_count');
        Cache::forget('sitemap_stats');
    }
}
