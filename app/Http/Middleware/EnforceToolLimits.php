<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceToolLimits
{
    public function __construct(private PlanLimitService $planService) {}

    /**
     * Handle an incoming request.
     *
     * Usage in routes:  ->middleware('tool.limit:crawl_audit')
     *
     * @param  string  $toolSlug  The tool identifier (e.g. seo_scan, crawl_audit)
     */
    public function handle(Request $request, Closure $next, string $toolSlug): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denyRequest($request, $toolSlug, 'Authentication required.');
        }

        $tenant = $user->tenant;

        $result = $this->planService->checkAndRecordDailyUsage($tenant, $user->id, $toolSlug);

        if (! $result['allowed']) {
            return $this->denyRequest($request, $toolSlug, $result['message'] ?? 'Daily limit reached.', $result);
        }

        return $next($request);
    }

    /**
     * Build the denial response based on request type (web redirect vs JSON 402).
     */
    private function denyRequest(Request $request, string $toolSlug, string $message, array $result = []): Response
    {
        $toolLabel = ucwords(str_replace('_', ' ', $toolSlug));

        if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message'          => $message,
                'error'            => 'plan_limit_exceeded',
                'limit_type'       => $toolSlug,
                'limit'            => $result['limit'] ?? null,
                'current'          => $result['used'] ?? null,
                'plan'             => $result['plan'] ?? 'free',
                'upgrade_required' => true,
            ], 402);
        }

        return redirect()->route('pricing')
            ->with('error', $message)
            ->with('limit_tool', $toolSlug);
    }
}
