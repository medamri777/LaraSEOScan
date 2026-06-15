@extends('layouts.app')

@section('title', ucfirst($plan) . ' Plan - Seo4ma')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <a href="{{ route('pricing') }}">Pricing</a>
    <span class="separator">/</span>
    <span class="current">{{ ucfirst($plan) }} Plan</span>
@endsection

@section('content')
<style>
    .checkout-wrapper {
        max-width: 960px;
        margin: 0 auto;
        padding: 2rem 0;
    }
    .plan-header-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .plan-header-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #10b981, #14b8a6);
    }

    /* Billing cycle selector */
    .cycle-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .cycle-option {
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        background: #f9fafb;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        text-align: center;
    }
    .cycle-option:hover {
        border-color: #a7f3d0;
    }
    .cycle-option.selected {
        border-color: #10b981;
        background: #ecfdf5;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .cycle-option .cycle-radio {
        position: absolute;
        top: 0.75rem; right: 0.75rem;
        width: 20px; height: 20px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s;
    }
    .cycle-option.selected .cycle-radio {
        border-color: #10b981;
        background: #10b981;
    }
    .cycle-option.selected .cycle-radio::after {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #ffffff;
    }
    .cycle-price { font-size: 1.75rem; font-weight: 900; color: #111827; }
    .cycle-period { font-size: 0.75rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
    .cycle-save-badge {
        display: inline-block;
        margin-top: 0.5rem;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #10b981;
        font-size: 0.625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    /* Summary card */
    .order-summary {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.625rem 0;
        font-size: 0.875rem;
        color: #374151;
    }
    .summary-row.total {
        border-top: 1px solid #e5e7eb;
        margin-top: 0.5rem;
        padding-top: 1rem;
        font-weight: 800;
        font-size: 1rem;
        color: #111827;
    }

    /* Feature recap */
    .feature-recap li {
        display: flex; align-items: center; gap: 0.625rem;
        padding: 0.4rem 0;
        font-size: 0.8125rem;
        color: #4b5563;
    }
    .feature-recap li i {
        color: #10b981;
        font-size: 0.875rem;
    }

    /* Checkout button */
    .btn-checkout {
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        width: 100%;
        padding: 1rem;
        border-radius: 10px;
        font-size: 0.8125rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: none; cursor: pointer;
        transition: all 0.3s;
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(16,185,129,0.2);
    }
    .btn-checkout:hover {
        background: #059669;
        box-shadow: 0 4px 16px rgba(16,185,129,0.3);
        transform: translateY(-1px);
    }
    .btn-checkout:active { transform: translateY(0); }

    .trial-badge {
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #10b981;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .security-badges {
        display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .security-badge {
        display: flex; align-items: center; gap: 0.375rem;
        font-size: 0.6875rem;
        color: #6b7280;
    }
    .security-badge i { color: #10b981; font-size: 0.875rem; }
</style>

<div class="checkout-wrapper" x-data="planCheckout()">
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <!-- Left: Plan Configuration -->
        <div class="col-lg-7">
            <div class="plan-header-card mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border:1px solid #e5e7eb;">
                        @if($plan === 'pro')
                            <i class="bi bi-award-fill" style="font-size:1.5rem;color:#d97706;"></i>
                        @elseif($plan === 'guru')
                            <i class="bi bi-shield-fill-check" style="font-size:1.5rem;color:#10b981;"></i>
                        @else
                            <i class="bi bi-gem" style="font-size:1.5rem;color:#ca8a04;"></i>
                        @endif
                    </div>
                    <div>
                        <h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin:0;">{{ ucfirst($plan) }} Plan</h2>
                        <p style="color:#6b7280;font-size:.85rem;margin:0.25rem 0 0;">{{ $planData['description'] }}</p>
                    </div>
                </div>

                @if($trial)
                    <div class="trial-badge mb-3">
                        <i class="bi bi-gift-fill"></i>
                        3-Day Free Trial — No charge today, cancel anytime
                    </div>
                @endif
            </div>

            <!-- Billing Cycle Selector -->
            <h5 style="font-weight:700;color:#111827;margin-bottom:1rem;">
                <i class="bi bi-calendar3 me-2" style="color:#6b7280;"></i>Select Billing Cycle
            </h5>
            <div class="cycle-selector mb-4">
                <div class="cycle-option" :class="{ 'selected': cycle === 'monthly' }" @click="cycle = 'monthly'">
                    <div class="cycle-radio"></div>
                    <div class="cycle-period">Monthly</div>
                    <div class="cycle-price">${{ $planData['monthly'] }}</div>
                    <div class="cycle-period">Per Month</div>
                </div>
                <div class="cycle-option" :class="{ 'selected': cycle === 'annual' }" @click="cycle = 'annual'">
                    <div class="cycle-radio"></div>
                    <div class="cycle-period">Annually</div>
                    <div class="cycle-price">${{ $planData['annual'] }}</div>
                    <div class="cycle-period">Per Year</div>
                    <div class="cycle-save-badge">Save 20%</div>
                </div>
            </div>

            <!-- Features Recap -->
            <h5 style="font-weight:700;color:#111827;margin-bottom:1rem;">
                <i class="bi bi-box-seam me-2" style="color:#6b7280;"></i>What's Included
            </h5>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                <ul class="feature-recap list-unstyled mb-0">
                    @foreach($planData['features'] as $feature)
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Right: Order Summary -->
        <div class="col-lg-5">
            <div class="order-summary position-sticky" style="top: calc(var(--topbar-height, 64px) + 1.5rem);">
                <h5 style="font-weight:700;color:#111827;margin-bottom:1rem;">Order Summary</h5>

                <div class="summary-row">
                    <span style="color:#6b7280;">Plan</span>
                    <span style="font-weight:600;">{{ ucfirst($plan) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color:#6b7280;">Billing Cycle</span>
                    <span style="font-weight:600;" x-text="cycle === 'monthly' ? 'Monthly' : 'Annually'"></span>
                </div>
                <div class="summary-row">
                    <span style="color:#6b7280;">Price</span>
                    <span style="font-weight:600;" x-text="cycle === 'monthly' ? '${{ $planData['monthly'] }} / mo' : '${{ $planData['annual'] }} / yr'"></span>
                </div>
                @if($trial)
                <div class="summary-row">
                    <span style="color:#6b7280;">Free Trial</span>
                    <span style="font-weight:600;color:#10b981;">3 Days</span>
                </div>
                @endif
                <div class="summary-row total">
                    <span>Due Today</span>
                    @if($trial)
                        <span style="color:#10b981;font-size:1.125rem;">$0.00</span>
                    @else
                        <span style="color:#10b981;font-size:1.125rem;" x-text="cycle === 'monthly' ? '${{ $planData['monthly'] }}' : '${{ $planData['annual'] }}'"></span>
                    @endif
                </div>

                <form action="{{ route('subscription.checkout') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan }}">
                    <input type="hidden" name="cycle" :value="cycle">
                    <button type="submit" class="btn-checkout">
                        <i class="bi bi-paypal"></i>
                        @if($trial)
                            Start Free Trial with PayPal
                        @else
                            Pay with PayPal
                        @endif
                    </button>
                </form>

                <div class="security-badges">
                    <div class="security-badge">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>SSL Secured</span>
                    </div>
                    <div class="security-badge">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Cancel Anytime</span>
                    </div>
                    <div class="security-badge">
                        <i class="bi bi-paypal"></i>
                        <span>PayPal Protected</span>
                    </div>
                </div>

                <div style="text-align:center;margin-top:1rem;">
                    <small style="font-size:0.6875rem;color:#6b7280;">
                        @if($trial)
                            Your 3-day free trial starts immediately. After the trial ends, you'll be charged
                            <span x-text="cycle === 'monthly' ? '${{ $planData['monthly'] }}/mo' : '${{ $planData['annual'] }}/yr'"></span>.
                            Cancel anytime before the trial ends to avoid charges.
                        @else
                            You will be charged immediately. Your subscription renews automatically until cancelled.
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function planCheckout() {
        return {
            cycle: '{{ $trial ? "monthly" : "monthly" }}'
        };
    }
</script>
@endpush
@endsection
