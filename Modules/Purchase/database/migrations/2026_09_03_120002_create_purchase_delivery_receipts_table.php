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
        Schema::create('purchase_delivery_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_delivery_order_id')->constrained('purchase_delivery_orders')->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->string('receipt_no')->index();
            $table->string('challan_no')->nullable();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->date('delivery_date');
            $table->string('delivery_person_name')->nullable();
            $table->string('delivery_person_phone')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_delivery_receipts');
    }
};
