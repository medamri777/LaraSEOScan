<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoScanController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectKeywordController;
use App\Http\Controllers\ProjectCompetitorController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SeoScanController::class, 'dashboard'])->name('dashboard');

    // Billing / Subscription overview
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects CRUD
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/select', [ProjectController::class, 'select'])->name('projects.select');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Project Keywords
    Route::get('/projects/{projectId}/keywords', [ProjectKeywordController::class, 'index'])->name('projects.keywords.index');
    Route::post('/projects/{projectId}/keywords', [ProjectKeywordController::class, 'store'])->name('projects.keywords.store');
    Route::delete('/projects/{projectId}/keywords/{keywordId}', [ProjectKeywordController::class, 'destroy'])->name('projects.keywords.destroy');
    Route::post('/projects/{projectId}/keywords/check', [ProjectKeywordController::class, 'check'])->name('projects.keywords.check')
        ->middleware('tool.limit:keyword_research');
    Route::post('/projects/{projectId}/keywords/suggest', [ProjectKeywordController::class, 'suggest'])->name('projects.keywords.suggest')
        ->middleware('tool.limit:keyword_research');
    Route::get('/projects/{projectId}/keyword-gap', [ProjectKeywordController::class, 'keywordGap'])->name('projects.keyword-gap');

    // Project Competitors
    Route::get('/projects/{projectId}/competitors', [ProjectCompetitorController::class, 'index'])->name('projects.competitors.index');
    Route::post('/projects/{projectId}/competitors', [ProjectCompetitorController::class, 'store'])->name('projects.competitors.store');
    Route::delete('/projects/{projectId}/competitors/{competitorId}', [ProjectCompetitorController::class, 'destroy'])->name('projects.competitors.destroy');

    // Detailed SEO Analyzer (Glenn Gabe style)
    Route::get('/seo-analyzer', [\App\Http\Controllers\InstantSeoController::class, 'index'])->name('seo.analyzer.index');
    Route::post('/seo-analyzer', [\App\Http\Controllers\InstantSeoController::class, 'analyze'])->name('seo.analyzer.analyze')
        ->middleware('tool.limit:seo_analyzer');

    // Free SEO Tools
    Route::get('/tools/keyword-overview', [\App\Http\Controllers\FreeToolsController::class, 'keywordOverview'])->name('tools.keyword-overview');
    Route::get('/tools/keyword-research', [\App\Http\Controllers\FreeToolsController::class, 'keywordResearch'])->name('tools.keyword-research');
    Route::get('/tools/serp-simulator', [\App\Http\Controllers\FreeToolsController::class, 'serpSimulator'])->name('tools.serp-simulator');
    Route::get('/tools/authority-checker', [\App\Http\Controllers\FreeToolsController::class, 'authorityChecker'])->name('tools.authority-checker');
    Route::get('/tools/review-link-generator', [\App\Http\Controllers\FreeToolsController::class, 'reviewLinkGenerator'])->name('tools.review-link-generator');
    Route::post('/tools/review-link-generator/search', [\App\Http\Controllers\FreeToolsController::class, 'reviewLinkSearch'])->name('tools.review-link-generator.search');
    Route::get('/tools/backlink-checker', [\App\Http\Controllers\FreeToolsController::class, 'backlinkChecker'])->name('tools.backlink-checker');
    Route::match(['GET', 'POST'], '/tools/keyword-magic', [\App\Http\Controllers\FreeToolsController::class, 'keywordMagic'])->name('tools.keyword-magic');
    Route::get('/tools/organic-research', [\App\Http\Controllers\FreeToolsController::class, 'organicResearch'])->name('tools.organic-research');
    Route::get('/tools/schema-generator', [\App\Http\Controllers\FreeToolsController::class, 'schemaGenerator'])->name('tools.schema-generator');
    Route::post('/tools/schema-generator/generate-description', [\App\Http\Controllers\FreeToolsController::class, 'generateDescription'])->name('tools.schema-generator.description')
        ->middleware('tool.limit:schema_generator');

    // SEO Audit Tool (WebCrawlPro-style single-page)
    Route::get('/tools/seo-audit', [\App\Http\Controllers\Tools\SeoAuditController::class, 'index'])->name('tools.seo-audit');
    Route::post('/tools/seo-audit/analyze', [\App\Http\Controllers\Tools\SeoAuditController::class, 'analyze'])->name('tools.seo-audit.analyze')
        ->middleware('tool.limit:seo_analyzer');

    // WebCrawl Audit (full multi-page crawler)
    Route::get('/tools/crawl-audit', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'index'])->name('tools.crawl-audit');
    Route::post('/tools/crawl-audit/start', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'start'])->name('tools.crawl-audit.start')
        ->middleware('tool.limit:crawl_audit');
    Route::get('/tools/crawl-audit/status/{taskId}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'status'])->name('tools.crawl-audit.status');
    Route::get('/tools/crawl-audit/data/{taskId}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'data'])->name('tools.crawl-audit.data');
    Route::get('/tools/crawl-audit/pages/{taskId}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'pages'])->name('tools.crawl-audit.pages');
    Route::get('/tools/crawl-audit/page/{pageId}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'pageDetail'])->name('tools.crawl-audit.page');
    Route::get('/tools/crawl-audit/load/{scan}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'load'])->name('tools.crawl-audit.load');
    Route::get('/tools/crawl-audit/results/{taskId}', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'showResults'])->name('tools.crawl-audit.results');
    Route::get('/tools/crawl-history', [\App\Http\Controllers\Tools\CrawlAuditController::class, 'history'])->name('tools.crawl-audit.history');

    // Robots.txt Generator
    Route::match(['GET', 'POST'], '/tools/robots-generator', [\App\Http\Controllers\Tools\RobotsController::class, 'index'])->name('tools.robots');

    // Sitemap Crawler
    Route::match(['GET', 'POST'], '/tools/sitemap-crawler', [\App\Http\Controllers\Tools\SitemapCrawlerController::class, 'index'])->name('tools.sitemap-crawler');
    Route::get('/tools/sitemap-crawler/status/{taskId}', [\App\Http\Controllers\Tools\SitemapCrawlerController::class, 'status'])->name('tools.sitemap-crawler.status');
    Route::get('/tools/sitemap-crawler/result/{taskId}', [\App\Http\Controllers\Tools\SitemapCrawlerController::class, 'result'])->name('tools.sitemap-crawler.result');

    // AI Description Generator
    Route::post('/ai/generate-description', [\App\Http\Controllers\SchemaController::class, 'generateDescription'])->name('ai.description')
        ->middleware('tool.limit:schema_generator');

    // Google Search Console Tool
    Route::get('/tools/search-console', [\App\Http\Controllers\SearchConsoleToolController::class, 'index'])->name('tools.search-console');
    Route::post('/tools/search-console/connect', [\App\Http\Controllers\SearchConsoleToolController::class, 'startConnect']);
    Route::post('/tools/search-console/callback', [\App\Http\Controllers\SearchConsoleToolController::class, 'handleCallback']);
    Route::post('/tools/search-console/store', [\App\Http\Controllers\SearchConsoleToolController::class, 'storeProperty']);
    Route::post('/tools/search-console/submit-sitemap', [\App\Http\Controllers\SearchConsoleToolController::class, 'submitSitemap']);
    Route::post('/tools/search-console/disconnect', [\App\Http\Controllers\SearchConsoleToolController::class, 'disconnect']);
});

// Domain entry from landing page (guest & auth)
Route::post('/domain-entry', [ProjectController::class, 'domainEntry'])->name('domain.entry');

// Legal Routes
Route::view('/legal', 'legal.index')->name('legal.index');
Route::view('/privacy-policy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms-of-service', 'legal.terms')->name('legal.terms');
Route::view('/cookie-policy', 'legal.cookies')->name('legal.cookies');

// Public SEO files (served to search engine crawlers)
Route::get('/robots.txt', [\App\Http\Controllers\Seo\RobotsController::class, 'show']);
Route::get('/sitemap.xml', [\App\Http\Controllers\Seo\SitemapController::class, 'index']);

// Pricing & Subscriptions
Route::get('/pricing', [\App\Http\Controllers\SubscriptionController::class, 'pricing'])->name('pricing');
Route::get('/plans/{plan}', [\App\Http\Controllers\SubscriptionController::class, 'planDetail'])->name('plan.detail');
Route::middleware('auth')->group(function () {
    Route::post('/subscription/checkout', [\App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

// PayPal Webhook
Route::post('/webhook/paypal', [\App\Http\Controllers\SubscriptionController::class, 'webhook'])->name('webhook.paypal');

Route::get('/', fn () => view('welcome'))->name('home');
Route::view('/about', 'about')->name('about');
Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');
require __DIR__.'/auth.php';
