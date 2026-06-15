@extends('layouts.app')

@section('title', 'Google Review Link Generator - Seo4ma')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4" style="max-width: 1100px; margin: 0 auto;">

    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-white fw-bold d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-star-fill" style="color: #f59e0b;"></i>
            <span>Google Review Link Generator</span>
        </h2>
        <p class="m-0" style="color: #CBD5E1; font-size: 0.9rem;">Paste your Google Maps URL or Place ID to generate a direct review link, QR code, and share templates.</p>
    </div>

    <div x-data="reviewApp()" x-init="init()">

        <!-- Input Card -->
        <div class="rlg-card mb-4">
            <div class="rlg-card-body">
                <label class="rlg-label mb-2">Google Maps URL or Place ID</label>
                <div class="rlg-search-row">
                    <i class="bi bi-geo-alt-fill" style="color: #53FC18; flex-shrink: 0;"></i>
                    <input type="text" x-model="userInput" @keydown.enter.prevent="generate()"
                           class="rlg-search-input"
                           placeholder="Paste Google Maps link, Place ID (ChIJ...), or business name...">
                    <button @click="generate()" :disabled="loading || !userInput.trim()" class="rlg-btn-primary">
                        <i class="bi" :class="loading ? 'bi-hourglass-split animate-spin' : 'bi-link-45deg'"></i>
                        <span x-text="loading ? 'Generating...' : 'Generate'"></span>
                    </button>
                </div>

                <!-- Example inputs -->
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <span style="color: #4B5563; font-size: 0.78rem;">Try:</span>
                    <button @click="userInput = 'https://www.google.com/maps/place/Restaurant+Al+Amine/@33.5731,-7.5898,17z/data=!4m6!3m5!1s0xda7cd4f8e1b46c1:0x7e5e6a8e5b2a1234!8m2!3d33.5731!4d-7.5898'" class="rlg-example-btn">
                        Maps URL
                    </button>
                    <button @click="userInput = 'ChIJN1t_tDeuEmsRUsoyG83frY4'" class="rlg-example-btn">
                        Place ID
                    </button>
                    <button @click="userInput = 'Cafe Clock Fez'" class="rlg-example-btn">
                        Business Name
                    </button>
                </div>

                <!-- How to find -->
                <div class="mt-3 p-3" style="background: var(--kick-bg-base); border-radius: 8px; border: 1px solid var(--kick-border-subtle);">
                    <p class="mb-1" style="color: #CBD5E1; font-size: 0.82rem; font-weight: 600;">
                        <i class="bi bi-info-circle me-1 text-success"></i> How to get your Google Maps URL:
                    </p>
                    <ol class="mb-0 ps-3" style="color: #8B919D; font-size: 0.78rem; line-height: 1.8;">
                        <li>Open <a href="https://maps.google.com" target="_blank" style="color: #53FC18;">Google Maps</a> and search your business</li>
                        <li>Click on your business listing</li>
                        <li>Click <strong style="color:#CBD5E1;">Share</strong> → <strong style="color:#CBD5E1;">Copy link</strong></li>
                        <li>Paste it above</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Error -->
        <template x-if="error">
            <div class="rlg-alert rlg-alert-error mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span x-text="error"></span>
            </div>
        </template>

        <!-- Results -->
        <template x-if="data">
            <div>
                <!-- Business Info Card -->
                <div class="rlg-card mb-4">
                    <div class="rlg-card-header">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span class="fw-bold text-white">Business Detected</span>
                    </div>
                    <div class="rlg-card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rlg-result-icon-lg">
                                <i class="bi bi-building-fill" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-1" x-text="data.business_name || 'Business'"></h5>
                                <div class="d-flex gap-3 flex-wrap mt-2">
                                    <template x-if="data.place_id">
                                        <span class="rlg-stat-badge">
                                            <i class="bi bi-key-fill me-1"></i> Place ID found
                                        </span>
                                    </template>
                                    <template x-if="data.cid">
                                        <span class="rlg-stat-badge">
                                            <i class="bi bi-hash me-1"></i> CID: <span x-text="data.cid"></span>
                                        </span>
                                    </template>
                                    <template x-if="data.coords">
                                        <span class="rlg-stat-badge">
                                            <i class="bi bi-pin-map me-1"></i> <span x-text="data.coords.lat + ', ' + data.coords.lng"></span>
                                        </span>
                                    </template>
                                </div>
                                <template x-if="data.place_id">
                                    <div class="mt-2" style="font-size: 0.75rem; color: #4B5563; font-family: monospace; word-break: break-all;" x-text="'Place ID: ' + data.place_id"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left: Links -->
                    <div class="col-lg-7">
                        <div class="rlg-card mb-4">
                            <div class="rlg-card-header">
                                <i class="bi bi-link-45deg text-success"></i>
                                <span class="fw-bold text-white">Generated Links</span>
                            </div>
                            <div class="rlg-card-body">
                                <!-- Review Link -->
                                <div class="mb-4">
                                    <label class="rlg-label-sm mb-2">
                                        <i class="bi bi-star-fill me-1" style="color: #f59e0b;"></i> Google Review Link (main)
                                    </label>
                                    <div class="rlg-link-box">
                                        <input type="text" :value="data.review_url" readonly class="rlg-link-input">
                                        <button @click="copy(data.review_url, 'copyMain')" class="rlg-btn-copy" id="copyMain">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Short Link -->
                                <template x-if="data.short_link">
                                    <div class="mb-4">
                                        <label class="rlg-label-sm mb-2">
                                            <i class="bi bi-link me-1 text-success"></i> Short Link (SMS / social media)
                                        </label>
                                        <div class="rlg-link-box">
                                            <input type="text" :value="data.short_link" readonly class="rlg-link-input">
                                            <button @click="copy(data.short_link, 'copyShort')" class="rlg-btn-copy" id="copyShort">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Maps Link -->
                                <template x-if="data.maps_link">
                                    <div class="mb-3">
                                        <label class="rlg-label-sm mb-2">
                                            <i class="bi bi-geo-alt me-1 text-info"></i> Google Maps Link
                                        </label>
                                        <div class="rlg-link-box">
                                            <input type="text" :value="data.maps_link" readonly class="rlg-link-input">
                                            <button @click="copy(data.maps_link, 'copyMaps')" class="rlg-btn-copy" id="copyMaps">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Share Buttons -->
                                <div class="d-flex gap-2 flex-wrap mt-3">
                                    <a :href="whatsappUrl" target="_blank" class="rlg-share-btn rlg-share-whatsapp">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </a>
                                    <a :href="emailUrl" class="rlg-share-btn rlg-share-email">
                                        <i class="bi bi-envelope"></i> Email
                                    </a>
                                    <a :href="smsUrl" class="rlg-share-btn rlg-share-sms">
                                        <i class="bi bi-chat-dots"></i> SMS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: QR + Template -->
                    <div class="col-lg-5">
                        <!-- QR Code -->
                        <div class="rlg-card mb-4">
                            <div class="rlg-card-header">
                                <i class="bi bi-qr-code text-success"></i>
                                <span class="fw-bold text-white">QR Code</span>
                            </div>
                            <div class="rlg-card-body text-center">
                                <div class="rlg-qr-box">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(data.review_url)" alt="QR Code" style="max-width: 170px;">
                                </div>
                                <p class="mt-3 mb-0" style="color: #64748b; font-size: 0.78rem;">
                                    Print for your counter, receipts, or business cards
                                </p>
                                <a :href="'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' + encodeURIComponent(data.review_url)"
                                   download="review-qr.png" target="_blank" class="rlg-btn-outline mt-3 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-download"></i> Download QR
                                </a>
                            </div>
                        </div>

                        <!-- Message Template -->
                        <div class="rlg-card">
                            <div class="rlg-card-header">
                                <i class="bi bi-chat-square-text text-success"></i>
                                <span class="fw-bold text-white">Review Request Message</span>
                            </div>
                            <div class="rlg-card-body">
                                <p class="rlg-label-sm mb-2">Copy this to send to customers:</p>
                                <div class="rlg-template-box" id="templateBox">
                                    <p class="mb-2">Hello! 👋</p>
                                    <p class="mb-2">Thank you for choosing <strong x-text="data.business_name || 'us'"></strong>. We'd love your feedback!</p>
                                    <p class="mb-2">Could you take 30 seconds to leave us a Google review? It helps us a lot ⭐</p>
                                    <p class="mb-0">👉 <span style="color: #53FC18;" x-text="data.short_link || data.review_url"></span></p>
                                </div>
                                <button @click="copyTemplate()" class="rlg-btn-outline w-100 mt-3 d-flex align-items-center justify-content-center gap-1" id="copyTemplateBtn">
                                    <i class="bi bi-clipboard"></i> Copy Message
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="!data && !error">
            <div class="rlg-card text-center py-5">
                <div style="font-size: 3rem; color: #53FC18; opacity: 0.3;">
                    <i class="bi bi-star"></i>
                </div>
                <h5 class="text-white fw-bold mt-3">Generate Your Review Link</h5>
                <p style="color: #8B919D; font-size: 0.9rem; max-width: 420px; margin: 0 auto;">
                    Paste your Google Maps URL above and get a direct review link, QR code, and share templates — all in one click.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
                    <div class="rlg-feature-pill"><i class="bi bi-link-45deg text-success me-1"></i> Review Link</div>
                    <div class="rlg-feature-pill"><i class="bi bi-qr-code text-success me-1"></i> QR Code</div>
                    <div class="rlg-feature-pill"><i class="bi bi-whatsapp text-success me-1"></i> Share</div>
                    <div class="rlg-feature-pill"><i class="bi bi-chat-square-text text-success me-1"></i> Template</div>
                </div>
            </div>
        </template>

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .rlg-card { background: var(--kick-surface-1); border: 1px solid var(--kick-border-subtle); border-radius: 12px; overflow: hidden; }
    .rlg-card-header { background: var(--kick-surface-2); border-bottom: 1px solid var(--kick-border-muted); padding: 0.85rem 1.25rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
    .rlg-card-body { padding: 1.25rem; }
    .rlg-label { font-size: 0.8rem; font-weight: 600; color: #CBD5E1; text-transform: uppercase; letter-spacing: 0.3px; }
    .rlg-label-sm { font-size: 0.75rem; font-weight: 600; color: #8B919D; text-transform: uppercase; letter-spacing: 0.3px; display: block; }

    .rlg-search-row { display: flex; align-items: center; gap: 0.6rem; background: var(--kick-bg-base); border: 1px solid var(--kick-border-subtle); border-radius: 10px; padding: 0.3rem 0.3rem 0.3rem 0.85rem; }
    .rlg-search-row:focus-within { border-color: var(--kick-green); box-shadow: 0 0 0 3px rgba(83,252,24,0.1); }
    .rlg-search-input { flex: 1; background: transparent; border: none; color: #fff; font-size: 0.9rem; padding: 0.55rem 0; min-width: 0; }
    .rlg-search-input:focus { outline: none; }
    .rlg-search-input::placeholder { color: #4B5563; }

    .rlg-btn-primary { background: linear-gradient(135deg, var(--kick-green), var(--kick-green-hover)); color: #000; border: none; font-weight: 700; font-size: 0.82rem; border-radius: 8px; padding: 0.55rem 1.1rem; display: flex; align-items: center; gap: 0.4rem; cursor: pointer; transition: filter 0.15s; white-space: nowrap; }
    .rlg-btn-primary:hover { filter: brightness(1.1); }
    .rlg-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

    .rlg-example-btn { background: var(--kick-surface-3); border: 1px solid var(--kick-border-muted); color: #8B919D; font-size: 0.75rem; border-radius: 6px; padding: 3px 10px; cursor: pointer; transition: all 0.15s; }
    .rlg-example-btn:hover { border-color: var(--kick-green); color: var(--kick-green); }

    .rlg-alert { border-radius: 10px; padding: 0.85rem 1.1rem; font-size: 0.85rem; display: flex; align-items: center; border: 1px solid; }
    .rlg-alert-error { background: rgba(233,25,22,0.08); border-color: rgba(233,25,22,0.25); color: #E91916; }

    .rlg-result-icon-lg { width: 52px; height: 52px; border-radius: 12px; background: var(--kick-green-subtle); color: var(--kick-green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rlg-stat-badge { font-size: 0.78rem; background: var(--kick-green-subtle); color: var(--kick-green); padding: 3px 10px; border-radius: 20px; font-weight: 600; }

    .rlg-link-box { display: flex; background: var(--kick-bg-base); border: 1px solid var(--kick-border-subtle); border-radius: 8px; overflow: hidden; }
    .rlg-link-input { flex: 1; background: transparent; border: none; color: #CBD5E1; font-size: 0.82rem; padding: 0.6rem 0.85rem; font-family: monospace; min-width: 0; }
    .rlg-link-input:focus { outline: none; }
    .rlg-btn-copy { background: var(--kick-surface-3); border: none; border-left: 1px solid var(--kick-border-subtle); color: var(--kick-green); padding: 0.5rem 0.9rem; cursor: pointer; transition: background 0.15s; flex-shrink: 0; }
    .rlg-btn-copy:hover { background: var(--kick-border-muted); }

    .rlg-btn-outline { background: transparent; border: 1px solid var(--kick-border-muted); color: #CBD5E1; font-size: 0.82rem; font-weight: 600; border-radius: 8px; padding: 0.5rem 1rem; cursor: pointer; transition: all 0.15s; text-decoration: none; }
    .rlg-btn-outline:hover { border-color: var(--kick-green); color: var(--kick-green); }

    .rlg-share-btn { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; transition: all 0.15s; }
    .rlg-share-whatsapp { background: rgba(37,211,102,0.1); color: #25D366; border: 1px solid rgba(37,211,102,0.2); }
    .rlg-share-whatsapp:hover { background: rgba(37,211,102,0.2); color: #25D366; }
    .rlg-share-email { background: rgba(59,130,246,0.1); color: #3B82F6; border: 1px solid rgba(59,130,246,0.2); }
    .rlg-share-email:hover { background: rgba(59,130,246,0.2); color: #3B82F6; }
    .rlg-share-sms { background: rgba(168,85,247,0.1); color: #A855F7; border: 1px solid rgba(168,85,247,0.2); }
    .rlg-share-sms:hover { background: rgba(168,85,247,0.2); color: #A855F7; }

    .rlg-qr-box { background: #fff; border-radius: 12px; padding: 1rem; display: inline-block; }
    .rlg-template-box { background: var(--kick-bg-base); border: 1px solid var(--kick-border-subtle); border-radius: 8px; padding: 1rem; font-size: 0.85rem; color: #CBD5E1; line-height: 1.6; }
    .rlg-template-box p { margin: 0; }
    .rlg-feature-pill { font-size: 0.82rem; color: #8B919D; background: var(--kick-surface-2); border: 1px solid var(--kick-border-subtle); border-radius: 20px; padding: 6px 14px; }
</style>

<script>
function reviewApp() {
    return {
        userInput: '',
        loading: false,
        error: null,
        data: null,

        get whatsappUrl() {
            if (!this.data) return '#';
            const name = this.data.business_name || 'us';
            const link = this.data.short_link || this.data.review_url;
            return 'https://wa.me/?text=' + encodeURIComponent('Hello! We would love your feedback on ' + name + '. Leave us a Google review ⭐ ' + link);
        },
        get emailUrl() {
            if (!this.data) return '#';
            const name = this.data.business_name || 'our business';
            const link = this.data.short_link || this.data.review_url;
            return 'mailto:?subject=' + encodeURIComponent('Leave us a review - ' + name) + '&body=' + encodeURIComponent('Hello,\n\nWe hope you had a great experience with ' + name + '. We would appreciate a Google review:\n\n' + link + '\n\nThank you!');
        },
        get smsUrl() {
            if (!this.data) return '#';
            const name = this.data.business_name || 'us';
            const link = this.data.short_link || this.data.review_url;
            return 'sms:?body=' + encodeURIComponent('Hi! Please leave a review for ' + name + ': ' + link);
        },

        init() {},

        async generate() {
            if (!this.userInput.trim() || this.loading) return;
            this.loading = true;
            this.error = null;
            this.data = null;

            try {
                const res = await fetch('{{ route("tools.review-link-generator.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ input: this.userInput.trim() }),
                });
                const d = await res.json();
                if (d.error) {
                    this.error = d.error;
                } else {
                    this.data = d;
                }
            } catch (e) {
                this.error = 'Generation failed: ' + e.message;
            } finally {
                this.loading = false;
            }
        },

        async copy(text, btnId) {
            try {
                await navigator.clipboard.writeText(text);
                const btn = document.getElementById(btnId);
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                    btn.style.color = '#53FC18';
                    setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; }, 1500);
                }
            } catch(e) {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        },
        copyTemplate() {
            const box = document.getElementById('templateBox');
            if (box) this.copy(box.innerText, 'copyTemplateBtn');
        },
    };
}
</script>
@endsection
