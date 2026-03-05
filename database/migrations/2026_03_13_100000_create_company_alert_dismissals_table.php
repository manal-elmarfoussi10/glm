<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_alert_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('identifier', 128);
            $table->string('action', 16); // done, snooze
            $table->timestamp('snooze_until')->nullable();
            $table->timestamp('dismissed_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('company_alert_dismissals', function (Blueprint $table) {
            $table->unique(['company_id', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_alert_dismissals');
    }
};
