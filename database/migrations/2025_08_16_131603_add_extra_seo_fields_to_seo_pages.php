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
        Schema::table('seo_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_pages', 'status_code')) {
                $table->integer('status_code')->nullable()->after('url');
            }
            if (!Schema::hasColumn('seo_pages', 'word_count')) {
                $table->integer('word_count')->nullable()->after('robots');
            }
            if (!Schema::hasColumn('seo_pages', 'shingle_signature')) {
                $table->text('shingle_signature')->nullable()->after('word_count');
            }
            if (!Schema::hasColumn('seo_pages', 'structured_data')) {
                $table->json('structured_data')->nullable()->after('shingle_signature');
            }
            if (!Schema::hasColumn('seo_pages', 'fetched_at')) {
                $table->timestamp('fetched_at')->nullable()->after('structured_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $cols = ['status_code', 'word_count', 'shingle_signature', 'structured_data', 'fetched_at'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('seo_pages', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
