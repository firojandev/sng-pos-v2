<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 18, 6)->change();
            $table->decimal('unit_price', 18, 6)->change();
        });
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('quantity', 18, 6)->default(0)->change();
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity_change', 18, 6)->change();
            $table->decimal('quantity_before', 18, 6)->change();
            $table->decimal('quantity_after', 18, 6)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
            $table->decimal('unit_price', 12, 2)->change();
        });
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->default(0)->change();
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity_change', 12, 2)->change();
            $table->decimal('quantity_before', 12, 2)->change();
            $table->decimal('quantity_after', 12, 2)->change();
        });
    }
};
