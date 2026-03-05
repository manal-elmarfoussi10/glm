<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('partner_company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 32)->nullable();
            $table->date('from_date');
            $table->date('to_date');
            $table->text('message')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['partner_company_id', 'status']);
            $table->index(['requester_company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_requests');
    }
};
