<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\Illuminate\Support\Facades\Cache::flush();

echo "=== Testing full keyword research for avito.ma ===\n\n";

$svc = app(\App\Services\AiKeywordResearchService::class);
$result = $svc->research('avito.ma', 'fr');

echo "Total keywords: {$result['total']}\n";
echo "Data source: {$result['data_source']}\n";
echo "Niche: {$result['niche']}\n";
echo "Summary: {$result['summary']}\n\n";

echo "--- Top 15 Keywords ---\n";
foreach (array_slice($result['keywords'], 0, 15) as $i => $kw) {
    $pos = $kw['position'] ? "#{$kw['position']}" : '—';
    $vol = $kw['volume'] ?: '?';
    echo sprintf("%2d. %-50s vol:%-6s diff:%3d src:%-12s pos:%s\n",
        $i+1, $kw['keyword'], $vol, $kw['difficulty'], $kw['source'], $pos);
}

echo "\n--- Related Searches ---\n";
foreach (array_slice($result['related_searches'], 0, 8) as $s) {
    echo "  - $s\n";
}

echo "\n--- Questions ---\n";
foreach (array_slice($result['questions'], 0, 5) as $q) {
    echo "  ? $q\n";
}

echo "\n--- Site Info ---\n";
echo "Title: " . ($result['site_info']['title'] ?? '?') . "\n";
echo "Desc: " . mb_substr($result['site_info']['description'] ?? '', 0, 100) . "\n";
