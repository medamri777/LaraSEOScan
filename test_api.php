<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$login = config('services.dataforseo.login');
$pass = config('services.dataforseo.password');

echo "Login: $login\n";

// Test the exact endpoint from existing KeywordOverviewService
echo "\n=== Test 1: kwrd/google/keywords_data/live (existing service endpoint) ===\n";
$r1 = Http::withBasicAuth($login, $pass)->timeout(30)->post(
    'https://api.dataforseo.com/v3/kwrd/google/keywords_data/live',
    [['keyword' => 'voiture', 'location_code' => 2504, 'language_code' => 'fr']]
);
echo "Status: {$r1->status()}\n";
echo substr($r1->body(), 0, 200) . "\n";

// Test SERP (used by existing DataForSeoService)
echo "\n=== Test 2: serp/google/organic/live/advanced (existing rankings service) ===\n";
$r2 = Http::withBasicAuth($login, $pass)->timeout(30)->post(
    'https://api.dataforseo.com/v3/serp/google/organic/live/advanced',
    [['keyword' => 'voiture occasion', 'location_code' => 2504, 'language_code' => 'fr', 'depth' => 10]]
);
echo "Status: {$r2->status()}\n";
echo substr($r2->body(), 0, 300) . "\n";

// Test Google Ads keyword planner endpoint
echo "\n=== Test 3: keywords_data/google_ads/search_volume/live ===\n";
$r3 = Http::withBasicAuth($login, $pass)->timeout(30)->post(
    'https://api.dataforseo.com/v3/keywords_data/google_ads/search_volume/live',
    [['keywords' => ['voiture occasion maroc', 'avito maroc'], 'location_code' => 2504, 'language_code' => 'fr']]
);
echo "Status: {$r3->status()}\n";
echo substr($r3->body(), 0, 300) . "\n";

// Test account info
echo "\n=== Test 4: Account info ===\n";
$r4 = Http::withBasicAuth($login, $pass)->timeout(10)->get(
    'https://api.dataforseo.com/v3/app_user'
);
echo "Status: {$r4->status()}\n";
echo substr($r4->body(), 0, 200) . "\n";
