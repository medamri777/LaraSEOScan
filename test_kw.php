<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear cache first
\Illuminate\Support\Facades\Cache::flush();

$svc = app(\App\Services\AiKeywordResearchService::class);
$result = $svc->research('avito.ma', 'fr');

echo "=== KEYWORD MAGIC TEST ===\n";
echo "Domain: " . $result['domain'] . "\n";
echo "Data Source: " . $result['data_source'] . "\n";
echo "Total Keywords: " . $result['total'] . "\n\n";

echo "First 5 keywords:\n";
foreach (array_slice($result['keywords'], 0, 5) as $i => $kw) {
    echo ($i+1) . ". " . $kw['keyword']
        . " | vol=" . $kw['volume']
        . " | src=" . $kw['source']
        . " | diff=" . $kw['difficulty']
        . " | cpc=" . $kw['cpc']
        . " | brand=" . ($kw['is_brand'] ? 'yes' : 'no')
        . "\n";
}
