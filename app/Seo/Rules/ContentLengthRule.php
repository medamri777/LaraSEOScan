<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;

class ContentLengthRule implements SeoRule
{
    protected int $minWords = 300;

    public function key(): string { return 'content_length'; }
    public function title(): string { return 'Content Length'; }
    public function category(): string { return 'content'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        // Get body text content
        $bodies = $xpath->query('//body');
        if ($bodies->length === 0) {
            return $issues;
        }

        $text = $bodies->item(0)->textContent;
        $text = preg_replace('/\s+/', ' ', trim($text));
        $wordCount = str_word_count($text);

        if ($wordCount < 100) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'error',
                'message'  => "Page has very thin content ({$wordCount} words). Google prefers at least 300 words of unique content.",
                'selector' => 'body',
                'context'  => ['word_count' => $wordCount, 'min_recommended' => $this->minWords],
            ];
        } elseif ($wordCount < $this->minWords) {
            $issues[] = [
                'rule'     => $this->key(),
                'severity' => 'warning',
                'message'  => "Page has {$wordCount} words. Consider adding more content — at least {$this->minWords} words recommended.",
                'selector' => 'body',
                'context'  => ['word_count' => $wordCount, 'min_recommended' => $this->minWords],
            ];
        }

        return $issues;
    }
}
