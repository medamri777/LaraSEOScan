@extends('layouts.app')

@section('title', 'Sitemap Crawler - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h3 class="fw-bold mb-1" style="color: #fff;">
            <i class="bi bi-diagram-3 me-2" style="color: #53FC18;"></i>Sitemap Crawler
        </h3>
        <p class="mb-0" style="font-size: 0.875rem; color: #9DA3AF;">
            Enter a domain — we crawl it for real and generate a <code style="color: #53FC18;">sitemap.xml</code> from actual URLs found.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 12px; padding: 1.5rem;">
                <form id="sitemapForm" method="POST" action="{{ route('tools.sitemap-crawler') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2" style="color: #fff; font-size: 0.875rem;">
                            <i class="bi bi-globe me-1" style="color: #53FC18;"></i> Domain <span style="color: #E91916;">*</span>
                        </label>
                        <input type="url" name="url" id="inputUrl" class="form-control shadow-sm rounded-3" style="background: #1a1a1a; border: 1px solid #2B2F35; color: #9DA3AF; font-size: 0.875rem; padding: 0.7rem 1rem;" value="{{ $currentProject?->url ?? '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" style="color: #9DA3AF;">
                            <i class="bi bi-translate me-1" style="color: #53FC18;"></i> Language
                        </label>
                        <select name="language" class="form-select shadow-sm rounded-3" style="background: #0B0E0F; border: 1px solid #2B2F35; color: #fff; font-size: 0.8125rem;">
                            @foreach($languages as $val => $label)
                                <option value="{{ $val }}" {{ old('language', 'en') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" style="color: #9DA3AF;">
                            <i class="bi bi-file-earmark-text me-1" style="color: #53FC18;"></i> Max pages to crawl
                        </label>
                        <select name="max_pages" class="form-select shadow-sm rounded-3" style="background: #0B0E0F; border: 1px solid #2B2F35; color: #fff; font-size: 0.8125rem;">
                            @foreach($pageOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('max_pages', 50) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" id="generateBtn" class="btn w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #53FC18 0%, #00E701 100%); color: #000; border: none; font-size: 0.875rem;">
                        <i class="bi bi-search me-1"></i> Crawl & Generate Sitemap
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div id="initialState">
                <div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 12px; padding: 3rem 2rem; text-align: center; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(83,252,24,0.08); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="bi bi-diagram-3" style="font-size: 1.5rem; color: #53FC18;"></i>
                    </div>
                    <h5 style="font-size: 1rem; color: #fff; margin-bottom: 0.5rem;">Ready to crawl</h5>
                    <p style="font-size: 0.8125rem; color: #9DA3AF; max-width: 380px; margin-bottom: 0;">
                        Enter a domain and we will crawl it to find all internal URLs, then generate a complete <code style="color: #53FC18;">sitemap.xml</code>.
                    </p>
                </div>
            </div>

            <div id="progressWrapper" style="display: none;">
                <div style="background: #191B1F; border: 1px solid #5323F7; border-radius: 12px; padding: 2rem;">
                    <div class="text-center mb-3">
                        <div class="spinner-border mb-2" style="color: #53FC18; width: 2rem; height: 2rem;" role="status"></div>
                        <h5 style="color: #fff; font-size: 1rem;">Crawling website...</h5>
                    </div>
                    <div class="mb-3">
                        <div style="background: #0B0E0F; border-radius: 8px; height: 10px; overflow: hidden;">
                            <div id="progressBar" style="background: linear-gradient(90deg, #53FC18, #00E701); width: 0%; height: 100%; border-radius: 8px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                    <div id="progressInfo" class="text-center" style="color: #9DA3AF; font-size: 0.8125rem;">
                        Crawling page 0 of 0... Found 0 URLs so far
                    </div>
                    <div id="currentUrl" class="text-center mt-2" style="color: #474F54; font-size: 0.7rem; word-break: break-all;">
                        Connecting...
                    </div>
                </div>
            </div>

            <div id="resultWrapper" style="display: none;">
                <div id="statsBar" class="mb-3"></div>
                <div id="outputWrapper">
                    <div style="background: #191B1F; border: 1px solid #53FC18; border-radius: 12px; overflow: hidden;">
                        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom: 1px solid #2B2F35;">
                            <div>
                                <h6 class="fw-bold mb-0" style="color: #53FC18; font-size: 0.875rem;">
                                    <i class="bi bi-file-code me-2"></i>sitemap.xml
                                </h6>
                                <div id="resultDomain" style="font-size: 0.7rem; color: #9DA3AF;"></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="copyBtn" style="font-size: 0.75rem; font-weight: 600; border: 1px solid #474F54; color: #53FC18; background: transparent; padding: 0.35rem 0.85rem;">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                                <button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="downloadBtn" style="font-size: 0.75rem; font-weight: 600; border: 1px solid #474F54; color: #fff; background: transparent; padding: 0.35rem 0.85rem;">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </div>
                        <pre id="sitemapOutput" style="margin: 0; padding: 1.25rem; background: #0B0E0F; font-size: 0.8125rem; line-height: 1.6; overflow-x: auto; min-height: 300px; max-height: 500px; overflow-y: auto; white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>

            <div id="errorWrapper" style="display: none;">
                <div style="background: #191B1F; border: 1px solid #E91916; border-radius: 12px; padding: 2rem; text-align: center;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(233,25,22,0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem; color: #E91916;"></i>
                    </div>
                    <h5 style="font-size: 1rem; color: #fff; margin-bottom: 0.5rem;">Crawl failed</h5>
                    <p id="errorMessage" style="font-size: 0.8125rem; color: #E91916; margin-bottom: 0;"></p>
                </div>
            </div>

            <div id="copyToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
                <div style="background: #53FC18; color: #000; padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; box-shadow: 0 4px 20px rgba(83,252,24,0.3);">
                    <i class="bi bi-check-circle me-1"></i> Copied to clipboard!
                </div>
            </div>
            <div id="downloadToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
                <div style="background: #5323F7; color: #fff; padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; box-shadow: 0 4px 20px rgba(83,35,247,0.3);">
                    <i class="bi bi-download me-1"></i> File downloaded!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var form = document.getElementById('sitemapForm');
    if (!form) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    var initialState = document.getElementById('initialState');
    var progressWrapper = document.getElementById('progressWrapper');
    var resultWrapper = document.getElementById('resultWrapper');
    var errorWrapper = document.getElementById('errorWrapper');
    var progressBar = document.getElementById('progressBar');
    var progressInfo = document.getElementById('progressInfo');
    var currentUrlEl = document.getElementById('currentUrl');
    var sitemapOutput = document.getElementById('sitemapOutput');
    var statsBar = document.getElementById('statsBar');
    var resultDomain = document.getElementById('resultDomain');
    var copyBtn = document.getElementById('copyBtn');
    var downloadBtn = document.getElementById('downloadBtn');
    var generateBtn = document.getElementById('generateBtn');
    var errorMessage = document.getElementById('errorMessage');

    function uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function showToast(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        setTimeout(function() { el.style.display = 'none'; }, 2500);
    }

    function formatDuration(seconds) {
        if (seconds < 60) return seconds + 's';
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + 'm ' + s + 's';
    }

    function renderStats(stats) {
        var html = '<div class="d-flex flex-wrap gap-3">' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.75rem 1.25rem; flex: 1; min-width: 120px;">' +
                '<div style="font-size: 1.25rem; font-weight: 700; color: #53FC18;">' + stats.total_urls + '</div>' +
                '<div style="font-size: 0.7rem; color: #9DA3AF;">URLs in sitemap</div>' +
            '</div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.75rem 1.25rem; flex: 1; min-width: 120px;">' +
                '<div style="font-size: 1.25rem; font-weight: 700; color: #5323F7;">' + stats.pages_crawled + '</div>' +
                '<div style="font-size: 0.7rem; color: #9DA3AF;">Pages crawled</div>' +
            '</div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.75rem 1.25rem; flex: 1; min-width: 120px;">' +
                '<div style="font-size: 1.25rem; font-weight: 700; color: #FFA500;">' + formatDuration(stats.elapsed) + '</div>' +
                '<div style="font-size: 0.7rem; color: #9DA3AF;">Crawl duration</div>' +
            '</div>';
        if (stats.timed_out) {
            html += '<div style="background: #191B1F; border: 1px solid #E91916; border-radius: 10px; padding: 0.75rem 1.25rem; flex: 1; min-width: 120px;">' +
                '<div style="font-size: 0.75rem; font-weight: 600; color: #E91916;"><i class="bi bi-exclamation-triangle me-1"></i>Crawl stopped after 300s</div>' +
                '<div style="font-size: 0.7rem; color: #9DA3AF;">Website may be large</div>' +
            '</div>';
        }
        html += '</div>';
        return html;
    }

    function showProgress() {
        initialState.style.display = 'none';
        resultWrapper.style.display = 'none';
        errorWrapper.style.display = 'none';
        progressWrapper.style.display = 'block';
    }

    function updateProgress(data) {
        if (!progressBar || !progressInfo) return;
        progressBar.style.width = data.progress + '%';
        progressInfo.innerHTML = 'Crawling page ' + data.crawled + ' of ' + data.total + '... Found <strong>' + data.found + '</strong> URLs so far';
        if (currentUrlEl && data.current_url) {
            currentUrlEl.textContent = data.current_url;
        }
    }

    function showResult(data) {
        progressWrapper.style.display = 'none';
        initialState.style.display = 'none';
        errorWrapper.style.display = 'none';

        resultDomain.textContent = data.stats.host;
        statsBar.innerHTML = renderStats(data.stats);
        sitemapOutput.textContent = data.sitemap;
        resultWrapper.style.display = 'block';
    }

    function showError(msg) {
        progressWrapper.style.display = 'none';
        initialState.style.display = 'none';
        resultWrapper.style.display = 'none';
        errorWrapper.style.display = 'block';
        errorMessage.textContent = msg;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = generateBtn;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Crawling...';

        var taskId = uuid();
        var fd = new FormData(form);
        fd.append('task_id', taskId);
        fd.append('_token', csrfToken);

        showProgress();
        progressBar.style.width = '0%';
        progressInfo.innerHTML = 'Starting crawl...';

        var completed = false;
        var pollInterval = setInterval(function() {
            fetch('/tools/sitemap-crawler/status/' + taskId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (completed) return;
                    if (data.status === 'completed') {
                        clearInterval(pollInterval);
                        completed = true;
                        fetch('/tools/sitemap-crawler/result/' + taskId)
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                showResult(res);
                                btn.disabled = false;
                                btn.innerHTML = '<i class="bi bi-search me-1"></i> Crawl & Generate Sitemap';
                            });
                    } else if (data.status === 'crawling') {
                        updateProgress(data);
                    } else if (data.status === 'error') {
                        clearInterval(pollInterval);
                        completed = true;
                        showError(data.error || 'Crawl failed');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-search me-1"></i> Crawl & Generate Sitemap';
                    }
                })
                .catch(function() {});
        }, 1500);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (completed) return;
            clearInterval(pollInterval);
            completed = true;

            try {
                var data = JSON.parse(xhr.responseText);
                if (data.error) {
                    showError(data.error);
                } else {
                    if (data.status === 'completed') {
                        showResult(data);
                    } else {
                        showResult(data);
                    }
                }
            } catch (e) {
                showError('Unexpected server response');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Crawl & Generate Sitemap';
        };

        xhr.onerror = function() {
            if (completed) return;
            clearInterval(pollInterval);
            completed = true;
            showError('Network error — could not reach the server');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Crawl & Generate Sitemap';
        };

        xhr.ontimeout = function() {
            if (completed) return;
            clearInterval(pollInterval);
            completed = true;
            showError('Request timed out');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Crawl & Generate Sitemap';
        };

        xhr.send(fd);
    });

    copyBtn.addEventListener('click', function() {
        var text = sitemapOutput ? sitemapOutput.textContent : '';
        navigator.clipboard.writeText(text).then(function() { showToast('copyToast'); });
    });

    downloadBtn.addEventListener('click', function() {
        var text = sitemapOutput ? sitemapOutput.textContent : '';
        var blob = new Blob([text], { type: 'application/xml' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'sitemap.xml';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(a.href);
        showToast('downloadToast');
    });
})();
</script>
@endsection
