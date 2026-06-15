<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_page_id')->constrained()->onDelete('cascade');
            $table->string('rule_key')->index();      // e.g. meta.missing_title
            $table->string('severity')->default('info'); // error, warning, info
            $table->text('message');                 // description of issue
            $table->string('selector')->nullable();  // DOM selector (optional)
            $table->json('context')->nullable();     // extra info
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_issues');
    }
};
