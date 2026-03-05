<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('cin', 32);
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('city', 128)->nullable();
            $table->string('address', 500)->nullable();

            $table->string('driving_license_number', 64)->nullable();
            $table->date('driving_license_expiry')->nullable();

            $table->string('cin_front_path')->nullable();
            $table->string('cin_back_path')->nullable();
            $table->string('license_document_path')->nullable();

            $table->text('internal_notes')->nullable();
            $table->boolean('is_flagged')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
