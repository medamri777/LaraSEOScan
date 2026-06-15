@extends('layouts.app')

@section('title', 'Surveillance des Concurrents - ' . $project->name)

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
            <span class="badge rounded-pill text-uppercase fw-semibold mb-2" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; letter-spacing: 0.5px; font-size: 0.75rem; padding: 6px 12px;">
                <i class="bi bi-people-fill me-1"></i> Spy Concurrents
            </span>
            <h2 class="fw-bold text-slate-800 mb-1" style="font-family: 'Figtree', sans-serif; letter-spacing: -0.5px;">Espionnage de la Concurrence</h2>
            <p class="text-muted mb-0">Surveillez et comparez vos positions face à vos principaux concurrents locaux.</p>
        </div>
        <div>
            @if($competitors->count() < $maxCompetitors)
                <button type="button" class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 py-2.5 rounded-3 border-0 transition-all hover-scale" data-bs-toggle="modal" data-bs-target="#addCompetitorModal" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Ajouter un Concurrent
                </button>
            @else
                <button type="button" class="btn btn-secondary d-inline-flex align-items-center shadow-sm px-4 py-2.5 rounded-3 border-0 opacity-75" disabled>
                    <i class="bi bi-lock-fill me-2"></i> Limite de Concurrents Atteinte
                </button>
            @endif
        </div>
    </div>

    <!-- Competitors List Section -->
    <div class="mb-5">
        <h4 class="fw-bold text-slate-800 mb-3"><i class="bi bi-list-stars text-secondary me-2"></i> Concurrents surveillés ({{ $competitors->count() }} / {{ $maxCompetitors }})</h4>
        @if($competitors->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="my-3">
                    <div class="mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background-color: rgba(243, 244, 246, 1) !important;">
                        <i class="bi bi-person-plus fs-2" style="color: #6b7280;"></i>
                    </div>
                    <h5 class="fw-bold text-slate-800 mb-2">Aucun site concurrent configuré</h5>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">Associez les sites de vos concurrents directs pour suivre en temps réel la comparaison de positionnement sur vos mots-clés stratégiques.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#addCompetitorModal">
                        Ajouter un premier concurrent
                    </button>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($competitors as $competitor)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border d-flex flex-row justify-content-between align-items-center" style="border: 1px solid rgba(229, 231, 235, 0.5) !important;">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning me-3 border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bi bi-shield-slash fs-5"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold text-slate-800 mb-0 text-truncate">{{ $competitor->name }}</h6>
                                    <a href="{{ $competitor->url }}" target="_blank" class="text-decoration-none text-muted fs-8 text-truncate d-block hover-primary">
                                        {{ $competitor->url }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <form action="{{ route('projects.competitors.destroy', [$project->id, $competitor->id]) }}" method="POST" onsubmit="return confirm('Arrêter de surveiller ce concurrent ?');" class="flex-shrink-0 ms-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0 border-0 hover-scale" title="Supprimer le concurrent">
                                    <i class="bi bi-trash3 fs-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Comparison Matrix Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="border: 1px solid rgba(229, 231, 235, 0.5) !important;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-slate-800 mb-0"><i class="bi bi-grid-3x3-gap text-secondary me-2"></i> Matrice Comparative des Rangs</h4>
            <span class="text-muted small">Comparez vos positions SEO</span>
        </div>

        @if($matrix->isEmpty())
            <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                <div class="mx-auto bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
                    <i class="bi bi-tags fs-3 text-muted"></i>
                </div>
                <h5 class="fw-bold text-slate-700">Aucun mot-clé actif dans le projet</h5>
                <p class="text-muted small mx-auto mb-4" style="max-width: 450px;">Ajoutez des mots-clés dans le module Rank Tracker pour commencer à construire votre matrice comparative concurrentielle.</p>
                <a href="{{ route('projects.keywords.index', $project->id) }}" class="btn btn-sm btn-primary rounded-3 px-4 py-2 border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Gérer les mots-clés
                </a>
            </div>
        @elseif($competitors->isEmpty())
            <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                <div class="mx-auto bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
                    <i class="bi bi-people fs-3 text-muted"></i>
                </div>
                <h5 class="fw-bold text-slate-700">Aucun concurrent actif</h5>
                <p class="text-muted small mx-auto mb-4" style="max-width: 450px;">Pour afficher la comparaison, ajoutez au moins un concurrent en haut de cette page.</p>
                <button type="button" class="btn btn-sm btn-primary rounded-3 px-4 py-2 border-0" data-bs-toggle="modal" data-bs-target="#addCompetitorModal" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Ajouter un concurrent
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr class="text-uppercase text-slate-500 fs-7 border-bottom fw-bold" style="letter-spacing: 0.5px;">
                            <th class="ps-3 py-3" style="width: 25%;">Mot-clé</th>
                            <th class="py-3 text-center" style="width: 15%;">Vol. Recherche</th>
                            <th class="py-3 text-center" style="width: 15%; background: rgba(16, 185, 129, 0.05);">Ma position</th>
                            @foreach($competitors as $comp)
                                <th class="py-3 text-center text-truncate" style="width: 15%; max-width: 150px;" title="{{ $comp->name }}">{{ $comp->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $row)
                            <tr class="border-bottom">
                                <td class="ps-3 py-3 fw-semibold text-slate-800">{{ $row['keyword'] }}</td>
                                <td class="py-3 text-center text-muted small">
                                    {{ $row['search_volume'] ? number_format($row['search_volume'], 0, ',', ' ') : '-' }}
                                </td>
                                
                                <!-- User rank -->
                                <td class="py-3 text-center" style="background: rgba(16, 185, 129, 0.03);">
                                    @if($row['own_rank'] !== null)
                                        <span class="badge fw-bold text-white fs-7 px-3 py-2 rounded-3 shadow-sm d-inline-block" style="width: 44px; background: {{ $row['own_rank'] <= 10 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)' }};">
                                            {{ $row['own_rank'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-slate-400 border fs-7 px-3 py-2 rounded-3 d-inline-block" style="width: 44px;">
                                            >100
                                        </span>
                                    @endif
                                </td>

                                <!-- Competitor ranks -->
                                @foreach($competitors as $comp)
                                    @php
                                        $compData = $row['competitors'][$comp->id] ?? null;
                                        $compRank = $compData['rank'] ?? null;
                                        $compUrl = $compData['url'] ?? null;
                                    @endphp
                                    <td class="py-3 text-center">
                                        @if($compRank !== null)
                                            <span class="badge fw-semibold text-white fs-7 px-3 py-2 rounded-3 shadow-sm d-inline-block" style="width: 44px; background: {{ $compRank <= 10 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)' }};" @if($compUrl) title="{{ $compUrl }}" @endif>
                                                {{ $compRank }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-slate-400 border fs-7 px-3 py-2 rounded-3 d-inline-block" style="width: 44px;">
                                                >100
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal Add Competitor -->
<div class="modal fade" id="addCompetitorModal" tabindex="-1" aria-labelledby="addCompetitorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 text-white p-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div>
                    <h5 class="modal-title fw-bold" id="addCompetitorModalLabel"><i class="bi bi-person-plus me-2"></i> Nouveau Concurrent</h5>
                    <p class="mb-0 text-gray-300 small" style="color: #cbd5e1; margin-top: 4px;">Ajoutez un site à espionner sur votre liste de mots-clés</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('projects.competitors.store', $project->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold text-slate-700 small">Nom du Concurrent</label>
                        <input type="text" class="form-control border-0 shadow-sm p-3 rounded-3" id="name" name="name" placeholder="Ex: Principal Concurrent SARL" required style="font-size: 0.9rem;">
                    </div>
                    
                    <!-- URL -->
                    <div class="mb-3">
                        <label for="url" class="form-label fw-bold text-slate-700 small">URL de la Page d'accueil du Concurrent</label>
                        <input type="url" class="form-control border-0 shadow-sm p-3 rounded-3" id="url" name="url" placeholder="https://www.concurrent.ma" required style="font-size: 0.9rem;">
                    </div>

                    <div class="bg-warning bg-opacity-10 text-warning-emphasis p-3 rounded-3 small border border-warning border-opacity-25 d-flex align-items-start mt-4">
                        <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0 mt-0.5"></i>
                        <div>
                            Le positionnement de ce concurrent sera automatiquement déterminé lors de chaque actualisation ou vérification planifiée des mots-clés du projet.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-0 p-4 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light px-4 py-2.5 rounded-3 fw-semibold border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-3 fw-semibold border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">Ajouter le Concurrent</button>
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
    .fs-8 {
        font-size: 0.72rem;
    }
</style>
@endsection
