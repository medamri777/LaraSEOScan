<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;

class H1Rule implements SeoRule
{
    public function key(): string { return 'content.h1_count'; }
    public function title(): string { return 'H1 presence and count'; }
    public function category(): string { return 'content'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        $h1s = $xpath->query('//h1');
        $count = $h1s->length;

        if ($count === 0) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'error',
                'message' => 'No H1 found on page. Each page should have exactly one H1.',
                'selector' => 'h1',
                'context' => [],
            ];
        } elseif ($count > 1) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'warning',
                'message' => "{$count} H1 elements found. Recommended: exactly 1 H1 per page.",
                'selector' => 'h1',
                'context' => ['count' => $count],
            ];
        }

        $headingNodes = $xpath->query('//h1|//h2|//h3');
        $prev = null;
        foreach ($headingNodes as $node) {
            $tag = strtolower($node->nodeName);
            if ($tag === 'h3' && $prev === null) {
                $issues[] = [
                    'rule' => 'content.heading_hierarchy',
                    'severity' => 'info',
                    'message' => 'h3 appears without a preceding h2; check heading hierarchy.',
                    'selector' => 'h3',
                    'context' => [],
                ];
                break;
            }
            $prev = $tag;
        }

        return $issues;
    }
}
