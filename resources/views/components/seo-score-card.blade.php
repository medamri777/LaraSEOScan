@props(['score', 'label' => 'SEO Score'])

<div class="card-dashboard h-100">
    <div class="card-header">
        <h5 class="mb-0" style="font-size: 0.875rem; font-weight: 700; color: #111827;">{{ $label }}</h5>
    </div>
    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
        <div id="seoScoreChart" data-score="{{ $score }}" style="width: 200px; height: 200px;"></div>
        <div class="mt-3">
            @if($score >= 90)
                <span class="badge-severity" style="font-size: 0.75rem; padding: 0.375rem 0.875rem; background: rgba(16, 185, 129, 0.1); color: #10b981;">Excellent</span>
            @elseif($score >= 70)
                <span class="badge-severity" style="font-size: 0.75rem; padding: 0.375rem 0.875rem; background: #f3f4f6; color: #6b7280;">Good</span>
            @elseif($score >= 50)
                <span class="badge-severity" style="font-size: 0.75rem; padding: 0.375rem 0.875rem; background: #f3f4f6; color: #6b7280;">Fair</span>
            @else
                <span class="badge-severity" style="font-size: 0.75rem; padding: 0.375rem 0.875rem; background: rgba(239, 68, 68, 0.1); color: #ef4444;">Poor</span>
            @endif
        </div>
    </div>
</div>
