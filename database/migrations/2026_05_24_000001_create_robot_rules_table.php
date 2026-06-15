<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('robot_rules', function (Blueprint $table) {
            $table->id();
            $table->string('user_agent', 100)->default('*');
            $table->enum('rule_type', ['allow', 'disallow'])->default('disallow');
            $table->string('path', 500);
            $table->unsignedSmallInteger('crawl_delay')->nullable();
            $table->string('sitemap_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robot_rules');
    }
};
