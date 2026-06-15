@extends('layouts.legal')

@section('title', 'Privacy Policy - Seo4ma')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="display-5 fw-bold mb-4">Privacy Policy</h1>
            <p class="text-muted mb-5">Last Updated: {{ date('F d, Y') }}</p>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <p class="lead mb-5">
                        At Seo4ma, we prioritize your privacy. This policy explains how we collect, use, and protect your data when you use our SEO crawling and auditing services ("Service").
                    </p>

                    <h2 class="h4 fw-bold mb-3">1. Information We Collect</h2>
                    <p class="text-muted mb-4">
                        We collect the following types of information:
                    </p>
                    <ul class="text-muted mb-4">
                        <li class="mb-2"><strong>Account Information:</strong> When you register, we collect your name and email address.</li>
                        <li class="mb-2"><strong>Website Scan Data:</strong> We store the URLs you submit for auditing, as well as the reports generated from our crawl. This includes SEO scores, detected issues, and performance metrics.</li>
                        <li class="mb-2"><strong>Usage Data:</strong> We may collect information on how the Service is accessed and used (e.g., page views, time spent).</li>
                        <li class="mb-2"><strong>Cookies:</strong> We use cookies to maintain your session and improve user experience.</li>
                    </ul>

                    <h2 class="h4 fw-bold mb-3">2. How We Use Your Information</h2>
                    <p class="text-muted mb-4">
                        Your data is used to:
                    </p>
                    <ul class="text-muted mb-4">
                        <li class="mb-2">Provide, operate, and maintain our Service.</li>
                        <li class="mb-2">Generate and store your SEO audit reports.</li>
                        <li class="mb-2">Notify you about changes to our Service.</li>
                        <li class="mb-2">Detect, prevent, and address technical issues.</li>
                    </ul>

                    <h2 class="h4 fw-bold mb-3">3. Data Protection</h2>
                    <p class="text-muted mb-4">
                        We implement security measures to protect your personal information. However, no method of transmission over the Internet or electronic storage is 100% secure. We strive to use commercially acceptable means to protect your data but cannot guarantee absolute security.
                    </p>

                    <h2 class="h4 fw-bold mb-3">4. Data Retention</h2>
                    <p class="text-muted mb-4">
                        We retain your personal data and scan history only for as long as is necessary for the purposes set out in this Privacy Policy. You can request deletion of your account and data at any time via your dashboard or by contacting support.
                    </p>

                    <h2 class="h4 fw-bold mb-3">5. Your Rights</h2>
                    <p class="text-muted mb-4">
                        You have the right to access, correct, or delete your personal data. You may also object to the processing of your data or request data portability.
                    </p>
                    
                    <h2 class="h4 fw-bold mb-3">6. Contact Us</h2>
                    <p class="text-muted mb-0">
                        If you have questions about this Privacy Policy, please contact us at: <a href="mailto:privacy@seo4ma.com">privacy@seo4ma.com</a>.
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
