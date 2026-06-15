@extends('layouts.app')

@section('title', 'Keyword Magic - Semrush-Style Research - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1600px; margin: 0 auto;">
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1" style="color: #fff;">
                <i class="bi bi-magic me-2" style="color: #53FC18;"></i>Keyword Magic
            </h3>
            <p class="mb-0" style="font-size: 0.875rem; color: #9DA3AF;">
                Enter a domain — we scrape the site, analyze its content, and pull real keyword suggestions from Google.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 col-xl-3" id="filtersPanel" style="display: none;">
            <div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 12px; padding: 1.25rem; position: sticky; top: 1rem;">
                <h6 style="color: #fff; font-size: 0.8125rem; font-weight: 700; margin-bottom: 1rem;">
                    <i class="bi bi-funnel me-2" style="color: #53FC18;"></i>Filters
                </h6>

                <div class="mb-3">
                    <label style="font-size: 0.7rem; color: #9DA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Volume</label>
                    <div class="d-flex flex-wrap gap-1 mt-1" id="volumeFilters">
                        <button class="filter-pill active" data-filter="volume" data-value="all" style="background: #53FC18; color: #000; border: none; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">All</button>
                        <button class="filter-pill" data-filter="volume" data-value="low" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Low</button>
                        <button class="filter-pill" data-filter="volume" data-value="medium" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Medium</button>
                        <button class="filter-pill" data-filter="volume" data-value="high" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">High</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label style="font-size: 0.7rem; color: #9DA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Difficulty</label>
                    <div class="d-flex flex-wrap gap-1 mt-1" id="difficultyFilters">
                        <button class="filter-pill active" data-filter="difficulty" data-value="all" style="background: #53FC18; color: #000; border: none; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">All</button>
                        <button class="filter-pill" data-filter="difficulty" data-value="easy" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Easy</button>
                        <button class="filter-pill" data-filter="difficulty" data-value="medium" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Medium</button>
                        <button class="filter-pill" data-filter="difficulty" data-value="hard" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Hard</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label style="font-size: 0.7rem; color: #9DA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Intent</label>
                    <div class="d-flex flex-wrap gap-1 mt-1" id="intentFilters">
                        <button class="filter-pill active" data-filter="intent" data-value="all" style="background: #53FC18; color: #000; border: none; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">All</button>
                        <button class="filter-pill" data-filter="intent" data-value="commercial" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">🛒 Commercial</button>
                        <button class="filter-pill" data-filter="intent" data-value="informational" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">ℹ️ Info</button>
                        <button class="filter-pill" data-filter="intent" data-value="transactional" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; cursor: pointer;">💰 Transactional</button>
                    </div>
                </div>

                <hr style="border-color: #2B2F35; margin: 1rem 0;">

                <div class="d-flex align-items-center justify-content-between">
                    <span style="font-size: 0.75rem; color: #9DA3AF;"><span id="filterCount">0</span> keywords shown</span>
                    <button id="clearFiltersBtn" style="background: none; border: none; color: #53FC18; font-size: 0.7rem; font-weight: 600; cursor: pointer;">Clear</button>
                </div>
            </div>
        </div>

        <div class="col" id="mainCol">
            <div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 12px; padding: 1.5rem;">
                <form id="kwForm" method="POST" action="{{ route('tools.keyword-magic') }}">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold mb-1" style="color: #fff; font-size: 0.8125rem;">
                                <i class="bi bi-globe me-1" style="color: #53FC18;"></i> Domain
                            </label>
                            <input type="text" name="domain" id="inputDomain" class="form-control shadow-sm rounded-3" style="background: #1a1a1a; border: 1px solid #2B2F35; color: #9DA3AF; font-size: 0.875rem; padding: 0.6rem 1rem;" value="{{ $currentProject ? (parse_url($currentProject->url, PHP_URL_HOST) ?? $currentProject->name) : '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1" style="color: #fff; font-size: 0.8125rem;">
                                <i class="bi bi-translate me-1" style="color: #53FC18;"></i> Language
                            </label>
                            <select name="language" class="form-select shadow-sm rounded-3" style="background: #0B0E0F; border: 1px solid #2B2F35; color: #fff; font-size: 0.8125rem; padding: 0.6rem 1rem;">
                                @foreach($languages as $code => $label)
                                    <option value="{{ $code }}" {{ ($language ?? 'fr') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" id="generateBtn" class="btn w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #53FC18 0%, #00E701 100%); color: #000; border: none; font-size: 0.8125rem;">
                                <i class="bi bi-magic me-1"></i> Research
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="resultsArea" style="display: none; margin-top: 1rem;">
                <div id="summaryBar" class="mb-3"></div>

                <div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 12px; overflow: hidden;">
                    <div style="border-bottom: 1px solid #2B2F35; padding: 0 0.5rem;">
                        <ul class="nav nav-tabs border-0" id="kwTabs" role="tablist" style="gap: 0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active border-0 rounded-0" id="tab-overview" data-bs-toggle="tab" data-bs-target="#pane-overview" type="button" role="tab" style="color: #9DA3AF; font-size: 0.75rem; font-weight: 600; padding: 0.75rem 1rem; background: transparent; border-bottom: 2px solid transparent;">
                                    <i class="bi bi-list-ul me-1"></i> Overview
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link border-0 rounded-0" id="tab-gap" data-bs-toggle="tab" data-bs-target="#pane-gap" type="button" role="tab" style="color: #9DA3AF; font-size: 0.75rem; font-weight: 600; padding: 0.75rem 1rem; background: transparent; border-bottom: 2px solid transparent;">
                                    <i class="bi bi-arrows-expand me-1"></i> Keyword Gap
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link border-0 rounded-0" id="tab-related" data-bs-toggle="tab" data-bs-target="#pane-related" type="button" role="tab" style="color: #9DA3AF; font-size: 0.75rem; font-weight: 600; padding: 0.75rem 1rem; background: transparent; border-bottom: 2px solid transparent;">
                                    <i class="bi bi-link-45deg me-1"></i> Related
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link border-0 rounded-0" id="tab-competitors" data-bs-toggle="tab" data-bs-target="#pane-competitors" type="button" role="tab" style="color: #9DA3AF; font-size: 0.75rem; font-weight: 600; padding: 0.75rem 1rem; background: transparent; border-bottom: 2px solid transparent;">
                                    <i class="bi bi-people me-1"></i> Competitors
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link border-0 rounded-0" id="tab-trends" data-bs-toggle="tab" data-bs-target="#pane-trends" type="button" role="tab" style="color: #9DA3AF; font-size: 0.75rem; font-weight: 600; padding: 0.75rem 1rem; background: transparent; border-bottom: 2px solid transparent;">
                                    <i class="bi bi-graph-up me-1"></i> Trends
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content p-0">
                        <div class="tab-pane fade show active" id="pane-overview" role="tabpanel">
                            <div class="px-3 py-2" style="border-bottom: 1px solid #2B2F35;">
                                <div class="d-flex flex-wrap align-items-center gap-2" id="serpFilterPills">
                                    <span style="font-size: 0.65rem; color: #9DA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">SERP Features:</span>
                                    <button class="serp-pill active" data-serp="all" style="background: #53FC18; color: #000; border: none; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">All</button>
                                    <button class="serp-pill" data-serp="ads" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">Ads</button>
                                    <button class="serp-pill" data-serp="featured_snippet" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">Featured</button>
                                    <button class="serp-pill" data-serp="shopping_ads" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">Shopping</button>
                                    <button class="serp-pill" data-serp="local_pack" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">Local</button>
                                    <button class="serp-pill" data-serp="people_also_ask" style="background: #0B0E0F; color: #9DA3AF; border: 1px solid #2B2F35; border-radius: 4px; padding: 0.15rem 0.5rem; font-size: 0.65rem; font-weight: 600; cursor: pointer;">PAA</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                                <table class="table mb-0" id="kwTable" style="font-size: 0.8125rem;">
                                    <thead style="position: sticky; top: 0; z-index: 2; background: #0B0E0F;">
                                        <tr>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap;">Keyword</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: right;">Volume</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: center;">Difficulty</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: center;">Intent</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: center;">Trend</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: right;">CPC</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap; text-align: center;">Pos.</th>
                                            <th style="color: #9DA3AF; font-weight: 600; padding: 0.75rem 0.75rem; border-bottom: 1px solid #2B2F35; white-space: nowrap;">SERP</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kwBody"></tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-top: 1px solid #2B2F35;">
                                <span style="font-size: 0.7rem; color: #9DA3AF;">
                                    Showing <strong id="visibleCount" style="color: #fff;">0</strong> of <strong id="totalCount" style="color: #fff;">0</strong> keywords
                                </span>
                                <div class="d-flex gap-2">
                                    <button id="exportCsvBtn" class="btn btn-sm rounded-3" style="font-size: 0.65rem; font-weight: 600; border: 1px solid #474F54; color: #fff; background: transparent; padding: 0.25rem 0.7rem;">
                                        <i class="bi bi-download"></i> CSV
                                    </button>
                                    <button id="copyTableBtn" class="btn btn-sm rounded-3" style="font-size: 0.65rem; font-weight: 600; border: 1px solid #474F54; color: #53FC18; background: transparent; padding: 0.25rem 0.7rem;">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-gap" role="tabpanel">
                            <div class="p-4" id="gapContent">
                                <p style="color: #9DA3AF; font-size: 0.8125rem;">Enter competitor domains to see keyword gaps.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-related" role="tabpanel">
                            <div class="p-4" id="relatedContent">
                                <p style="color: #9DA3AF; font-size: 0.8125rem;">Related keywords will appear here.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-competitors" role="tabpanel">
                            <div class="p-4" id="competitorContent">
                                <p style="color: #9DA3AF; font-size: 0.8125rem;">Competitor keyword data will appear here.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-trends" role="tabpanel">
                            <div class="p-4" id="trendContent">
                                <div id="trendChart" style="width: 100%; height: 400px;"></div>
                                <div id="trendTable" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="loadingState" style="display: none; margin-top: 1rem;">
                <div style="background: #191B1F; border: 1px solid #5323F7; border-radius: 12px; padding: 2.5rem; text-align: center;">
                    <div class="spinner-border mb-3" style="color: #53FC18; width: 2.5rem; height: 2.5rem;" role="status"></div>
                    <h5 style="color: #fff; font-size: 1rem;">Analyzing domain...</h5>
                    <p style="color: #9DA3AF; font-size: 0.8125rem;">Scraping site content & fetching real keyword data from Google</p>
                </div>
            </div>

            <div id="errorState" style="display: none; margin-top: 1rem;">
                <div style="background: #191B1F; border: 1px solid #E91916; border-radius: 12px; padding: 2rem; text-align: center;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(233,25,22,0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 1.3rem; color: #E91916;"></i>
                    </div>
                    <h5 style="font-size: 0.9375rem; color: #fff; margin-bottom: 0.5rem;">Research failed</h5>
                    <p id="errorMsg" style="font-size: 0.8125rem; color: #E91916; margin-bottom: 0;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link.active {
    color: #53FC18 !important;
    border-bottom: 2px solid #53FC18 !important;
    background: transparent !important;
}
.nav-tabs .nav-link:hover {
    color: #fff !important;
    border-color: transparent;
}
#kwTable tbody tr {
    border-color: #2B2F35;
    transition: background 0.15s;
}
#kwTable tbody tr:hover {
    background: rgba(83,252,24,0.04);
}
#kwTable td {
    color: #fff;
    padding: 0.55rem 0.75rem;
    border-bottom: 1px solid #2B2F35;
    vertical-align: middle;
}
#kwTable,
#kwTable.table,
#kwTable.table > :not(caption) > * > * {
    background: #0B0E0F !important;
    border-color: #2B2F35;
}
.filter-pill.active {
    background: #53FC18 !important;
    color: #000 !important;
    border-color: #53FC18 !important;
}
.serp-pill.active {
    background: #53FC18 !important;
    color: #000 !important;
    border-color: #53FC18 !important;
}
.diff-bar {
    height: 4px;
    border-radius: 2px;
    background: #0B0E0F;
    overflow: hidden;
    width: 60px;
    display: inline-block;
    vertical-align: middle;
}
.diff-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s;
}
</style>

