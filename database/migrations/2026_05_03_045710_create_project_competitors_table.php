<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->timestamps();
            $table->softDeletes();

            // Max 5 competitors per project enforced at app level
            $table->unique(['project_id', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_competitors');
    }
};
