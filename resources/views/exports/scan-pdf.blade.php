<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SEO Audit Report - {{ parse_url($scan->url, PHP_URL_HOST) }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .header {
            background: {{ $tenant?->primary_color ?? '#0f172a' }};
            color: #ffffff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .header-logo {
            max-height: 40px;
            max-width: 140px;
            margin-right: 16px;
        }
        .header-text h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header-text p {
            margin: 0;
            font-size: 11px;
            opacity: 0.85;
        }
        .score-section {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .score-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }
        .score-card.main {
            background: {{ $tenant?->primary_color ?? '#0f172a' }};
            color: #fff;
            border-color: {{ $tenant?->primary_color ?? '#0f172a' }};
        }
        .score-value {
            font-size: 32px;
            font-weight: 800;
            margin: 8px 0;
        }
        .score-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .meta-box p { margin: 4px 0; font-size: 10px; }
        h2 {
            font-size: 14px;
            color: {{ $tenant?->primary_color ?? '#0f172a' }};
            margin: 24px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid {{ $tenant?->primary_color ?? '#0f172a' }};
            font-weight: 700;
        }
        h3 { font-size: 12px; margin: 16px 0 8px 0; color: #475569; font-weight: 600; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 16px 0;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
        }
        th { background: #f1f5f9; font-weight: 600; color: #334155; }
        tr:nth-child(even) { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge-ok  { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fef9c3; color: #854d0e; }
        .badge-err { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
        .page-break { page-break-before: always; }
        .issue-critical { border-left: 3px solid #ef4444; }
        .issue-error { border-left: 3px solid #f97316; }
        .issue-warning { border-left: 3px solid #eab308; }
        .issue-info { border-left: 3px solid #3b82f6; }
    </style>
</head>
<body>

{{-- ── Cover Page ──────────────────────────────────────────────── --}}
<div class="header">
    <div style="display: flex; align-items: center;">
        @if($tenant?->logo_base64)
            <img src="{{ $tenant->logo_base64 }}" class="header-logo" alt="Logo">
        @endif
        <div class="header-text">
            <h1>{{ $tenant?->agency_name ?? $tenant?->name ?? 'Seo4ma' }}</h1>
            <p>Professional SEO Audit Report</p>
        </div>
    </div>
    <div style="text-align: right; font-size: 10px;">
        <div>{{ now()->format('d/m/Y') }}</div>
        <div style="opacity: 0.7;">{{ $scan->created_at->format('H:i') }}</div>
    </div>
</div>

<div class="score-section">
    <div class="score-card main">
        <div class="score-label">Overall Score</div>
        <div class="score-value">{{ $scan->score_total ?? 0 }}/100</div>
        <div style="font-size: 10px; opacity: 0.9;">
            @if(($scan->score_total ?? 0) >= 90) Excellent
            @elseif(($scan->score_total ?? 0) >= 70) Good
            @elseif(($scan->score_total ?? 0) >= 50) Needs Work
            @else Critical @endif
        </div>
    </div>
    <div class="score-card">
        <div class="score-label">Technical</div>
        <div class="score-value" style="color: {{ $tenant?->primary_color ?? '#0f172a' }};">{{ $scan->score_technical ?? 0 }}/30</div>
    </div>
    <div class="score-card">
        <div class="score-label">On-Page</div>
        <div class="score-value" style="color: {{ $tenant?->primary_color ?? '#0f172a' }};">{{ $scan->score_on_page ?? 0 }}/30</div>
    </div>
    <div class="score-card">
        <div class="score-label">Mobile</div>
        <div class="score-value" style="color: {{ $tenant?->primary_color ?? '#0f172a' }};">{{ $scan->score_mobile ?? 0 }}/10</div>
    </div>
    <div class="score-card">
        <div class="score-label">Speed</div>
        <div class="score-value" style="color: {{ $tenant?->primary_color ?? '#0f172a' }};">{{ $scan->score_speed ?? 0 }}/10</div>
    </div>
</div>

<div class="meta-box">
    <p><strong>Audited URL:</strong> {{ $scan->url }}</p>
    <p><strong>Pages Crawled:</strong> {{ $scan->pages->count() }}</p>
    <p><strong>Robots.txt:</strong> <span class="badge {{ $scan->has_robots_txt ? 'badge-ok' : 'badge-err' }}">{{ $scan->has_robots_txt ? '✓ Present' : '✗ Missing' }}</span></p>
    <p><strong>Sitemap.xml:</strong> <span class="badge {{ $scan->has_sitemap_xml ? 'badge-ok' : 'badge-err' }}">{{ $scan->has_sitemap_xml ? '✓ Present' : '✗ Missing' }}</span></p>
</div>

{{-- ── Issues Summary ──────────────────────────────────────────── --}}
@php
    $critical = $scan->pages->flatMap->issues->where('severity', 'critical')->count();
    $errors = $scan->pages->flatMap->issues->where('severity', 'error')->count();
    $warnings = $scan->pages->flatMap->issues->where('severity', 'warning')->count();
    $infos = $scan->pages->flatMap->issues->where('severity', 'info')->count();
@endphp

<h2>Issue Summary</h2>
<table>
    <thead>
        <tr>
            <th width="20%">Severity</th>
            <th width="15%">Count</th>
            <th width="65%">Impact</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="badge badge-err">Critical</span></td>
            <td>{{ $critical }}</td>
            <td>Immediate action required. Directly impacts rankings.</td>
        </tr>
        <tr>
            <td><span class="badge badge-err">Error</span></td>
            <td>{{ $errors }}</td>
            <td>Significant issues that should be fixed ASAP.</td>
        </tr>
        <tr>
            <td><span class="badge badge-warn">Warning</span></td>
            <td>{{ $warnings }}</td>
            <td>Recommendations for better optimization.</td>
        </tr>
        <tr>
            <td><span class="badge badge-ok">Notice</span></td>
            <td>{{ $infos }}</td>
            <td>Informational items for review.</td>
        </tr>
    </tbody>
</table>

{{-- ── Detailed Page Analysis ──────────────────────────────────── --}}
<div class="page-break"></div>
<h2>Detailed Page Analysis</h2>

@foreach ($scan->pages as $i => $page)
    @if ($i > 0)<hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 20px 0;">@endif

    <h3>Page {{ $i + 1 }}: {{ Str::limit($page->url, 70) }}</h3>

    <div class="meta-box">
        <table style="margin: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px 8px 2px 0; font-weight: 600; width: 120px;">Title Tag:</td>
                <td style="border: none; padding: 2px 0;">{{ $page->title ?? '<span class="badge badge-err">Missing</span>' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 8px 2px 0; font-weight: 600;">Meta Description:</td>
                <td style="border: none; padding: 2px 0;">{{ Str::limit($page->description ?? 'Missing', 100) }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 8px 2px 0; font-weight: 600;">Canonical:</td>
                <td style="border: none; padding: 2px 0;">{{ $page->canonical ?? 'N/A' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 8px 2px 0; font-weight: 600;">Word Count:</td>
                <td style="border: none; padding: 2px 0;">{{ $page->word_count ?? 0 }} words</td>
            </tr>
        </table>
    </div>

    @if (!empty($page->headings))
        <h4>Heading Structure</h4>
        <table>
            <thead><tr><th width="50">Level</th><th>Content</th></tr></thead>
            <tbody>
                @foreach ($page->headings as $heading)
                <tr>
                    <td>{{ strtoupper($heading['tag'] ?? '') }}</td>
                    <td>{{ $heading['text'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($page->images->count())
        <h4>Image Optimization ({{ $page->images->count() }} images)</h4>
        <table>
            <thead><tr><th>Image</th><th width="250">Alt Text</th><th width="60">Status</th></tr></thead>
            <tbody>
                @foreach ($page->images->take(15) as $img)
                <tr>
                    <td style="word-break:break-all;">{{ Str::limit($img->src, 60) }}</td>
                    <td>{{ $img->alt ? Str::limit($img->alt, 50) : '<span class="badge badge-warn">Missing</span>' }}</td>
                    <td>{{ $img->alt ? '<span class="badge badge-ok">Optimized</span>' : '<span class="badge badge-warn">Missing Alt</span>' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($page->issues->count())
        <h4>Page Issues ({{ $page->issues->count() }})</h4>
        <table>
            <thead><tr><th width="60">Severity</th><th>Issue</th><th width="150">URL</th></tr></thead>
            <tbody>
                @foreach ($page->issues->sortBy(fn($i) => match($i->severity) { 'critical' => 1, 'error' => 2, 'warning' => 3, default => 4 }) as $issue)
                <tr class="issue-{{ $issue->severity }}">
                    <td><span class="badge {{ $issue->severity == 'critical' || $issue->severity == 'error' ? 'badge-err' : ($issue->severity == 'warning' ? 'badge-warn' : 'badge-ok') }}">{{ ucfirst($issue->severity) }}</span></td>
                    <td>{{ $issue->message }}</td>
                    <td style="word-break:break-all;">{{ Str::limit($page->url, 40) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endforeach

<div class="footer">
    <p><strong>{{ $tenant?->agency_name ?? 'Seo4ma' }}</strong> @if($tenant?->agency_website) &bull; {{ $tenant->agency_website }} @endif</p>
    <p>Report generated {{ now()->format('d/m/Y H:i') }} &bull; Powered by Seo4ma</p>
</div>

</body>
</html>
