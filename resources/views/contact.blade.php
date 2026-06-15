<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Contact Us - Seo4ma | Get in Touch</title>
    <meta name="description" content="Have questions about Seo4ma? Contact our team — we're here to help you succeed with SEO in Morocco.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">

    <style>
        .contact-hero {
            padding: 5rem 0 3rem;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
            text-align: center;
        }
        .contact-hero-title {
            font-size: 3rem;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            line-height: 1.1;
        }
        .contact-hero-title span { color: #10b981; }
        .contact-hero-desc {
            font-size: 1.15rem;
            color: #6b7280;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Contact cards */
        .contact-cards {
            padding: 3rem 0;
        }
        .contact-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }
        .contact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-color: #a7f3d0;
        }
        .contact-card-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        .contact-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .contact-card-text {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.6;
        }
        .contact-card-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #10b981;
            text-decoration: none;
        }
        .contact-card-link:hover { color: #059669; }

        /* Form section */
        .form-section {
            padding: 4rem 0 5rem;
            background: #f9fafb;
        }
        .form-wrapper {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }
        .form-control-custom {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #111827;
            background: #ffffff;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .form-control-custom:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        .form-control-custom::placeholder { color: #9ca3af; }
        textarea.form-control-custom { resize: vertical; min-height: 140px; }
        select.form-control-custom { appearance: auto; }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(16,185,129,0.2);
        }
        .btn-submit:hover {
            background: #059669;
            box-shadow: 0 4px 16px rgba(16,185,129,0.3);
            transform: translateY(-1px);
        }

        /* FAQ mini */
        .faq-mini {
            padding: 4rem 0;
            background: #ffffff;
        }
        .faq-mini-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            overflow: hidden;
        }
        .faq-mini-question {
            padding: 1rem 1.25rem;
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
            font-size: 0.9rem;
        }
        .faq-mini-question i { color: #6b7280; flex-shrink: 0; transition: transform 0.2s; }
        .faq-mini-question[aria-expanded="true"] i { transform: rotate(180deg); }
        .faq-mini-answer {
            padding: 0 1.25rem 1rem;
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.6;
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
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('contact') }}" style="color:#10b981;font-weight:600;">Contact</a></li>
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
    <section class="contact-hero">
        <div class="container">
            <h1 class="contact-hero-title">Get in <span>touch</span></h1>
            <p class="contact-hero-desc">Have a question, need a demo, or want to partner with us? We'd love to hear from you. Our team typically responds within 24 hours.</p>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="form-section">
        <div class="container" style="max-width:640px;">
            <div class="text-center mb-4">
                <h2 style="font-size:1.75rem;font-weight:800;color:#111827;margin-bottom:0.5rem;">Send us a message</h2>
                <p style="color:#6b7280;font-size:0.95rem;">Fill out the form below and we'll get back to you as soon as possible.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;border-radius:12px;">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background:#fef2f2;border:1px solid #fecaca;color:#ef4444;border-radius:12px;">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <span>{{ $errors->first() }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="form-wrapper">
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">First Name *</label>
                            <input type="text" name="first_name" class="form-control-custom" placeholder="Youssef" required value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Last Name *</label>
                            <input type="text" name="last_name" class="form-control-custom" placeholder="El Amrani" required value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Email *</label>
                            <input type="email" name="email" class="form-control-custom" placeholder="you@company.com" required value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Company</label>
                            <input type="text" name="company" class="form-control-custom" placeholder="Your company" value="{{ old('company') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Subject *</label>
                            <select name="subject" class="form-control-custom" required>
                                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Choose a topic...</option>
                                <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Question</option>
                                <option value="sales" {{ old('subject') == 'sales' ? 'selected' : '' }}>Sales & Pricing</option>
                                <option value="support" {{ old('subject') == 'support' ? 'selected' : '' }}>Technical Support</option>
                                <option value="partnership" {{ old('subject') == 'partnership' ? 'selected' : '' }}>Partnership</option>
                                <option value="bug" {{ old('subject') == 'bug' ? 'selected' : '' }}>Report a Bug</option>
                                <option value="feature" {{ old('subject') == 'feature' ? 'selected' : '' }}>Feature Request</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Message *</label>
                            <textarea name="message" class="form-control-custom" placeholder="Tell us how we can help..." required>{{ old('message') }}</textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn-submit">
                                <i class="bi bi-send-fill"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-mini">
        <div class="container" style="max-width:640px;">
            <div class="text-center mb-4">
                <h2 style="font-size:1.5rem;font-weight:800;color:#111827;margin-bottom:0.5rem;">Quick Answers</h2>
            </div>
            <div class="accordion" id="contactFaq">
                <div class="faq-mini-item">
                    <button class="faq-mini-question" data-bs-toggle="collapse" data-bs-target="#cfq1" aria-expanded="false">
                        How quickly will I get a response?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="cfq1" class="collapse" data-bs-parent="#contactFaq">
                        <div class="faq-mini-answer">We typically respond within 24 hours on business days. For urgent technical issues, paid plan users get priority support with responses within 4 hours.</div>
                    </div>
                </div>
                <div class="faq-mini-item">
                    <button class="faq-mini-question" data-bs-toggle="collapse" data-bs-target="#cfq2" aria-expanded="false">
                        Do you offer custom enterprise plans?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="cfq2" class="collapse" data-bs-parent="#contactFaq">
                        <div class="faq-mini-answer">Yes! For agencies and enterprises with specific needs (custom API limits, white-labeling, dedicated support), reach out to sales@seo4ma.com and we'll build a custom plan for you.</div>
                    </div>
                </div>
                <div class="faq-mini-item">
                    <button class="faq-mini-question" data-bs-toggle="collapse" data-bs-target="#cfq3" aria-expanded="false">
                        Can I get a refund?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="cfq3" class="collapse" data-bs-parent="#contactFaq">
                        <div class="faq-mini-answer">We offer a 3-day free trial on Pro and Guru plans so you can test before paying. If you're not satisfied after subscribing, contact us within 7 days of your first charge for a full refund.</div>
                    </div>
                </div>
                <div class="faq-mini-item">
                    <button class="faq-mini-question" data-bs-toggle="collapse" data-bs-target="#cfq4" aria-expanded="false">
                        Do you offer training or onboarding?
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="cfq4" class="collapse" data-bs-parent="#contactFaq">
                        <div class="faq-mini-answer">Yes! Guru and Business plan users get a free 30-minute onboarding call where we walk you through the platform and help set up your first project. We also have video tutorials and documentation.</div>
                    </div>
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
