<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;

class PurchaseItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id', 'product_id', 'unit_id', 'batch_id', 'batch_no', 'mfg_date', 'expiry_date',
        'quantity', 'received_quantity', 'purchase_price', 'total',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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

    public function receiptItem(): HasOne
    {
        return $this->hasOne(PurchaseReceiptItem::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function pendingQuantity(): float
    {
        return max(0.0, (float) $this->quantity - (float) ($this->received_quantity ?? $this->quantity));
    }

    public function isFullyReceived(): bool
    {
        return (float) ($this->received_quantity ?? $this->quantity) >= (float) $this->quantity;
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

    public function baseReceivedQuantity(): float
    {
        return (float) ($this->received_quantity ?? $this->quantity) * $this->unitConversionFactor();
    }
}
