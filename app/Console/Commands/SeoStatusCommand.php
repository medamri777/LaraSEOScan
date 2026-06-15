<?php

namespace App\Console\Commands;

use App\Services\RobotsService;
use App\Services\SitemapService;
use Illuminate\Console\Command;

class SeoStatusCommand extends Command
{
    protected $signature = 'seo:status';
    protected $description = 'Show SEO system status (robots cache, sitemap, URL count)';

    public function handle(RobotsService $robots, SitemapService $sitemap): int
    {
        $robotsContent = $robots->getCachedRobots();
        $robotsLines = substr_count($robotsContent, "\n");

        $lastGen = $sitemap->getLastGenerated();
        $urlCount = $sitemap->getUrlCount();
        $fileSize = $sitemap->getFileSize();
        $stats = $sitemap->getStats();

        $this->info('SEO System Status');
        $this->newLine();

        $rows = [
            ['Robots Cache', $robotsLines > 0 ? 'Cached (' . $robotsLines . ' lines)' : 'Empty'],
            ['Sitemap Last Build', $lastGen ? $lastGen->diffForHumans() : 'Never'],
            ['Sitemap URLs', number_format($urlCount)],
            ['Sitemap File Size', $fileSize > 0 ? number_format($fileSize) . ' bytes' : 'N/A'],
        ];

        foreach ($stats as $key => $val) {
            if ($key !== 'total') {
                $rows[] = ['Sitemap: ' . ucfirst($key), number_format($val)];
            }
        }

        $this->table(['Metric', 'Value'], $rows);

        return Command::SUCCESS;
    }
}
