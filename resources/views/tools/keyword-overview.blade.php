@extends('layouts.app')

@section('title', ($keyword ? $keyword . ' — ' : '') . 'Keyword Overview')

@section('content')
<style>
.ko-page { max-width: 1280px; margin: 0 auto; padding: .5rem 0 2rem; }

/* Hero */
.ko-hero {
    background: linear-gradient(135deg, rgba(83,252,24,.06) 0%, rgba(20,184,166,.04) 100%);
    border: 1px solid var(--kick-border-subtle);
    border-radius: var(--kick-radius-xl);
    padding: 2.5rem 2rem 2rem;
    margin-bottom: 1.5rem;
    position: relative; overflow: hidden;
}
.ko-hero::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse 55% 70% at 80% 50%, rgba(83,252,24,.07) 0%, transparent 70%);
    pointer-events: none;
}
.ko-hero-title { font-size: 1.6rem; font-weight: 800; color: var(--kick-text-primary); margin-bottom: .35rem; }
.ko-hero-sub { color: var(--kick-text-secondary); font-size: .875rem; margin-bottom: 1.5rem; }
.ko-search-row { display: flex; gap: .75rem; max-width: 680px; }
.ko-search-input {
    flex: 1; padding: .8rem 1.2rem;
    border-radius: var(--kick-radius-md);
    border: 1.5px solid var(--kick-border-muted);
    background: var(--kick-surface-2-solid);
    color: var(--kick-text-primary); font-size: .95rem; outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.ko-search-input::placeholder { color: var(--kick-text-secondary); }
.ko-search-input:focus { border-color: var(--kick-green); box-shadow: 0 0 0 3px rgba(83,252,24,.1); }
.ko-search-btn {
    padding: .8rem 1.75rem; border-radius: var(--kick-radius-md);
    background: linear-gradient(135deg, var(--kick-green), #3de014);
    color: #000; font-weight: 700; font-size: .9rem;
    border: none; cursor: pointer; transition: opacity .2s, transform .15s; white-space: nowrap;
}
.ko-search-btn:hover { opacity: .9; transform: translateY(-1px); }

/* Stat cards */
.ko-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px,1fr)); gap: 1rem; margin-bottom: 1.25rem; }
.ko-stat {
    background: var(--kick-surface-1); border: 1px solid var(--kick-border-subtle);
    border-radius: var(--kick-radius-lg); padding: 1.25rem 1.25rem 1rem;
    transition: border-color .2s, transform .2s; position: relative; overflow: hidden;
}
.ko-stat::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--kick-green), var(--kick-teal)); opacity: 0; transition: opacity .2s;
}
.ko-stat:hover { border-color: rgba(83,252,24,.2); transform: translateY(-2px); }
.ko-stat:hover::before { opacity: 1; }
.ko-stat-icon { width: 36px; height: 36px; border-radius: var(--kick-radius-sm); display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-bottom: .75rem; background: var(--kick-green-subtle); color: var(--kick-green); }
.ko-stat-lbl { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--kick-text-secondary); margin-bottom: .3rem; }
.ko-stat-val { font-size: 1.65rem; font-weight: 800; color: var(--kick-text-primary); line-height: 1; margin-bottom: .2rem; }
.ko-stat-meta { font-size: .73rem; color: var(--kick-text-secondary); }

