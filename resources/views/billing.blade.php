@extends('layouts.app')

@section('title', 'My Subscription — Seo4ma')

@section('content')
@php
    $planColors = [
        'free'     => ['accent' => '#6b7280', 'bg' => '#f3f4f6', 'light' => '#f9fafb'],
        'pro'      => ['accent' => '#10b981', 'bg' => '#ecfdf5', 'light' => '#f0fdf4'],
        'guru'     => ['accent' => '#f59e0b', 'bg' => '#fffbeb', 'light' => '#fefce8'],
        'business' => ['accent' => '#ef4444', 'bg' => '#fef2f2', 'light' => '#fff1f2'],
        'agency'   => ['accent' => '#14b8a6', 'bg' => '#f0fdfa', 'light' => '#ecfdf5'],
    ];
    $pc = $planColors[$plan] ?? $planColors['free'];
@endphp

<style>
    .billing-hero {
        border-radius: 12px;
        border: 1px solid {{ $pc['accent'] }}30;
        background: #ffffff;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .billing-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: {{ $pc['accent'] }};
    }
    .plan-badge-big {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.9rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        background: {{ $pc['bg'] }};
        color: {{ $pc['accent'] }};
        border: 1px solid {{ $pc['accent'] }}30;
        margin-bottom: 0.75rem;
    }
    .usage-bar-wrap { margin-bottom: 1.25rem; }
    .usage-bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.8125rem;
        margin-bottom: 0.35rem;
        color: #6b7280;
        font-weight: 500;
    }
    .usage-bar-label strong { color: #111827; }
    .usage-bar-track {
        height: 8px;
        border-radius: 9999px;
        background: #f3f4f6;
        overflow: hidden;
    }
    .usage-bar-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .upgrade-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 1.5rem;
        transition: all 0.2s;
        height: 100%;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .upgrade-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        border-color: {{ $pc['accent'] }}40;
    }
    .upgrade-card .plan-price {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    .upgrade-card .plan-period {
        font-size: 0.8rem;
        color: #6b7280;
        margin-left: 2px;
    }
    .feature-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.83rem;
        color: #4b5563;
        margin-bottom: 0.5rem;
    }
    .feature-check i { color: #10b981; font-size: 0.875rem; }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .status-pill.active  { background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0; }
    .status-pill.trial   { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .status-pill.free    { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
    .status-pill.expired { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
</style>

<div class="main-content">

    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 style="font-size:1.4rem;font-weight:800;margin:0;color:#111827;">My Subscription</h1>
            <p style="color:#6b7280;font-size:.875rem;margin:0.25rem 0 0;">Manage your plan and monitor usage limits.</p>
        </div>
        @if($plan === 'free')
            <a href="{{ route('pricing') }}" class="btn-filament btn-filament-primary" style="text-decoration:none;font-size:.85rem;">
                <i class="bi bi-lightning-charge-fill me-1"></i> Upgrade Plan
            </a>
        @endif
    </div>

    <!-- Hero: Current Plan -->
    <div class="billing-hero">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <div class="plan-badge-big">
                    <i class="bi bi-lightning-charge-fill"></i>
                    {{ $label }} Plan
                </div>
                <h2 style="font-size:2rem;font-weight:800;color:#111827;margin:0 0 0.25rem;">{{ $label }}</h2>
                <p style="color:#6b7280;font-size:.875rem;margin-bottom:1rem;">
                    @if($plan === 'free')
                        You are on the free tier. Upgrade to unlock more projects, keywords, and scans.
                    @elseif($plan === 'pro')
                        Perfect for freelancers and in-house marketers who need solid SEO audit tools.
                    @elseif($plan === 'guru')
                        Confident growth for agencies and marketing SMBs with keyword tracking.
                    @elseif($plan === 'business')
                        Absolute power for large agencies and enterprises with unlimited crawl capacity.
                    @else
                        You have a custom Agency plan with full access to all features.
                    @endif
                </p>

                <!-- Subscription Status -->
                @if($isOnTrial)
                    <span class="status-pill trial">
                        <i class="bi bi-clock"></i>
                        Trial — expires {{ $trialEndsAt?->format('M d, Y') }}
                    </span>
                @elseif($hasActiveSub && $plan !== 'free')
                    <span class="status-pill active">
                        <i class="bi bi-check-circle-fill"></i>
                        Active Subscription
                    </span>
                @elseif($plan !== 'free')
                    <span class="status-pill expired">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Subscription Inactive
                    </span>
                @else
                    <span class="status-pill free">
                        <i class="bi bi-person"></i>
                        Free Tier
                    </span>
                @endif

                @if($subscriptionId)
                    <div style="margin-top:.75rem;font-size:.78rem;color:#6b7280;">
                        Subscription ID: <code style="color:#111827;">{{ $subscriptionId }}</code>
                        @if($billingCycle)
                            &nbsp;·&nbsp; Billed {{ ucfirst($billingCycle) }}
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-md-5 text-md-end">
                @if($plan === 'free')
                    <a href="{{ route('pricing') }}" class="btn-filament btn-filament-primary" style="text-decoration:none;">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Upgrade Now
                    </a>
                @else
                    <a href="{{ route('pricing') }}" class="btn-filament btn-filament-secondary" style="text-decoration:none;font-size:.85rem;">
                        <i class="bi bi-arrow-up-circle me-1"></i> Change Plan
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Usage Cards -->
    <div class="row g-3 mb-4">
        @foreach($usage as $u)
        @php
            $limit = $u['limit'];
            $used  = $u['used'];
            $pct   = ($limit && $limit > 0) ? min(100, round(($used / $limit) * 100)) : ($limit === null ? 0 : 100);
            $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : $u['color']);
            $unlimitedText = ($limit === null) ? '∞' : (($limit === 0) ? 'N/A' : number_format($limit));
        @endphp
        <div class="col-sm-6 col-lg-3">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.25rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);height:100%;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:{{ $u['color'] }}15;color:{{ $u['color'] }};">
                        <i class="bi {{ $u['icon'] }}"></i>
                    </div>
                    <span style="font-size:.72rem;color:#6b7280;font-weight:500;">{{ $u['label'] }}</span>
                </div>
                <div style="font-size:1.5rem;font-weight:700;margin-bottom:.5rem;color:#111827;">
                    {{ number_format($used) }}
                    <span style="font-size:.875rem;font-weight:400;color:#6b7280;">/ {{ $unlimitedText }}</span>
                </div>
                @if($limit !== null && $limit > 0)
                <div class="usage-bar-track">
                    <div class="usage-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                </div>
                <div style="font-size:.7rem;color:#6b7280;margin-top:.25rem;">{{ $pct }}% used</div>
                @elseif($limit === null)
                <div style="font-size:.75rem;color:{{ $u['color'] }};font-weight:600;">Unlimited</div>
                @else
                <div style="font-size:.75rem;color:#ef4444;font-weight:600;">Not included in this plan</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Daily Tool Usage -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.04);margin-bottom:1.5rem;">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid #f3f4f6;background:#fafbfc;" class="d-flex align-items-center justify-content-between">
            <span style="font-weight:700;font-size:.95rem;color:#111827;">
                <i class="bi bi-speedometer2 me-2" style="color:#f59e0b;"></i>
                Daily Tool Usage <span style="font-weight:400;font-size:.78rem;color:#6b7280;">(resets at midnight)</span>
            </span>
        </div>
        <div style="padding:1.5rem;">
            <div class="row g-3">
                @foreach($toolUsage as $slug => $data)
                    @php
                        $tLimit = $data['limit'];
                        $tUsed  = $data['used'];
                        $tPct   = ($tLimit && $tLimit > 0) ? min(100, round(($tUsed / $tLimit) * 100)) : ($tLimit === null ? 0 : 100);
                        $tBarColor = $tPct >= 90 ? '#ef4444' : ($tPct >= 70 ? '#f59e0b' : '#53FC18');
                        $tLimitText = ($tLimit === null) ? '∞' : (($tLimit === 0) ? 'N/A' : $tLimit);
                        $isDisabled = ($tLimit === 0);
                    @endphp
                    <div class="col-sm-6 col-lg-4">
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:0.85rem 1rem;{{ $isDisabled ? 'opacity:.5;' : '' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ $toolIcons[$slug] ?? 'bi-tools' }}" style="font-size:.9rem;color:#6b7280;"></i>
                                    <span style="font-size:.8rem;font-weight:600;color:#111827;">{{ $toolLabels[$slug] ?? $slug }}</span>
                                </div>
                                <span style="font-size:.75rem;font-weight:700;color:{{ $isDisabled ? '#ef4444' : ($tPct >= 90 ? '#ef4444' : '#111827') }};">
                                    {{ $tUsed }}/{{ $tLimitText }}
                                </span>
                            </div>
                            @if(!$isDisabled && $tLimit !== null && $tLimit > 0)
                            <div class="usage-bar-track" style="height:5px;">
                                <div class="usage-bar-fill" style="width:{{ $tPct }}%;background:{{ $tBarColor }};"></div>
                            </div>
                            @elseif($tLimit === null)
                            <div style="font-size:.7rem;color:#10b981;font-weight:600;">Unlimited</div>
                            @else
                            <div style="font-size:.7rem;color:#ef4444;font-weight:600;">Not available</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- What's included in your plan -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.04);margin-bottom:1.5rem;">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid #f3f4f6;background:#fafbfc;" class="d-flex align-items-center justify-content-between">
            <span style="font-weight:700;font-size:.95rem;color:#111827;">
                <i class="bi bi-list-check me-2" style="color:#10b981;"></i>
                What's Included in {{ $label }} Plan
            </span>
        </div>
        <div style="padding:1.5rem;">
            <div class="row g-3">
                @php
                    $featureMap = [
                        'free'     => ['1 Project', '2 Scans/day', '2 SEO Analyses/day', '10 AI Credits/month', 'SEO Analyzer', 'SERP Simulator', 'Schema Generator (1/day)', 'Robots.txt Generator'],
                        'pro'      => ['1 Project', '10 Scans/day', '10 SEO Analyses/day', '3 Crawl Audits/day', '1,000 AI Credits/month', 'SEO Audit Tool', 'Crawl Audit', 'Schema Generator', 'Sitemap Crawler', 'SERP Simulator', 'Keyword Research (10/day)'],
                        'guru'     => ['15 Projects', '1,500 Keywords Tracked', '150 Pages Crawled', '30 Scans/day', '30 SEO Analyses/day', '10 Crawl Audits/day', '5 Competitors/project', '3,000 AI Credits/month', 'Keyword Research (30/day)', 'Backlink Checker (15/day)', 'Organic Research (15/day)', 'Keyword Magic (10/day)'],
                        'business' => ['100 Projects', '5,000 Keywords Tracked', '1M Pages Crawled', '100 Scans/day', '50 Crawl Audits/day', '50 Sitemap Crawls/day', '10 Competitors/project', '10,000 AI Credits/month', 'All Guru Features', 'API Access', 'Priority Support'],
                        'agency'   => ['Unlimited Projects', 'Unlimited Keywords', 'Unlimited Scans', 'Unlimited AI Credits', 'All Business Features', 'Dedicated Support', 'Custom Branding'],
                    ];
                    $features = $featureMap[$plan] ?? $featureMap['free'];
                @endphp
                @foreach($features as $feature)
                <div class="col-md-6 col-lg-4">
                    <div class="feature-check">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ $feature }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Upgrade options (only if not on business/agency) -->
    @if(!in_array($plan, ['business', 'agency']))
    <div class="mb-4">
        <h5 style="font-weight:700;margin-bottom:1rem;color:#111827;">
            <i class="bi bi-rocket-takeoff me-2" style="color:#10b981;"></i>
            Upgrade Your Plan
        </h5>
        <div class="row g-3">
            @foreach($upgradePlans as $planKey => $pd)
                @if($planKey !== $plan && !($plan === 'guru' && $planKey === 'pro') && !($plan === 'business'))
                <div class="col-md-4">
                    <div class="upgrade-card" style="border-color:{{ $pd['color'] }}25;">
                        <div style="color:{{ $pd['color'] }};font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">
                            <i class="bi bi-lightning-charge-fill me-1"></i>{{ $pd['label'] }}
                        </div>
                        <div class="d-flex align-items-baseline gap-1 mb-1">
                            <span class="plan-price" style="color:{{ $pd['color'] }}">{{ $pd['price'] }}</span>
                            <span style="font-size:1rem;color:#6b7280;">MAD</span>
                            <span class="plan-period">/month</span>
                        </div>
                        <hr style="border-color:#e5e7eb;margin:.75rem 0;">
                        @foreach($pd['features'] as $f)
                        <div class="feature-check"><i class="bi bi-check-circle-fill" style="color:{{ $pd['color'] }}"></i>{{ $f }}</div>
                        @endforeach
                        <a href="{{ route('plan.detail', $planKey) }}" class="btn-filament w-100 mt-3" style="
                            text-decoration:none;text-align:center;font-size:.8rem;
                            background:{{ $pd['color'] }}15;
                            color:{{ $pd['color'] }};
                            border:1px solid {{ $pd['color'] }}35;
                            font-weight:700;
                        ">
                            Upgrade to {{ $pd['label'] }} →
                        </a>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Subscription history note -->
    @if($hasActiveSub)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill" style="color:#14b8a6;font-size:1.25rem;flex-shrink:0;"></i>
            <div>
                <div style="font-weight:600;margin-bottom:.2rem;color:#111827;">Need to cancel or change payment method?</div>
                <div style="color:#6b7280;font-size:.85rem;">
                    Log in to your PayPal account to manage your subscription, update billing details, or cancel at any time.
                    Your subscription ID is <code>{{ $subscriptionId }}</code>.
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
