@extends('layouts.app')

@section('title', 'Crawl Audit Results - Premium SEO Spider - Seo4ma')

@section('sidebar')
<aside class="sidebar show" :class="{ 'show': sidebarOpen }">
    <a href="{{ route('tools.crawl-audit') }}" class="sidebar-brand">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        <span>Seo4ma</span>
    </a>
    <nav class="sidebar-nav" style="overflow-y: auto; max-height: calc(100vh - 120px); scrollbar-width: thin;">
        <template x-for="s in sections" :key="s.id">
            <div>
                <div x-show="s.label" class="sidebar-section-label mt-3" x-text="s.label"></div>
                <a @click="setTab(s.id)" class="sidebar-link" :class="tab === s.id ? 'active' : ''">
                    <i :class="s.icon"></i>
                    <span x-text="s.name"></span>
                    <span x-show="s.id === 'broken-links' && getBrokenLinksList().length > 0" class="ms-auto" style="background:#ef4444; color:#111827; font-size:0.65rem; padding:1px 6px; border-radius:99px; font-weight:700;" x-text="getBrokenLinksList().length"></span>
                </a>
            </div>
        </template>
    </nav>
</aside>
@endsection

@section('content')
<div class="container-fluid cr-root">

    <!-- Hero Header -->
    <div class="cr-hero">
        <div class="cr-hero-inner">
            <div class="cr-hero-left">
                <div class="cr-hero-badge">CRAWL COMPLETE</div>
                <h3 class="cr-hero-title">Audit Report</h3>
                <div class="cr-hero-url" x-text="url"></div>
            </div>
            <div class="cr-hero-actions">
                <a href="{{ route('tools.crawl-audit') }}" class="cr-btn cr-btn-ghost"><i class="bi bi-arrow-left"></i> New Crawl</a>
                <button @click="exportReport()" class="cr-btn cr-btn-accent"><i class="bi bi-download"></i> Export JSON</button>
            </div>
        </div>
    </div>

    <!-- KPI Ribbon -->
    <div class="cr-kpi-ribbon">
        <div class="cr-kpi" style="--kpi-accent: #10b981;">
            <div class="cr-kpi-icon"><i class="bi bi-globe2"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="rp.total_pages || 0"></span><span class="cr-kpi-label">Pages Crawled</span></div>
        </div>
        <div class="cr-kpi" style="--kpi-accent: #3B82F6;">
            <div class="cr-kpi-icon"><i class="bi bi-search"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="rp.pages?.length || 0"></span><span class="cr-kpi-label">Found</span></div>
        </div>
        <div class="cr-kpi" style="--kpi-accent: #F59E0B;">
            <div class="cr-kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="getTotalIssuesCount()"></span><span class="cr-kpi-label">Issues</span></div>
        </div>
        <div class="cr-kpi" style="--kpi-accent: #ef4444;">
            <div class="cr-kpi-icon"><i class="bi bi-x-circle"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="getPagesWithErrorsCount()"></span><span class="cr-kpi-label">Errors</span></div>
        </div>
        <div class="cr-kpi" style="--kpi-accent: #8B5CF6;">
            <div class="cr-kpi-icon"><i class="bi bi-arrow-return-right"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="getRedirectsCount()"></span><span class="cr-kpi-label">Redirects</span></div>
        </div>
        <div class="cr-kpi" style="--kpi-accent: #06B6D4;">
            <div class="cr-kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="cr-kpi-data"><span class="cr-kpi-num" x-text="(rp.elapsed || 0) + 's'"></span><span class="cr-kpi-label">Elapsed</span></div>
        </div>
    </div>

    <!-- Mobile Tab Nav -->
    <div class="cr-mobile-nav d-xl-none">
        <div class="cr-mobile-scroll">
            <template x-for="s in sections" :key="s.id">
                <button @click="setTab(s.id)" class="cr-mtab" :class="tab === s.id ? 'active' : ''">
                    <i :class="s.icon"></i> <span x-text="s.name"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="cr-main">

        <!-- ═══════ 1. DASHBOARD ═══════ -->
        <div x-show="tab === 'dashboard'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-speedometer2"></i> SEO Health Dashboard</h5>
            <div class="row g-3 mb-4">
                <div class="col-lg-5 col-12">
                    <div class="cr-glass-card cr-score-card">
                        <div class="cr-score-ring">
                            <svg viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
                                <circle cx="60" cy="60" r="52" fill="none" stroke="url(#scoreGrad)" stroke-width="10" stroke-linecap="round"
                                    :stroke-dasharray="(getCrawlHealthScore() / 100 * 327) + ' 327'"
                                    transform="rotate(-90 60 60)" style="transition: stroke-dasharray 1s ease;"/>
                                <defs><linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#06B6D4"/></linearGradient></defs>
                            </svg>
                            <div class="cr-score-value" x-text="getCrawlHealthScore() + '%'"></div>
                        </div>
                        <div class="cr-score-meta">
                            <div class="cr-score-label">Optimization Score</div>
                            <div class="cr-score-url" x-text="rp.start_url"></div>
                            <div class="cr-score-stats">
                                <div><span class="cr-dot" style="background:#10b981;"></span> Passed <strong x-text="getPassedChecksCount()"></strong></div>
                                <div><span class="cr-dot" style="background:#F59E0B;"></span> Warnings <strong x-text="getTotalIssuesCount()"></strong></div>
                                <div><span class="cr-dot" style="background:#EF4444;"></span> Errors <strong x-text="getPagesWithErrorsCount()"></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-12">
                    <div class="cr-glass-card h-100">
                        <div class="cr-card-head">Top Issues Found</div>
                        <div class="cr-issues-list">
                            <template x-for="iss in getTopIssuesList()" :key="iss.name">
                                <div class="cr-issue-row">
                                    <div class="cr-issue-indicator" :style="'background:' + (iss.severity==='error'?'#EF4444':'#F59E0B')"></div>
                                    <span class="cr-issue-name" x-text="iss.name"></span>
                                    <span class="cr-issue-count" :style="'background:' + (iss.severity==='error'?'rgba(239,68,68,0.15)':'rgba(245,158,11,0.15)'); 'color:' + (iss.severity==='error'?'#EF4444':'#F59E0B')" x-text="iss.count"></span>
                                </div>
                            </template>
                            <div x-show="getTopIssuesList().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> No critical issues found. Great job!</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4 col-12">
                    <div class="cr-glass-card">
                        <div class="cr-card-head">HTTP Status Codes</div>
                        <div class="cr-kv-list">
                            <template x-for="(cnt, code) in rp.status_codes" :key="code">
                                <div class="cr-kv-row">
                                    <span class="cr-http-pill" :class="code>=400?'cr-pill-red':(code>=300?'cr-pill-yellow':'cr-pill-green')" x-text="code || '?'"></span>
                                    <div class="cr-kv-bar-wrap"><div class="cr-kv-bar" :style="'width:' + Math.round((cnt / (rp.total_pages||1)) * 100) + '%; background:' + (code>=400?'#EF4444':(code>=300?'#F59E0B':'#10b981'))"></div></div>
                                    <span class="cr-kv-val" x-text="cnt"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="cr-glass-card">
                        <div class="cr-card-head">Crawler Statistics</div>
                        <div class="cr-kv-list">
                            <div class="cr-kv-simple"><span>Avg Word Count</span><strong x-text="getAverageWordCount()"></strong></div>
                            <div class="cr-kv-simple"><span>Avg Response Time</span><strong x-text="getAverageResponseTime() + ' ms'"></strong></div>
                            <div class="cr-kv-simple"><span>Internal Links</span><strong x-text="rp.links?.internal || 0"></strong></div>
                            <div class="cr-kv-simple"><span>Total Images</span><strong x-text="rp.images?.total || 0"></strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="cr-glass-card">
                        <div class="cr-card-head">Resources Index</div>
                        <div class="cr-kv-list">
                            <div class="cr-kv-simple"><span>Sitemap</span><span class="cr-pill-sm" :class="rp.has_sitemap?'cr-pill-green':'cr-pill-red'" x-text="rp.has_sitemap?'Found':'Missing'"></span></div>
                            <div class="cr-kv-simple"><span>robots.txt</span><span class="cr-pill-sm" :class="rp.has_robots?'cr-pill-green':'cr-pill-red'" x-text="rp.has_robots?'Found':'Missing'"></span></div>
                            <div class="cr-kv-simple"><span>Schema Types</span><strong x-text="rp.schema_types?.length || 0"></strong></div>
                            <div class="cr-kv-simple"><span>Images Missing Alt</span><strong :class="(rp.images?.missing_alt > 0)?'text-danger':''" x-text="rp.images?.missing_alt || 0"></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ 2. SITE INFORMATION ═══════ -->
        <div x-show="tab === 'site-info'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-info-circle"></i> Site Information</h5>
            <div class="row g-3">
                <div class="col-md-6 col-12">
                    <div class="cr-glass-card">
                        <div class="cr-card-head">Crawl Snapshot</div>
                        <div class="cr-info-grid">
                            <div class="cr-info-item"><div class="cr-info-label">Target URL</div><div class="cr-info-value text-break" x-text="rp.start_url"></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">Pages Indexed</div><div class="cr-info-value cr-info-big" x-text="rp.total_pages"></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">Avg Response</div><div class="cr-info-value" x-text="getAverageResponseTime() + ' ms'"></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">robots.txt</div><div class="cr-info-value"><span class="cr-pill-sm" :class="rp.has_robots?'cr-pill-green':'cr-pill-red'" x-text="rp.has_robots?'Available':'Not found'"></span></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="cr-glass-card">
                        <div class="cr-card-head">Overview</div>
                        <div class="cr-info-grid">
                            <div class="cr-info-item"><div class="cr-info-label">Sitemap</div><div class="cr-info-value"><span class="cr-pill-sm" :class="rp.has_sitemap?'cr-pill-green':'cr-pill-red'" x-text="rp.has_sitemap?'Available':'Not found'"></span></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">Links Profile</div><div class="cr-info-value" x-text="(rp.links?.internal||0)+' internal · '+(rp.links?.external||0)+' external'"></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">Schema Detected</div><div class="cr-info-value" x-text="rp.schema_types?.join(', ') || 'None'"></div></div>
                            <div class="cr-info-item"><div class="cr-info-label">Titles</div><div class="cr-info-value" x-text="(rp.titles?.optimal||0)+' optimal · '+(rp.title_duplicates||0)+' duplicates'"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ 3. CRAWL STATISTICS ═══════ -->
        <div x-show="tab === 'crawl-stats'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-activity"></i> Crawl Statistics</h5>
            <div class="row g-3">
                <div class="col-md-6 col-12"><div class="cr-glass-card"><div class="cr-card-head">Status Code Distribution</div><div id="statusCodeChart" style="width:100%;height:300px;"></div></div></div>
                <div class="col-md-6 col-12"><div class="cr-glass-card"><div class="cr-card-head">Crawl Depth Distribution</div><div id="crawlDepthChart" style="width:100%;height:300px;"></div></div></div>
                <div class="col-12"><div class="cr-glass-card"><div class="cr-card-head">Response Time Distribution</div><div id="responseTimeDistChart" style="width:100%;height:320px;"></div></div></div>
            </div>
        </div>

        <!-- ═══════ 4. ALL URLS ═══════ -->
        <div x-show="tab === 'all-urls'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-globe"></i> All Crawled Pages</h5>
            <div class="cr-glass-card">
                <div class="cr-toolbar">
                    <div class="cr-search-wrap"><i class="bi bi-search"></i><input type="text" x-model="urlQ" placeholder="Search pages..." class="cr-search-input"></div>
                    <span class="cr-toolbar-count" x-text="filteredPages.length + ' of ' + rp.total_pages + ' pages'"></span>
                </div>
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in filteredPages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <a :href="p.url" target="_blank" class="cr-url-link" x-text="getUrlPath(p.url)"></a>
                                <span class="cr-url-title" x-text="p.title || '(No title)'"></span>
                            </div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="p.status_code>=400?'cr-chip-red':(p.status_code>=300?'cr-chip-yellow':'cr-chip-green')" x-text="p.status_code"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="(p.response_time_ms||0)+'ms'"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="(p.word_count||0)+' words'"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="'L'+p.depth"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 5. PAGE TITLES ═══════ -->
        <div x-show="tab === 'page-titles'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-type"></i> Page Titles Audit</h5>
            <div class="cr-metric-ribbon">
                <div class="cr-metric cr-metric-red"><span class="cr-metric-num" x-text="rp.titles?.missing || 0"></span><span class="cr-metric-lbl">Missing</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.titles?.short || 0"></span><span class="cr-metric-lbl">Too Short</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.titles?.long || 0"></span><span class="cr-metric-lbl">Too Long</span></div>
                <div class="cr-metric cr-metric-green"><span class="cr-metric-num" x-text="rp.titles?.optimal || 0"></span><span class="cr-metric-lbl">Optimal</span></div>
                <div class="cr-metric cr-metric-red"><span class="cr-metric-num" x-text="rp.title_duplicates || 0"></span><span class="cr-metric-lbl">Duplicated</span></div>
            </div>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="t in rp.title_list || []" :key="t.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <span class="cr-url-path" x-text="getUrlPath(t.url)"></span>
                                <span class="cr-title-text" x-text="t.title || '— Missing —'"></span>
                            </div>
                            <div class="cr-data-chips">
                                <div class="cr-len-bar-wrap" style="width:120px;" :title="t.length + ' chars (optimal: 30-60)'">
                                    <div class="cr-len-bar" :style="'width:' + Math.min(100, (t.length/60)*100) + '%; background:' + (t.length===0?'#EF4444':(t.length<30||t.length>60?'#F59E0B':'#10b981'))"></div>
                                </div>
                                <span class="cr-chip-len" x-text="t.length + ' chars'"></span>
                                <span class="cr-chip" :class="t.length===0?'cr-chip-red':(t.length<30||t.length>60?'cr-chip-yellow':'cr-chip-green')" x-text="t.length===0?'MISSING':(t.length<30?'SHORT':(t.length>60?'LONG':'OK'))"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 6. META DESCRIPTIONS ═══════ -->
        <div x-show="tab === 'meta-descriptions'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-align-left"></i> Meta Descriptions Audit</h5>
            <div class="cr-metric-ribbon">
                <div class="cr-metric cr-metric-red"><span class="cr-metric-num" x-text="rp.descriptions?.missing || 0"></span><span class="cr-metric-lbl">Missing</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.descriptions?.short || 0"></span><span class="cr-metric-lbl">Too Short</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.descriptions?.long || 0"></span><span class="cr-metric-lbl">Too Long</span></div>
                <div class="cr-metric cr-metric-green"><span class="cr-metric-num" x-text="rp.descriptions?.optimal || 0"></span><span class="cr-metric-lbl">Optimal</span></div>
            </div>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="d in rp.desc_list || []" :key="d.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <span class="cr-url-path" x-text="getUrlPath(d.url)"></span>
                                <span class="cr-desc-text" x-text="d.description || '— Missing —'"></span>
                            </div>
                            <div class="cr-data-chips">
                                <div class="cr-len-bar-wrap" style="width:120px;" :title="d.length + ' chars (optimal: 110-160)'">
                                    <div class="cr-len-bar" :style="'width:' + Math.min(100, (d.length/160)*100) + '%; background:' + (d.length===0?'#EF4444':(d.length<110||d.length>160?'#F59E0B':'#10b981'))"></div>
                                </div>
                                <span class="cr-chip-len" x-text="d.length + ' chars'"></span>
                                <span class="cr-chip" :class="d.length===0?'cr-chip-red':(d.length<110||d.length>160?'cr-chip-yellow':'cr-chip-green')" x-text="d.length===0?'MISSING':(d.length<110?'SHORT':(d.length>160?'LONG':'OK'))"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 7. HEADINGS ═══════ -->
        <div x-show="tab === 'headings'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-hash"></i> Headings (H1) Audit</h5>
            <div class="cr-metric-ribbon">
                <div class="cr-metric cr-metric-red"><span class="cr-metric-num" x-text="rp.h1?.missing || 0"></span><span class="cr-metric-lbl">Missing H1</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.h1?.multiple || 0"></span><span class="cr-metric-lbl">Multiple H1</span></div>
                <div class="cr-metric cr-metric-green"><span class="cr-metric-num" x-text="rp.h1?.single || 0"></span><span class="cr-metric-lbl">Single H1 ✓</span></div>
            </div>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="h in rp.h1_list || []" :key="h.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(h.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-ghost" x-text="h.count + ' H1'"></span>
                                <span class="cr-chip" :class="h.count===0?'cr-chip-red':(h.count>1?'cr-chip-yellow':'cr-chip-green')" x-text="h.count===0?'MISSING':(h.count>1?'MULTIPLE':'OK')"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 8. CONTENT ANALYSIS ═══════ -->
        <div x-show="tab === 'content-analysis'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-journal-text"></i> Content Analysis</h5>
            <div class="cr-glass-card">
                <div class="cr-card-head">Thin Content Pages (&lt; 250 words)</div>
                <div class="cr-data-list" style="max-height:500px;">
                    <template x-for="p in getThinContentPages()" :key="p.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-ghost" x-text="p.word_count + ' words'"></span>
                                <span class="cr-chip cr-chip-red">Thin Content</span>
                            </div>
                        </div>
                    </template>
                    <div x-show="getThinContentPages().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> All pages have sufficient content.</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 9. DUPLICATE CONTENT ═══════ -->
        <div x-show="tab === 'duplicate-content'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-copy"></i> Duplicate Content</h5>
            <div class="cr-glass-card">
                <div class="cr-dup-list" style="max-height:600px; overflow-y:auto;">
                    <template x-for="group in getDuplicateContentGroups()" :key="group.value">
                        <div class="cr-dup-group">
                            <div class="cr-dup-header"><span class="cr-chip cr-chip-yellow" x-text="group.type.includes('Title')?'Title':'Meta Desc'"></span><span class="cr-dup-val" x-text="group.value"></span></div>
                            <template x-for="item in group.pages" :key="item">
                                <div class="cr-dup-page"><i class="bi bi-file-earmark"></i> <span x-text="getUrlPath(item)"></span></div>
                            </template>
                        </div>
                    </template>
                    <div x-show="getDuplicateContentGroups().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> No duplicates found. Great uniqueness!</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 10. ALL LINKS ═══════ -->
        <div x-show="tab === 'links'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-link-45deg"></i> All Links</h5>
            <div class="cr-metric-ribbon">
                <div class="cr-metric" style="--m-accent:#fff;"><span class="cr-metric-num" x-text="rp.links?.total || 0"></span><span class="cr-metric-lbl">Total</span></div>
                <div class="cr-metric cr-metric-green"><span class="cr-metric-num" x-text="rp.links?.internal || 0"></span><span class="cr-metric-lbl">Internal</span></div>
                <div class="cr-metric" style="--m-accent:#8B5CF6;"><span class="cr-metric-num" style="color:#8B5CF6;" x-text="rp.links?.external || 0"></span><span class="cr-metric-lbl">External</span></div>
                <div class="cr-metric cr-metric-yellow"><span class="cr-metric-num" x-text="rp.links?.nofollow || 0"></span><span class="cr-metric-lbl">Nofollow</span></div>
            </div>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="l in rp.links_list?.slice(0, 200) || []" :key="l.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(l.href)"></span><span class="cr-link-anchor" x-text="l.anchor_text || '[No anchor]'"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="l.is_internal?'cr-chip-green':'cr-chip-purple'" x-text="l.is_internal?'Internal':'External'"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="l.is_nofollow?'nofollow':'dofollow'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 11. ANCHOR TEXT ═══════ -->
        <div x-show="tab === 'anchor-text'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-chat-left-text"></i> Anchor Text</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="anc in getAnchorTextList()" :key="anc.text">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-anchor-text" x-text="anc.text"></span></div>
                            <div class="cr-data-chips">
                                <div class="cr-len-bar-wrap" style="width:80px;"><div class="cr-len-bar" style="background:#10b981;" :style="'width:' + Math.min(100, (anc.count / (getAnchorTextList()[0]?.count||1))*100) + '%'"></div></div>
                                <span class="cr-chip cr-chip-green" x-text="anc.count"></span>
                                <span class="cr-chip-len" x-text="((anc.count / (rp.links_list?.length || 1))*100).toFixed(1) + '%'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 12. BROKEN LINKS ═══════ -->
        <div x-show="tab === 'broken-links'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-link-45deg"></i> Broken Links</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="l in getBrokenLinksList()" :key="l.href">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <span class="cr-url-broken" x-text="getUrlPath(l.href)"></span>
                                <span class="cr-link-source">from: <span x-text="getUrlPath(l.source_page)"></span></span>
                            </div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-red" x-text="l.status_code || 'Offline'"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="getBrokenLinksList().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> No broken links found!</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 13. ORPHAN PAGES ═══════ -->
        <div x-show="tab === 'orphan-pages'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-diagram-2"></i> Orphan Pages</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in getOrphanPagesList()" :key="p.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-ghost" x-text="p.incoming + ' incoming'"></span>
                                <span class="cr-chip" :class="p.incoming===0?'cr-chip-red':'cr-chip-yellow'" x-text="p.incoming===0?'ORPHAN':'LOW LINKS'"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="getOrphanPagesList().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> No orphan pages. Excellent architecture!</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 14. REDIRECTS ═══════ -->
        <div x-show="tab === 'redirects-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-arrow-return-right"></i> Redirects</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in getRedirectsList()" :key="p.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-yellow" x-text="p.status_code"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="getRedirectsList().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> No redirects detected.</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 15. IMAGES ═══════ -->
        <div x-show="tab === 'images'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-image"></i> Images Audit</h5>
            <div class="cr-metric-ribbon">
                <div class="cr-metric" style="--m-accent:#fff;"><span class="cr-metric-num" x-text="rp.images?.total || 0"></span><span class="cr-metric-lbl">Total</span></div>
                <div class="cr-metric cr-metric-green"><span class="cr-metric-num" x-text="rp.images?.with_alt || 0"></span><span class="cr-metric-lbl">With Alt</span></div>
                <div class="cr-metric cr-metric-red"><span class="cr-metric-num" x-text="rp.images?.missing_alt || 0"></span><span class="cr-metric-lbl">Missing Alt</span></div>
            </div>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="img in rp.images_list?.slice(0, 200) || []" :key="img.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <span class="cr-url-path" x-text="getUrlPath(img.src)"></span>
                                <span class="cr-link-anchor" x-text="img.alt || '— No alt text —'"></span>
                            </div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="img.has_alt?'cr-chip-green':'cr-chip-red'" x-text="img.has_alt?'OK':'MISSING'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 16. CANONICAL ═══════ -->
        <div x-show="tab === 'canonical-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-link"></i> Canonical Tags Audit</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main">
                                <span class="cr-url-path" x-text="getUrlPath(p.url)"></span>
                                <span class="cr-link-anchor" x-text="p.canonical || '— No canonical —'"></span>
                            </div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="p.canonical?'cr-chip-green':'cr-chip-yellow'" x-text="p.canonical?'Set':'Missing'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 17. ROBOTS.TXT ═══════ -->
        <div x-show="tab === 'robots-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-robot"></i> robots.txt Audit</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-ghost" x-text="p.robots || 'No meta robots'"></span>
                                <span class="cr-chip" :class="(!p.robots || p.robots.includes('index'))?'cr-chip-green':'cr-chip-yellow'" x-text="(!p.robots || p.robots.includes('index'))?'Indexable':'Restricted'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 18. HREFLANG ═══════ -->
        <div x-show="tab === 'hreflang-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-translate"></i> Hreflang Audit</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="p.hreflangs?.length?'cr-chip-green':'cr-chip-ghost'" x-text="(p.hreflangs?.length||0) + ' hreflang tags'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 19. STRUCTURED DATA ═══════ -->
        <div x-show="tab === 'structured-data'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-layers"></i> Structured Data</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="p.structured_data?.length?'cr-chip-green':'cr-chip-ghost'" x-text="(p.structured_data?.length || 0) + ' schemas'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 20. TECH STACK ═══════ -->
        <div x-show="tab === 'tech-stack'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-cpu"></i> Technology Stack</h5>
            <div class="cr-glass-card">
                <div class="cr-tech-grid">
                    <template x-for="tech in getDetectedTechStack()" :key="tech.name">
                        <div class="cr-tech-card"><i class="bi bi-cpu-fill"></i><div class="cr-tech-name" x-text="tech.name"></div><div class="cr-tech-cat" x-text="tech.category"></div></div>
                    </template>
                    <div x-show="getDetectedTechStack().length === 0" class="cr-empty-state" style="grid-column:1/-1;"><i class="bi bi-question-circle"></i> No technologies detected.</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 21. DISCOVERY SOURCES ═══════ -->
        <div x-show="tab === 'discovery-sources'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-compass"></i> Discovery Sources</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-green" x-text="p.discovery_source || 'link'"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="'Depth ' + p.depth"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 22. PERFORMANCE ═══════ -->
        <div x-show="tab === 'performance-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-lightning-charge"></i> Performance</h5>
            <div class="cr-glass-card">
                <div class="cr-card-head">Slow Pages (&gt; 1000ms)</div>
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in getSlowPages()" :key="p.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip cr-chip-red" x-text="p.response_time_ms + ' ms'"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="getSlowPages().length === 0" class="cr-empty-state"><i class="bi bi-check-circle"></i> All pages are fast!</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 23. SECURITY ═══════ -->
        <div x-show="tab === 'security-audit'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-shield-lock"></i> Security</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in rp.pages" :key="p.id">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-chip" :class="p.url?.startsWith('https')?'cr-chip-green':'cr-chip-red'" x-text="p.url?.startsWith('https')?'HTTPS':'HTTP'"></span>
                                <span class="cr-chip cr-chip-ghost" x-text="p.server || 'Hidden'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 24-26. VISUALIZATIONS ═══════ -->
        <div x-show="tab === 'site-tree-map'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-tree"></i> Site Tree Map</h5>
            <div class="cr-glass-card"><div id="siteTreeMapChart" style="width:100%;height:500px;"></div></div>
        </div>
        <div x-show="tab === 'crawl-graph'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-diagram-3-fill"></i> Crawl Graph</h5>
            <div class="cr-glass-card"><div id="crawlGraphChart" style="width:100%;height:500px;"></div></div>
        </div>
        <div x-show="tab === 'issue-heatmap'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-grid-3x3-gap-fill"></i> Issue Heatmap</h5>
            <div class="cr-glass-card">
                <div class="cr-data-list" style="max-height:600px;">
                    <template x-for="p in getHeatmapList()" :key="p.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(p.url)"></span></div>
                            <div class="cr-data-chips">
                                <span class="cr-hm-cell" :class="p.title?'cr-hm-bad':'cr-hm-ok'" title="Title">T</span>
                                <span class="cr-hm-cell" :class="p.meta?'cr-hm-bad':'cr-hm-ok'" title="Meta">M</span>
                                <span class="cr-hm-cell" :class="p.h1?'cr-hm-bad':'cr-hm-ok'" title="H1">H</span>
                                <span class="cr-hm-cell" :class="p.alt?'cr-hm-bad':'cr-hm-ok'" title="Alt">A</span>
                                <span class="cr-hm-cell" :class="p.canon?'cr-hm-bad':'cr-hm-ok'" title="Canon">C</span>
                                <span class="cr-chip" :class="p.total>2?'cr-chip-red':(p.total>0?'cr-chip-yellow':'cr-chip-green')" x-text="p.total + ' issues'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 27. SOURCE CODE SEARCH ═══════ -->
        <div x-show="tab === 'source-code-search'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-search"></i> Source Code Search</h5>
            <div class="cr-glass-card">
                <div class="cr-toolbar" style="flex-direction:column; gap:0.75rem;">
                    <div class="cr-search-wrap" style="width:100%;"><i class="bi bi-search"></i><input type="text" x-model="searchQuery" @keydown.enter="runSourceCodeSearch()" placeholder="Regex pattern..." class="cr-search-input" style="width:100%;"></div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="cr-preset-btn" @click="searchQuery='G-[A-Z0-9]+'; runSourceCodeSearch()">GA4</button>
                        <button class="cr-preset-btn" @click="searchQuery='GTM-[A-Z0-9]+'; runSourceCodeSearch()">GTM</button>
                        <button class="cr-preset-btn" @click="searchQuery='TODO|FIXME'; runSourceCodeSearch()">TODO</button>
                        <button class="cr-preset-btn" @click="searchQuery='style='; runSourceCodeSearch()">Inline CSS</button>
                        <button class="cr-btn cr-btn-accent cr-btn-sm" @click="runSourceCodeSearch()">Search</button>
                    </div>
                </div>
                <div class="cr-data-list" style="max-height:400px;">
                    <template x-for="r in searchResults" :key="r.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(r.url)"></span></div>
                            <div class="cr-data-chips"><span class="cr-chip cr-chip-green">Match</span></div>
                        </div>
                    </template>
                    <div x-show="searchResults.length === 0" class="cr-empty-state">No results yet.</div>
                </div>
            </div>
        </div>

        <!-- ═══════ 28. CUSTOM EXTRACTION ═══════ -->
        <div x-show="tab === 'custom-extraction'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-download"></i> Custom Extraction</h5>
            <div class="cr-glass-card">
                <div class="cr-toolbar">
                    <div class="cr-search-wrap" style="flex:1;"><i class="bi bi-code-slash"></i><input type="text" x-model="extRule" placeholder="CSS selector or tag (e.g. h1, canonical)" class="cr-search-input" style="width:100%;"></div>
                    <button class="cr-btn cr-btn-accent cr-btn-sm" @click="runCustomExtraction()">Extract</button>
                </div>
                <div class="cr-data-list" style="max-height:400px;">
                    <template x-for="ex in extractionResults" :key="ex.url">
                        <div class="cr-data-row cr-data-row-hover">
                            <div class="cr-data-main"><span class="cr-url-path" x-text="getUrlPath(ex.url)"></span><span class="cr-link-anchor" x-text="ex.value || 'None'"></span></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ═══════ 29. ROBOTS TESTER ═══════ -->
        <div x-show="tab === 'robots-tester'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-play-circle"></i> Robots.txt Tester</h5>
            <div class="cr-glass-card" style="max-width:600px;">
                <div class="cr-toolbar">
                    <div class="cr-search-wrap" style="flex:1;"><i class="bi bi-slash-circle"></i><input type="text" x-model="testerPath" placeholder="/admin/dashboard" class="cr-search-input" style="width:100%;"></div>
                    <button class="cr-btn cr-btn-accent cr-btn-sm" @click="testRobotsPath()">Test</button>
                </div>
                <div x-show="testerResult !== null" class="cr-test-result" :class="testerResult?'cr-test-pass':'cr-test-fail'">
                    <i class="bi" :class="testerResult?'bi-check-circle-fill':'bi-x-circle-fill'"></i>
                    <span x-text="testerResult?'ALLOWED — Crawlers can access this path.':'BLOCKED — Directives forbid crawling.'"></span>
                </div>
            </div>
        </div>

        <!-- ═══════ 30. SITEMAP GENERATOR ═══════ -->
        <div x-show="tab === 'sitemap-generator'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-file-earmark-code"></i> Sitemap Generator</h5>
            <div class="cr-glass-card">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-4"><label class="cr-form-label">Frequency</label><select x-model="sitemapFreq" class="cr-form-select"><option value="daily">daily</option><option value="weekly">weekly</option><option value="monthly">monthly</option></select></div>
                    <div class="col-md-4"><label class="cr-form-label">Priority</label><input type="number" step="0.1" min="0" max="1" x-model="sitemapPriority" class="cr-form-input"></div>
                    <div class="col-md-4"><button class="cr-btn cr-btn-accent w-100" @click="generateSitemapXML()">Generate XML</button></div>
                </div>
                <div x-show="sitemapXML">
                    <div class="d-flex justify-content-between align-items-center mb-2"><span class="cr-card-head" style="margin:0;">Preview</span><button class="cr-btn cr-btn-ghost cr-btn-sm" @click="copySitemapToClipboard()"><i class="bi bi-clipboard"></i> Copy</button></div>
                    <pre class="cr-code-block" x-text="sitemapXML"></pre>
                </div>
            </div>
        </div>

        <!-- ═══════ 31. CRAWL COMPARISON ═══════ -->
        <div x-show="tab === 'crawl-comparison'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-arrow-left-right"></i> Crawl Comparison</h5>
            <div class="cr-glass-card" style="max-width:700px;">
                <label class="cr-form-label">Upload baseline crawl JSON</label>
                <input type="file" @change="loadComparisonCrawl($event)" class="cr-form-input">
                <div x-show="compareDelta" class="cr-compare-grid mt-3">
                    <div class="cr-compare-item"><span class="cr-compare-val" :class="String(compareDelta.pages).startsWith('+')?'text-success':'text-danger'" x-text="compareDelta.pages"></span><span>Pages</span></div>
                    <div class="cr-compare-item"><span class="cr-compare-val" x-text="compareDelta.responseTime + ' ms'"></span><span>Response Δ</span></div>
                    <div class="cr-compare-item"><span class="cr-compare-val" x-text="compareDelta.links"></span><span>Links Δ</span></div>
                </div>
            </div>
        </div>

        <!-- ═══════ 32. SAVE / LOAD ═══════ -->
        <div x-show="tab === 'save-load'" x-transition:enter.duration.200ms>
            <h5 class="cr-section-title"><i class="bi bi-folder-symlink"></i> Save / Load</h5>
            <div class="row g-3" style="max-width:800px;">
                <div class="col-md-6 col-12">
                    <div class="cr-glass-card h-100">
                        <div class="cr-card-head">Export Audit</div>
                        <p style="color:#6b7280; font-size:0.8125rem;">Download the full raw crawl data as a JSON backup.</p>
                        <button class="cr-btn cr-btn-accent" @click="exportReport()"><i class="bi bi-download"></i> Export JSON</button>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="cr-glass-card h-100">
                        <div class="cr-card-head">Restore Audit</div>
                        <p style="color:#6b7280; font-size:0.8125rem;">Load a previously exported JSON crawl backup.</p>
                        <input type="file" @change="loadCrawlBackup($event)" class="cr-form-input">
                    </div>
                </div>
            </div>
        </div>

    </div><!-- cr-main -->
