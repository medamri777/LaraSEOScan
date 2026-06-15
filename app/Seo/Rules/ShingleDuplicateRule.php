<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;
use Illuminate\Support\Str;

class ShingleDuplicateRule implements SeoRule
{
    public function key(): string { return 'content.shingle_duplicates'; }
    public function title(): string { return 'Duplicate content via shingle overlap'; }
    public function category(): string { return 'content'; }

    protected $k = 5;
    protected $overlapThreshold = 0.75;

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $bodyNode = $xpath->query('//body')->item(0);
        if (!$bodyNode) return $issues;
        $bodyText = trim(strip_tags($dom->saveHTML($bodyNode)));
        $words = preg_split('/\s+/', preg_replace('/[^\\p{L}\\p{N}\\s]+/u', '', mb_strtolower($bodyText)));
        $n = count($words);
        if ($n < $this->k) {
            $page->word_count = $n;
            $page->save();
            return $issues;
        }

        $shingles = [];
        for ($i = 0; $i <= $n - $this->k; $i++) {
            $shingle = array_slice($words, $i, $this->k);
            $shingles[] = md5(implode(' ', $shingle));
        }

        $sig = implode(',', array_slice($shingles, 0, 50));
        $page->shingle_signature = $sig;
        $page->word_count = $n;
        $page->save();

        $candidates = SeoPage::where('id', '!=', $page->id)
            ->whereNotNull('shingle_signature')
            ->limit(50)
            ->get();

        foreach ($candidates as $cand) {
            $candShingles = explode(',', $cand->shingle_signature);
            if (count($candShingles) === 0) continue;
            $common = count(array_intersect($shingles, $candShingles));
            $overlap = $common / max(1, min(count($shingles), count($candShingles)));
            if ($overlap >= $this->overlapThreshold) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => "High content overlap with another page (approx {$overlap}).",
                    'selector' => 'body',
                    'context' => ['other_page_id' => $cand->id, 'overlap' => $overlap],
                ];
            }
        }

        return $issues;
    }
}
