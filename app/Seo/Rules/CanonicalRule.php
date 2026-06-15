<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class CanonicalRule implements SeoRule
{
    public function key(): string { return 'canonical_tag'; }
    public function title(): string { return 'Canonical Tag'; }
    public function category(): string { return 'technical'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $canonicals = $xpath->query('//link[@rel="canonical"]');

        if ($canonicals->length === 0) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'warning',
                'message'  => 'Missing canonical tag. Add <link rel="canonical"> to prevent duplicate content issues.',
                'selector' => 'head',
                'context'  => ['url' => $page->url],
            ];
        } else {
            $href = $canonicals->item(0)->getAttribute('href');
            $pageUrl = rtrim($page->url, '/');
            $canonicalUrl = rtrim($href, '/');

            if ($canonicalUrl && $canonicalUrl !== $pageUrl) {
                $issues[] = [
                    'rule'     => $this->key(),
                    'severity' => 'info',
                    'message'  => "Canonical tag points to a different URL: {$canonicalUrl}",
                    'selector' => 'link[rel=canonical]',
                    'context'  => ['canonical' => $canonicalUrl, 'page' => $pageUrl],
                ];
            }
        }

        return $issues;
    }
}
