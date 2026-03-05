<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('pickup_branch_id')->nullable()->after('customer_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('return_branch_id')->nullable()->after('pickup_branch_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['pickup_branch_id']);
            $table->dropForeign(['return_branch_id']);
        });
    }
};
