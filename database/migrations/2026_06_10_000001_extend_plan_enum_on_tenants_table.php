<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend the plan enum to include guru and business tiers.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for enums — change to string instead.
        // On MySQL this is a safe ALTER.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: columns are already stored as TEXT, no-op needed
            // Just verify the column exists (it does from the original migration)
        } else {
            // MySQL / PostgreSQL: alter the enum
            DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('free','pro','guru','business','agency') NOT NULL DEFAULT 'free'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('free','pro','agency') NOT NULL DEFAULT 'free'");
        }
    }
};
