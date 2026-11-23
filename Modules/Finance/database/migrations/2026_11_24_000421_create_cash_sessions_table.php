<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A cash drawer session on one cash account: opened with a carried-forward
     * (optionally counted) balance and closed with a physical cash count.
     */
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open');

            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('opened_at');
            $table->decimal('system_balance_at_open', 12, 2);
            $table->decimal('expected_opening_balance', 12, 2);
            $table->decimal('counted_opening_balance', 12, 2)->nullable();
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('opening_difference', 12, 2)->default(0);
            $table->text('opening_note')->nullable();

            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->decimal('system_balance_at_close', 12, 2)->nullable();
            $table->decimal('expected_closing_balance', 12, 2)->nullable();
            $table->decimal('counted_closing_balance', 12, 2)->nullable();
            $table->decimal('closing_difference', 12, 2)->nullable();
            $table->text('closing_note')->nullable();
            $table->json('movement_summary')->nullable();

            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index(['shop_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
