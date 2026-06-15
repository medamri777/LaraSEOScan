<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_keywords', function (Blueprint $table) {
            $table->foreignId('seo_scan_id')->nullable()->after('seo_page_id');
            $table->foreignId('seo_page_id')->nullable()->change();
            
            $table->index(['seo_scan_id', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::table('seo_keywords', function (Blueprint $table) {
            $table->dropColumn('seo_scan_id');
            $table->foreignId('seo_page_id')->change();
        });
    }
};
