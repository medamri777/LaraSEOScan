<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Seo4ma — Morocco's #1 SEO Platform | Audit, Track & Dominate Google</title>
    <meta name="description" content="Seo4ma is the only SEO tool built for Moroccan businesses. Audit your website, track keywords in Darija & French, and outrank your competitors in every city.">
    <meta name="keywords" content="SEO Maroc, audit SEO, référencement Maroc, SEO tool Morocco, تحسين محركات البحث المغرب, SEO Casablanca, SEO Marrakech">
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Seo4ma — Morocco's #1 SEO Platform">
    <meta property="og:description" content="Audit your website, track keywords in Darija & French, and outrank your competitors — built for Moroccan businesses.">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="Seo4ma — Morocco's #1 SEO Platform">
    <meta property="twitter:description" content="Audit your website, track keywords in Darija & French, and outrank your competitors.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "Seo4ma",
      "operatingSystem": "Web",
      "applicationCategory": "SEOApplication",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "MAD"
      },
      "description": "Seo4ma is the only SEO tool built for Moroccan businesses. Audit websites, track keywords in Darija & French, and outrank competitors.",
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "2400"
      }
    }
    </script>
</head>
<body class="antialiased">

    <!-- Navbar -->
    <nav class="landing-navbar navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <div class="brand-icon">
                    <i class="bi bi-bar-chart-fill"></i>
                </div>
                <span class="brand-text">Seo4ma</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tools">Free Tools</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pricing') }}">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3 d-flex gap-2">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-nav-primary">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-nav-ghost">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-nav-primary">Start Free <i class="bi bi-arrow-right ms-1"></i></a>
                                @endif
                            @endauth
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg-pattern"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <span class="badge-dot"></span>
                        Morocco's #1 SEO Platform — Free to Start
                    </div>
                    <h1 class="hero-title">
                        شوف فين كتبان فـ Google
                        <span class="text-gradient">— وكيفاش تتصدر</span>
                    </h1>
                    <p class="hero-subtitle">
                        L'unique outil SEO conçu pour le marché marocain. Audit gratuit en 30 secondes.
                    </p>
                    <p class="hero-desc">
                        The only SEO tool built for Moroccan businesses — Darija, French & Arabic support out of the box.
                    </p>

                    <!-- Domain Search Form -->
                    <form action="{{ route('domain.entry') }}" method="POST" class="hero-form">
                        @csrf
                        <div class="search-box">
                            <div class="search-icon">
                                <i class="bi bi-globe2"></i>
                            </div>
                            <input type="text" name="url" placeholder="Enter your website (e.g., example.com)" required>
                            <button type="submit">
                                Start Free Audit <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        @error('url')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </form>

                    <div class="hero-trust">
                        <span><i class="bi bi-check-circle-fill"></i> No credit card</span>
                        <span><i class="bi bi-check-circle-fill"></i> Darija + French</span>
                        <span><i class="bi bi-check-circle-fill"></i> Score /100</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="hero-card-v2">
                            {{-- Card top: domain + live badge --}}
                            <div class="hc-top">
                                <div class="hc-domain-row">
                                    <div class="hc-favicon">
                                        <i class="bi bi-globe-americas"></i>
                                    </div>
                                    <div>
                                        <div class="hc-domain">maroc-immobilier.ma</div>
                                        <div class="hc-url-path">seo4ma.ma/audit</div>
                                    </div>
                                </div>
                                <span class="hc-live-badge"><span class="hc-live-dot"></span> Live</span>
                            </div>

                            {{-- Score ring + category bars --}}
                            <div class="hc-score-section">
                                <div class="hc-ring-wrap">
                                    <svg viewBox="0 0 140 140" class="hc-ring">
                                        <defs>
                                            <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stop-color="#10b981"/>
                                                <stop offset="100%" stop-color="#34d399"/>
                                            </linearGradient>
                                        </defs>
                                        <circle cx="70" cy="70" r="62" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                                        <circle cx="70" cy="70" r="62" fill="none" stroke="url(#ringGrad)" stroke-width="10" stroke-dasharray="389.6" stroke-dashoffset="50.6" stroke-linecap="round" transform="rotate(-90 70 70)" class="hc-ring-progress"/>
                                    </svg>
                                    <div class="hc-ring-text">
                                        <span class="hc-ring-value">87</span>
                                        <span class="hc-ring-label">/100</span>
                                    </div>
                                </div>
                                <div class="hc-categories">
                                    <div class="hc-cat">
                                        <div class="hc-cat-header">
                                            <span class="hc-cat-icon hc-icon-green"><i class="bi bi-gear-fill"></i></span>
                                            <span class="hc-cat-name">Technical</span>
                                            <span class="hc-cat-score">26/30</span>
                                        </div>
                                        <div class="hc-bar"><div class="hc-bar-fill hc-bar-green" style="width:87%"></div></div>
                                    </div>
                                    <div class="hc-cat">
                                        <div class="hc-cat-header">
                                            <span class="hc-cat-icon hc-icon-amber"><i class="bi bi-file-earmark-text-fill"></i></span>
                                            <span class="hc-cat-name">On-Page</span>
                                            <span class="hc-cat-score">22/30</span>
                                        </div>
                                        <div class="hc-bar"><div class="hc-bar-fill hc-bar-amber" style="width:73%"></div></div>
                                    </div>
                                    <div class="hc-cat">
                                        <div class="hc-cat-header">
                                            <span class="hc-cat-icon hc-icon-blue"><i class="bi bi-geo-alt-fill"></i></span>
                                            <span class="hc-cat-name">Local SEO</span>
                                            <span class="hc-cat-score">18/20</span>
                                        </div>
                                        <div class="hc-bar"><div class="hc-bar-fill hc-bar-blue" style="width:90%"></div></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Issue breakdown --}}
                            <div class="hc-issues">
                                <div class="hc-issue-row">
                                    <span class="hc-issue-dot hc-dot-red"></span>
                                    <span class="hc-issue-name">Errors</span>
                                    <span class="hc-issue-count hc-count-red">23</span>
                                </div>
                                <div class="hc-issue-row">
                                    <span class="hc-issue-dot hc-dot-amber"></span>
                                    <span class="hc-issue-name">Warnings</span>
                                    <span class="hc-issue-count hc-count-amber">67</span>
                                </div>
                                <div class="hc-issue-row">
                                    <span class="hc-issue-dot hc-dot-blue"></span>
                                    <span class="hc-issue-name">Notices</span>
                                    <span class="hc-issue-count hc-count-blue">52</span>
                                </div>
                            </div>

                            {{-- Bottom stats --}}
                            <div class="hc-bottom-stats">
                                <div class="hc-bstat">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <span><strong>38</strong> pages</span>
                                </div>
                                <div class="hc-bstat-divider"></div>
                                <div class="hc-bstat">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                    <span><strong>1.2s</strong> load</span>
                                </div>
                                <div class="hc-bstat-divider"></div>
                                <div class="hc-bstat">
                                    <i class="bi bi-shield-check"></i>
                                    <span><strong>HTTPS</strong> ok</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Bar -->
    <section class="trust-bar">
        <div class="container">
            <div class="trust-bar-inner">
                <div class="trust-stat">
                    <div class="trust-stat-value">2,400+</div>
                    <div class="trust-stat-label">Sites Audited</div>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <div class="trust-stat-value">50+</div>
                    <div class="trust-stat-label">SEO Factors</div>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <div class="trust-stat-value">30s</div>
                    <div class="trust-stat-label">Audit Time</div>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <div class="trust-stat-value">4.8★</div>
                    <div class="trust-stat-label">User Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2 class="section-title">Everything you need to dominate Google in Morocco</h2>
                <p class="section-desc">Seo4ma checks 50+ SEO factors and gives you a score out of 100 with actionable fixes you can implement today.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-emerald">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h4 class="feature-title">SEO Score /100</h4>
                        <p class="feature-desc">Get a clear score across 5 categories: Technical, On-Page, Local, Mobile, and Speed performance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-blue">
                            <i class="bi bi-translate"></i>
                        </div>
                        <h4 class="feature-title">Darija + French + Arabic</h4>
                        <p class="feature-desc">Analyzes your content in all Moroccan languages with hreflang and RTL support built in.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-amber">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h4 class="feature-title">Local SEO Check</h4>
                        <p class="feature-desc">Check your Google Business Profile, NAP consistency, and local citations across Moroccan directories.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-purple">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4 class="feature-title">Rank Tracking</h4>
                        <p class="feature-desc">Track keyword positions daily for Casablanca, Marrakech, Rabat, and every Moroccan city.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-rose">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <h4 class="feature-title">PDF & CSV Reports</h4>
                        <p class="feature-desc">Generate professional white-label reports to share with clients or your marketing team.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrap icon-teal">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4 class="feature-title">Competitor Analysis</h4>
                        <p class="feature-desc">Discover who ranks above you and find keyword gaps to exploit in your local market.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Free Tools Section -->
    <section class="tools-section" id="tools">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Free Tools</span>
                <h2 class="section-title">Powerful SEO tools — completely free</h2>
                <p class="section-desc">No sign-up required. Start using professional SEO tools right now.</p>
            </div>

            <div class="tools-grid">
                <a href="{{ route('tools.seo-audit') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-clipboard2-check"></i></div>
                    <div>
                        <h5 class="tool-title">SEO Audit</h5>
                        <p class="tool-desc">Instant page-level SEO analysis with actionable fixes</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
                <a href="{{ route('tools.keyword-overview') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-search"></i></div>
                    <div>
                        <h5 class="tool-title">Keyword Overview</h5>
                        <p class="tool-desc">Analyze any keyword's search volume and difficulty</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
                <a href="{{ route('tools.serp-simulator') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-layout-text-window"></i></div>
                    <div>
                        <h5 class="tool-title">SERP Simulator</h5>
                        <p class="tool-desc">Preview how your page looks in Google search results</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
                <a href="{{ route('tools.schema-generator') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-code-square"></i></div>
                    <div>
                        <h5 class="tool-title">Schema Generator</h5>
                        <p class="tool-desc">Create structured data markup for rich snippets</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
                <a href="{{ route('tools.backlink-checker') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-link-45deg"></i></div>
                    <div>
                        <h5 class="tool-title">Backlink Checker</h5>
                        <p class="tool-desc">Discover who's linking to your competitors</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
                <a href="{{ route('tools.authority-checker') }}" class="tool-card">
                    <div class="tool-icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <h5 class="tool-title">Authority Checker</h5>
                        <p class="tool-desc">Check domain authority and page strength metrics</p>
                    </div>
                    <i class="bi bi-arrow-right tool-arrow"></i>
                </a>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('register') }}" class="btn-cta-outline">
                    Unlock All 15+ Tools — Start Free <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="steps-section" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">How It Works</span>
                <h2 class="section-title">Get your SEO score in 3 simple steps</h2>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <h5 class="step-title">Create Account</h5>
                    <p class="step-desc">Sign up for free in 30 seconds. No credit card required.</p>
                </div>
                <div class="step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="step-card">
                    <div class="step-num">2</div>
                    <h5 class="step-title">Enter Your URL</h5>
                    <p class="step-desc">Paste your website link and our crawler analyzes every page.</p>
                </div>
                <div class="step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="step-card">
                    <div class="step-num">3</div>
                    <h5 class="step-title">Get Score & Fix</h5>
                    <p class="step-desc">See your score /100 and follow actionable fixes to rank higher.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">Ready to dominate Google in Morocco?</h2>
                <p class="cta-desc">Join +2,400 Moroccan businesses already using Seo4ma to grow their online visibility.</p>
                <form action="{{ route('domain.entry') }}" method="POST" class="cta-form">
                    @csrf
                    <div class="search-box cta-search">
                        <div class="search-icon">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <input type="text" name="url" placeholder="Enter your website..." required>
                        <button type="submit">
                            Start Now <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
                <div class="cta-trust">
                    <i class="bi bi-check-circle-fill"></i> Free forever plan available &nbsp;&bull;&nbsp;
                    <i class="bi bi-check-circle-fill"></i> Setup in 30 seconds &nbsp;&bull;&nbsp;
                    <i class="bi bi-check-circle-fill"></i> Cancel anytime
                </div>
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
                    <p class="footer-about">
                        The #1 SEO platform built for Moroccan businesses — Darija, French & Arabic. Audit, track, and dominate Google search results.
                    </p>
                    <div class="footer-socials">
                        <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h6 class="footer-heading">Product</h6>
                    <a href="#features" class="footer-link">Features</a>
                    <a href="#tools" class="footer-link">Free Tools</a>
                    <a href="{{ route('pricing') }}" class="footer-link">Pricing</a>
                    <a href="#how-it-works" class="footer-link">How It Works</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="footer-heading">Free Tools</h6>
                    <a href="{{ route('tools.seo-audit') }}" class="footer-link">SEO Audit</a>
                    <a href="{{ route('tools.keyword-overview') }}" class="footer-link">Keyword Overview</a>
                    <a href="{{ route('tools.serp-simulator') }}" class="footer-link">SERP Simulator</a>
                    <a href="{{ route('tools.schema-generator') }}" class="footer-link">Schema Generator</a>
                    <a href="{{ route('tools.backlink-checker') }}" class="footer-link">Backlink Checker</a>
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
