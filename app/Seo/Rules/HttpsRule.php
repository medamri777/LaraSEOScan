<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class HttpsRule implements SeoRule
{
    public function key(): string { return 'https_check'; }
    public function title(): string { return 'HTTPS Security'; }
    public function category(): string { return 'technical'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        if (str_starts_with($page->url, 'http://')) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'error',
                'message'  => 'Page is served over HTTP instead of HTTPS. This is a critical security and SEO issue.',
                'selector' => '',
                'context'  => ['url' => $page->url],
            ];
        }

        return $issues;
    }
}
