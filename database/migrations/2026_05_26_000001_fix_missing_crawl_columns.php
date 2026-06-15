<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix seo_scans - add missing columns
        Schema::table('seo_scans', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_scans', 'crawl_config')) {
                $table->json('crawl_config')->nullable()->after('crawled_metrics');
            }
            if (!Schema::hasColumn('seo_scans', 'audit_metrics')) {
                $table->json('audit_metrics')->nullable()->after('crawl_config');
            }
        });

        // Fix seo_pages - add missing columns
        Schema::table('seo_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_pages', 'twitter_tags')) {
                $table->json('twitter_tags')->nullable()->after('og_tags');
            }
            if (!Schema::hasColumn('seo_pages', 'hreflangs')) {
                $table->json('hreflangs')->nullable()->after('twitter_tags');
            }
            if (!Schema::hasColumn('seo_pages', 'content_type')) {
                $table->string('content_type', 200)->nullable()->after('hreflangs');
            }
            if (!Schema::hasColumn('seo_pages', 'server')) {
                $table->string('server', 200)->nullable()->after('content_type');
            }
            if (!Schema::hasColumn('seo_pages', 'x_powered_by')) {
                $table->string('x_powered_by', 200)->nullable()->after('server');
            }
            if (!Schema::hasColumn('seo_pages', 'content_length')) {
                $table->integer('content_length')->nullable()->after('x_powered_by');
            }
            if (!Schema::hasColumn('seo_pages', 'lang')) {
                $table->string('lang', 20)->nullable()->after('content_length');
            }
            if (!Schema::hasColumn('seo_pages', 'viewport')) {
                $table->string('viewport', 500)->nullable()->after('lang');
            }
            if (!Schema::hasColumn('seo_pages', 'favicon')) {
                $table->string('favicon', 500)->nullable()->after('viewport');
            }
            if (!Schema::hasColumn('seo_pages', 'author')) {
                $table->string('author', 200)->nullable()->after('favicon');
            }
            if (!Schema::hasColumn('seo_pages', 'generator')) {
                $table->string('generator', 200)->nullable()->after('author');
            }
            if (!Schema::hasColumn('seo_pages', 'x_robots_tag')) {
                $table->string('x_robots_tag', 500)->nullable()->after('generator');
            }
            if (!Schema::hasColumn('seo_pages', 'discovery_source')) {
                $table->string('discovery_source', 50)->nullable()->after('x_robots_tag');
            }
            if (!Schema::hasColumn('seo_pages', 'response_time_ms')) {
                $table->integer('response_time_ms')->nullable()->after('discovery_source');
            }
            if (!Schema::hasColumn('seo_pages', 'depth')) {
                $table->integer('depth')->default(0)->after('response_time_ms');
            }
        });

        // Fix seo_links - add missing columns
        Schema::table('seo_links', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_links', 'is_nofollow')) {
                $table->boolean('is_nofollow')->default(false)->after('rel');
            }
        });

        // Fix seo_images - add missing columns
        Schema::table('seo_images', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_images', 'has_alt')) {
                $table->boolean('has_alt')->default(false)->after('alt');
            }
            if (!Schema::hasColumn('seo_images', 'is_empty_alt')) {
                $table->boolean('is_empty_alt')->default(false)->after('has_alt');
            }
            if (!Schema::hasColumn('seo_images', 'loading')) {
                $table->string('loading', 20)->nullable()->after('is_empty_alt');
            }
            if (!Schema::hasColumn('seo_images', 'width')) {
                $table->string('width', 20)->nullable()->after('loading');
            }
            if (!Schema::hasColumn('seo_images', 'height')) {
                $table->string('height', 20)->nullable()->after('width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            if (Schema::hasColumn('seo_scans', 'crawl_config')) $table->dropColumn('crawl_config');
            if (Schema::hasColumn('seo_scans', 'audit_metrics')) $table->dropColumn('audit_metrics');
        });

        Schema::table('seo_pages', function (Blueprint $table) {
            foreach (['twitter_tags','hreflangs','content_type','server','x_powered_by',
                'content_length','lang','viewport','favicon','author','generator',
                'x_robots_tag','discovery_source','response_time_ms','depth'] as $col) {
                if (Schema::hasColumn('seo_pages', $col)) $table->dropColumn($col);
            }
        });

        Schema::table('seo_links', function (Blueprint $table) {
            if (Schema::hasColumn('seo_links', 'is_nofollow')) $table->dropColumn('is_nofollow');
        });

        Schema::table('seo_images', function (Blueprint $table) {
            foreach (['has_alt','is_empty_alt','loading','width','height'] as $col) {
                if (Schema::hasColumn('seo_images', $col)) $table->dropColumn($col);
            }
        });
    }
};
