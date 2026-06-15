<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_urls', function (Blueprint $table) {
            $table->id();
            $table->string('loc', 500);
            $table->string('changefreq', 20)->default('weekly');
            $table->decimal('priority', 3, 1)->default(0.5);
            $table->timestamp('lastmod')->nullable();
            $table->string('type', 50)->default('manual');
            $table->boolean('is_active')->default(true);
            $table->string('image_url', 500)->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_urls');
    }
};
