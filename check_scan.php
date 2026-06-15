<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$url = 'https://medflix.cyou/';

echo "=== Testing with Chrome User-Agent ===\n";
try {
    $client = new \GuzzleHttp\Client([
        'timeout' => 10,
        'allow_redirects' => ['track_redirects' => true],
        'http_errors' => false,
        'verify' => false,
        'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'],
    ]);
    
    $response = $client->get($url);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body length: " . strlen((string) $response->getBody()) . "\n";
    
    if ($response->getStatusCode() === 200) {
        $html = (string) $response->getBody();
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html, $url);
        $title = $crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'NO TITLE';
        echo "Title: $title\n";
        
        $linkCount = $crawler->filter('a')->count();
        echo "Links found: $linkCount\n";
        echo "\nSUCCESS! The crawler will now work correctly.\n";
    } else {
        echo "Still blocked. Status: " . $response->getStatusCode() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
