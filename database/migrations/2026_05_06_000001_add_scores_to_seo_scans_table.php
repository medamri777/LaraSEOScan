<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->unsignedTinyInteger('score_total')->nullable()->after('status');
            $table->unsignedTinyInteger('score_technical')->nullable()->after('score_total');
            $table->unsignedTinyInteger('score_on_page')->nullable()->after('score_technical');
            $table->unsignedTinyInteger('score_local')->nullable()->after('score_on_page');
            $table->unsignedTinyInteger('score_mobile')->nullable()->after('score_local');
            $table->unsignedTinyInteger('score_speed')->nullable()->after('score_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropColumn([
                'score_total',
                'score_technical',
                'score_on_page',
                'score_local',
                'score_mobile',
                'score_speed',
            ]);
        });
    }
};
