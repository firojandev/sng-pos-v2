<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Note-by-note physical cash counts taken when a cash session opens or closes.
     */
    public function up(): void
    {
        Schema::create('cash_session_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->cascadeOnDelete();
            $table->enum('stage', ['opening', 'closing']);
            $table->decimal('denomination', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->unique(['cash_session_id', 'stage', 'denomination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_session_counts');
    }
};
