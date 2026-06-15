<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Route URL: " . route('tools.crawl-audit.start') . "\n";
echo "Route action: " . json_encode(Illuminate\Support\Facades\Route::getRoutes()->getByName('tools.crawl-audit.start')->getAction()) . "\n";
echo "Route methods: " . json_encode(Illuminate\Support\Facades\Route::getRoutes()->getByName('tools.crawl-audit.start')->methods()) . "\n";
