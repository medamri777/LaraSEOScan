<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RobotsTxtService
{
    protected array $rules = [];
    protected array $sitemaps = [];

    public function fetch(string $url): self
    {
        try {
            $parsed = parse_url($url);
            $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
            $robotsUrl = $baseUrl . '/robots.txt';

            $response = Http::timeout(5)->get($robotsUrl);

            if ($response->successful()) {
                $this->parse($response->body());
            }
        } catch (\Exception $e) {
            // Treat as allowed if robots.txt is unreachable
        }

        return $this;
    }

    protected function parse(string $content): void
    {
        $lines = explode("\n", $content);
        $userAgent = '*';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^User-agent:\s*(.+)$/i', $line, $matches)) {
                $userAgent = trim($matches[1]);
            } elseif (preg_match('/^Disallow:\s*(.*)$/i', $line, $matches)) {
                $path = trim($matches[1]);
                if (empty($path)) continue; // Empty disallow means allow all
                if ($userAgent === '*' || Str::contains(strtolower($userAgent), 'seo4mabot')) {
                    $this->rules[] = ['type' => 'disallow', 'path' => $path];
                }
            } elseif (preg_match('/^Allow:\s*(.*)$/i', $line, $matches)) {
                 $path = trim($matches[1]);
                 if ($userAgent === '*' || Str::contains(strtolower($userAgent), 'seo4mabot')) {
                    $this->rules[] = ['type' => 'allow', 'path' => $path];
                }
            } elseif (preg_match('/^Sitemap:\s*(.+)$/i', $line, $matches)) {
                $this->sitemaps[] = trim($matches[1]);
            }
        }
    }

    public function isAllowed(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        
        $allowed = true;
        $longestMatch = 0;

        foreach ($this->rules as $rule) {
            $rulePath = $rule['path'];
            if (empty($rulePath)) continue;

            $pattern = preg_quote($rulePath, '#');
            $pattern = str_replace('\*', '.*', $pattern);
            
            if (preg_match('#^' . $pattern . '#', $path)) {
                 if (strlen($rulePath) >= $longestMatch) {
                     $longestMatch = strlen($rulePath);
                     $allowed = ($rule['type'] === 'allow');
                 }
            }
        }

        return $allowed;
    }
}
