<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSitemapJob;
use App\Services\SitemapService;
use Illuminate\Console\Command;

class SitemapGenerateCommand extends Command
{
    protected $signature = 'sitemap:generate {--model=} {--ping}';
    protected $description = 'Generate the XML sitemap';

    public function handle(SitemapService $service): int
    {
        $model = $this->option('model');
        $ping = (bool) $this->option('ping');

        if ($model) {
            $this->info("Generating sitemap for model: {$model}");
        }

        if ($model) {
            dispatch(new GenerateSitemapJob($ping));
            $this->info('Sitemap generation job dispatched.');
        } else {
            $stats = $service->generate();
            $this->info('Sitemap generated successfully.');
            $this->table(['Source', 'Count'], collect($stats)->filter(fn($v, $k) => $k !== 'total')->map(fn($v, $k) => [ucfirst($k), $v]));
            $this->line("Total URLs: {$stats['total']}");
            $this->line("File size: " . $service->getFileSize() . ' bytes');
        }

        return Command::SUCCESS;
    }
}
