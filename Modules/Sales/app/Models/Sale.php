<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Customer\Models\Customer;
use Modules\Shop\Models\Warehouse;

class Sale extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id', 'warehouse_id', 'customer_id', 'invoice_no', 'sale_date',
        'subtotal', 'discount', 'product_discount', 'tax', 'delivery_charge', 'adjustment', 'total', 'paid_amount', 'due_amount', 'profit',
        'payment_status', 'payment_method', 'note', 'employee_name', 'employee_phone',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'product_discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Group items that share the same product, unit, and price (e.g. lines split across batches)
     * into a single aggregated line for customer invoices, sale details, and carts.
     *
     * @return Collection<int, SaleItem>
     */
    public function getGroupedItemsAttribute(): Collection
    {
        return $this->items
            ->groupBy(function ($item) {
                return $item->product_id.'-'.($item->unit_id ?? 'default').'-'.(string) $item->unit_price;
            })
            ->map(function ($group) {
                $first = $group->first();
                if ($group->count() === 1) {
                    $first->batch_no_display = $first->batch?->batch_no;
                    $first->batches_count = $first->batch ? 1 : 0;

                    return $first;
                }

                $totalQuantity = (float) $group->sum('quantity');
                $totalDiscount = (float) $group->sum('discount');
                $totalAmount = (float) $group->sum('total');

                $batches = $group->map(fn ($i) => $i->batch)->filter();
                $batchNos = $batches->pluck('batch_no')->filter()->unique();

                $latestWarrantyItem = $group->sortByDesc('warranty_expires_at')->first();

                $cloned = clone $first;
                $cloned->quantity = $totalQuantity;
                $cloned->discount = $totalDiscount;
                $cloned->total = $totalAmount;
                $cloned->warranty_expires_at = $latestWarrantyItem?->warranty_expires_at;
                $cloned->batch_no_display = $batchNos->implode(', ');
                $cloned->batches_count = $batchNos->count();

                return $cloned;
            })
            ->values();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function canBeDeleted(): bool
    {
        return $this->cannotBeDeletedReason() === null;
    }

    public function cannotBeDeletedReason(): ?string
    {
        if ($this->relationLoaded('returns') ? $this->returns->isNotEmpty() : $this->returns()->exists()) {
            return 'এই বিক্রয়ের বিপরীতে ফেরত রেকর্ড রয়েছে। প্রথমে ফেরত এন্ট্রি বাতিল করুন।';
        }

        return null;
    }

    public function canBeEdited(): bool
    {
        return $this->cannotBeEditedReason() === null;
    }

    public function cannotBeEditedReason(): ?string
    {
        if ($this->relationLoaded('returns') ? $this->returns->isNotEmpty() : $this->returns()->exists()) {
            return 'এই বিক্রয়ের বিপরীতে ফেরত রেকর্ড রয়েছে।';
        }

        return null;
    }
}
