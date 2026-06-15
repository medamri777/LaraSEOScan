@extends('layouts.app')

@section('title', 'Keyword Gap Analysis - ' . $project->name)

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>Keyword Gap Analysis</h2>
            <p class="text-muted mb-0">Discover keywords your competitors rank for that you're missing.</p>
        </div>
        <a href="{{ route('projects.keywords.index', $project->id) }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Keywords
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('projects.keyword-gap', $project->id) }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Location</label>
                    <select name="location" class="form-select border-0 shadow-sm rounded-3">
                        @foreach($locations as $code => $label)
                            <option value="{{ $code }}" {{ $location == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Language</label>
                    <select name="language" class="form-select border-0 shadow-sm rounded-3">
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" {{ $language == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-arrow-repeat me-1"></i> Refresh Analysis
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(count($competitorDomains) === 0)
    <div class="alert alert-warning d-flex align-items-center rounded-4">
        <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
        <div>
            <strong>No competitors added yet.</strong>
            <p class="mb-0 small">Add competitors in the <a href="{{ route('projects.competitors.index', $project->id) }}" class="alert-link">Competitors tab</a> to perform gap analysis.</p>
        </div>
    </div>
    @else
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold mb-2">Your Keywords</h6>
                    <h2 class="fw-bold mb-0 text-success">{{ count($yourKeywords) }}</h2>
                    <small class="text-muted">{{ $yourDomain }}</small>
                </div>
            </div>
        </div>
        @foreach($competitorDomains as $compDomain)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-danger">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold mb-2">Competitor</h6>
                    <h2 class="fw-bold mb-0 text-danger">{{ count($competitorKeywords[$compDomain] ?? []) }}</h2>
                    <small class="text-muted">{{ $compDomain }}</small>
                </div>
            </div>
        </div>
        @endforeach
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-4 text-center">
                    <h6 class="text-muted small fw-semibold mb-2">Gap Keywords</h6>
                    <h2 class="fw-bold mb-0 text-warning">{{ count($gapAnalysis) }}</h2>
                    <small class="text-muted">Missing opportunities</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Keywords You're Missing</h6>
            <span class="badge bg-warning text-dark">{{ count($gapAnalysis) }} gaps found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Keyword</th>
                            <th>Competitors Ranking</th>
                            <th>Count</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gapAnalysis as $gap)
                        <tr>
                            <td class="fw-semibold">{{ $gap['keyword'] }}</td>
                            <td>
                                @foreach($gap['competitors_ranking'] as $comp)
                                <span class="badge bg-danger me-1">{{ $comp }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge bg-warning text-dark">{{ $gap['competitor_count'] }}</span></td>
                            <td>
                                <form action="{{ route('projects.keywords.store', $project->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="keywords_input" value="{{ $gap['keyword'] }}">
                                    <input type="hidden" name="location_code" value="{{ $location }}">
                                    <input type="hidden" name="language_code" value="{{ $language }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-3">
                                        <i class="bi bi-plus-lg me-1"></i> Track
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                Great! You're not missing any competitor keywords.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
