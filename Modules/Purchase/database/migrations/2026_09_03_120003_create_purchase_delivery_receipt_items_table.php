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
        Schema::create('purchase_delivery_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_delivery_receipt_id')
                ->constrained('purchase_delivery_receipts', indexName: 'pd_rcpt_items_rcpt_fk')
                ->cascadeOnDelete();
            $table->foreignId('purchase_delivery_order_item_id')
                ->constrained('purchase_delivery_order_items', indexName: 'pd_rcpt_items_order_item_fk')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_no');
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_delivery_receipt_items');
    }
};
