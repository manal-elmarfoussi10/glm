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
        if (Schema::hasTable('vehicles') && ! Schema::hasColumn('vehicles', 'status')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('status', 32)->default('available')->after('branch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'status')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
