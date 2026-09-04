<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class Purchase extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id', 'warehouse_id', 'supplier_id', 'invoice_no', 'purchase_date',
        'subtotal', 'discount', 'delivery_charge', 'total', 'paid_amount', 'due_amount', 'payment_status',
        'note', 'employee_name', 'employee_phone',
        'do_number', 'do_date', 'transportation_cost', 'adjustment_cost', 'vehicle_number', 'delivery_person_name',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'do_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'adjustment_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function deliveryReceipt(): HasOne
    {
        return $this->hasOne(PurchaseDeliveryReceipt::class);
    }

    public function hasPendingItems(): bool
    {
        return $this->items->contains(fn ($item) => (float) ($item->received_quantity ?? $item->quantity) < (float) $item->quantity);
    }

    public function pendingItems()
    {
        return $this->items->filter(fn ($item) => (float) ($item->received_quantity ?? $item->quantity) < (float) $item->quantity);
    }

    public function totalPendingQuantity(): float
    {
        return (float) $this->items->sum(fn ($item) => max(0.0, (float) $item->quantity - (float) ($item->received_quantity ?? $item->quantity)));
    }

    public function totalReceivedQuantity(): float
    {
        return (float) $this->items->sum(fn ($item) => (float) ($item->received_quantity ?? $item->quantity));
    }

    public function isFullyReceived(): bool
    {
        return ! $this->hasPendingItems();
    }
}
