<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('client_identifier', 64)->unique()->comment('Hash of normalized phone+email for anonymized lookup');
            $table->boolean('verified_identity')->default(false);
            $table->unsignedInteger('successful_rentals_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_verifications');
    }
};
