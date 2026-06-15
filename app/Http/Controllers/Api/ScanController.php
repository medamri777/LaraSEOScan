<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSeoScan;
use App\Models\SeoIssue;
use App\Models\SeoScan;
use App\Exports\SeoScanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class ScanController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $scans = SeoScan::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $stats = [
            'total'     => SeoScan::where('user_id', $user->id)->count(),
            'completed' => SeoScan::where('user_id', $user->id)->where('status', 'COMPLETED')->count(),
            'pending'   => SeoScan::where('user_id', $user->id)->where('status', '!=', 'COMPLETED')->count(),
        ];

        return response()->json([
            'scans' => $scans,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'        => ['required', 'url', 'max:2048'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $user = $request->user();

        // If project_id given, ensure it belongs to the user's tenant
        if (! empty($validated['project_id']) && $user->hasTenant()) {
            $project = \App\Models\Project::where('id', $validated['project_id'])
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
        }

        // Daily scan limit is enforced by the 'tool.limit:seo_scan' middleware

        $sitewideChecks = $this->checkSitewideSeoFiles($validated['url']);

        $scan = SeoScan::create([
            'user_id'        => $user->id,
            'url'            => $validated['url'],
            'status'         => 'QUEUED',
            'has_robots_txt' => $sitewideChecks['robots_txt'],
            'has_sitemap_xml'=> $sitewideChecks['sitemap_xml'],
            'project_id'     => $validated['project_id'] ?? null,
        ]);

        ProcessSeoScan::dispatch($scan);

        return response()->json([
            'message' => 'Scan queued successfully.',
            'scan'    => $this->formatScan($scan),
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $issues = SeoIssue::whereHas('page', fn ($q) => $q->where('seo_scan_id', $scan->id))
            ->with('page')
            ->orderByRaw("CASE severity
                WHEN 'critical' THEN 1
                WHEN 'error'    THEN 2
                WHEN 'warning'  THEN 3
                WHEN 'info'     THEN 4
                ELSE 5 END")
            ->paginate(20, ['*'], 'issues_page');

        $pages = $scan->pages()
            ->with(['issues', 'links', 'images'])
            ->paginate(10, ['*'], 'pages_page');

        return response()->json([
            'scan'    => $this->formatScan($scan),
            'issues'  => $issues,
            'pages'   => $pages,
            'sitewide'=> [
                'robots_txt'  => $scan->has_robots_txt,
                'sitemap_xml' => $scan->has_sitemap_xml,
                'base_url'    => parse_url($scan->url, PHP_URL_SCHEME) . '://' . parse_url($scan->url, PHP_URL_HOST),
            ],
        ]);
    }

    public function status(string $uuid): JsonResponse
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'uuid'       => $scan->uuid,
            'status'     => $scan->status,
            'url'        => $scan->url,
            'created_at' => $scan->created_at,
            'updated_at' => $scan->updated_at,
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $scan->delete();

        return response()->json(['message' => 'Scan deleted successfully.']);
    }

    public function exportPdf(string $uuid)
    {
        $user = Auth::user();

        $scan = SeoScan::with(['pages.links', 'pages.images'])
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Load tenant for white-label branding
        $tenant = $user->hasTenant() ? $user->tenant : null;

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);

        $pdf = Pdf::loadView('exports.scan-pdf', [
            'scan'   => $scan,
            'tenant' => $tenant,
        ]);

        $filename = ($tenant?->agency_name ?? 'seo') . '-audit-' . $scan->uuid . '.pdf';

        return $pdf->download($filename);
    }

    public function exportCsv(string $uuid)
    {
        $scan = SeoScan::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return Excel::download(new SeoScanExport($scan->id), 'seo-scan-' . $scan->uuid . '.csv');
    }

    private function formatScan(SeoScan $scan): array
    {
        return [
            'uuid'            => $scan->uuid,
            'url'             => $scan->url,
            'status'          => $scan->status,
            'has_robots_txt'  => $scan->has_robots_txt,
            'has_sitemap_xml' => $scan->has_sitemap_xml,
            'score_total'     => $scan->score_total,
            'score_technical' => $scan->score_technical,
            'score_on_page'   => $scan->score_on_page,
            'score_local'     => $scan->score_local,
            'score_mobile'    => $scan->score_mobile,
            'score_speed'     => $scan->score_speed,
            'created_at'      => $scan->created_at,
            'updated_at'      => $scan->updated_at,
        ];
    }

    private function checkSitewideSeoFiles(string $url): array
    {
        try {
            $parsed  = parse_url($url);
            $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

            $robots  = Http::timeout(5)->get($baseUrl . '/robots.txt');
            $sitemap = Http::timeout(5)->get($baseUrl . '/sitemap.xml');

            return [
                'robots_txt'  => $robots->successful(),
                'sitemap_xml' => $sitemap->successful(),
                'base_url'    => $baseUrl,
            ];
        } catch (\Exception) {
            return ['robots_txt' => false, 'sitemap_xml' => false, 'base_url' => null];
        }
    }
}
