<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_page_id')->constrained()->onDelete('cascade');
            $table->string('keyword');
            $table->integer('occurrences')->default(0);
            $table->decimal('density', 5, 2)->default(0);
            $table->integer('rake_score')->default(0);
            $table->boolean('in_title')->default(false);
            $table->boolean('in_h1')->default(false);
            $table->boolean('in_meta_description')->default(false);
            $table->boolean('in_headings')->default(false);
            $table->boolean('in_first_paragraph')->default(false);
            $table->boolean('in_image_alt')->default(false);
            $table->integer('placement_score')->default(0);
            $table->string('type')->default('extracted');
            $table->timestamps();

            $table->index(['seo_page_id', 'keyword']);
            $table->index(['seo_page_id', 'density']);
            $table->index(['seo_page_id', 'placement_score']);
        });

        Schema::table('seo_pages', function (Blueprint $table) {
            $table->json('keyword_analysis')->nullable()->after('keyword_density');
            $table->string('target_keyword')->nullable()->after('keyword_analysis');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->dropColumn(['keyword_analysis', 'target_keyword']);
        });

        Schema::dropIfExists('seo_keywords');
    }
};
