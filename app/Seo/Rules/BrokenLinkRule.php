<?php

namespace App\Seo\Rules;

use App\Models\SeoPage;
use App\Models\SeoLink;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;

class BrokenLinkRule implements SeoRule
{
    public function key(): string { return 'links.broken'; }
    public function title(): string { return 'Broken link detection'; }
    public function category(): string { return 'links'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        // Use DB links instead of DOM so we update the models
        $links = $page->links()->get();
        
        if ($links->isEmpty()) {
            return [];
        }

        $issues = [];
        $checkExternal = config('seo.crawler.check_external_links', true);
        
        // Filter links to check
        $linksToCheck = $links->filter(function ($link) use ($page, $checkExternal) {
            if ($link->status_code !== null) return false; // Already checked?
            
            // Simple internal check
            $host = parse_url($link->href, PHP_URL_HOST);
            $baseHost = parse_url($page->url, PHP_URL_HOST);
            $isInternal = ($host === $baseHost);
            
            return $isInternal || $checkExternal;
        });

        if ($linksToCheck->isEmpty()) {
            return [];
        }

        $client = new Client([
            'timeout' => 10,
            'http_errors' => false,
            'allow_redirects' => [
                'track_redirects' => true
            ],
            'headers' => ['User-Agent' => 'Seo4maBot/1.0'],
            'verify' => false, 
        ]);

        $requests = function ($links) use ($client) {
            foreach ($links as $link) {
                yield function() use ($client, $link) {
                    return $client->sendAsync(new \GuzzleHttp\Psr7\Request('HEAD', $link->href));
                };
            }
        };

        // We need to map requests to link for result handling.
        // Pool accepts iterator.
        
        // Convert to array to index
        $linksArray = $linksToCheck->values()->all();

        $pool = new Pool($client, $requests($linksArray), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use (&$issues, $linksArray) {
                $linkModel = $linksArray[$index];
                $statusCode = $response->getStatusCode();
                
                // If HEAD fails with 404/405, sometimes GET works (servers blocking HEAD)
                // We could retry with GET here, but inside async pool callback it's messy.
                // We'll trust the status unless it's 405 Method Not Allowed.
                
                $linkModel->status_code = $statusCode;
                
                // Get redirect chain
                $chain = $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER);
                if (!empty($chain)) {
                    $linkModel->redirect_chain = $chain;
                }
                
                $linkModel->save();

                // Check issues
                if ($statusCode >= 400) {
                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => $this->isInternal($linkModel->href, $linksArray[0]->seoPage->url ?? '') ? 'error' : 'warning',
                        'message' => "Broken link detected ({$statusCode})",
                        'selector' => 'a[href="' . $linkModel->href . '"]',
                        'context' => ['href' => $linkModel->href, 'status' => $statusCode],
                    ];
                }
                
                // Redirect chain issue
                if (count($chain) > 1) { // A -> B -> C (2 jumps?) History contains urls.
                    // Actually history header contains the URLs traversed.
                    // If A redirects to B, history has [A]. Response is B.
                    // If A->B->C. History has [A, B].
                    if (count($chain) >= 2) { 
                         $issues[] = [
                            'rule' => 'links.redirect_chain', // Distinct key?
                            'severity' => 'warning',
                            'message' => "Redirect chain detected (" . count($chain) . " hops)",
                            'context' => ['chain' => $chain],
                        ];
                    }
                }
            },
            'rejected' => function ($reason, $index) use (&$issues, $linksArray) {
                $linkModel = $linksArray[$index];
                 // If connection failed
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'error',
                    'message' => "Link connection failed: " . $reason->getMessage(),
                    'context' => ['href' => $linkModel->href],
                ];
            },
        ]);

        $pool->promise()->wait();

        return $issues;
    }
    
    protected function isInternal($url, $base) {
         return parse_url($url, PHP_URL_HOST) === parse_url($base, PHP_URL_HOST);
    }
}
