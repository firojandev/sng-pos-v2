<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Shop\Models\Warehouse;

/**
 * Stock of a product in a warehouse, costed per batch: unit_cost is what one
 * base unit of this batch cost.
 */
class Batch extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'warehouse_id', 'product_id', 'batch_no', 'quantity', 'unit_cost', 'mfg_date', 'expiry_date'];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        // Stock entered without a known cost is valued at the product's
        // current purchase price.
        static::creating(function (Batch $batch) {
            if ($batch->unit_cost === null) {
                $batch->unit_cost = (float) (Product::find($batch->product_id)?->purchase_price ?? 0);
            }
        });
    }

    /**
     * Blend the cost of stock being added into the batch's cost (weighted
     * average). Call before increasing the quantity.
     */
    public function absorbCost(float $incomingQuantity, float $incomingUnitCost): void
    {
        $currentQuantity = max((float) $this->quantity, 0);
        $totalQuantity = $currentQuantity + $incomingQuantity;

        if ($this->unit_cost === null || $currentQuantity <= 0 || $totalQuantity <= 0) {
            $this->unit_cost = round($incomingUnitCost, 4);

            return;
        }

        $this->unit_cost = round(
            (($currentQuantity * (float) $this->unit_cost) + ($incomingQuantity * $incomingUnitCost)) / $totalQuantity,
            4,
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
