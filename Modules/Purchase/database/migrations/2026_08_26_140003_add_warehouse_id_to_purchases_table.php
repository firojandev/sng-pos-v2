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
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('shop_id')->constrained()->nullOnDelete();
        });

        // Default existing purchases to their shop's (auto-provisioned) main warehouse.
        foreach (DB::table('warehouses')->select('id', 'shop_id')->get() as $warehouse) {
            DB::table('purchases')->where('shop_id', $warehouse->shop_id)->whereNull('warehouse_id')->update(['warehouse_id' => $warehouse->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
