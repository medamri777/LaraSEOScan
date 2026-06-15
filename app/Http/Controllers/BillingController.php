<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SeoScan;
use App\Services\PlanLimitService;
use App\Support\PlanLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class BillingController extends Controller
{
    public function __construct(private PlanLimitService $planService) {}

    /**
     * Show the My Subscription / Billing overview page.
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $tenant = $user->tenant;

        $plan     = $tenant?->plan ?? 'free';
        $limits   = PlanLimits::forPlan($plan);
        $label    = PlanLimits::labelFor($plan);

        // Usage stats
        $projectsUsed = $tenant ? Project::where('tenant_id', $tenant->id)->count() : 0;

        $keywordsUsed = $tenant
            ? Project::where('tenant_id', $tenant->id)
                ->withCount('keywords')
                ->get()
                ->sum('keywords_count')
            : 0;

        $todayScans = \App\Models\ToolUsageLog::getUsageToday($tenant?->id, 'seo_scan');

        $totalScans = SeoScan::where('user_id', $user->id)->count();

        // PayPal subscription info
        $subscriptionStatus = $tenant?->paypal_subscription_status;
        $subscriptionId     = $tenant?->paypal_subscription_id;
        $billingCycle       = $tenant?->billing_cycle;
        $trialEndsAt        = $tenant?->trial_ends_at;
        $isOnTrial          = $tenant?->onTrial() ?? false;
        $hasActiveSub       = $tenant?->hasActiveSubscription() ?? false;

        // Build usage array for easy display
        $usage = [
            [
                'label'   => 'Projects',
                'icon'    => 'bi-folder2',
                'used'    => $projectsUsed,
                'limit'   => $limits['projects'],
                'color'   => '#53FC18',
            ],
            [
                'label'   => 'Keywords Tracked',
                'icon'    => 'bi-key',
                'used'    => $keywordsUsed,
                'limit'   => $limits['keywords'],
                'color'   => '#14b8a6',
            ],
            [
                'label'   => 'Scans Today',
                'icon'    => 'bi-search-heart',
                'used'    => $todayScans,
                'limit'   => $limits['scans_per_day'],
                'color'   => '#f59e0b',
            ],
            [
                'label'   => 'AI Credits / Month',
                'icon'    => 'bi-cpu',
                'used'    => 0,
                'limit'   => $limits['ai_credits'],
                'color'   => '#8b5cf6',
            ],
        ];

        // Per-tool daily usage summary
        $toolUsage = $this->planService->getToolUsageSummary($tenant);
        $toolLabels = [
            'seo_analyzer'      => 'SEO Analyzer',
            'crawl_audit'       => 'Crawl Audit',
            'sitemap_crawler'   => 'Sitemap Crawler',
            'keyword_research'  => 'Keyword Research',
            'schema_generator'  => 'Schema Generator',
            'authority_checker' => 'Authority Checker',
            'backlink_checker'  => 'Backlink Checker',
            'organic_research'  => 'Organic Research',
            'keyword_magic'     => 'Keyword Magic',
            'serp_simulator'    => 'SERP Simulator',
            'seo_scan'          => 'SEO Scan',
        ];
        $toolIcons = [
            'seo_analyzer'      => 'bi-bar-chart',
            'crawl_audit'       => 'bi-bug',
            'sitemap_crawler'   => 'bi-diagram-3',
            'keyword_research'  => 'bi-key',
            'schema_generator'  => 'bi-code-slash',
            'authority_checker' => 'bi-shield-check',
            'backlink_checker'  => 'bi-link-45deg',
            'organic_research'  => 'bi-graph-up',
            'keyword_magic'     => 'bi-magic',
            'serp_simulator'    => 'bi-google',
            'seo_scan'          => 'bi-search',
        ];

        // Upgrade plans to display
        $upgradePlans = [
            'pro' => [
                'label'    => 'Pro',
                'price'    => '119',
                'color'    => '#53FC18',
                'features' => ['1 Project', '10 Scans/day', '1,000 AI Credits', 'SEO Audit Tool', 'Crawl Audit', 'Schema Generator'],
            ],
            'guru' => [
                'label'    => 'Guru',
                'price'    => '229',
                'color'    => '#f59e0b',
                'features' => ['15 Projects', '1,500 Keywords', '150 Pages Crawled', '30 Scans/day', '5 Competitors/project', '3,000 AI Credits'],
            ],
            'business' => [
                'label'    => 'Business',
                'price'    => '449',
                'color'    => '#ef4444',
                'features' => ['100 Projects', '5,000 Keywords', '1M Pages Crawled', '100 Scans/day', '10 Competitors/project', '10,000 AI Credits'],
            ],
        ];

        return view('billing', compact(
            'tenant', 'plan', 'label', 'limits', 'usage', 'toolUsage', 'toolLabels', 'toolIcons',
            'subscriptionStatus', 'subscriptionId', 'billingCycle',
            'trialEndsAt', 'isOnTrial', 'hasActiveSub',
            'projectsUsed', 'keywordsUsed', 'todayScans', 'totalScans',
            'upgradePlans'
        ));
    }
}
