@extends('layouts.app')

@section('title', $project->name . ' - Dashboard')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 1.5rem;">

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #10b981; border-radius: 12px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Back to projects -->
    <a href="{{ route('projects.index') }}" style="display:inline-flex;align-items:center;color:#6b7280;font-size:0.875rem;text-decoration:none;margin-bottom:1.5rem;">
        <i class="bi bi-arrow-left me-2"></i> Back to projects
    </a>

    <!-- Project Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div class="d-flex align-items-center">
            <div style="width:48px;height:48px;border-radius:12px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                <i class="bi bi-globe" style="color:#10b981;font-size:1.25rem;"></i>
            </div>
            <div>
                <h2 style="font-weight:700;margin:0 0 0.25rem;color:#111827;font-size:1.35rem;">{{ $project->name }}</h2>
                <a href="{{ $project->url }}" target="_blank" style="display:flex;align-items:center;color:#6b7280;font-size:0.85rem;text-decoration:none;">
                    {{ $project->url }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:0.7rem;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Crawl Audit Card -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:3rem 1.5rem;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.04);margin-bottom:2rem;">
        <div style="width:80px;height:80px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
            <i class="bi bi-search" style="font-size:2rem;color:#3b82f6;"></i>
        </div>
        <h3 style="font-weight:700;color:#111827;margin-bottom:0.5rem;">Site Crawl Audit</h3>
        <p style="color:#6b7280;max-width:480px;margin:0 auto 2rem;font-size:0.9rem;">
            Crawl your entire website to find SEO issues, broken links, missing meta tags, slow pages, and more.
        </p>

        <!-- Start Crawl Form -->
        <div id="crawlFormWrapper" style="max-width:500px;margin:0 auto;">
            <div style="display:flex;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.06);margin-bottom:1rem;">
                <span style="display:flex;align-items:center;padding:0 0.75rem;background:#f9fafb;color:#6b7280;border-right:1px solid #e5e7eb;">
                    <i class="bi bi-link-45deg" style="font-size:1.25rem;"></i>
                </span>
                <input type="url" id="crawlUrl" value="{{ $project->url }}" readonly
                       style="flex:1;padding:0.75rem;border:none;background:#f3f4f6;color:#6b7280;font-size:0.95rem;outline:none;">
            </div>
            <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                <label for="maxPages" style="color:#6b7280;font-size:0.85rem;">Max pages:</label>
                <select id="maxPages" style="width:100px;padding:0.4rem 0.5rem;background:#f9fafb;border:1px solid #e5e7eb;color:#111827;border-radius:8px;font-size:0.85rem;">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
            </div>
            <button id="startCrawlBtn" class="btn-filament btn-filament-primary" style="padding:0.75rem 2rem;font-size:1rem;">
                <i class="bi bi-play-circle me-2"></i> Start Crawl Audit
            </button>
        </div>

        <!-- Progress Area (hidden by default) -->
        <div id="crawlProgress" class="d-none mt-4" style="max-width:500px;margin-left:auto;margin-right:auto;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="spinner-border" role="status" style="width:1.5rem;height:1.5rem;color:#10b981;">
                    <span class="visually-hidden">Crawling...</span>
                </div>
                <span id="progressText" style="color:#111827;font-weight:600;">Starting crawl...</span>
            </div>
            <div style="height:8px;border-radius:9999px;background:#f3f4f6;overflow:hidden;">
                <div id="progressBar" style="width:0%;height:100%;border-radius:9999px;background:linear-gradient(90deg,#3b82f6,#10b981);transition:width 0.3s;"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small id="pagesCrawled" style="color:#6b7280;font-size:0.8rem;">0 pages crawled</small>
                <small id="progressPercent" style="color:#6b7280;font-size:0.8rem;">0%</small>
            </div>
        </div>
    </div>

    <!-- Audit History -->
    @if(!$scans->isEmpty())
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div class="d-flex justify-content-between align-items-center" style="padding:1.25rem 1.5rem;border-bottom:1px solid #f3f4f6;">
                <h5 style="font-weight:700;margin:0;color:#111827;font-size:1.1rem;">
                    <i class="bi bi-clock-history me-2" style="color:#6b7280;"></i> Audit History
                </h5>
                <span style="display:inline-block;padding:0.2rem 0.6rem;background:#f3f4f6;color:#6b7280;border-radius:9999px;font-size:0.8rem;">{{ $stats['total'] }} audit(s)</span>
            </div>
            <table class="table mb-0 align-middle">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                        <th class="ps-4 py-3" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Date</th>
                        <th class="py-3 text-center" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Score</th>
                        <th class="py-3 text-center" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Pages</th>
                        <th class="py-3 text-center" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Status</th>
                        <th class="py-3 pe-4 text-end" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scans as $scan)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td class="ps-4 py-3">
                                <span style="color:#111827;font-size:0.9rem;">{{ $scan->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </td>
                            <td class="py-3 text-center">
                                @if($scan->status === 'COMPLETED' && $scan->score_total !== null)
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;font-weight:700;color:#fff;font-size:0.85rem;background:{{ $scan->score_total >= 80 ? '#10b981' : ($scan->score_total >= 50 ? '#f59e0b' : '#ef4444') }};">
                                        {{ $scan->score_total }}
                                    </span>
                                @else
                                    <span style="color:#6b7280;">-</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <span style="display:inline-block;padding:0.2rem 0.6rem;background:#f3f4f6;color:#6b7280;border-radius:9999px;font-size:0.8rem;">{{ $scan->pages()->count() }}</span>
                            </td>
                            <td class="py-3 text-center">
                                @if($scan->status === 'COMPLETED')
                                    <span style="display:inline-block;padding:0.2rem 0.6rem;background:#ecfdf5;color:#10b981;border-radius:9999px;font-size:0.75rem;font-weight:600;">Done</span>
                                @elseif($scan->status === 'FAILED')
                                    <span style="display:inline-block;padding:0.2rem 0.6rem;background:#fef2f2;color:#ef4444;border-radius:9999px;font-size:0.75rem;font-weight:600;">Failed</span>
                                @elseif($scan->status === 'RUNNING')
                                    <span style="display:inline-block;padding:0.2rem 0.6rem;background:#eff6ff;color:#3b82f6;border-radius:9999px;font-size:0.75rem;font-weight:600;">Running</span>
                                @else
                                    <span style="display:inline-block;padding:0.2rem 0.6rem;background:#f3f4f6;color:#6b7280;border-radius:9999px;font-size:0.75rem;font-weight:600;">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4 text-end">
                                @if($scan->status === 'COMPLETED')
                                    <a href="{{ route('tools.crawl-audit.results', ['taskId' => $scan->uuid]) }}" style="display:inline-block;padding:0.35rem 1rem;border:1px solid #93c5fd;color:#3b82f6;background:transparent;border-radius:8px;font-size:0.8rem;font-weight:600;text-decoration:none;"
                                       onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='transparent'">
                                        View <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                @else
                                    <span style="color:#6b7280;font-size:0.8rem;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($scans->hasPages())
            <div class="mt-4">
                {{ $scans->links() }}
            </div>
        @endif
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startCrawlBtn');
    const formWrapper = document.getElementById('crawlFormWrapper');
    const progressArea = document.getElementById('crawlProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const pagesCrawled = document.getElementById('pagesCrawled');
    const progressPercent = document.getElementById('progressPercent');
    let pollInterval = null;

    startBtn.addEventListener('click', async function() {
        const url = document.getElementById('crawlUrl').value.trim();
        const maxPages = document.getElementById('maxPages').value;

        if (!url) return;

        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Starting...';

        try {
            const response = await fetch('{{ route("tools.crawl-audit.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ url: url, max_pages: parseInt(maxPages) })
            });

            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="bi bi-play-circle me-2"></i> Start Crawl Audit';
                return;
            }

            const taskId = data.task_id || data.uuid;
            if (!taskId) {
                alert('Could not start crawl. Please try again.');
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="bi bi-play-circle me-2"></i> Start Crawl Audit';
                return;
            }

            formWrapper.classList.add('d-none');
            progressArea.classList.remove('d-none');

            pollInterval = setInterval(async function() {
                try {
                    const statusRes = await fetch('/tools/crawl-audit/status/' + taskId);
                    const status = await statusRes.json();

                    if (status.status === 'completed') {
                        clearInterval(pollInterval);
                        progressBar.style.width = '100%';
                        progressText.textContent = 'Crawl complete!';
                        pagesCrawled.textContent = (status.pages_crawled || 0) + ' pages crawled';
                        progressPercent.textContent = '100%';

                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                        return;
                    }

                    if (status.status === 'failed' || status.error) {
                        clearInterval(pollInterval);
                        progressText.textContent = 'Crawl failed';
                        progressArea.querySelector('.spinner-border').classList.add('d-none');
                        return;
                    }

                    const pct = Math.min(status.progress || 0, 99);
                    progressBar.style.width = pct + '%';
                    progressText.textContent = status.message || 'Crawling...';
                    pagesCrawled.textContent = (status.pages_crawled || 0) + ' pages crawled';
                    progressPercent.textContent = Math.round(pct) + '%';

                } catch (e) {
                    console.error('Poll error:', e);
                }
            }, 2000);

        } catch (e) {
            alert('Network error. Please try again.');
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-play-circle me-2"></i> Start Crawl Audit';
        }
    });
});
</script>
@endsection
