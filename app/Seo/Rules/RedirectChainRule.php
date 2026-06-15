<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;
use GuzzleHttp\Client;
use GuzzleHttp\RedirectMiddleware;

class RedirectChainRule implements SeoRule
{
    public function key(): string { return 'technical.redirect_chain'; }
    public function title(): string { return 'Redirect chain detection'; }
    public function category(): string { return 'technical'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $chain = [];

        // Check if redirect_history was set on the page model at crawl time
        if (isset($page->redirect_history) && is_array($page->redirect_history)) {
            $chain = $page->redirect_history;
        } else {
            // Fallback: If not set, run a quick HEAD request to follow redirects
            try {
                $client = new Client([
                    'timeout' => 5,
                    'allow_redirects' => [
                        'max' => 10,
                        'track_redirects' => true
                    ],
                    'headers' => ['User-Agent' => 'Seo4maBot/1.0'],
                    'verify' => false,
                ]);

                $response = $client->head($page->url);
                $chain = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (empty($chain)) {
            return [];
        }

        $hops = count($chain);

        if ($hops >= 4) {
            return [
                [
                    'rule' => $this->key(),
                    'severity' => 'error',
                    'message' => "Long redirect chain detected ({$hops} hops). This hurts load speed and search crawling.",
                    'context' => [
                        'hops' => $hops,
                        'chain' => $chain,
                    ],
                ]
            ];
        }

        if ($hops >= 2) {
            return [
                [
                    'rule' => $this->key(),
                    'severity' => 'warning',
                    'message' => "Redirect chain detected ({$hops} hops). Consider linking directly to the final destination.",
                    'context' => [
                        'hops' => $hops,
                        'chain' => $chain,
                    ],
                ]
            ];
        }

        // 1 redirect is fine (e.g. HTTP -> HTTPS)
        return [
            [
                'rule' => $this->key(),
                'severity' => 'info',
                'message' => "Single redirect detected ({$hops} hop). Path: " . implode(' -> ', array_merge($chain, [$page->url])),
                'context' => [
                    'hops' => $hops,
                    'chain' => $chain,
                ],
            ]
        ];
    }
}
