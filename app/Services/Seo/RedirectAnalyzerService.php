<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\RedirectMiddleware;

class RedirectAnalyzerService
{
    public function analyze(string $url): array
    {
        $redirects = [];
        
        try {
            $response = Http::withOptions([
                'allow_redirects' => [
                    'track_redirects' => true,
                ],
                'timeout' => 10,
            ])->head($url);

            // Guzzle stores redirect history in this header
            $history = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
            
            $status = $response->status();
            
            // If no history header, maybe no redirects or Guzzle didn't track it properly for HEAD?
            // Usually works.
            
            return [
                'redirect_count' => count($history),
                'redirects' => $history,
                'final_status' => $status,
                'final_url' => (string) $response->effectiveUri() 
            ];

        } catch (\Exception $e) {
             return [
                'redirect_count' => 0,
                'redirects' => [],
                'final_status' => 0,
                'final_url' => $url,
                'error' => $e->getMessage()
            ];
        }
    }
}
