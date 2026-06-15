<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->integer('location_code')->default(2504);   // 2504=Morocco, 2012=Algeria, 2788=Tunisia
            $table->string('language_code', 10)->default('fr'); // fr, ar, en
            $table->enum('device', ['desktop', 'mobile'])->default('desktop');
            $table->boolean('is_active')->default(true);
            $table->date('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'keyword', 'location_code', 'language_code', 'device'], 'keywords_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
