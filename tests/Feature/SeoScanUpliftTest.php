<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SeoScan;
use App\Models\User;
use App\Services\SeoScannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class SeoScanUpliftTest extends TestCase
{
    use RefreshDatabase;

    public function test_uplift_agency_scan()
    {
        $url = 'https://upliftazency.in/';

        // Create user
        $user = User::first() ?? User::factory()->create();

        // Cleanup previous
        SeoScan::where('url', $url)->delete();

        $scan = SeoScan::create([
            'user_id' => $user->id,
            'url' => $url,
            'status' => 'PENDING',
        ]);

        /** @var SeoScannerService $scanner */
        $scanner = app(SeoScannerService::class);

        Log::info("TEST: Starting scan for $url");
        
        try {
            $scanner->scan($scan);
        } catch (\Exception $e) {
            $this->fail("Scan threw exception: " . $e->getMessage());
        }

        $scan->refresh();
        $this->assertEquals('COMPLETED', $scan->status, "Scan status should be COMPLETED");

        $this->assertGreaterThan(0, $scan->pages()->count(), "Should have crawled at least 1 page");
        
        $page = $scan->pages()->first();
        $this->assertNotEmpty($page->title, "Page title should be captured");
        $this->assertNotNull($page->headings, "Headings should be captured");

        // Check if issues were generated
        $issueCount = $page->issues()->count();
        Log::info("TEST: Generated $issueCount issues for first page.");
        
        $this->assertGreaterThan(0, $page->links()->count(), "Should have extracted links");
        
        // Check for specific columns
        // $this->assertNotNull($page->keyword_density, "keyword_density format check");
    }
}
