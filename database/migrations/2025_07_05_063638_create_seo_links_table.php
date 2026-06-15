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
        Schema::create('seo_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_page_id')->constrained()->onDelete('cascade');
            $table->string('href');
            $table->string('status_code')->nullable(); // e.g. 200, 404
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_links');
    }
};
