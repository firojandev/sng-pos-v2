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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_wholesale')->default(false)->after('sale_price');
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('is_wholesale');
            $table->unsignedInteger('wholesale_min_qty')->nullable()->after('wholesale_price');

            $table->boolean('has_discount')->default(false)->after('wholesale_min_qty');
            $table->enum('discount_type', ['flat', 'percentage'])->nullable()->after('has_discount');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');

            $table->boolean('has_barcode')->default(false)->after('discount_value');
            $table->string('barcode')->nullable()->unique()->after('has_barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_wholesale',
                'wholesale_price',
                'wholesale_min_qty',
                'has_discount',
                'discount_type',
                'discount_value',
                'has_barcode',
                'barcode',
            ]);
        });
    }
};
