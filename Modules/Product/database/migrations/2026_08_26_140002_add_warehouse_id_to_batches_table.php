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
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        // Every existing shop gets one default branch + warehouse so current
        // stock keeps working without requiring anyone to configure locations first.
        // Safe to re-run: skips shops that already have a warehouse (e.g. a retried migration).
        foreach (DB::table('shops')->select('id')->get() as $shop) {
            $warehouseId = DB::table('warehouses')->where('shop_id', $shop->id)->value('id');

            if (! $warehouseId) {
                $branchId = DB::table('branches')->where('shop_id', $shop->id)->value('id');

                if (! $branchId) {
                    $branchId = DB::table('branches')->insertGetId([
                        'shop_id' => $shop->id,
                        'name' => 'প্রধান শাখা',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $warehouseId = DB::table('warehouses')->insertGetId([
                    'shop_id' => $shop->id,
                    'branch_id' => $branchId,
                    'name' => 'প্রধান গুদাম',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('batches')->where('shop_id', $shop->id)->whereNull('warehouse_id')->update(['warehouse_id' => $warehouseId]);
        }

        // Any legacy batch with no shop_id: derive it from its product, then assign a warehouse.
        foreach (DB::table('batches')->whereNull('warehouse_id')->get() as $batch) {
            $productShopId = DB::table('products')->where('id', $batch->product_id)->value('shop_id');
            $warehouseId = $productShopId ? DB::table('warehouses')->where('shop_id', $productShopId)->value('id') : null;

            if ($warehouseId) {
                DB::table('batches')->where('id', $batch->id)->update([
                    'shop_id' => $productShopId,
                    'warehouse_id' => $warehouseId,
                ]);
            }
        }

        // Add the new unique index before dropping the old one — the old index is
        // currently backing the product_id foreign key, so MySQL refuses to drop it
        // until another index starting with product_id exists to take over.
        Schema::table('batches', function (Blueprint $table) {
            $table->unique(['product_id', 'batch_no', 'warehouse_id']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'batch_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unique(['product_id', 'batch_no']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'batch_no', 'warehouse_id']);
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
