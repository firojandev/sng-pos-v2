<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'subtotal', 'discount', 'delivery_charge', 'total', 'paid_amount', 'due_amount', 'profit',
        'payment_status', 'payment_method', 'note', 'employee_name', 'employee_phone',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
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
