@extends('layouts.app')

@section('title', 'Dashboard - Seo4ma')

@section('content')
<div style="max-width: 1280px; margin: 0 auto;">

    {{-- Dashboard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 pb-4" style="border-bottom: 1px solid #e5e7eb;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #111827; margin-bottom: 4px;">Dashboard</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">Welcome back! Here's an overview of your SEO projects and audits.</p>
        </div>
        <button type="button"
                @click="$dispatch('open-project-modal')"
                class="btn-filament btn-filament-primary">
            <i class="bi bi-plus-lg"></i> Create Project
        </button>
    </div>

    {{-- Quick Tool: Domain Overview --}}
    <div class="card-filament mb-4 overflow-hidden">
        <div class="p-6 md:p-8" style="background: linear-gradient(to right, #ecfdf5, #fff);">
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <div class="lg:w-5/12">
                    <h3 class="flex items-center gap-2 mb-2" style="font-size: 1.125rem; font-weight: 700; color: #111827;">
                        <i class="bi bi-search" style="color: #10b981;"></i> Domain Overview
                    </h3>
                    <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">Get instant SEO metrics, backlink data, and keyword positions for your project domain.</p>
                </div>
                <div class="lg:w-7/12">
                    <form action="{{ route('seo.analyzer.analyze') }}" method="POST">
                        @csrf
                        <div class="flex rounded-xl overflow-hidden" style="border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <span class="flex items-center px-3" style="background: #f9fafb; border-right: 1px solid #e5e7eb; color: #9ca3af;">
                                <i class="bi bi-globe2"></i>
                            </span>
                            <input type="url" name="url"
                                   value="{{ $currentProject?->url ?? '' }}"
                                   readonly
                                   class="flex-1 px-3 py-3 text-sm border-0 focus:ring-0 focus:outline-none"
                                   style="background: #f9fafb; color: #6b7280;"
                                   placeholder="{{ $currentProject ? 'No project selected' : 'Create a project first' }}">
                            <button class="btn-filament btn-filament-primary px-6 py-3" style="border-radius: 0;" type="submit" {{ !$currentProject ? 'disabled' : '' }}>Analyze</button>
                        </div>
                        @if(!$currentProject)
                        <p style="font-size: 0.8rem; color: #ef4444; margin-top: 0.5rem;">
                            <i class="bi bi-exclamation-circle"></i> Please <a href="{{ route('projects.index') }}" style="color: #10b981;">create a project</a> first to use this tool.
                        </p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Active Projects --}}
        <div class="card-filament p-5">
            <div class="flex items-start justify-between mb-3">
                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Active Projects</div>
                <div class="flex items-center justify-center" style="width: 40px; height: 40px; border-radius: 8px; background: #d1fae5; color: #059669;">
                    <i class="bi bi-folder" style="font-size: 1.25rem;"></i>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #111827;">{{ isset($projects) ? $projects->count() : 0 }}</div>
        </div>

        {{-- Total Audits --}}
        <div class="card-filament p-5">
            <div class="flex items-start justify-between mb-3">
                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Total Audits</div>
                <div class="flex items-center justify-center" style="width: 40px; height: 40px; border-radius: 8px; background: #e0f2fe; color: #0284c7;">
                    <i class="bi bi-bar-chart" style="font-size: 1.25rem;"></i>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #111827;">{{ $scanStats['total'] ?? 0 }}</div>
        </div>

        {{-- Completed --}}
        <div class="card-filament p-5">
            <div class="flex items-start justify-between mb-3">
                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Completed</div>
                <div class="flex items-center justify-center" style="width: 40px; height: 40px; border-radius: 8px; background: #d1fae5; color: #059669;">
                    <i class="bi bi-check-circle" style="font-size: 1.25rem;"></i>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #111827;">{{ $scanStats['completed'] ?? 0 }}</div>
        </div>

        {{-- In Progress --}}
        <div class="card-filament p-5">
            <div class="flex items-start justify-between mb-3">
                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">In Progress</div>
                <div class="flex items-center justify-center" style="width: 40px; height: 40px; border-radius: 8px; background: #fef3c7; color: #d97706;">
                    <i class="bi bi-hourglass-split" style="font-size: 1.25rem;"></i>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #111827;">{{ $scanStats['pending'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Two-column: Projects + Recent Crawls --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- My Projects --}}
        <div class="card-filament overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom: 1px solid #e5e7eb; background: #f9fafb80;">
                <h5 class="flex items-center gap-2" style="font-weight: 700; color: #111827; font-size: 0.875rem; margin: 0;">
                    <i class="bi bi-folder-fill" style="color: #10b981;"></i> My Projects
                </h5>
                <a href="{{ route('projects.index') }}" style="font-size: 0.75rem; font-weight: 600; color: #059669; text-decoration: none;">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div>
                @if(!isset($projects) || $projects->isEmpty())
                    <div class="p-10 text-center">
                        <i class="bi bi-folder-plus mb-3 d-block" style="font-size: 2.5rem; color: #d1d5db;"></i>
                        <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">No projects yet. Create one to track SEO over time.</p>
                        <button type="button"
                                @click="$dispatch('open-project-modal')"
                                class="btn-filament btn-filament-secondary text-sm">
                            Set up Project
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Project Domain</th>
                                    <th style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Scans</th>
                                    <th style="text-align: right; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                <tr style="border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
                                    onclick="window.location='{{ route('projects.show', $project->id) }}'">
                                    <td style="padding: 14px 20px;">
                                        <div class="flex items-center gap-2" style="font-weight: 600; color: #111827;">
                                            <i class="bi bi-globe2" style="color: #9ca3af;"></i> {{ $project->name }}
                                        </div>
                                        <div style="color: #9ca3af; font-size: 0.75rem; margin-left: 26px;">{{ $project->url }}</div>
                                    </td>
                                    <td style="padding: 14px 20px;">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md" style="background: #f3f4f6; color: #4b5563; font-size: 0.75rem; font-weight: 500;">
                                            {{ $project->scans_count }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right;">
                                        <a href="{{ route('projects.show', $project->id) }}"
                                           class="inline-flex items-center gap-1"
                                           style="padding: 6px 12px; font-size: 0.75rem; font-weight: 500; border-radius: 8px; border: 1px solid #e5e7eb; color: #374151; text-decoration: none; transition: background 0.15s;">
                                            View <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Crawls --}}
        <div class="card-filament overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom: 1px solid #e5e7eb; background: #f9fafb80;">
                <h5 class="flex items-center gap-2" style="font-weight: 700; color: #111827; font-size: 0.875rem; margin: 0;">
                    <i class="bi bi-radar" style="color: #ef4444;"></i> Recent Crawls
                </h5>
            </div>
            <div>
                @if(!isset($recentScans) || $recentScans->isEmpty())
                    <div class="p-10 text-center">
                        <i class="bi bi-activity mb-3 d-block" style="font-size: 2.5rem; color: #d1d5db;"></i>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">No audits run yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Website</th>
                                    <th style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Status</th>
                                    <th style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Date</th>
                                    <th style="text-align: right; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 12px 20px;">Report</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentScans as $scan)
                                <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.15s; {{ $scan->status === 'COMPLETED' ? 'cursor: pointer;' : '' }}"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
                                    @if($scan->status === 'COMPLETED') onclick="window.location='{{ route('tools.crawl-audit.results', $scan->uuid) }}'" @endif>
                                    <td style="padding: 14px 20px;">
                                        <div style="font-weight: 600; color: #111827; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $scan->url }}">{{ $scan->url }}</div>
                                    </td>
                                    <td style="padding: 14px 20px;">
                                        @if($scan->status === 'COMPLETED')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full" style="font-size: 0.75rem; font-weight: 500; background: #d1fae5; color: #047857; border: 1px solid #a7f3d0;">Completed</span>
                                        @elseif($scan->status === 'FAILED')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full" style="font-size: 0.75rem; font-weight: 500; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">Failed</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full" style="font-size: 0.75rem; font-weight: 500; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                                Running <i class="bi bi-arrow-repeat" style="animation: spin 2s linear infinite; display: inline-block;"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 20px; color: #6b7280; font-size: 0.8rem;">
                                        {{ $scan->created_at->shortRelativeDiffForHumans() }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right;">
                                        @if($scan->status === 'COMPLETED')
                                            <a href="{{ route('tools.crawl-audit.results', $scan->uuid) }}"
                                               class="inline-flex items-center"
                                               style="padding: 6px 12px; font-size: 0.75rem; font-weight: 500; border-radius: 8px; border: 1px solid #a7f3d0; color: #059669; background: #ecfdf5; text-decoration: none; transition: background 0.15s;">
                                                View
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg" style="font-size: 0.75rem; font-weight: 500; background: #f3f4f6; color: #9ca3af; opacity: 0.5;">Wait</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Create Project Modal --}}
<div x-data="{ open: false }"
     @open-project-modal.window="open = true"
     x-show="open"
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0" style="background: rgba(17,24,39,0.5); backdrop-filter: blur(4px);"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"></div>
    {{-- Modal content --}}
    <div class="relative w-full overflow-hidden"
         style="background: #fff; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-width: 28rem;"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        {{-- Header --}}
        <div class="px-6 py-5" style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <h5 class="flex items-center gap-2" style="font-weight: 700; color: #111827; margin: 0;">
                        <i class="bi bi-plus-circle" style="color: #10b981;"></i> Create Project
                    </h5>
                    <p style="color: #6b7280; font-size: 0.75rem; margin: 4px 0 0;">Enter your website URL to get started.</p>
                </div>
                <button type="button" @click="open = false"
                        class="flex items-center justify-center"
                        style="width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: #9ca3af; cursor: pointer; transition: all 0.15s;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#4b5563'"
                        onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        {{-- Form --}}
        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            <div class="px-6 py-5">
                <label for="url" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 8px;">Website URL</label>
                <input type="url" id="url" name="url"
                       class="w-full px-4 py-3 text-sm"
                       style="border-radius: 12px; border: 1px solid #d1d5db;"
                       placeholder="https://www.example.com" required>
            </div>
            <div class="px-6 py-4 flex justify-end gap-3" style="background: #f9fafb; border-top: 1px solid #f3f4f6;">
                <button type="button" @click="open = false"
                        class="btn-filament btn-filament-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" class="btn-filament btn-filament-primary text-sm">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endsection
