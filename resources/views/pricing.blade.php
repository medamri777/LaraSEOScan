<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pricing & Plans - Seo4ma | SEO Plans for Moroccan Businesses</title>
    <meta name="description" content="Choose the perfect SEO plan for your business. From freelancers to enterprises, Seo4ma has a plan that fits. Start with a 3-day free trial.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">

    <style>
        /* --- Pricing Page Specific Styles --- */
        .pricing-page { padding: 3rem 0 5rem; background: #fff; }

        .pricing-hero {
            text-align: center;
            padding: 2rem 0 3rem;
        }
        .pricing-hero-title {
            font-size: 2.75rem;
            font-weight: 900;
            color: #111827;
            margin-bottom: 1rem;
            letter-spacing: -0.03em;
        }
        .pricing-hero-desc {
            font-size: 1.1rem;
            color: #6b7280;
            max-width: 520px;
            margin: 0 auto;
        }

        /* Toggle */
        .pricing-toggle-wrap { text-align: center; margin-bottom: 3rem; }
        .pricing-toggle {
            display: inline-flex;
            border-radius: 9999px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 4px;
        }
        .pricing-toggle button {
            padding: 0.5rem 1.75rem;
            border-radius: 9999px;
            border: none;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: #6b7280;
        }
        .pricing-toggle button.active {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(16,185,129,0.25);
        }
        .pricing-toggle .save-badge {
            font-size: 0.7rem;
            color: #10b981;
            font-weight: 700;
            margin-left: 4px;
        }

        /* Plan Cards */
        .plan-card {
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            height: 100%;
        }
        .plan-card:hover {
            transform: translateY(-6px);
            border-color: #a7f3d0;
            box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        }
        .plan-card.featured {
            border: 2px solid #10b981;
            box-shadow: 0 4px 20px rgba(16,185,129,0.12);
            z-index: 2;
        }
        .plan-card.featured:hover {
            box-shadow: 0 16px 40px rgba(16,185,129,0.15);
        }

        /* Popular badge */
        .popular-badge {
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.35rem 1.5rem;
            border-radius: 0 0 10px 10px;
            z-index: 20;
        }

        /* Plan icon */
        .plan-icon-wrap {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            transition: transform 0.3s;
            font-size: 1.5rem;
        }
        .plan-card:hover .plan-icon-wrap { transform: scale(1.1); }
        .plan-icon-wrap.pro { background: #fef3c7; border: 1px solid #fde68a; color: #d97706; }
        .plan-icon-wrap.guru { background: #ecfdf5; border: 1px solid #a7f3d0; color: #10b981; }
        .plan-icon-wrap.business { background: #fef9c3; border: 1px solid #fde047; color: #ca8a04; }

        /* Price box */
        .price-box {
            padding: 1.25rem;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .price-amount {
            font-size: 2.75rem;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .price-period {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }
        .price-original {
            font-size: 0.75rem;
            color: #9ca3af;
            text-decoration: line-through;
        }
        .price-save {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #10b981;
            margin-top: 0.5rem;
        }
        .trial-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        /* Feature list */
        .feature-list { list-style: none; padding: 0; margin: 0 0 1.5rem 0; flex: 1; }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.45rem 0;
            font-size: 0.85rem;
            color: #4b5563;
        }
        .feature-list li i {
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .feature-list li .check { color: #10b981; }
        .feature-list li .star { color: #f59e0b; }

        /* CTA buttons */
        .btn-plan-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(16,185,129,0.2);
        }
        .btn-plan-primary:hover {
            background: #059669;
            box-shadow: 0 4px 16px rgba(16,185,129,0.3);
            transform: translateY(-1px);
            color: #ffffff;
            text-decoration: none;
        }
        .btn-plan-secondary {
            display: block;
            width: 100%;
            padding: 0.7rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.25s;
            background: #f9fafb;
            color: #4b5563;
        }
        .btn-plan-secondary:hover {
            background: #f3f4f6;
            color: #111827;
            border-color: #d1d5db;
            text-decoration: none;
        }
        .btn-plan-outline {
            display: block;
            width: 100%;
            padding: 0.7rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: 1px solid #a7f3d0;
            cursor: pointer;
            transition: all 0.25s;
            background: #f0fdf4;
            color: #10b981;
        }
        .btn-plan-outline:hover {
            background: #ecfdf5;
            border-color: #10b981;
            text-decoration: none;
        }

        /* Comparison Table */
        .comparison-section { padding: 4rem 0; background: #f9fafb; }
        .comparison-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
        .comparison-table th, .comparison-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            text-align: center;
            font-size: 0.85rem;
            color: #374151;
        }
        .comparison-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 700;
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .comparison-table thead th:first-child { text-align: left; }
        .comparison-table tbody td:first-child {
            text-align: left;
            color: #374151;
            font-weight: 500;
        }
        .comparison-table tbody tr:hover { background: #fafbfc; }
        .comparison-table .col-featured { background: #f0fdf9; }

        /* FAQ */
        .faq-section { padding: 4rem 0; background: #fff; }
        .faq-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            overflow: hidden;
            transition: all 0.2s;
        }
        .faq-item:hover { border-color: #a7f3d0; }
        .faq-question {
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: #111827;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: none;
            width: 100%;
            text-align: left;
            font-size: 0.95rem;
        }
        .faq-question i { transition: transform 0.2s; color: #6b7280; flex-shrink: 0; }
        .faq-question[aria-expanded="true"] i { transform: rotate(180deg); }
        .faq-answer {
            padding: 0 1.5rem 1.25rem;
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Guarantee */
        .guarantee-bar {
            text-align: center;
            padding: 2rem 0;
            background: #ecfdf5;
            border-top: 1px solid #d1fae5;
            border-bottom: 1px solid #d1fae5;
        }
        .guarantee-bar i { color: #10b981; font-size: 1.5rem; }

        /* Bottom CTA */
        .bottom-cta {
            padding: 4rem 0;
            background: linear-gradient(180deg, #fff 0%, #ecfdf5 100%);
            text-align: center;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Navbar (same as landing page) -->
    <nav class="landing-navbar navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
                <span class="brand-text">Seo4ma</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#features') }}">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#tools') }}">Free Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#how-it-works') }}">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('pricing') }}" style="color:#10b981;font-weight:600;">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                    <li class="nav-item ms-lg-3 d-flex gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-nav-primary">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-nav-ghost">Log in</a>
                            <a href="{{ route('register') }}" class="btn-nav-primary">Start Free <i class="bi bi-arrow-right ms-1"></i></a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Pricing Hero -->
    <section class="pricing-page">
        <div class="container">
            @if(session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-success mb-4">{{ session('info') }}</div>
            @endif

            <div class="pricing-hero">
                <h1 class="pricing-hero-title">Simple, transparent pricing</h1>
                <p class="pricing-hero-desc">Choose the plan that fits your business. All plans include a 3-day free trial. Cancel anytime.</p>
            </div>

            <!-- Toggle -->
            <div class="pricing-toggle-wrap">
                <div class="pricing-toggle" id="pricingToggle">
                    <button class="active" id="monthlyBtn" onclick="toggleBilling('monthly')">Monthly</button>
                    <button id="annuallyBtn" onclick="toggleBilling('annually')">Annually <span class="save-badge">-20%</span></button>
                </div>
            </div>

            <!-- Plan Cards -->
            <div class="row g-4 justify-content-center mb-5">

                <!-- Pro -->
                <div class="col-lg-4 col-md-6">
                    <div class="plan-card">
                        <div class="text-center mb-3">
                            <div class="plan-icon-wrap pro"><i class="bi bi-award-fill"></i></div>
                            <h3 style="font-size:1.2rem;font-weight:700;color:#111827;margin-bottom:0.35rem;">Pro</h3>
                            <p style="color:#6b7280;font-size:.85rem;margin:0;min-height:38px;">For freelancers & in-house marketers getting started with SEO.</p>
                        </div>
                        <div class="price-box">
                            <div class="trial-label">3-Day Free Trial</div>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="price-amount monthly-price">$119</span>
                                <span class="price-amount annually-price" style="display:none;">$1,142</span>
                                <div class="text-start">
                                    <span class="monthly-original-price price-original">$149</span>
                                    <span class="annually-original-price price-original" style="display:none;">$1,428</span>
                                    <span class="monthly-suffix price-period">Per Month</span>
                                    <span class="annually-suffix price-period" style="display:none;">Per Year</span>
                                </div>
                            </div>
                            <div class="annually-save" style="display:none;">
                                <span class="price-save"><i class="bi bi-tag-fill me-1"></i>Save 20%</span>
                            </div>
                        </div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>1</strong> Project</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>500</strong> Scans / Day</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>1,000</strong> AI Credits / month</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span>SEO Audit Tool</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span>SERP Simulator</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span>Schema & Robots Generators</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span>Sitemap Crawler</span></li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <a href="{{ route('plan.detail', 'pro') }}?trial=1" class="btn-plan-primary">
                                <i class="bi bi-play-fill"></i> Start Free Trial
                            </a>
                            <a href="{{ route('plan.detail', 'pro') }}" class="btn-plan-secondary">Order Now</a>
                        </div>
                    </div>
                </div>

                <!-- Guru (Featured) -->
                <div class="col-lg-4 col-md-6">
                    <div class="plan-card featured">
                        <div class="popular-badge">Most Popular</div>
                        <div class="text-center mb-3 pt-3">
                            <div class="plan-icon-wrap guru"><i class="bi bi-shield-fill-check"></i></div>
                            <h3 style="font-size:1.2rem;font-weight:700;color:#111827;margin-bottom:0.35rem;">Guru</h3>
                            <p style="color:#6b7280;font-size:.85rem;margin:0;min-height:38px;">For agencies & SMBs that need keyword tracking & research.</p>
                        </div>
                        <div class="price-box">
                            <div class="trial-label">3-Day Free Trial</div>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="price-amount monthly-price">$229</span>
                                <span class="price-amount annually-price" style="display:none;">$2,198</span>
                                <div class="text-start">
                                    <span class="monthly-original-price price-original">$289</span>
                                    <span class="annually-original-price price-original" style="display:none;">$2,748</span>
                                    <span class="monthly-suffix price-period">Per Month</span>
                                    <span class="annually-suffix price-period" style="display:none;">Per Year</span>
                                </div>
                            </div>
                            <div class="annually-save" style="display:none;">
                                <span class="price-save"><i class="bi bi-tag-fill me-1"></i>Save 20%</span>
                            </div>
                        </div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>15</strong> Projects</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>1,500</strong> Keywords Tracked</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>150</strong> Pages to Crawl</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>1,500</strong> Scans / Day</span></li>
                            <li><i class="bi bi-star-fill star"></i><span><strong>3,000</strong> AI Credits / month</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>Keyword Research & Magic Tool</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>Competitor Analysis</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>All Pro Tools Included</span></li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <a href="{{ route('plan.detail', 'guru') }}?trial=1" class="btn-plan-primary">
                                <i class="bi bi-play-fill"></i> Start Free Trial
                            </a>
                            <a href="{{ route('plan.detail', 'guru') }}" class="btn-plan-secondary">Order Now</a>
                        </div>
                    </div>
                </div>

                <!-- Business -->
                <div class="col-lg-4 col-md-6">
                    <div class="plan-card">
                        <div class="text-center mb-3">
                            <div class="plan-icon-wrap business"><i class="bi bi-gem"></i></div>
                            <h3 style="font-size:1.2rem;font-weight:700;color:#111827;margin-bottom:0.35rem;">Business</h3>
                            <p style="color:#6b7280;font-size:.85rem;margin:0;min-height:38px;">For large agencies & enterprises needing unlimited power.</p>
                        </div>
                        <div class="price-box">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span style="font-size:1.25rem;font-weight:700;color:#6b7280;margin-top:0.5rem;">$</span>
                                <span class="price-amount monthly-price">449</span>
                                <span class="price-amount annually-price" style="display:none;">4,310</span>
                                <div class="text-start">
                                    <span class="monthly-original-price price-original">$559</span>
                                    <span class="annually-original-price price-original" style="display:none;">$5,388</span>
                                    <span class="monthly-suffix price-period">Per Month</span>
                                    <span class="annually-suffix price-period" style="display:none;">Per Year</span>
                                </div>
                            </div>
                            <div class="annually-save" style="display:none;">
                                <span class="price-save"><i class="bi bi-tag-fill me-1"></i>Save 20%</span>
                            </div>
                        </div>
                        <ul class="feature-list">
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>100</strong> Projects</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>5,000</strong> Keywords Tracked</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>1M</strong> Pages to Crawl</span></li>
                            <li><i class="bi bi-check-circle-fill check"></i><span><strong>5,000</strong> Scans / Day</span></li>
                            <li><i class="bi bi-star-fill star"></i><span><strong>10,000</strong> AI Credits / month</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>API Access</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>All Tools Included</span></li>
                            <li><i class="bi bi-star-fill star"></i><span>Priority Support</span></li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <a href="{{ route('plan.detail', 'business') }}" class="btn-plan-outline">Order Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guarantee Bar -->
            <div class="guarantee-bar rounded-4 mb-4">
                <div class="row align-items-center g-3 text-center">
                    <div class="col-md-4">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong style="color:#111827;">3-Day Free Trial</strong>
                        <p class="mb-0 small text-muted">Try before you pay</p>
                    </div>
                    <div class="col-md-4">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        <strong style="color:#111827;">Cancel Anytime</strong>
                        <p class="mb-0 small text-muted">No long-term contracts</p>
                    </div>
                    <div class="col-md-4">
                        <i class="bi bi-paypal me-2"></i>
                        <strong style="color:#111827;">PayPal Protected</strong>
                        <p class="mb-0 small text-muted">Secure payments</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="comparison-section">
        <div class="container">
            <div class="text-center mb-4">
                <h2 style="font-size:2rem;font-weight:800;color:#111827;margin-bottom:0.5rem;">Compare all plans</h2>
                <p style="color:#6b7280;">See exactly what's included in each plan</p>
            </div>
            <div class="table-responsive">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th style="width:30%;">Feature</th>
                            <th>Pro<br><span style="color:#10b981;font-weight:800;">$119<span class="fw-normal" style="font-size:0.65rem;"> /mo</span></span></th>
                            <th class="col-featured">Guru<br><span style="color:#10b981;font-weight:800;">$229<span class="fw-normal" style="font-size:0.65rem;"> /mo</span></span></th>
                            <th>Business<br><span style="color:#10b981;font-weight:800;">$449<span class="fw-normal" style="font-size:0.65rem;"> /mo</span></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Projects</td><td>1</td><td class="col-featured"><strong>15</strong></td><td><strong>100</strong></td></tr>
                        <tr><td>Keywords Tracked</td><td>0</td><td class="col-featured"><strong>1,500</strong></td><td><strong>5,000</strong></td></tr>
                        <tr><td>Pages to Crawl</td><td>0</td><td class="col-featured"><strong>150</strong></td><td><strong>1,000,000</strong></td></tr>
                        <tr><td>Scans / Day</td><td>500</td><td class="col-featured"><strong>1,500</strong></td><td><strong>5,000</strong></td></tr>
                        <tr><td>AI Credits / Month</td><td>1,000</td><td class="col-featured"><strong>3,000</strong></td><td><strong>10,000</strong></td></tr>
                        <tr><td>Competitors per Project</td><td>0</td><td class="col-featured"><strong>5</strong></td><td><strong>10</strong></td></tr>
                        <tr><td>SEO Audit Tool</td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>SERP Simulator</td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>Schema Generator</td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>Keyword Research</td><td><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>Competitor Analysis</td><td><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>Google Search Console</td><td><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td class="col-featured"><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>API Access</td><td><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td class="col-featured"><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr><td>Priority Support</td><td><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td class="col-featured"><i class="bi bi-x-circle" style="color:#d1d5db;"></i></td><td><i class="bi bi-check-circle-fill" style="color:#10b981;"></i></td></tr>
                        <tr>
                            <td></td>
                            <td><a href="{{ route('plan.detail', 'pro') }}" class="btn-plan-secondary" style="max-width:160px;margin:0 auto;">Select Pro</a></td>
                            <td class="col-featured"><a href="{{ route('plan.detail', 'guru') }}" class="btn-plan-primary" style="max-width:160px;margin:0 auto;">Select Guru</a></td>
                            <td><a href="{{ route('plan.detail', 'business') }}" class="btn-plan-outline" style="max-width:160px;margin:0 auto;">Select Business</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section">
        <div class="container" style="max-width:720px;">
            <div class="text-center mb-4">
                <h2 style="font-size:1.75rem;font-weight:800;color:#111827;margin-bottom:0.5rem;">Frequently asked questions</h2>
            </div>

            <div class="accordion" id="faqAccordion">
                <div class="faq-item">
                    <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false">
                        Is there a free plan available?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="faq1" class="collapse" data-bs-parent="#faqAccordion">
                        <div class="faq-answer">Yes! We offer a free tier with 1 project, 10 keywords, 10 scans/day, and 10 AI credits/month. No credit card required to get started.</div>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                        How does the 3-day free trial work?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="faq2" class="collapse" data-bs-parent="#faqAccordion">
                        <div class="faq-answer">When you sign up for Pro or Guru, you get full access for 3 days without being charged. If you cancel before the trial ends, you won't pay anything. After the trial, your plan renews automatically.</div>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                        Can I switch plans later?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="faq3" class="collapse" data-bs-parent="#faqAccordion">
                        <div class="faq-answer">Absolutely. You can upgrade or downgrade your plan at any time from your billing page. Changes take effect immediately and billing is prorated.</div>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                        What payment methods do you accept?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="faq4" class="collapse" data-bs-parent="#faqAccordion">
                        <div class="faq-answer">We accept PayPal for all payments. PayPal supports credit cards, debit cards, and bank transfers, so you can pay however you prefer securely.</div>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                        Do you support Moroccan languages (Darija, French, Arabic)?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="faq5" class="collapse" data-bs-parent="#faqAccordion">
                        <div class="faq-answer">Yes! Seo4ma is the only SEO tool built specifically for the Moroccan market. We analyze content in Darija, French, and Arabic with full hreflang and RTL support.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="bottom-cta">
        <div class="container">
            <h2 style="font-size:2rem;font-weight:800;color:#111827;margin-bottom:1rem;">Not sure which plan to choose?</h2>
            <p style="color:#6b7280;font-size:1.05rem;max-width:500px;margin:0 auto 2rem;">Start with our free plan and upgrade when you're ready. No credit card required.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-nav-primary" style="padding:0.75rem 2rem;font-size:1rem;">
                        Go to Dashboard <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-nav-primary" style="padding:0.75rem 2rem;font-size:1rem;">
                        Start Free <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="{{ url('/') }}" class="btn-nav-ghost" style="padding:0.75rem 2rem;font-size:1rem;">
                        Back to Home
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer (same as landing) -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4">
                    <a class="footer-brand d-flex align-items-center gap-2 mb-3" href="{{ url('/') }}">
                        <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
                        <span class="brand-text">Seo4ma</span>
                    </a>
                    <p class="footer-about">The #1 SEO platform built for Moroccan businesses — Darija, French & Arabic.</p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h6 class="footer-heading">Product</h6>
                    <a href="{{ url('/#features') }}" class="footer-link">Features</a>
                    <a href="{{ url('/#tools') }}" class="footer-link">Free Tools</a>
                    <a href="{{ route('pricing') }}" class="footer-link">Pricing</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="footer-heading">Company</h6>
                    <a href="{{ route('about') }}" class="footer-link">About Us</a>
                    <a href="{{ route('contact') }}" class="footer-link">Contact</a>
                    <a href="{{ route('pricing') }}" class="footer-link">Pricing</a>
                </div>
                <div class="col-lg-3">
                    <h6 class="footer-heading">Legal</h6>
                    <a href="{{ route('legal.privacy') }}" class="footer-link">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="footer-link">Terms of Service</a>
                    <a href="{{ route('legal.cookies') }}" class="footer-link">Cookie Policy</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} Seo4ma. All rights reserved.</span>
                <span>Made with <i class="bi bi-heart-fill text-danger"></i> in Morocco</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleBilling(mode) {
            const isAnnual = mode === 'annually';
            document.getElementById('monthlyBtn').classList.toggle('active', !isAnnual);
            document.getElementById('annuallyBtn').classList.toggle('active', isAnnual);
            document.querySelectorAll('.monthly-price, .monthly-suffix, .monthly-original-price').forEach(el => {
                el.style.display = isAnnual ? 'none' : '';
            });
            document.querySelectorAll('.annually-price, .annually-suffix, .annually-original-price, .annually-save').forEach(el => {
                el.style.display = isAnnual ? '' : 'none';
            });
        }
    </script>
</body>
</html>
