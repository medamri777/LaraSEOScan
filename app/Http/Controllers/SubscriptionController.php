<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Tenant;
use App\Models\PlanChangeLog;
use App\Support\PlanLimits;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Display the pricing page.
     */
    public function pricing()
    {
        return view('pricing');
    }

    /**
     * Show a specific plan detail / checkout page.
     */
    public function planDetail(string $plan, Request $request)
    {
        $plans = [
            'pro' => [
                'monthly' => '119',
                'annual' => '1,142',
                'description' => 'The smart start for freelancers & in-house marketers.',
                'features' => [
                    '1 Project',
                    'No Keywords Tracked',
                    'No Crawling',
                    '500 Scans / Day',
                    '1,000 AI Credits / month',
                    'SEO Audit Tool',
                    'SERP Simulator',
                    'Schema Generator',
                    'Robots.txt Generator',
                ],
            ],
            'guru' => [
                'monthly' => '229',
                'annual' => '2,198',
                'description' => 'Confident growth for agencies & marketing SMBs.',
                'features' => [
                    '15 Projects',
                    '1,500 Keywords Tracked',
                    '150 Pages to Crawl (Fast Process)',
                    '1,500 Scans / Day',
                    '5 Competitors per Project',
                    '3,000 AI Credits / month',
                    'All Pro Features Included',
                    'Keyword Overview & Research',
                    'Competitor Analysis',
                    'Backlink Checker',
                    'Organic Research',
                ],
            ],
            'business' => [
                'monthly' => '449',
                'annual' => '4,310',
                'description' => 'Absolute power for large agencies & enterprises.',
                'features' => [
                    '100 Projects',
                    '5,000 Keywords Tracked',
                    '1,000,000 Pages to Crawl',
                    '5,000 Scans / Day',
                    '10 Competitors per Project',
                    '10,000 AI Credits / month',
                    'All Guru Features Included',
                    'API Access',
                    'Priority Support',
                    'Advanced Fast Processing',
                ],
            ],
        ];

        if (!array_key_exists($plan, $plans)) {
            abort(404);
        }

        return view('plan-detail', [
            'plan' => $plan,
            'planData' => $plans[$plan],
            'trial' => $request->boolean('trial'),
        ]);
    }

    /**
     * Initialize PayPal Subscription Checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:pro,guru,business',
            'cycle' => 'required|in:monthly,annual',
        ]);

        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->back()->with('error', 'You need an active workspace to subscribe.');
        }

        // Map internal plans to PayPal Plan IDs (These should ideally come from config or db)
        $paypalPlanIds = [
            'pro' => [
                'monthly' => config('paypal.plans.pro_monthly'),
                'annual' => config('paypal.plans.pro_annual'),
            ],
            'guru' => [
                'monthly' => config('paypal.plans.guru_monthly'),
                'annual' => config('paypal.plans.guru_annual'),
            ],
            'business' => [
                'monthly' => config('paypal.plans.business_monthly'),
                'annual' => config('paypal.plans.business_annual'),
            ],
        ];

        $paypalPlanId = $paypalPlanIds[$request->plan][$request->cycle] ?? null;

        if (!$paypalPlanId) {
            return redirect()->back()->with('error', 'Invalid plan selected or plan ID not configured.');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $data = [
            'plan_id' => $paypalPlanId,
            'application_context' => [
                'brand_name' => config('app.name'),
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => route('subscription.success', ['plan' => $request->plan, 'cycle' => $request->cycle]),
                'cancel_url' => route('subscription.cancel'),
            ]
        ];

        $response = $provider->createSubscription($data);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        }

        return redirect()->route('pricing')->with('error', 'Something went wrong while initiating the subscription.');
    }

    /**
     * Handle successful subscription setup.
     */
    public function success(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $subscriptionId = $request->get('subscription_id');

        if (!$subscriptionId) {
            return redirect()->route('pricing')->with('error', 'Invalid subscription payload.');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $subscriptionDetails = $provider->showSubscriptionDetails($subscriptionId);

        if (isset($subscriptionDetails['id'])) {
            // Update tenant subscription info
            $tenant->update([
                'plan' => $request->get('plan'),
                'billing_cycle' => $request->get('cycle'),
                'paypal_subscription_id' => $subscriptionDetails['id'],
                'paypal_subscription_status' => $subscriptionDetails['status'],
                // Set trial end if applicable (3 days from now)
                'trial_ends_at' => now()->addDays(3),
            ]);

            return redirect()->route('dashboard')->with('success', 'Subscription activated successfully! Enjoy your 3-day free trial.');
        }

        return redirect()->route('pricing')->with('error', 'Unable to verify subscription.');
    }

    /**
     * Handle cancellation of checkout flow.
     */
    public function cancel()
    {
        return redirect()->route('pricing')->with('info', 'Subscription checkout cancelled.');
    }

    /**
     * Webhook to listen for PayPal subscription events (Cancellations, Renewals, etc.)
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        
        Log::info('PayPal Webhook Received: ' . $eventType);

        if (!$eventType) return response()->json(['status' => 'ignored']);

        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if ($subscriptionId) {
            $tenant = Tenant::where('paypal_subscription_id', $subscriptionId)->first();

            if ($tenant) {
                $oldPlan = $tenant->plan;

                switch ($eventType) {
                    case 'BILLING.SUBSCRIPTION.CANCELLED':
                    case 'BILLING.SUBSCRIPTION.EXPIRED':
                    case 'BILLING.SUBSCRIPTION.SUSPENDED':
                        $newStatus = $resource['status'] ?? 'CANCELLED';

                        // Downgrade to free when subscription ends
                        $tenant->update([
                            'plan'                       => 'free',
                            'paypal_subscription_status' => $newStatus,
                            'scan_limit_per_day'         => PlanLimits::scanLimitPerDay('free'),
                        ]);

                        PlanChangeLog::create([
                            'tenant_id'      => $tenant->id,
                            'admin_id'       => null,
                            'old_plan'       => $oldPlan,
                            'new_plan'       => 'free',
                            'old_scan_limit' => $tenant->scan_limit_per_day,
                            'new_scan_limit' => PlanLimits::scanLimitPerDay('free'),
                            'source'         => 'paypal_webhook',
                            'note'           => "PayPal {$eventType}: subscription {$subscriptionId} ended. Downgraded to Free.",
                        ]);

                        PlanLimits::clearCache();

                        Log::info('PayPal subscription ended — tenant downgraded to free', [
                            'tenant'   => $tenant->name,
                            'old_plan' => $oldPlan,
                            'event'    => $eventType,
                        ]);
                        break;

                    case 'PAYMENT.SALE.COMPLETED':
                        $tenant->update([
                            'paypal_subscription_status' => 'ACTIVE',
                        ]);
                        break;
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
