<?php

namespace Tests\Feature;

use App\Models\RobotRule;
use App\Models\SitemapUrl;
use App\Models\User;
use App\Services\RobotsService;
use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoRobotsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_builds_robots_content_from_db_rules()
    {
        RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'disallow',
            'path' => '/admin/',
        ]);
        RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'disallow',
            'path' => '/private/',
            'crawl_delay' => 10,
        ]);
        RobotRule::create([
            'user_agent' => 'GPTBot',
            'rule_type' => 'disallow',
            'path' => '/',
        ]);

        $service = app(RobotsService::class);
        $content = $service->buildRobotsContent();

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /private/', $content);
        $this->assertStringContainsString('Crawl-delay: 10', $content);
        $this->assertStringContainsString('User-agent: GPTBot', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    /** @test */
    public function it_validates_rule_paths()
    {
        $service = app(RobotsService::class);

        $this->assertTrue($service->validateRule('/admin/'));
        $this->assertTrue($service->validateRule('/'));
        $this->assertFalse($service->validateRule('admin'));
        $this->assertFalse($service->validateRule(''));
    }

    /** @test */
    public function it_caches_robots_content()
    {
        RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'allow',
            'path' => '/',
        ]);

        $service = app(RobotsService::class);
        $service->cacheRobots();

        $this->assertTrue(Cache::has('robots_txt'));
        $cached = Cache::get('robots_txt');
        $this->assertStringContainsString('Allow: /', $cached);
    }

    /** @test */
    public function it_clears_robots_cache()
    {
        Cache::put('robots_txt', 'test', 3600);
        $service = app(RobotsService::class);
        $service->clearCache();

        $this->assertFalse(Cache::has('robots_txt'));
    }

    /** @test */
    public function it_serves_robots_txt_via_web_route()
    {
        RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'disallow',
            'path' => '/admin/',
        ]);

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertSee('Disallow: /admin/');
        $response->assertSee('User-agent: *');
    }

    /** @test */
    public function it_creates_robot_rule_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/seo/robots', [
            'user_agent' => 'Googlebot',
            'rule_type' => 'allow',
            'path' => '/public/',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['user_agent' => 'Googlebot']);
        $this->assertDatabaseHas('robot_rules', ['path' => '/public/']);
    }

    /** @test */
    public function it_rejects_invalid_path_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/seo/robots', [
            'user_agent' => 'Googlebot',
            'rule_type' => 'disallow',
            'path' => 'invalid-path',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_updates_robot_rule_via_api()
    {
        $user = User::factory()->create();
        $rule = RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'disallow',
            'path' => '/old/',
        ]);

        $response = $this->actingAs($user)->putJson("/api/seo/robots/{$rule->id}", [
            'path' => '/new/',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('robot_rules', ['path' => '/new/']);
        $this->assertDatabaseMissing('robot_rules', ['path' => '/old/']);
    }

    /** @test */
    public function it_deletes_robot_rule_via_api()
    {
        $user = User::factory()->create();
        $rule = RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'disallow',
            'path' => '/temp/',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/seo/robots/{$rule->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('robot_rules', ['id' => $rule->id]);
    }

    /** @test */
    public function it_rebuilds_robots_via_artisan_command()
    {
        RobotRule::create([
            'user_agent' => '*',
            'rule_type' => 'allow',
            'path' => '/',
        ]);

        $this->artisan('robots:rebuild')
            ->expectsOutput('Robots.txt rebuilt and cached successfully.')
            ->assertExitCode(0);

        $this->assertTrue(Cache::has('robots_txt'));
    }
}
