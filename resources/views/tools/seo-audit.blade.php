@extends('layouts.app')

@section('title', 'SEO Audit Tool — Seo4ma')

@section('content')
<div class="sa-root" x-data="seoAuditApp()" x-init="init()">

    {{-- ============ HERO + URL INPUT ============ --}}
    <div class="sa-hero">
        <div class="sa-hero-bg"></div>
        <div class="sa-hero-inner">
            <div class="sa-hero-text">
                <div class="sa-hero-badge"><i class="bi bi-lightning-charge-fill"></i> SEO Audit</div>
                <h1 class="sa-hero-title">Analyze your project's SEO health</h1>
                <p class="sa-hero-sub">Get a comprehensive audit covering meta tags, headings, links, images, structured data, performance, and more — in seconds.</p>
            </div>
            <form @submit.prevent="startAudit()" class="sa-search-box">
                <div class="sa-search-inner">
                    <i class="bi bi-globe2 sa-search-icon"></i>
                    <input type="url" x-model="url" class="sa-search-input" style="background: #f3f4f6; color: #6b7280;" readonly placeholder="{{ $currentProject ? $currentProject->url : 'Create a project first' }}" required>
                    <button type="submit" :disabled="loading || !url" class="sa-search-btn">
                        <template x-if="!loading"><span><i class="bi bi-search me-1"></i> Run Audit</span></template>
                        <template x-if="loading"><span class="sa-btn-loading"><span class="sa-spinner"></span> Analyzing...</span></template>
                    </button>
                </div>
                {{-- Step progress --}}
                <div x-show="loading" x-cloak class="sa-progress">
                    <div class="sa-progress-steps">
                        <template x-for="(step, i) in progressSteps" :key="i">
                            <div class="sa-progress-step" :class="{ 'active': progressStep >= i, 'done': progressStep > i }">
                                <div class="sa-progress-dot">
                                    <template x-if="progressStep > i"><i class="bi bi-check2"></i></template>
                                    <template x-if="progressStep === i"><span class="sa-dot-pulse"></span></template>
                                </div>
                                <span class="sa-progress-label" x-text="step"></span>
                            </div>
                        </template>
                    </div>
                    <div class="sa-progress-bar"><div class="sa-progress-fill" :style="'width:' + progressPct + '%'"></div></div>
                </div>
                {{-- Error --}}
                <div x-show="error" x-cloak class="sa-error-msg">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <span x-text="error"></span>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ EMPTY STATE ============ --}}
    <div x-show="!report && !loading && !error" x-cloak class="sa-empty">
        <div class="sa-empty-icon"><i class="bi bi-radar"></i></div>
        <h3>Ready to scan</h3>
        <p>Enter a URL above to run a full SEO analysis. We check 15+ signals across meta tags, content, links, technical SEO, and performance.</p>
        <div class="sa-empty-features">
            <div class="sa-empty-feat"><i class="bi bi-tags"></i> Meta Tags</div>
            <div class="sa-empty-feat"><i class="bi bi-list-columns"></i> Headings</div>
            <div class="sa-empty-feat"><i class="bi bi-link-45deg"></i> Links</div>
            <div class="sa-empty-feat"><i class="bi bi-images"></i> Images</div>
            <div class="sa-empty-feat"><i class="bi bi-code-slash"></i> Schema</div>
            <div class="sa-empty-feat"><i class="bi bi-speedometer2"></i> Performance</div>
        </div>
    </div>

    {{-- ============ RESULTS ============ --}}
    <div x-show="report && !loading" x-cloak class="sa-results">

        {{-- Score + Stats Row --}}
        <div class="sa-stats-row">
            <div class="sa-score-card">
                <svg viewBox="0 0 120 70" class="sa-gauge-svg">
                    <path d="M10 65 A50 50 0 0 1 110 65" fill="none" stroke="var(--kick-surface-3)" stroke-width="8" stroke-linecap="round"/>
                    <path d="M10 65 A50 50 0 0 1 110 65" fill="none" :stroke="scoreColor()" stroke-width="8" stroke-linecap="round"
                          :stroke-dasharray="gaugeArcLen" :stroke-dashoffset="gaugeArcLen - (gaugeArcLen * scorePct)"
                          style="transition: stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1), stroke 0.4s;"/>
                </svg>
                <div class="sa-score-num" x-text="report.diagnostics.score"></div>
                <div class="sa-score-label">/ 100</div>
                <div class="sa-grade-badge" :style="'background:' + scoreColor() + '22; color:' + scoreColor()" x-text="scoreGrade()"></div>
            </div>
            <div class="sa-stat-cards">
                <div class="sa-stat-card sa-stat-pass">
                    <div class="sa-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="sa-stat-val" x-text="report.diagnostics.successes.length"></div>
                    <div class="sa-stat-lbl">Passed</div>
                </div>
                <div class="sa-stat-card sa-stat-warn">
                    <div class="sa-stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="sa-stat-val" x-text="report.diagnostics.warnings.length"></div>
                    <div class="sa-stat-lbl">Warnings</div>
                </div>
                <div class="sa-stat-card sa-stat-err">
                    <div class="sa-stat-icon"><i class="bi bi-x-circle-fill"></i></div>
                    <div class="sa-stat-val" x-text="report.diagnostics.errors.length"></div>
                    <div class="sa-stat-lbl">Errors</div>
                </div>
                <div class="sa-stat-card sa-stat-http">
                    <div class="sa-stat-icon"><i class="bi bi-hdd-rack"></i></div>
                    <div class="sa-stat-val" x-text="report.http_status"></div>
                    <div class="sa-stat-lbl">HTTP</div>
                </div>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="sa-action-bar">
            <div class="sa-audited-url"><i class="bi bi-link-45deg"></i> <span x-text="report.url"></span></div>
            <div class="sa-actions">
                <button @click="copySummary()" class="sa-action-btn" title="Copy summary"><i class="bi bi-clipboard"></i> Copy</button>
                <button @click="exportReport()" class="sa-action-btn" title="Export JSON"><i class="bi bi-download"></i> Export</button>
                <button @click="startAudit()" class="sa-action-btn sa-action-btn-accent" title="Re-run"><i class="bi bi-arrow-clockwise"></i> Re-scan</button>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="sa-tabs">
            <template x-for="tab in tabs" :key="tab.id">
                <button class="sa-tab" :class="{ active: activeTab === tab.id }" @click="activeTab = tab.id">
                    <i :class="tab.icon"></i>
                    <span x-text="tab.name"></span>
                    <span x-show="tab.badge !== undefined" class="sa-tab-badge" x-text="tab.badge !== undefined ? getTabBadge(tab.id) : ''" x-cloak></span>
                </button>
            </template>
        </div>

        {{-- ====== TAB: OVERVIEW ====== --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div class="sa-grid-2">
                {{-- SERP Preview --}}
                <div class="sa-card sa-card-serp">
                    <div class="sa-card-head"><i class="bi bi-google"></i> SERP Preview</div>
                    <div class="sa-serp-preview">
                        <div class="sa-serp-url" x-text="report.url"></div>
                        <div class="sa-serp-title" x-text="report.general.title.text || 'No title'"></div>
                        <div class="sa-serp-desc" x-text="report.general.description.text || 'No description available for this page.'"></div>
                    </div>
                </div>
                {{-- Quick Health --}}
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-heart-pulse"></i> Quick Health</div>
                    <div class="sa-health-grid">
                        <div class="sa-health-item">
                            <i :class="report.general.is_https ? 'bi bi-lock-fill sa-ok' : 'bi bi-unlock-fill sa-bad'"></i>
                            <span x-text="report.general.is_https ? 'HTTPS' : 'No HTTPS'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.general.viewport ? 'bi bi-phone sa-ok' : 'bi bi-phone sa-bad'"></i>
                            <span x-text="report.general.viewport ? 'Mobile-ready' : 'No viewport'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.general.canonical ? 'bi bi-bookmark-check sa-ok' : 'bi bi-bookmark-x sa-bad'"></i>
                            <span x-text="report.general.canonical ? 'Canonical set' : 'No canonical'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.schemas.length ? 'bi bi-code-square sa-ok' : 'bi bi-code-square sa-warn'"></i>
                            <span x-text="report.schemas.length ? report.schemas.length + ' Schema(s)' : 'No schema'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.general.language ? 'bi bi-translate sa-ok' : 'bi bi-translate sa-bad'"></i>
                            <span x-text="report.general.language ? 'Lang: ' + report.general.language : 'No lang attr'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.robots_txt.exists ? 'bi bi-file-earmark-text sa-ok' : 'bi bi-file-earmark-x sa-bad'"></i>
                            <span x-text="report.robots_txt.exists ? 'robots.txt' : 'No robots.txt'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.sitemap.exists ? 'bi bi-diagram-3 sa-ok' : 'bi bi-diagram-3 sa-bad'"></i>
                            <span x-text="report.sitemap.exists ? 'Sitemap (' + report.sitemap.url_count + ')' : 'No sitemap'"></span>
                        </div>
                        <div class="sa-health-item">
                            <i :class="report.general.favicon ? 'bi bi-star-fill sa-ok' : 'bi bi-star sa-bad'"></i>
                            <span x-text="report.general.favicon ? 'Favicon' : 'No favicon'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Diagnostic findings --}}
            <template x-if="report.diagnostics.errors.length">
                <div class="sa-card sa-card-err">
                    <div class="sa-card-head sa-head-err"><i class="bi bi-x-circle-fill"></i> Errors <span class="sa-count" x-text="report.diagnostics.errors.length"></span></div>
                    <template x-for="(item, i) in report.diagnostics.errors" :key="'e'+i">
                        <div class="sa-finding">
                            <div class="sa-finding-icon sa-icon-err"><i class="bi bi-x"></i></div>
                            <div class="sa-finding-body">
                                <div class="sa-finding-cat" x-text="item.category"></div>
                                <div class="sa-finding-title" x-text="item.title"></div>
                                <div class="sa-finding-desc" x-text="item.description"></div>
                                <div x-show="item.recommendation" class="sa-finding-fix"><i class="bi bi-lightbulb"></i> <span x-text="item.recommendation"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="report.diagnostics.warnings.length">
                <div class="sa-card sa-card-warn">
                    <div class="sa-card-head sa-head-warn"><i class="bi bi-exclamation-triangle-fill"></i> Warnings <span class="sa-count" x-text="report.diagnostics.warnings.length"></span></div>
                    <template x-for="(item, i) in report.diagnostics.warnings" :key="'w'+i">
                        <div class="sa-finding">
                            <div class="sa-finding-icon sa-icon-warn"><i class="bi bi-exclamation"></i></div>
                            <div class="sa-finding-body">
                                <div class="sa-finding-cat" x-text="item.category"></div>
                                <div class="sa-finding-title" x-text="item.title"></div>
                                <div class="sa-finding-desc" x-text="item.description"></div>
                                <div x-show="item.recommendation" class="sa-finding-fix"><i class="bi bi-lightbulb"></i> <span x-text="item.recommendation"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="report.diagnostics.successes.length">
                <div class="sa-card sa-card-pass">
                    <div class="sa-card-head sa-head-pass"><i class="bi bi-check-circle-fill"></i> Passed <span class="sa-count" x-text="report.diagnostics.successes.length"></span></div>
                    <div class="sa-passed-grid">
                        <template x-for="(item, i) in report.diagnostics.successes" :key="'s'+i">
                            <div class="sa-passed-item">
                                <i class="bi bi-check2-circle sa-ok"></i>
                                <div>
                                    <div class="sa-finding-title" x-text="item.title"></div>
                                    <div class="sa-finding-desc" x-text="item.description"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- ====== TAB: META ====== --}}
        <div x-show="activeTab === 'meta'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div class="sa-grid-2">
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-pencil-square"></i> Title Tag
                        <span class="sa-status-pill" :class="'sa-pill-' + report.general.title.status" x-text="report.general.title.status"></span>
                    </div>
                    <div class="sa-meta-value" x-text="report.general.title.text || '— Missing —'"></div>
                    <div class="sa-meta-detail">
                        <span x-text="report.general.title.length + ' characters'"></span>
                        <span class="sa-meta-range">Optimal: 30–60</span>
                    </div>
                    <div class="sa-char-bar"><div class="sa-char-fill" :class="report.general.title.status === 'optimal' ? 'sa-fill-ok' : 'sa-fill-warn'" :style="'width:' + Math.min(report.general.title.length / 70 * 100, 100) + '%'"></div></div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-card-text"></i> Meta Description
                        <span class="sa-status-pill" :class="'sa-pill-' + report.general.description.status" x-text="report.general.description.status"></span>
                    </div>
                    <div class="sa-meta-value" x-text="report.general.description.text || '— Missing —'"></div>
                    <div class="sa-meta-detail">
                        <span x-text="report.general.description.length + ' characters'"></span>
                        <span class="sa-meta-range">Optimal: 110–160</span>
                    </div>
                    <div class="sa-char-bar"><div class="sa-char-fill" :class="report.general.description.status === 'optimal' ? 'sa-fill-ok' : 'sa-fill-warn'" :style="'width:' + Math.min(report.general.description.length / 170 * 100, 100) + '%'"></div></div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-link"></i> Canonical URL</div>
                    <div class="sa-meta-value sa-mono" x-text="report.general.canonical || '— Not set —'"></div>
                    <div x-show="report.general.canonical" class="sa-meta-detail">
                        <span :class="report.general.canonical_matches ? 'sa-ok' : 'sa-warn'" x-text="report.general.canonical_matches ? 'Matches current URL' : 'Mismatch with current URL'"></span>
                    </div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-info-circle"></i> Page Details</div>
                    <div class="sa-detail-grid">
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Language</span><span x-text="report.general.language || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Viewport</span><span :class="report.general.viewport ? 'sa-ok' : 'sa-bad'" x-text="report.general.viewport ? 'Configured' : 'Missing'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Favicon</span><span :class="report.general.favicon ? 'sa-ok' : 'sa-bad'" x-text="report.general.favicon ? 'Present' : 'Missing'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Author</span><span x-text="report.general.author || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Generator</span><span x-text="report.general.generator || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Robots Meta</span><span x-text="report.general.robots || 'None (index allowed)'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">X-Robots-Tag</span><span x-text="report.general.x_robots || 'Not set'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Server</span><span x-text="report.general.server || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Content-Type</span><span x-text="report.general.content_type || '—'"></span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== TAB: CONTENT ====== --}}
        <div x-show="activeTab === 'content'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div class="sa-grid-2">
                {{-- Headings --}}
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-list-columns"></i> Headings Structure</div>
                    <div class="sa-heading-stats">
                        <template x-for="(count, tag) in report.headings.counts" :key="tag">
                            <div class="sa-heading-stat">
                                <div class="sa-heading-tag" x-text="tag.toUpperCase()"></div>
                                <div class="sa-heading-count" x-text="count"></div>
                            </div>
                        </template>
                    </div>
                    <div class="sa-list-scroll">
                        <template x-for="(h, i) in report.headings.list" :key="i">
                            <div class="sa-heading-row">
                                <span class="sa-heading-badge" :class="'sa-hb-' + h.tag" x-text="h.tag.toUpperCase()"></span>
                                <span class="sa-heading-text" x-text="h.text || '(empty)'"></span>
                            </div>
                        </template>
                        <div x-show="!report.headings.list.length" class="sa-no-data">No headings found</div>
                    </div>
                </div>
                {{-- Images --}}
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-images"></i> Images</div>
                    <div class="sa-img-stats">
                        <div class="sa-img-stat"><span class="sa-img-val" x-text="report.images.total"></span><span class="sa-img-lbl">Total</span></div>
                        <div class="sa-img-stat"><span class="sa-img-val sa-ok" x-text="report.images.with_alt"></span><span class="sa-img-lbl">With Alt</span></div>
                        <div class="sa-img-stat"><span class="sa-img-val sa-bad" x-text="report.images.missing_alt"></span><span class="sa-img-lbl">Missing Alt</span></div>
                    </div>
                    <div class="sa-list-scroll">
                        <template x-for="(img, i) in report.images.list" :key="i">
                            <div class="sa-img-row">
                                <i :class="img.has_alt ? 'bi bi-check-circle-fill sa-ok' : 'bi bi-x-circle-fill sa-bad'" class="sa-img-icon"></i>
                                <div class="sa-img-info">
                                    <div class="sa-img-src" x-text="img.src"></div>
                                    <div x-show="img.alt" class="sa-img-alt" x-text="'alt: ' + img.alt"></div>
                                    <div x-show="!img.alt" class="sa-img-noalt">No alt text</div>
                                </div>
                            </div>
                        </template>
                        <div x-show="!report.images.list.length" class="sa-no-data">No images found</div>
                    </div>
                </div>
            </div>
            {{-- Social Tags --}}
            <div class="sa-card" style="margin-top:1rem">
                <div class="sa-card-head"><i class="bi bi-share"></i> Social Tags</div>
                <div class="sa-grid-2" style="margin-top:.75rem">
                    <div>
                        <div class="sa-social-label">Open Graph</div>
                        <div class="sa-social-box">
                            <template x-for="(val, key) in report.social.og" :key="key">
                                <div class="sa-social-row">
                                    <span class="sa-social-key" x-text="'og:' + key"></span>
                                    <span class="sa-social-val" x-text="val || '—'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <div class="sa-social-label">Twitter Card</div>
                        <div class="sa-social-box">
                            <template x-for="(val, key) in report.social.twitter" :key="key">
                                <div class="sa-social-row">
                                    <span class="sa-social-key" x-text="'twitter:' + key"></span>
                                    <span class="sa-social-val" x-text="val || '—'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== TAB: LINKS ====== --}}
        <div x-show="activeTab === 'links'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div class="sa-stat-cards" style="margin-bottom:1rem">
                <div class="sa-stat-card"><div class="sa-stat-icon"><i class="bi bi-link-45deg"></i></div><div class="sa-stat-val" x-text="report.links.total"></div><div class="sa-stat-lbl">Total</div></div>
                <div class="sa-stat-card sa-stat-pass"><div class="sa-stat-icon"><i class="bi bi-house-fill"></i></div><div class="sa-stat-val" x-text="report.links.internal"></div><div class="sa-stat-lbl">Internal</div></div>
                <div class="sa-stat-card" style="--sa-accent:var(--kick-purple)"><div class="sa-stat-icon"><i class="bi bi-box-arrow-up-right"></i></div><div class="sa-stat-val" x-text="report.links.external"></div><div class="sa-stat-lbl">External</div></div>
                <div class="sa-stat-card sa-stat-warn"><div class="sa-stat-icon"><i class="bi bi-slash-circle"></i></div><div class="sa-stat-val" x-text="report.links.nofollow"></div><div class="sa-stat-lbl">Nofollow</div></div>
            </div>
            <div class="sa-card">
                <div class="sa-card-head">
                    <i class="bi bi-list-ul"></i> All Links
                    <div class="sa-link-filters">
                        <button class="sa-filter-btn" :class="{active: linkFilter==='all'}" @click="linkFilter='all'">All</button>
                        <button class="sa-filter-btn" :class="{active: linkFilter==='internal'}" @click="linkFilter='internal'">Internal</button>
                        <button class="sa-filter-btn" :class="{active: linkFilter==='external'}" @click="linkFilter='external'">External</button>
                    </div>
                </div>
                <div class="sa-list-scroll sa-list-tall">
                    <template x-for="(link, i) in filteredLinks" :key="i">
                        <div class="sa-link-row">
                            <i :class="link.is_internal ? 'bi bi-house-fill sa-ok' : 'bi bi-box-arrow-up-right'" :style="!link.is_internal ? 'color:var(--kick-purple)' : ''" class="sa-link-icon"></i>
                            <div class="sa-link-info">
                                <div class="sa-link-href" x-text="link.href"></div>
                                <div class="sa-link-text" x-text="link.text"></div>
                            </div>
                            <div class="sa-link-badges">
                                <span class="sa-link-type-badge" :class="link.is_internal ? 'sa-type-int' : 'sa-type-ext'" x-text="link.is_internal ? 'INT' : 'EXT'"></span>
                                <span x-show="link.is_nofollow" class="sa-link-type-badge sa-type-nf">NF</span>
                            </div>
                        </div>
                    </template>
                    <div x-show="!filteredLinks.length" class="sa-no-data">No links match filter</div>
                </div>
            </div>
        </div>

        {{-- ====== TAB: TECHNICAL ====== --}}
        <div x-show="activeTab === 'technical'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div class="sa-grid-2">
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-shield-check"></i> Security & Protocol</div>
                    <div class="sa-detail-grid">
                        <div class="sa-detail-row"><span class="sa-detail-lbl">HTTPS</span><span :class="report.technical.is_https ? 'sa-ok' : 'sa-bad'" x-text="report.technical.is_https ? 'Enabled' : 'Not enabled'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Server</span><span x-text="report.technical.server || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">X-Powered-By</span><span x-text="report.technical.x_powered_by || '—'"></span></div>
                        <div class="sa-detail-row"><span class="sa-detail-lbl">Content-Type</span><span x-text="report.technical.content_type || '—'"></span></div>
                    </div>
                    <div x-show="report.technical.redirect_chain.length" style="margin-top:.75rem">
                        <div class="sa-detail-lbl" style="margin-bottom:.25rem">Redirect Chain</div>
                        <template x-for="(r,i) in report.technical.redirect_chain" :key="i">
                            <div class="sa-redirect-step"><i class="bi bi-arrow-right"></i> <span x-text="r"></span></div>
                        </template>
                    </div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-translate"></i> Hreflang Tags</div>
                    <template x-if="report.technical.hreflangs.length">
                        <div class="sa-list-scroll">
                            <template x-for="(hl, i) in report.technical.hreflangs" :key="i">
                                <div class="sa-hreflang-row">
                                    <span class="sa-hreflang-badge" x-text="hl.hreflang"></span>
                                    <span class="sa-hreflang-url" x-text="hl.href"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <div x-show="!report.technical.hreflangs.length" class="sa-no-data">No hreflang tags found</div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-code-square"></i> Structured Data (JSON-LD)</div>
                    <template x-if="report.schemas.length">
                        <div class="sa-list-scroll">
                            <div class="sa-schema-count"><i class="bi bi-check-circle-fill sa-ok"></i> <span x-text="report.schemas.length + ' schema(s) found'"></span></div>
                            <template x-for="(schema, i) in report.schemas" :key="i">
                                <div class="sa-schema-block">
                                    <div x-show="schema['@type']" class="sa-schema-type" x-text="schema['@type']"></div>
                                    <pre class="sa-schema-json" x-text="JSON.stringify(schema, null, 2)"></pre>
                                </div>
                            </template>
                        </div>
                    </template>
                    <div x-show="!report.schemas.length" class="sa-no-data">No structured data found</div>
                </div>
                <div class="sa-card">
                    <div class="sa-card-head"><i class="bi bi-files"></i> Robots.txt & Sitemap</div>
                    <div class="sa-detail-grid">
                        <div class="sa-detail-row">
                            <span class="sa-detail-lbl">robots.txt</span>
                            <span :class="report.robots_txt.exists ? 'sa-ok' : 'sa-bad'" x-text="report.robots_txt.exists ? 'Found' : 'Not found'"></span>
                        </div>
                        <div class="sa-detail-row">
                            <span class="sa-detail-lbl">sitemap.xml</span>
                            <span :class="report.sitemap.exists ? 'sa-ok' : 'sa-bad'" x-text="report.sitemap.exists ? report.sitemap.url_count + ' URLs' : 'Not found'"></span>
                        </div>
                    </div>
                    <div style="margin-top:.75rem">
                        <div class="sa-detail-lbl" style="margin-bottom:.25rem">Pagination</div>
                        <template x-if="Object.keys(report.technical.pagination).length">
                            <div>
                                <div x-show="report.technical.pagination.prev" style="font-size:.8rem;margin-bottom:.2rem"><span class="sa-detail-lbl" style="display:inline;margin:0 .5rem 0 0">Prev:</span><span x-text="report.technical.pagination.prev"></span></div>
                                <div x-show="report.technical.pagination.next" style="font-size:.8rem"><span class="sa-detail-lbl" style="display:inline;margin:0 .5rem 0 0">Next:</span><span x-text="report.technical.pagination.next"></span></div>
                            </div>
                        </template>
                        <div x-show="!Object.keys(report.technical.pagination).length" style="font-size:.8rem;color:var(--kick-text-secondary)">No pagination tags</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== TAB: PERFORMANCE ====== --}}
        <div x-show="activeTab === 'performance'" x-transition:enter="sa-fade-in" class="sa-tab-content">
            <div x-show="report.pagespeed.available">
                <div class="sa-stat-cards" style="margin-bottom:1rem">
                    <template x-for="(score, key) in report.pagespeed.scores" :key="key">
                        <div class="sa-stat-card" style="text-align:center">
                            <svg viewBox="0 0 80 45" class="sa-mini-gauge">
                                <path d="M5 42 A35 35 0 0 1 75 42" fill="none" stroke="var(--kick-surface-3)" stroke-width="5" stroke-linecap="round"/>
                                <path d="M5 42 A35 35 0 0 1 75 42" fill="none" :stroke="psColor(score)" stroke-width="5" stroke-linecap="round"
                                      :stroke-dasharray="110" :stroke-dashoffset="110 - (110 * (score||0) / 100)"
                                      style="transition: stroke-dashoffset 1s ease;"/>
                                <text x="40" y="38" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" x-text="score ?? '—'"></text>
                            </svg>
                            <div class="sa-stat-lbl" style="text-transform:capitalize;margin-top:.25rem" x-text="key.replace('_',' ')"></div>
                        </div>
                    </template>
                </div>
                <div class="sa-card" style="margin-bottom:1rem">
                    <div class="sa-card-head"><i class="bi bi-activity"></i> Core Web Vitals</div>
                    <div class="sa-vitals-grid">
                        <template x-for="(val, key) in report.pagespeed.core_vitals" :key="key">
                            <div class="sa-vital-item">
                                <div class="sa-vital-key" x-text="key.toUpperCase()"></div>
                                <div class="sa-vital-val" x-text="val !== null ? (val / 1000).toFixed(2) + 's' : 'N/A'"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <template x-if="report.pagespeed.opportunities && report.pagespeed.opportunities.length">
                    <div class="sa-card">
                        <div class="sa-card-head"><i class="bi bi-lightbulb"></i> Opportunities</div>
                        <template x-for="(opp, i) in report.pagespeed.opportunities" :key="i">
                            <div class="sa-opp-row">
                                <span class="sa-opp-title" x-text="opp.title"></span>
                                <span class="sa-opp-savings" x-text="'~' + opp.savings_ms + 'ms'"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div x-show="!report.pagespeed.available" class="sa-card">
                <div class="sa-no-data">
                    <i class="bi bi-info-circle" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                    <span x-text="report.pagespeed.reason || 'PageSpeed Insights data not available.'"></span>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
