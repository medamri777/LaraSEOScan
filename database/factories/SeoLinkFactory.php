<?php

namespace Database\Factories;

use App\Models\SeoLink;
use App\Models\SeoPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoLinkFactory extends Factory
{
    protected $model = SeoLink::class;

    public function definition(): array
    {
        return [
            'seo_page_id' => SeoPage::factory(),
            'href' => $this->faker->url(),
            'status_code' => 200,
            'is_internal' => true,
        ];
    }
}
