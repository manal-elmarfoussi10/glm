<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_inspection_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_inspection_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_inspection_photos');
    }
};
