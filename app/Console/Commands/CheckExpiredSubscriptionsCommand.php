<?php

namespace App\Console\Commands;

use App\Models\PlanChangeLog;
use App\Models\Tenant;
use App\Support\PlanLimits;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Fallback cron — catches PayPal subscription expirations when the webhook is missed.
 * Runs hourly and downgrades any paid tenant whose PayPal status is no longer ACTIVE.
 */
class CheckExpiredSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:check-expired';

    protected $description = 'Check for expired PayPal subscriptions and trials, downgrade to free (webhook fallback)';

    public function handle(): int
    {
        $downgraded = 0;

        // 1. Expired trials with no active PayPal subscription
        $expiredTrials = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', Carbon::now())
            ->where('plan', '!=', 'free')
            ->where(function ($q) {
                $q->whereNull('paypal_subscription_status')
                  ->orWhereNotIn('paypal_subscription_status', ['ACTIVE']);
            })
            ->get();

        foreach ($expiredTrials as $tenant) {
            $this->downgradeToFree($tenant, 'Trial expired on ' . $tenant->trial_ends_at->format('Y-m-d'));
            $downgraded++;
        }

        // 2. Paid tenants with a cancelled/suspended/expired PayPal subscription
        $cancelledPayPal = Tenant::where('plan', '!=', 'free')
            ->whereIn('paypal_subscription_status', ['CANCELLED', 'SUSPENDED', 'EXPIRED'])
            ->get();

        foreach ($cancelledPayPal as $tenant) {
            $this->downgradeToFree(
                $tenant,
                'PayPal subscription status: ' . $tenant->paypal_subscription_status
            );
            $downgraded++;
        }

        if ($downgraded > 0) {
            PlanLimits::clearCache();
        }

        $this->info("Checked expired PayPal subscriptions — downgraded {$downgraded} tenant(s) to Free.");

        return self::SUCCESS;
    }

    private function downgradeToFree(Tenant $tenant, string $reason): void
    {
        $oldPlan = $tenant->plan;

        if ($oldPlan === 'free') {
            return;
        }

        $oldLimit = $tenant->scan_limit_per_day;
        $newLimit = PlanLimits::scanLimitPerDay('free');

        $tenant->update([
            'plan'             => 'free',
            'scan_limit_per_day' => $newLimit,
        ]);

        PlanChangeLog::create([
            'tenant_id'      => $tenant->id,
            'admin_id'       => null,
            'old_plan'       => $oldPlan,
            'new_plan'       => 'free',
            'old_scan_limit' => $oldLimit,
            'new_scan_limit' => $newLimit,
            'source'         => 'scheduled_check',
            'note'           => $reason,
        ]);

        Log::info('Subscription check: downgraded tenant to free', [
            'tenant'   => $tenant->name,
            'old_plan' => $oldPlan,
            'reason'   => $reason,
        ]);
    }
}
