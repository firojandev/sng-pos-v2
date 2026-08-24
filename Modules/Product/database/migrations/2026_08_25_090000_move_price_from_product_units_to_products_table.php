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
            $table->decimal('purchase_price', 12, 2)->default(0)->after('size');
            $table->decimal('sale_price', 12, 2)->default(0)->after('purchase_price');
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'sale_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'sale_price']);
        });
    }
};
