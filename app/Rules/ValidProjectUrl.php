<?php

namespace App\Rules;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that the given URL belongs to one of the authenticated user's projects.
 * This prevents users from analyzing domains they don't own.
 */
class ValidProjectUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        if (! $user || ! $user->tenant_id) {
            $fail('You must have a workspace to use this tool.');
            return;
        }

        // Normalize the submitted URL to extract the host
        $submittedHost = parse_url($value, PHP_URL_HOST);

        if (! $submittedHost) {
            // Try adding https:// and re-parsing
            $submittedHost = parse_url('https://' . $value, PHP_URL_HOST);
        }

        if (! $submittedHost) {
            $fail('Please enter a valid URL.');
            return;
        }

        // Clean host: remove www. prefix
        $submittedHost = preg_replace('/^www\./', '', strtolower($submittedHost));

        // Get all project hosts for this tenant
        $projects = Project::where('tenant_id', $user->tenant_id)->pluck('url');

        $allowed = $projects->map(function ($url) {
            $host = parse_url($url, PHP_URL_HOST) ?: $url;
            return preg_replace('/^www\./', '', strtolower($host));
        });

        if (! $allowed->contains($submittedHost)) {
            $fail('You can only analyze domains that belong to your projects. Please add this domain as a project first.');
        }
    }
}
