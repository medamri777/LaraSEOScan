@props(['title', 'value', 'icon', 'color' => 'primary', 'trend' => null])

<div class="metric-card">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <div class="metric-label">{{ $title }}</div>
            <div class="metric-value">{{ $value }}</div>
            @if($trend)
                <small class="d-block mt-1" style="font-size: 0.75rem; color: {{ $trend > 0 ? '#10b981' : '#ef4444' }};">
                    <i class="bi bi-arrow-{{ $trend > 0 ? 'up' : 'down' }}"></i> {{ abs($trend) }}%
                </small>
            @endif
        </div>
        <div class="metric-icon" style="color: #10b981;">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
</div>
