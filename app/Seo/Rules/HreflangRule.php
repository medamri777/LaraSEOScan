<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class HreflangRule implements SeoRule
{
    public function key(): string { return 'hreflang_tags'; }
    public function title(): string { return 'Hreflang Tags'; }
    public function category(): string { return 'technical'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $hreflangs = $xpath->query('//link[@hreflang]');

        if ($hreflangs->length === 0) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'info',
                'message'  => 'No hreflang tags found. If your site has multiple languages (French/Arabic/Darija), add hreflang tags to help Google serve the right version.',
                'selector' => 'head',
                'context'  => ['url' => $page->url],
            ];
        } else {
            // Check for x-default
            $hasDefault = false;
            foreach ($hreflangs as $node) {
                if ($node->getAttribute('hreflang') === 'x-default') {
                    $hasDefault = true;
                    break;
                }
            }

            if (!$hasDefault) {
                $issues[] = [
                    'rule'     => $this->key(),
                    'severity' => 'info',
                    'message'  => 'Hreflang tags found but missing x-default. Consider adding x-default for users without a language preference.',
                    'selector' => 'link[hreflang]',
                    'context'  => ['url' => $page->url, 'count' => $hreflangs->length],
                ];
            }
        }

        return $issues;
    }
}
