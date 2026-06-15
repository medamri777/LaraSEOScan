<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class InternalLinkingRule implements SeoRule
{
    protected int $minInternalLinks = 3;

    public function key(): string { return 'internal_linking'; }
    public function title(): string { return 'Internal Linking'; }
    public function category(): string { return 'content'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        $pageHost = parse_url($page->url, PHP_URL_HOST);

        $links = $xpath->query('//a[@href]');
        $internalLinks = 0;

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Skip anchors, javascript, mailto
            if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:')) {
                continue;
            }

            // Relative links are internal
            if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
                $internalLinks++;
                continue;
            }

            // Check if same host
            $linkHost = parse_url($href, PHP_URL_HOST);
            if ($linkHost && $linkHost === $pageHost) {
                $internalLinks++;
            }
        }

        if ($internalLinks === 0) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'error',
                'message'  => 'No internal links found. Internal linking helps Google discover and index your other pages.',
                'selector' => 'body',
                'context'  => ['internal_links' => $internalLinks],
            ];
        } elseif ($internalLinks < $this->minInternalLinks) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'warning',
                'message'  => "Only {$internalLinks} internal link(s) found. Aim for at least {$this->minInternalLinks} per page.",
                'selector' => 'body',
                'context'  => ['internal_links' => $internalLinks, 'min_recommended' => $this->minInternalLinks],
            ];
        }

        return $issues;
    }
}
