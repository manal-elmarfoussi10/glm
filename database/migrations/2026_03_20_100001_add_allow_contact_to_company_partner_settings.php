<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_partner_settings', function (Blueprint $table) {
            $table->boolean('allow_contact_requests')->default(false)->after('show_price');
        });
    }

    public function down(): void
    {
        Schema::table('company_partner_settings', function (Blueprint $table) {
            $table->dropColumn('allow_contact_requests');
        });
    }
};
