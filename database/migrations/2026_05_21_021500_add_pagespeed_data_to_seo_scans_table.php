<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->integer('pagespeed_performance')->nullable();
            $table->integer('pagespeed_seo')->nullable();
            $table->integer('pagespeed_accessibility')->nullable();
            $table->integer('pagespeed_best_practices')->nullable();
            $table->json('core_web_vitals')->nullable();
            $table->json('pagespeed_opportunities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropColumn([
                'pagespeed_performance',
                'pagespeed_seo',
                'pagespeed_accessibility',
                'pagespeed_best_practices',
                'core_web_vitals',
                'pagespeed_opportunities',
            ]);
        });
    }
};
