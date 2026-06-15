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
            $table->uuid('uuid')->nullable()->after('id');
        });
        
        // Populate existing rows
        $scans = \DB::table('seo_scans')->get();
        foreach ($scans as $scan) {
            \DB::table('seo_scans')
                ->where('id', $scan->id)
                ->update(['uuid' => \Illuminate\Support\Str::uuid()]);
        }
        
        // Make it unique and not null
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
