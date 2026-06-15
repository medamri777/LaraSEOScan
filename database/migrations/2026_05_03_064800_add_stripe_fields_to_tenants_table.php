<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_customer_id', 64)->nullable()->after('plan');
            $table->string('stripe_subscription_id', 64)->nullable()->after('stripe_customer_id');
            $table->string('stripe_subscription_status', 32)->nullable()->after('stripe_subscription_id');

            $table->index('stripe_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropColumn(['stripe_customer_id', 'stripe_subscription_id', 'stripe_subscription_status']);
        });
    }
};
