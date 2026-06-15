@extends('layouts.app')

@section('title', 'Website Authority Checker - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-shield-check me-2 text-primary"></i>Website Authority Checker</h2>
        <p class="text-muted">Check the domain authority score, backlink profile, and organic traffic estimates for any website.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tools.authority-checker') }}" class="row g-3" id="authorityForm">
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Domain</label>
                    <input type="text" name="domain" class="form-control border-0 shadow-sm rounded-3" style="background: #f3f4f6; color: #6b7280;" value="{{ $currentProject ? (parse_url($currentProject->url, PHP_URL_HOST) ?? $currentProject->name) : '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" id="submitBtn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-search me-1"></i> Check Authority
                    </button>
                </div>
            </form>
            <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted small mt-2">Fetching real data from multiple sources... This may take 10-15 seconds.</p>
            </div>
        </div>
    </div>

    @if($data)
    @if(isset($data['error']) && $data['error'])
    <div class="alert alert-warning rounded-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Notice:</strong> {{ $data['error'] }}. Showing estimated data.
    </div>
    @endif

    <div class="alert alert-info rounded-4 small">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Data Source:</strong> Real-time data from DataForSEO API (backlinks, organic keywords, referring domains).
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold mb-3">Authority Score</h6>
                    <div class="position-relative d-inline-block">
                        <svg width="160" height="160" viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="70" stroke="#e5e7eb" stroke-width="12" fill="none"/>
                            <circle cx="80" cy="80" r="70" stroke="{{ $data['color'] === 'success' ? '#10b981' : ($data['color'] === 'warning' ? '#f59e0b' : ($data['color'] === 'info' ? '#3b82f6' : '#ef4444')) }}" stroke-width="12" fill="none" stroke-dasharray="{{ 2 * pi() * 70 }}" stroke-dashoffset="{{ 2 * pi() * 70 * (1 - $data['authority_score'] / 100) }}" stroke-linecap="round" transform="rotate(-90 80 80)"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <span class="fs-2 fw-bold">{{ $data['authority_score'] }}</span>
                            <br><small class="text-muted">/100</small>
                        </div>
                    </div>
                    <h5 class="mt-3 fw-bold text-{{ $data['color'] }}">{{ $data['label'] }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-link-45deg fs-2 text-primary mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Backlinks</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['backlinks']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-globe fs-2 text-success mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Referring Domains</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['referring_domains']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-bar-chart fs-2 text-warning mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Organic Keywords</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['organic_keywords']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-graph-up-arrow fs-2 text-info mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Organic Traffic</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['organic_traffic']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Organic Keywords</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Keyword</th>
                                    <th>Position</th>
                                    <th>Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_keywords'] as $kw)
                                <tr>
                                    <td class="fw-semibold">{{ $kw['keyword'] }}</td>
                                    <td><span class="badge bg-{{ $kw['position'] <= 10 ? 'success' : ($kw['position'] <= 30 ? 'warning' : 'secondary') }}">{{ $kw['position'] }}</span></td>
                                    <td>{{ number_format($kw['volume']) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No keyword data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Top Backlinks</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Referring Domain</th>
                                    <th>Backlinks</th>
                                    <th>Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_backlinks'] as $bl)
                                <tr>
                                    <td class="fw-semibold">{{ $bl['domain'] }}</td>
                                    <td>{{ number_format($bl['backlinks']) }}</td>
                                    <td>{{ number_format($bl['prev_rank']) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No backlink data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('authorityForm').addEventListener('submit', function() {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking...';
});
</script>
@endpush
@endsection
