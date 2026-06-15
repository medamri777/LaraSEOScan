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
        Schema::table('tenants', function (Blueprint $table) {
            // Drop enum constraint if it exists (for SQLite) or just change column on MySQL
            // But since this is a new feature, we'll just add the paypal fields.
            // Note: If you need to change the 'plan' enum later, consider a separate migration.
            
            $table->string('paypal_customer_id')->nullable()->after('stripe_subscription_status');
            $table->string('paypal_subscription_id')->nullable()->after('paypal_customer_id');
            $table->string('paypal_subscription_status')->nullable()->after('paypal_subscription_id');
            $table->string('paypal_plan_id')->nullable()->after('paypal_subscription_status');
            $table->string('billing_cycle')->nullable()->after('paypal_plan_id'); // 'monthly' or 'annual'
            $table->timestamp('trial_ends_at')->nullable()->after('billing_cycle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_customer_id',
                'paypal_subscription_id',
                'paypal_subscription_status',
                'paypal_plan_id',
                'billing_cycle',
                'trial_ends_at',
            ]);
        });
    }
};
