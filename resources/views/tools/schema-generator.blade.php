@extends('layouts.app')

@section('title', 'Schema Markup Generator AI - Seo4ma')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1"><i class="bi bi-stars me-2 text-success"></i>Schema Generator AI</h3>
        <p class="text-muted small mb-0">Analyse intelligente de votre site &mdash; génération automatique de JSON‑LD optimisé pour le SEO local.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('tools.schema-generator') }}">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-link-45deg me-1"></i>URL du site <span class="text-danger">*</span></label>
                            <input type="url" name="url" class="form-control shadow-sm rounded-3" style="background: #f3f4f6; color: #6b7280;" value="{{ $currentProject?->url ?? '' }}" readonly placeholder="{{ $currentProject ? '' : 'Create a project first' }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-building me-1"></i>Nom <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="nameField" class="form-control shadow-sm rounded-3" placeholder="Mon entreprise" value="{{ $name }}" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-tag me-1"></i>Type</label>
                                <select name="type" id="typeField" class="form-select shadow-sm rounded-3">
                                    <option value="Restaurant" {{ request('type') == 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                                    <option value="Ecommerce" {{ request('type') == 'Ecommerce' ? 'selected' : '' }}>E-commerce</option>
                                    <option value="LocalBusiness" {{ request('type') == 'LocalBusiness' ? 'selected' : '' }}>Commerce local</option>
                                    <option value="Clinic" {{ request('type') == 'Clinic' ? 'selected' : '' }}>Clinique / Médical</option>
                                    <option value="Agency" {{ request('type') == 'Agency' ? 'selected' : '' }}>Agence / Service</option>
                                    <option value="SaaS" {{ request('type') == 'SaaS' ? 'selected' : '' }}>SaaS / App web</option>
                                    <option value="Blog" {{ request('type') == 'Blog' ? 'selected' : '' }}>Blog / Actualités</option>
                                    <option value="Streaming" {{ request('type') == 'Streaming' ? 'selected' : '' }}>Streaming</option>
                                    <option value="Portfolio" {{ request('type') == 'Portfolio' ? 'selected' : '' }}>Portfolio</option>
                                    <option value="Museum" {{ request('type') == 'Museum' ? 'selected' : '' }}>Musée / Culture</option>
                                    <option value="Other" {{ request('type') == 'Other' ? 'selected' : '' }}>Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-chat-dots me-1"></i>Sujet</label>
                                <input type="text" name="topic" id="topicField" class="form-control shadow-sm rounded-3" placeholder="Pizzeria, Avocat, Hôtel..." value="{{ $topic }}">
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-geo-alt me-1"></i>Ville</label>
                                <input type="text" name="city" id="cityField" class="form-control shadow-sm rounded-3" placeholder="Casablanca" value="{{ $city }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-file-text me-1"></i>Description</label>
                            <div class="position-relative">
                                <textarea name="description" id="descField" class="form-control shadow-sm rounded-3" rows="2" placeholder="Saisissez ou laissez l'IA générer...">{{ $description }}</textarea>
                                <div id="descSpinner" class="position-absolute d-none" style="bottom: 10px; right: 10px;">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                </div>
                            </div>
                            <div id="descStatus" class="form-text small mt-1"><i class="bi bi-robot me-1 text-muted"></i>L'IA génère la description automatiquement.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1"><i class="bi bi-globe me-1"></i>Langue</label>
                            <select name="language" class="form-select shadow-sm rounded-3">
                                @foreach($languages as $value => $label)
                                    <option value="{{ $value }}" {{ $language == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-3 p-3 mb-3" style="background: #f8fafc; border: 1px dashed #d1d5db;">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 w-100 text-start" id="seoToggle">
                                <i class="bi bi-plus-circle me-1"></i> Options SEO avancées
                            </button>
                            <div id="seoFields" style="display: none;" class="mt-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Téléphone</label>
                                        <input type="text" name="telephone" class="form-control form-control-sm shadow-sm rounded-3" placeholder="+212 6XX-XXXXXX" value="{{ request('telephone') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Logo (URL)</label>
                                        <input type="url" name="image" class="form-control form-control-sm shadow-sm rounded-3" placeholder="https://example.com/logo.png" value="{{ request('image') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Adresse</label>
                                    <input type="text" name="street_address" class="form-control form-control-sm shadow-sm rounded-3" placeholder="123 Boulevard Mohammed V" value="{{ request('street_address') }}">
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Code postal</label>
                                        <input type="text" name="postal_code" class="form-control form-control-sm shadow-sm rounded-3" placeholder="20000" value="{{ request('postal_code') }}">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Cuisine</label>
                                        <input type="text" name="cuisine" class="form-control form-control-sm shadow-sm rounded-3" placeholder="Marocaine" value="{{ request('cuisine') }}">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Budget</label>
                                        <select name="price_range" class="form-select form-select-sm shadow-sm rounded-3">
                                            <option value="">—</option>
                                            <option value="$" {{ request('price_range') == '$' ? 'selected' : '' }}>$</option>
                                            <option value="$$" {{ request('price_range') == '$$' ? 'selected' : '' }}>$$</option>
                                            <option value="$$$" {{ request('price_range') == '$$$' ? 'selected' : '' }}>$$$</option>
                                            <option value="$$$$" {{ request('price_range') == '$$$$' ? 'selected' : '' }}>$$$$</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Horaires</label>
                                        <input type="text" name="opening_hours" class="form-control form-control-sm shadow-sm rounded-3" placeholder="Mo-Fr 09:00-18:00" value="{{ request('opening_hours') }}">
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Note</label>
                                        <input type="number" name="rating" class="form-control form-control-sm shadow-sm rounded-3" placeholder="4.5" min="1" max="5" step="0.1" value="{{ request('rating') }}">
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label small fw-semibold text-secondary mb-1">Avis</label>
                                        <input type="number" name="review_count" class="form-control form-control-sm shadow-sm rounded-3" placeholder="50" min="0" value="{{ request('review_count') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="generate" value="1" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                            <i class="bi bi-stars me-1"></i> Générer le schéma avec l'IA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            @if($result)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-check-circle me-2 text-success"></i>
                            Schéma détecté : <span class="badge bg-dark rounded-3 ms-1">{{ $result['schema_type'] ?? 'N/A' }}</span>
                        </h6>
                        <button class="btn btn-sm btn-outline-success rounded-3" onclick="copySchema()">
                            <i class="bi bi-clipboard me-1"></i> Copier
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <pre id="schemaOutput" class="p-4 mb-0" style="background: #1e293b; color: #e2e8f0; font-size: 0.8rem; overflow-x: auto; max-height: 400px; overflow-y: auto;">{{ json_encode($result['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-3 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-code-slash me-2 text-primary"></i>Code HTML à copier</h6>
                    </div>
                    <div class="card-body p-0">
                        <pre id="htmlOutput" class="p-4 mb-0" style="background: #1e293b; color: #e2e8f0; font-size: 0.8rem; overflow-x: auto;">&lt;script type="application/ld+json"&gt;
{{ json_encode($result['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
&lt;/script&gt;</pre>
                        <div class="p-3 border-top">
                            <button class="btn btn-sm btn-outline-success rounded-3" onclick="copyHtml()">
                                <i class="bi bi-clipboard me-1"></i> Copier le code HTML
                            </button>
                        </div>
                    </div>
                </div>

                @if(!empty($result['rich_results_unlocked']))
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Rich Results débloqués</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($result['rich_results_unlocked'] as $feature)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2">{{ $feature }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($result['improvement_tips']))
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-info">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2 text-info"></i>Conseils d'amélioration</h6>
                        <ol class="mb-0 ps-3">
                            @foreach($result['improvement_tips'] as $tip)
                                <li class="mb-1 small">{{ $tip }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
                @endif

                @if(!empty($result['missing_fields_warning']))
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Champs manquants</h6>
                        <p class="small text-muted mb-0">{{ $result['missing_fields_warning'] }}</p>
                    </div>
                </div>
                @endif

                <div class="alert alert-info rounded-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Testez ce schéma avec <a href="https://search.google.com/test/rich-results" target="_blank" class="alert-link">Google Rich Results Test</a>
                </div>

                @if($meta && !empty($meta['description']))
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Meta Description générée</h6>
                            <button class="btn btn-sm btn-outline-primary rounded-3" onclick="copyMeta()">
                                <i class="bi bi-clipboard me-1"></i> Copier
                            </button>
                        </div>
                        <div id="metaOutput" class="p-3 rounded-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 0.9rem;">
                            {{ $meta['description'] }}
                        </div>
                        <div class="row g-2 small">
                            <div class="col-md-4">
                                <span class="text-muted">Caractères :</span>
                                <strong>{{ $meta['character_count'] ?? strlen($meta['description']) }}</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Langue :</span>
                                <strong>{{ $meta['language_used'] ?? $language }}</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Score SEO :</span>
                                <strong class="text-success">{{ $meta['seo_score'] ?? 'good' }}</strong>
                            </div>
                        </div>
                        @if(!empty($meta['main_keyword_used']))
                        <div class="mt-2 small">
                            <span class="text-muted">Mot-clé principal :</span>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-3">{{ $meta['main_keyword_used'] }}</span>
                        </div>
                        @endif
                        @if(!empty($meta['alternative']))
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted fw-semibold">Version alternative :</small>
                            <p class="small text-muted mt-1 mb-0">{{ $meta['alternative'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @elseif(request()->has('generate'))
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 text-center text-warning">
                        <i class="bi bi-robot fs-1 mb-3 d-block"></i>
                        <h5>L'IA n'a pas pu générer le schéma</h5>
                        <p class="small mb-0">Vérifiez que la clé API Groq est configurée ou réessayez avec plus d'informations.</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 text-center text-muted">
                        <i class="bi bi-robot fs-1 mb-3 d-block"></i>
                        <h5>L'IA génère le schéma pour vous</h5>
                        <p class="small">Donnez l'URL et le nom de votre site. L'IA détecte automatiquement le bon type de schéma (LocalBusiness, Restaurant, Article, etc.) et génère un JSON‑LD valide pour les Rich Results Google.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seoBtn = document.getElementById('seoToggle');
    const seoEl = document.getElementById('seoFields');
    if (seoBtn && seoEl) {
        seoBtn.addEventListener('click', function() {
            if (seoEl.style.display === 'none') {
                seoEl.style.display = 'block';
                seoBtn.innerHTML = '<i class="bi bi-dash-circle me-1"></i> Options SEO avancées';
            } else {
                seoEl.style.display = 'none';
                seoBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Options SEO avancées';
            }
        });
    }
});

let descTimeout;

document.getElementById('topicField').addEventListener('input', function() {
    clearTimeout(descTimeout);
    const topic = this.value.trim();
    if (topic.length < 3) return;

    descTimeout = setTimeout(() => {
        const city = document.getElementById('cityField').value.trim();
        const spinner = document.getElementById('descSpinner');
        const descField = document.getElementById('descField');
        const statusEl = document.getElementById('descStatus');

        spinner.classList.remove('d-none');
        statusEl.textContent = 'Génération de la description...';

        fetch('{{ route("tools.schema-generator.description") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ topic, city })
        })
        .then(r => r.json())
        .then(data => {
            spinner.classList.add('d-none');
            if (data.description) {
                descField.value = data.description;
                descField.dispatchEvent(new Event('input'));
                statusEl.textContent = '✓ Description générée par l\'IA. Vous pouvez la modifier.';
                statusEl.className = 'form-text small text-success mt-1';
            } else {
                statusEl.textContent = 'Impossible de générer la description. Écrivez-la manuellement.';
                statusEl.className = 'form-text small text-danger mt-1';
            }
        })
        .catch(() => {
            spinner.classList.add('d-none');
            statusEl.textContent = 'Erreur réseau. Écrivez la description manuellement.';
            statusEl.className = 'form-text small text-danger mt-1';
        });
    }, 1000);
});

function copySchema() {
    const schema = document.getElementById('schemaOutput').textContent;
    navigator.clipboard.writeText(schema).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copié !';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }, 2000);
    });
}

function copyHtml() {
    const html = document.getElementById('htmlOutput').textContent;
    navigator.clipboard.writeText(html).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copié !';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }, 2000);
    });
}

function copyMeta() {
    const text = document.getElementById('metaOutput').textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copié !';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    });
}

let nameTimeout;
const nameField = document.getElementById('nameField');
const typeField = document.getElementById('typeField');
const descField = document.getElementById('descField');
const descStatus = document.getElementById('descStatus');

function triggerDescriptionGeneration() {
    clearTimeout(nameTimeout);
    const name = nameField.value.trim();
    if (name.length < 3) return;

    nameTimeout = setTimeout(() => {
        const type = typeField.value;
        const language = document.querySelector('select[name="language"]')?.value || 'french';

        descStatus.textContent = 'Génération en cours...';
        descStatus.className = 'form-text small text-muted mt-1';

        fetch('{{ route("ai.description") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, type, language })
        })
        .then(r => r.json())
        .then(data => {
            if (data.description) {
                descField.value = data.description;
                descField.dispatchEvent(new Event('input'));
                descStatus.textContent = '✓ Description générée';
                descStatus.className = 'form-text small text-success mt-1';
            }
        })
        .catch(() => {
            descStatus.textContent = 'Erreur lors de la génération';
            descStatus.className = 'form-text small text-danger mt-1';
        });
    }, 800);
}

nameField.addEventListener('input', triggerDescriptionGeneration);
typeField.addEventListener('change', triggerDescriptionGeneration);
</script>
@endsection