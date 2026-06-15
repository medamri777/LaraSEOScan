<?php

namespace App\Rules;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that the given domain (e.g. "example.com") belongs to one of the user's projects.
 * Used by tools that accept a plain domain instead of a full URL.
 */
class ValidProjectDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        if (! $user || ! $user->tenant_id) {
            $fail('You must have a workspace to use this tool.');
            return;
        }

        // Normalize domain: strip protocol and www
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', strtolower(trim($value)));
        $domain = explode('/', $domain)[0]; // remove any path

        // Get all project hosts for this tenant
        $projects = Project::where('tenant_id', $user->tenant_id)->pluck('url');

        $allowed = $projects->map(function ($url) {
            $host = parse_url($url, PHP_URL_HOST) ?: $url;
            return preg_replace('/^www\./', '', strtolower($host));
        });

        if (! $allowed->contains($domain)) {
            $fail('You can only analyze domains that belong to your projects. Please add this domain as a project first.');
        }
    }
}
