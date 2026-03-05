<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('type', 8); // 'out' | 'in'
            $table->dateTime('inspected_at')->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->string('fuel_level', 32)->nullable(); // vide, 1/4, 1/2, 3/4, plein
            $table->text('notes')->nullable();
            $table->json('damage_checklist')->nullable(); // out: [{area, description}, ...]
            $table->text('new_damages')->nullable(); // in: free text or structured
            $table->decimal('extra_fees', 12, 2)->nullable(); // in
            $table->string('deposit_refund_status', 32)->nullable(); // pending, refunded, retained, partial
            $table->timestamps();
        });

        Schema::table('reservation_inspections', function (Blueprint $table) {
            $table->unique(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_inspections');
    }
};
