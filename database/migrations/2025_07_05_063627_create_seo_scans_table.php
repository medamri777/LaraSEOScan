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
        Schema::create('seo_scans', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, FAILED
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_scans');
    }
};
