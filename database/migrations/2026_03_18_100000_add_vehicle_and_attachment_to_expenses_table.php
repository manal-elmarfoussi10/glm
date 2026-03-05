<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->string('attachment_path')->nullable()->after('description');
            $table->foreignId('created_by')->nullable()->after('attachment_path')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['vehicle_id', 'attachment_path', 'created_by']);
        });
    }
};
