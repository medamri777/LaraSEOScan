@extends('layouts.app')

@section('title', 'SERP Simulator - Seo4ma')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4" style="max-width: 1200px; margin: 0 auto;">

    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-white fw-bold d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-window-sidebar text-success"></i>
            <span>SERP Simulator</span>
        </h2>
        <p class="m-0" style="color: #CBD5E1; font-size: 0.9rem;">Preview how your page appears in Google search results. Type and see changes instantly.</p>
    </div>

    <div class="row g-4">
        <!-- Left: Input Form -->
        <div class="col-lg-5">
            <div class="serp-card">
                <div class="serp-card-header">
                    <i class="bi bi-pencil-square text-success"></i>
                    <span class="fw-bold text-white">Edit Your Snippet</span>
                </div>
                <div class="serp-card-body">
                    <div class="mb-4">
                        <label class="serp-label">Page Title</label>
                        <input type="text" id="titleInput" class="serp-input"
                               placeholder="Best SEO Tools for 2026 — Free Audit"
                               value="{{ $title }}" maxlength="70">
                        <div class="d-flex justify-content-between mt-1">
                            <small style="color: #64748b; font-size: 0.75rem;">50-60 chars recommended</small>
                            <small id="titleCount" class="fw-bold" style="font-size: 0.75rem;">0/60</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="serp-label">Meta Description</label>
                        <textarea id="descInput" class="serp-input" rows="3"
                                  placeholder="Discover the best SEO tools to rank higher on Google. Free audit included..."
                                  maxlength="160">{{ $description }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small style="color: #64748b; font-size: 0.75rem;">150-160 chars recommended</small>
                            <small id="descCount" class="fw-bold" style="font-size: 0.75rem;">0/160</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="serp-label">URL</label>
                        <input type="text" id="urlInput" class="serp-input"
                               placeholder="https://example.com/seo-tools"
                               value="{{ $url }}">
                    </div>

                    <div class="mb-3">
                        <label class="serp-label">Target Keyword <span style="color: #64748b; font-weight: 400;">(highlighted in preview)</span></label>
                        <input type="text" id="keywordInput" class="serp-input"
                               placeholder="e.g. seo tools"
                               value="{{ $keyword }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Live Preview -->
        <div class="col-lg-7">
            <!-- Google Preview Card -->
            <div class="serp-card mb-4">
                <div class="serp-card-header">
                    <i class="bi bi-google text-success"></i>
                    <span class="fw-bold text-white">Google Search Preview</span>
                    <span class="ms-auto badge" style="background: rgba(83,252,24,0.1); color: #53FC18; font-size: 0.7rem; padding: 4px 10px; border-radius: 20px;">LIVE</span>
                </div>
                <div class="serp-card-body">
                    <div class="google-preview-box">
                        <!-- Breadcrumb URL -->
                        <div class="d-flex align-items-center gap-1 mb-1" style="font-size: 14px; line-height: 1.4;">
                            <span id="previewDomain" style="color: #202124;">example.com</span>
                            <span style="color: #70757a;">›</span>
                            <span id="previewPath" style="color: #4d5156;">page</span>
                        </div>
                        <!-- Title -->
                        <div id="previewTitle" class="google-title">
                            Your Page Title Here
                        </div>
                        <!-- Description -->
                        <div id="previewDesc" class="google-desc">
                            Your meta description will appear here. Make it compelling and include your target keyword for better click-through rates.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Preview -->
            <div class="serp-card mb-4">
                <div class="serp-card-header">
                    <i class="bi bi-phone text-success"></i>
                    <span class="fw-bold text-white">Mobile Preview</span>
                </div>
                <div class="serp-card-body">
                    <div class="google-preview-mobile">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: #e8eaed; display: flex; align-items: center; justify-content: center;">
                                <span id="mobileFavicon" style="font-size: 14px; font-weight: 700; color: #202124;">E</span>
                            </div>
                            <div>
                                <div id="mobileDomain" style="font-size: 12px; color: #202124; line-height: 1.3;">example.com</div>
                                <div style="font-size: 11px; color: #70757a; line-height: 1.3;" id="mobilePath">› page</div>
                            </div>
                        </div>
                        <div id="mobileTitle" class="google-title-mobile">
                            Your Page Title Here
                        </div>
                        <div id="mobileDesc" class="google-desc-mobile">
                            Your meta description will appear here...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="serp-card">
                <div class="serp-card-header">
                    <i class="bi bi-lightbulb text-warning"></i>
                    <span class="fw-bold text-white">Optimization Tips</span>
                </div>
                <div class="serp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="tip-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Keep title between <strong>50-60</strong> characters</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="tip-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Description: <strong>150-160</strong> characters max</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="tip-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Put keyword <strong>near the start</strong> of title</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="tip-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Add a <strong>call-to-action</strong> in description</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .serp-card {
        background: var(--kick-surface-1);
        border: 1px solid var(--kick-border-subtle);
        border-radius: 12px;
        overflow: hidden;
    }
    .serp-card-header {
        background: var(--kick-surface-2);
        border-bottom: 1px solid var(--kick-border-muted);
        padding: 0.85rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }
    .serp-card-body {
        padding: 1.25rem;
    }
    .serp-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #CBD5E1;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .serp-input {
        width: 100%;
        background: var(--kick-bg-base);
        border: 1px solid var(--kick-border-subtle);
        border-radius: 8px;
        color: #fff;
        font-size: 0.875rem;
        padding: 0.6rem 0.85rem;
        transition: border-color 0.15s, box-shadow 0.15s;
        resize: vertical;
    }
    .serp-input:focus {
        outline: none;
        border-color: var(--kick-green);
        box-shadow: 0 0 0 3px rgba(83, 252, 24, 0.12);
        background: var(--kick-bg-base);
        color: #fff;
    }
    .serp-input::placeholder {
        color: #4B5563;
    }

    /* Google Desktop Preview */
    .google-preview-box {
        background: #fff;
        border-radius: 10px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e5e7eb;
    }
    .google-title {
        font-family: Arial, sans-serif;
        font-size: 20px;
        color: #1a0dab;
        line-height: 1.3;
        text-decoration: none;
        cursor: pointer;
        margin-bottom: 4px;
    }
    .google-title:hover {
        text-decoration: underline;
    }
    .google-desc {
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #4d5156;
        line-height: 1.58;
    }

    /* Google Mobile Preview */
    .google-preview-mobile {
        background: #fff;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        border: 1px solid #e5e7eb;
        max-width: 400px;
    }
    .google-title-mobile {
        font-family: Arial, sans-serif;
        font-size: 16px;
        color: #1a0dab;
        line-height: 1.25;
        margin-bottom: 3px;
        cursor: pointer;
    }
    .google-title-mobile:hover {
        text-decoration: underline;
    }
    .google-desc-mobile {
        font-family: Arial, sans-serif;
        font-size: 13px;
        color: #4d5156;
        line-height: 1.5;
    }

    .tip-item {
        display: flex;
        align-items: center;
        font-size: 0.82rem;
        color: #CBD5E1;
    }
    .tip-item strong {
        color: #fff;
    }

    mark {
        background: rgba(83, 252, 24, 0.25);
        color: #1a0dab;
        font-weight: 700;
        padding: 0 2px;
        border-radius: 2px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('titleInput');
    const descInput = document.getElementById('descInput');
    const urlInput = document.getElementById('urlInput');
    const keywordInput = document.getElementById('keywordInput');
    const titleCount = document.getElementById('titleCount');
    const descCount = document.getElementById('descCount');

    // Desktop preview elements
    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');
    const previewDomain = document.getElementById('previewDomain');
    const previewPath = document.getElementById('previewPath');

    // Mobile preview elements
    const mobileTitle = document.getElementById('mobileTitle');
    const mobileDesc = document.getElementById('mobileDesc');
    const mobileDomain = document.getElementById('mobileDomain');
    const mobilePath = document.getElementById('mobilePath');
    const mobileFavicon = document.getElementById('mobileFavicon');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function highlightKeyword(text, keyword) {
        if (!keyword || !text) return escapeHtml(text);
        const escaped = escapeHtml(text);
        const escapedKeyword = escapeHtml(keyword).trim();
        if (!escapedKeyword) return escaped;
        const regex = new RegExp('(' + escapedKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        // Only highlight if keyword actually found in text
        if (!regex.test(escaped)) return escaped;
        return escaped.replace(regex, '<mark>$1</mark>');
    }

    function updatePreview() {
        const title = titleInput.value || 'Your Page Title Here';
        const desc = descInput.value || 'Your meta description will appear here. Make it compelling and include your target keyword for better click-through rates.';
        const url = urlInput.value || 'https://example.com/page';
        const keyword = keywordInput.value;

        // Parse URL - auto-add https:// and handle plain text
        let domain = 'example.com';
        let path = 'page';
        let urlValue = url.trim();
        // Auto-add protocol if missing
        if (urlValue && !urlValue.match(/^https?:\/\//i)) {
            urlValue = 'https://' + urlValue;
        }
        try {
            const parsed = new URL(urlValue);
            domain = parsed.hostname || 'example.com';
            path = parsed.pathname.replace(/^\/+/, '').replace(/\/+$/, '') || 'page';
        } catch (e) {
            // If still invalid, use the raw input as domain
            domain = url.trim() || 'example.com';
            path = '';
        }

        // Desktop preview
        previewDomain.textContent = domain;
        previewPath.textContent = path;
        previewTitle.innerHTML = highlightKeyword(title, keyword);
        previewDesc.innerHTML = highlightKeyword(desc, keyword);

        // Mobile preview
        mobileDomain.textContent = domain;
        mobilePath.textContent = '› ' + path;
        mobileTitle.innerHTML = highlightKeyword(title, keyword);
        mobileFavicon.textContent = domain.charAt(0).toUpperCase();

        // Truncate mobile desc
        const mobileDescText = desc.length > 120 ? desc.substring(0, 120) + '...' : desc;
        mobileDesc.innerHTML = highlightKeyword(mobileDescText, keyword);

        // Character counts
        const tLen = titleInput.value.length;
        titleCount.textContent = tLen + '/60';
        titleCount.style.color = tLen > 60 ? '#E91916' : (tLen >= 50 ? '#53FC18' : '#f59e0b');

        const dLen = descInput.value.length;
        descCount.textContent = dLen + '/160';
        descCount.style.color = dLen > 160 ? '#E91916' : (dLen >= 140 ? '#53FC18' : '#f59e0b');
    }

    // Live update on every keystroke
    titleInput.addEventListener('input', updatePreview);
    descInput.addEventListener('input', updatePreview);
    urlInput.addEventListener('input', updatePreview);
    keywordInput.addEventListener('input', updatePreview);

    // Initialize on load
    updatePreview();
});
</script>
@endsection
