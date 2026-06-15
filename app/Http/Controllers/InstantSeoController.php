<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SeoReportService;
use App\Http\Traits\UsesProjectDomain;

class InstantSeoController extends Controller
{
    use UsesProjectDomain;

    protected SeoReportService $reportService;

    public function __construct(SeoReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        return view('seo.analyzer');
    }

    public function analyze(Request $request)
    {
        // Force URL from active project — ignore whatever was submitted
        $url = $this->requireProject();

        try {
            $analysis = $this->reportService->analyze($url);

            $viewData = [
                'url' => $analysis['url'],
                'http_status' => $analysis['http_status'],
                'title' => $analysis['general']['title'],
                'description' => $analysis['general']['description'],
                'canonical' => $analysis['general']['canonical'],
                'canonical_matches' => $analysis['general']['canonical_matches'],
                'robots' => $analysis['general']['robots'],
                'x_robots' => $analysis['general']['x_robots'],
                'author' => $analysis['general']['author'],
                'generator' => $analysis['general']['generator'],
                'headings' => $analysis['headings'],
                'images' => $analysis['images'],
                'links' => $analysis['links'],
                'schemas' => $analysis['schemas'],
                'social' => $analysis['social'],
                'diagnostics' => $analysis['diagnostics'],
                'pagespeed' => $analysis['pagespeed'],
                'robots_txt' => $analysis['robots_txt'],
                'sitemap' => $analysis['sitemap'],
                'technical' => $analysis['technical'],
            ];

            return view('seo.analyzer', [
                'analysis' => $viewData,
                'scannedUrl' => $url,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['url' => "Error analyzing site: " . $e->getMessage()]);
        }
    }
}
