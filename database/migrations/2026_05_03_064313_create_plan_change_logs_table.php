<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_plan', 20)->nullable();
            $table->string('new_plan', 20);
            $table->integer('old_scan_limit')->nullable();
            $table->integer('new_scan_limit')->nullable();
            $table->string('source', 30)->default('admin'); // admin | stripe | system
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_change_logs');
    }
};
