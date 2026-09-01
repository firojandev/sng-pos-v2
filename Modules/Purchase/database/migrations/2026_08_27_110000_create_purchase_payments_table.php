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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cash', 'bank', 'mobile_banking', 'card', 'other'])->default('cash');
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Backfill: every existing purchase with a paid_amount > 0 gets one cash payment line.
        foreach (DB::table('purchases')->where('paid_amount', '>', 0)->get() as $purchase) {
            DB::table('purchase_payments')->insert([
                'purchase_id' => $purchase->id,
                'method' => 'cash',
                'amount' => $purchase->paid_amount,
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->created_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
