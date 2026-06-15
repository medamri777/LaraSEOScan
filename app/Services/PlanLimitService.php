<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\ToolUsageLog;
use App\Support\PlanLimits;

class PlanLimitService
{
    /**
     * Tools available per plan (hardcoded fallback).
     * 'all' means every tool is accessible.
     */
    const PLAN_TOOLS_FALLBACK = [
        'free'     => ['seo_analyzer', 'serp_simulator', 'schema_generator', 'robots_generator'],
        'pro'      => ['seo_analyzer', 'serp_simulator', 'schema_generator', 'robots_generator', 'sitemap_crawler', 'crawl_audit'],
        'guru'     => ['all'],
        'business' => ['all'],
        'agency'   => ['all'],
    ];

    /**
     * Get allowed tools for a plan — reads from DB features field first.
     */
    public static function planTools(string $plan): array
    {
        try {
            $planModel = \App\Models\Plan::where('slug', $plan)->where('is_active', true)->first();
            if ($planModel && is_array($planModel->features) && count($planModel->features) > 0) {
                return $planModel->features;
            }
        } catch (\Throwable) {
            // Table may not exist yet
        }

        return self::PLAN_TOOLS_FALLBACK[$plan] ?? self::PLAN_TOOLS_FALLBACK['free'];
    }

    /**
     * Get the numeric limit for a feature based on the tenant's plan.
     * Returns 0 when the feature is disabled, null when unlimited.
     */
    public function getLimit(?Tenant $tenant, string $feature): ?int
    {
        $plan = $tenant?->plan ?? 'free';
        return PlanLimits::forPlan($plan)[$feature] ?? 0;
    }

    /**
     * Check if the tenant's plan allows access to a specific tool.
     * Returns true when tenant is null (graceful degradation = free plan).
     */
    public function canAccessTool(?Tenant $tenant, string $tool): bool
    {
        $plan = $tenant?->plan ?? 'free';
        $allowed = self::planTools($plan);

        if (in_array('all', $allowed)) {
            return true;
        }

        return in_array($tool, $allowed);
    }

    /**
     * Check whether the tenant can create a new project.
     */
    public function canAddProject(?Tenant $tenant): bool
    {
        $limit = $this->getLimit($tenant, 'projects');
        if ($limit === null) return true; // unlimited

        $current = $tenant?->projects()->count() ?? 0;
        return $current < $limit;
    }

    /**
     * Check whether the tenant can add more keywords.
     */
    public function canAddKeyword(?Tenant $tenant, int $requestedAmount = 1): bool
    {
        $limit = $this->getLimit($tenant, 'keywords');
        if ($limit === null) return true;
        if ($limit === 0) return false; // plan does not include keywords

        $current = $tenant?->projects()
            ->withCount('keywords')
            ->get()
            ->sum('keywords_count') ?? 0;

        return ($current + $requestedAmount) <= $limit;
    }

    /**
     * Check whether the tenant can add a competitor to a project.
     */
    public function canAddCompetitor(?Tenant $tenant, int $projectId): bool
    {
        $limit = $this->getLimit($tenant, 'competitors');
        if ($limit === null) return true;
        if ($limit === 0) return false;

        $project = $tenant?->projects()->find($projectId);
        if (!$project) return false;

        $current = $project->competitors()->count();
        return $current < $limit;
    }

    /**
     * Check whether the tenant can run a scan today.
     */
    public function canPerformScan(?Tenant $tenant): bool
    {
        $limit = $this->getLimit($tenant, 'scans_per_day');
        if ($limit === null) return true;
        if ($limit === 0) return false;

        $todayScans = ToolUsageLog::getUsageToday($tenant?->id, 'seo_scan');

        return $todayScans < $limit;
    }

    /**
     * Return the remaining daily scans count for the tenant.
     */
    public function remainingScans(?Tenant $tenant): ?int
    {
        $limit = $this->getLimit($tenant, 'scans_per_day');
        if ($limit === null) return null; // unlimited

        $used = ToolUsageLog::getUsageToday($tenant?->id, 'seo_scan');

        return max(0, $limit - $used);
    }