[x-cloak]{display:none!important}
.sa-root{max-width:1440px;margin:0 auto;padding:0 .5rem}

/* ── Hero ── */
.sa-hero{position:relative;border-radius:var(--kick-radius-xl);overflow:hidden;margin-bottom:1.5rem;border:1px solid var(--kick-border-subtle)}
.sa-hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,rgba(83,252,24,.06) 0%,rgba(20,184,166,.04) 50%,rgba(83,35,247,.03) 100%);pointer-events:none}
.sa-hero-bg::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 20% 100%,rgba(83,252,24,.08),transparent)}
.sa-hero-inner{position:relative;padding:2rem 2.5rem}
.sa-hero-badge{display:inline-flex;align-items:center;gap:.375rem;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--kick-green);background:rgba(83,252,24,.1);padding:.3rem .75rem;border-radius:var(--kick-radius-pill);margin-bottom:.75rem}
.sa-hero-title{font-size:1.75rem;font-weight:800;color:var(--kick-text-primary);margin-bottom:.375rem;line-height:1.2}
.sa-hero-sub{font-size:.875rem;color:var(--kick-text-secondary);margin:0;max-width:540px}

/* ── Search Box ── */
.sa-search-box{margin-top:1.25rem}
.sa-search-inner{display:flex;align-items:center;background:var(--kick-surface-1-solid);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-lg);overflow:hidden;transition:border-color .2s}
.sa-search-inner:focus-within{border-color:var(--kick-green);box-shadow:0 0 0 3px rgba(83,252,24,.1)}
.sa-search-icon{font-size:1.125rem;color:var(--kick-text-secondary);padding-left:1rem;flex-shrink:0}
.sa-search-input{flex:1;background:transparent;border:none;color:var(--kick-text-primary);font-size:.9375rem;padding:.875rem 1rem;outline:none}
.sa-search-input::placeholder{color:var(--kick-text-secondary)}
.sa-search-btn{background:linear-gradient(135deg,var(--kick-green),#3de014);border:none;color:#000;font-weight:700;font-size:.875rem;padding:.875rem 1.75rem;cursor:pointer;transition:filter .15s,transform .15s;white-space:nowrap}
.sa-search-btn:hover:not(:disabled){filter:brightness(1.1);transform:translateY(0)}
.sa-search-btn:disabled{opacity:.5;cursor:not-allowed}
.sa-btn-loading{display:flex;align-items:center;gap:.5rem}
.sa-spinner{width:14px;height:14px;border:2px solid rgba(0,0,0,.2);border-top-color:#000;border-radius:50%;animation:sa-spin .6s linear infinite}
@keyframes sa-spin{to{transform:rotate(360deg)}}

/* ── Progress ── */
.sa-progress{margin-top:1rem;animation:sa-fade-in .3s ease}
.sa-progress-steps{display:flex;gap:1.25rem;flex-wrap:wrap;margin-bottom:.75rem}
.sa-progress-step{display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--kick-text-secondary);transition:color .3s}
.sa-progress-step.active{color:var(--kick-green)}
.sa-progress-step.done{color:var(--kick-text-secondary);opacity:.6}
.sa-progress-dot{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;background:var(--kick-surface-3);transition:background .3s}
.sa-progress-step.active .sa-progress-dot{background:rgba(83,252,24,.2);color:var(--kick-green)}
.sa-progress-step.done .sa-progress-dot{background:var(--kick-green);color:#000}
.sa-dot-pulse{width:6px;height:6px;background:var(--kick-green);border-radius:50%;animation:sa-pulse .8s ease-in-out infinite alternate}
@keyframes sa-pulse{from{opacity:.3;transform:scale(.8)}to{opacity:1;transform:scale(1.2)}}
.sa-progress-bar{height:3px;background:var(--kick-surface-3);border-radius:2px;overflow:hidden}
.sa-progress-fill{height:100%;background:linear-gradient(90deg,var(--kick-green),var(--kick-teal));border-radius:2px;transition:width .6s ease}

/* ── Error ── */
.sa-error-msg{margin-top:.75rem;padding:.75rem 1rem;background:rgba(233,25,22,.08);border:1px solid rgba(233,25,22,.2);border-radius:var(--kick-radius-md);color:var(--kick-live-red);font-size:.8125rem}

/* ── Empty ── */
.sa-empty{text-align:center;padding:4rem 2rem}
.sa-empty-icon{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem;background:var(--kick-green-subtle);color:var(--kick-green);box-shadow:0 0 32px rgba(83,252,24,.1)}
.sa-empty h3{font-weight:700;color:var(--kick-text-primary);margin-bottom:.375rem}
.sa-empty p{font-size:.875rem;color:var(--kick-text-secondary);max-width:480px;margin:0 auto 1.5rem}
.sa-empty-features{display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem}
.sa-empty-feat{display:flex;align-items:center;gap:.375rem;font-size:.75rem;font-weight:600;color:var(--kick-text-secondary);background:var(--kick-surface-1);border:1px solid var(--kick-border-subtle);padding:.4rem .75rem;border-radius:var(--kick-radius-pill)}
.sa-empty-feat i{color:var(--kick-green);font-size:.875rem}

/* ── Stats Row ── */
.sa-stats-row{display:flex;gap:1rem;align-items:stretch;margin-bottom:1rem;flex-wrap:wrap}
.sa-score-card{background:var(--kick-surface-1);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-xl);padding:1.5rem;text-align:center;min-width:180px;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;overflow:hidden}
.sa-score-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--kick-green),var(--kick-teal))}
.sa-gauge-svg{width:140px;height:78px;margin-bottom:-.5rem}
.sa-score-num{font-size:2.5rem;font-weight:800;line-height:1;color:var(--kick-text-primary);margin-top:-.25rem}
.sa-score-label{font-size:.75rem;color:var(--kick-text-secondary);margin-top:.125rem}
.sa-grade-badge{display:inline-flex;align-items:center;justify-content:center;font-size:.8125rem;font-weight:800;padding:.25rem .75rem;border-radius:var(--kick-radius-pill);margin-top:.5rem}
.sa-stat-cards{display:flex;gap:.75rem;flex:1;flex-wrap:wrap}
.sa-stat-card{flex:1;min-width:100px;background:var(--kick-surface-1);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-lg);padding:1rem;text-align:center;display:flex;flex-direction:column;align-items:center;gap:.125rem;transition:border-color .2s,box-shadow .2s}
.sa-stat-card:hover{border-color:rgba(83,252,24,.15);box-shadow:0 4px 16px rgba(0,0,0,.15)}
.sa-stat-icon{font-size:1.25rem;color:var(--kick-green)}
.sa-stat-pass .sa-stat-icon{color:var(--kick-green)}
.sa-stat-warn .sa-stat-icon{color:var(--kick-amber)}
.sa-stat-err .sa-stat-icon{color:var(--kick-live-red)}
.sa-stat-http .sa-stat-icon{color:var(--kick-purple)}
.sa-stat-val{font-size:1.5rem;font-weight:800;color:var(--kick-text-primary);line-height:1.2}
.sa-stat-lbl{font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--kick-text-secondary)}

