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
            // Temporary fields for pending registrations
            $table->string('requested_company_name')->nullable();
            $table->string('requested_ice')->nullable();
            $table->integer('fleet_size')->nullable(); // How many cars
            $table->json('operating_cities')->nullable(); // Which cities do you have branches in?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'requested_company_name',
                'requested_ice',
                'fleet_size',
                'operating_cities',
            ]);
        });
    }
};
