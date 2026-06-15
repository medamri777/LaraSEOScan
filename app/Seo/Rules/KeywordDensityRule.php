<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;
use App\Services\Seo\KeywordDensityService;

class KeywordDensityRule implements SeoRule
{
    protected $service;

    public function __construct(KeywordDensityService $service)
    {
        $this->service = $service;
    }

    public function key(): string { return 'content.keyword_density'; }
    public function title(): string { return 'Keyword density analysis'; }
    public function category(): string { return 'content'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        // Get HTML content - we need the raw HTML from somewhere?
        // SeoScannerService passes the HTML to runRules but the interface check() receives $dom and $xpath.
        // We can reconstruct text from DOM or request HTML in arguments.
        // The interface check() signature is fixed in `SeoScannerService::runRules` call:
        // $issues = $rule->check($page, $dom, $xpath);
        // Wait, the previous `SeoScannerService.php` had:
        // $issues = $rule->check($page, $dom, $xpath);
        // But the `SeoRule` interface I read only has check($page, $dom, $xpath).
        // I need to use DOM to get text content.
        
        $body = $dom->getElementsByTagName('body')->item(0);
        $html = $body ? $dom->saveHTML($body) : '';

        $analysis = $this->service->analyze($html);
        
        // Save to DB
        $page->keyword_density = $analysis['keywords'] ?? [];
        $page->saveQuietly(); // Avoid triggering events if any

        $issues = [];
        // Check density for top keywords
        foreach ($analysis['keywords'] ?? [] as $word => $data) {
            if ($data['density'] > 3) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => "Keyword '{$word}' appears too frequently ({$data['density']}%)",
                    'context' => ['word' => $word, 'density' => $data['density']],
                ];
            }
            // Low density check might be noisy for generic words
        }

        return $issues;
    }
}