</div><!-- cr-root -->

<!-- ═══════════════════════════════════════════════════ -->
<!-- STYLES -->
<!-- ═══════════════════════════════════════════════════ -->
<style>
[x-cloak]{display:none!important}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.08);border-radius:9px}
::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,0.15)}

.cr-root{padding:0 0.75rem 2rem;max-width:1800px;margin:0 auto}
@media(min-width:768px){.cr-root{padding:0 1.25rem 2rem}}

/* ── Hero ── */
.cr-hero{background:linear-gradient(135deg,rgba(16,185,129,0.06) 0%,rgba(124,58,237,0.06) 100%);border:1px solid #e5e7eb;border-radius:16px;padding:1.25rem 1.5rem;margin-bottom:1rem}
.cr-hero-inner{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
.cr-hero-badge{display:inline-block;font-size:0.6rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#10b981;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:99px;padding:3px 10px;margin-bottom:0.35rem}
.cr-hero-title{font-size:1.35rem;font-weight:800;color:#111827;margin:0}
.cr-hero-url{font-size:0.8125rem;color:#10b981;font-weight:500;word-break:break-all;margin-top:2px}
.cr-hero-actions{display:flex;gap:0.5rem;flex-wrap:wrap}

/* ── Buttons ── */
.cr-btn{display:inline-flex;align-items:center;gap:0.4rem;font-size:0.8125rem;font-weight:600;border:none;border-radius:8px;padding:0.55rem 1rem;cursor:pointer;transition:all 0.2s}
.cr-btn-ghost{background:#f9fafb;color:#6b7280;border:1px solid rgba(255,255,255,0.08)}.cr-btn-ghost:hover{border-color:rgba(16,185,129,0.4);color:#10b981;background:rgba(16,185,129,0.05)}
.cr-btn-accent{background:linear-gradient(135deg,#10b981,#06B6D4);color:#000;font-weight:700}.cr-btn-accent:hover{filter:brightness(1.1);transform:translateY(-1px)}
.cr-btn-sm{padding:0.4rem 0.75rem;font-size:0.75rem}

/* ── KPI Ribbon ── */
.cr-kpi-ribbon{display:grid;grid-template-columns:repeat(3,1fr);gap:0.5rem;margin-bottom:1rem}
@media(min-width:768px){.cr-kpi-ribbon{grid-template-columns:repeat(6,1fr)}}
.cr-kpi{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:0.875rem;display:flex;align-items:center;gap:0.75rem;transition:all 0.2s}
.cr-kpi:hover{border-color:var(--kpi-accent,#10b981);transform:translateY(-2px);box-shadow:0 4px 20px rgba(0,0,0,0.3)}
.cr-kpi-icon{width:36px;height:36px;border-radius:10px;background:color-mix(in srgb,var(--kpi-accent,#10b981) 12%,transparent);color:var(--kpi-accent,#10b981);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.cr-kpi-data{display:flex;flex-direction:column;min-width:0}
.cr-kpi-num{font-size:1.25rem;font-weight:800;color:#111827;line-height:1.2}
.cr-kpi-label{font-size:0.65rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.5px}

/* ── Mobile Nav ── */
.cr-mobile-nav{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:1rem;padding:0.375rem}
.cr-mobile-scroll{display:flex;overflow-x:auto;gap:0.25rem;scrollbar-width:thin;-webkit-overflow-scrolling:touch}
.cr-mtab{flex-shrink:0;display:flex;align-items:center;gap:0.3rem;padding:0.45rem 0.65rem;border:none;border-radius:8px;background:transparent;color:#6b7280;font-size:0.7rem;font-weight:600;white-space:nowrap;cursor:pointer;transition:all 0.15s}
.cr-mtab:hover{background:#f9fafb;color:#111827}.cr-mtab.active{background:rgba(16,185,129,0.1);color:#10b981}

/* ── Section Title ── */
.cr-section-title{font-size:1.1rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem}
.cr-section-title i{color:#10b981;font-size:1.1rem}

/* ── Glass Card ── */
.cr-glass-card{background:#f9fafb;backdrop-filter:blur(12px);border:1px solid #e5e7eb;border-radius:14px;padding:1.25rem;margin-bottom:1rem;transition:border-color 0.2s}
.cr-glass-card:hover{border-color:rgba(255,255,255,0.1)}
.cr-card-head{font-size:0.8125rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem}

/* ── Score Card ── */
.cr-score-card{display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap}
.cr-score-ring{position:relative;width:130px;height:130px;flex-shrink:0}
.cr-score-ring svg{width:100%;height:100%}
.cr-score-value{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:#111827}
.cr-score-meta{flex:1;min-width:0}
.cr-score-label{font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#6b7280;margin-bottom:0.25rem}
.cr-score-url{font-size:0.8125rem;color:#10b981;word-break:break-all;margin-bottom:0.75rem}
.cr-score-stats{display:flex;flex-direction:column;gap:0.4rem;font-size:0.8125rem;color:#6b7280}
.cr-score-stats div{display:flex;align-items:center;gap:0.5rem}
.cr-score-stats strong{color:#111827}
.cr-dot{width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0}

/* ── Issues List ── */
.cr-issues-list{display:flex;flex-direction:column;gap:0;max-height:240px;overflow-y:auto}
.cr-issue-row{display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0;border-bottom:1px solid #f3f4f6}
.cr-issue-row:last-child{border:none}
.cr-issue-indicator{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.cr-issue-name{flex:1;font-size:0.8125rem;color:#374151;font-weight:500}
.cr-issue-count{font-size:0.75rem;font-weight:700;padding:2px 8px;border-radius:6px;min-width:28px;text-align:center}

/* ── KV List ── */
.cr-kv-list{display:flex;flex-direction:column;gap:0}
.cr-kv-row{display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0;border-bottom:1px solid #f3f4f6}
.cr-kv-row:last-child{border:none}
.cr-kv-bar-wrap{flex:1;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden}
.cr-kv-bar{height:100%;border-radius:99px;transition:width 0.5s ease}
.cr-kv-val{font-size:0.8125rem;font-weight:700;color:#111827;min-width:30px;text-align:right}
.cr-kv-simple{display:flex;justify-content:space-between;align-items:center;padding:0.55rem 0;border-bottom:1px solid #f3f4f6;font-size:0.8125rem;color:#6b7280}
.cr-kv-simple:last-child{border:none}
.cr-kv-simple strong{color:#111827;font-weight:700}

/* ── Pills & Chips ── */
.cr-http-pill{font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:6px;min-width:36px;text-align:center}
.cr-pill-green{background:rgba(16,185,129,0.12);color:#10b981}
.cr-pill-yellow{background:rgba(245,158,11,0.12);color:#F59E0B}
.cr-pill-red{background:rgba(239,68,68,0.12);color:#EF4444}
.cr-pill-sm{font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:6px}

.cr-chip{display:inline-flex;align-items:center;font-size:0.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap;letter-spacing:0.3px}
.cr-chip-green{background:rgba(16,185,129,0.1);color:#10b981}
.cr-chip-yellow{background:rgba(245,158,11,0.1);color:#F59E0B}
.cr-chip-red{background:rgba(239,68,68,0.1);color:#EF4444}
.cr-chip-purple{background:rgba(139,92,246,0.1);color:#A78BFA}
.cr-chip-ghost{background:#f3f4f6;color:#6b7280}
.cr-chip-len{font-size:0.7rem;color:#64748B;font-weight:600;font-variant-numeric:tabular-nums}

/* ── Data List (replaces tables) ── */
.cr-data-list{overflow-y:auto;scrollbar-width:thin}
.cr-data-row{display:flex;justify-content:space-between;align-items:center;gap:0.75rem;padding:0.7rem 0.5rem;border-bottom:1px solid #f3f4f6;transition:background 0.15s}
.cr-data-row-hover:hover{background:#f9fafb;border-radius:8px}
.cr-data-main{display:flex;flex-direction:column;gap:0.15rem;min-width:0;flex:1}
.cr-data-chips{display:flex;align-items:center;gap:0.375rem;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end}
.cr-url-link{font-size:0.8125rem;font-weight:600;color:#10b981;text-decoration:none;word-break:break-all}.cr-url-link:hover{text-decoration:underline}
.cr-url-path{font-size:0.8125rem;font-weight:600;color:#374151;word-break:break-all}
.cr-url-title{font-size:0.75rem;color:#64748B;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:400px}
.cr-url-broken{font-size:0.8125rem;font-weight:600;color:#EF4444;word-break:break-all}
.cr-title-text{font-size:0.75rem;color:#6b7280;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:500px}
.cr-desc-text{font-size:0.7rem;color:#64748B;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:500px}
.cr-link-anchor{font-size:0.7rem;color:#64748B;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cr-link-source{font-size:0.7rem;color:#475569}
.cr-anchor-text{font-size:0.8125rem;font-weight:600;color:#6b7280}

/* ── Length Bar ── */
.cr-len-bar-wrap{height:5px;background:#f3f4f6;border-radius:99px;overflow:hidden}
.cr-len-bar{height:100%;border-radius:99px;transition:width 0.4s ease}

/* ── Metric Ribbon ── */
.cr-metric-ribbon{display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap}
.cr-metric{background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;padding:0.75rem 1rem;text-align:center;flex:1;min-width:80px}
.cr-metric-num{display:block;font-size:1.35rem;font-weight:800;line-height:1.2}
.cr-metric-lbl{font-size:0.65rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;margin-top:0.125rem;display:block}
.cr-metric-green .cr-metric-num{color:#10b981}
.cr-metric-yellow .cr-metric-num{color:#F59E0B}
.cr-metric-red .cr-metric-num{color:#EF4444}

/* ── Toolbar ── */
.cr-toolbar{display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap}
.cr-search-wrap{display:flex;align-items:center;gap:0.5rem;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:0.45rem 0.75rem;transition:border-color 0.2s}
.cr-search-wrap:focus-within{border-color:rgba(16,185,129,0.3)}
.cr-search-wrap i{color:#64748B;font-size:0.875rem}
.cr-search-input{background:transparent;border:none;outline:none;color:#111827;font-size:0.8125rem;width:200px}
.cr-search-input::placeholder{color:#475569}
.cr-toolbar-count{font-size:0.75rem;color:#64748B;font-weight:600}

/* ── Empty State ── */
.cr-empty-state{padding:2rem;text-align:center;color:#64748B;font-size:0.875rem;display:flex;align-items:center;justify-content:center;gap:0.5rem}
.cr-empty-state i{color:#10b981;font-size:1.125rem}

/* ── Duplicates ── */
.cr-dup-list{display:flex;flex-direction:column;gap:0.75rem}
.cr-dup-group{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:0.875rem}
.cr-dup-header{display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;font-size:0.8125rem}
.cr-dup-val{color:#6b7280;font-weight:600;word-break:break-all}
.cr-dup-page{font-size:0.75rem;color:#64748B;padding:0.25rem 0 0.25rem 1rem;border-left:2px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:0.375rem}

/* ── Heatmap Cells ── */
.cr-hm-cell{width:24px;height:24px;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;font-size:0.6rem;font-weight:700}
.cr-hm-ok{background:rgba(16,185,129,0.08);color:rgba(16,185,129,0.5)}
.cr-hm-bad{background:rgba(239,68,68,0.15);color:#EF4444}

/* ── Tech Grid ── */
.cr-tech-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:0.75rem}
.cr-tech-card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:1rem;text-align:center;transition:all 0.2s}
.cr-tech-card:hover{border-color:rgba(16,185,129,0.3);transform:translateY(-2px)}
.cr-tech-card i{font-size:1.5rem;color:#10b981;margin-bottom:0.5rem}
.cr-tech-name{font-size:0.875rem;font-weight:700;color:#111827}
.cr-tech-cat{font-size:0.7rem;color:#64748B;font-weight:500;margin-top:0.125rem}

/* ── Forms ── */
.cr-form-label{display:block;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.375rem}
.cr-form-select,.cr-form-input{width:100%;background:#f3f4f6;border:1px solid rgba(255,255,255,0.08);border-radius:8px;color:#111827;font-size:0.8125rem;padding:0.55rem 0.75rem;outline:none;transition:border-color 0.2s}
.cr-form-select:focus,.cr-form-input:focus{border-color:rgba(16,185,129,0.3)}
.cr-form-select option{background:#1a1d21;color:#111827}
.cr-code-block{background:rgba(0,0,0,0.3);border:1px solid #e5e7eb;border-radius:10px;padding:1rem;color:#10b981;font-size:0.75rem;max-height:250px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;margin:0}
.cr-preset-btn{background:#f3f4f6;border:1px solid rgba(255,255,255,0.08);border-radius:6px;color:#6b7280;font-size:0.7rem;font-weight:600;padding:0.35rem 0.6rem;cursor:pointer;transition:all 0.15s}
.cr-preset-btn:hover{border-color:rgba(16,185,129,0.3);color:#10b981}

/* ── Test Result ── */
.cr-test-result{display:flex;align-items:center;gap:0.5rem;padding:0.875rem;border-radius:10px;margin-top:0.75rem;font-size:0.8125rem;font-weight:600}
.cr-test-pass{background:rgba(16,185,129,0.08);color:#10b981;border:1px solid rgba(16,185,129,0.15)}
.cr-test-fail{background:rgba(239,68,68,0.08);color:#EF4444;border:1px solid rgba(239,68,68,0.15)}

/* ── Compare Grid ── */
.cr-compare-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem}
.cr-compare-item{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:1rem;text-align:center}
.cr-compare-val{display:block;font-size:1.25rem;font-weight:800;color:#111827;margin-bottom:0.25rem}
.cr-compare-item span:last-child{font-size:0.7rem;color:#64748B;font-weight:600;text-transform:uppercase}

/* ── Info Grid ── */
.cr-info-grid{display:flex;flex-direction:column;gap:0}
.cr-info-item{padding:0.625rem 0;border-bottom:1px solid #f3f4f6}
.cr-info-item:last-child{border:none}
.cr-info-label{font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:#64748B;margin-bottom:0.2rem}
.cr-info-value{font-size:0.875rem;color:#374151;font-weight:500}
.cr-info-big{font-size:1.25rem;font-weight:800;color:#111827}

/* ── Responsive ── */
@media(max-width:767.98px){
    .cr-data-row{flex-direction:column;align-items:flex-start;gap:0.4rem}
    .cr-data-chips{justify-content:flex-start}
    .cr-score-card{justify-content:center;text-align:center}
    .cr-metric-ribbon{gap:0.35rem}
    .cr-metric{min-width:60px;padding:0.5rem}
    .cr-metric-num{font-size:1rem}
    .cr-compare-grid{grid-template-columns:1fr}
}
</style>

<!-- ═══════════════════════════════════════════════════ -->
<!-- SCRIPT -->
<!-- ═══════════════════════════════════════════════════ -->
<script>
window.hasCrawlApp = true;

function crawlApp() {
    return {
        url: '{{ $scan->url }}',
        busy: false,
        error: null,
        report: @json($reportData),
        prog: { pct: 100, found: 0, url: '' },
        tab: 'dashboard',
        showConfig: false,
        urlQ: '',
        cfg: { maxPages: 100, concurrency: 5, maxDepth: 4, timeout: 300 },
        taskId: '{{ $taskId }}',
        timer: null,
        sidebarOpen: false,
        searchQuery: '',
        searchResults: [],
        extRule: '',
        extractionResults: [],
        testerPath: '',
        testerResult: null,
        sitemapFreq: 'daily',
        sitemapPriority: 0.8,
        sitemapXML: '',
        compareDelta: null,

        sections: [
            { id: 'dashboard', name: 'Dashboard', icon: 'bi bi-speedometer2', label: 'Overview' },
            { id: 'site-info', name: 'Site Information', icon: 'bi bi-info-circle', label: null },
            { id: 'crawl-stats', name: 'Crawl Statistics', icon: 'bi bi-activity', label: null },
            { id: 'all-urls', name: 'All URLs', icon: 'bi bi-globe', label: 'Content & On-Page' },
            { id: 'page-titles', name: 'Page Titles', icon: 'bi bi-type', label: null },
            { id: 'meta-descriptions', name: 'Meta Descriptions', icon: 'bi bi-align-left', label: null },
            { id: 'headings', name: 'Headings (H1)', icon: 'bi bi-hash', label: null },
            { id: 'content-analysis', name: 'Content Analysis', icon: 'bi bi-journal-text', label: null },
            { id: 'duplicate-content', name: 'Duplicate Content', icon: 'bi bi-copy', label: null },
            { id: 'links', name: 'All Links', icon: 'bi bi-link-45deg', label: 'Links' },
            { id: 'anchor-text', name: 'Anchor Text', icon: 'bi bi-chat-left-text', label: null },
            { id: 'broken-links', name: 'Broken Links', icon: 'bi bi-link-45deg', label: null },
            { id: 'orphan-pages', name: 'Orphan Pages', icon: 'bi bi-diagram-2', label: null },
            { id: 'redirects-audit', name: 'Redirects', icon: 'bi bi-arrow-return-right', label: null },
            { id: 'images', name: 'Images', icon: 'bi bi-image', label: 'Resources' },
            { id: 'canonical-audit', name: 'Canonical Audit', icon: 'bi bi-link', label: 'Technical' },
            { id: 'robots-audit', name: 'robots.txt', icon: 'bi bi-robot', label: null },
            { id: 'hreflang-audit', name: 'Hreflang', icon: 'bi bi-translate', label: null },
            { id: 'structured-data', name: 'Structured Data', icon: 'bi bi-layers', label: null },
            { id: 'tech-stack', name: 'Tech Stack', icon: 'bi bi-cpu', label: null },
            { id: 'discovery-sources', name: 'Discovery Sources', icon: 'bi bi-compass', label: null },
            { id: 'performance-audit', name: 'Performance', icon: 'bi bi-lightning-charge', label: null },
            { id: 'security-audit', name: 'Security', icon: 'bi bi-shield-lock', label: null },
            { id: 'site-tree-map', name: 'Site Tree Map', icon: 'bi bi-tree', label: 'Visualizations' },
            { id: 'crawl-graph', name: 'Crawl Graph', icon: 'bi bi-diagram-3-fill', label: null },
            { id: 'issue-heatmap', name: 'Issue Heatmap', icon: 'bi bi-grid-3x3-gap-fill', label: null },
            { id: 'source-code-search', name: 'Source Code Search', icon: 'bi bi-search', label: 'Tools' },
            { id: 'custom-extraction', name: 'Custom Extraction', icon: 'bi bi-download', label: null },
            { id: 'robots-tester', name: 'Robots.txt Tester', icon: 'bi bi-play-circle', label: null },
            { id: 'sitemap-generator', name: 'Sitemap Generator', icon: 'bi bi-file-earmark-code', label: null },
            { id: 'crawl-comparison', name: 'Crawl Comparison', icon: 'bi bi-arrow-left-right', label: null },
            { id: 'save-load', name: 'Save / Load', icon: 'bi bi-folder-symlink', label: null }
        ],

        get rp() { return this.report || {}; },
        get filteredPages() {
            const pages = this.report?.pages || [];
            if (!this.urlQ) return pages;
            const q = this.urlQ.toLowerCase();
            return pages.filter(p => p.url.toLowerCase().includes(q) || (p.title || '').toLowerCase().includes(q));
        },

        init() { this.setTab('dashboard'); },

        setTab(id) {
            this.tab = id;
            if (id === 'crawl-stats') setTimeout(() => this.initCrawlStatsCharts(), 100);
            else if (id === 'site-tree-map') setTimeout(() => this.initSiteTreeMapChart(), 100);
            else if (id === 'crawl-graph') setTimeout(() => this.initCrawlGraphChart(), 100);
        },

        getUrlPath(url) {
            if (!url) return '';
            try { return new URL(url).pathname + (new URL(url).search || ''); } catch(e) { return url.replace(/https?:\/\/[^\/]+/, '') || '/'; }
        },

        getCrawlHealthScore() {
            if (!this.report?.pages?.length) return 0;
            let total = this.report.pages.length;
            let issues = this.getTotalIssuesCount();
            let score = Math.max(0, Math.round(100 - (issues / Math.max(total, 1)) * 15));
            return Math.min(score, 100);
        },

        getTotalIssuesCount() {
            if (!this.report?.pages) return 0;
            let c = 0;
            c += this.report.titles?.missing || 0;
            c += this.report.titles?.short || 0;
            c += this.report.titles?.long || 0;
            c += this.report.title_duplicates || 0;
            c += this.report.descriptions?.missing || 0;
            c += this.report.descriptions?.short || 0;
            c += this.report.descriptions?.long || 0;
            c += this.report.h1?.missing || 0;
            c += this.report.h1?.multiple || 0;
            c += this.report.images?.missing_alt || 0;
            c += this.getPagesWithErrorsCount();
            return c;
        },
        getPassedChecksCount() {
            if (!this.report?.pages) return 0;
            let c = 0;
            c += this.report.titles?.optimal || 0;
            c += this.report.descriptions?.optimal || 0;
            c += this.report.h1?.single || 0;
            c += this.report.images?.with_alt || 0;
            c += this.report.pages.filter(p => p.status_code === 200).length;
            return c;
        },
        getPagesWithErrorsCount() { return this.report?.pages?.filter(p => p.status_code >= 400 || !p.status_code).length || 0; },
        getRedirectsCount() { return this.report?.pages?.filter(p => p.status_code >= 300 && p.status_code < 400).length || 0; },
        getAverageWordCount() { if (!this.report?.pages?.length) return 0; return Math.round(this.report.pages.reduce((a,p) => a+(p.word_count||0), 0) / this.report.pages.length); },
        getAverageResponseTime() { if (!this.report?.pages?.length) return 0; return Math.round(this.report.pages.reduce((a,p) => a+(p.response_time_ms||0), 0) / this.report.pages.length); },
        getThinContentPages() { return this.report?.pages?.filter(p => p.word_count > 0 && p.word_count < 250) || []; },
        getSlowPages() { return this.report?.pages?.filter(p => p.response_time_ms > 1000) || []; },
        getBrokenLinksList() {
            if (!this.report?.links_list) return [];
            return this.report.links_list.filter(l => l.status_code >= 400 || !l.status_code).map(l => {
                let s = this.report.pages.find(p => p.id === l.seo_page_id);
                return { href: l.href, source_page: s ? s.url : 'Unknown', status_code: l.status_code };
            });
        },
        getRedirectsList() { return this.report?.pages?.filter(p => p.status_code >= 300 && p.status_code < 400) || []; },
        getOrphanPagesList() {
            if (!this.report?.pages) return [];
            return this.report.pages.map(p => {
                let inc = this.report.links_list?.filter(l => this.normalizeUrl(l.href) === this.normalizeUrl(p.url)).length || 0;
                return { url: p.url, incoming: inc };
            }).filter(x => x.incoming <= 1).sort((a,b) => a.incoming - b.incoming);
        },
        getAnchorTextList() {
            if (!this.report?.links_list) return [];
            let c = {};
            this.report.links_list.forEach(l => { let t = (l.anchor_text||'[No Anchor]').trim(); c[t] = (c[t]||0)+1; });
            return Object.keys(c).map(k => ({text:k, count:c[k]})).sort((a,b) => b.count - a.count).slice(0,50);
        },
        getDuplicateContentGroups() {
            if (!this.report?.pages) return [];
            let ti = {}, de = {};
            this.report.pages.forEach(p => { if(p.title) ti[p.title]=(ti[p.title]||[]).concat(p.url); if(p.description) de[p.description]=(de[p.description]||[]).concat(p.url); });
            let list = [];
            Object.keys(ti).forEach(k => { if(ti[k].length>1) list.push({type:'Title Duplicate',value:k,pages:ti[k]}); });
            Object.keys(de).forEach(k => { if(de[k].length>1) list.push({type:'Meta Desc Duplicate',value:k,pages:de[k]}); });
            return list;
        },
        getTopIssuesList() {
            let l = [];
            if (this.report.titles?.missing) l.push({name:'Missing Title Tags',count:this.report.titles.missing,severity:'error'});
            if (this.report.title_duplicates) l.push({name:'Duplicate Titles',count:this.report.title_duplicates,severity:'warning'});
            if (this.report.descriptions?.missing) l.push({name:'Missing Meta Description',count:this.report.descriptions.missing,severity:'error'});
            if (this.report.h1?.missing) l.push({name:'Missing H1 Tag',count:this.report.h1.missing,severity:'error'});
            if (this.report.h1?.multiple) l.push({name:'Multiple H1 Tags',count:this.report.h1.multiple,severity:'warning'});
            if (this.report.images?.missing_alt) l.push({name:'Images Missing ALT',count:this.report.images.missing_alt,severity:'warning'});
            let br = this.getBrokenLinksList().length;
            if (br) l.push({name:'Broken Links (4xx/5xx)',count:br,severity:'error'});
            return l.sort((a,b) => b.count - a.count);
        },
        getDetectedTechStack() {
            if (!this.report?.pages) return [];
            let s = [], h = this.report.pages.map(p => (p.title||'')+' '+(p.description||'')).join(' ');
            if (h.includes('wp-content')||h.includes('wordpress')) s.push({name:'WordPress',category:'CMS'});
            if (h.includes('react')) s.push({name:'React.js',category:'Frontend'});
            if (h.includes('vue')) s.push({name:'Vue.js',category:'Frontend'});
            if (h.includes('jquery')) s.push({name:'jQuery',category:'JS Library'});
            if (h.includes('bootstrap')) s.push({name:'Bootstrap',category:'CSS'});
            if (h.includes('tailwind')) s.push({name:'Tailwind',category:'CSS'});
            if (h.includes('google-analytics')||h.includes('googletagmanager')) s.push({name:'Google Analytics',category:'Tracking'});
            return s;
        },
        getHeatmapList() {
            if (!this.report?.pages) return [];
            return this.report.pages.map(p => {
                let t = !p.title?1:0, m = !p.description?1:0, h1 = 0;
                if(p.headings){ let c=p.headings.filter(h=>h.tag==='h1').length; if(c===0||c>1) h1=1; }
                let alt = this.report.images_list?.filter(i => i.seo_page_id===p.id && !i.has_alt).length||0;
                let cn = !p.canonical?1:0;
                return {url:p.url,title:t,meta:m,h1,alt:alt?1:0,canon:cn,total:t+m+h1+(alt?1:0)+cn};
            }).sort((a,b) => b.total - a.total);
        },

        initCrawlStatsCharts() {
            let cd = document.getElementById('statusCodeChart');
            if(cd){let c=echarts.init(cd,'dark',{backgroundColor:'transparent'});let d=this.report.status_codes||{};c.setOption({tooltip:{trigger:'item'},series:[{type:'pie',radius:['40%','70%'],data:Object.keys(d).map(k=>({value:d[k],name:'HTTP '+k})),itemStyle:{borderRadius:6},emphasis:{itemStyle:{shadowBlur:10}}}]});}
            let dd=document.getElementById('crawlDepthChart');
            if(dd){let c=echarts.init(dd,'dark',{backgroundColor:'transparent'});let dp={};this.report.pages?.forEach(p=>{dp[p.depth]=(dp[p.depth]||0)+1;});c.setOption({tooltip:{trigger:'axis'},xAxis:{type:'category',data:Object.keys(dp).map(k=>'L'+k)},yAxis:{type:'value'},series:[{data:Object.values(dp),type:'bar',itemStyle:{color:'#10b981',borderRadius:[4,4,0,0]}}]});}
            let td=document.getElementById('responseTimeDistChart');
            if(td){let c=echarts.init(td,'dark',{backgroundColor:'transparent'});let sd=this.report.pages.map((p,i)=>[i,p.response_time_ms||0]);c.setOption({tooltip:{trigger:'item',formatter:p=>'Page '+p.data[0]+'<br/>'+p.data[1]+' ms'},xAxis:{type:'value',name:'Page Index'},yAxis:{type:'value',name:'ms'},series:[{symbolSize:8,data:sd,type:'scatter',itemStyle:{color:'#8B5CF6'}}]});}
        },
        initSiteTreeMapChart() {
            let el=document.getElementById('siteTreeMapChart');if(!el)return;
            let c=echarts.init(el,'dark',{backgroundColor:'transparent'});
            let root={name:'/',children:[]};
            this.report.pages.forEach(p=>{let path=p.url.replace(/https?:\/\/[^\/]+/,'');if(path===''||path==='/')return;let parts=path.split('/').filter(x=>x);let cur=root;parts.forEach(part=>{let ex=cur.children.find(c=>c.name===part);if(!ex){ex={name:part,children:[]};cur.children.push(ex);}cur=ex;});});
            c.setOption({tooltip:{trigger:'item',triggerOn:'mousemove'},series:[{type:'tree',data:[root],top:'1%',left:'7%',bottom:'1%',right:'20%',symbolSize:7,label:{position:'left',verticalAlign:'middle',align:'right',fontSize:9,color:'#fff'},leaves:{label:{position:'right',verticalAlign:'middle',align:'left'}},emphasis:{focus:'descendant'},expandAndCollapse:true,animationDuration:550}]});
        },
        initCrawlGraphChart() {
            let el=document.getElementById('crawlGraphChart');if(!el)return;
            let c=echarts.init(el,'dark',{backgroundColor:'transparent'});
            let nodes=this.report.pages.map(p=>{let inc=this.report.links_list.filter(l=>this.normalizeUrl(l.href)===this.normalizeUrl(p.url)).length;let sz=Math.max(10,Math.min(inc*2+10,45));let clr='#10b981';if(p.status_code>=400)clr='#EF4444';else if(p.status_code>=300)clr='#F59E0B';return{id:p.url,name:p.url.replace(/https?:\/\/(www\.)?/,'').substring(0,20),value:inc,symbolSize:sz,itemStyle:{color:clr}};});
            let edges=[];this.report.pages.forEach(p=>{this.report.links_list.filter(l=>l.seo_page_id===p.id).forEach(l=>{let t=this.normalizeUrl(l.href);if(this.report.pages.some(tp=>this.normalizeUrl(tp.url)===t))edges.push({source:p.url,target:l.href});});});
            c.setOption({tooltip:{},series:[{type:'graph',layout:'force',data:nodes,links:edges,roam:true,label:{show:true,position:'right',color:'#fff',fontSize:9},force:{repulsion:120,edgeLength:[30,80]},lineStyle:{color:'rgba(255,255,255,0.1)',width:1.5,curveness:0.1}}]});
        },

        runSourceCodeSearch() { if(!this.searchQuery)return; try{let r=new RegExp(this.searchQuery,'i');this.searchResults=this.report.pages.filter(p=>r.test(p.title||'')||r.test(p.description||'')||r.test(p.url));}catch(e){alert('Invalid regex.');} },
        runCustomExtraction() { if(!this.extRule)return; this.extractionResults=this.report.pages.map(p=>{let v='';if(this.extRule.toLowerCase()==='h1'){let f=p.headings?.find(h=>h.tag==='h1');v=f?f.text:'';}else if(this.extRule.toLowerCase()==='canonical'){v=p.canonical||'';}else{v=p.url?.includes(this.extRule)?'Match':'';}return{url:p.url,value:v};}); },
        testRobotsPath() { if(!this.testerPath)return; this.testerResult=!(this.testerPath.includes('admin')||this.testerPath.includes('login')); },
        generateSitemapXML() {
            let pages=this.report.pages.filter(p=>p.status_code===200);
            let x='<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
            pages.forEach(p=>{x+='  <url>\n    <loc>'+p.url+'</loc>\n    <changefreq>'+this.sitemapFreq+'</changefreq>\n    <priority>'+this.sitemapPriority.toFixed(1)+'</priority>\n  </url>\n';});
            x+='</urlset>';this.sitemapXML=x;
        },
        copySitemapToClipboard() { navigator.clipboard.writeText(this.sitemapXML); alert('Copied!'); },
        exportReport() { if(!this.report)return;const b=new Blob([JSON.stringify(this.report,null,2)],{type:'application/json'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='crawl_'+this.url.replace(/https?:\/\//,'').replace(/[^a-zA-Z0-9]/g,'_')+'.json';a.click();URL.revokeObjectURL(a.href); },
        loadCrawlBackup(e) { let f=e.target.files[0];if(!f)return;let r=new FileReader();r.onload=(ev)=>{try{this.report=JSON.parse(ev.target.result);this.url=this.report.start_url||'';this.tab='dashboard';}catch(err){alert('Invalid JSON.');}};r.readAsText(f); },
        loadComparisonCrawl(e) { let f=e.target.files[0];if(!f)return;let r=new FileReader();r.onload=(ev)=>{try{let b=JSON.parse(ev.target.result);let bp=b.pages?.length||0;let cp=this.report.pages?.length||0;let bl=b.pages?Math.round(b.pages.reduce((a,p)=>a+(p.response_time_ms||0),0)/bp):0;let cl=this.getAverageResponseTime();this.compareDelta={pages:(cp-bp)>=0?'+'+(cp-bp):(cp-bp),responseTime:(cl-bl)>=0?'+'+(cl-bl):(cl-bl),links:(this.report.links_list?.length||0)-(b.links_list?.length||0)};}catch(err){alert('Invalid JSON.');}};r.readAsText(f); },
        normalizeUrl(url) { if(!url)return '';url=url.replace(/#.*$/,'').replace(/\/$/,'');return url.toLowerCase(); }
    };
}
</script>
@endsection
