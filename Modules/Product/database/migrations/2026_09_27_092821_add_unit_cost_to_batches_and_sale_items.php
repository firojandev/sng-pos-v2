<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock is costed per batch: batches.unit_cost is the cost of one base unit
 * in that batch, and sale_items.cost_total is what the sold quantity cost.
 *
 * Existing batches take the cost from their latest purchase line (purchase
 * price converted to the base unit), falling back to the product's purchase
 * price; existing sale lines are costed from their batch.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('unit_cost', 14, 4)->nullable()->after('quantity');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('cost_total', 14, 2)->nullable()->after('total');
        });

        $this->costExistingBatches();
        $this->costExistingSaleItems();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cost_total');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }

    private function costExistingBatches(): void
    {
        DB::table('batches')->orderBy('id')->chunkById(500, function ($batches) {
            foreach ($batches as $batch) {
                $purchaseLine = DB::table('purchase_items')
                    ->where('batch_id', $batch->id)
                    ->orderByDesc('id')
                    ->first(['product_id', 'unit_id', 'purchase_price']);

                $unitCost = $purchaseLine
                    ? (float) $purchaseLine->purchase_price / $this->baseUnitsPer($purchaseLine->product_id, $purchaseLine->unit_id)
                    : (float) DB::table('products')->where('id', $batch->product_id)->value('purchase_price');

                DB::table('batches')->where('id', $batch->id)->update(['unit_cost' => round($unitCost, 4)]);
            }
        });
    }

    private function costExistingSaleItems(): void
    {
        DB::table('sale_items')->whereNotNull('batch_id')->orderBy('id')->chunkById(500, function ($items) {
            foreach ($items as $item) {
                $unitCost = DB::table('batches')->where('id', $item->batch_id)->value('unit_cost');

                if ($unitCost === null) {
                    continue;
                }

                $baseQuantity = (float) $item->quantity * $this->baseUnitsPer($item->product_id, $item->unit_id ?? null);

                DB::table('sale_items')->where('id', $item->id)->update([
                    'cost_total' => round($baseQuantity * (float) $unitCost, 2),
                ]);
            }
        });
    }

    /**
     * How many base units one of the given unit holds (1 when unknown).
     */
    private function baseUnitsPer(int $productId, ?int $unitId): float
    {
        if (! $unitId) {
            return 1.0;
        }

        $pivot = DB::table('product_units')->where('product_id', $productId)->where('unit_id', $unitId)->first();
        $factor = $pivot ? (float) $pivot->conversion_factor : 0.0;

        if ($factor <= 0) {
            return 1.0;
        }

        return ($pivot->is_smaller_unit ?? false) ? 1 / $factor : $factor;
    }
};
