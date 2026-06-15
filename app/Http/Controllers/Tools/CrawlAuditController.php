<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\WebCrawlService;
use App\Models\SeoScan;
use App\Models\SeoPage;
use App\Http\Traits\UsesProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlAuditController extends Controller
{
    use UsesProjectDomain;
    protected WebCrawlService $crawlService;

    public function __construct(WebCrawlService $crawlService)
    {
        $this->crawlService = $crawlService;
    }

    public function index()
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'crawl_audit')) {
            return redirect()->route('pricing')->with('error', 'Please upgrade your plan to access the Crawl Audit Tool.');
        }

        $recentScans = SeoScan::where('user_id', auth()->id())
            ->whereIn('status', ['COMPLETED', 'FAILED'])
            ->latest()
            ->take(10)
            ->get(['id', 'uuid', 'url', 'status', 'total_urls_found', 'time_elapsed', 'created_at']);

        return view('tools.crawl-audit', [
            'recentScans' => $recentScans,
        ]);
    }

    public function start(Request $request)
    {
        if (!app(\App\Services\PlanLimitService::class)->canAccessTool(auth()->user()->tenant, 'crawl_audit')) {
            return response()->json(['error' => 'Please upgrade your plan to perform crawl audits.'], 403);
        }

        $request->validate([
            'max_pages' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ]);

        // Force URL from active project — ignore whatever was submitted
        $url = $this->requireProject();
        $taskId = (string) Str::uuid();

        try {
            $result = $this->crawlService->startCrawl($url, [
                'task_id' => $taskId,
                'max_pages' => $request->input('max_pages', 200),
                'user_id' => auth()->id(),
            ]);

            Log::debug('CrawlAudit: start response', ['result' => $result]);
            return response()->json($result);
        } catch (\Throwable $e) {
            Log::warning('CrawlAudit: start exception', ['error' => $e->getMessage(), 'task_id' => $taskId]);
            return response()->json([
                'error' => $e->getMessage(),
                'task_id' => $taskId,
            ], 500);
        }
    }

    public function status(string $taskId)
    {
        $progress = $this->crawlService->getProgress($taskId);

        if (!$progress) {
            $scan = SeoScan::where('uuid', $taskId)->first();
            if ($scan) {
                return response()->json([
                    'status' => $scan->status === 'COMPLETED' ? 'completed' : 'unknown',
                    'scan_id' => $scan->id,
                ]);
            }
            return response()->json(['status' => 'unknown']);
        }

        return response()->json($progress);
    }

    public function data(string $taskId)
    {
        $scan = SeoScan::where('uuid', $taskId)->firstOrFail();

        if ($scan->status !== 'COMPLETED') {
            return response()->json(['error' => 'Crawl not completed yet'], 400);
        }

        $data = $this->crawlService->getCrawlData($scan->id);
        return response()->json($data);
    }

    public function pages(Request $request, string $taskId)
    {
        $scan = SeoScan::where('uuid', $taskId)->firstOrFail();

        $query = SeoPage::where('seo_scan_id', $scan->id);

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($sq) use ($q) {
                $sq->where('url', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->input('status_code'));
        }

        if ($request->filled('has_issue')) {
            $hasIssue = $request->input('has_issue') === 'true';
            if ($hasIssue) {
                $query->whereHas('issues');
            } else {
                $query->whereDoesntHave('issues');
            }
        }

        $perPage = min((int) $request->input('per_page', 50), 100);
        $pages = $query->orderBy('url')->paginate($perPage);

        return response()->json($pages);
    }

    public function pageDetail(int $pageId)
    {
        $page = $this->crawlService->getPage($pageId);
        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }
        return response()->json($page);
    }

    public function load(string $scan)
    {
        $scanModel = SeoScan::where('uuid', $scan)->orWhere('id', $scan)->first();
        if (!$scanModel) {
            return redirect()->route('tools.crawl-audit')->with('error', 'Scan not found');
        }

        return redirect()->route('tools.crawl-audit.results', ['taskId' => $scanModel->uuid]);
    }

    public function showResults(string $taskId)
    {
        $scan = SeoScan::where('uuid', $taskId)->firstOrFail();

        if ($scan->status !== 'COMPLETED') {
            return redirect()->route('tools.crawl-audit')->with('error', 'Crawl not completed yet.');
        }

        $data = $this->crawlService->getCrawlData($scan->id);

        return view('tools.crawl-audit-results', [
            'scan' => $scan,
            'taskId' => $taskId,
            'reportData' => $data,
        ]);
    }

    public function history()
    {
        $scans = SeoScan::where('user_id', auth()->id())
            ->whereIn('status', ['COMPLETED', 'FAILED'])
            ->latest()
            ->paginate(20);

        return view('tools.crawl-history', ['scans' => $scans]);
    }
}
