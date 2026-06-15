<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized plan limit definitions.
 * Reads from DB first (plans table), falls back to hardcoded constants.
 * null means unlimited.
 */
class PlanLimits
{
    /**
     * Hardcoded fallback limits — used when the plans table is empty.
     */
    private const LIMITS = [
        'free'     => [
            'projects'      => 1,
            'keywords'      => 10,
            'competitors'   => 1,
            'scans_per_day' => 2,
            'crawl_pages'   => 0,
            'ai_credits'    => 10,
        ],
        'pro'      => [
            'projects'      => 1,
            'keywords'      => 0,
            'competitors'   => 0,
            'scans_per_day' => 10,
            'crawl_pages'   => 0,
            'ai_credits'    => 1000,
        ],
        'guru'     => [
            'projects'      => 15,
            'keywords'      => 1500,
            'competitors'   => 5,
            'scans_per_day' => 30,
            'crawl_pages'   => 150,
            'ai_credits'    => 3000,
        ],
        'business' => [
            'projects'      => 100,
            'keywords'      => 5000,
            'competitors'   => 10,
            'scans_per_day' => 100,
            'crawl_pages'   => 1000000,
            'ai_credits'    => 10000,
        ],
        'agency'   => [
            'projects'      => null,
            'keywords'      => null,
            'competitors'   => null,
            'scans_per_day' => null,
            'crawl_pages'   => null,
            'ai_credits'    => null,
        ],
    ];

    /**
     * Per-tool daily usage limits (hardcoded fallback).
     * null = unlimited, 0 = disabled for that plan.
     */
    private const TOOL_DAILY_LIMITS = [
        'free'     => [
            'seo_analyzer'      => 2,
            'crawl_audit'       => 0,
            'sitemap_crawler'   => 0,
            'keyword_research'  => 2,
            'schema_generator'  => 1,
            'authority_checker' => 2,
            'backlink_checker'  => 1,
            'organic_research'  => 0,
            'keyword_magic'     => 0,
            'serp_simulator'    => 3,
            'seo_scan'          => 2,
        ],
        'pro'      => [
            'seo_analyzer'      => 10,
            'crawl_audit'       => 3,
            'sitemap_crawler'   => 3,
            'keyword_research'  => 10,
            'schema_generator'  => 5,
            'authority_checker' => 10,
            'backlink_checker'  => 5,
            'organic_research'  => 5,
            'keyword_magic'     => 3,
            'serp_simulator'    => 10,
            'seo_scan'          => 10,
        ],
        'guru'     => [
            'seo_analyzer'      => 30,
            'crawl_audit'       => 10,
            'sitemap_crawler'   => 10,
            'keyword_research'  => 30,
            'schema_generator'  => 15,
            'authority_checker' => 30,
            'backlink_checker'  => 15,
            'organic_research'  => 15,
            'keyword_magic'     => 10,
            'serp_simulator'    => 30,
            'seo_scan'          => 30,
        ],
        'business' => [
            'seo_analyzer'      => 100,
            'crawl_audit'       => 50,
            'sitemap_crawler'   => 50,
            'keyword_research'  => 100,
            'schema_generator'  => 50,
            'authority_checker' => 100,
            'backlink_checker'  => 50,
            'organic_research'  => 50,
            'keyword_magic'     => 30,
            'serp_simulator'    => 100,
            'seo_scan'          => 100,
        ],
        'agency'   => [
            'seo_analyzer'      => null,
            'crawl_audit'       => null,
            'sitemap_crawler'   => null,
            'keyword_research'  => null,
            'schema_generator'  => null,
            'authority_checker' => null,
            'backlink_checker'  => null,
            'organic_research'  => null,
            'keyword_magic'     => null,
            'serp_simulator'    => null,
            'seo_scan'          => null,
        ],
    ];

    /**
     * Human-readable plan names.
     */
    public const PLAN_LABELS = [
        'free'     => 'Free',
        'pro'      => 'Pro',
        'guru'     => 'Guru',
        'business' => 'Business',
        'agency'   => 'Agency',
    ];

    /**
     * Plan badge colours (Tailwind-style class segments used in Blade).
     */
    public const PLAN_COLORS = [
        'free'     => 'secondary',
        'pro'      => 'primary',
        'guru'     => 'warning',
        'business' => 'danger',
        'agency'   => 'info',
    ];

    /**
     * Get limits for a plan. Checks DB first, falls back to hardcoded.
     */
    public static function forPlan(string $plan): array
    {
        $dbLimits = static::fromDatabase($plan);

        return $dbLimits ?? (self::LIMITS[$plan] ?? self::LIMITS['free']);
    }

    /**
     * Try to load limits from the plans table (cached for 1 hour).
     * Returns null if the plan doesn't exist in DB.
     */
    public static function fromDatabase(string $plan): ?array
    {
        try {
            $planModel = Cache::remember(
                "plan_limits_{$plan}",
                3600,
                fn () => Plan::where('slug', $plan)->where('is_active', true)->first()
            );

            if (! $planModel) {
                return null;
            }

            return $planModel->toLimitsArray();
        } catch (\Throwable) {
            // Table may not exist yet during migrations
            return null;
        }
    }

