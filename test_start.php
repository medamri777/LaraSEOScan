<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Check the last scan test
$scan = App\Models\SeoScan::where('uuid', 'like', 'test-crawl-%')->latest()->first();
if ($scan) {
    echo "Last test scan: id={$scan->id} uuid={$scan->uuid} status={$scan->status} pages={$scan->total_urls_found}\n";
    echo "crawl_config: " . json_encode($scan->crawl_config) . "\n";
} else {
    echo "No test scan found\n";
}

// Now simulate the controller
$controller = $app->make(App\Http\Controllers\Tools\CrawlAuditController::class);
$request = new Illuminate\Http\Request();
$request->replace(['url' => 'https://httpbin.org', 'max_pages' => 3]);
$request->setMethod('POST');
$request->setUserResolver(function() {
    return App\Models\User::first();
});
$request->setLaravelSession($app['session']->driver());

try {
    $response = $controller->start($request);
    echo "Response status: " . $response->status() . "\n";
    echo "Response body: " . $response->getContent() . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
