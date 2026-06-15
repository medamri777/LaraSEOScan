<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keyword_rankings', function (Blueprint $table) {
            $table->string('data_source', 50)->nullable()->after('serp_features');
        });
    }

    public function down(): void
    {
        Schema::table('keyword_rankings', function (Blueprint $table) {
            $table->dropColumn('data_source');
        });
    }
};
