<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\SeoReportService;
use App\Http\Traits\UsesProjectDomain;
use Illuminate\Http\Request;

class SeoAuditController extends Controller
{
    use UsesProjectDomain;

    protected SeoReportService $reportService;

    public function __construct(SeoReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('tools.seo-audit');
    }

    public function analyze(Request $request)
    {
        // Force URL from active project — ignore whatever was submitted
        $url = $this->requireProject();

        try {
            $report = $this->reportService->analyze($url);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
