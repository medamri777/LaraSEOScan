<?php

namespace App\Jobs;

use App\Services\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class PingSearchEnginesJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct()
    {
        $this->onQueue('seo');
    }

    public function handle(SitemapService $service): void
    {
        $service->pingSearchEngines();
    }
}
