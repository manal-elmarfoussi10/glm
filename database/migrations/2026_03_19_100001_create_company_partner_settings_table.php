<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_partner_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->boolean('share_enabled')->default(false);
            $table->json('shared_branch_ids')->nullable()->comment('Branch IDs to share in partner search');
            $table->json('shared_categories')->nullable()->comment('economy, sedan, suv');
            $table->boolean('show_price')->default(false);
            $table->timestamps();
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_partner_settings');
    }
};
