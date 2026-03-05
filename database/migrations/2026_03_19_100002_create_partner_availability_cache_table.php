<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_availability_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('category', 32);
            $table->date('date');
            $table->unsignedInteger('available_count')->default(0);
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'branch_id', 'category', 'date']);
            $table->index(['branch_id', 'category', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_availability_cache');
    }
};
