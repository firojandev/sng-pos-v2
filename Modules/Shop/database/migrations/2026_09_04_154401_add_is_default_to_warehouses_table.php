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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('status');
        });

        // Set the first active warehouse of each shop as default if one exists
        $shopIds = DB::table('warehouses')->distinct()->pluck('shop_id');
        foreach ($shopIds as $shopId) {
            $firstWarehouse = DB::table('warehouses')
                ->where('shop_id', $shopId)
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            if ($firstWarehouse) {
                DB::table('warehouses')
                    ->where('id', $firstWarehouse->id)
                    ->update(['is_default' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
