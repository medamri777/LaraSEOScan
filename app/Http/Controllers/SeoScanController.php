<?php

namespace App\Http\Controllers;

use App\Models\SeoScan;
use App\Services\PlanLimitService;
use App\Support\PlanLimits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SeoScanExport;
use App\Jobs\ProcessSeoScan;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreScanRequest;

class SeoScanController extends Controller
{
    public function index()
    {
        $recentScans = SeoScan::where('user_id', auth()->id())
            ->whereIn('status', ['COMPLETED', 'FAILED', 'RUNNING'])
            ->latest()
            ->take(10)
            ->get(['id', 'uuid', 'url', 'status', 'total_urls_found', 'time_elapsed', 'created_at']);

        $scans = SeoScan::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $scanStats = [
            'total'     => SeoScan::where('user_id', auth()->id())->count(),
            'completed' => SeoScan::where('user_id', auth()->id())->where('status', 'COMPLETED')->count(),
            'pending'   => SeoScan::where('user_id', auth()->id())->where('status', '!=', 'COMPLETED')->count(),
        ];

        $projects = \App\Models\Project::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('scans')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('scans', 'scanStats', 'recentScans', 'projects'));
    }

    public function dashboard()
    {
        return $this->index();
    }

    public function create()
    {
        return view('scan.index');
    }

    public function scan(StoreScanRequest $request)
    {
        $validated  = $request->validated();
        $url        = $validated['url'];
        $projectId  = $validated['project_id'] ?? null;

        $user = Auth::user();

        if ($projectId) {
            $project = \App\Models\Project::where('id', $projectId)
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
        }

        $tenant = $user->tenant;
        $plan   = $tenant?->plan ?? 'free';
        $scanLimit = PlanLimits::scanLimitPerDay($plan) ?? PHP_INT_MAX;

        $todayScanCount = SeoScan::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($todayScanCount >= $scanLimit) {
            $label = PlanLimits::labelFor($plan);
            return redirect()->back()->withErrors([
                'limit' => "🚫 You've reached your {$scanLimit} daily scan limit on the {$label} plan. Upgrade to run more scans or try again tomorrow.",
            ]);
        }

        $sitewideChecks = $this->checkSitewidesSeoFiles($url);

        $scan = SeoScan::create([
            'user_id'         => $user->id,
            'project_id'      => $projectId,
            'url'             => $url,
            'status'          => 'QUEUED',
            'has_robots_txt'  => $sitewideChecks['robots_txt'],
            'has_sitemap_xml' => $sitewideChecks['sitemap_xml'],
        ]);

        ProcessSeoScan::dispatch($scan);

        if ($projectId) {
            return redirect()->route('projects.show', $projectId)
                ->with('success', 'Scan d\'audit de site soumis avec succès ! Les résultats seront bientôt disponibles.');
        }

        return redirect()->route('dashboard')
            ->with('message', 'Scan submitted! Results will be available shortly.');
    }

    public function results($uuid)
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // If the scan is not done yet, show the waiting/status page
        if ($scan->status !== 'COMPLETED') {
            return view('scan.status', compact('scan'));
        }

        // Paginate Issues
        $paginatedIssues = \App\Models\SeoIssue::whereHas('page', function($q) use ($scan) {
                $q->where('seo_scan_id', $scan->id);
            })
            ->with('page')
            ->orderByRaw("FIELD(severity, 'critical', 'error', 'warning', 'info')")
            ->paginate(10, ['*'], 'issues_page');

        // Paginate Pages
        $paginatedPages = $scan->pages()
            ->with(['issues', 'links', 'images'])
            ->paginate(10, ['*'], 'pages_page');

        // Sitewide checks
        $sitewideChecks = [
            'robots_txt'  => $scan->has_robots_txt,
            'sitemap_xml' => $scan->has_sitemap_xml,
            'base_url'    => parse_url($scan->url, PHP_URL_SCHEME) . '://' . parse_url($scan->url, PHP_URL_HOST),
        ];

        // Issue Breakdown for Dashboard
        $issueBreakdown = [
            'missing_titles' => \App\Models\SeoIssue::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('rule_key', 'like', '%title%missing%')->count(),
            'missing_descriptions' => \App\Models\SeoIssue::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('rule_key', 'like', '%description%missing%')->count(),
            'missing_h1' => \App\Models\SeoIssue::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('rule_key', 'like', '%h1%missing%')->count(),
            'duplicate_titles' => \App\Models\SeoIssue::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('rule_key', 'like', '%title%duplicate%')->count(),
            'redirect_chains' => \App\Models\SeoIssue::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('rule_key', 'like', '%redirect%chain%')->count(),
            'images_missing_alt' => \App\Models\SeoImage::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where(fn($q) => $q->whereNull('alt')->orWhere('alt', ''))->count(),
            'broken_pages' => \App\Models\SeoLink::whereHas('page', fn($q) => $q->where('seo_scan_id', $scan->id))
                ->where('status_code', '>=', 400)->count(),
            'slow_pages' => 0,
        ];

        return view('scan.results', compact('scan', 'sitewideChecks', 'paginatedIssues', 'paginatedPages', 'issueBreakdown'));
    }

    public function destroy($uuid)
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $scan->delete();

        return redirect()->route('dashboard')->with('success', 'Scan deleted successfully.');
    }

    public function exportPdf($uuid)
    {
        $scan = SeoScan::with(['pages.links', 'pages.images', 'user.tenant'])->where('uuid', $uuid)->firstOrFail();

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);

        $tenant = $scan->user?->tenant;

        $pdf = Pdf::loadView('exports.scan-pdf', compact('scan', 'tenant'));
        return $pdf->download('seo-scan-' . $scan->uuid . '.pdf');
    }

    public function exportCsv($uuid)
    {
        $scan = SeoScan::where('uuid', $uuid)->firstOrFail();
        return Excel::download(new SeoScanExport($scan->id), 'seo-scan-' . $scan->uuid . '.csv');
    }

    public function status($uuid)
    {
        $scan = SeoScan::where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();
        return view('scan.status', compact('scan'));
    }

    protected function checkSitewidesSeoFiles($url)
    {
        try {
            $parsed   = parse_url($url);
            $baseUrl  = $parsed['scheme'] . '://' . $parsed['host'];

            $robotsResponse  = Http::timeout(5)->get($baseUrl . '/robots.txt');
            $sitemapResponse = Http::timeout(5)->get($baseUrl . '/sitemap.xml');

            return [
                'robots_txt'  => $robotsResponse->successful(),
                'sitemap_xml' => $sitemapResponse->successful(),
                'base_url'    => $baseUrl,
            ];
        } catch (\Exception $e) {
            return [
                'robots_txt'  => false,
                'sitemap_xml' => false,
                'base_url'    => null,
            ];
        }
    }
}
