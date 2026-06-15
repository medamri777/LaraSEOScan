<?php
namespace App\Jobs;

use App\Models\SeoScan;
use App\Services\SeoScannerService;
use App\Services\SeoScoreCalculator;
use App\Jobs\CheckPageSpeed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ProcessSeoScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scan;

    public function __construct(SeoScan $scan)
    {
        $this->scan = $scan;
    }

    public function handle(SeoScannerService $scanner, SeoScoreCalculator $scoreCalculator)
    {
        $startTime = microtime(true);

        $scanner->scan($this->scan);

        $scoreCalculator->calculateAndSave($this->scan);

        $elapsedTime = (int) round(microtime(true) - $startTime);
        $this->scan->update([
            'status' => 'COMPLETED',
            'time_elapsed' => $elapsedTime
        ]);

        CheckPageSpeed::dispatch($this->scan);
    }
}
