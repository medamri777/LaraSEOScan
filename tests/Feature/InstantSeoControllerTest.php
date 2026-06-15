<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstantSeoControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_unauthenticated_users_to_login()
    {
        $response = $this->get('/seo-analyzer');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_allows_authenticated_users_to_access_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/seo-analyzer');

        $response->assertStatus(200);
        $response->assertSee('Detailed SEO Extension');
    }

    /** @test */
    public function it_validates_required_and_valid_url()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/seo-analyzer', [
            'url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['url']);
    }

    /** @test */
    public function it_performs_seo_analysis_successfully()
    {
        $user = User::factory()->create();

        // Mock the HTTP request to return a sample HTML page
        Http::fake([
            'https://example.com' => Http::response(
                '<!DOCTYPE html>
                <html>
                <head>
                    <title>Test Page Title</title>
                    <meta name="description" content="This is a test description tag.">
                    <link rel="canonical" href="https://example.com">
                    <meta name="robots" content="index, follow">
                    <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "WebSite",
                        "name": "Test Site"
                    }
                    </script>
                </head>
                <body>
                    <h1>Main Heading H1</h1>
                    <h2>Secondary Heading H2</h2>
                    <img src="/logo.png" alt="Company Logo">
                    <img src="/banner.png"> <!-- Missing Alt -->
                    <a href="https://example.com/about">Internal Link</a>
                    <a href="https://google.com" rel="nofollow">External Link</a>
                </body>
                </html>',
                200
            )
        ]);

        $response = $this->actingAs($user)->post('/seo-analyzer', [
            'url' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Analyse terminée');
        $response->assertSee('Score SEO');
        $response->assertSee('Rapport de Diagnostic On-Page');
        $response->assertSee('Test Page Title');
        $response->assertSee('This is a test description tag.');
        $response->assertSee('Main Heading H1');
        $response->assertSee('Secondary Heading H2');
        $response->assertSee('Company Logo');
        $response->assertSee('Alt Manquant');
        $response->assertSee('Internal Link');
        $response->assertSee('External Link');
        $response->assertSee('WebSite'); // Schema type
    }
}
