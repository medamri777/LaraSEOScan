@extends('layouts.app')

@section('title', 'Suivi des Mots-clés - ' . $project->name)

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-4 px-4 py-3 d-flex align-items-center" role="alert" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-left: 5px solid #10b981 !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong class="d-block fw-bold">Succès</strong>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-4 px-4 py-3 d-flex align-items-center" role="alert" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-left: 5px solid #ef4444 !important;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong class="d-block fw-bold">Attention</strong>
                <ul class="mb-0 ps-3 mt-1 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Back link -->
    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-link text-decoration-none p-0 mb-4 d-inline-flex align-items-center text-secondary hover-primary">
        <i class="bi bi-arrow-left me-2"></i> Retour au tableau de bord du projet
    </a>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <span class="badge rounded-pill text-uppercase fw-semibold mb-2" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; letter-spacing: 0.5px; font-size: 0.75rem; padding: 6px 12px;">
                <i class="bi bi-graph-up-arrow me-1"></i> Rank Tracker
            </span>
            <h2 class="fw-bold text-slate-800 mb-1" style="font-family: 'Figtree', sans-serif; letter-spacing: -0.5px;">Positionnement des Mots-clés</h2>
            <p class="text-muted mb-0">Suivez l'évolution des positions de <strong>{{ $project->name }}</strong> sur Google.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('projects.keyword-gap', $project->id) }}" class="btn btn-outline-warning px-4 py-2.5 rounded-3 border bg-white shadow-sm d-inline-flex align-items-center hover-btn">
                <i class="bi bi-diagram-3 me-2"></i> Keyword Gap
            </a>
            <!-- Launch Check Batch form -->
            <form action="{{ route('projects.keywords.check', $project->id) }}" method="POST" class="d-inline">
                @csrf
                @if($recentBatch && in_array($recentBatch->status, ['pending', 'processing']))
                    <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 border d-inline-flex align-items-center opacity-75" disabled>
                        <i class="bi bi-arrow-repeat spin me-2"></i> Analyse en cours...
                    </button>
                @else
                    <button type="submit" class="btn btn-outline-primary px-4 py-2.5 rounded-3 border bg-white shadow-sm d-inline-flex align-items-center hover-btn">
                        <i class="bi bi-arrow-repeat me-2"></i> Actualiser Tout
                    </button>
                @endif
            </form>

            @if($limit === null || $currentCount < $limit)
                <button type="button" class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 py-2.5 rounded-3 border-0 transition-all hover-scale" data-bs-toggle="modal" data-bs-target="#addKeywordsModal" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Ajouter des Mots-clés
                </button>
            @else
                <button type="button" class="btn btn-secondary d-inline-flex align-items-center shadow-sm px-4 py-2.5 rounded-3 border-0 opacity-75" disabled>
                    <i class="bi bi-lock-fill me-2"></i> Quota Rempli
                </button>
            @endif
        </div>
    </div>

    <!-- Active Batch Alert -->
    @if($recentBatch && in_array($recentBatch->status, ['pending', 'processing']))
        <div class="alert alert-info border-0 shadow-sm mb-5 rounded-4 p-4 d-flex align-items-start" style="background: rgba(59, 130, 246, 0.08); color: #1e3a8a; border-left: 5px solid #3b82f6 !important;">
            <i class="bi bi-info-circle-fill fs-4 me-3 mt-0.5 text-primary"></i>
            <div>
                <strong class="d-block fw-bold mb-1">Mise à jour des positions en cours</strong>
                <span class="small text-slate-600">Un processus d'analyse en arrière-plan a été démarré pour vérifier les positions de {{ $recentBatch->keywords_count }} mots-clés. Les scores et tendances seront mis à jour automatiquement sous quelques minutes.</span>
            </div>
        </div>
    @endif

    <!-- Overview Stats Cards -->
    <div class="row g-4 mb-5">
        <!-- Keywords usage progress -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2 position-relative bg-white">
                <div class="card-body">
                    <span class="text-uppercase fw-semibold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Mots-clés Actifs</span>
                    <h3 class="fw-bold text-slate-800 mb-1">{{ $stats['total'] }} <span class="fs-6 text-muted fw-normal">/ {{ $limit ?? '∞' }}</span></h3>
                    @if($limit)
                        <div class="progress rounded-pill mt-3" style="height: 5px;">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ ($currentCount / $limit) * 100 }}%; background-color: #3b82f6;" aria-valuenow="{{ $currentCount }}" aria-valuemin="0" aria-valuemax="{{ $limit }}"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Top 10 -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2 position-relative bg-white">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-semibold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Top 10 (Page 1)</span>
                        <h3 class="fw-bold text-success mb-0">{{ $stats['in_top_10'] }}</h3>
                    </div>
                    <div class="rounded-3 p-3 text-success d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1);">
                        <i class="bi bi-chevron-double-up fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Top 100 -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2 position-relative bg-white">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-semibold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Classés (1 - 100)</span>
                        <h3 class="fw-bold text-primary mb-0">{{ $stats['in_top_100'] }}</h3>
                    </div>
                    <div class="rounded-3 p-3 text-primary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1);">
                        <i class="bi bi-list-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Not Ranked -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-2 position-relative bg-white">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-semibold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Non-classés (>100)</span>
                        <h3 class="fw-bold text-slate-500 mb-0">{{ $stats['not_ranked'] }}</h3>
                    </div>
                    <div class="rounded-3 p-3 text-slate-500 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(107, 114, 128, 0.1);">
                        <i class="bi bi-slash-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Keywords Table Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="border: 1px solid rgba(229, 231, 235, 0.5) !important;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-slate-800 mb-0">Mots-clés suivis</h4>
            <span class="text-muted small">Mise à jour quotidienne à 3h00 UTC</span>
        </div>

        @if($keywords->isEmpty())
            <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                <div class="mx-auto bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
                    <i class="bi bi-tags fs-3 text-muted"></i>
                </div>
                <h5 class="fw-bold text-slate-700">Aucun mot-clé suivi pour le moment</h5>
                <p class="text-muted small mx-auto mb-4" style="max-width: 450px;">Ajoutez les requêtes de recherche saisies par vos prospects pour surveiller votre visibilité et votre positionnement face aux concurrents.</p>
                @if($limit === null || $currentCount < $limit)
                    <button type="button" class="btn btn-sm btn-primary rounded-3 px-4 py-2 border-0" data-bs-toggle="modal" data-bs-target="#addKeywordsModal" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        Ajouter des mots-clés
                    </button>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr class="text-uppercase text-slate-500 fs-7 border-bottom fw-bold" style="letter-spacing: 0.5px;">
                            <th class="ps-3 py-3" style="width: 30%;">Mot-clé</th>
                            <th class="py-3 text-center" style="width: 15%;">Ciblage</th>
                            <th class="py-3 text-center" style="width: 12%;">Position</th>
                            <th class="py-3 text-center" style="width: 13%;">Vol. de rech.</th>
                            <th class="py-3 text-center" style="width: 10%;">CPC</th>
                            <th class="py-3 text-center" style="width: 10%;">Features SERP</th>
                            <th class="py-3 text-end pe-3" style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keywords as $kw)
                            <tr class="border-bottom">
                                <td class="ps-3 py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-slate-800">{{ $kw->keyword }}</span>
                                        @if($kw->latestRanking?->url)
                                            <a href="{{ $kw->latestRanking->url }}" target="_blank" class="text-muted text-truncate hover-primary fs-7" style="max-width: 300px;">
                                                {{ $kw->latestRanking->url }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-light text-slate-700 border fs-8 px-2 py-1"><i class="bi bi-geo-alt me-1"></i> {{ $locations[$kw->location_code] ?? 'MA' }}</span>
                                        <span class="badge bg-light text-slate-700 border fs-8 px-2 py-1"><i class="bi bi-translate me-1"></i> {{ strtoupper($kw->language_code) }}</span>
                                        <span class="badge bg-light text-slate-700 border fs-8 px-2 py-1"><i class="bi bi-{{ $kw->device === 'desktop' ? 'laptop' : 'phone' }} me-1"></i> {{ ucfirst($kw->device) }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @if($kw->latestRanking && $kw->latestRanking->rank !== null)
                                            <span class="badge fw-bold text-white fs-7 px-3 py-2 rounded-3 shadow-sm d-inline-block" style="width: 46px; background: {{ $kw->latestRanking->rank <= 10 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)' }};">
                                                {{ $kw->latestRanking->rank }}
                                            </span>
                                            
                                            <!-- Trend representation -->
                                            @php
                                                $trend = $kw->latestRanking->trend;
                                            @endphp
                                            @if($trend !== null)
                                                @if($trend > 0)
                                                    <span class="text-success small fw-semibold" title="Amélioration de +{{ $trend }} positions"><i class="bi bi-caret-up-fill me-0.5"></i>{{ $trend }}</span>
                                                @elseif($trend < 0)
                                                    <span class="text-danger small fw-semibold" title="Baisse de {{ $trend }} positions"><i class="bi bi-caret-down-fill me-0.5"></i>{{ abs($trend) }}</span>
                                                @else
                                                    <span class="text-muted small" title="Stable"><i class="bi bi-dash"></i></span>
                                                @endif
                                            @endif
                                        @else
                                            <span class="badge bg-light text-slate-400 border fs-7 px-3 py-2 rounded-3 d-inline-block" style="width: 46px;">
                                                >100
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="text-slate-800 fw-semibold">{{ $kw->latestRanking?->search_volume ? number_format($kw->latestRanking->search_volume, 0, ',', ' ') : '-' }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="text-slate-800 small">{{ $kw->latestRanking?->cpc ? number_format($kw->latestRanking->cpc, 2, ',', ' ') . ' $' : '-' }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="d-flex justify-content-center gap-1.5 fs-6 text-secondary">
                                        @php
                                            $features = $kw->latestRanking?->serp_features ?? [];
                                        @endphp
                                        @forelse($features as $feat)
                                            @if($feat === 'organic_snippet')
                                                <i class="bi bi-google" title="Extrait Organique"></i>
                                            @elseif($feat === 'featured_snippet')
                                                <i class="bi bi-bookmark-star-fill text-warning" title="Featured Snippet"></i>
                                            @elseif($feat === 'local_pack')
                                                <i class="bi bi-map" title="Pack Local (Maps)"></i>
                                            @elseif($feat === 'people_also_ask')
                                                <i class="bi bi-question-circle" title="Autres questions posées"></i>
                                            @else
                                                <i class="bi bi-star" title="{{ $feat }}"></i>
                                            @endif
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3 text-end pe-3">
                                    <form action="{{ route('projects.keywords.destroy', [$project->id, $kw->id]) }}" method="POST" onsubmit="return confirm('Supprimer ce mot-clé du suivi ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0 hover-scale">
                                            <i class="bi bi-trash3 fs-6"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $keywords->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Add Keywords -->
<div class="modal fade" id="addKeywordsModal" tabindex="-1" aria-labelledby="addKeywordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 text-white p-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div>
                    <h5 class="modal-title fw-bold" id="addKeywordsModalLabel"><i class="bi bi-tags me-2"></i> Ajouter des Mots-clés</h5>
                    <p class="mb-0 text-gray-300 small" style="color: #cbd5e1; margin-top: 4px;">Entrez vos mots-clés ou utilisez l'IA pour les générer</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('projects.keywords.store', $project->id) }}" method="POST" id="addKeywordsForm">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <!-- AI Suggestion Section -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4" style="border: 1px solid rgba(139, 92, 246, 0.2) !important; background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-stars text-purple fs-4" style="color: #8b5cf6;"></i>
                                <h6 class="fw-bold mb-0" style="color: #8b5cf6;">Générer des Mots-clés avec l'IA</h6>
                            </div>
                            <p class="small text-muted mb-3">Décrivez votre activité et laissez l'IA trouver les meilleurs mots-clés pour le marché marocain.</p>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Thème / Activité</label>
                                    <input type="text" class="form-control border-0 shadow-sm rounded-3" id="ai_topic" placeholder="Ex: Agence de marketing digital">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Ville cible</label>
                                    <input type="text" class="form-control border-0 shadow-sm rounded-3" id="ai_ville" placeholder="Ex: Casablanca">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Langue</label>
                                    <select class="form-select border-0 shadow-sm rounded-3" id="ai_language">
                                        <option value="fr">Français</option>
                                        <option value="ar">Arabe</option>
                                        <option value="en">Anglais</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="form-label fw-semibold small">Modificateurs (séparés par des virgules)</label>
                                <input type="text" class="form-control border-0 shadow-sm rounded-3" id="ai_modifiers" placeholder="Ex: meilleur, pas cher, professionnel, avis">
                                <small class="text-muted">Mots à ajouter aux mots-clés générés (optionnel)</small>
                            </div>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm px-4 py-2 rounded-3 fw-semibold border-0" id="btnGenerateKeywords" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
                                    <i class="bi bi-stars me-1"></i> <span id="btnGenerateText">Générer avec l'IA</span>
                                    <span id="btnGenerateSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- AI Results (hidden by default) -->
                    <div id="aiResults" class="d-none mb-4">
                        <!-- Strategy Summary -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3" style="border-left: 4px solid #8b5cf6 !important;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1" style="color: #8b5cf6;"><i class="bi bi-lightbulb me-2"></i>Stratégie</h6>
                                <p class="mb-0 small text-muted" id="aiSummary">-</p>
                            </div>
                        </div>

                        <!-- Quick Wins -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3" style="border-left: 4px solid #10b981 !important;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-2 text-success"><i class="bi bi-trophy me-2"></i>Victoires Rapides</h6>
                                <div id="aiQuickWins"></div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0"><i class="bi bi-check-circle me-2 text-success"></i>Mots-clés suggérés</h6>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-3" id="btnAddSelected">
                                    <i class="bi bi-plus-lg me-1"></i> Ajouter sélectionnés
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th style="width: 40px;"></th>
                                                <th>Mot-clé</th>
                                                <th>Langue</th>
                                                <th>Type</th>
                                                <th>Intent</th>
                                                <th>Difficulté</th>
                                                <th>Pourquoi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="aiKeywordsTable"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Keywords to Avoid -->
                        <div class="card border-0 shadow-sm rounded-3 mt-3" id="aiAvoidSection">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-x-circle me-2"></i>Mots-clés à éviter</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="aiAvoidList"></div>
                            </div>
                        </div>

                        <!-- Content Ideas -->
                        <div class="card border-0 shadow-sm rounded-3 mt-3" id="aiContentSection">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-lightbulb me-2"></i>Idées de contenu</h6>
                            </div>
                            <div class="card-body p-3">
                                <div id="aiContentList"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Textarea keywords -->
                    <div class="mb-3">
                        <label for="keywords_input" class="form-label fw-bold text-slate-700 small">Mots-clés (Un par ligne, ou séparés par des virgules)</label>
                        <textarea class="form-control border-0 shadow-sm p-3 rounded-3" id="keywords_input" name="keywords_input" rows="6" placeholder="Ex: agence seo casablanca&#10;darija seo&#10;meilleur e-commerce maroc" style="font-size: 0.9rem; resize: none;"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Location Selection -->
                        <div class="col-6">
                            <label for="location_code" class="form-label fw-bold text-slate-700 small">Zone Géographique</label>
                            <select class="form-select border-0 shadow-sm p-3 rounded-3" id="location_code" name="location_code" style="font-size: 0.9rem;">
                                @foreach($locations as $code => $label)
                                    <option value="{{ $code }}" {{ $code == 2504 ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Language Selection -->
                        <div class="col-6">
                            <label for="language_code" class="form-label fw-bold text-slate-700 small">Langue</label>
                            <select class="form-select border-0 shadow-sm p-3 rounded-3" id="language_code" name="language_code" style="font-size: 0.9rem;">
                                @foreach($languages as $code => $label)
                                    <option value="{{ $code }}" {{ $code === 'fr' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Device selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700 small d-block">Appareil de ciblage</label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="device" id="device_desktop" value="desktop" checked>
                            <label class="form-check-label small" for="device_desktop"><i class="bi bi-laptop me-1"></i> Desktop (Ordinateur)</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="device" id="device_mobile" value="mobile">
                            <label class="form-check-label small" for="device_mobile"><i class="bi bi-phone me-1"></i> Mobile (Téléphone)</label>
                        </div>
                    </div>

                    <div class="bg-primary bg-opacity-10 text-primary-emphasis p-3 rounded-3 small border border-primary border-opacity-25 d-flex align-items-start mt-4">
                        <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0 mt-0.5"></i>
                        <div>
                            Chaque mot-clé sera scanné et positionné automatiquement sur Google lors du lancement ou de la mise à jour quotidienne. Vous en utilisez actuellement <strong>{{ $currentCount }} / {{ $limit ?? '∞' }}</strong>.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-0 p-4 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light px-4 py-2.5 rounded-3 fw-semibold border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-3 fw-semibold border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">Ajouter les Mots-clés</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-scale {
        transition: all 0.2s ease-in-out;
    }
    .hover-scale:hover {
        transform: translateY(-2px);
    }
    .hover-primary:hover {
        color: #3b82f6 !important;
    }
    .hover-btn {
        transition: all 0.2s;
    }
    .hover-btn:hover {
        background-color: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
    .spin {
        animation: spin-anim 1.5s linear infinite;
    }
    @keyframes spin-anim {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .fs-8 {
        font-size: 0.72rem;
    }
    .bg-purple { background-color: #8b5cf6 !important; }
    .bg-teal { background-color: #14b8a6 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGenerate = document.getElementById('btnGenerateKeywords');
    const btnGenerateText = document.getElementById('btnGenerateText');
    const btnGenerateSpinner = document.getElementById('btnGenerateSpinner');
    const aiResults = document.getElementById('aiResults');
    const aiKeywordsTable = document.getElementById('aiKeywordsTable');
    const aiAvoidList = document.getElementById('aiAvoidList');
    const aiContentList = document.getElementById('aiContentList');
    const keywordsInput = document.getElementById('keywords_input');
    const btnAddSelected = document.getElementById('btnAddSelected');

    if (!btnGenerate) return;

    btnGenerate.addEventListener('click', async function() {
        const topic = document.getElementById('ai_topic').value.trim();
        const ville = document.getElementById('ai_ville').value.trim();
        const language = document.getElementById('ai_language').value;
        const modifiers = document.getElementById('ai_modifiers').value.trim();

        if (!topic || !ville) {
            alert('Veuillez remplir le thème et la ville cible.');
            return;
        }

        btnGenerate.disabled = true;
        btnGenerateText.textContent = 'Génération en cours...';
        btnGenerateSpinner.classList.remove('d-none');

        try {
            const response = await fetch("{{ route('projects.keywords.suggest', $project->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ topic, ville, language, modifiers: modifiers || '' }),
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.error || `Erreur serveur (${response.status}). Vérifiez la console (F12).`);
                return;
            }

            // Display summary
            if (data.summary) {
                const summaryEl = document.getElementById('aiSummary');
                if (summaryEl) summaryEl.textContent = data.summary;
            }

            // Display quick wins
            if (data.quick_wins && data.quick_wins.length > 0) {
                const quickWinsEl = document.getElementById('aiQuickWins');
                if (quickWinsEl) {
                    quickWinsEl.innerHTML = data.quick_wins.map(kw => `<span class="badge bg-success me-1">${kw}</span>`).join('');
                }
            }

            // Display keywords
            aiKeywordsTable.innerHTML = '';
            if (data.keywords && data.keywords.length > 0) {
                data.keywords.forEach((kw, index) => {
                    const typeBadge = kw.type === 'local' ? 'bg-info' : (kw.type === 'commercial' ? 'bg-warning text-dark' : (kw.type === 'question' ? 'bg-purple text-white' : 'bg-secondary'));
                    const intentBadge = kw.intent === 'transactional' ? 'bg-success' : (kw.intent === 'commercial' ? 'bg-primary' : 'bg-light text-dark');
                    const diffBadge = kw.difficulty === 'easy' ? 'bg-success' : (kw.difficulty === 'hard' ? 'bg-danger' : 'bg-warning text-dark');
                    const langBadge = kw.language === 'arabic' ? 'bg-dark' : (kw.language === 'darija' ? 'bg-teal text-white' : (kw.language === 'mixed' ? 'bg-info' : 'bg-secondary'));
                    
                    aiKeywordsTable.innerHTML += `
                        <tr>
                            <td><input type="checkbox" class="form-check-input ai-kw-check" value="${kw.keyword}" checked></td>
                            <td class="fw-semibold">${kw.keyword}</td>
                            <td><span class="badge ${langBadge}">${kw.language}</span></td>
                            <td><span class="badge ${typeBadge}">${kw.type}</span></td>
                            <td><span class="badge ${intentBadge}">${kw.intent}</span></td>
                            <td><span class="badge ${diffBadge}">${kw.difficulty}</span></td>
                            <td class="small text-muted">${kw.why_good || '-'}</td>
                        </tr>
                    `;
                });
            }

            // Display avoid keywords
            if (data.avoid && data.avoid.length > 0) {
                aiAvoidList.innerHTML = data.avoid.map(kw => `
                    <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded" style="background: rgba(239, 68, 68, 0.05);">
                        <i class="bi bi-x-circle text-danger mt-1"></i>
                        <div>
                            <strong class="text-danger">${kw.keyword}</strong>
                            <div class="small text-muted">${kw.reason || ''}</div>
                        </div>
                    </div>
                `).join('');
            }

            // Display content ideas
            if (data.content_ideas && data.content_ideas.length > 0) {
                aiContentList.innerHTML = data.content_ideas.map((idea, i) => `
                    <div class="p-3 rounded mb-2" style="background: rgba(59, 130, 246, 0.05);">
                        <h6 class="fw-bold mb-1 text-primary">${i + 1}. ${idea.title}</h6>
                        <p class="small text-muted mb-1">${idea.why || ''}</p>
                        <span class="badge bg-light text-dark border">${idea.target_keyword || ''}</span>
                    </div>
                `).join('');
            }

            aiResults.classList.remove('d-none');
        } catch (error) {
            console.error('Fetch error:', error);
            alert(`Erreur: ${error.message || 'Vérifiez la console (F12) pour les détails.'}`);
        } finally {
            btnGenerate.disabled = false;
            btnGenerateText.textContent = 'Générer avec l\'IA';
            btnGenerateSpinner.classList.add('d-none');
        }
    });

    // Add selected keywords to textarea
    if (btnAddSelected) {
        btnAddSelected.addEventListener('click', function() {
            const checked = document.querySelectorAll('.ai-kw-check:checked');
            const keywords = Array.from(checked).map(cb => cb.value);
            
            if (keywords.length > 0) {
                const currentValue = keywordsInput.value.trim();
                keywordsInput.value = currentValue ? currentValue + '\n' + keywords.join('\n') : keywords.join('\n');
                
                // Visual feedback
                btnAddSelected.innerHTML = '<i class="bi bi-check-lg me-1"></i> Ajoutés!';
                btnAddSelected.classList.remove('btn-outline-success');
                btnAddSelected.classList.add('btn-success');
                setTimeout(() => {
                    btnAddSelected.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Ajouter sélectionnés';
                    btnAddSelected.classList.remove('btn-success');
                    btnAddSelected.classList.add('btn-outline-success');
                }, 2000);
            }
        });
    }
});
</script>
@endsection
