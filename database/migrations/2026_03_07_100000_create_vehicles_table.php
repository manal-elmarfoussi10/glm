<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('available');

            // Identification
            $table->string('plate', 32);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('vin', 50)->nullable();
            $table->string('fuel', 32)->nullable(); // essence, diesel, hybrid, electric
            $table->string('transmission', 32)->nullable(); // manual, automatic
            $table->unsignedInteger('mileage')->nullable();
            $table->string('color', 64)->nullable();
            $table->unsignedTinyInteger('seats')->nullable();

            // Pricing
            $table->decimal('daily_price', 12, 2)->nullable();
            $table->decimal('weekly_price', 12, 2)->nullable();
            $table->decimal('monthly_price', 12, 2)->nullable();
            $table->decimal('deposit', 12, 2)->nullable();

            // Insurance (Morocco)
            $table->string('insurance_company', 255)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->string('insurance_type', 64)->nullable(); // rc, rc+tierce, tous risques, etc.
            $table->date('insurance_start_date')->nullable();
            $table->date('insurance_end_date')->nullable();
            $table->decimal('insurance_annual_cost', 12, 2)->nullable();
            $table->string('insurance_document_path')->nullable();
            $table->boolean('insurance_reminder')->default(true);

            // Vignette (Dariba) – Morocco
            $table->unsignedSmallInteger('vignette_year')->nullable();
            $table->decimal('vignette_amount', 12, 2)->nullable();
            $table->date('vignette_paid_date')->nullable();
            $table->string('vignette_receipt_path')->nullable();
            $table->boolean('vignette_reminder')->default(true);

            // Visite Technique – Morocco
            $table->date('visite_last_date')->nullable();
            $table->date('visite_expiry_date')->nullable();
            $table->string('visite_document_path')->nullable();
            $table->boolean('visite_reminder')->default(true);

            // Financing
            $table->boolean('is_financed')->default(false);
            $table->string('financing_type', 32)->nullable(); // credit, leasing
            $table->string('financing_bank', 255)->nullable();
            $table->decimal('financing_monthly_payment', 12, 2)->nullable();
            $table->date('financing_start_date')->nullable();
            $table->date('financing_end_date')->nullable();
            $table->decimal('financing_remaining_amount', 12, 2)->nullable();
            $table->string('financing_contract_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
