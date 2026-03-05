<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('company_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $blueprint->string('category');
            $blueprint->decimal('amount', 12, 2);
            $blueprint->date('date');
            $blueprint->text('description')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
