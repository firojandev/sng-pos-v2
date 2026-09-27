<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock can move between shops of the same company. to_shop_id is the
 * receiving shop (the same as shop_id for a transfer inside one shop), and
 * each item carries the cost of the stock it moves.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('to_shop_id')->nullable()->after('shop_id')->constrained('shops')->cascadeOnDelete();
        });

        DB::table('stock_transfers')->whereNull('to_shop_id')->update(['to_shop_id' => DB::raw('shop_id')]);

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 14, 4)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('to_shop_id');
        });
    }
};
