<?php

namespace App\Jobs;

use App\Models\SeoScan;
use App\Services\Seo\PageSpeedService;
use App\Services\SeoScoreCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckPageSpeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SeoScan $scan;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    public function __construct(SeoScan $scan)
    {
        $this->scan = $scan;
    }

    public function handle(PageSpeedService $pageSpeedService, SeoScoreCalculator $scoreCalculator)
    {
        Log::info("Starting PageSpeed check for scan: {$this->scan->id} (URL: {$this->scan->url})");

        $result = $pageSpeedService->analyze($this->scan->url);

        if ($result) {
            $this->scan->update([
                'pagespeed_performance'    => $result['scores']['performance'] ?? null,
                'pagespeed_seo'            => $result['scores']['seo'] ?? null,
                'pagespeed_accessibility'  => $result['scores']['accessibility'] ?? null,
                'pagespeed_best_practices' => $result['scores']['best_practices'] ?? null,
                'core_web_vitals'          => $result['core_vitals'] ?? null,
                'pagespeed_opportunities'  => $result['opportunities'] ?? null,
            ]);

            Log::info("PageSpeed check successful for scan {$this->scan->id}. Performance: {$result['scores']['performance']}");
        } else {
            Log::warning("PageSpeed check returned empty or failed for scan: {$this->scan->id}");
        }

        // Recalculate the score using the new PageSpeed performance metrics
        $scoreCalculator->calculateAndSave($this->scan);
    }
}
