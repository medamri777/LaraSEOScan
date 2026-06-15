@extends('layouts.app')

@section('title', 'Crawl Audit - Premium SEO Spider - Seo4ma')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4 py-3 py-md-4" style="max-width: 1200px; margin: 0 auto;">
    <div x-data="crawlApp()" x-init="init()">

        <!-- Top Header Title -->
        <div class="mb-4">
            <h2 class="text-gray-700 fw-bold d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-diagram-3 text-success"></i>
                <span>Technical SEO Spider</span>
            </h2>
            <p class="text-gray-500 m-0">Audit your website structure, find broken links, index structured markup, and diagnose crawl errors with deep spider scan diagnostics.</p>
        </div>

        <!-- Top Header Controls Bar -->
        <div class="ca-controls-bar">
            <div class="ca-controls-inner">
                <div class="ca-url-input-wrap">
                    <i class="bi bi-globe" style="color: #6b7280; flex-shrink: 0;"></i>
                    <input type="url" x-model="url" @keydown.enter.prevent="startCrawl()"
                        class="form-control border-0 shadow-none"
                        style="background: #f3f4f6; color: #6b7280; font-size: 0.875rem; padding: 0.6rem 0.5rem; min-width: 0;"
                        readonly
                        placeholder="{{ $currentProject ? $currentProject->url : 'Create a project first' }}">
                </div>
                <div class="ca-btn-group">
                    <button @click="showConfig = !showConfig" class="btn ca-btn-outline d-flex align-items-center gap-1">
                        <i class="bi bi-sliders"></i> <span class="d-none d-sm-inline">Config</span>
                    </button>
                    <button @click="startCrawl()" :disabled="busy" class="btn ca-btn-primary d-flex align-items-center gap-1">
                        <i class="bi" :class="busy ? 'bi-hourglass-split animate-spin' : 'bi-play-fill'"></i>
                        <span x-text="busy ? 'Crawling...' : 'Start Crawl'"></span>
                    </button>
                </div>
            </div>

            <!-- Configuration Drawer -->
            <div x-show="showConfig" x-transition x-cloak style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <label class="ca-label">Max Pages</label>
                        <input type="number" x-model.number="cfg.maxPages" min="10" max="5000" class="form-control mt-1 ca-input">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="ca-label">Concurrency</label>
                        <input type="number" x-model.number="cfg.concurrency" min="1" max="20" class="form-control mt-1 ca-input">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="ca-label">Max Depth</label>
                        <input type="number" x-model.number="cfg.maxDepth" min="1" max="20" class="form-control mt-1 ca-input">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="ca-label">Timeout (s)</label>
                        <input type="number" x-model.number="cfg.timeout" min="30" max="600" class="form-control mt-1 ca-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- Crawling Progress Indicator -->
        <div x-show="busy" x-transition x-cloak style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold text-gray-800 d-flex align-items-center gap-2" style="font-size: 0.875rem;">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                    <span>Crawling Site... <span style="color: #6b7280; font-weight: 500;" x-text="'(' + prog.found + ' URLs discovered)'"></span></span>
                </span>
                <span class="fw-bold" style="color: #10b981; font-size: 0.875rem;" x-text="prog.pct + '%'"></span>
            </div>
            <div style="background: #f3f4f6; border-radius: 8px; height: 8px; overflow: hidden;">
                <div :style="'width: ' + prog.pct + '%'" style="background: linear-gradient(90deg, #10b981, #059669); height: 100%; border-radius: 8px; transition: width 0.4s;"></div>
            </div>
            <div x-show="prog.url" class="mt-2 text-truncate font-monospace" style="font-size: 0.75rem; color: #6b7280;" x-text="'Auditing: ' + prog.url"></div>
        </div>

        <!-- Error banner -->
        <div x-show="error" x-transition x-cloak class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="background: rgba(233,25,22,0.1); border: 1px solid rgba(233,25,22,0.3); color: #ef4444; font-size: 0.875rem; border-radius: 10px; padding: 1rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="fw-semibold" x-text="error"></span>
        </div>

        <!-- Recent Crawls Table Section -->
        <div class="card-dashboard mb-4">
            <div class="text-gray-700 fw-bold d-flex justify-content-between align-items-center">
                <span>Recent Crawl Audits</span>
                <i class="bi bi-clock-history text-success"></i>
            </div>
            <div class="table-responsive">
                <table class="table table-dashboard">
                    <thead>
                        <tr>
                            <th>Website Target</th>
                            <th>Status</th>
                            <th>Pages Audited</th>
                            <th>Elapsed</th>
                            <th>Date Scanned</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentScans as $scan)
                            <tr>
                                <td>
                                    <div class="text-gray-800 fw-bold text-truncate" style="max-width: 280px;" title="{{ $scan->url }}">
                                        {{ $scan->url }}
                                    </div>
                                </td>
                                <td>
                                    @if($scan->status === 'COMPLETED')
                                        <span class="badge bg-success text-black fw-bold">COMPLETED</span>
                                    @elseif($scan->status === 'FAILED')
                                        <span class="badge bg-danger text-white fw-bold">FAILED</span>
                                    @else
                                        <span class="badge bg-warning text-black fw-bold">IN PROGRESS</span>
                                    @endif
                                </td>
                                <td class="text-gray-800 font-monospace">
                                    {{ $scan->total_urls_found ?? 0 }} page(s)
                                </td>
                                <td class="text-gray-800 font-monospace">
                                    {{ $scan->time_elapsed ? round($scan->time_elapsed, 1) . 's' : '—' }}
                                </td>
                                <td class="text-gray-500">
                                    {{ $scan->created_at->diffForHumans() }}
                                </td>
                                <td class="text-end">
                                    @if($scan->status === 'COMPLETED')
                                        <a href="{{ route('tools.crawl-audit.results', ['taskId' => $scan->uuid]) }}" class="btn btn-sm btn-success fw-bold text-black px-3">
                                            <i class="bi bi-eye"></i> View Audit
                                        </a>
                                    @else
                                        <button disabled class="btn btn-sm btn-secondary opacity-50 px-3">
                                            Unavailable
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-4">
                                    <i class="bi bi-diagram-3 text-gray-400 display-6 d-block mb-3"></i>
                                    <span>No recent crawls found. Enter a URL above and click <strong>Start Crawl</strong> to begin!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f3f4f6; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Custom high-contrast overrides */
    .text-gray-400 {
        color: #6b7280 !important;
        font-weight: 500;
    }
    .border-subtle-custom {
        border: 1px solid #d1d5db !important;
    }
    .bg-custom-dark {
        background-color: #f3f4f6 !important;
    }

    /* ===== Controls Bar ===== */
    .ca-controls-bar {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .ca-controls-inner {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .ca-url-input-wrap {
        flex: 1 1 280px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.25rem 0.75rem;
        min-width: 0;
    }
    .ca-btn-group {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        flex-wrap: wrap;
    }
    .ca-btn-outline {
        background: transparent;
        border: 1px solid #e5e7eb;
        color: #111827;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.55rem 0.875rem;
        transition: all 0.15s;
    }
    .ca-btn-outline:hover { border-color: #10b981; color: #10b981; }
    .ca-btn-primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        border: none;
        font-size: 0.8125rem;
        font-weight: 700;
        border-radius: 8px;
        padding: 0.55rem 1.25rem;
        transition: filter 0.15s;
    }
    .ca-btn-primary:hover { filter: brightness(1.1); color: #ffffff; }
    .ca-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
    .ca-label {
        font-size: 0.75rem;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
    }
    .ca-input {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        font-size: 0.8125rem;
        border-radius: 6px;
    }
    .ca-input:focus {
        background: #f3f4f6;
        border-color: #10b981;
        box-shadow: 0 0 0 2px rgba(83,252,24,0.15);
        color: #fff;
    }

    /* ===== Cards & Containers ===== */
    .card-dashboard {
        background: #f9fafb;
        border: 1px solid #d1d5db !important;
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }
    .card-header {
        background: #ffffff;
        border-bottom: 1px solid #d1d5db !important;
        padding: 0.875rem 1.25rem;
    }
    .card-body {
        padding: 1.25rem;
    }

    /* ===== High Contrast Table Design ===== */
    .table-dashboard {
        margin: 0;
        width: 100%;
        color: #111827 !important;
        background-color: transparent;
        border-collapse: collapse;
    }
    .table-dashboard th {
        background-color: #ffffff !important;
        color: #111827 !important;
        font-weight: 700;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #d1d5db !important;
        padding: 0.875rem 1rem;
        vertical-align: middle;
    }
    .table-dashboard td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb !important;
        color: #374151 !important;
        font-size: 0.875rem;
    }
    .table-dashboard tbody tr:hover {
        background-color: #ffffff !important;
    }
</style>

<script>
function crawlApp() {
    return {
        url: '{{ $currentProject?->url ?? '' }}',
        busy: false,
        error: null,
        prog: { pct: 0, found: 0, url: '' },
        showConfig: false,
        cfg: { maxPages: 100, concurrency: 5, maxDepth: 4, timeout: 300 },
        taskId: null,
        timer: null,

        init() {
            // URL is locked to the active project — no session override
        },

        startCrawl() {
            if (!this.url || this.busy) return;
            this.busy = true; this.error = null;
            this.prog = { pct: 0, found: 0, url: '' }; this.taskId = null;

            fetch('{{ route("tools.crawl-audit.start") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ url: this.url.trim(), max_pages: this.cfg.maxPages }),
            })
            .then(r => r.text().then(text => ({ ok: r.ok, status: r.status, text })))
            .then(({ ok, status, text }) => {
                let d;
                try { d = JSON.parse(text); } catch (e) {
                    this.error = 'Crawl start returned non-JSON (status ' + status + ').';
                    this.busy = false; return;
                }
                if (!ok) {
                    this.error = d.error || 'Server error starting crawl.';
                    this.busy = false; return;
                }
                if (d.error) {
                    this.error = d.error;
                    this.busy = false; return;
                }
                this.taskId = d.task_id;
                this.poll();
            })
            .catch(e => { this.error = 'Crawl start failed: ' + e.message; this.busy = false; });
        },

        poll() {
            this.timer = setInterval(() => {
                fetch('{{ url("tools/crawl-audit/status") }}/' + this.taskId)
                .then(r => r.json())
                .then(d => {
                    if (d.status === 'completed' || d.scan_id) {
                        clearInterval(this.timer);
                        window.location.href = '{{ url("tools/crawl-audit/results") }}/' + this.taskId;
                    } else if (d.status === 'error') {
                        this.error = d.error || d.current_url || 'Crawl job failed.';
                        this.busy = false; clearInterval(this.timer);
                    } else {
                        this.prog = { pct: d.progress || 0, found: d.found || 0, url: d.current_url || '' };
                    }
                }).catch(() => {});
            }, 1500);
        }
    };
}
</script>
@endsection
