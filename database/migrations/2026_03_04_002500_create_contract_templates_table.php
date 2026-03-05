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
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('source_global_id')->nullable()->constrained('contract_templates')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->longText('content')->nullable();
            $table->json('variables')->nullable(); // optional: list of placeholder keys used
            $table->string('version')->nullable()->default('1.0');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'slug']); // per-company uniqueness; global (null) enforced in app
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
