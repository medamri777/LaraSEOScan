<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->json('og_tags')->nullable()->after('structured_data');
            $table->json('twitter_tags')->nullable()->after('og_tags');
            $table->json('hreflangs')->nullable()->after('twitter_tags');
            $table->string('content_type', 200)->nullable()->after('hreflangs');
            $table->string('server', 200)->nullable()->after('content_type');
            $table->string('x_powered_by', 200)->nullable()->after('server');
            $table->integer('content_length')->nullable()->after('x_powered_by');
            $table->string('lang', 20)->nullable()->after('content_length');
            $table->string('viewport', 500)->nullable()->after('lang');
            $table->string('favicon', 500)->nullable()->after('viewport');
            $table->string('author', 200)->nullable()->after('favicon');
            $table->string('generator', 200)->nullable()->after('author');
            $table->string('x_robots_tag', 500)->nullable()->after('generator');
            $table->string('discovery_source', 50)->nullable()->after('x_robots_tag');
            $table->integer('response_time_ms')->nullable()->after('discovery_source');
            $table->integer('depth')->default(0)->after('response_time_ms');
        });

        Schema::table('seo_links', function (Blueprint $table) {
            $table->string('anchor_text', 500)->nullable()->after('href');
            $table->string('rel', 200)->nullable()->after('is_internal');
            $table->boolean('is_nofollow')->default(false)->after('rel');
        });

        Schema::table('seo_images', function (Blueprint $table) {
            $table->boolean('has_alt')->default(false)->after('alt');
            $table->boolean('is_empty_alt')->default(false)->after('has_alt');
            $table->string('loading', 20)->nullable()->after('is_empty_alt');
            $table->string('width', 20)->nullable()->after('loading');
            $table->string('height', 20)->nullable()->after('width');
        });

        Schema::table('seo_scans', function (Blueprint $table) {
            $table->json('crawl_config')->nullable()->after('crawled_metrics');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->dropColumn([
                'og_tags', 'twitter_tags', 'hreflangs',
                'content_type', 'server', 'x_powered_by', 'content_length',
                'lang', 'viewport', 'favicon', 'author', 'generator',
                'x_robots_tag', 'discovery_source', 'response_time_ms', 'depth',
            ]);
        });

        Schema::table('seo_links', function (Blueprint $table) {
            $table->dropColumn(['anchor_text', 'rel', 'is_nofollow']);
        });

        Schema::table('seo_images', function (Blueprint $table) {
            $table->dropColumn(['has_alt', 'is_empty_alt', 'loading', 'width', 'height']);
        });

        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropColumn(['crawl_config']);
        });
    }
};
