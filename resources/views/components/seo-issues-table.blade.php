@props(['issues'])

<div class="table-responsive rounded" style="border: 1px solid #e5e7eb;">
    <table class="table table-dashboard">
        <thead>
            <tr>
                <th style="width: 32px;"></th>
                <th>Issue</th>
                <th>Severity</th>
                <th class="text-end">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($issues as $issue)
                <tr>
                    <td>
                        @if($issue->severity == 'critical')
                            <i class="bi bi-x-circle-fill" style="color: #ef4444;"></i>
                        @elseif($issue->severity == 'error')
                            <i class="bi bi-exclamation-octagon-fill" style="color: #ef4444;"></i>
                        @elseif($issue->severity == 'warning')
                            <i class="bi bi-exclamation-triangle-fill" style="color: #6b7280;"></i>
                        @else
                            <i class="bi bi-info-circle-fill" style="color: #6b7280;"></i>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size: 0.8125rem; color: #111827;">{{ $issue->message }}</div>
                        <small style="color: #6b7280; font-size: 0.75rem;">{{ $issue->page_url ?? 'Site-wide' }}</small>
                    </td>
                    <td>
                        <span class="badge-severity
                            @if($issue->severity == 'critical') text-white" style="background: #ef4444;
                            @elseif($issue->severity == 'error') text-white" style="background: #f3f4f6; color: #6b7280;
                            @elseif($issue->severity == 'warning') text-white" style="background: #f3f4f6; color: #6b7280;
                            @else text-white" style="background: #f3f4f6; color: #6b7280; @endif">
                            {{ ucfirst($issue->severity) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn-icon" data-bs-toggle="collapse" data-bs-target="#issue-{{ $loop->index }}">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="issue-{{ $loop->index }}">
                    <td colspan="4" class="px-4 py-3">
                        <div class="p-3 rounded" style="background: #f9fafb;">
                            <div class="mb-1" style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Context</div>
                            <pre class="mb-0 text-wrap" style="font-size: 0.75rem; white-space: pre-wrap; color: #111827;">{{ json_encode($issue->context, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state py-4">
                            <div class="empty-state-icon mx-auto" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h5 style="font-size: 0.9375rem;">No issues found</h5>
                            <p style="font-size: 0.8125rem;">Great job — everything looks good.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
