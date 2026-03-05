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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('contract_status', 32)->default('draft')->after('internal_notes'); // draft, generated, signed
            $table->string('contract_generated_path')->nullable()->after('contract_status');
            $table->string('contract_signed_path')->nullable()->after('contract_generated_path');
            $table->timestamp('contract_signed_at')->nullable()->after('contract_signed_path');
            $table->text('contract_signed_notes')->nullable()->after('contract_signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'contract_status',
                'contract_generated_path',
                'contract_signed_path',
                'contract_signed_at',
                'contract_signed_notes',
            ]);
        });
    }
};
