<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;

class MissingTitleRule implements SeoRule
{
    public function key(): string { return 'meta.title'; }
    public function title(): string { return 'Title tag presence and length'; }
    public function category(): string { return 'meta'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $nodes = $xpath->query('//head/title');
        $title = null;
        if ($nodes->length) {
            $title = trim($nodes->item(0)->textContent);
        }

        if (!$title) {
            $issues[] = [
                'rule' => $this->key(),
                'severity' => 'error',
                'message' => 'Title tag is missing or empty.',
                'selector' => 'head > title',
                'context' => [],
            ];
        } else {
            $len = mb_strlen($title);
            if ($len < 30) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => "Title is too short ({$len} chars). Recommended 30–60 characters.",
                    'selector' => 'head > title',
                    'context' => ['length' => $len, 'title' => $title],
                ];
            } elseif ($len > 60) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => "Title is too long ({$len} chars). Recommended 30–60 characters.",
                    'selector' => 'head > title',
                    'context' => ['length' => $len, 'title' => $title],
                ];
            }
        }

        $page->title = $title;
        $page->save();

        return $issues;
    }
}
