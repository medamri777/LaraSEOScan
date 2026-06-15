@extends('layouts.app')

@section('title', 'Robots.txt Generator - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h3 class="fw-bold mb-1" style="color: #111827;">
            <i class="bi bi-robot me-2" style="color: #10b981;"></i>Robots.txt Generator
        </h3>
        <p class="mb-0" style="font-size: 0.875rem; color: #6b7280;">
            Pick your site type and protection level — we generate the perfect <code style="color: #10b981;">robots.txt</code> for you.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <form id="robotsForm" method="POST" action="{{ route('tools.robots') }}">
                    @csrf
                    <input type="hidden" name="generate" value="1">

                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2" style="color: #374151; font-size: 0.875rem;">
                            <i class="bi bi-globe me-1" style="color: #10b981;"></i> Domain <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="url" name="url" id="inputUrl" class="form-control shadow-sm rounded-3" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; font-size: 0.875rem; padding: 0.7rem 1rem;" value="{{ $currentProject?->url ?? '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" style="color: #6b7280;">Site type</label>
                        <select name="site_type" id="siteType" class="form-select shadow-sm rounded-3" style="background: #f9fafb; border: 1px solid #e5e7eb; color: #111827; font-size: 0.8125rem;">
                            @foreach($types as $val => $label)
                                <option value="{{ $val }}" {{ $siteType === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" style="color: #6b7280;">Protection level</label>
                        <select name="protection_level" class="form-select shadow-sm rounded-3" style="background: #f9fafb; border: 1px solid #e5e7eb; color: #111827; font-size: 0.8125rem;">
                            @foreach($levels as $val => $label)
                                <option value="{{ $val }}" {{ $level === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem;" class="mb-3">
                        <label class="form-label fw-semibold mb-2" style="color: #374151; font-size: 0.8125rem;">
                            <i class="bi bi-shield-exclamation me-1" style="color: #ef4444;"></i> Crawlers
                        </label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="block_ai" id="chkAi" value="1" {{ $blockAi ? 'checked' : '' }} style="border-color: #d1d5db; background: #ffffff;">
                                <label class="form-check-label small" for="chkAi" style="color: #111827;">
                                    <i class="bi bi-cpu me-1" style="color: #10b981;"></i> Block AI scrapers (GPTBot, Claude, Perplexity...)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="block_all" id="chkAll" value="1" {{ $blockAll ? 'checked' : '' }} style="border-color: #d1d5db; background: #ffffff;">
                                <label class="form-check-label small" for="chkAll" style="color: #ef4444;">
                                    <i class="bi bi-x-circle me-1"></i> Block ALL search engines
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="generateBtn" class="btn w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border: none; font-size: 0.875rem;">
                        <i class="bi bi-magic me-1"></i> Generate robots.txt
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            @if($robotsTxt)
            <div id="outputWrapper">
                <div style="background: #ffffff; border: 1px solid #10b981; border-radius: 12px; overflow: hidden;">
                    <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom: 1px solid #e5e7eb;">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #10b981; font-size: 0.875rem;">
                                <i class="bi bi-file-code me-2"></i>robots.txt
                            </h6>
                            <div style="font-size: 0.7rem; color: #6b7280;">{{ $domain }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="copyBtn" style="font-size: 0.75rem; font-weight: 600; border: 1px solid #d1d5db; color: #10b981; background: transparent; padding: 0.35rem 0.85rem;">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                            <button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="downloadBtn" style="font-size: 0.75rem; font-weight: 600; border: 1px solid #d1d5db; color: #374151; background: transparent; padding: 0.35rem 0.85rem;">
                                <i class="bi bi-download"></i> Download
                            </button>
                        </div>
                    </div>
                    <pre id="robotsOutput" style="margin: 0; padding: 1.25rem; background: #f3f4f6; color: #111827; font-size: 0.8125rem; line-height: 1.6; overflow-x: auto; min-height: 300px; max-height: 500px; overflow-y: auto; white-space: pre-wrap;">{{ $robotsTxt }}</pre>
                </div>
            </div>
            @else
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 3rem 2rem; text-align: center; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16,185,129,0.08); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="bi bi-robot" style="font-size: 1.5rem; color: #10b981;"></i>
                </div>
                <h5 style="font-size: 1rem; color: #111827; margin-bottom: 0.5rem;">Ready to generate</h5>
                <p style="font-size: 0.8125rem; color: #6b7280; max-width: 380px; margin-bottom: 0;">
                    Choose your site type and protection level, then generate a complete <code style="color: #10b981;">robots.txt</code> file.
                </p>
            </div>
            @endif

            <div id="copyToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
                <div style="background: #10b981; color: #fff; padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; box-shadow: 0 4px 20px rgba(16,185,129,0.3);">
                    <i class="bi bi-check-circle me-1"></i> Copied to clipboard!
                </div>
            </div>
            <div id="downloadToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
                <div style="background: #7c3aed; color: #fff; padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; box-shadow: 0 4px 20px rgba(124,58,237,0.3);">
                    <i class="bi bi-download me-1"></i> File downloaded!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    var form = document.getElementById('robotsForm');
    if (!form) return;

    var pathsByType = {
        blog:       { admin: ['/admin/','/login/'], private: ['/private/','/backup/'] },
        ecommerce:  { admin: ['/admin/','/login/','/dashboard/'], private: ['/private/','/backup/'] },
        saas:       { admin: ['/admin/','/login/','/dashboard/'], private: ['/private/','/backup/'] },
        wordpress:  { admin: ['/wp-admin/','/wp-includes/','/xmlrpc.php'], private: ['/private/','/backup/'] },
        wix:        { admin: ['/admin/'], private: [] },
        static:     { admin: ['/admin/'], private: ['/private/'] },
        custom:     { admin: ['/admin/','/login/'], private: ['/private/','/backup/'] },
    };

    var aiBots = ['GPTBot','ChatGPT-User','anthropic-ai','Claude-Web','diffbot','Bytespider','PerplexityBot','CCBot'];

    function generateRobotsTxt(data) {
        var now = new Date();
        var ds = now.getFullYear()+'-'+String(now.getMonth()+1).padStart(2,'0')+'-'+String(now.getDate()).padStart(2,'0');
        var domain = '';
        try { domain = new URL(data.url).hostname.replace(/^www\./, ''); } catch(e) { domain = data.url; }
        var level = data.protection_level || 'standard';
        var type = data.site_type || 'custom';
        var cfg = pathsByType[type] || pathsByType.custom;
        var sitemapUrl = 'https://' + domain + '/sitemap.xml';

        function addDisallows(paths) {
            paths.forEach(function(p) { lines.push('Disallow: ' + p); });
        }

        var lines = [];
        lines.push('# Robots.txt generated by Seo4ma');
        lines.push('# Domain: ' + domain);
        lines.push('# Generated: ' + ds);
        lines.push('');

        if (data.block_all) {
            lines.push('User-agent: *');
            lines.push('Disallow: /');
            lines.push('');
            lines.push('Sitemap: ' + sitemapUrl);
            return { text: lines.join('\n'), domain: domain };
        }

        lines.push('# Allow all robots to crawl');
        lines.push('User-agent: *');
        lines.push('Allow: /');
        lines.push('');

        lines.push('# Admin & sensitive pages');
        addDisallows(cfg.admin);
        if (level !== 'minimal') {
            addDisallows(cfg.private);
            if (level === 'maximum') lines.push('Disallow: /uploads/tmp/');
        }
        lines.push('');

        if (level !== 'minimal') {
            lines.push('# Crawl delay');
            lines.push('Crawl-delay: ' + (level === 'maximum' ? '5' : '2'));
            lines.push('');
        }

        if (level === 'maximum') {

        if (level === 'maximum') {
            lines.push('# Block AI scrapers');
            aiBots.forEach(function(bot) {
                lines.push('User-agent: ' + bot);
                lines.push('Disallow: /');
                lines.push('');
            });
        }

        lines.push('# Sitemap');
        lines.push('Sitemap: ' + sitemapUrl);

        return { text: lines.join('\n'), domain: domain };
    }

    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function showToast(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        setTimeout(function() { el.style.display = 'none'; }, 2500);
    }

    function renderOutput(result) {
        var colored = result.text.split('\n').map(function(line) {
            if (line.startsWith('#')) return '<span style="color:#6b7280;">' + escapeHtml(line) + '</span>';
            if (line.startsWith('User-agent')) return '<span style="color:#10b981;">User-agent:</span> ' + escapeHtml(line.split(': ')[1] || '');
            if (line.startsWith('Disallow')) return '<span style="color:#ef4444;">Disallow:</span> ' + escapeHtml(line.split(': ')[1] || '');
            if (line.startsWith('Allow')) return '<span style="color:#10b981;">Allow:</span> ' + escapeHtml(line.split(': ')[1] || '');
            if (line.startsWith('Crawl-delay')) return '<span style="color:#FFA500;">Crawl-delay:</span> ' + escapeHtml(line.split(': ')[1] || '');
            if (line.startsWith('Sitemap')) return '<span style="color:#7c3aed;">Sitemap:</span> ' + escapeHtml(line.split(': ')[1] || '');
            return escapeHtml(line);
        }).join('\n');

        var html = '<div style="background:#ffffff;border:1px solid #10b981;border-radius:12px;overflow:hidden;">' +
            '<div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom:1px solid #e5e7eb;">' +
                '<div><h6 class="fw-bold mb-0" style="color:#10b981;font-size:0.875rem;"><i class="bi bi-file-code me-2"></i>robots.txt</h6><div style="font-size:0.7rem;color:#6b7280;">' + escapeHtml(result.domain) + '</div></div>' +
                '<div class="d-flex gap-2">' +
                    '<button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="copyBtn" style="font-size:0.75rem;font-weight:600;border:1px solid #d1d5db;color:#10b981;background:transparent;padding:0.35rem 0.85rem;"><i class="bi bi-clipboard"></i> Copy</button>' +
                    '<button class="btn btn-sm d-inline-flex align-items-center gap-1 rounded-3" id="downloadBtn" style="font-size:0.75rem;font-weight:600;border:1px solid #d1d5db;color:#374151;background:transparent;padding:0.35rem 0.85rem;"><i class="bi bi-download"></i> Download</button>' +
                '</div>' +
            '</div>' +
            '<pre id="robotsOutput" style="margin:0;padding:1.25rem;background:#f9fafb;font-size:0.8125rem;line-height:1.6;overflow-x:auto;min-height:300px;max-height:500px;overflow-y:auto;white-space:pre-wrap;">' + colored + '</pre>' +
        '</div>';

        var wrapper = document.getElementById('outputWrapper');
        var placeholder = document.querySelector('.col-lg-7 > div:not(#outputWrapper):not(#copyToast):not(#downloadToast)');
        if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
        if (!wrapper) { wrapper = document.createElement('div'); wrapper.id = 'outputWrapper'; document.querySelector('.col-lg-7').insertBefore(wrapper, document.querySelector('.col-lg-7').firstChild); }
        wrapper.innerHTML = html;
        wrapper.style.display = '';

        document.getElementById('copyBtn').addEventListener('click', function() { navigator.clipboard.writeText(result.text).then(function() { showToast('copyToast'); }); });
        document.getElementById('downloadBtn').addEventListener('click', function() {
            var blob = new Blob([result.text], { type: 'text/plain' });
            var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'robots.txt';
            document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
            showToast('downloadToast');
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('generateBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';

        var fd = new FormData(form);
        var data = {};
        fd.forEach(function(v, k) { data[k] = v; });
        data.block_ai = fd.has('block_ai');
        data.block_all = fd.has('block_all');

        var result = generateRobotsTxt(data);
        renderOutput(result);

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generate robots.txt';
    });

    (function() {
        var pre = document.getElementById('robotsOutput');
        var copyBtn = document.getElementById('copyBtn');
        var dlBtn = document.getElementById('downloadBtn');
        if (copyBtn && pre) copyBtn.addEventListener('click', function() { navigator.clipboard.writeText(pre.textContent).then(function() { showToast('copyToast'); }); });
        if (dlBtn && pre) dlBtn.addEventListener('click', function() {
            var blob = new Blob([pre.textContent], { type: 'text/plain' });
            var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'robots.txt';
            document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
            showToast('downloadToast');
        });
    })();
})();
</script>
@endsection
