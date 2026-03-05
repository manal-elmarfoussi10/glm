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
        Schema::table('upgrade_requests', function (Blueprint $table) {
            $table->text('message')->nullable()->after('requested_by');
            $table->text('internal_notes')->nullable()->after('notes');
            $table->foreignId('assigned_to')->nullable()->after('internal_notes')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upgrade_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['message', 'internal_notes', 'assigned_to']);
        });
    }
};
