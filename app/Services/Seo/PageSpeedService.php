<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PageSpeedService
{
    private string $baseUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_pagespeed.api_key');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Call the PageSpeed Insights API for a given URL and extract structured data
     */
    public function analyze(string $url, string $strategy = 'mobile'): ?array
    {
        if (!$this->isConfigured()) {
            Log::info("Google PageSpeed Insights API key is not configured. Returning mock/default values.");
            return $this->getMockData($url);
        }

        try {
            $categories = ['performance', 'seo', 'accessibility', 'best-practices'];
            
            $query = [
                'url'      => $url,
                'key'      => $this->apiKey,
                'strategy' => $strategy,
            ];

            // Build request with multiple category parameters
            $urlWithParams = $this->baseUrl . '?' . http_build_query($query);
            foreach ($categories as $category) {
                $urlWithParams .= '&category=' . urlencode($category);
            }

            $response = Http::timeout(45)->get($urlWithParams);

            if (!$response->successful()) {
                Log::warning("PageSpeed Insights API error", [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500)
                ]);
                return null;
            }

            $json = $response->json();
            return $this->parseResponse($json);

        } catch (\Throwable $e) {
            Log::error("PageSpeed Insights API exception", ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse the raw Lighthouse response into a structured format
     */
    protected function parseResponse(array $json): array
    {
        $lighthouse = $json['lighthouseResult'] ?? [];
        $categories = $lighthouse['categories'] ?? [];
        $audits = $lighthouse['audits'] ?? [];

        // Parse category scores (scaled 0-100)
        $scores = [
            'performance'    => isset($categories['performance']['score']) ? (int)round($categories['performance']['score'] * 100) : null,
            'seo'            => isset($categories['seo']['score']) ? (int)round($categories['seo']['score'] * 100) : null,
            'accessibility'  => isset($categories['accessibility']['score']) ? (int)round($categories['accessibility']['score'] * 100) : null,
            'best_practices' => isset($categories['best-practices']['score']) ? (int)round($categories['best-practices']['score'] * 100) : null,
        ];

        // Parse Core Web Vitals and key audits
        $coreWebVitals = [
            'lcp'  => $audits['largest-contentful-paint']['numericValue'] ?? null,  // ms
            'fcp'  => $audits['first-contentful-paint']['numericValue'] ?? null,    // ms
            'cls'  => $audits['cumulative-layout-shift']['numericValue'] ?? null,    // decimal
            'ttfb' => $audits['server-response-time']['numericValue'] ?? null,       // ms
            'inp'  => $audits['interaction-to-next-paint']['numericValue'] ?? null,  // ms
            'fid'  => $audits['max-potential-fid']['numericValue'] ?? null,          // ms
            
            // Human readable string formats
            'lcp_display'  => $audits['largest-contentful-paint']['displayValue'] ?? null,
            'fcp_display'  => $audits['first-contentful-paint']['displayValue'] ?? null,
            'cls_display'  => $audits['cumulative-layout-shift']['displayValue'] ?? null,
            'ttfb_display' => $audits['server-response-time']['displayValue'] ?? null,
        ];

        // Parse Opportunities
        $opportunities = [];
        foreach ($audits as $key => $audit) {
            $details = $audit['details'] ?? null;
            if ($details && isset($details['type']) && $details['type'] === 'opportunity') {
                $score = $audit['score'] ?? 1.0;
                if ($score < 0.9) {
                    $opportunities[] = [
                        'key'           => $key,
                        'title'         => $audit['title'] ?? '',
                        'description'   => $audit['description'] ?? '',
                        'score'         => $score,
                        'savings_ms'    => $details['overallSavingsMs'] ?? 0,
                        'savings_bytes' => $details['overallSavingsBytes'] ?? 0,
                    ];
                }
            }
        }

        // Sort opportunities by savings or importance (descending)
        usort($opportunities, fn($a, $b) => $b['savings_ms'] <=> $a['savings_ms']);

        return [
            'scores'         => $scores,
            'core_vitals'    => $coreWebVitals,
            'opportunities'  => $opportunities,
        ];
    }

    /**
     * Provide deterministic mock data for testing and local environments when no key is set.
     */
    protected function getMockData(string $url): array
    {
        $seed = crc32($url);
        srand($seed);

        $perf = rand(65, 95);
        $seo = rand(70, 98);
        $acc = rand(75, 96);
        $bp = rand(80, 97);

        return [
            'scores' => [
                'performance'    => $perf,
                'seo'            => $seo,
                'accessibility'  => $acc,
                'best_practices' => $bp,
            ],
            'core_vitals' => [
                'lcp'          => rand(1200, 3500),
                'fcp'          => rand(800, 2000),
                'cls'          => rand(10, 250) / 1000,
                'ttfb'         => rand(100, 800),
                'inp'          => rand(80, 250),
                'fid'          => rand(10, 100),
                'lcp_display'  => (rand(12, 35) / 10) . ' s',
                'fcp_display'  => (rand(8, 20) / 10) . ' s',
                'cls_display'  => (rand(10, 250) / 1000),
                'ttfb_display' => rand(100, 800) . ' ms',
            ],
            'opportunities' => [
                [
                    'key'           => 'render-blocking-resources',
                    'title'         => 'Éliminer les ressources qui bloquent le rendu',
                    'description'   => 'Des ressources empêchent le premier rendu de votre page. Envisagez de fournir les fichiers JS/CSS critiques en ligne et de différer tous les fichiers JS/styles non critiques.',
                    'score'         => 0.45,
                    'savings_ms'    => rand(400, 1200),
                    'savings_bytes' => rand(50000, 150000),
                ],
                [
                    'key'           => 'modern-image-formats',
                    'title'         => 'Servir des images aux formats de nouvelle génération',
                    'description'   => 'Les formats d\'image comme WebP et AVIF offrent souvent une meilleure compression que le format PNG ou JPEG, ce qui accélère les téléchargements et réduit la consommation de données.',
                    'score'         => 0.55,
                    'savings_ms'    => rand(200, 800),
                    'savings_bytes' => rand(100000, 300000),
                ],
                [
                    'key'           => 'offscreen-images',
                    'title'         => 'Différer le chargement des images hors écran',
                    'description'   => 'Envisagez de charger les images hors écran et masquées après le chargement de toutes les ressources critiques afin de réduire le temps d\'interactivité.',
                    'score'         => 0.72,
                    'savings_ms'    => rand(100, 400),
                    'savings_bytes' => rand(30000, 80000),
                ],
            ]
        ];
    }
}
