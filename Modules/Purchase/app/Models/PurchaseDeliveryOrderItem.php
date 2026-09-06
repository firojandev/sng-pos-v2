<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Models\Product;
use Modules\Product\Models\Unit;

class PurchaseDeliveryOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_delivery_order_id',
        'product_id',
        'unit_id',
        'ordered_quantity',
        'received_quantity',
        'purchase_price',
        'subtotal',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseDeliveryOrder::class, 'purchase_delivery_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseDeliveryReceiptItem::class, 'purchase_delivery_order_item_id');
    }

    public function pendingQuantity(): float
    {
        return max(0.0, (float) $this->ordered_quantity - (float) $this->received_quantity);
    }

    public function isFulfilled(): bool
    {
        return (float) $this->received_quantity >= (float) $this->ordered_quantity;
    }
}
