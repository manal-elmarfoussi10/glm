<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('city', 128)->nullable()->after('name');
            $table->string('address', 500)->nullable()->after('city');
            $table->string('phone', 32)->nullable()->after('address');
            $table->foreignId('manager_id')->nullable()->after('phone')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['city', 'address', 'phone', 'manager_id']);
        });
    }
};
