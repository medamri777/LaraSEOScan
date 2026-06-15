<?php

namespace Database\Factories;

use App\Models\SeoPage;
use App\Models\SeoScan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoPageFactory extends Factory
{
    protected $model = SeoPage::class;

    public function definition(): array
    {
        return [
            'seo_scan_id' => SeoScan::factory(),
            'url' => $this->faker->url(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(6),
            'canonical' => $this->faker->url(),
            'headings' => ['h1' => $this->faker->words(3, true)],
        ];
    }
}
