<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;

class PurchaseItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id', 'product_id', 'batch_id', 'batch_no', 'mfg_date', 'expiry_date',
        'quantity', 'purchase_price', 'total',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
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
}
