@extends('layouts.app')

@section('title', 'Backlink Checker - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-link-45deg me-2 text-primary"></i>Backlink Checker</h2>
        <p class="text-muted">Analyze the backlink profile of any domain. Discover referring domains, anchor text distribution, and authority metrics.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tools.backlink-checker') }}" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Domain</label>
                    <input type="text" name="domain" class="form-control border-0 shadow-sm rounded-3" style="background: #f3f4f6; color: #6b7280;" value="{{ $currentProject ? (parse_url($currentProject->url, PHP_URL_HOST) ?? $currentProject->name) : '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-search me-1"></i> Analyze Backlinks
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($data)
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold mb-2">Authority Score</h6>
                    <div class="position-relative d-inline-block">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                            <circle cx="60" cy="60" r="50" stroke="{{ $data['authority_score'] > 60 ? '#10b981' : ($data['authority_score'] > 30 ? '#f59e0b' : '#ef4444') }}" stroke-width="8" fill="none" stroke-dasharray="{{ 2 * pi() * 50 }}" stroke-dashoffset="{{ 2 * pi() * 50 * (1 - $data['authority_score'] / 100) }}" stroke-linecap="round" transform="rotate(-90 60 60)"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <span class="fs-4 fw-bold">{{ $data['authority_score'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-link-45deg fs-2 text-primary mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Total Backlinks</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['backlinks']) }}</h3>
                    <small class="text-muted">{{ number_format($data['backlinks_nofollow']) }} nofollow</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-globe fs-2 text-success mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Referring Domains</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['referring_domains']) }}</h3>
                    <small class="text-muted">{{ number_format($data['referring_ips']) }} IPs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-file-earmark-text fs-2 text-warning mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Referring Pages</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($data['referring_pages']) }}</h3>
                    <small class="text-muted">{{ $data['referring_tlds'] }} TLDs</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Referring Domains</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Domain</th>
                                    <th>Backlinks</th>
                                    <th>First Seen</th>
                                    <th>Domain Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['top_backlinks'] as $backlink)
                                <tr>
                                    <td class="fw-semibold">{{ $backlink['domain'] }}</td>
                                    <td>{{ number_format($backlink['backlinks']) }}</td>
                                    <td>{{ $backlink['first_seen'] }}</td>
                                    <td><span class="badge bg-light text-dark">{{ number_format($backlink['prev_rank']) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Anchor Distribution</h6>
                </div>
                <div class="card-body p-4">
                    @foreach($data['anchor_distribution'] as $anchor)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-semibold">{{ $anchor['anchor'] }}</small>
                            <small class="text-muted">{{ $anchor['percentage'] }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $anchor['percentage'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
