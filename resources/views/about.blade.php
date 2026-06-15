<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>About Us - Seo4ma | Morocco's #1 SEO Platform</title>
    <meta name="description" content="Seo4ma is built by a passionate Moroccan team dedicated to helping businesses dominate search results in Darija, French & Arabic.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">

    <style>
        .about-hero {
            padding: 5rem 0 4rem;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
            text-align: center;
        }
        .about-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #a7f3d0;
            font-size: 0.75rem;
            font-weight: 700;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1.5rem;
        }
        .about-hero-title {
            font-size: 3rem;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            line-height: 1.1;
        }
        .about-hero-title span { color: #10b981; }
        .about-hero-desc {
            font-size: 1.15rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Stats bar */
        .stats-bar {
            padding: 3rem 0;
            background: #ffffff;
            border-bottom: 1px solid #f3f4f6;
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: #10b981;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* Story section */
        .story-section {
            padding: 5rem 0;
            background: #ffffff;
        }
        .story-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 2.5rem;
            height: 100%;
        }
        .story-card-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .story-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
        }
        .story-card-text {
            font-size: 0.95rem;
            color: #6b7280;
            line-height: 1.7;
        }

        /* Mission section */
        .mission-section {
            padding: 5rem 0;
            background: #f9fafb;
        }
        .mission-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .mission-icon {
            width: 72px; height: 72px;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            background: #ecfdf5;
            border: 2px solid #a7f3d0;
            color: #10b981;
        }

        /* Values section */
        .values-section {
            padding: 5rem 0;
            background: #ffffff;
        }
        .value-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        .value-item:hover {
            border-color: #a7f3d0;
            background: #f0fdf4;
        }
        .value-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Team section */
        .team-section {
            padding: 5rem 0;
            background: #f9fafb;
        }
        .team-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }
        .team-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-color: #a7f3d0;
        }
        .team-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
        }

        /* Tech section */
        .tech-section {
            padding: 5rem 0;
            background: #ffffff;
        }
        .tech-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 9999px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin: 0.25rem;
            transition: all 0.2s;
        }
        .tech-badge:hover {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #10b981;
        }

        /* CTA section */
        .about-cta {
            padding: 5rem 0;
            background: linear-gradient(180deg, #f9fafb 0%, #ecfdf5 100%);
            text-align: center;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        .section-desc {
            font-size: 1rem;
            color: #6b7280;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('about') }}" style="color:#10b981;font-weight:600;">About</a></li>
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

    <!-- Hero -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-badge">
                <i class="bi bi-geo-alt-fill"></i> Built in Morocco
            </div>
            <h1 class="about-hero-title">We're building the future of<br><span>Moroccan SEO</span></h1>
            <p class="about-hero-desc">Seo4ma is the first SEO platform designed from the ground up for Moroccan businesses. We understand Darija, French, and Arabic — because we live it every day.</p>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats-bar">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Sites Audited</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">1M+</div>
                        <div class="stat-label">Keywords Tracked</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="story-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-label"><i class="bi bi-book"></i> Our Story</div>
                <h2 class="section-title">Why we built Seo4ma</h2>
                <p class="section-desc">Moroccan businesses deserved better than generic SEO tools that don't understand their market.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="story-card">
                        <div class="story-card-icon" style="background:#fef3c7;border:1px solid #fde68a;color:#d97706;">
                            <i class="bi bi-lightbulb-fill"></i>
                        </div>
                        <h3 class="story-card-title">The Problem</h3>
                        <p class="story-card-text">Moroccan businesses were paying $200+/month for international SEO tools that couldn't handle Darija, French, or Arabic content. Rankings in local search were inaccurate.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="story-card">
                        <div class="story-card-icon" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#10b981;">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                        <h3 class="story-card-title">The Solution</h3>
                        <p class="story-card-text">We built a platform that natively understands Moroccan search behavior — from keyword research in Darija to local SERP tracking across Casablanca, Marrakech, and every city.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="story-card">
                        <div class="story-card-icon" style="background:#ede9fe;border:1px solid #c4b5fd;color:#8b5cf6;">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                        </div>
                        <h3 class="story-card-title">The Vision</h3>
                        <p class="story-card-text">Become the go-to SEO platform for every Moroccan business — from solo freelancers in Rabat to agencies in Casablanca — making professional SEO accessible to all.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission -->
    <section class="mission-section">
        <div class="container" style="max-width:700px;">
            <div class="mission-card">
                <div class="mission-icon"><i class="bi bi-bullseye"></i></div>
                <h2 style="font-size:1.75rem;font-weight:800;color:#111827;margin-bottom:1rem;">Our Mission</h2>
                <p style="font-size:1.1rem;color:#4b5563;line-height:1.8;margin:0;">
                    To democratize SEO for Moroccan businesses by providing enterprise-grade tools that understand local languages, local markets, and local budgets. We believe every Moroccan website deserves to rank #1 on Google — regardless of size or budget.
                </p>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section">
        <div class="container" style="max-width:700px;">
            <div class="text-center mb-5">
                <div class="section-label"><i class="bi bi-heart-fill"></i> Our Values</div>
                <h2 class="section-title">What drives us</h2>
            </div>
            <div class="value-item">
                <div class="value-icon" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#10b981;">
                    <i class="bi bi-translate"></i>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Local First</h4>
                    <p style="font-size:0.9rem;color:#6b7280;margin:0;">Everything we build starts with the Moroccan market. Darija keyword research, French content analysis, Arabic RTL support — it's all native.</p>
                </div>
            </div>
            <div class="value-item">
                <div class="value-icon" style="background:#eff6ff;border:1px solid #93c5fd;color:#3b82f6;">
                    <i class="bi bi-unlock-fill"></i>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Accessible to All</h4>
                    <p style="font-size:0.9rem;color:#6b7280;margin:0;">Professional SEO shouldn't cost $200/month. Our plans start at $119 with a free tier so every business can get started.</p>
                </div>
            </div>
            <div class="value-item">
                <div class="value-icon" style="background:#fef3c7;border:1px solid #fde68a;color:#d97706;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Data-Driven</h4>
                    <p style="font-size:0.9rem;color:#6b7280;margin:0;">Every feature is backed by real data. We don't guess — we crawl, analyze, and deliver actionable insights that move the needle.</p>
                </div>
            </div>
            <div class="value-item">
                <div class="value-icon" style="background:#fce7f3;border:1px solid #f9a8d4;color:#ec4899;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Trust & Transparency</h4>
                    <p style="font-size:0.9rem;color:#6b7280;margin:0;">No hidden fees, no lock-in contracts. 3-day free trial on all paid plans, cancel anytime, and your data is always yours.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="tech-section">
        <div class="container" style="max-width:700px;">
            <div class="text-center mb-5">
                <div class="section-label"><i class="bi bi-code-slash"></i> Built With</div>
                <h2 class="section-title">Our Technology</h2>
                <p class="section-desc">We use modern, battle-tested technologies to deliver speed, reliability, and security.</p>
            </div>
            <div class="text-center">
                <span class="tech-badge"><i class="bi bi-filetype-php"></i> Laravel</span>
                <span class="tech-badge"><i class="bi bi-palette-fill"></i> Tailwind CSS</span>
                <span class="tech-badge"><i class="bi bi-lightning-fill"></i> Alpine.js</span>
                <span class="tech-badge"><i class="bi bi-database-fill"></i> MySQL</span>
                <span class="tech-badge"><i class="bi bi-hdd-rack-fill"></i> Redis</span>
                <span class="tech-badge"><i class="bi bi-robot"></i> OpenAI API</span>
                <span class="tech-badge"><i class="bi bi-diagram-3-fill"></i> Filament</span>
                <span class="tech-badge"><i class="bi bi-globe2"></i> DataForSEO API</span>
                <span class="tech-badge"><i class="bi bi-paypal"></i> PayPal</span>
                <span class="tech-badge"><i class="bi bi-speedometer2"></i> PageSpeed API</span>
                <span class="tech-badge"><i class="bi bi-cloud-fill"></i> Google Search Console</span>
                <span class="tech-badge"><i class="bi bi-shield-lock-fill"></i> Sanctum Auth</span>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="about-cta">
        <div class="container">
            <h2 style="font-size:2rem;font-weight:800;color:#111827;margin-bottom:1rem;">Ready to dominate Moroccan SEO?</h2>
            <p style="color:#6b7280;font-size:1.05rem;max-width:500px;margin:0 auto 2rem;">Join hundreds of Moroccan businesses already using Seo4ma to grow their online presence.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-nav-primary" style="padding:0.75rem 2rem;font-size:1rem;">
                        Go to Dashboard <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-nav-primary" style="padding:0.75rem 2rem;font-size:1rem;">
                        Start Free <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="{{ route('pricing') }}" class="btn-nav-ghost" style="padding:0.75rem 2rem;font-size:1rem;">
                        View Pricing
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
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
</body>
</html>
