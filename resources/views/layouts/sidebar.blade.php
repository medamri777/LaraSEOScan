@php
    $tenant   = auth()->user()?->tenant;
    $planKey  = $tenant?->plan ?? 'free';
    $planService = app(\App\Services\PlanLimitService::class);
    $planLabel = \App\Support\PlanLimits::labelFor($planKey);

    // Current project URL for tool auto-fill
    $projectUrl = $currentProject?->url ?? '';
    $projectDomain = $projectUrl ? (parse_url($projectUrl, PHP_URL_HOST) ?? $projectUrl) : '';

    // Auto-open groups based on current route
    $seoActive = request()->routeIs('seo.analyzer.*') || request()->routeIs('tools.seo-audit') || request()->routeIs('tools.crawl-audit') || request()->routeIs('tools.serp-simulator');
    $kwActive = request()->routeIs('tools.keyword*') || request()->routeIs('tools.organic*');
    $siteActive = request()->routeIs('tools.search-console') || request()->routeIs('tools.authority*') || request()->routeIs('tools.backlink*') || request()->routeIs('tools.sitemap*') || request()->routeIs('tools.robots');
    $contentActive = request()->routeIs('tools.schema*') || request()->routeIs('tools.review*');
    $billingActive = request()->routeIs('pricing') || request()->routeIs('billing');
@endphp

