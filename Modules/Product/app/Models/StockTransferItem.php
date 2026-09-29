<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['stock_transfer_id', 'product_id', 'batch_id', 'batch_no', 'quantity', 'unit_cost'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    /**
     * The sending shop's product (the receiving shop sees it on the transfer
     * though the catalogue is per shop).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('catalog');
    }

    /**
     * The source batch, which belongs to the sending shop (so the receiving
     * shop can read it too).
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class)->withoutGlobalScope('shop');
    }
}