    /**
     * Build a summary array of limits + current usage for display.
     */
    public function usageSummary(?Tenant $tenant): array
    {
        $plan = $tenant?->plan ?? 'free';
        $limits = PlanLimits::forPlan($plan);

        $projectsUsed = $tenant?->projects()->count() ?? 0;

        $keywordsUsed = $tenant?->projects()
            ->withCount('keywords')
            ->get()
            ->sum('keywords_count') ?? 0;

        $todayScans = ToolUsageLog::getUsageToday($tenant?->id, 'seo_scan');

        return [
            'plan'       => $plan,
            'plan_label' => PlanLimits::labelFor($plan),
            'projects'   => ['used' => $projectsUsed,  'limit' => $limits['projects']],
            'keywords'   => ['used' => $keywordsUsed,  'limit' => $limits['keywords']],
            'scans'      => ['used' => $todayScans,    'limit' => $limits['scans_per_day']],
            'ai_credits' => ['used' => 0,               'limit' => $limits['ai_credits']],
        ];
    }

    // ── Per-Tool Daily Usage ──────────────────────────────────────────────────────

    /**
     * Check if the tenant can use a tool today, and record the usage if yes.
     *
     * Returns an array:
     *   ['allowed' => true]  — usage recorded
     *   ['allowed' => false, 'limit' => int, 'used' => int, 'plan' => string]
     */
    public function checkAndRecordDailyUsage(?Tenant $tenant, ?int $userId, string $toolSlug): array
    {
        $plan = $tenant?->plan ?? 'free';
        $limit = PlanLimits::toolDailyLimit($plan, $toolSlug);

        // null = unlimited
        if ($limit === null) {
            ToolUsageLog::logUsage($tenant?->id, $userId, $toolSlug);
            return ['allowed' => true];
        }

        // 0 = disabled for this plan
        if ($limit === 0) {
            return [
                'allowed' => false,
                'limit'   => 0,
                'used'    => 0,
                'plan'    => $plan,
                'message' => "This tool is not available on the " . ucfirst($plan) . " plan. Please upgrade.",
            ];
        }

        $usedToday = ToolUsageLog::getUsageToday($tenant?->id, $toolSlug);

        if ($usedToday >= $limit) {
            return [
                'allowed' => false,
                'limit'   => $limit,
                'used'    => $usedToday,
                'plan'    => $plan,
                'message' => "You've used all {$limit} daily " . str_replace('_', ' ', $toolSlug) . " actions on the " . ucfirst($plan) . " plan.",
            ];
        }

        // Record usage
        ToolUsageLog::logUsage($tenant?->id, $userId, $toolSlug);

        return ['allowed' => true];
    }

    /**
     * Check if the tenant can use a tool today (without recording).
     */
    public function canUseToolToday(?Tenant $tenant, string $toolSlug): bool
    {
        $plan = $tenant?->plan ?? 'free';
        $limit = PlanLimits::toolDailyLimit($plan, $toolSlug);

        if ($limit === null) return true;
        if ($limit === 0) return false;

        $usedToday = ToolUsageLog::getUsageToday($tenant?->id, $toolSlug);
        return $usedToday < $limit;
    }

    /**
     * Get remaining daily uses for a tool.
     * Returns null for unlimited.
     */
    public function remainingDailyUses(?Tenant $tenant, string $toolSlug): ?int
    {
        $plan = $tenant?->plan ?? 'free';
        $limit = PlanLimits::toolDailyLimit($plan, $toolSlug);

        if ($limit === null) return null;
        if ($limit === 0) return 0;

        $usedToday = ToolUsageLog::getUsageToday($tenant?->id, $toolSlug);
        return max(0, $limit - $usedToday);
    }

    /**
     * Get a full usage summary for all tools with daily limits.
     * Returns an array of ['tool_slug' => ['used' => int, 'limit' => int|null]].
     */
    public function getToolUsageSummary(?Tenant $tenant): array
    {
        $plan = $tenant?->plan ?? 'free';
        $limits = PlanLimits::allToolDailyLimits($plan);
        $allUsage = ToolUsageLog::getAllUsageToday($tenant?->id);

        $summary = [];
        foreach ($limits as $toolSlug => $limit) {
            $summary[$toolSlug] = [
                'used'  => $allUsage[$toolSlug] ?? 0,
                'limit' => $limit,
            ];
        }

        return $summary;
    }
}
