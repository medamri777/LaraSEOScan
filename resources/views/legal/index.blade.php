@extends('layouts.legal')

@section('title', 'Legal Overview - Seo4ma')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="display-5 fw-bold mb-4 text-center">Legal Center</h1>
            <p class="lead text-muted text-center mb-5">
                Transparency and trust are at the core of Seo4ma. Below you will find our legal policies and agreements.
            </p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3 text-primary">
                                <i class="bi bi-shield-lock-fill fs-1"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Privacy Policy</h3>
                            <p class="text-muted small mb-4">
                                Learn how we collect, use, and protect your personal data when you use our services.
                            </p>
                            <a href="{{ route('legal.privacy') }}" class="btn btn-outline-primary rounded-pill stretched-link">View Policy</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3 text-primary">
                                <i class="bi bi-file-earmark-text-fill fs-1"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Terms of Service</h3>
                            <p class="text-muted small mb-4">
                                The rules and agreements that govern your use of the Seo4ma platform.
                            </p>
                            <a href="{{ route('legal.terms') }}" class="btn btn-outline-primary rounded-pill stretched-link">Read Terms</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3 text-primary">
                                <i class="bi bi-cookie fs-1"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Cookie Policy</h3>
                            <p class="text-muted small mb-4">
                                Understand what cookies we use to improve your experience and how to manage them.
                            </p>
                            <a href="{{ route('legal.cookies') }}" class="btn btn-outline-primary rounded-pill stretched-link">Cookie Info</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 p-4 bg-light rounded-3 text-center">
                <p class="mb-0 text-muted">
                    If you have any questions about these legal documents, please contact us at <a href="mailto:support@seo4ma.com" class="text-decoration-none">support@seo4ma.com</a>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
