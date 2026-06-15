<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;

class MetaDescriptionRule implements SeoRule
{
    public function key(): string { return 'meta.description'; }
    public function title(): string { return 'Meta description presence and length'; }
    public function category(): string { return 'meta'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        $meta = $xpath->query('//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="description"]')->item(0);

        $desc = null;
        if ($meta instanceof \DOMElement) {
            $desc = trim($meta->getAttribute('content'));
        }

        $page->description = $desc;
        $page->save();

        if (!$desc) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'warning',
                'message' => 'Meta description is missing.',
                'selector' => 'meta[name=description]',
                'context' => [],
            ];
            return $issues;
        }

        $len = mb_strlen($desc);
        if ($len < 50) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'warning',
                'message' => "Meta description too short ({$len} chars). Recommended 50–160 characters.",
                'selector' => 'meta[name=description]',
                'context' => ['length' => $len],
            ];
        } elseif ($len > 160) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'warning',
                'message' => "Meta description too long ({$len} chars). Recommended 50–160 characters.",
                'selector' => 'meta[name=description]',
                'context' => ['length' => $len],
            ];
        }

        return $issues;
    }
}
