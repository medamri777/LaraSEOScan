<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'projects_limit',
        'keywords_limit',
        'competitors_limit',
        'scans_per_day',
        'crawl_pages_limit',
        'ai_credits_limit',
        'seo_analyzer_per_day',
        'crawl_audit_per_day',
        'sitemap_crawler_per_day',
        'keyword_research_per_day',
        'schema_generator_per_day',
        'authority_checker_per_day',
        'backlink_checker_per_day',
        'organic_research_per_day',
        'keyword_magic_per_day',
        'serp_simulator_per_day',
        'features',
        'is_active',
        'sort_order',
        'trial_days',
    ];

    protected $casts = [
        'features'                 => 'array',
        'is_active'                => 'boolean',
        'price_monthly'            => 'decimal:2',
        'price_yearly'             => 'decimal:2',
        'projects_limit'           => 'integer',
        'keywords_limit'           => 'integer',
        'competitors_limit'        => 'integer',
        'scans_per_day'            => 'integer',
        'crawl_pages_limit'        => 'integer',
        'ai_credits_limit'         => 'integer',
        'seo_analyzer_per_day'     => 'integer',
        'crawl_audit_per_day'      => 'integer',
        'sitemap_crawler_per_day'  => 'integer',
        'keyword_research_per_day' => 'integer',
        'schema_generator_per_day' => 'integer',
        'authority_checker_per_day'=> 'integer',
        'backlink_checker_per_day' => 'integer',
        'organic_research_per_day' => 'integer',
        'keyword_magic_per_day'    => 'integer',
        'serp_simulator_per_day'   => 'integer',
        'sort_order'               => 'integer',
        'trial_days'               => 'integer',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Return all limits as an associative array compatible with PlanLimits::forPlan().
     */
    public function toLimitsArray(): array
    {
        return [
            'projects'      => $this->projects_limit,
            'keywords'      => $this->keywords_limit,
            'competitors'   => $this->competitors_limit,
            'scans_per_day' => $this->scans_per_day,
            'crawl_pages'   => $this->crawl_pages_limit,
            'ai_credits'    => $this->ai_credits_limit,
        ];
    }

    /**
     * Return all per-tool daily limits as an associative array.
     */
    public function toToolDailyLimitsArray(): array
    {
        return [
            'seo_analyzer'     => $this->seo_analyzer_per_day,
            'crawl_audit'      => $this->crawl_audit_per_day,
            'sitemap_crawler'  => $this->sitemap_crawler_per_day,
            'keyword_research' => $this->keyword_research_per_day,
            'schema_generator' => $this->schema_generator_per_day,
            'authority_checker'=> $this->authority_checker_per_day,
            'backlink_checker' => $this->backlink_checker_per_day,
            'organic_research' => $this->organic_research_per_day,
            'keyword_magic'    => $this->keyword_magic_per_day,
            'serp_simulator'   => $this->serp_simulator_per_day,
            'seo_scan'         => $this->scans_per_day,
        ];
    }

    /**
     * Human-readable label with price.
     */
    public function getDisplayNameAttribute(): string
    {
        $price = $this->price_monthly
            ? " (${$this->price_monthly}/mo)"
            : ' (Free)';

        return $this->name . $price;
    }

    public function isFree(): bool
    {
        return $this->slug === 'free';
    }

    public function isUnlimited(): bool
    {
        return $this->projects_limit === null
            && $this->keywords_limit === null
            && $this->scans_per_day === null;
    }

    /**
     * All available tool slugs for the features field.
     */
    public static function allToolSlugs(): array
    {
        return [
            'seo_analyzer',
            'serp_simulator',
            'schema_generator',
            'robots_generator',
            'sitemap_crawler',
            'crawl_audit',
            'keyword_tracking',
            'competitor_analysis',
            'ai_keywords',
            'pagespeed',
            'gsc_integration',
        ];
    }
}