/* ── Action bar ── */
.sa-action-bar{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap}
.sa-audited-url{font-size:.8125rem;color:var(--kick-text-secondary);display:flex;align-items:center;gap:.375rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.sa-audited-url span{overflow:hidden;text-overflow:ellipsis}
.sa-actions{display:flex;gap:.5rem}
.sa-action-btn{display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;font-weight:600;padding:.4rem .75rem;border-radius:var(--kick-radius-sm);border:1px solid var(--kick-border-subtle);background:transparent;color:var(--kick-text-secondary);cursor:pointer;transition:all .15s}
.sa-action-btn:hover{background:var(--kick-surface-2);color:var(--kick-text-primary);border-color:rgba(83,252,24,.3)}
.sa-action-btn-accent{color:var(--kick-green);border-color:rgba(83,252,24,.3)}
.sa-action-btn-accent:hover{background:rgba(83,252,24,.08)}

/* ── Tabs ── */
.sa-tabs{display:flex;gap:.25rem;border-bottom:1px solid var(--kick-border-subtle);margin-bottom:1.25rem;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;padding-bottom:0}
.sa-tabs::-webkit-scrollbar{display:none}
.sa-tab{display:flex;align-items:center;gap:.375rem;padding:.7rem .875rem;border:none;background:transparent;color:var(--kick-text-secondary);font-size:.8125rem;font-weight:500;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;white-space:nowrap;position:relative}
.sa-tab:hover{color:var(--kick-text-primary)}
.sa-tab.active{color:var(--kick-green);font-weight:600;border-bottom-color:var(--kick-green)}
.sa-tab i{font-size:.9375rem}
.sa-tab-badge{font-size:.6rem;font-weight:700;background:var(--kick-surface-3);color:var(--kick-text-secondary);padding:.1rem .375rem;border-radius:var(--kick-radius-pill);min-width:18px;text-align:center}
.sa-tab.active .sa-tab-badge{background:rgba(83,252,24,.15);color:var(--kick-green)}

/* ── Cards ── */
.sa-card{background:var(--kick-surface-1);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-lg);padding:1.25rem;margin-bottom:1rem;backdrop-filter:blur(12px);transition:border-color .2s}
.sa-card:hover{border-color:rgba(83,252,24,.12)}
.sa-card-head{display:flex;align-items:center;gap:.5rem;font-size:.8125rem;font-weight:700;color:var(--kick-text-primary);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.75rem;padding-bottom:.75rem;border-bottom:1px solid var(--kick-border-subtle)}
.sa-card-head i{color:var(--kick-green);font-size:1rem}
.sa-card-err{border-color:rgba(233,25,22,.2)}
.sa-card-err .sa-card-head{border-bottom-color:rgba(233,25,22,.15)}
.sa-card-warn{border-color:rgba(245,158,11,.2)}
.sa-card-warn .sa-card-head{border-bottom-color:rgba(245,158,11,.15)}
.sa-card-pass{border-color:rgba(83,252,24,.15)}
.sa-card-pass .sa-card-head{border-bottom-color:rgba(83,252,24,.1)}
.sa-head-err{color:var(--kick-live-red)}
.sa-head-err i{color:var(--kick-live-red)}
.sa-head-warn{color:var(--kick-amber)}
.sa-head-warn i{color:var(--kick-amber)}
.sa-head-pass{color:var(--kick-green)}
.sa-head-pass i{color:var(--kick-green)}
.sa-count{font-size:.65rem;font-weight:700;background:currentColor;padding:.1rem .4rem;border-radius:var(--kick-radius-pill);opacity:.8;margin-left:auto}

