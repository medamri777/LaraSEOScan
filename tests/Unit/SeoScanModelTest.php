<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\SeoScan;
use App\Models\SeoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoScanModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_scan_has_many_pages()
    {
        $user = User::factory()->create();
        $scan = SeoScan::factory()->for($user)->create();

        SeoPage::factory()->for($scan)->count(2)->create();

        $this->assertCount(2, $scan->pages);
    }
}
