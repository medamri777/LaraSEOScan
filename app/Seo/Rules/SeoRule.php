<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;

interface SeoRule
{
    public function key(): string;
    public function title(): string;
    public function category(): string;

    /**
     * Run the check.
     * Returns array of issues (possibly empty). Each issue:
     * [
     *   'rule' => $this->key(),
     *   'severity' => 'info'|'warning'|'error',
     *   'message' => 'Human readable',
     *   'selector' => 'CSS or xpath if any',
     *   'context' => [...],
     * ]
     */
    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array;
}
