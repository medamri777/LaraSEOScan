<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('slug');
            $table->string('primary_color', 7)->nullable()->default('#3B82F6')->after('logo_path');
            $table->string('agency_name')->nullable()->after('primary_color');
            $table->string('agency_website')->nullable()->after('agency_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'primary_color', 'agency_name', 'agency_website']);
        });
    }
};
