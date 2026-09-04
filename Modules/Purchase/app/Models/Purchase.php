<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Product\Models\Batch;
use Modules\Product\Models\StockMovement;
use Modules\Sales\Models\SaleItem;
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

    /**
     * Get the reason why this purchase cannot be deleted, or null if it can be safely deleted.
     */
    public function cannotBeDeletedReason(): ?string
    {
        if ($this->relationLoaded('returns') ? $this->returns->isNotEmpty() : $this->returns()->exists()) {
            return 'এই ক্রয়ের বিপরীতে পণ্য ফেরত রেকর্ড রয়েছে। / There are purchase return records against this purchase.';
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->with(['product', 'batch'])->get();

        foreach ($items as $item) {
            if (PurchaseReturnItem::where('purchase_item_id', $item->id)->exists()) {
                $productName = $item->product?->name ?? 'পণ্য';

                return "'{$productName}' পণ্যের ফেরত রেকর্ড রয়েছে। / Product has return records.";
            }

            $receivedQty = (float) ($item->received_quantity ?? $item->quantity);
            if ($receivedQty <= 0) {
                continue;
            }

            if (! $item->batch_id) {
                continue;
            }

            /** @var Batch|null $batch */
            $batch = $item->relationLoaded('batch') && $item->batch ? $item->batch : Batch::find($item->batch_id);
            if (! $batch) {
                $productName = $item->product?->name ?? 'পণ্য';

                return "'{$productName}' পণ্যের ব্যাচ পাওয়া যায়নি বা স্টক ইতিমধ্যে ব্যবহার হয়েছে। / Product batch not found or stock already used.";
            }

            if ((float) $batch->quantity < $receivedQty) {
                $productName = $item->product?->name ?? 'পণ্য';

                return "'{$productName}' পণ্যের স্টক ইতিমধ্যে ব্যবহার বা বিক্রয় করা হয়েছে। / Product stock has already been used or sold.";
            }

            $movement = StockMovement::where('batch_id', $item->batch_id)
                ->where('reference_type', static::class)
                ->where('reference_id', $this->id)
                ->latest('id')
                ->first();

            if ($movement) {
                if ((float) $batch->quantity < (float) $movement->quantity_after) {
                    $productName = $item->product?->name ?? 'পণ্য';

                    return "'{$productName}' পণ্যের স্টক ইতিমধ্যে ব্যবহার বা বিক্রয় করা হয়েছে। / Product stock has already been used or sold.";
                }

                if (StockMovement::where('batch_id', $item->batch_id)
                    ->whereIn('type', ['sale', 'transfer_out', 'adjustment_decrease', 'purchase_return'])
                    ->where('id', '>', $movement->id)
                    ->exists()) {
                    $productName = $item->product?->name ?? 'পণ্য';

                    return "'{$productName}' পণ্যের স্টক ইতিমধ্যে বিক্রয়, ট্রান্সফার বা সমন্বয় করা হয়েছে। / Product stock has already been sold, transferred, or adjusted.";
                }
            }

            if ($this->created_at && SaleItem::where('batch_id', $item->batch_id)->where('created_at', '>=', $this->created_at)->exists()) {
                $productName = $item->product?->name ?? 'পণ্য';

                return "'{$productName}' পণ্যটি ইতিমধ্যে বিক্রয় করা হয়েছে। / Product has already been sold.";
            }
        }

        return null;
    }

    /**
     * Check if any quantity from this purchase has already been used, sold, transferred, or returned.
     */
    public function hasUsedQuantity(): bool
    {
        return $this->cannotBeDeletedReason() !== null;
    }

    /**
     * Check if this purchase can be safely deleted and rolled back.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->hasUsedQuantity();
    }
}
