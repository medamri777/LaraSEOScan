<?php

namespace Tests\Feature;

use App\Models\SitemapUrl;
use App\Models\User;
use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoSitemapTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_sitemap_from_db_urls()
    {
        SitemapUrl::create([
            'loc' => 'https://example.com/page1',
            'changefreq' => 'weekly',
            'priority' => 0.8,
            'type' => 'manual',
        ]);
        SitemapUrl::create([
            'loc' => 'https://example.com/page2',
            'changefreq' => 'daily',
            'priority' => 1.0,
            'type' => 'manual',
        ]);

        $service = app(SitemapService::class);
        $stats = $service->generate();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(2, $stats['manual']);
        $this->assertFileExists(public_path('sitemap.xml'));

        $xml = simplexml_load_file(public_path('sitemap.xml'));
        $this->assertNotFalse($xml);
        $this->assertCount(2, $xml->url);
    }

    /** @test */
    public function it_caches_sitemap_metadata()
    {
        SitemapUrl::create([
            'loc' => 'https://example.com/test',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        $service = app(SitemapService::class);
        $service->generate();

        $this->assertNotNull(Cache::get('sitemap_last_generated'));
        $this->assertEquals(1, Cache::get('sitemap_url_count'));
    }

    /** @test */
    public function it_serves_sitemap_via_web_route()
    {
        SitemapUrl::create([
            'loc' => 'https://example.com/test',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        $service = app(SitemapService::class);
        $service->generate();

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $this->assertFileExists(public_path('sitemap.xml'));
        $this->assertStringContainsString('example.com/test', file_get_contents(public_path('sitemap.xml')));
    }

    /** @test */
    public function it_creates_sitemap_url_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/seo/sitemap', [
            'loc' => 'https://example.com/new-page',
            'changefreq' => 'monthly',
            'priority' => 0.6,
            'type' => 'manual',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['loc' => 'https://example.com/new-page']);
        $this->assertDatabaseHas('sitemap_urls', ['loc' => 'https://example.com/new-page']);
    }

    /** @test */
    public function it_updates_sitemap_url_via_api()
    {
        $user = User::factory()->create();
        $url = SitemapUrl::create([
            'loc' => 'https://example.com/old',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        $response = $this->actingAs($user)->putJson("/api/seo/sitemap/{$url->id}", [
            'loc' => 'https://example.com/updated',
            'priority' => 0.9,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sitemap_urls', ['loc' => 'https://example.com/updated']);
        $this->assertDatabaseMissing('sitemap_urls', ['loc' => 'https://example.com/old']);
    }

    /** @test */
    public function it_deletes_sitemap_url_via_api()
    {
        $user = User::factory()->create();
        $url = SitemapUrl::create([
            'loc' => 'https://example.com/delete-me',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/seo/sitemap/{$url->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('sitemap_urls', ['id' => $url->id]);
    }

    /** @test */
    public function it_regenerates_sitemap_via_api()
    {
        $user = User::factory()->create();
        SitemapUrl::create([
            'loc' => 'https://example.com/api-test',
            'changefreq' => 'daily',
            'priority' => 1.0,
            'type' => 'manual',
        ]);

        $response = $this->actingAs($user)->postJson('/api/seo/sitemap/generate', [
            'ping' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stats.total', 1);
    }

    /** @test */
    public function it_returns_sitemap_status_via_api()
    {
        $user = User::factory()->create();
        SitemapUrl::create([
            'loc' => 'https://example.com/status-test',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        app(SitemapService::class)->generate();

        $response = $this->actingAs($user)->getJson('/api/seo/sitemap/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'last_generated',
            'url_count',
            'file_size',
            'stats',
        ]);
    }

    /** @test */
    public function it_generates_sitemap_via_artisan_command()
    {
        SitemapUrl::create([
            'loc' => 'https://example.com/command-test',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        $this->artisan('sitemap:generate')
            ->expectsOutput('Sitemap generated successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(public_path('sitemap.xml'));
    }

    /** @test */
    public function it_shows_seo_status_via_artisan_command()
    {
        SitemapUrl::create([
            'loc' => 'https://example.com/status',
            'changefreq' => 'weekly',
            'priority' => 0.5,
            'type' => 'manual',
        ]);

        app(SitemapService::class)->generate();

        $this->artisan('seo:status')
            ->expectsOutputToContain('SEO System Status')
            ->assertExitCode(0);
    }
}
