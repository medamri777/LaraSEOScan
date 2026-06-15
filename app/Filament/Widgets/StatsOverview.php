<?php

namespace App\Filament\Widgets;

use App\Models\Keyword;
use App\Models\Plan;
use App\Models\Project;
use App\Models\SeoScan;
use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tenants  = Tenant::count();
        $users    = User::count();
        $scans    = SeoScan::count();
        $keywords = Keyword::count();
        $projects = Project::count();

        $newTenantsThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $scansToday = SeoScan::whereDate('created_at', today())->count();

        // Paid subscriptions (any plan that is not 'free')
        $paidSubscriptions = Tenant::whereNotNull('plan')
            ->where('plan', '!=', 'free')
            ->count();

        // Estimated monthly revenue from DB plans
        $estimatedRevenue = $this->calculateEstimatedRevenue();

        return [
            Stat::make('Workspaces', $tenants)
                ->description("+{$newTenantsThisMonth} this month")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Users', $users)
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Projects', $projects)
                ->description('Across all workspaces')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make('SEO Scans', $scans)
                ->description("{$scansToday} today")
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('warning'),

            Stat::make('Keywords Tracked', $keywords)
                ->description('Across all workspaces')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),

            Stat::make('Paid Subscriptions', $paidSubscriptions)
                ->description('Active paid plans')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Est. Monthly Revenue', $estimatedRevenue)
                ->description('Based on active subscriptions')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }

    /**
     * Calculate estimated monthly revenue from active paid subscriptions.
     */
    private function calculateEstimatedRevenue(): string
    {
        try {
            $paidTenants = Tenant::whereNotNull('plan')
                ->where('plan', '!=', 'free')
                ->get(['plan'])
                ->groupBy('plan')
                ->map->count();

            $revenue = 0;
            foreach ($paidTenants as $planSlug => $count) {
                $plan = Plan::where('slug', $planSlug)->first();
                if ($plan && $plan->price_monthly) {
                    $revenue += (float) $plan->price_monthly * $count;
                }
            }

            return '$' . number_format($revenue, 2);
        } catch (\Throwable) {
            return '---';
        }
    }
}
