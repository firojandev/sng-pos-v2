<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Shop\Models\Warehouse;

class Batch extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'warehouse_id', 'product_id', 'batch_no', 'quantity', 'mfg_date', 'expiry_date'];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
