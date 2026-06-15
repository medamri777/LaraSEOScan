@extends('layouts.app')

@section('title', 'Google Search Console - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-google me-2" style="color:#4285F4"></i>Google Search Console</h2>
        <p class="text-muted">View real search performance data directly from Google — clicks, impressions, CTR, position, and indexing status.</p>
    </div>

    @if($error)
    <div class="alert alert-danger rounded-4"><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</div>
    @endif

    {{-- Project selector + Connect --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Select Project</label>
                    <select id="projectSelect" class="form-select border-0 shadow-sm rounded-3">
                        <option value="">-- Choose a project --</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $selectedProject && $selectedProject->id == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->url }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Period</label>
                    <select id="daysSelect" class="form-select border-0 shadow-sm rounded-3">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="28" {{ $days == 28 ? 'selected' : '' }}>Last 28 days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button id="loadDataBtn" class="btn btn-primary rounded-3 py-2 fw-semibold flex-fill" style="background: linear-gradient(135deg, #4285F4 0%, #34A853 100%); border:none;" {{ !$connection ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-clockwise me-1"></i> Load Data
                    </button>
                    @if(!$connection)
                    <button id="connectBtn" class="btn btn-outline-dark rounded-3 py-2 fw-semibold flex-fill">
                        <i class="bi bi-link-45deg me-1"></i> Connect GSC
                    </button>
                    @else
                    <button id="disconnectBtn" class="btn btn-outline-danger rounded-3 py-2 fw-semibold">
                        <i class="bi bi-x-circle"></i>
                    </button>
                    @endif
                </div>
            </div>
            @if($connection)
            <div class="mt-3">
                <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Connected</span>
                <span class="text-muted small ms-2">{{ $connection->property_url }}</span>
                @if($connection->last_sync_at)
                <span class="text-muted small ms-2">| Last sync: {{ $connection->last_sync_at->diffForHumans() }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($connection && $performanceData)
    {{-- Summary Cards --}}
    @php
        $totalClicks = 0; $totalImpressions = 0; $positionSum = 0; $dayCount = count($performanceData['rows'] ?? []);
        foreach($performanceData['rows'] ?? [] as $r) { $totalClicks += $r['clicks']; $totalImpressions += $r['impressions']; $positionSum += $r['position']; }
        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $avgPos = $dayCount > 0 ? round($positionSum / $dayCount, 1) : 0;
    @endphp
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-hand-index-thumb fs-2 text-primary mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Total Clicks</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($totalClicks) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-eye fs-2 text-info mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Total Impressions</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($totalImpressions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-percent fs-2 text-success mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Average CTR</h6>
                    <h3 class="fw-bold mb-0">{{ $avgCtr }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-graph-up fs-2 text-warning mb-2"></i>
                    <h6 class="text-muted small fw-semibold">Avg. Position</h6>
                    <h3 class="fw-bold mb-0">{{ $avgPos }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Performance Chart --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Daily Performance</h6>
        </div>
        <div class="card-body p-4">
            <canvas id="performanceChart" height="80"></canvas>
        </div>
    </div>

    {{-- Top Queries + Top Pages --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-search me-2 text-primary"></i>Top Search Queries</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr><th>Query</th><th>Clicks</th><th>Impr.</th><th>CTR</th><th>Pos.</th></tr>
                            </thead>
                            <tbody>
                                @foreach($topQueries['rows'] ?? [] as $row)
                                <tr>
                                    <td class="fw-semibold small">{{ $row['keys'][0] ?? '' }}</td>
                                    <td>{{ number_format($row['clicks']) }}</td>
                                    <td>{{ number_format($row['impressions']) }}</td>
                                    <td>{{ $row['ctr'] }}%</td>
                                    <td><span class="badge bg-{{ $row['position'] <= 10 ? 'success' : ($row['position'] <= 20 ? 'warning' : 'secondary') }}">{{ $row['position'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-success"></i>Top Pages</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr><th>Page</th><th>Clicks</th><th>Impr.</th><th>CTR</th><th>Pos.</th></tr>
                            </thead>
                            <tbody>
                                @foreach($topPages['rows'] ?? [] as $row)
                                <tr>
                                    <td class="small" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $row['keys'][0] ?? '' }}">{{ $row['keys'][0] ?? '' }}</td>
                                    <td>{{ number_format($row['clicks']) }}</td>
                                    <td>{{ number_format($row['impressions']) }}</td>
                                    <td>{{ $row['ctr'] }}%</td>
                                    <td><span class="badge bg-{{ $row['position'] <= 10 ? 'success' : ($row['position'] <= 20 ? 'warning' : 'secondary') }}">{{ $row['position'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- URL Inspector --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-search me-2 text-warning"></i>URL Inspector</h6>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tools.search-console') }}" class="row g-3">
                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                <input type="hidden" name="days" value="{{ $days }}">
                <div class="col-md-9">
                    <input type="url" name="inspect_url" class="form-control border-0 shadow-sm rounded-3" placeholder="Enter a URL from {{ $connection->property_url }} to inspect..." value="{{ request('inspect_url', '') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning w-100 rounded-3 py-2 fw-semibold text-white">
                        <i class="bi bi-search me-1"></i> Inspect URL
                    </button>
                </div>
            </form>
            @if($inspectionResult)
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Verdict</small>
                        <strong class="text-{{ ($inspectionResult['verdict'] ?? '') === 'PASS' ? 'success' : (($inspectionResult['verdict'] ?? '') === 'FAIL' ? 'danger' : 'warning') }}">
                            {{ $inspectionResult['verdict'] ?? 'Unknown' }}
                        </strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Coverage</small>
                        <strong>{{ $inspectionResult['coverage_state'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Indexing</small>
                        <strong>{{ $inspectionResult['indexing_state'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Page Fetch</small>
                        <strong>{{ $inspectionResult['page_fetch'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Robots.txt</small>
                        <strong>{{ $inspectionResult['robots_txt'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Last Crawl</small>
                        <strong>{{ $inspectionResult['last_crawl'] ? \Carbon\Carbon::parse($inspectionResult['last_crawl'])->diffForHumans() : 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Rich Results</small>
                        <strong>{{ $inspectionResult['rich_results'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                @if(!empty($inspectionResult['google_canonical']))
                <div class="col-12">
                    <div class="p-3 rounded-3 bg-light">
                        <small class="text-muted d-block">Google Canonical</small>
                        <strong class="small">{{ $inspectionResult['google_canonical'] }}</strong>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Sitemap Manager --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-info"></i>Sitemap Manager</h6>
            <form method="GET" action="{{ route('tools.search-console') }}" class="d-inline">
                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                <input type="hidden" name="days" value="{{ $days }}">
                <input type="hidden" name="show_sitemaps" value="1">
                <button type="submit" class="btn btn-sm btn-outline-info rounded-3">Load Sitemaps</button>
            </form>
        </div>
        <div class="card-body p-4">
            <form id="submitSitemapForm" class="row g-3 mb-3">
                <div class="col-md-9">
                    <input type="url" id="sitemapUrlInput" class="form-control border-0 shadow-sm rounded-3" placeholder="Sitemap URL to submit (e.g. https://example.com/sitemap.xml)">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info w-100 rounded-3 py-2 fw-semibold text-white">
                        <i class="bi bi-cloud-upload me-1"></i> Submit Sitemap
                    </button>
                </div>
            </form>
            @if($sitemaps)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light"><tr><th>Sitemap Path</th><th>Last Submitted</th><th>Pending</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($sitemaps as $sm)
                        <tr>
                            <td class="small">{{ $sm['path'] }}</td>
                            <td class="small">{{ $sm['last_submitted'] ?? 'N/A' }}</td>
                            <td>{{ $sm['is_pending'] ? 'Yes' : 'No' }}</td>
                            <td><span class="badge bg-{{ $sm['is_sitemapped'] ? 'success' : 'secondary' }}">{{ $sm['is_sitemapped'] ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    @elseif($connection)
    <div class="text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <p class="text-muted">Connected but no data yet. Click "Load Data" to fetch your Search Console data.</p>
    </div>
    @else
    <div class="text-center py-5">
        <i class="bi bi-google display-1 text-muted mb-3 d-block"></i>
        <h4 class="fw-bold">Connect Google Search Console</h4>
        <p class="text-muted">Link your Google Search Console to see real search performance data — clicks, impressions, CTR, average position, indexing status, and more.</p>
        <p class="text-muted small">Select a project above and click "Connect GSC" to get started.</p>
    </div>
    @endif
</div>

{{-- OAuth popup modal --}}
<div class="modal fade" id="propertiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header"><h5 class="modal-title">Select a Property</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="propertiesList"><div class="text-center py-3"><div class="spinner-border"></div></div></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const projectId = '{{ $selectedProject->id ?? "" }}';

// Connect flow
document.getElementById('connectBtn')?.addEventListener('click', async () => {
    if (!projectId) return alert('Select a project first.');
    try {
        const res = await fetch('/tools/search-console/connect', {
            method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({project_id: projectId})
        });
        const data = await res.json();
        if (data.auth_url) {
            const popup = window.open(data.auth_url, 'gsc_auth', 'width=600,height=700');
            
            const messageHandler = async (event) => {
                if (event.data && event.data.type === 'gsc_auth_callback') {
                    window.removeEventListener('message', messageHandler);
                    if (interval) clearInterval(interval);
                    if (!popup.closed) popup.close();
                    
                    const { code, state } = event.data;
                    if (code && state) {
                        await handleOAuthCallback(code, state);
                    }
                }
            };
            window.addEventListener('message', messageHandler);

            const interval = setInterval(async () => {
                if (popup.closed) {
                    clearInterval(interval);
                    window.removeEventListener('message', messageHandler);
                    return;
                }
                try {
                    const url = new URL(popup.location.href);
                    const code = url.searchParams.get('code');
                    const state = url.searchParams.get('state');
                    if (code && state) {
                        clearInterval(interval);
                        window.removeEventListener('message', messageHandler);
                        popup.close();
                        await handleOAuthCallback(code, state);
                    }
                } catch(e) { /* cross-origin, keep waiting */ }
            }, 1000);
        }
    } catch(e) { alert('Error: ' + e.message); }
});

async function handleOAuthCallback(code, state) {
    try {
        const res = await fetch('/tools/search-console/callback', {
            method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({code, state})
        });
        const data = await res.json();
        if (data.error) return alert(data.error);
        showPropertySelector(data.properties, data.token_data, data.project_id);
    } catch(e) { alert('Callback error: ' + e.message); }
}

function showPropertySelector(properties, tokenData, pid) {
    const list = document.getElementById('propertiesList');
    if (!properties || properties.length === 0) {
        list.innerHTML = '<p class="text-muted">No Search Console properties found. Add your site to <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> first.</p>';
    } else {
        let html = '<div class="list-group">';
        properties.forEach(p => {
            html += `<button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="selectProperty('${p.url}', '${tokenData.access_token}', '${tokenData.refresh_token}', ${tokenData.expires_in}, ${pid})">
                <span class="small">${p.url}</span><span class="badge bg-${p.permission === 'siteFullUser' || p.permission === 'siteOwner' ? 'success' : 'secondary'}">${p.permission}</span>
            </button>`;
        });
        html += '</div>';
        list.innerHTML = html;
    }
    new bootstrap.Modal(document.getElementById('propertiesModal')).show();
}

window.selectProperty = async (propertyUrl, accessToken, refreshToken, expiresIn, pid) => {
    try {
        const res = await fetch('/tools/search-console/store', {
            method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({project_id: pid, property_url: propertyUrl, access_token: accessToken, refresh_token: refreshToken, expires_in: expiresIn})
        });
        const data = await res.json();
        if (data.error) return alert(data.error);
        bootstrap.Modal.getInstance(document.getElementById('propertiesModal')).hide();
        location.reload();
    } catch(e) { alert('Error: ' + e.message); }
};

// Disconnect
document.getElementById('disconnectBtn')?.addEventListener('click', async () => {
    if (!confirm('Disconnect Search Console?')) return;
    await fetch('/tools/search-console/disconnect', {
        method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        body: JSON.stringify({project_id: projectId})
    });
    location.reload();
});

// Load Data
document.getElementById('loadDataBtn')?.addEventListener('click', () => {
    const days = document.getElementById('daysSelect').value;
    window.location.href = `{{ route('tools.search-console') }}?project_id=${projectId}&days=${days}`;
});

// Project select change
document.getElementById('projectSelect')?.addEventListener('change', (e) => {
    window.location.href = `{{ route('tools.search-console') }}?project_id=${e.target.value}`;
});

// Submit sitemap
document.getElementById('submitSitemapForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('sitemapUrlInput').value;
    if (!url) return;
    try {
        const res = await fetch('/tools/search-console/submit-sitemap', {
            method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({project_id: projectId, sitemap_url: url})
        });
        const data = await res.json();
        alert(data.message || data.error);
    } catch(e) { alert('Error: ' + e.message); }
});

// Performance chart
@if($connection && $performanceData && !empty($performanceData['rows']))
const perfData = @json($performanceData['rows']);
const ctx = document.getElementById('performanceChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: perfData.map(r => r.keys[0] || ''),
            datasets: [
                {label: 'Clicks', data: perfData.map(r => r.clicks), borderColor: '#4285F4', backgroundColor: 'rgba(66,133,244,0.1)', fill: true, yAxisID: 'y', tension: 0.3},
                {label: 'Impressions', data: perfData.map(r => r.impressions), borderColor: '#34A853', backgroundColor: 'rgba(52,168,83,0.1)', fill: true, yAxisID: 'y1', tension: 0.3},
            ]
        },
        options: {
            responsive: true,
            interaction: {mode: 'index', intersect: false},
            scales: {
                y: {type: 'linear', position: 'left', title: {display: true, text: 'Clicks'}},
                y1: {type: 'linear', position: 'right', title: {display: true, text: 'Impressions'}, grid: {drawOnChartArea: false}},
            }
        }
    });
}
@endif
</script>
@endpush
@endsection
