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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->enum('source', [
                'opening_balance',
                'sale',
                'purchase',
                'income',
                'expense',
                'sale_return',
                'purchase_return',
                'transfer_in',
                'transfer_out',
                'manual_adjustment',
            ]);
            $table->string('sourceable_type')->nullable();
            $table->unsignedBigInteger('sourceable_id')->nullable();
            $table->text('note')->nullable();
            $table->datetime('occurred_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sourceable_type', 'sourceable_id']);
            $table->index(['shop_id', 'account_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
