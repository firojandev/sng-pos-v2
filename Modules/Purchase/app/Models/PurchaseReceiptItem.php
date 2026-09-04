<?php

namespace Modules\Purchase\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;

class PurchaseReceiptItem extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id',
        'purchase_id',
        'purchase_item_id',
        'product_id',
        'batch_id',
        'received_quantity',
        'do_number',
        'do_date',
        'vehicle_number',
        'delivery_person_name',
        'note',
        'received_by',
    ];

    protected $casts = [
        'received_quantity' => 'decimal:2',
        'do_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
