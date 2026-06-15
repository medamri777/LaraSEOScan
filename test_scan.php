<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first() ?? App\Models\User::factory()->create();
$scan = App\Models\SeoScan::create([
    'user_id' => $user->id,
    'url' => 'https://example.com',
    'status' => 'QUEUED',
    'has_robots_txt' => true,
    'has_sitemap_xml' => true,
]);
App\Jobs\ProcessSeoScan::dispatchSync($scan);
$scan->refresh();
echo json_encode([
    'status' => $scan->status,
    'score_total' => $scan->score_total,
    'score_technical' => $scan->score_technical,
    'score_on_page' => $scan->score_on_page,
    'issues_count' => $scan->pages->sum(fn($p) => $p->issues->count()),
], JSON_PRETTY_PRINT);
