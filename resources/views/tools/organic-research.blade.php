@extends('layouts.app')

@section('title', 'Organic Research - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Organic Research</h2>
        <p class="text-muted">Discover all organic keywords a domain ranks for. Analyze competitor traffic, top pages, and position distribution.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tools.organic-research') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Domain</label>
                    <input type="text" name="domain" class="form-control border-0 shadow-sm rounded-3" style="background: #f3f4f6; color: #6b7280;" value="{{ $currentProject ? (parse_url($currentProject->url, PHP_URL_HOST) ?? $currentProject->name) : '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Location</label>
                    <select name="location" class="form-select border-0 shadow-sm rounded-3">
                        @foreach($locations as $code => $label)
                            <option value="{{ $code }}" {{ $location == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Language</label>
                    <select name="language" class="form-select border-0 shadow-sm rounded-3">
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" {{ $language == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-search me-1"></i> Analyze
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
                    <i class="bi bi-key fs-2 text-primary mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Total Keywords</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($data['total_keywords']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-people fs-2 text-success mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Estimated Traffic</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($data['estimated_traffic']) }}</h2>
                    <small class="text-muted">monthly visits</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-file-earmark-text fs-2 text-warning mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Top Pages</h6>
                    <h2 class="fw-bold mb-0">{{ count($data['top_pages']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold">Domain</h6>
                    <h5 class="fw-bold mb-0 text-truncate">{{ $data['domain'] }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Position Distribution</h6>
                </div>
                <div class="card-body p-4">
                    @foreach($data['position_distribution'] as $range => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-semibold">Position {{ $range }}</small>
                            <small class="text-muted">{{ $count }} keywords</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $range === '1-3' ? 'bg-success' : ($range === '4-10' ? 'bg-info' : ($range === '11-20' ? 'bg-warning' : 'bg-secondary')) }}" style="width: {{ $data['total_keywords'] > 0 ? ($count / $data['total_keywords'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Pages</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>URL</th>
                                    <th>Keywords</th>
                                    <th>Traffic</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['top_pages'] as $page)
                                <tr>
                                    <td class="fw-semibold text-truncate" style="max-width: 300px;">{{ $page['url'] }}</td>
                                    <td>{{ $page['keywords'] }}</td>
                                    <td>{{ number_format($page['traffic']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Top Keywords</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Keyword</th>
                            <th>Position</th>
                            <th>URL</th>
                            <th>Volume</th>
                            <th>CPC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($data['keywords'], 0, 50) as $kw)
                        <tr>
                            <td class="fw-semibold">{{ $kw['keyword'] }}</td>
                            <td><span class="badge bg-{{ $kw['position'] <= 3 ? 'success' : ($kw['position'] <= 10 ? 'info' : ($kw['position'] <= 20 ? 'warning' : 'secondary')) }}">{{ $kw['position'] }}</span></td>
                            <td class="text-truncate" style="max-width: 250px;">{{ $kw['url'] }}</td>
                            <td>{{ number_format($kw['search_volume']) }}</td>
                            <td>${{ $kw['cpc'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
