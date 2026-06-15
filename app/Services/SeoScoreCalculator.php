<?php

namespace App\Services;

use App\Models\SeoScan;
use App\Models\SeoIssue;

/**
 * Seo4ma Score Calculator
 *
 * Calculates a deterministic SEO score from 0-100 based on
 * issues found during a scan. Score is broken down into 5 categories:
 *
 * - Technical (30 pts): HTTPS, broken links, canonical, schema, duplicates, hreflang
 * - On-Page  (30 pts): Title, meta, H1, keyword density, images, OG, content length, internal links
 * - Local    (20 pts): Placeholder baseline (no GMB API yet)
 * - Mobile   (10 pts): Viewport, responsive
 * - Speed    (10 pts): Image optimization (no PageSpeed API yet)
 */
class SeoScoreCalculator
{
    /** Category weights (max points) */
    protected array $weights = [
        'technical' => 30,
        'on_page'   => 30,
        'local'     => 20,
        'mobile'    => 10,
        'speed'     => 10,
    ];

    /** Map rule_key values to their scoring categories */
    protected array $ruleCategories = [
        // Technical
        'https_check'         => 'technical',
        'canonical_tag'       => 'technical',
        'json_ld'             => 'technical',
        'broken_link'         => 'technical',
        'shingle_duplicate'   => 'technical',
        'sitemap.missing_page'=> 'technical',
        'technical.redirect_chain' => 'technical',
        'technical.robots_validation' => 'technical',

        // On-Page
        'missing_title'       => 'on_page',
        'meta_description'    => 'on_page',
        'h1_missing'          => 'on_page',
        'h1_multiple'         => 'on_page',
        'keyword_density'     => 'on_page',
        'image_optimization'  => 'on_page',
        'open_graph'          => 'on_page',
        'content_length'      => 'on_page',
        'internal_linking'    => 'on_page',

        // Mobile
        'mobile_viewport'     => 'mobile',
    ];

    /** Severity penalty points */
    protected array $severityPenalty = [
        'error'   => 5,
        'warning' => 2,
        'info'    => 1,
    ];

    /**
     * Calculate the SEO score for a completed scan.
     */
    public function calculate(SeoScan $scan): array
    {
        // Start with full marks per category
        $scores = $this->weights;

        $penalties = array_fill_keys(array_keys($this->weights), 0);

        // Load all issues for this scan
        $issues = SeoIssue::whereHas('page', function ($q) use ($scan) {
            $q->where('seo_scan_id', $scan->id);
        })->get();

        // Calculate penalties from issues
        foreach ($issues as $issue) {
            $ruleKey = $issue->rule_key;
            $severity = strtolower($issue->severity ?? 'info');
            $penalty = $this->severityPenalty[$severity] ?? 1;

            $category = $this->ruleCategories[$ruleKey] ?? null;
            if ($category) {
                $penalties[$category] += $penalty;
            }

            // ImageOptimization also penalizes speed
            if ($ruleKey === 'image_optimization') {
                $penalties['speed'] += $penalty;
            }
        }

        // Apply penalties (floor at 0)
        foreach ($scores as $category => &$score) {
            $score = max(0, $this->weights[$category] - $penalties[$category]);
        }

        // Local SEO: give neutral baseline since we don't have GMB API
        if ($penalties['local'] === 0) {
            $scores['local'] = 12; // 12/20 baseline
        }

        // Speed: use real PageSpeed Performance score if available (scaled 0-100 to 0-10)
        if (is_numeric($scan->pagespeed_performance)) {
            $scores['speed'] = (int) round($scan->pagespeed_performance / 10);
        } else {
            // Speed: without PageSpeed API, base on image issues only
            if ($penalties['speed'] === 0) {
                $scores['speed'] = 7; // 7/10 baseline
            }
        }

        $total = array_sum($scores);

        $grade = match (true) {
            $total >= 90 => 'A+',
            $total >= 80 => 'A',
            $total >= 70 => 'B',
            $total >= 60 => 'C',
            $total >= 50 => 'D',
            default      => 'F',
        };

        return [
            'total'     => (int) $total,
            'technical' => (int) $scores['technical'],
            'on_page'   => (int) $scores['on_page'],
            'local'     => (int) $scores['local'],
            'mobile'    => (int) $scores['mobile'],
            'speed'     => (int) $scores['speed'],
            'grade'     => $grade,
            'breakdown' => [
                'total_issues' => $issues->count(),
                'errors'       => $issues->where('severity', 'error')->count(),
                'warnings'     => $issues->where('severity', 'warning')->count(),
                'info'         => $issues->where('severity', 'info')->count(),
                'penalties'    => $penalties,
            ],
        ];
    }

    /**
     * Calculate and persist the score on the scan model.
     */
    public function calculateAndSave(SeoScan $scan): array
    {
        $result = $this->calculate($scan);

        $scan->update([
            'score_total'     => $result['total'],
            'score_technical' => $result['technical'],
            'score_on_page'   => $result['on_page'],
            'score_local'     => $result['local'],
            'score_mobile'    => $result['mobile'],
            'score_speed'     => $result['speed'],
        ]);

        return $result;
    }
}
