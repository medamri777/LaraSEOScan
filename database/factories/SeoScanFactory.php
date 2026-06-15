<?php

namespace Database\Factories;

use App\Models\SeoScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoScanFactory extends Factory
{
    protected $model = SeoScan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => $this->faker->url(),
            'status' => 'pending',
            'has_robots_txt' => false,
            'has_sitemap_xml' => false,
        ];
    }
}
