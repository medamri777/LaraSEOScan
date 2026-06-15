<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_links', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_links', 'redirect_chain')) {
                $table->json('redirect_chain')->nullable()->after('status_code');
            }
        });

        Schema::table('seo_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_pages', 'keyword_density')) {
                $table->json('keyword_density')->nullable()->after('headings');
            }
            if (!Schema::hasColumn('seo_pages', 'image_total_size')) {
                $table->bigInteger('image_total_size')->nullable()->after('keyword_density');
            }
            if (!Schema::hasColumn('seo_pages', 'image_unoptimized_count')) {
                $table->integer('image_unoptimized_count')->nullable()->after('image_total_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_links', function (Blueprint $table) {
            if (Schema::hasColumn('seo_links', 'redirect_chain')) {
                $table->dropColumn('redirect_chain');
            }
        });

        Schema::table('seo_pages', function (Blueprint $table) {
            $table->dropColumn(['keyword_density', 'image_total_size', 'image_unoptimized_count']);
        });
    }
};
