<?php

namespace App\Jobs;

use App\Services\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSitemapJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public bool $ping;

    public function __construct(bool $ping = true)
    {
        $this->ping = $ping;
        $this->onQueue('seo');
    }

    public function middleware(): array
    {
        return [new \Illuminate\Queue\Middleware\WithoutOverlapping('sitemap-generate')];
    }

    public function handle(SitemapService $service): void
    {
        $originalPing = config('seo.sitemap.ping_on_generate');
        config(['seo.sitemap.ping_on_generate' => $this->ping]);
        $service->generate();
        config(['seo.sitemap.ping_on_generate' => $originalPing]);
    }
}
