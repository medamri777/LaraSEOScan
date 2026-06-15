<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\RobotsService;
use App\Http\Traits\UsesProjectDomain;
use Illuminate\Http\Request;

class RobotsController extends Controller
{
    use UsesProjectDomain;
    protected RobotsService $robotsService;

    public function __construct(RobotsService $robotsService)
    {
        $this->robotsService = $robotsService;
    }

    public function index(Request $request)
    {
        $url = $request->input('url', '');
        $siteType = $request->input('site_type', 'custom');
        $blockAi = $request->has('block_ai');
        $blockAll = $request->has('block_all');

        $robotsTxt = null;
        $domain = '';
        $usedPaths = [];
        $level = $request->input('protection_level', 'standard');

        if ($request->has('generate')) {
            // Force URL from active project — ignore whatever was submitted
            $url = $this->requireProject();
            $parsed = parse_url($url);
            $domain = $parsed['host'] ?? $url;
            $domain = preg_replace('#^www\.#', '', $domain);

            if ($blockAll) {
                $robotsTxt = "User-agent: *\nDisallow: /\n";
            } else {
                if (!$this->robotsService->validateLevel($level)) {
                    $level = 'standard';
                }

                config(['seo.domain' => $domain]);
                $robotsTxt = $this->robotsService->buildForLevel($level, $siteType);
            }
        }

        $types = [
            'blog' => 'Blog',
            'ecommerce' => 'E-commerce',
            'saas' => 'SaaS / Web App',
            'wordpress' => 'WordPress',
            'wix' => 'Wix',
            'static' => 'Static HTML',
            'custom' => 'Custom',
        ];

        $levels = [
            'minimal' => 'Minimal — block admin pages only',
            'standard' => 'Standard — block admin, private & login pages',
            'maximum' => 'Maximum — block admin, private, AI scrapers & sensitive files',
        ];

        return view('tools.robots-generator', compact(
            'url', 'siteType', 'level',
            'blockAi', 'blockAll',
            'robotsTxt', 'domain', 'usedPaths', 'types', 'levels'
        ));
    }
}
