<?php

namespace Tests\Feature;

use App\Jobs\ProcessSeoScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessSeoScanJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_process_seo_scan_job()
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post('/scan', ['url' => 'https://example.com']);

        Queue::assertPushed(ProcessSeoScan::class);
    }
}