    /**
     * Clear the cached limits for a plan (call after updating a plan in admin).
     */
    public static function clearCache(?string $plan = null): void
    {
        if ($plan) {
            Cache::forget("plan_limits_{$plan}");
        } else {
            // Clear all plan caches
            foreach (array_keys(self::LIMITS) as $slug) {
                Cache::forget("plan_limits_{$slug}");
            }
        }
    }

    public static function projectLimit(string $plan): ?int
    {
        return self::forPlan($plan)['projects'];
    }

    public static function keywordLimit(string $plan): ?int
    {
        return self::forPlan($plan)['keywords'];
    }

    public static function scanLimitPerDay(string $plan): ?int
    {
        return self::forPlan($plan)['scans_per_day'];
    }

    public static function competitorLimit(string $plan): ?int
    {
        return self::forPlan($plan)['competitors'];
    }

    public static function crawlPagesLimit(string $plan): ?int
    {
        return self::forPlan($plan)['crawl_pages'];
    }

    public static function aiCreditsLimit(string $plan): ?int
    {
        return self::forPlan($plan)['ai_credits'];
    }

    /**
     * Get the daily limit for a specific tool on a given plan.
     * Returns null = unlimited, 0 = disabled, int = specific limit.
     */
    public static function toolDailyLimit(string $plan, string $toolSlug): ?int
    {
        // Try DB first
        $dbLimits = static::toolDailyLimitsFromDatabase($plan);
        if ($dbLimits !== null && array_key_exists($toolSlug, $dbLimits)) {
            return $dbLimits[$toolSlug];
        }

        // Fallback to hardcoded
        return self::TOOL_DAILY_LIMITS[$plan][$toolSlug]
            ?? self::TOOL_DAILY_LIMITS['free'][$toolSlug]
            ?? 0;
    }

    /**
     * Get all per-tool daily limits for a plan.
     */
    public static function allToolDailyLimits(string $plan): array
    {
        $dbLimits = static::toolDailyLimitsFromDatabase($plan);
        return $dbLimits ?? (self::TOOL_DAILY_LIMITS[$plan] ?? self::TOOL_DAILY_LIMITS['free']);
    }

    /**
     * Try to load per-tool daily limits from the plans table (cached).
     */
    public static function toolDailyLimitsFromDatabase(string $plan): ?array
    {
        try {
            $planModel = Cache::remember(
                "plan_tool_daily_limits_{$plan}",
                3600,
                fn () => Plan::where('slug', $plan)->where('is_active', true)->first()
            );

            if (! $planModel) {
                return null;
            }

            return $planModel->toToolDailyLimitsArray();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * All tool slugs that have daily limits.
     */
    public static function allDailyLimitToolSlugs(): array
    {
        return array_keys(self::TOOL_DAILY_LIMITS['free']);
    }

    public static function labelFor(string $plan): string
    {
        // Try DB first for custom plan names
        try {
            $planModel = Plan::where('slug', $plan)->first();
            if ($planModel) {
                return $planModel->name;
            }
        } catch (\Throwable) {
            // Table may not exist yet
        }

        return self::PLAN_LABELS[$plan] ?? ucfirst($plan);
    }

    /**
     * Build a standard 402 response payload.
     */
    public static function limitResponse(
        string $limitType,
        int|null $limit,
        int $current,
        string $plan
    ): array {
        $messages = [
            'scans'             => "You've used all {$limit} daily audits on the " . ucfirst($plan) . " plan.",
            'projects'          => "You've reached the {$limit}-project limit on the " . ucfirst($plan) . " plan.",
            'keywords'          => "You've reached the {$limit}-keyword limit on the " . ucfirst($plan) . " plan.",
            'competitors'       => "You've reached the {$limit}-competitor limit on the " . ucfirst($plan) . " plan.",
            'seo_analyzer'      => "You've used all {$limit} daily SEO analyses on the " . ucfirst($plan) . " plan.",
            'crawl_audit'       => "You've used all {$limit} daily crawl audits on the " . ucfirst($plan) . " plan.",
            'sitemap_crawler'   => "You've used all {$limit} daily sitemap crawls on the " . ucfirst($plan) . " plan.",
            'keyword_research'  => "You've used all {$limit} daily keyword researches on the " . ucfirst($plan) . " plan.",
            'schema_generator'  => "You've used all {$limit} daily schema generations on the " . ucfirst($plan) . " plan.",
            'authority_checker' => "You've used all {$limit} daily authority checks on the " . ucfirst($plan) . " plan.",
            'backlink_checker'  => "You've used all {$limit} daily backlink checks on the " . ucfirst($plan) . " plan.",
            'organic_research'  => "You've used all {$limit} daily organic researches on the " . ucfirst($plan) . " plan.",
            'keyword_magic'     => "You've used all {$limit} daily keyword magic runs on the " . ucfirst($plan) . " plan.",
            'serp_simulator'    => "You've used all {$limit} daily SERP simulations on the " . ucfirst($plan) . " plan.",
            'seo_scan'          => "You've used all {$limit} daily SEO scans on the " . ucfirst($plan) . " plan.",
        ];

        return [
            'message'          => $messages[$limitType] ?? 'Plan limit reached.',
            'error'            => 'plan_limit_exceeded',
            'limit_type'       => $limitType,
            'limit'            => $limit,
            'current'          => $current,
            'plan'             => $plan,
            'upgrade_required' => true,
        ];
    }
}
