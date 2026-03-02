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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff'); // super_admin, company_admin, manager, accountant, etc.
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active, suspended, invited, archived
            
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('cin')->nullable();
            $table->json('preferences')->nullable();
            
            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'role', 'company_id', 'branch_id', 'status',
                'phone', 'whatsapp_phone', 'cin', 'preferences', 'last_login_at'
            ]);
        });
    }
};
