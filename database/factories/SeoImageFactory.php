<?php

namespace Database\Factories;

use App\Models\SeoImage;
use App\Models\SeoPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoImageFactory extends Factory
{
    protected $model = SeoImage::class;

    public function definition(): array
    {
        return [
            'seo_page_id' => SeoPage::factory(),
            'src' => $this->faker->imageUrl(),
            'alt' => $this->faker->words(3, true),
        ];
    }
}
