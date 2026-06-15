
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\KeywordController;
use App\Http\Controllers\Api\CompetitorController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\SearchConsoleController;

/*
|--------------------------------------------------------------------------
| API Routes — Seo4ma
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/login',            [AuthController::class, 'login']);
    Route::post('/forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',   [AuthController::class, 'resetPassword']);

    // Email verification — GET link from email, verified server-side → redirect to frontend
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('throttle:6,1')
        ->name('api.verification.verify');

    // Resend verification email — requires auth token
    Route::post('/email/resend', [AuthController::class, 'resendVerification'])
        ->middleware('auth:sanctum');
});

// Public invitation routes (no auth required — token is the auth)
Route::prefix('invitations')->group(function () {
    Route::get('/{token}',        [InvitationController::class, 'preview']);
    Route::post('/{token}/accept', [InvitationController::class, 'accept']);
});

// Google OAuth popup callback (no auth — popup has no Sanctum token)
Route::get('/search-console/callback', function () {
    return response('<html><head><title>Authenticating...</title></head><body style="background:#191B1F;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh"><p>Authenticating... You can close this window.</p><script>if(window.opener){const params=new URLSearchParams(window.location.search);window.opener.postMessage({type:"gsc_auth_callback",code:params.get("code"),state:params.get("state")}, "*");window.close();}</script></body></html>')->header('Content-Type', 'text/html');
});

// Protected routes — require Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Profile
    Route::get('/profile',    [ProfileController::class, 'show']);
    Route::patch('/profile',  [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Workspace (Tenant)
    Route::get('/workspace',         [TenantController::class, 'current']);
    Route::post('/workspace',        [TenantController::class, 'store']);
    Route::patch('/workspace',       [TenantController::class, 'update']);
    Route::post('/workspace/logo',   [TenantController::class, 'uploadLogo']);
    Route::delete('/workspace/logo', [TenantController::class, 'deleteLogo']);

    // Workspace invitations (owner-only: send, list, revoke)
    Route::get('/workspace/invitations',        [InvitationController::class, 'index']);
    Route::post('/workspace/invitations',       [InvitationController::class, 'send']);
    Route::delete('/workspace/invitations/{id}', [InvitationController::class, 'revoke']);

    // Workspace members list
    Route::get('/workspace/members', [TenantController::class, 'members']);

    // Projects
    Route::get('/projects',         [ProjectController::class, 'index']);
    Route::post('/projects',        [ProjectController::class, 'store']);
    Route::get('/projects/{id}',    [ProjectController::class, 'show']);
    Route::patch('/projects/{id}',  [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{id}/search-console', [ProjectController::class, 'searchConsoleStats']);

    // Per-project: Keywords + Competitors
    Route::prefix('projects/{projectId}')->group(function () {

        // Keywords
        Route::get('/keywords',                      [KeywordController::class, 'index']);
        Route::post('/keywords',                     [KeywordController::class, 'store']);
        Route::get('/keywords/summary',              [KeywordController::class, 'projectSummary']);
        Route::post('/keywords/check',               [KeywordController::class, 'checkRankings'])->middleware('tool.limit:keyword_research');
        Route::get('/keywords/batch/{batchId}',      [KeywordController::class, 'batchStatus']);
        Route::get('/keywords/{keywordId}/rankings', [KeywordController::class, 'rankings']);
        Route::delete('/keywords/{keywordId}',       [KeywordController::class, 'destroy']);

        // Competitors
        Route::get('/competitors',                   [CompetitorController::class, 'index']);
        Route::post('/competitors',                  [CompetitorController::class, 'store']);
        Route::delete('/competitors/{competitorId}', [CompetitorController::class, 'destroy']);
        Route::get('/competitors/matrix',            [CompetitorController::class, 'matrix']);
    });

    // SEO Scans
    Route::get('/scans',                   [ScanController::class, 'index']);
    Route::post('/scans',                  [ScanController::class, 'store'])->middleware('tool.limit:seo_scan');
    Route::get('/scans/{uuid}',            [ScanController::class, 'show']);
    Route::get('/scans/{uuid}/status',     [ScanController::class, 'status']);
    Route::delete('/scans/{uuid}',         [ScanController::class, 'destroy']);
    Route::get('/scans/{uuid}/export/pdf', [ScanController::class, 'exportPdf']);
    Route::get('/scans/{uuid}/export/csv', [ScanController::class, 'exportCsv']);

    // SEO Tools Admin
    Route::prefix('seo')->group(function () {
        // Robots.txt rules CRUD
        Route::get('/robots',           [\App\Http\Controllers\Seo\RobotsController::class, 'preview']);
        Route::post('/robots',          [\App\Http\Controllers\Seo\RobotsController::class, 'store']);
        Route::put('/robots/{id}',      [\App\Http\Controllers\Seo\RobotsController::class, 'update']);
        Route::delete('/robots/{id}',   [\App\Http\Controllers\Seo\RobotsController::class, 'destroy']);
        Route::get('/robots/export',    [\App\Http\Controllers\Seo\RobotsController::class, 'export']);

        // Sitemap URLs CRUD
        Route::get('/sitemap',          [\App\Http\Controllers\Seo\SitemapUrlController::class, 'index']);
        Route::post('/sitemap',         [\App\Http\Controllers\Seo\SitemapUrlController::class, 'store']);
        Route::put('/sitemap/{id}',     [\App\Http\Controllers\Seo\SitemapUrlController::class, 'update']);
        Route::delete('/sitemap/{id}',  [\App\Http\Controllers\Seo\SitemapUrlController::class, 'destroy']);

        // Sitemap generation
        Route::post('/sitemap/generate',[\App\Http\Controllers\Seo\SitemapController::class, 'regenerate']);
        Route::get('/sitemap/status',   [\App\Http\Controllers\Seo\SitemapController::class, 'status']);
    });

    // Google Search Console
    Route::prefix('search-console')->group(function () {
        Route::post('/connect',                       [SearchConsoleController::class, 'connect']);
        Route::post('/callback',                      [SearchConsoleController::class, 'callback']);

        // GET callback moved to public routes (popup has no Sanctum token)

        Route::post('/store',                         [SearchConsoleController::class, 'storeConnection']);
        Route::get('/status/{projectId}',             [SearchConsoleController::class, 'status']);
        Route::delete('/disconnect/{connectionId}',   [SearchConsoleController::class, 'disconnect']);

        Route::get('/{connectionId}/queries',         [SearchConsoleController::class, 'topQueries']);
        Route::get('/{connectionId}/pages',           [SearchConsoleController::class, 'topPages']);
        Route::get('/{connectionId}/performance',     [SearchConsoleController::class, 'dailyPerformance']);
        Route::post('/{connectionId}/analytics',      [SearchConsoleController::class, 'searchAnalytics']);
        Route::post('/{connectionId}/inspect',        [SearchConsoleController::class, 'inspectUrl']);
        Route::get('/{connectionId}/sitemaps',        [SearchConsoleController::class, 'sitemaps']);
        Route::post('/{connectionId}/sitemaps',       [SearchConsoleController::class, 'submitSitemap']);
        Route::delete('/{connectionId}/sitemaps',     [SearchConsoleController::class, 'deleteSitemap']);
    });
});
