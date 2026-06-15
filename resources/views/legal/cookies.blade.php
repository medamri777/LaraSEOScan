@extends('layouts.legal')

@section('title', 'Cookie Policy - Seo4ma')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="display-5 fw-bold mb-4">Cookie Policy</h1>
            <p class="text-muted mb-5">Last Updated: {{ date('F d, Y') }}</p>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <p class="lead mb-4">
                        Seo4ma uses cookies and similar technologies to help provide, protect, and improve the Seo4ma Service. This policy explains what cookies are, how we use them, and your choices regarding cookies.
                    </p>

                    <h2 class="h4 fw-bold mb-3">1. What Are Cookies?</h2>
                    <p class="text-muted mb-4">
                        Cookies are small text files that are stored on your computer or mobile device when you visit a website. They allow the website to recognize your device and remember if you have visited the site before. Cookies are widely used in order to make websites work, or work more efficiently, as well as to provide information to the owners of the site.
                    </p>

                    <h2 class="h4 fw-bold mb-3">2. Types of Cookies We Use</h2>
                    <p class="text-muted mb-4">
                        We use the following types of cookies:
                    </p>
                    <ul class="text-muted mb-4">
                        <li class="mb-2"><strong>Essential Cookies:</strong> These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility. You cannot opt-out of these cookies.</li>
                        <li class="mb-2"><strong>Analytics Cookies:</strong> These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously. This helps us improve our Service.</li>
                        <li class="mb-2"><strong>Authentication Cookies:</strong> These cookies are used to identify you when you log in to your account.</li>
                        <li class="mb-2"><strong>Performance Cookies:</strong> These cookies collect information about how you use our website, for instance which pages you go to most often.</li>
                    </ul>

                    <h2 class="h4 fw-bold mb-3">3. Managing Cookies</h2>
                    <p class="text-muted mb-4">
                        Most web browsers allow some control of most cookies through the browser settings. To find out more about cookies, including how to see what cookies have been set and how to manage and delete them, visit <a href="https://www.aboutcookies.org" target="_blank">www.aboutcookies.org</a> or <a href="https://www.allaboutcookies.org" target="_blank">www.allaboutcookies.org</a>.
                    </p>
                    <p class="text-muted mb-4">
                        Please note that if you choose to disable cookies, some parts of our Service may not function properly.
                    </p>
                    
                    <h2 class="h4 fw-bold mb-3">4. Changes to This Cookie Policy</h2>
                    <p class="text-muted mb-4">
                        We may update this Cookie Policy from time to time. We encourage you to review this page periodically for any changes.
                    </p>

                    <h2 class="h4 fw-bold mb-0">5. Contact Us</h2>
                    <p class="text-muted mb-0">
                        If you have any questions about our use of cookies, please email us at <a href="mailto:privacy@seo4ma.com">privacy@seo4ma.com</a>.
                    </p>
                </div>
            </div>

            <div class="mt-4 text-center">
                 <a href="{{ route('legal.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Back to Legal Overview</a>
            </div>
        </div>
    </div>
</div>
@endsection