<script>
(function() {
    'use strict';

    var form = document.getElementById('kwForm');
    var generateBtn = document.getElementById('generateBtn');
    var resultsArea = document.getElementById('resultsArea');
    var loadingState = document.getElementById('loadingState');
    var errorState = document.getElementById('errorState');
    var errorMsg = document.getElementById('errorMsg');
    var kwBody = document.getElementById('kwBody');
    var summaryBar = document.getElementById('summaryBar');
    var visibleCount = document.getElementById('visibleCount');
    var totalCount = document.getElementById('totalCount');
    var filterCount = document.getElementById('filterCount');
    var filtersPanel = document.getElementById('filtersPanel');
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    var allKeywords = [];
    var chartInstance = null;
    var dataSource = '';

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function numberFormat(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function getDifficultyColor(d) {
        if (d <= 35) return '#53FC18';
        if (d <= 65) return '#FFA500';
        return '#E91916';
    }

    function getDifficultyLabel(d) {
        if (d <= 35) return 'Easy';
        if (d <= 65) return 'Medium';
        return 'Hard';
    }

    function getSerpBadgeHtml(features) {
        var icons = { ads: 'bi bi-currency-dollar', featured_snippet: 'bi bi-card-text', shopping_ads: 'bi bi-bag', local_pack: 'bi bi-geo-alt', people_also_ask: 'bi bi-question-circle', organic: 'bi bi-google' };
        return features.map(function(f) {
            var icon = icons[f] || 'bi bi-dot';
            return '<span style="display:inline-flex;align-items:center;gap:2px;background:#0B0E0F;border-radius:3px;padding:1px 5px;font-size:0.6rem;color:#9DA3AF;margin-right:2px;"><i class="' + icon + '" style="font-size:0.55rem;"></i> ' + f.replace('_', ' ') + '</span>';
        }).join('');
    }

    function getSourceBadge(source) {
        if (!source) return '';
        var map = {
            'google_autocomplete + dataforseo_volume': ['Google + Real Volume API', '#53FC18', '#000'],
            'google_autocomplete': ['Google — Real Search Keywords', '#4285F4', '#fff'],
        };
        var m = map[source] || [source, '#474F54', '#fff'];
        return '<span style="background:' + m[1] + ';color:' + m[2] + ';border-radius:4px;padding:2px 8px;font-size:0.6rem;font-weight:700;">' + escapeHtml(m[0]) + '</span>';
    }

    function getKwSourceBadge(kw) {
        var s = kw.source || '';
        if (s === 'dataforseo') return '<span style="background:#53FC18;color:#000;border-radius:3px;padding:1px 5px;font-size:0.55rem;font-weight:600;margin-left:4px;">Real Vol</span>';
        if (s === 'google') return '<span style="background:#4285F4;color:#fff;border-radius:3px;padding:1px 5px;font-size:0.55rem;font-weight:600;margin-left:4px;">Google</span>';
        if (kw.is_brand) return '<span style="background:#5323F7;color:#fff;border-radius:3px;padding:1px 5px;font-size:0.55rem;font-weight:600;margin-left:4px;">Brand</span>';
        return '<span style="background:#474F54;color:#9DA3AF;border-radius:3px;padding:1px 5px;font-size:0.55rem;font-weight:600;margin-left:4px;">Related</span>';
    }

    function renderSummary(data) {
        dataSource = data.data_source || '';
        var intentCounts = {};
        var totalVol = 0;
        data.keywords.forEach(function(k) {
            intentCounts[k.intent] = (intentCounts[k.intent] || 0) + 1;
            totalVol += k.volume;
        });
        var kLen = data.keywords.length || 1;
        var avgDiff = Math.round(data.keywords.reduce(function(s, k) { return s + k.difficulty; }, 0) / kLen);
        var avgVol = Math.round(totalVol / kLen);
        var niche = data.niche || '';

        return '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">' +
            getSourceBadge(dataSource) +
            (niche ? '<span style="background:#0B0E0F;border:1px solid #2B2F35;color:#fff;border-radius:4px;padding:2px 8px;font-size:0.6rem;font-weight:600;">Niche: ' + escapeHtml(niche) + '</span>' : '') +
            '</div>' +
            (data.summary ? '<p style="color:#9DA3AF;font-size:0.75rem;margin-bottom:0.75rem;">' + escapeHtml(data.summary) + '</p>' : '') +
            '<div class="d-flex flex-wrap gap-3">' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.7rem 1.1rem; flex: 1; min-width: 100px; text-align: center;">' +
                '<div style="font-size: 1.1rem; font-weight: 700; color: #53FC18;">' + data.total + '</div>' +
                '<div style="font-size: 0.65rem; color: #9DA3AF;">Keywords</div></div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.7rem 1.1rem; flex: 1; min-width: 100px; text-align: center;">' +
                '<div style="font-size: 1.1rem; font-weight: 700; color: #fff;">' + numberFormat(avgVol) + '</div>' +
                '<div style="font-size: 0.65rem; color: #9DA3AF;">Avg Volume</div></div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.7rem 1.1rem; flex: 1; min-width: 100px; text-align: center;">' +
                '<div style="font-size: 1.1rem; font-weight: 700; color: ' + getDifficultyColor(avgDiff) + ';">' + avgDiff + '%</div>' +
                '<div style="font-size: 0.65rem; color: #9DA3AF;">Avg Difficulty</div></div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.7rem 1.1rem; flex: 1; min-width: 100px; text-align: center;">' +
                '<div style="font-size: 1.1rem; font-weight: 700; color: #fff;">' + (intentCounts.commercial || 0) + '</div>' +
                '<div style="font-size: 0.65rem; color: #9DA3AF;">Commercial</div></div>' +
            '<div style="background: #191B1F; border: 1px solid #2B2F35; border-radius: 10px; padding: 0.7rem 1.1rem; flex: 1; min-width: 100px; text-align: center;">' +
                '<div style="font-size: 1.1rem; font-weight: 700; color: #fff;">' + (intentCounts.informational || 0) + '</div>' +
                '<div style="font-size: 0.65rem; color: #9DA3AF;">Informational</div></div>' +
            '</div>';
    }

    function renderTable(keywords) {
        var html = '';
        keywords.forEach(function(k) {
            var dc = getDifficultyColor(k.difficulty);
            html += '<tr data-volume="' + k.volume + '" data-difficulty="' + k.difficulty + '" data-difficulty-label="' + getDifficultyLabel(k.difficulty).toLowerCase() + '" data-intent="' + k.intent + '" data-serp="' + (k.serp_features || []).join(',') + '">' +
                '<td style="font-weight: 600;">' + escapeHtml(k.keyword) + getKwSourceBadge(k) + '</td>' +
                '<td style="text-align: right; color: #53FC18; font-weight: 600;">' + numberFormat(k.volume) + '</td>' +
                '<td style="text-align: center;">' +
                    '<div class="diff-bar"><div class="diff-bar-fill" style="width:' + k.difficulty + '%;background:' + dc + ';"></div></div>' +
                    ' <span style="font-size:0.7rem;font-weight:600;color:' + dc + ';">' + k.difficulty + '%</span>' +
                '</td>' +
                '<td style="text-align: center;"><span style="font-size:0.7rem;">' + (k.intent_icon || '') + '</span> <span style="font-size:0.7rem;color:#9DA3AF;">' + (k.intent_label || '') + '</span></td>' +
                '<td style="text-align: center;"><span style="color:' + (k.trend_color || '#9DA3AF') + ';font-size:1rem;">' + (k.trend_icon || '→') + '</span></td>' +
                '<td style="text-align: right; font-size:0.75rem; color: #9DA3AF;">' + (k.cpc || '—') + '</td>' +
                '<td style="text-align: center;">' + (k.position ? '<span style="font-size:0.75rem;font-weight:700;color:#53FC18;">#' + k.position + '</span>' : '<span style="font-size:0.7rem;color:#474F54;">—</span>') + '</td>' +
                '<td style="font-size:0;">' + getSerpBadgeHtml(k.serp_features || ['organic']) + '</td>' +
            '</tr>';
        });
        kwBody.innerHTML = html;
        totalCount.textContent = keywords.length;
        applyFilters();
    }

    function applyFilters() {
        var volumeVal = document.querySelector('#volumeFilters .active')?.getAttribute('data-value') || 'all';
        var diffVal = document.querySelector('#difficultyFilters .active')?.getAttribute('data-value') || 'all';
        var intentVal = document.querySelector('#intentFilters .active')?.getAttribute('data-value') || 'all';
        var serpVal = document.querySelector('#serpFilterPills .active')?.getAttribute('data-serp') || 'all';

        var rows = kwBody.querySelectorAll('tr');
        var visible = 0;
        rows.forEach(function(row) {
            var vol = parseInt(row.getAttribute('data-volume')) || 0;
            var diff = row.getAttribute('data-difficulty-label') || '';
            var intent = row.getAttribute('data-intent') || '';
            var serp = (row.getAttribute('data-serp') || '').toLowerCase();

            var show = true;
            if (volumeVal === 'low' && (vol < 0 || vol > 100)) show = false;
            if (volumeVal === 'medium' && (vol < 100 || vol > 1000)) show = false;
            if (volumeVal === 'high' && vol < 1000) show = false;

            if (diffVal !== 'all' && diff !== diffVal) show = false;
            if (intentVal !== 'all' && intent !== intentVal) show = false;

            if (serpVal !== 'all') {
                if (serp.indexOf(serpVal) === -1) show = false;
            }

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCount.textContent = visible;
        filterCount.textContent = visible;
    }

    function renderGapTab(data) {
        var html = '';
        if (data.competitors && data.competitors.length > 0) {
            html += '<div class="row g-3">';
            data.competitors.forEach(function(c) {
                html += '<div class="col-md-6">' +
                    '<div style="background: #0B0E0F; border: 1px solid #2B2F35; border-radius: 10px; padding: 1rem 1.25rem;">' +
                        '<h6 style="color: #53FC18; font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="bi bi-globe me-1"></i>' + escapeHtml(c.domain) + '</h6>' +
                        '<div class="d-flex gap-3 flex-wrap">';
                if (c.keywords_missing) {
                    html += '<div style="flex:1;min-width:80px;"><div style="font-size:1rem;font-weight:700;color:#53FC18;">' + c.keywords_missing + '</div><div style="font-size:0.65rem;color:#9DA3AF;">Opportunity</div></div>';
                }
                if (c.keywords_overlap) {
                    html += '<div style="flex:1;min-width:80px;"><div style="font-size:1rem;font-weight:700;color:#FFA500;">' + c.keywords_overlap + '</div><div style="font-size:0.65rem;color:#9DA3AF;">Overlap</div></div>';
                }
                if (c.keywords_unique) {
                    html += '<div style="flex:1;min-width:80px;"><div style="font-size:1rem;font-weight:700;color:#E91916;">' + c.keywords_unique + '</div><div style="font-size:0.65rem;color:#9DA3AF;">Competitor Unique</div></div>';
                }
                html += '</div></div></div>';
            });
            html += '</div>';
        } else {
            html += '<p style="color: #9DA3AF; font-size: 0.8125rem;">No competitor data available. Try generating research for a different domain.</p>';
        }
        document.getElementById('gapContent').innerHTML = html;
    }

    function renderRelatedTab(data) {
        var html = '<div class="row g-3">';

        if (data.related_searches && data.related_searches.length > 0) {
            html += '<div class="col-md-6"><div style="background: #0B0E0F; border: 1px solid #2B2F35; border-radius: 10px; padding: 1rem;">' +
                '<h6 style="color: #53FC18; font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="bi bi-link-45deg me-1"></i> Related Searches</h6>';
            data.related_searches.forEach(function(s) {
                html += '<div style="padding: 0.3rem 0; font-size: 0.8125rem; color: #fff; border-bottom: 1px solid #2B2F35;"><i class="bi bi-arrow-return-right me-2" style="color: #474F54;"></i>' + escapeHtml(s) + '</div>';
            });
            html += '</div></div>';
        }

        if (data.questions && data.questions.length > 0) {
            html += '<div class="col-md-6"><div style="background: #0B0E0F; border: 1px solid #2B2F35; border-radius: 10px; padding: 1rem;">' +
                '<h6 style="color: #FFA500; font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="bi bi-question-circle me-1"></i> People Also Ask</h6>';
            data.questions.forEach(function(q) {
                html += '<div style="padding: 0.3rem 0; font-size: 0.8125rem; color: #fff; border-bottom: 1px solid #2B2F35;"><i class="bi bi-question-lg me-2" style="color: #FFA500;"></i>' + escapeHtml(q) + '</div>';
            });
            html += '</div></div>';
        }

        html += '</div>';
        document.getElementById('relatedContent').innerHTML = html;
    }

    function renderCompetitorsTab(data) {
        var html = '';
        if (data.competitors && data.competitors.length > 0) {
            html += '<div class="row g-3">';
            data.competitors.forEach(function(c) {
                html += '<div class="col-md-4">' +
                    '<div style="background: #0B0E0F; border: 1px solid #2B2F35; border-radius: 10px; padding: 1.25rem; text-align: center;">' +
                        '<div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(83,252,24,0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">' +
                            '<i class="bi bi-building" style="font-size: 1.2rem; color: #53FC18;"></i></div>' +
                        '<h6 style="color: #fff; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; word-break: break-all;">' + escapeHtml(c.domain) + '</h6>' +
                        '<div class="d-flex justify-content-center gap-3 flex-wrap">' +
                            '<div><div style="font-size: 1.1rem; font-weight: 700; color: #53FC18;">' + (c.keywords_missing || 0) + '</div><div style="font-size: 0.6rem; color: #9DA3AF;">Missing</div></div>' +
                            '<div><div style="font-size: 1.1rem; font-weight: 700; color: #FFA500;">' + (c.keywords_overlap || 0) + '</div><div style="font-size: 0.6rem; color: #9DA3AF;">Overlap</div></div>' +
                            '<div><div style="font-size: 1.1rem; font-weight: 700; color: #E91916;">' + (c.keywords_unique || 0) + '</div><div style="font-size: 0.6rem; color: #9DA3AF;">Unique</div></div>' +
                        '</div></div></div>';
            });
            html += '</div>';
        } else {
            html += '<p style="color: #9DA3AF; font-size: 0.8125rem;">No competitor data available.</p>';
        }
        document.getElementById('competitorContent').innerHTML = html;
    }

    function renderTrendsChart(data) {
        if (!data.trends || !data.trends.data || !data.trends.themes) {
            document.getElementById('trendContent').innerHTML = '<p style="color: #9DA3AF; font-size: 0.8125rem;">Trend data not available.</p>';
            return;
        }

        if (chartInstance) { chartInstance.dispose(); chartInstance = null; }

        var chartDom = document.getElementById('trendChart');
        if (!chartDom) return;

        var myChart = echarts.init(chartDom);
        chartInstance = myChart;

        var months = data.trends.data.map(function(d) { return d.month; });
        var themeColors = ['#53FC18', '#5323F7', '#FFA500', '#E91916'];

        var series = data.trends.themes.map(function(theme, idx) {
            return {
                name: theme,
                type: 'line',
                smooth: true,
                symbol: 'circle',
                symbolSize: 6,
                lineStyle: { width: 2 },
                itemStyle: { color: themeColors[idx % themeColors.length] },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: themeColors[idx % themeColors.length] + '40' },
                        { offset: 1, color: themeColors[idx % themeColors.length] + '05' }
                    ])
                },
                data: data.trends.data.map(function(d) {
                    var vals = d.values;
                    if (Array.isArray(vals)) return vals[idx] || 0;
                    if (typeof vals === 'object') return vals[theme] || 0;
                    return 0;
                })
            };
        });

        myChart.setOption({
            backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', backgroundColor: '#191B1F', borderColor: '#2B2F35', textStyle: { color: '#fff', fontSize: 11 } },
            legend: { data: data.trends.themes, textStyle: { color: '#9DA3AF', fontSize: 10 }, bottom: 0 },
            grid: { left: '3%', right: '4%', bottom: '18%', top: '5%', containLabel: true },
            xAxis: {
                type: 'category',
                data: months,
                axisLine: { lineStyle: { color: '#2B2F35' } },
                axisLabel: { color: '#9DA3AF', fontSize: 9 },
                splitLine: { show: false }
            },
            yAxis: {
                type: 'value',
                splitLine: { lineStyle: { color: '#2B2F35', type: 'dashed' } },
                axisLabel: { color: '#9DA3AF', fontSize: 9 }
            },
            series: series
        });

        var trendTableHtml = '<div style="background: #0B0E0F; border: 1px solid #2B2F35; border-radius: 10px; overflow: hidden;"><table style="width:100%;font-size:0.75rem;border-collapse:collapse;">' +
            '<thead><tr style="background:#191B1F;">' +
            '<th style="padding:0.5rem 0.75rem;color:#9DA3AF;font-weight:600;border-bottom:1px solid #2B2F35;text-align:left;">Month</th>';
        data.trends.themes.forEach(function(t) {
            trendTableHtml += '<th style="padding:0.5rem 0.75rem;color:#9DA3AF;font-weight:600;border-bottom:1px solid #2B2F35;text-align:right;">' + escapeHtml(t) + '</th>';
        });
        trendTableHtml += '</tr></thead><tbody>';
        data.trends.data.forEach(function(d) {
            trendTableHtml += '<tr><td style="padding:0.35rem 0.75rem;color:#fff;border-bottom:1px solid #2B2F35;">' + d.month + '</td>';
            var vals = d.values;
            data.trends.themes.forEach(function(t, vi) {
                var v = Array.isArray(vals) ? (vals[vi] || 0) : (vals[t] || 0);
                trendTableHtml += '<td style="padding:0.35rem 0.75rem;color:#53FC18;border-bottom:1px solid #2B2F35;text-align:right;font-weight:600;">' + v + '</td>';
            });
            trendTableHtml += '</tr>';
        });
        trendTableHtml += '</tbody></table></div>';
        document.getElementById('trendTable').innerHTML = trendTableHtml;
    }

    function showResults(data) {
        allKeywords = data.keywords || [];
        resultsArea.style.display = 'block';
        loadingState.style.display = 'none';
        errorState.style.display = 'none';
        filtersPanel.style.display = 'block';

        summaryBar.innerHTML = renderSummary(data);
        renderTable(allKeywords);
        renderGapTab(data);
        renderRelatedTab(data);
        renderCompetitorsTab(data);
        renderTrendsChart(data);

        document.querySelector('#kwTabs [data-bs-toggle="tab"]')?.click();
    }

    function showLoading() {
        resultsArea.style.display = 'none';
        errorState.style.display = 'none';
        filtersPanel.style.display = 'none';
        loadingState.style.display = 'block';
    }

    function showError(msg) {
        loadingState.style.display = 'none';
        resultsArea.style.display = 'none';
        filtersPanel.style.display = 'none';
        errorState.style.display = 'block';
        errorMsg.textContent = msg;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = generateBtn;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Researching...';

        showLoading();

        var fd = new FormData(form);
        fd.append('_token', csrfToken);
        fd.append('_t', Date.now());

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action + '?_t=' + Date.now(), true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic me-1"></i> Research';

            var rawText = xhr.responseText || '';
            var data;
            try {
                data = JSON.parse(rawText);
            } catch (e) {
                showError('JSON parse error: ' + e.message + ' | Preview: ' + rawText.substring(0, 300));
                console.error('Full raw response:', rawText);
                return;
            }

            if (data.error) { showError(data.error); return; }
            if (!data.keywords || data.keywords.length === 0) {
                showError('No keywords found. Try a different domain.');
                return;
            }

            try {
                showResults(data);
            } catch (e) {
                showError('Render error: ' + e.message + ' (line ' + e.lineNumber + ')');
                console.error('Render exception:', e, data);
            }
        };

        xhr.onerror = function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic me-1"></i> Research';
            showError('Network error');
        };

        xhr.send(fd);
    });

    document.querySelectorAll('.filter-pill').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var group = this.parentElement;
            group.querySelectorAll('.filter-pill').forEach(function(p) {
                p.className = 'filter-pill';
                p.style.background = '#0B0E0F';
                p.style.color = '#9DA3AF';
                p.style.border = '1px solid #2B2F35';
            });
            this.className = 'filter-pill active';
            this.style.background = '#53FC18';
            this.style.color = '#000';
            this.style.border = 'none';
            applyFilters();
        });
    });

    document.querySelectorAll('.serp-pill').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var parent = this.parentElement;
            parent.querySelectorAll('.serp-pill').forEach(function(p) {
                p.className = 'serp-pill';
                p.style.background = '#0B0E0F';
                p.style.color = '#9DA3AF';
                p.style.border = '1px solid #2B2F35';
            });
            this.className = 'serp-pill active';
            this.style.background = '#53FC18';
            this.style.color = '#000';
            this.style.border = 'none';
            applyFilters();
        });
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.querySelectorAll('.filter-pill').forEach(function(p) {
            if (p.getAttribute('data-value') === 'all') { p.click(); }
        });
        document.querySelectorAll('.serp-pill').forEach(function(p) {
            if (p.getAttribute('data-serp') === 'all') { p.click(); }
        });
    });

    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        var visibleRows = kwBody.querySelectorAll('tr:not([style*="display: none"])');
        if (visibleRows.length === 0) return;
        var csv = 'Keyword,Volume,Difficulty,Intent,Trend,CPC,Position\n';
        visibleRows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                var kw = cells[0].textContent.trim();
                var vol = cells[1].textContent.trim().replace(/,/g, '');
                var diff = cells[2].textContent.trim();
                var intent = cells[3].textContent.trim();
                var trend = cells[4].textContent.trim();
                var cpc = cells[5].textContent.trim();
                var pos = cells[6].textContent.trim();
                csv += '"' + kw.replace(/"/g, '""') + '",' + vol + ',' + diff + ',"' + intent + '",' + trend + ',' + cpc + ',' + pos + '\n';
            }
        });
        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'keyword-magic.csv';
        document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
    });

    document.getElementById('copyTableBtn').addEventListener('click', function() {
        var visibleRows = kwBody.querySelectorAll('tr:not([style*="display: none"])');
        if (visibleRows.length === 0) return;
        var text = 'Keyword\tVolume\tDifficulty\tIntent\tTrend\tCPC\tPosition\n';
        visibleRows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                text += cells[0].textContent.trim() + '\t' + cells[1].textContent.trim() + '\t' +
                    cells[2].textContent.trim() + '\t' + cells[3].textContent.trim() + '\t' +
                    cells[4].textContent.trim() + '\t' + cells[5].textContent.trim() + '\t' +
                    cells[6].textContent.trim() + '\n';
            }
        });
        navigator.clipboard.writeText(text).then(function() {
            var t = document.getElementById('copyTableBtn');
            t.innerHTML = '<i class="bi bi-check"></i> Copied!';
            setTimeout(function() { t.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
        });
    });

    window.addEventListener('resize', function() {
        if (chartInstance) { chartInstance.resize(); }
    });
})();
</script>
@endsection
