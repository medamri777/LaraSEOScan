<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreScanRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_invalid_urls()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/scan', [
            'url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('url');
    }

    /** @test */
    public function it_accepts_valid_urls()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/scan', [
            'url' => 'https://example.com',
        ]);

        $response->assertRedirect();
    }
}
