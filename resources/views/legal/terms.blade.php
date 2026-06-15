@extends('layouts.legal')

@section('title', 'Terms of Service - Seo4ma')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="display-5 fw-bold mb-4">Terms of Service</h1>
            <p class="text-muted mb-5">Last Updated: {{ date('F d, Y') }}</p>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <p class="lead mb-4">
                        Please read these Terms of Service ("Terms") carefully before using the Seo4ma platform ("Service") operated by Seo4ma ("us", "we", or "our").
                    </p>

                    <h2 class="h4 fw-bold mb-3">1. Acceptance of Terms</h2>
                    <p class="text-muted mb-4">
                        By accessing or using the Service, you agree to be bound by these Terms. If you disagree with any part of the terms, then you may not access the Service.
                    </p>

                    <h2 class="h4 fw-bold mb-3">2. Use of Service</h2>
                    <p class="text-muted mb-4">
                        The Service provides SEO crawling, analysis, and auditing tools for websites. You agree to use the Service only for lawful purposes in accordance with these Terms.
                    </p>

                    <h2 class="h4 fw-bold mb-3">3. User Responsibilities</h2>
                    <p class="text-muted mb-4">
                        You represent and warrant that (i) you have the legal right and authority to enter into these Terms; (ii) you own or have permission to scan the websites you submit for auditing; and (iii) your use of the Service will not disrupt or harm the Service.
                    </p>
                    <p class="text-muted mb-4">
                        You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
                    </p>

                    <h2 class="h4 fw-bold mb-3">4. Limitation of Liability</h2>
                    <p class="text-muted mb-4">
                        In no event shall Seo4ma, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content.
                    </p>

                    <h2 class="h4 fw-bold mb-3">5. Termination</h2>
                    <p class="text-muted mb-4">
                        We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms. Upon termination, your right to use the Service will immediately cease.
                    </p>

                    <h2 class="h4 fw-bold mb-3">6. Changes to Terms</h2>
                    <p class="text-muted mb-4">
                        We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.
                    </p>
                    
                    <h2 class="h4 fw-bold mb-3">7. Contact</h2>
                    <p class="text-muted mb-0">
                        If you have any questions about these Terms, please contact us at: <a href="mailto:terms@seo4ma.com">terms@seo4ma.com</a>.
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
