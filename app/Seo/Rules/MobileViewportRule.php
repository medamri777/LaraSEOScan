<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class MobileViewportRule implements SeoRule
{
    public function key(): string { return 'mobile_viewport'; }
    public function title(): string { return 'Mobile Viewport'; }
    public function category(): string { return 'mobile'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $viewports = $xpath->query('//meta[@name="viewport"]');

        if ($viewports->length === 0) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'error',
                'message'  => 'Missing viewport meta tag. Mobile-friendliness is a Google ranking factor. Add: <meta name="viewport" content="width=device-width, initial-scale=1">',
                'selector' => 'head',
                'context'  => ['url' => $page->url],
            ];
        } else {
            $content = $viewports->item(0)->getAttribute('content');
            if (!str_contains($content, 'width=device-width')) {
                $issues[] = [
                    'rule'     => $this->key(),
                    'severity' => 'warning',
                    'message'  => 'Viewport meta tag is present but does not include "width=device-width".',
                    'selector' => 'meta[name=viewport]',
                    'context'  => ['viewport' => $content],
                ];
            }
        }

        return $issues;
    }
}
