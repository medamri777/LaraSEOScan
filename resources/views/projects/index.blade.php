@extends('layouts.app')

@section('title', 'My Projects - Seo4ma')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem;">

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #10b981; border-radius: 12px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span><br>
                @endforeach
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Plan Limit Banner -->
    @php
        $atLimit = $limit !== null && $currentCount >= $limit;
        $nearLimit = $limit !== null && !$atLimit && $currentCount >= ($limit * 0.8);
    @endphp

    @if($atLimit)
        <div class="d-flex align-items-center gap-3 mb-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 12px; padding: 1rem 1.5rem;">
            <div style="width:44px;height:44px;border-radius:50%;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-lock-fill" style="font-size:1.25rem;color:#d97706;"></i>
            </div>
            <div style="flex:1;">
                <strong style="color:#92400e;font-size:0.9rem;display:block;">Project limit reached</strong>
                <span style="color:#a16207;font-size:0.8rem;">You're using {{ $currentCount }}/{{ $limit }} projects on the {{ ucfirst($plan) }} plan. Upgrade to add more.</span>
            </div>
            <a href="{{ route('pricing') }}" class="btn-filament" style="background:#d97706;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:10px;font-size:0.8rem;font-weight:600;text-decoration:none;white-space:nowrap;">
                Upgrade Plan <i class="bi bi-arrow-up-circle ms-1"></i>
            </a>
        </div>
    @elseif($nearLimit)
        <div class="d-flex align-items-center gap-3 mb-4" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 1rem 1.5rem;">
            <div style="width:44px;height:44px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-info-circle-fill" style="font-size:1.25rem;color:#0284c7;"></i>
            </div>
            <div style="flex:1;">
                <strong style="color:#0c4a6e;font-size:0.9rem;display:block;">Almost at your project limit</strong>
                <span style="color:#0369a1;font-size:0.8rem;">You're using {{ $currentCount }}/{{ $limit }} projects on the {{ ucfirst($plan) }} plan.</span>
            </div>
            <a href="{{ route('pricing') }}" style="color:#0284c7;font-size:0.8rem;font-weight:600;text-decoration:none;white-space:nowrap;">
                View Plans <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight:700;margin:0;color:#111827;font-size:1.5rem;">My Projects</h2>
        <div class="d-flex align-items-center gap-3">
            @if($limit !== null)
                <span style="font-size:0.8rem;color:#6b7280;">{{ $currentCount }}/{{ $limit }} projects</span>
            @else
                <span style="font-size:0.8rem;color:#6b7280;">{{ $currentCount }} projects (unlimited)</span>
            @endif
            @if($atLimit)
                <a href="{{ route('pricing') }}" class="btn-filament" style="background:#111827;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:10px;font-size:0.85rem;font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-up-circle me-2"></i>Upgrade
                </a>
            @else
                <button type="button" class="btn-filament btn-filament-primary" data-bs-toggle="modal" data-bs-target="#newProjectModal" style="font-size:.85rem;">
                    <i class="bi bi-plus-lg me-2"></i> Create Project
                </button>
            @endif
        </div>
    </div>

    <!-- Projects List -->
    @if($projects->isEmpty())
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:3rem 1.5rem;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="width:80px;height:80px;border-radius:50%;background:#ecfdf5;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <i class="bi bi-folder-plus" style="font-size:2rem;color:#10b981;"></i>
            </div>
            <h4 style="font-weight:700;color:#111827;margin-bottom:0.5rem;">No projects yet</h4>
            <p style="color:#6b7280;max-width:400px;margin:0 auto 1.5rem;font-size:0.9rem;">Click "Create Project" to add your first website and start tracking SEO.</p>
            <button type="button" class="btn-filament btn-filament-primary" data-bs-toggle="modal" data-bs-target="#newProjectModal" style="padding:0.75rem 2rem;">
                <i class="bi bi-plus-lg me-2"></i> Create Your First Project
            </button>
        </div>
    @else
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                        <th class="ps-4 py-3" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">Project</th>
                        <th class="py-3" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;">URL</th>
                        <th class="py-3 text-center" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;width:100px;">Audits</th>
                        <th class="py-3 pe-4 text-end" style="font-weight:600;text-transform:uppercase;color:#6b7280;font-size:0.7rem;letter-spacing:0.5px;border:0;width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr style="border-bottom:1px solid #f3f4f6;cursor:pointer;" onclick="window.location='{{ route('dashboard') }}'">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div style="width:40px;height:40px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                                        <i class="bi bi-globe2" style="color:#10b981;font-size:1rem;"></i>
                                    </div>
                                    <span style="font-weight:600;color:#111827;">{{ $project->name }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="text-truncate d-inline-block" style="max-width:280px;color:#6b7280;font-size:0.85rem;">{{ $project->url }}</span>
                            </td>
                            <td class="py-3 text-center">
                                <span style="display:inline-block;padding:0.2rem 0.6rem;background:#f3f4f6;color:#6b7280;border-radius:9999px;font-size:0.8rem;">{{ $project->scans_count }}</span>
                            </td>
                            <td class="py-3 pe-4 text-end">
                                <a href="{{ route('dashboard') }}" style="display:inline-block;padding:0.35rem 1rem;border:1px solid #a7f3d0;color:#10b981;background:transparent;border-radius:8px;font-size:0.8rem;font-weight:600;text-decoration:none;"
                                   onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='transparent'">
                                    Start <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    @endif
</div>

<!-- Create Project Modal -->
<div class="modal fade" id="newProjectModal" tabindex="-1" aria-labelledby="newProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;background:#fff;border:1px solid #e5e7eb;box-shadow:0 24px 64px rgba(0,0,0,0.12);">
            <div class="modal-header border-0 p-4" style="background:#f9fafb;border-bottom:1px solid #f3f4f6;">
                <div>
                    <h5 class="modal-title" id="newProjectModalLabel" style="font-weight:700;color:#111827;">
                        <i class="bi bi-plus-circle me-2" style="color:#10b981;"></i> Create Project
                    </h5>
                    <p style="color:#6b7280;font-size:0.85rem;margin:0.25rem 0 0;">Enter your website URL to get started.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="url" style="font-weight:600;color:#4b5563;font-size:0.85rem;display:block;margin-bottom:0.5rem;">Website URL</label>
                        <input type="url" id="url" name="url" placeholder="https://www.example.com" required
                               style="width:100%;padding:0.75rem;font-size:0.95rem;background:#f9fafb;border:1px solid #e5e7eb;color:#111827;border-radius:10px;outline:none;"
                               onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e5e7eb'">
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0 justify-content-end">
                    <button type="button" data-bs-dismiss="modal" style="padding:0.5rem 1.25rem;background:#f3f4f6;color:#6b7280;border:1px solid #d1d5db;border-radius:10px;font-weight:600;cursor:pointer;margin-right:0.5rem;">Cancel</button>
                    <button type="submit" class="btn-filament btn-filament-primary" style="border-radius:10px;">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