/* ── Grid helpers ── */
.sa-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media(max-width:768px){.sa-grid-2{grid-template-columns:1fr}}

/* ── SERP Preview ── */
.sa-card-serp{border-color:rgba(83,252,24,.15)}
.sa-serp-preview{background:var(--kick-bg-base);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-md);padding:1rem}
.sa-serp-url{font-size:.75rem;color:var(--kick-green);margin-bottom:.25rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sa-serp-title{font-size:1.125rem;font-weight:600;color:#8ab4f8;margin-bottom:.25rem;line-height:1.3;word-break:break-word}
.sa-serp-desc{font-size:.8125rem;color:var(--kick-text-secondary);line-height:1.5;word-break:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* ── Quick Health ── */
.sa-health-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.sa-health-item{display:flex;align-items:center;gap:.5rem;font-size:.8125rem;color:var(--kick-text-primary);padding:.5rem .625rem;background:var(--kick-bg-base);border-radius:var(--kick-radius-sm);border:1px solid var(--kick-border-subtle)}
.sa-health-item i{font-size:1rem;flex-shrink:0}

/* ── Findings ── */
.sa-finding{display:flex;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-finding:last-child{border-bottom:none}
.sa-finding-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.75rem;font-weight:700;margin-top:.125rem}
.sa-icon-err{background:rgba(233,25,22,.15);color:var(--kick-live-red)}
.sa-icon-warn{background:rgba(245,158,11,.15);color:var(--kick-amber)}
.sa-finding-body{flex:1;min-width:0}
.sa-finding-cat{font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--kick-text-secondary);margin-bottom:.125rem}
.sa-finding-title{font-size:.8125rem;font-weight:600;color:var(--kick-text-primary)}
.sa-finding-desc{font-size:.75rem;color:var(--kick-text-secondary);margin-top:.125rem}
.sa-finding-fix{font-size:.7rem;color:var(--kick-green);margin-top:.375rem;display:flex;gap:.25rem}
.sa-finding-fix i{flex-shrink:0}

/* ── Passed grid ── */
.sa-passed-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
@media(max-width:768px){.sa-passed-grid{grid-template-columns:1fr}}
.sa-passed-item{display:flex;gap:.5rem;padding:.5rem .625rem;background:var(--kick-bg-base);border-radius:var(--kick-radius-sm);border:1px solid var(--kick-border-subtle)}
.sa-passed-item i{font-size:1rem;color:var(--kick-green);flex-shrink:0;margin-top:.125rem}
.sa-passed-item .sa-finding-title{font-size:.75rem}
.sa-passed-item .sa-finding-desc{font-size:.6875rem}

/* ── Meta tab ── */
.sa-meta-value{font-size:.875rem;color:var(--kick-text-primary);word-break:break-word;line-height:1.5;margin-bottom:.5rem}
.sa-meta-detail{font-size:.7rem;color:var(--kick-text-secondary);display:flex;align-items:center;gap:.75rem;margin-bottom:.375rem}
.sa-meta-range{font-size:.65rem;color:var(--kick-text-secondary);opacity:.6}
.sa-mono{font-family:'SF Mono',SFMono-Regular,Consolas,monospace;font-size:.8125rem}
.sa-char-bar{height:4px;background:var(--kick-surface-3);border-radius:2px;overflow:hidden}
.sa-char-fill{height:100%;border-radius:2px;transition:width .8s ease}
.sa-fill-ok{background:var(--kick-green)}
.sa-fill-warn{background:var(--kick-amber)}
.sa-status-pill{font-size:.6rem;font-weight:700;text-transform:uppercase;padding:.15rem .5rem;border-radius:var(--kick-radius-pill);margin-left:auto}
.sa-pill-optimal{background:rgba(83,252,24,.12);color:var(--kick-green)}
.sa-pill-short{background:rgba(245,158,11,.12);color:var(--kick-amber)}
.sa-pill-long{background:rgba(245,158,11,.12);color:var(--kick-amber)}
.sa-pill-missing{background:rgba(233,25,22,.12);color:var(--kick-live-red)}

/* ── Detail grid ── */
.sa-detail-grid{display:flex;flex-direction:column;gap:.375rem}
.sa-detail-row{display:flex;justify-content:space-between;align-items:center;padding:.375rem 0;border-bottom:1px solid rgba(43,47,53,.4);font-size:.8125rem}
.sa-detail-row:last-child{border-bottom:none}
.sa-detail-lbl{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--kick-text-secondary)}

/* ── Headings ── */
.sa-heading-stats{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem}
.sa-heading-stat{text-align:center;min-width:44px}
.sa-heading-tag{font-size:.65rem;font-weight:800;color:var(--kick-green);background:var(--kick-green-subtle);padding:.15rem .4rem;border-radius:3px;margin-bottom:.125rem}
.sa-heading-count{font-size:.875rem;font-weight:700;color:var(--kick-text-primary)}
.sa-heading-row{display:flex;align-items:flex-start;gap:.5rem;padding:.375rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-heading-row:last-child{border-bottom:none}
.sa-heading-badge{font-size:.5625rem;font-weight:800;padding:.125rem .375rem;border-radius:3px;flex-shrink:0;margin-top:.125rem}
.sa-hb-h1{background:rgba(83,252,24,.15);color:var(--kick-green)}
.sa-hb-h2{background:rgba(20,184,166,.15);color:var(--kick-teal)}
.sa-hb-h3{background:rgba(83,35,247,.15);color:var(--kick-purple)}
.sa-hb-h4,.sa-hb-h5,.sa-hb-h6{background:var(--kick-surface-3);color:var(--kick-text-secondary)}
.sa-heading-text{font-size:.8125rem;color:var(--kick-text-primary);word-break:break-word}

/* ── Images ── */
.sa-img-stats{display:flex;gap:1rem;margin-bottom:.75rem;justify-content:center}
.sa-img-stat{text-align:center}
.sa-img-val{display:block;font-size:1.25rem;font-weight:700}
.sa-img-lbl{font-size:.6rem;text-transform:uppercase;letter-spacing:.04em;color:var(--kick-text-secondary)}
.sa-img-row{display:flex;align-items:flex-start;gap:.5rem;padding:.375rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-img-row:last-child{border-bottom:none}
.sa-img-icon{font-size:.875rem;flex-shrink:0;margin-top:.2rem}
.sa-img-info{flex:1;min-width:0}
.sa-img-src{font-size:.6875rem;color:var(--kick-text-secondary);word-break:break-all;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sa-img-alt{font-size:.75rem;color:var(--kick-text-primary);margin-top:.0625rem}
.sa-img-noalt{font-size:.7rem;color:var(--kick-live-red);margin-top:.0625rem}

/* ── Social ── */
.sa-social-label{font-size:.7rem;font-weight:700;color:var(--kick-text-secondary);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.375rem}
.sa-social-box{background:var(--kick-bg-base);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-md);padding:.75rem}
.sa-social-row{display:flex;gap:.5rem;padding:.25rem 0;border-bottom:1px solid rgba(43,47,53,.3);font-size:.75rem}
.sa-social-row:last-child{border-bottom:none}
.sa-social-key{font-size:.65rem;font-weight:700;color:var(--kick-green);min-width:80px;flex-shrink:0}
.sa-social-val{color:var(--kick-text-primary);word-break:break-word}

/* ── Links ── */
.sa-link-filters{display:flex;gap:.25rem;margin-left:auto}
.sa-filter-btn{font-size:.6875rem;font-weight:600;padding:.25rem .625rem;border-radius:var(--kick-radius-pill);border:1px solid var(--kick-border-subtle);background:transparent;color:var(--kick-text-secondary);cursor:pointer;transition:all .15s}
.sa-filter-btn:hover{border-color:rgba(83,252,24,.3);color:var(--kick-text-primary)}
.sa-filter-btn.active{background:rgba(83,252,24,.1);border-color:var(--kick-green);color:var(--kick-green)}
.sa-link-row{display:flex;align-items:flex-start;gap:.625rem;padding:.5rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-link-row:last-child{border-bottom:none}
.sa-link-icon{font-size:.875rem;flex-shrink:0;margin-top:.2rem}
.sa-link-info{flex:1;min-width:0}
.sa-link-href{font-size:.6875rem;color:var(--kick-text-secondary);word-break:break-all;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sa-link-text{font-size:.8125rem;color:var(--kick-text-primary);margin-top:.0625rem}
.sa-link-badges{display:flex;gap:.25rem;flex-shrink:0}
.sa-link-type-badge{font-size:.5625rem;font-weight:700;padding:.125rem .375rem;border-radius:3px}
.sa-type-int{background:rgba(83,252,24,.12);color:var(--kick-green)}
.sa-type-ext{background:rgba(83,35,247,.12);color:var(--kick-purple)}
.sa-type-nf{background:rgba(245,158,11,.12);color:var(--kick-amber)}

/* ── Scrollable lists ── */
.sa-list-scroll{max-height:360px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--kick-border-muted) transparent}
.sa-list-tall{max-height:500px}
.sa-no-data{text-align:center;padding:1.5rem;color:var(--kick-text-secondary);font-size:.8125rem}

/* ── Technical ── */
.sa-redirect-step{font-size:.75rem;color:var(--kick-amber);margin-bottom:.125rem;word-break:break-all}
.sa-hreflang-row{display:flex;align-items:center;gap:.5rem;padding:.375rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-hreflang-row:last-child{border-bottom:none}
.sa-hreflang-badge{font-size:.625rem;font-weight:700;background:var(--kick-green-subtle);color:var(--kick-green);padding:.125rem .375rem;border-radius:3px;flex-shrink:0}
.sa-hreflang-url{font-size:.75rem;color:var(--kick-text-primary);word-break:break-all}
.sa-schema-count{font-size:.8125rem;font-weight:600;color:var(--kick-text-primary);margin-bottom:.5rem;display:flex;align-items:center;gap:.375rem}
.sa-schema-block{background:var(--kick-bg-base);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-sm);padding:.75rem;margin-bottom:.5rem}
.sa-schema-type{font-size:.7rem;font-weight:700;color:var(--kick-green);margin-bottom:.375rem}
.sa-schema-json{font-size:.6875rem;color:var(--kick-text-primary);font-family:'SF Mono',monospace;white-space:pre-wrap;word-break:break-word;margin:0;max-height:200px;overflow-y:auto}

/* ── Performance ── */
.sa-mini-gauge{width:90px;height:50px}
.sa-vitals-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:.5rem}
.sa-vital-item{background:var(--kick-bg-base);border:1px solid var(--kick-border-subtle);border-radius:var(--kick-radius-md);padding:.75rem;text-align:center}
.sa-vital-key{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--kick-text-secondary);margin-bottom:.25rem}
.sa-vital-val{font-size:1rem;font-weight:700;color:var(--kick-text-primary)}
.sa-opp-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--kick-border-subtle)}
.sa-opp-row:last-child{border-bottom:none}
.sa-opp-title{font-size:.8125rem;color:var(--kick-text-primary)}
.sa-opp-savings{font-size:.75rem;font-weight:600;color:var(--kick-amber);flex-shrink:0;margin-left:.5rem}

