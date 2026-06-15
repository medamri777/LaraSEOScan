<?php

namespace App\Http\Traits;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the URL / domain from the user's active project in session.
 * This completely ignores whatever the user submitted in the form,
 * preventing DevTools / inspect-element cheating.
 */
trait UsesProjectDomain
{
    /**
     * Get the URL of the currently selected project.
     * Returns null if no project is selected or it doesn't belong to the user.
     */
    protected function getProjectUrl(): ?string
    {
        $user = Auth::user();
        if (! $user || ! $user->tenant_id) {
            return null;
        }

        $projectId = session('current_project_id');
        if (! $projectId) {
            // Auto-select first project
            $first = Project::where('tenant_id', $user->tenant_id)->first();
            if ($first) {
                session(['current_project_id' => $first->id]);
                return $first->url;
            }
            return null;
        }

        $project = Project::where('id', $projectId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        return $project?->url;
    }

    /**
     * Get just the host/domain from the current project (e.g. "example.com").
     */
    protected function getProjectDomain(): ?string
    {
        $url = $this->getProjectUrl();
        if (! $url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        return preg_replace('/^www\./', '', strtolower($host));
    }

    /**
     * Abort with 403 if no project is selected.
     * Call this at the top of tool actions that require a project.
     */
    protected function requireProject(): string
    {
        $url = $this->getProjectUrl();
        if (! $url) {
            abort(403, 'You must create and select a project before using this tool.');
        }
        return $url;
    }

    /**
     * Same as requireProject() but returns just the domain.
     */
    protected function requireProjectDomain(): string
    {
        $domain = $this->getProjectDomain();
        if (! $domain) {
            abort(403, 'You must create and select a project before using this tool.');
        }
        return $domain;
    }
}
