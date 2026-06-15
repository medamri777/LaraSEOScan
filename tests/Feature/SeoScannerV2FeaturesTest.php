<?php

namespace Tests\Feature;

use App\Models\SeoScan;
use App\Models\User;
use App\Rules\ValidPublicUrl;
use App\Seo\Rules\RedirectChainRule;
use App\Seo\Rules\RobotsTxtValidationRule;
use App\Services\Seo\PageSpeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoScannerV2FeaturesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_public_urls_and_blocks_ssrf()
    {
        $rule = new ValidPublicUrl();

        // Private/reserved IPs should fail
        $rule->validate('url', 'http://127.0.0.1', function($msg) {
            $this->assertStringContainsString('Scanning internal, private, or reserved network addresses is not allowed', $msg);
        });

        $rule->validate('url', 'http://192.168.1.1', function($msg) {
            $this->assertStringContainsString('Scanning internal, private, or reserved network addresses is not allowed', $msg);
        });

        // Valid public URL should not trigger fail
        $failed = false;
        $rule->validate('url', 'https://google.com', function($msg) use (&$failed) {
            $failed = true;
        });
        $this->assertFalse($failed);
    }

    /** @test */
    public function it_correctly_uses_pagespeed_mock_fallback_when_api_key_is_missing()
    {
        config(['services.google_pagespeed.api_key' => null]);

        $service = new PageSpeedService();
        $data = $service->analyze('https://example.com');

        $this->assertNotNull($data);
        $this->assertArrayHasKey('scores', $data);
        $this->assertArrayHasKey('core_vitals', $data);
        $this->assertArrayHasKey('opportunities', $data);

        $scores = $data['scores'];
        $this->assertArrayHasKey('performance', $scores);
        $this->assertArrayHasKey('seo', $scores);
        $this->assertArrayHasKey('accessibility', $scores);
        $this->assertArrayHasKey('best_practices', $scores);

        $this->assertGreaterThanOrEqual(65, $scores['performance']);
        $this->assertGreaterThanOrEqual(70, $scores['seo']);
    }

    /** @test */
    public function it_detects_redirect_chains()
    {
        $rule = new RedirectChainRule();
        
        $user = User::factory()->create();
        $scan = SeoScan::create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'status' => 'PENDING',
        ]);
        
        $page = new \App\Models\SeoPage();
        $page->url = 'https://example.com/final';
        $page->seo_scan_id = $scan->id;
        $page->redirect_history = [
            'https://example.com/start',
            'https://example.com/hop1'
        ];

        $dom = new \DOMDocument();
        $dom->loadHTML('<html><body></body></html>');
        $xpath = new \DOMXPath($dom);

        $issues = $rule->check($page, $dom, $xpath);

        $this->assertNotEmpty($issues);
        $this->assertEquals('warning', $issues[0]['severity']);
        $this->assertStringContainsString('Redirect chain detected (2 hops)', $issues[0]['message']);
    }

    /** @test */
    public function it_validates_image_dimensions_and_lazy_loading()
    {
        $rule = new \App\Seo\Rules\ImageOptimizationRule();
        
        $user = User::factory()->create();
        $scan = SeoScan::create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'status' => 'PENDING',
        ]);

        $page = new \App\Models\SeoPage();
        $page->url = 'https://example.com';
        $page->seo_scan_id = $scan->id;

        $dom = new \DOMDocument();
        // First img has loading="lazy" (warn above fold), missing width/height
        // Second img is fine (no lazy above fold, has width/height)
        // Third img has no lazy (warn below fold)
        $dom->loadHTML('<html><body>
            <img src="above-fold-lazy.png" loading="lazy" />
            <img src="above-fold-ok.png" width="100" height="100" />
            <img src="below-fold-no-lazy.png" width="100" height="100" />
        </body></html>');
        $xpath = new \DOMXPath($dom);

        $issues = $rule->check($page, $dom, $xpath);

        $ruleKeys = array_column($issues, 'rule');

        $this->assertContains('image.lazy_loading_above_fold', $ruleKeys);
        $this->assertContains('image.missing_dimensions', $ruleKeys);
        $this->assertContains('image.no_lazy_loading', $ruleKeys);
    }
}
