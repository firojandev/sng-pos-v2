<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cash', 'bank', 'mobile_banking', 'card', 'other'])->default('cash');
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Backfill: every existing sale with a paid_amount > 0 gets one payment
        // line using its free-text payment_method (defaulting to cash), so
        // reporting can always read from sale_payments regardless of how the
        // sale was originally created.
        foreach (DB::table('sales')->where('paid_amount', '>', 0)->get() as $sale) {
            DB::table('sale_payments')->insert([
                'sale_id' => $sale->id,
                'method' => 'cash',
                'amount' => $sale->paid_amount,
                'note' => $sale->payment_method,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->created_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
