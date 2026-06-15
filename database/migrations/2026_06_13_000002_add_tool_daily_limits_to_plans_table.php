<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('seo_analyzer_per_day')->nullable()->after('ai_credits_limit');
            $table->integer('crawl_audit_per_day')->nullable()->after('seo_analyzer_per_day');
            $table->integer('sitemap_crawler_per_day')->nullable()->after('crawl_audit_per_day');
            $table->integer('keyword_research_per_day')->nullable()->after('sitemap_crawler_per_day');
            $table->integer('schema_generator_per_day')->nullable()->after('keyword_research_per_day');
            $table->integer('authority_checker_per_day')->nullable()->after('schema_generator_per_day');
            $table->integer('backlink_checker_per_day')->nullable()->after('authority_checker_per_day');
            $table->integer('organic_research_per_day')->nullable()->after('backlink_checker_per_day');
            $table->integer('keyword_magic_per_day')->nullable()->after('organic_research_per_day');
            $table->integer('serp_simulator_per_day')->nullable()->after('keyword_magic_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'seo_analyzer_per_day',
                'crawl_audit_per_day',
                'sitemap_crawler_per_day',
                'keyword_research_per_day',
                'schema_generator_per_day',
                'authority_checker_per_day',
                'backlink_checker_per_day',
                'organic_research_per_day',
                'keyword_magic_per_day',
                'serp_simulator_per_day',
            ]);
        });
    }
};
