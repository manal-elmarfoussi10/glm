<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('subscription_status')->constrained('plans')->nullOnDelete();
            $table->timestamp('subscription_started_at')->nullable()->after('plan_id');
            $table->timestamp('next_billing_date')->nullable()->after('subscription_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['subscription_started_at', 'next_billing_date']);
        });
    }
};