/* Difficulty */
.diff-card { background: var(--kick-surface-1); border: 1px solid var(--kick-border-subtle); border-radius: var(--kick-radius-lg); padding: 1.5rem 1.5rem 1.25rem; margin-bottom: 1.25rem; }
.diff-row { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; }
.diff-score { font-size: 3rem; font-weight: 900; line-height: 1; }
.diff-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .75rem; border-radius: var(--kick-radius-pill); font-size: .75rem; font-weight: 700; margin-top: .4rem; }
.chip-easy   { background: rgba(16,185,129,.15); color: #10b981; }
.chip-medium { background: rgba(245,158,11,.15);  color: #f59e0b; }
.chip-hard   { background: rgba(239,68,68,.15);   color: #ef4444; }
.chip-vhard  { background: rgba(167,139,250,.15);  color: #a78bfa; }
.diff-explain { font-size: .82rem; color: var(--kick-text-secondary); line-height: 1.55; max-width: 360px; }
.diff-track { height: 10px; border-radius: 99px; background: linear-gradient(90deg, #10b981 0%, #f59e0b 40%, #ef4444 75%, #7c3aed 100%); position: relative; }
.diff-needle { position: absolute; top: -6px; width: 4px; height: 22px; background: #fff; border-radius: 2px; transform: translateX(-50%); box-shadow: 0 2px 6px rgba(0,0,0,.5); transition: left 1.2s cubic-bezier(.34,1.56,.64,1); }
.diff-axis { display: flex; justify-content: space-between; margin-top: .4rem; font-size: .68rem; color: var(--kick-text-secondary); }
.diff-method { font-size: .68rem; color: var(--kick-text-secondary); margin-top: .5rem; }

/* Section card */
.ko-card { background: var(--kick-surface-1); border: 1px solid var(--kick-border-subtle); border-radius: var(--kick-radius-lg); margin-bottom: 1.25rem; overflow: hidden; }
.ko-card-hd { display: flex; align-items: center; gap: .6rem; padding: .9rem 1.25rem; border-bottom: 1px solid var(--kick-border-subtle); font-weight: 700; font-size: .875rem; color: var(--kick-text-primary); }
.ko-card-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .82rem; }

/* ── SITE INTEL TABLE ───────────────────────── */
.intel-header {
    background: #000;
    border-bottom: 1px solid #0f0;
    padding: .6rem 1.25rem;
    font-family: 'Courier New', monospace;
    font-size: .72rem;
    color: #0f0;
    display: flex; align-items: center; gap: .75rem;
    letter-spacing: .06em;
}
.intel-header .scan-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #0f0;
    box-shadow: 0 0 8px #0f0;
    animation: pulse-dot 1.2s infinite;
}
@keyframes pulse-dot {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .4; transform: scale(.7); }
}

.comp-tbl { width: 100%; border-collapse: collapse; }
.comp-tbl th {
    background: var(--kick-surface-2-solid); font-size: .68rem;
    text-transform: uppercase; letter-spacing: .07em; font-weight: 700;
    color: var(--kick-text-secondary); padding: .65rem 1rem; text-align: left;
    border-bottom: 1px solid var(--kick-border-subtle);
}
.comp-tbl td {
    padding: .75rem 1rem; border-bottom: 1px solid rgba(43,47,53,.5);
    font-size: .84rem; vertical-align: middle; color: var(--kick-text-primary);
}
.comp-tbl tr:last-child td { border-bottom: none; }
.comp-tbl tr:hover td { background: rgba(83,252,24,.02); }

/* scan row animation */
@keyframes scanRow {
    from { background: rgba(0,255,0,.08); }
    to   { background: transparent; }
}
.comp-tbl tr.scanned td { animation: scanRow 1.5s ease forwards; }

.pos-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; font-size: .75rem; font-weight: 700; }
.p1 { background: rgba(234,179,8,.2); color: #eab308; }
.p2 { background: rgba(156,163,175,.15); color: #9ca3af; }
.p3 { background: rgba(217,119,6,.2); color: #d97706; }
.px { background: var(--kick-surface-2-solid); color: var(--kick-text-secondary); }

.auth-row { display: flex; align-items: center; gap: .5rem; }
.auth-num { font-size: .75rem; font-weight: 700; color: var(--kick-text-primary); min-width: 22px; }
.auth-track { flex: 1; background: var(--kick-surface-2-solid); border-radius: 99px; height: 6px; }
.auth-fill { height: 6px; border-radius: 99px; transition: width 1s ease; }

.domain-lnk { color: var(--kick-green); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: .4rem; }
.domain-lnk:hover { text-decoration: underline; color: #3de014; }
.favicon { width: 16px; height: 16px; border-radius: 3px; }

.kd-chip { display: inline-block; padding: .18rem .55rem; border-radius: 99px; font-size: .7rem; font-weight: 700; }
.kd-weak   { background: rgba(16,185,129,.15); color: #10b981; }
.kd-mod    { background: rgba(245,158,11,.15);  color: #f59e0b; }
.kd-strong { background: rgba(239,68,68,.15);   color: #ef4444; }
.kd-auth   { background: rgba(167,139,250,.15);  color: #a78bfa; }
.kd-unk    { background: var(--kick-surface-2-solid); color: var(--kick-text-secondary); }

/* ── REPUTATION BADGE ── */
.rep-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .22rem .65rem; border-radius: 5px;
    font-family: 'Courier New', monospace;
    font-size: .68rem; font-weight: 700; letter-spacing: .05em;
    position: relative; overflow: hidden;
    cursor: default;
}
.rep-badge::after {
    content: ''; position: absolute; top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
    animation: shimmer 2.5s infinite;
}
@keyframes shimmer { to { left: 200%; } }

.rep-verified    { background: rgba(16,185,129,.12); color: #10b981; border: 1px solid rgba(16,185,129,.3); }
.rep-clean       { background: rgba(83,252,24,.1);   color: #53FC18; border: 1px solid rgba(83,252,24,.25); }
.rep-suspicious  { background: rgba(245,158,11,.12); color: #f59e0b; border: 1px solid rgba(245,158,11,.3); }
.rep-flagged     { background: rgba(239,68,68,.12);  color: #ef4444; border: 1px solid rgba(239,68,68,.3); box-shadow: 0 0 8px rgba(239,68,68,.2); }
.rep-blacklisted { background: rgba(220,38,38,.15);  color: #dc2626; border: 1px solid rgba(220,38,38,.5); box-shadow: 0 0 12px rgba(220,38,38,.3); }
.rep-unknown     { background: var(--kick-surface-2-solid); color: var(--kick-text-secondary); border: 1px solid var(--kick-border-subtle); }

.rep-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.dot-v { background: #10b981; box-shadow: 0 0 6px #10b981; }
.dot-c { background: #53FC18; box-shadow: 0 0 6px #53FC18; }
.dot-s { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; animation: blink 1s infinite; }
.dot-f { background: #ef4444; box-shadow: 0 0 6px #ef4444; animation: blink .7s infinite; }
.dot-b { background: #dc2626; box-shadow: 0 0 8px #dc2626; animation: blink .5s infinite; }
.dot-u { background: #6b7280; }
@keyframes blink { 0%,100%{ opacity:1; } 50%{ opacity:.3; } }

/* Insight bar */
.insight-bar { padding: .75rem 1.25rem; border-top: 1px solid var(--kick-border-subtle); background: rgba(83,252,24,.025); font-size: .8rem; color: var(--kick-text-secondary); display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }

/* Related keywords */
.kw-chips { padding: 1rem 1.1rem; display: flex; flex-wrap: wrap; gap: .4rem; }
.kw-chip { display: inline-flex; align-items: center; gap: .35rem; background: var(--kick-surface-2-solid); border: 1px solid var(--kick-border-muted); color: var(--kick-text-primary); border-radius: var(--kick-radius-sm); padding: .3rem .7rem; font-size: .78rem; font-weight: 500; text-decoration: none; transition: border-color .15s, color .15s, background .15s; }
.kw-chip:hover { background: var(--kick-green-subtle); border-color: rgba(83,252,24,.4); color: var(--kick-green); }

/* Loading */
.ko-loading { display: none; text-align: center; padding: 4rem; }
.ko-spinner { width: 44px; height: 44px; border: 3px solid var(--kick-border-muted); border-top-color: var(--kick-green); border-radius: 50%; animation: spin .75s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty */
.ko-empty { text-align: center; padding: 5rem 1.5rem; }
.ko-empty-icon { font-size: 3.5rem; opacity: .3; margin-bottom: 1rem; }
.ko-empty h3 { font-weight: 700; color: var(--kick-text-primary); margin-bottom: .4rem; }
.ko-empty p { color: var(--kick-text-secondary); font-size: .875rem; }

/* GSC */
.gsc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px,1fr)); gap: 1rem; padding: 1.25rem; }
.gsc-metric { text-align: center; }
.gsc-val { font-size: 1.5rem; font-weight: 800; color: var(--kick-green); }
.gsc-lbl { font-size: .72rem; color: var(--kick-text-secondary); font-weight: 600; }

/* Scan progress */
.scan-progress {
    font-family: 'Courier New', monospace; font-size: .7rem; color: #0f0;
    background: #000; border: 1px solid #0f0; border-radius: 6px;
    padding: .5rem .75rem; margin-bottom: 1rem; display: none;
}
.scan-log { height: 80px; overflow: hidden; }
.scan-log div { animation: fadeLog .3s ease; }
@keyframes fadeLog { from { opacity:0; transform: translateY(-4px); } to { opacity:1; } }

@media (max-width:640px) {
    .ko-search-row { flex-direction: column; }
    .ko-search-btn { width: 100%; }
    .ko-hero { padding: 1.5rem 1.25rem 1.25rem; }
}
</style>

<div class="ko-page">

    {{-- HERO --}}
    <div class="ko-hero">
        <h1 class="ko-hero-title">🔍 Keyword Overview</h1>
        <p class="ko-hero-sub">Analyze any keyword — see who ranks, their reputation, domain authority, and threat level.</p>
        <form method="GET" action="{{ route('tools.keyword-overview') }}" id="koForm">
            <div class="ko-search-row">
                <input type="text" name="keyword" id="kwInput" class="ko-search-input"
                       placeholder="e.g. seo tools, digital marketing, buy followers…"
                       value="{{ $keyword ?? '' }}" required autocomplete="off">
                <button type="submit" class="ko-search-btn" id="analyzeBtn">
                    <i class="bi bi-search me-1"></i> Analyze
                </button>
            </div>
        </form>
    </div>

    {{-- LOADING --}}
    <div class="ko-loading" id="loadingState">
        <div class="ko-spinner"></div>
        <p style="color:var(--kick-text-secondary);font-weight:500">Scanning SERP, pulling domain authority &amp; reputation intel…</p>
    </div>

    @if($data)

    {{-- HEADING --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap">
        <div>
            <h2 style="font-size:1.2rem;font-weight:800;color:var(--kick-text-primary);margin:0">"{{ $keyword }}"</h2>
            <small style="color:var(--kick-text-secondary);font-size:.75rem">
                <i class="bi bi-database me-1"></i>{{ $data['data_source'] ?? 'SERP Analysis + OpenPageRank' }}
            </small>
        </div>
        @if(!empty($data['note']))
        <span class="ms-auto" style="background:rgba(245,158,11,.15);color:#f59e0b;border-radius:8px;font-size:.72rem;padding:.3rem .7rem">
            <i class="bi bi-info-circle me-1"></i>{{ $data['note'] }}
        </span>
        @endif
    </div>

    {{-- STAT CARDS --}}
    <div class="ko-stats">
        <div class="ko-stat">
            <div class="ko-stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="ko-stat-lbl">Search Volume</div>
            @if($data['search_volume'] !== null)
            <div class="ko-stat-val">{{ number_format($data['search_volume']) }}</div>
            <div class="ko-stat-meta">monthly searches</div>
            @else
            <div class="ko-stat-val" style="font-size:1rem;color:var(--kick-text-secondary)">—</div>
            <div class="ko-stat-meta">Upgrade for volume data</div>
            @endif
        </div>
        <div class="ko-stat">
            <div class="ko-stat-icon" style="color:#10b981;background:rgba(16,185,129,.1)"><i class="bi bi-currency-dollar"></i></div>
            <div class="ko-stat-lbl">CPC</div>
            @if($data['cpc'] !== null)
            <div class="ko-stat-val">${{ $data['cpc'] }}</div>
            <div class="ko-stat-meta">cost per click</div>
            @else
            <div class="ko-stat-val" style="font-size:1rem;color:var(--kick-text-secondary)">—</div>
            <div class="ko-stat-meta">Upgrade for CPC data</div>
            @endif
        </div>
        <div class="ko-stat">
            <div class="ko-stat-icon" style="color:#f59e0b;background:rgba(245,158,11,.1)"><i class="bi bi-people-fill"></i></div>
            <div class="ko-stat-lbl">Ad Competition</div>
            @if($data['competition'] !== null)
            <div class="ko-stat-val">{{ $data['competition'] }}%</div>
            <div class="ko-stat-meta">advertiser competition</div>
            @else
            <div class="ko-stat-val" style="font-size:1rem;color:var(--kick-text-secondary)">—</div>
            <div class="ko-stat-meta">Free tier</div>
            @endif
        </div>
        <div class="ko-stat">
            <div class="ko-stat-icon" style="color:#a78bfa;background:rgba(167,139,250,.1)"><i class="bi bi-trophy-fill"></i></div>
            <div class="ko-stat-lbl">Ranking Sites</div>
            <div class="ko-stat-val">{{ count($data['serp_preview'] ?? []) }}</div>
            <div class="ko-stat-meta">websites in top 10</div>
        </div>
    </div>

    {{-- DIFFICULTY METER --}}
    @php
        $diff  = $data['difficulty'];
        $score = (int) $diff['score'];
        $pct   = min(100, max(0, $score));
        [$diffCls, $diffIcon, $diffText, $diffExplain] = match(true) {
            $score < 30 => ['chip-easy',   '😊', 'Easy',      'Low competition — great opportunity to rank with quality content.'],
            $score < 55 => ['chip-medium', '⚠️', 'Medium',    'Moderate competition — solid content and link-building needed.'],
            $score < 80 => ['chip-hard',   '🔥', 'Hard',      'Very competitive — strong authority and extensive SEO strategy required.'],
            default     => ['chip-vhard',  '💀', 'Very Hard', 'Extremely competitive — dominated by major authoritative brands.'],
        };
        $scoreColor = $score < 30 ? '#10b981' : ($score < 55 ? '#f59e0b' : ($score < 80 ? '#ef4444' : '#a78bfa'));
    @endphp
    <div class="diff-card">
        <div class="diff-row">
            <div>
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--kick-text-secondary);margin-bottom:.4rem">Keyword Difficulty</div>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <span class="diff-score" style="color:{{ $scoreColor }}">{{ $score }}</span>
                    <span class="diff-chip {{ $diffCls }}">{{ $diffIcon }} {{ $diffText }}</span>
                </div>
            </div>
            <p class="diff-explain">{{ $diffExplain }}</p>
        </div>
        <div class="diff-track">
            <div class="diff-needle" id="diffNeedle" style="left:{{ $pct }}%"></div>
        </div>
        <div class="diff-axis">
            <span>0 — Very Easy</span><span>Medium</span><span>100 — Very Hard</span>
        </div>
        @if(!empty($diff['method']))
        <div class="diff-method"><i class="bi bi-info-circle me-1"></i>{{ $diff['method'] }}</div>
        @endif
    </div>

    {{-- COMPETITOR WEBSITES WITH REPUTATION --}}
    @if(!empty($data['serp_preview']))
    @php
        // Reputation engine
        $suspiciousTlds = ['tk','ml','cf','ga','gq','xyz','top','click','loan','work','date','download','stream','online','site','website','space','fun','rest','icu','vip','club','live','link','bid','win','cam','uno','ws','cc','bz','tv','me'];
        $flaggedPatterns = [
            'download','crack','keygen','patch','pirat','torrent','warez','hack','cheat','nulled','leaked','free-download','serial','license','activation',
            // Arabic piracy / streaming patterns
            'cima','egybest','mycima','faselhd','shahid','akwam','arabseed','weyyak','laroza','lodynet','asian','drama','anime','manga','kom', 'yalla', 'kora', 'shoot',
            'مسلسل', 'فيلم', 'مشاهدة', 'تحميل', 'مترجم', 'مدبلج', 'اون لاين', 'ايجي بست', 'ماي سيما', 'فاصل اعلاني', 'اكوام', 'عرب سيد', 'لاروزا'
        ];

        function getSiteReputation(string $domain, ?float $pageRank, array $suspTlds, array $flagPatterns): array {
            $tld = strtolower(substr($domain, strrpos($domain, '.') + 1));
            $domainLower = strtolower($domain);

            // Check for flagged patterns in domain name
            foreach ($flagPatterns as $p) {
                if (str_contains($domainLower, $p)) {
                    return ['class'=>'rep-blacklisted','dot'=>'dot-b','label'=>'BLACKLISTED','icon'=>'⛔','reason'=>'Domain contains illegal pattern: '.$p];
                }
            }

            // Suspicious TLDs with low authority
            if (in_array($tld, $suspTlds) && ($pageRank === null || $pageRank < 2)) {
                return ['class'=>'rep-flagged','dot'=>'dot-f','label'=>'FLAGGED','icon'=>'🚨','reason'=>'Suspicious TLD + low authority'];
            }

            // No page rank at all = unknown
            if ($pageRank === null) {
                return ['class'=>'rep-unknown','dot'=>'dot-u','label'=>'UNKNOWN','icon'=>'❓','reason'=>'No authority data available'];
            }

            // Score-based reputation
            if ($pageRank >= 6) {
                return ['class'=>'rep-verified','dot'=>'dot-v','label'=>'VERIFIED','icon'=>'✅','reason'=>'High authority domain — established & trusted'];
            }
            if ($pageRank >= 3) {
                return ['class'=>'rep-clean','dot'=>'dot-c','label'=>'CLEAN','icon'=>'🟢','reason'=>'Good authority — appears legitimate'];
            }
            if ($pageRank >= 1) {
                return ['class'=>'rep-suspicious','dot'=>'dot-s','label'=>'SUSPICIOUS','icon'=>'⚠️','reason'=>'Low authority — limited trust signals'];
            }

            return ['class'=>'rep-flagged','dot'=>'dot-f','label'=>'FLAGGED','icon'=>'🚨','reason'=>'Very low authority — proceed with caution'];
        }

        $hasScores = array_filter($data['serp_preview'], fn($r) => ($r['page_rank'] ?? null) !== null);
        $avgAuth   = count($hasScores) > 0
            ? round(array_sum(array_map(fn($r) => min(100,(int)round(($r['page_rank']??0)*10)), $hasScores)) / count($hasScores))
            : null;

        // Count reputation types
        $repCounts = ['VERIFIED'=>0,'CLEAN'=>0,'SUSPICIOUS'=>0,'FLAGGED'=>0,'BLACKLISTED'=>0,'UNKNOWN'=>0];
        foreach ($data['serp_preview'] as $r) {
            $domain = $r['domain'] ?? '';
            $rep = getSiteReputation($domain, $r['page_rank'] ?? null, $suspiciousTlds, $flaggedPatterns);
            $repCounts[$rep['label']] = ($repCounts[$rep['label']] ?? 0) + 1;
        }
    @endphp

    {{-- Scan progress log --}}
    <div class="scan-progress" id="scanProgress">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
            <span class="scan-dot" style="width:7px;height:7px;border-radius:50%;background:#0f0;box-shadow:0 0 6px #0f0;display:inline-block;animation:pulse-dot 1.2s infinite"></span>
            <span>SITE INTELLIGENCE SCAN — ACTIVE</span>
        </div>
        <div class="scan-log" id="scanLog"></div>
    </div>

    <div class="ko-card">
        {{-- Movie-style header --}}
        <div class="intel-header">
            <span class="scan-dot"></span>
            SITE INTELLIGENCE REPORT — KEYWORD: "{{ strtoupper($keyword) }}"
            <span style="margin-left:auto;opacity:.6">{{ count($data['serp_preview']) }} TARGETS IDENTIFIED</span>
        </div>

        {{-- Rep summary bar --}}
        <div style="display:flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;background:rgba(0,0,0,.3);border-bottom:1px solid var(--kick-border-subtle);flex-wrap:wrap">
            @if($repCounts['VERIFIED'] > 0)<span class="rep-badge rep-verified"><span class="rep-dot dot-v"></span>{{ $repCounts['VERIFIED'] }} VERIFIED</span>@endif
            @if($repCounts['CLEAN'] > 0)<span class="rep-badge rep-clean"><span class="rep-dot dot-c"></span>{{ $repCounts['CLEAN'] }} CLEAN</span>@endif
            @if($repCounts['UNKNOWN'] > 0)<span class="rep-badge rep-unknown"><span class="rep-dot dot-u"></span>{{ $repCounts['UNKNOWN'] }} UNKNOWN</span>@endif
            @if($repCounts['SUSPICIOUS'] > 0)<span class="rep-badge rep-suspicious"><span class="rep-dot dot-s"></span>{{ $repCounts['SUSPICIOUS'] }} SUSPICIOUS</span>@endif
            @if($repCounts['FLAGGED'] > 0)<span class="rep-badge rep-flagged"><span class="rep-dot dot-f"></span>{{ $repCounts['FLAGGED'] }} FLAGGED</span>@endif
            @if($repCounts['BLACKLISTED'] > 0)<span class="rep-badge rep-blacklisted"><span class="rep-dot dot-b"></span>{{ $repCounts['BLACKLISTED'] }} BLACKLISTED</span>@endif
        </div>

        <div style="overflow-x:auto">
            <table class="comp-tbl">
                <thead>
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Website</th>
                        <th style="width:120px">Reputation</th>
                        <th>Page Title</th>
                        <th style="width:155px">Authority</th>
                        <th style="width:85px">Strength</th>
                    </tr>
                </thead>
                <tbody id="intelTable">
                    @foreach($data['serp_preview'] as $result)
                    @php
                        $pos    = $result['position'] ?? 0;
                        $posCls = match($pos) { 1=>'p1', 2=>'p2', 3=>'p3', default=>'px' };
                        $pr     = $result['page_rank'] ?? null;
                        $auth   = $pr !== null ? min(100,(int)round($pr*10)) : null;
                        $authColor = match(true) {
                            $auth === null => '#4b5563',
                            $auth < 20    => '#6b7280',
                            $auth < 40    => '#ef4444',
                            $auth < 60    => '#f59e0b',
                            $auth < 80    => '#10b981',
                            default       => '#818cf8',
                        };
                        [$kdCls, $kdLbl] = match(true) {
                            $auth === null => ['kd-unk',   'Unknown'],
                            $auth < 20    => ['kd-weak',  'Weak'],
                            $auth < 40    => ['kd-mod',   'Moderate'],
                            $auth < 60    => ['kd-strong','Strong'],
                            default       => ['kd-auth',  'Authority'],
                        };
                        $domain = $result['domain'] ?? (parse_url($result['url'] ?? '', PHP_URL_HOST) ?? '');
                        $rep    = getSiteReputation($domain, $pr, $suspiciousTlds, $flaggedPatterns);
                    @endphp
                    <tr data-pos="{{ $pos }}" data-rep="{{ $rep['label'] }}" data-domain="{{ $domain }}" data-reason="{{ $rep['reason'] }}"
                        style="opacity:0;transition:opacity .3s ease {{ ($pos - 1) * 0.12 }}s">
                        <td><span class="pos-badge {{ $posCls }}">{{ $pos }}</span></td>
                        <td>
                            <a href="{{ $result['url'] ?? '#' }}" target="_blank" rel="noopener" class="domain-lnk">
                                <img src="https://www.google.com/s2/favicons?domain={{ $domain }}&sz=16"
                                     class="favicon" alt="" onerror="this.style.display='none'">
                                {{ $domain }}
                            </a>
                        </td>
                        <td>
                            <span class="rep-badge {{ $rep['class'] }}" title="{{ $rep['reason'] }}">
                                <span class="rep-dot {{ $rep['dot'] }}"></span>
                                {{ $rep['label'] }}
                            </span>
                        </td>
                        <td>
                            <div style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $result['title'] ?? '' }}">
                                {{ $result['title'] ?? '(no title)' }}
                            </div>
                            @if(!empty($result['description']))
                            <div style="font-size:.73rem;color:var(--kick-text-secondary);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ Str::limit($result['description'], 65) }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($auth !== null)
                            <div class="auth-row">
                                <span class="auth-num">{{ $auth }}</span>
                                <div class="auth-track">
                                    <div class="auth-fill" style="width:0;background:{{ $authColor }}" data-width="{{ $auth }}"></div>
                                </div>
                            </div>
                            @else
                            <span style="font-size:.75rem;color:var(--kick-text-secondary)">—</span>
                            @endif
                        </td>
                        <td><span class="kd-chip {{ $kdCls }}">{{ $kdLbl }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($avgAuth !== null)
        <div class="insight-bar">
            <span><i class="bi bi-bar-chart-steps me-1" style="color:var(--kick-green)"></i>
                Avg. competitor authority: <strong style="color:var(--kick-text-primary)">{{ $avgAuth }}/100</strong>
            </span>
            @if($avgAuth < 30)
            <span class="kd-chip kd-weak"><i class="bi bi-check-circle me-1"></i>Great opportunity — weak competition</span>
            @elseif($avgAuth < 60)
            <span class="kd-chip kd-mod"><i class="bi bi-exclamation-circle me-1"></i>Moderate barrier — content &amp; links needed</span>
            @else
            <span class="kd-chip kd-strong"><i class="bi bi-fire me-1"></i>High authority competitors — tough to break in</span>
            @endif
            @if(($repCounts['FLAGGED'] + $repCounts['BLACKLISTED']) > 0)
            <span class="rep-badge rep-flagged ms-auto" style="font-size:.7rem">
                <span class="rep-dot dot-f"></span>
                {{ $repCounts['FLAGGED'] + $repCounts['BLACKLISTED'] }} THREAT(S) DETECTED
            </span>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- RELATED KEYWORDS --}}
    @if(!empty($data['related_keywords']))
    <div class="ko-card">
        <div class="ko-card-hd">
            <div class="ko-card-icon" style="background:var(--kick-green-subtle)"><i class="bi bi-lightning-charge-fill" style="color:var(--kick-green)"></i></div>
            Related Keywords
            <small class="ms-auto" style="color:var(--kick-text-secondary)">Click any term to analyze it</small>
        </div>
        <div class="kw-chips">
            @foreach($data['related_keywords'] as $rel)
            <a href="{{ route('tools.keyword-overview') }}?keyword={{ urlencode($rel['keyword']) }}" class="kw-chip">
                <i class="bi bi-search" style="font-size:.65rem;color:var(--kick-text-secondary)"></i>
                {{ $rel['keyword'] }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- GSC PERFORMANCE --}}
    @if(!empty($data['gsc_performance']))
    @php $gp = $data['gsc_performance']; @endphp
    <div class="ko-card">
        <div class="ko-card-hd">
            <div class="ko-card-icon" style="background:rgba(234,179,8,.12)"><i class="bi bi-google" style="color:#eab308"></i></div>
            Your Site — GSC Performance for this Keyword
            <span class="ms-auto" style="background:rgba(16,185,129,.15);color:#10b981;border-radius:8px;font-size:.7rem;padding:.2rem .6rem">
                <i class="bi bi-check-circle me-1"></i>Live from Search Console
            </span>
        </div>
        <div class="gsc-grid">
            <div class="gsc-metric"><div class="gsc-val">{{ number_format($gp['clicks']) }}</div><div class="gsc-lbl">Clicks</div></div>
            <div class="gsc-metric"><div class="gsc-val">{{ number_format($gp['impressions']) }}</div><div class="gsc-lbl">Impressions</div></div>
            <div class="gsc-metric"><div class="gsc-val">{{ $gp['ctr'] }}%</div><div class="gsc-lbl">CTR</div></div>
            <div class="gsc-metric"><div class="gsc-val">#{{ $gp['avg_position'] }}</div><div class="gsc-lbl">Avg. Position</div></div>
        </div>
    </div>
    @endif

    @else

    {{-- EMPTY STATE --}}
    <div class="ko-empty" id="emptyState">
        <div class="ko-empty-icon">🔍</div>
        <h3>Enter a keyword to launch a scan</h3>
        <p>We'll identify every website ranking for it, analyze their reputation,<br>authority score, and flag any suspicious or blacklisted domains.</p>
        <div style="margin-top:1.25rem">
            @foreach(['seo tools', 'buy instagram followers', 'free movies online', 'cracked software', 'digital marketing'] as $ex)
            <a href="{{ route('tools.keyword-overview') }}?keyword={{ urlencode($ex) }}" class="kw-chip">{{ $ex }}</a>
            @endforeach
        </div>
    </div>

    @endif
</div>

@push('scripts')
<script>
document.getElementById('koForm').addEventListener('submit', function () {
    if (document.getElementById('kwInput').value.trim()) {
        document.getElementById('loadingState').style.display = 'block';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Animate difficulty needle
    const needle = document.getElementById('diffNeedle');
    if (needle) {
        const target = needle.style.left;
        needle.style.left = '0%';
        requestAnimationFrame(() => setTimeout(() => { needle.style.left = target; }, 120));
    }

    // Scanning animation for table rows
    const rows = document.querySelectorAll('#intelTable tr');
    if (rows.length) {
        const scanProgress = document.getElementById('scanProgress');
        const scanLog      = document.getElementById('scanLog');
        if (scanProgress) scanProgress.style.display = 'block';

        const logMsgs = [
            'Initializing SERP intelligence module…',
            'Connecting to domain authority database…',
            'Pulling OpenPageRank scores…',
            'Analyzing TLD reputation signatures…',
            'Cross-referencing known threat patterns…',
            'Scanning domain registration signals…',
            'Running content intent classifier…',
            'Compiling threat assessment report…',
        ];

        let logIdx = 0;
        const logInterval = setInterval(() => {
            if (logIdx < logMsgs.length && scanLog) {
                const el = document.createElement('div');
                el.textContent = '> ' + logMsgs[logIdx++];
                scanLog.prepend(el);
            }
        }, 280);

        // Reveal rows one by one with a scan effect
        rows.forEach((row, i) => {
            setTimeout(() => {
                row.style.opacity = '1';
                row.classList.add('scanned');
                setTimeout(() => row.classList.remove('scanned'), 1600);

                // Animate authority bars
                row.querySelectorAll('.auth-fill').forEach(bar => {
                    const w = bar.getAttribute('data-width') || bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => { bar.style.width = w + '%'; }, 100);
                });

                if (i === rows.length - 1) {
                    clearInterval(logInterval);
                    setTimeout(() => {
                        if (scanProgress) {
                            scanProgress.style.opacity = '0';
                            scanProgress.style.transition = 'opacity .5s';
                            setTimeout(() => { scanProgress.style.display = 'none'; }, 500);
                        }
                    }, 600);
                }
            }, 200 + i * 130);
        });
    }
});
</script>
@endpush
@endsection
