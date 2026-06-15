<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->boolean('has_robots_txt')->default(false);
            $table->boolean('has_sitemap_xml')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropColumn(['has_robots_txt', 'has_sitemap_xml']);
        });
    }
};
