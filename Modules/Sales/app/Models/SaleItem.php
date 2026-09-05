<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;

class SaleItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id', 'product_id', 'batch_id', 'unit_id', 'quantity', 'unit_price',
        'discount', 'total', 'warranty_expires_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'warranty_expires_at' => 'date',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitConversionFactor(): float
    {
        if (! $this->unit_id) {
            return 1.0;
        }

        return $this->product?->unitConversionFactor($this->unit_id) ?? 1.0;
    }

    public function baseQuantity(): float
    {
        return (float) $this->quantity * $this->unitConversionFactor();
    }
}
