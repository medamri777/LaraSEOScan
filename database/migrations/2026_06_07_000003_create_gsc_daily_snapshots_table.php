<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gsc_daily_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gsc_connection_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->decimal('avg_position', 8, 2)->default(0);
            $table->json('top_queries')->nullable();
            $table->timestamps();

            $table->unique(['gsc_connection_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gsc_daily_snapshots');
    }
};