<aside class="fixed top-0 left-0 bottom-0 w-[260px] z-50 flex flex-col transition-transform duration-300 lg:translate-x-0 sidebar-mobile-hidden"
       :class="sidebarOpen ? '!translate-x-0' : ''"
       x-data="{
           billingOpen: {{ $billingActive ? 'true' : 'false' }},
           seoAnalysisOpen: {{ $seoActive ? 'true' : 'false' }},
           keywordToolsOpen: {{ $kwActive ? 'true' : 'false' }},
           siteToolsOpen: {{ $siteActive ? 'true' : 'false' }},
           contentToolsOpen: {{ $contentActive ? 'true' : 'false' }}
       }"
       style="background: #fff; border-right: 1px solid #e5e7eb; width: 260px;">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}"
       class="h-16 flex items-center gap-2.5 px-5 shrink-0 no-underline transition"
       style="border-bottom: 1px solid #e5e7eb; text-decoration: none; color: #111827; font-weight: 700; font-size: 1.125rem;">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 28px; height: 28px; fill: #10b981;">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
        </svg>
        <span>Seo4ma</span>
    </a>

    {{-- Active Project Selector --}}
    @if(isset($userProjects) && $userProjects->count() > 0)
    <div class="px-3 py-2" style="border-bottom: 1px solid #e5e7eb;">
        <form action="{{ route('projects.select') }}" method="POST" id="projectSelectorForm">
            @csrf
            <label style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; display: block; margin-bottom: 4px;">Active Project</label>
            <select name="project_id" onchange="this.form.submit()"
                    style="width: 100%; padding: 6px 8px; font-size: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; color: #111827; outline: none; cursor: pointer;">
                @foreach($userProjects as $p)
                    <option value="{{ $p->id }}" {{ (isset($currentProject) && $currentProject->id === $p->id) ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            <div style="font-size: 10px; color: #6b7280; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $projectUrl }}">
                <i class="bi bi-globe" style="font-size: 10px;"></i> {{ $projectDomain }}
            </div>
        </form>
    </div>
    @elseif(auth()->check() && auth()->user()->tenant_id)
    <div class="px-3 py-2" style="border-bottom: 1px solid #e5e7eb;">
        <label style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; display: block; margin-bottom: 4px;">Active Project</label>
        <a href="{{ route('projects.index') }}" style="font-size: 12px; color: #10b981; font-weight: 600;">
            <i class="bi bi-plus-circle"></i> Add your first project
        </a>
    </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3">
        {{-- Main section --}}
        <div class="px-3 pt-2 pb-1.5" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af;">Main</div>

        @php
            $activeClass = 'background: #ecfdf5; color: #047857; border-left: 2px solid #10b981; font-weight: 600;';
            $inactiveClass = 'color: #4b5563;';
        @endphp

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm mb-0.5 transition no-underline"
           style="text-decoration: none; {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
            <i class="bi bi-grid-1x2" style="font-size: 18px;"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('projects.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm mb-0.5 transition no-underline"
           style="text-decoration: none; {{ request()->routeIs('projects.*') ? $activeClass : $inactiveClass }}">
            <i class="bi bi-folder2" style="font-size: 18px;"></i>
            <span>Projects</span>
        </a>

        {{-- Billing group (collapsible) --}}
        <div class="mt-1">
            <button @click="billingOpen = !billingOpen"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm mb-0.5 transition w-full border-0 cursor-pointer"
                    style="background: transparent; color: #4b5563; font-size: 14px;">
                <i class="bi bi-credit-card" style="font-size: 18px;"></i>
                <span class="flex-1 text-left">Billing & Plans</span>
                <i class="bi bi-chevron-right" style="font-size: 12px; transition: transform 0.3s;" :style="'transform: rotate(' + (billingOpen ? '90deg' : '0deg') + ')'"></i>
            </button>
            <div x-show="billingOpen" x-collapse.duration.300ms class="ml-8">
                <a href="{{ route('pricing') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                   style="text-decoration: none; {{ request()->routeIs('pricing') ? $activeClass : $inactiveClass }}">
                    <i class="bi bi-tag" style="font-size: 16px;"></i>
                    <span>Pricing & Plans</span>
                </a>
                <a href="{{ route('billing') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                   style="text-decoration: none; {{ request()->routeIs('billing') ? $activeClass : $inactiveClass }}">
                    <i class="bi bi-receipt" style="font-size: 16px;"></i>
                    <span>My Subscription</span>
                </a>
            </div>
        </div>

        {{-- Tools section label --}}
        <div class="px-3 pt-5 pb-1.5" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af;">Tools</div>

            {{-- SEO Analysis sub-group --}}
            <div class="mt-1">
                <button @click="seoAnalysisOpen = !seoAnalysisOpen"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition w-full border-0 cursor-pointer"
                        style="background: transparent; color: #6b7280; font-size: 13px; font-weight: 500;">
                    <i class="bi bi-clipboard-data" style="font-size: 16px;"></i>
                    <span class="flex-1 text-left">SEO Analysis</span>
                    <i class="bi bi-chevron-right" style="font-size: 10px; transition: transform 0.3s;" :style="'transform: rotate(' + (seoAnalysisOpen ? '90deg' : '0deg') + ')'"></i>
                </button>
                <div x-show="seoAnalysisOpen" x-collapse.duration.300ms class="ml-8">
                    @if($planService->canAccessTool($tenant, 'seo_analyzer'))
                    <a href="{{ route('seo.analyzer.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('seo.analyzer.*') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-search" style="font-size: 16px;"></i>
                        <span>SEO Analyzer</span>
                    </a>
                    @endif

                    @if($planService->canAccessTool($tenant, 'crawl_audit'))
                    <a href="{{ route('tools.seo-audit') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.seo-audit') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-search-heart" style="font-size: 16px;"></i>
                        <span>SEO Audit Tool</span>
                    </a>
                    <a href="{{ route('tools.crawl-audit') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.crawl-audit') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-diagram-3" style="font-size: 16px;"></i>
                        <span>Crawl Audit</span>
                    </a>
                    @else
                    <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5" style="color: #9ca3af; opacity: 0.5;">
                        <i class="bi bi-diagram-3" style="font-size: 16px;"></i>
                        <span>Crawl Audit</span>
                        <i class="bi bi-lock-fill ms-auto" style="font-size: 10px;"></i>
                    </span>
                    @endif

                    @if($planService->canAccessTool($tenant, 'serp_simulator'))
                    <a href="{{ route('tools.serp-simulator') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.serp-simulator') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-window-sidebar" style="font-size: 16px;"></i>
                        <span>SERP Simulator</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Keyword Tools sub-group --}}
            <div class="mt-1">
                <button @click="keywordToolsOpen = !keywordToolsOpen"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition w-full border-0 cursor-pointer"
                        style="background: transparent; color: #6b7280; font-size: 13px; font-weight: 500;">
                    <i class="bi bi-key" style="font-size: 16px;"></i>
                    <span class="flex-1 text-left">Keyword Tools</span>
                    <i class="bi bi-chevron-right" style="font-size: 10px; transition: transform 0.3s;" :style="'transform: rotate(' + (keywordToolsOpen ? '90deg' : '0deg') + ')'"></i>
                </button>
                <div x-show="keywordToolsOpen" x-collapse.duration.300ms class="ml-8">
                    @if($planService->canAccessTool($tenant, 'keyword_research'))
                    <a href="{{ route('tools.keyword-overview') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.keyword-overview') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-bar-chart" style="font-size: 16px;"></i>
                        <span>Keyword Overview</span>
                    </a>
                    <a href="{{ route('tools.keyword-research') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.keyword-research') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-key" style="font-size: 16px;"></i>
                        <span>Keyword Research</span>
                    </a>
                    <a href="{{ route('tools.keyword-magic') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.keyword-magic') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-magic" style="font-size: 16px;"></i>
                        <span>Keyword Magic</span>
                    </a>
                    <a href="{{ route('tools.organic-research') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.organic-research') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-graph-up" style="font-size: 16px;"></i>
                        <span>Organic Research</span>
                    </a>
                    @else
                    <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5" style="color: #9ca3af; opacity: 0.5;">
                        <i class="bi bi-key" style="font-size: 16px;"></i>
                        <span>Keyword Research</span>
                        <i class="bi bi-lock-fill ms-auto" style="font-size: 10px;"></i>
                    </span>
                    @endif
                </div>
            </div>

            {{-- Site Tools sub-group --}}
            <div class="mt-1">
                <button @click="siteToolsOpen = !siteToolsOpen"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition w-full border-0 cursor-pointer"
                        style="background: transparent; color: #6b7280; font-size: 13px; font-weight: 500;">
                    <i class="bi bi-gear" style="font-size: 16px;"></i>
                    <span class="flex-1 text-left">Site Tools</span>
                    <i class="bi bi-chevron-right" style="font-size: 10px; transition: transform 0.3s;" :style="'transform: rotate(' + (siteToolsOpen ? '90deg' : '0deg') + ')'"></i>
                </button>
                <div x-show="siteToolsOpen" x-collapse.duration.300ms class="ml-8">
                    @if($planService->canAccessTool($tenant, 'search_console'))
                    <a href="{{ route('tools.search-console') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.search-console') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-google" style="font-size: 16px;"></i>
                        <span>Search Console</span>
                    </a>
                    @else
                    <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5" style="color: #9ca3af; opacity: 0.5;">
                        <i class="bi bi-google" style="font-size: 16px;"></i>
                        <span>Search Console</span>
                        <i class="bi bi-lock-fill ms-auto" style="font-size: 10px;"></i>
                    </span>
                    @endif

                    @if($planService->canAccessTool($tenant, 'authority_checker'))
                    <a href="{{ route('tools.authority-checker') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.authority-checker') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-shield-check" style="font-size: 16px;"></i>
                        <span>Authority Checker</span>
                    </a>
                    @endif

                    @if($planService->canAccessTool($tenant, 'backlink_checker'))
                    <a href="{{ route('tools.backlink-checker') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.backlink-checker') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-link-45deg" style="font-size: 16px;"></i>
                        <span>Backlink Checker</span>
                    </a>
                    @endif

                    @if($planService->canAccessTool($tenant, 'sitemap_crawler'))
                    <a href="{{ route('tools.sitemap-crawler') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.sitemap-crawler') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-diagram-3" style="font-size: 16px;"></i>
                        <span>Sitemap Crawler</span>
                    </a>
                    @endif

                    @if($planService->canAccessTool($tenant, 'robots_generator'))
                    <a href="{{ route('tools.robots') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.robots') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-robot" style="font-size: 16px;"></i>
                        <span>Robots.txt Generator</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Content sub-group --}}
            <div class="mt-1">
                <button @click="contentToolsOpen = !contentToolsOpen"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition w-full border-0 cursor-pointer"
                        style="background: transparent; color: #6b7280; font-size: 13px; font-weight: 500;">
                    <i class="bi bi-code-square" style="font-size: 16px;"></i>
                    <span class="flex-1 text-left">Content & Schema</span>
                    <i class="bi bi-chevron-right" style="font-size: 10px; transition: transform 0.3s;" :style="'transform: rotate(' + (contentToolsOpen ? '90deg' : '0deg') + ')'"></i>
                </button>
                <div x-show="contentToolsOpen" x-collapse.duration.300ms class="ml-8">
                    @if($planService->canAccessTool($tenant, 'schema_generator'))
                    <a href="{{ route('tools.schema-generator') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.schema-generator') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-code-square" style="font-size: 16px;"></i>
                        <span>Schema Generator</span>
                    </a>
                    @endif

                    <a href="{{ route('tools.review-link-generator') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5 transition no-underline"
                       style="text-decoration: none; {{ request()->routeIs('tools.review-link-generator') ? $activeClass : $inactiveClass }}">
                        <i class="bi bi-star" style="font-size: 16px;"></i>
                        <span>Review Link Generator</span>
                    </a>
                </div>
            </div>

    </nav>

    {{-- Plan badge + footer --}}
    <div class="p-3 shrink-0" style="border-top: 1px solid #e5e7eb;">
        <div class="flex items-center justify-between p-3 rounded-lg"
             style="{{ $planKey === 'free' ? 'background: #f9fafb; border: 1px solid #e5e7eb;' : 'background: #ecfdf5; border: 1px solid #a7f3d0;' }}">
            <div>
                <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 2px;">Current Plan</div>
                <div style="font-size: 14px; font-weight: 700; color: {{ $planKey === 'free' ? '#4b5563' : '#047857' }};">{{ $planLabel }}</div>
            </div>
            @if($planKey === 'free')
                <a href="{{ route('pricing') }}"
                   class="no-underline"
                   style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: #10b981; color: #fff; text-decoration: none; white-space: nowrap;">
                    Upgrade &uarr;
                </a>
            @else
                <a href="{{ route('billing') }}"
                   class="no-underline"
                   style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; text-decoration: none; white-space: nowrap;">
                    Manage
                </a>
            @endif
        </div>

        <a href="{{ route('dashboard') }}"
           class="flex items-center justify-center gap-2 px-3 py-2.5 mt-2 rounded-lg text-sm no-underline transition"
           style="font-weight: 600; background: #ecfdf5; color: #059669; text-decoration: none;">
            <i class="bi bi-diagram-3" style="font-size: 18px;"></i>
            <span>Start Crawl</span>
        </a>
    </div>
</aside>
