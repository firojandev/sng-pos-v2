<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;

class PurchaseDeliveryReceiptItem extends Model
{
    protected $fillable = [
        'purchase_delivery_receipt_id',
        'purchase_delivery_order_item_id',
        'product_id',
        'batch_id',
        'batch_no',
        'mfg_date',
        'expiry_date',
        'received_quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'received_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseDeliveryReceipt::class, 'purchase_delivery_receipt_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseDeliveryOrderItem::class, 'purchase_delivery_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
