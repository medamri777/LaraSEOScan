@extends('layouts.app')

@section('title', 'Keyword Research - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1440px; margin: 0 auto;">

    {{-- Header --}}
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, rgba(83,252,24,0.15), rgba(20,184,166,0.15)); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(83,252,24,0.2);">
                <i class="bi bi-key" style="font-size: 1.3rem; color: var(--kick-green); filter: drop-shadow(0 0 6px rgba(83,252,24,0.4));"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0" style="color: var(--kick-text-primary);">Keyword Research</h2>
                <p class="text-muted mb-0 small">Enter a keyword to see real Google data, who ranks, and related suggestions</p>
            </div>
        </div>
    </div>

    {{-- Search Form --}}
    <div class="card card-dashboard mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tools.keyword-research') }}">
                <div class="row g-3 align-items-end">
                    <div class="col">
                        <div class="input-group" style="border-radius: 14px; overflow: hidden; border: 1px solid var(--kick-border);">
                            <span class="input-group-text" style="background: var(--kick-surface-2-solid); border: none; color: var(--kick-green);">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="keyword" class="form-control form-control-lg"
                                   style="background: var(--kick-surface-2-solid); border: none; color: var(--kick-text-primary);"
                                   placeholder="Enter a keyword to research..." value="{{ $keyword ?? '' }}" required autofocus>
                            <button type="submit" class="btn btn-kick-primary px-4 fw-semibold" style="border-radius: 0;">
                                Research
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($data)

    {{-- ═══ Row 1: Quality Verdict + Metrics ═══ --}}
    <div class="row g-3 mb-4">

        {{-- Quality Score Card --}}
        <div class="col-lg-4">
            <div class="card card-dashboard h-100" style="border: 1px solid {{ $data['quality_score']['verdict_color'] }}33;">
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="mb-3" style="position: relative; width: 120px; height: 120px;">
                        <svg viewBox="0 0 120 120" width="120" height="120">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="10"/>
                            <circle cx="60" cy="60" r="52" fill="none"
                                    stroke="{{ $data['quality_score']['verdict_color'] }}"
                                    stroke-width="10" stroke-linecap="round"
                                    stroke-dasharray="{{ round($data['quality_score']['score'] * 3.27) }} 327"
                                    transform="rotate(-90 60 60)"
                                    style="filter: drop-shadow(0 0 8px {{ $data['quality_score']['verdict_color'] }}66);"/>
                        </svg>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <div style="font-size: 2rem; font-weight: 800; color: {{ $data['quality_score']['verdict_color'] }}; line-height: 1;">
                                {{ $data['quality_score']['score'] }}
                            </div>
                            <div style="font-size: 0.65rem; color: var(--kick-text-muted); text-transform: uppercase; letter-spacing: 1px;">/ 100</div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-1" style="color: {{ $data['quality_score']['verdict_color'] }};">
                        {{ $data['quality_score']['verdict'] }} Keyword
                    </h5>
                    <p class="small mb-3" style="color: var(--kick-text-muted);">
                        "{{ $data['keyword'] }}"
                    </p>

                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        @foreach($data['quality_score']['reasons'] as $reason)
                            <span class="badge px-2 py-1" style="background: rgba(255,255,255,0.06); color: var(--kick-text-secondary); font-weight: 500; font-size: 0.7rem; border-radius: 6px;">
                                {{ $reason }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 w-100" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <small style="color: var(--kick-text-muted); font-size: 0.68rem;">
                            <i class="bi bi-database me-1"></i>
                            Data source: {{ $data['metrics']['data_source'] ?? 'Google SERP' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metrics Column --}}
        <div class="col-lg-8">
            <div class="row g-3 h-100">
                {{-- Difficulty --}}
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="small fw-semibold" style="color: var(--kick-text-secondary);">
                                    <i class="bi bi-{{ $data['metrics']['difficulty']['icon'] }} me-1" style="color: {{ $data['metrics']['difficulty']['color'] }};"></i> Difficulty
                                </span>
                                <span class="badge" style="background: {{ $data['metrics']['difficulty']['color'] }}22; color: {{ $data['metrics']['difficulty']['color'] }}; font-size: 0.7rem;">
                                    {{ $data['metrics']['difficulty']['label'] }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div style="font-size: 2.2rem; font-weight: 800; color: {{ $data['metrics']['difficulty']['color'] }}; line-height: 1;">
                                    {{ $data['metrics']['difficulty']['score'] }}
                                </div>
                                <div style="flex: 1;">
                                    <div style="height: 8px; background: rgba(255,255,255,0.06); border-radius: 99px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $data['metrics']['difficulty']['score'] }}%; background: {{ $data['metrics']['difficulty']['color'] }}; border-radius: 99px; box-shadow: 0 0 12px {{ $data['metrics']['difficulty']['color'] }}66;"></div>
                                    </div>
                                    <div class="mt-1 small" style="color: var(--kick-text-muted);">out of 100</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Competition --}}
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="small fw-semibold" style="color: var(--kick-text-secondary);">
                                    <i class="bi bi-people me-1" style="color: #f97316;"></i> Competition
                                </span>
                                <span class="badge" style="background: rgba(249,115,22,0.1); color: #f97316; font-size: 0.7rem;">
                                    {{ $data['metrics']['competition_level'] ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div style="font-size: 2.2rem; font-weight: 800; color: var(--kick-text-primary); line-height: 1;">
                                    {{ $data['metrics']['competition'] }}%
                                </div>
                                <div style="flex: 1;">
                                    <div style="height: 8px; background: rgba(255,255,255,0.06); border-radius: 99px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $data['metrics']['competition'] }}%; background: linear-gradient(90deg, #f97316, #ef4444); border-radius: 99px;"></div>
                                    </div>
                                    <div class="mt-1 small" style="color: var(--kick-text-muted);">
                                        {{ $data['metrics']['total_results'] }} SERP results analyzed
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search Volume (if available) --}}
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="small fw-semibold" style="color: var(--kick-text-secondary);">
                                    <i class="bi bi-graph-up-arrow me-1" style="color: var(--kick-green);"></i> Search Volume
                                </span>
                            </div>
                            @if($data['metrics']['search_volume'] !== null)
                                <div style="font-size: 2.2rem; font-weight: 800; color: var(--kick-text-primary); line-height: 1;">
                                    {{ number_format($data['metrics']['search_volume']) }}
                                </div>
                                <div class="mt-2 small" style="color: var(--kick-text-muted);">monthly searches</div>
                            @else
                                <div style="font-size: 1.4rem; font-weight: 700; color: var(--kick-text-muted); line-height: 1;">
                                    <i class="bi bi-bar-chart me-1" style="color: var(--kick-green);"></i>
                                    SERP Analyzed
                                </div>
                                @if(!empty($data['metrics']['serp_analysis']))
                                    <div class="mt-2">
                                        @foreach($data['metrics']['serp_analysis'] as $insight)
                                            <div class="small" style="color: var(--kick-text-muted);">
                                                <i class="bi bi-check2 me-1" style="color: var(--kick-green); font-size: 0.65rem;"></i>{{ $insight }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CPC (if available) --}}
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="small fw-semibold" style="color: var(--kick-text-secondary);">
                                    <i class="bi bi-currency-dollar me-1" style="color: #14b8a6;"></i> Cost Per Click
                                </span>
                            </div>
                            @if($data['metrics']['cpc'] !== null)
                                <div style="font-size: 2.2rem; font-weight: 800; color: var(--kick-text-primary); line-height: 1;">
                                    ${{ number_format($data['metrics']['cpc'], 2) }}
                                </div>
                                <div class="mt-2 small" style="color: var(--kick-text-muted);">avg cost per click</div>
                            @else
                                <div style="font-size: 1.4rem; font-weight: 700; color: var(--kick-text-muted); line-height: 1;">
                                    <i class="bi bi-info-circle me-1" style="color: #14b8a6;"></i>
                                    Based on SERP
                                </div>
                                <div class="mt-2 small" style="color: var(--kick-text-muted);">
                                    @if($data['metrics']['competition'] > 60)
                                        High commercial competition suggests good CPC
                                    @elseif($data['metrics']['competition'] > 30)
                                        Moderate commercial interest detected
                                    @else
                                        Low advertiser interest for this keyword
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Row 2: SERP Results + Autocomplete Keywords ═══ --}}
    <div class="row g-3 mb-4">

        {{-- SERP Results (Who Ranks) --}}
        <div class="col-lg-7">
            <div class="card card-dashboard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4" style="background: transparent;">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0" style="color: var(--kick-text-primary);">
                            <i class="bi bi-list-ol me-2" style="color: var(--kick-green);"></i>
                            Who Ranks for "{{ Str::limit($data['keyword'], 30) }}"
                        </h6>
                        <span class="badge" style="background: rgba(83,252,24,0.1); color: var(--kick-green);">
                            {{ count($data['serp_results']) }} results
                        </span>
                    </div>
                    <p class="small mt-1 mb-0" style="color: var(--kick-text-muted);">Real Google search results</p>
                </div>
                <div class="card-body px-0 pb-0">
                    @if(!empty($data['serp_results']))
                    <div class="table-responsive">
                        <table class="table mb-0" style="color: var(--kick-text-primary);">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--kick-border);">
                                    <th class="ps-4 small fw-semibold" style="color: var(--kick-text-muted); width: 50px;">#</th>
                                    <th class="small fw-semibold" style="color: var(--kick-text-muted);">Website</th>
                                    <th class="small fw-semibold pe-4 text-end" style="color: var(--kick-text-muted);">Page Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['serp_results'] as $result)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                    <td class="ps-4">
                                        @if($result['position'] <= 3)
                                            <span class="badge fw-bold" style="background: linear-gradient(135deg, var(--kick-green), #14b8a6); color: #000; min-width: 28px;">
                                                {{ $result['position'] }}
                                            </span>
                                        @else
                                            <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--kick-text-secondary); min-width: 28px;">
                                                {{ $result['position'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 24px; height: 24px; border-radius: 6px; background: rgba(83,252,24,0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-globe2" style="font-size: 0.7rem; color: var(--kick-green);"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold small" style="color: var(--kick-text-primary);">{{ $result['domain'] }}</div>
                                                @if(!empty($result['description']))
                                                <div class="text-truncate" style="max-width: 280px; font-size: 0.72rem; color: var(--kick-text-muted);">
                                                    {{ Str::limit($result['description'], 80) }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ $result['url'] }}" target="_blank" class="text-decoration-none small text-truncate d-inline-block"
                                           style="max-width: 220px; color: var(--kick-green); font-size: 0.72rem;"
                                           title="{{ $result['title'] }}">
                                            {{ Str::limit($result['title'], 40) }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 px-4" style="color: var(--kick-text-muted);">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0 small">
                            Could not fetch SERP results. Google may have rate-limited the request.<br>
                            Try again in a moment.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Real Google Autocomplete Suggestions --}}
        <div class="col-lg-5">
            <div class="card card-dashboard h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4" style="background: transparent;">
                    <h6 class="fw-bold mb-0" style="color: var(--kick-text-primary);">
                        <i class="bi bi-lightbulb me-2" style="color: #fbbf24;"></i>
                        Real Related Keywords
                    </h6>
                    <p class="small mt-1 mb-0" style="color: var(--kick-text-muted);">
                        From Google Autocomplete
                    </p>
                </div>
                <div class="card-body px-4 pb-4">
                    @if(!empty($data['autocomplete']))
                        <div class="d-flex flex-column gap-2">
                            @foreach($data['autocomplete'] as $index => $suggestion)
                            <a href="{{ route('tools.keyword-research', ['keyword' => $suggestion]) }}"
                               class="d-flex align-items-center gap-3 text-decoration-none p-2"
                               style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.04); border-radius: 10px; transition: all 0.2s;"
                               onmouseover="this.style.background='rgba(83,252,24,0.06)'; this.style.borderColor='rgba(83,252,24,0.15)';"
                               onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.borderColor='rgba(255,255,255,0.04)';">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(83,252,24,0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi bi-search" style="font-size: 0.7rem; color: var(--kick-green);"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div class="small fw-semibold text-truncate" style="color: var(--kick-text-primary);">{{ $suggestion }}</div>
                                </div>
                                <i class="bi bi-arrow-right" style="font-size: 0.75rem; color: var(--kick-text-muted); flex-shrink: 0;"></i>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4" style="color: var(--kick-text-muted);">
                            <i class="bi bi-lightbulb" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0 small">No autocomplete suggestions found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Row 3: Recommendation ═══ --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card card-dashboard" style="border: 1px solid rgba(20,184,166,0.15);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(20,184,166,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-lightbulb" style="color: #14b8a6; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-2" style="color: var(--kick-text-primary);">SEO Recommendation</h6>
                            @if($data['quality_score']['score'] >= 60)
                                <p class="small mb-0" style="color: var(--kick-text-secondary);">
                                    This is a strong keyword opportunity. Create comprehensive, high-quality content targeting "<strong>{{ $data['keyword'] }}</strong>".
                                    Include it in your title, H1, meta description, and naturally throughout the content.
                                    @if(count($data['serp_results']) > 0)
                                        Study the top {{ min(3, count($data['serp_results'])) }} ranking pages to understand what Google rewards for this query.
                                    @endif
                                </p>
                            @elseif($data['quality_score']['score'] >= 35)
                                <p class="small mb-0" style="color: var(--kick-text-secondary);">
                                    This keyword has moderate potential. It can work as a supporting keyword for a broader topic.
                                    @if(!empty($data['autocomplete']))
                                        Consider targeting the related keywords from Google Autocomplete that may be easier to rank for.
                                    @endif
                                </p>
                            @else
                                <p class="small mb-0" style="color: var(--kick-text-secondary);">
                                    This keyword might be too competitive or not have enough search demand for a primary target.
                                    @if(!empty($data['autocomplete']))
                                        Focus on the related keywords from Google Autocomplete that may have better difficulty/volume ratios.
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection
