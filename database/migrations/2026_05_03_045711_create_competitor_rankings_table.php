<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competitor_id')
                  ->constrained('project_competitors')
                  ->cascadeOnDelete();
            $table->date('checked_at');
            $table->unsignedSmallInteger('rank')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->unique(['keyword_id', 'competitor_id', 'checked_at']);
            $table->index(['keyword_id', 'checked_at']);
            $table->index(['competitor_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_rankings');
    }
};