/* ── Color helpers ── */
.sa-ok{color:var(--kick-green)}
.sa-warn{color:var(--kick-amber)}
.sa-bad{color:var(--kick-live-red)}

/* ── Fade in animation ── */
.sa-fade-in{animation:saFadeIn .3s ease}
@keyframes saFadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* ── Responsive ── */
@media(max-width:640px){
    .sa-hero-inner{padding:1.25rem}
    .sa-hero-title{font-size:1.25rem}
    .sa-search-btn{padding:.875rem 1rem;font-size:.8125rem}
    .sa-stats-row{flex-direction:column}
    .sa-stat-cards{flex-wrap:wrap}
    .sa-stat-card{min-width:calc(50% - .375rem)}
    .sa-health-grid{grid-template-columns:1fr}
    .sa-passed-grid{grid-template-columns:1fr}
}
</style>

<script>
function seoAuditApp() {
    return {
        url: '{{ $currentProject?->url ?? '' }}',
        loading: false,
        error: null,
        report: null,
        activeTab: 'overview',
        linkFilter: 'all',
        progressStep: -1,
        progressPct: 0,
        progressSteps: ['Fetching page', 'Parsing HTML', 'Analyzing SEO', 'Scoring'],
        progressTimer: null,

        tabs: [
            { id: 'overview', name: 'Overview', icon: 'bi bi-grid-1x2' },
            { id: 'meta', name: 'Meta Tags', icon: 'bi bi-tags' },
            { id: 'content', name: 'Content', icon: 'bi bi-file-text' },
            { id: 'links', name: 'Links', icon: 'bi bi-link-45deg' },
            { id: 'technical', name: 'Technical', icon: 'bi bi-gear' },
            { id: 'performance', name: 'Performance', icon: 'bi bi-speedometer2' },
        ],

        get gaugeArcLen() { return 157; },
        get scorePct() { return this.report ? this.report.diagnostics.score / 100 : 0; },

        get filteredLinks() {
            if (!this.report) return [];
            const links = this.report.links.list || [];
            if (this.linkFilter === 'all') return links;
            return links.filter(l => this.linkFilter === 'internal' ? l.is_internal : !l.is_internal);
        },

        getTabBadge(tabId) {
            if (!this.report) return '';
            switch (tabId) {
                case 'overview': return this.report.diagnostics.errors.length + this.report.diagnostics.warnings.length;
                case 'meta': return null;
                case 'content': return (this.report.headings.list || []).length;
                case 'links': return this.report.links.total || 0;
                case 'technical': return this.report.schemas.length || 0;
                case 'performance': return this.report.pagespeed && this.report.pagespeed.available ? '!' : null;
                default: return null;
            }
        },

        init() {
            const saved = sessionStorage.getItem('seo_audit_url');
            if (saved) this.url = saved;
        },

        startAudit() {
            if (!this.url) return;
            this.loading = true;
            this.error = null;
            this.report = null;
            this.activeTab = 'overview';
            this.progressStep = 0;
            this.progressPct = 0;
            sessionStorage.setItem('seo_audit_url', this.url);

            // Animate progress
            let step = 0;
            this.progressTimer = setInterval(() => {
                step++;
                if (step <= 3) { this.progressStep = step; }
                this.progressPct = Math.min(this.progressPct + (100 / 16), 92);
            }, 1500);

            fetch('{{ route("tools.seo-audit.analyze") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ url: this.url }),
            })
            .then(r => r.json())
            .then(data => {
                clearInterval(this.progressTimer);
                this.progressPct = 100;
                this.progressStep = 4;
                if (data.error) {
                    this.error = data.error;
                    this.loading = false;
                    return;
                }
                setTimeout(() => {
                    this.report = data;
                    this.loading = false;
                }, 400);
            })
            .catch(err => {
                clearInterval(this.progressTimer);
                this.error = 'Network error: ' + err.message;
                this.loading = false;
            });
        },

        scoreColor() {
            if (!this.report) return 'var(--kick-text-secondary)';
            const s = this.report.diagnostics.score;
            if (s >= 80) return 'var(--kick-green)';
            if (s >= 60) return 'var(--kick-amber)';
            return 'var(--kick-live-red)';
        },

        scoreGrade() {
            if (!this.report) return 'N/A';
            const s = this.report.diagnostics.score;
            if (s >= 90) return 'A+';
            if (s >= 80) return 'A';
            if (s >= 70) return 'B';
            if (s >= 60) return 'C';
            if (s >= 50) return 'D';
            return 'F';
        },

        psColor(val) {
            if (val == null) return 'var(--kick-text-secondary)';
            if (val >= 80) return 'var(--kick-green)';
            if (val >= 50) return 'var(--kick-amber)';
            return 'var(--kick-live-red)';
        },

        copySummary() {
            if (!this.report) return;
            const r = this.report;
            const lines = [
                `SEO Audit: ${r.url}`,
                `Score: ${r.diagnostics.score}/100 (${this.scoreGrade()})`,
                `Passed: ${r.diagnostics.successes.length} | Warnings: ${r.diagnostics.warnings.length} | Errors: ${r.diagnostics.errors.length}`,
                '',
                '--- Errors ---',
                ...r.diagnostics.errors.map(e => `- ${e.title}: ${e.description}`),
                '',
                '--- Warnings ---',
                ...r.diagnostics.warnings.map(w => `- ${w.title}: ${w.description}`),
            ];
            navigator.clipboard.writeText(lines.join('\n')).then(() => {
                // brief visual feedback could be added
            });
        },

        exportReport() {
            if (!this.report) return;
            const blob = new Blob([JSON.stringify(this.report, null, 2)], { type: 'application/json' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            const domain = this.url ? this.url.replace(/https?:\/\//, '').replace(/[^a-zA-Z0-9]/g, '_') : 'audit';
            a.download = `seo_audit_${domain}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
        },
    };
}
</script>
@endsection
