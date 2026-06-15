<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->date('checked_at');
            $table->unsignedSmallInteger('rank')->nullable();          // null = not in top 100
            $table->unsignedSmallInteger('previous_rank')->nullable(); // for trend arrows
            $table->string('url', 2048)->nullable();                   // ranking URL
            $table->string('domain')->nullable();                      // extracted domain
            $table->string('title')->nullable();                       // page title
            $table->unsignedInteger('search_volume')->nullable();      // monthly search volume
            $table->decimal('cpc', 8, 2)->nullable();                  // cost-per-click USD
            $table->unsignedTinyInteger('competition')->nullable();    // 0-100
            $table->json('serp_features')->nullable();                 // featured snippet, PAA, etc.
            $table->timestamps();

            $table->unique(['keyword_id', 'checked_at']);
            $table->index(['keyword_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_rankings');
    }
};
