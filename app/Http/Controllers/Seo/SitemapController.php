<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSitemapJob;
use App\Services\SitemapService;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    protected SitemapService $sitemap;

    public function __construct(SitemapService $sitemap)
    {
        $this->sitemap = $sitemap;
    }

    public function index()
    {
        $path = public_path('sitemap.xml');
        if (!file_exists($path)) {
            $this->sitemap->generate();
        }

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    public function regenerate(Request $request)
    {
        $ping = (bool) $request->input('ping', true);

        if ($request->input('async')) {
            GenerateSitemapJob::dispatch($ping);
            return response()->json(['message' => 'Sitemap generation queued']);
        }

        $stats = $this->sitemap->generate();
        return response()->json([
            'message' => 'Sitemap generated',
            'stats' => $stats,
        ]);
    }

    public function status()
    {
        return response()->json([
            'last_generated' => $this->sitemap->getLastGenerated(),
            'url_count' => $this->sitemap->getUrlCount(),
            'file_size' => $this->sitemap->getFileSize(),
            'stats' => $this->sitemap->getStats(),
        ]);
    }
}
