<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('snapshot_html')->nullable();
            $table->string('status', 32)->default('draft'); // draft, generated, signed
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::table('reservation_contracts', function (Blueprint $table) {
            $table->unique('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_contracts');
    }
};
